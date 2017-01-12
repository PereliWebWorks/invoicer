<?php require_once("../global.php"); ?>
<?php require_once("../require-log-in.php"); ?>
<?php require_once("../../renderer.php"); ?>
<?php 
	$r = new Response();
	if (empty($_POST["invoice_id"]))
	{
		$r->message = "Bad post.";
		echo $r;
		die();
	}
	$invoice = Invoice::find($_POST["invoice_id"]);
	unset($_POST["invoice_id"]);
	if (!$invoice || $invoice->status !== "0")
	{
		$r->message = "Bad invoice.";
		echo $r;
		die();	
	}
	$client = $invoice->client;
	//If the client doesn't belong to the current user, die.
	if ($client->user != getCurrentUser())
	{
		$r->message = "Bad client.";
		echo $r;
		die();
	}

	//Create invoice html
	$fileHash = md5(rand());
	$htmlPath = __DIR__ . "/../../tmp-html/{$fileHash}.html";
	$renderer->invoice = $invoice;
	$file = fopen($htmlPath, "w");
	fwrite($file, "<html>");
	$renderer->prepare_template('../helpers/head');
	fwrite($file, $renderer->render_string());
	fwrite($file, "<body>");
	$renderer->prepare_template('invoice');
	fwrite($file, $renderer->render_string());
	fwrite($file, "</body></html>");
	fclose($file);
	chmod($htmlPath, 0400);
	$r->path = $htmlPath;
	//Create invoice PDF
	require __DIR__ . "/../../../vendor/phpmailer/phpmailer/PHPMailerAutoload.php";
	use mikehaertl\wkhtmlto\Pdf;
	$pdf = new Pdf($htmlPath);
	$pdfPath = __DIR__ . "/../../tmp-pdfs/{$fileHash}.pdf";
	$success = $pdf->saveAs($pdfPath);
	//Delete html file
	unlink($htmlPath);
	if (!$success) {
	    $r->message = "PDF Failed.";
	    $r->error = $pdf->getError();
		echo $r;
		die();
	}
	//Email invoice pdf
	$mail = new PHPMailer();
	$mail->setFrom("noreply@invoicer.drewpereli.com", "Invoicer");
	$mail->addAddress($client->email);
	$mail->addAttachment($pdfPath, "Invoice.pdf");
	$mail->Subject = "New Invoice from Invoicer!";
	$mail->Body = 'You have a new invoice!';
	$success = $mail->send();
	unlink($pdfPath);
	if(!$success) {
	    $r->message = 'Message could not be sent.';
	    echo $r;
	    die();
	}

	//Update it to pending
	$invoice->update("status", 1);
	//Create new invoice
	$new_invoice = new Invoice(array("client_id"=>$invoice->client->id));
	$new_invoice->save();
	//Respond with success
	$r->message = "success";
	$r->success = true;
	echo $r;	
?>