<?php require_once("../global.php"); ?>
<?php require_once("../require-log-in.php"); ?>
<?php require_once("../../renderer.php"); ?>
<?php 
	$response = array("message"=>"", "success"=>false);
	if (empty($_POST["invoice_id"]))
	{
		$response["message"] = "Bad post.";
		echo json_encode($response);
		die();
	}
	$invoice = getInvoice($_POST["invoice_id"]);
	unset($_POST["invoice_id"]);
	if (!$invoice || $invoice["status"] !== "0")
	{
		$response["message"] = "Bad invoice.";
		echo json_encode($response);
		die();	
	}
	$client = getClient($invoice["client_id"]);
	//If the client doesn't belong to the current user, die.
	if ($client["user_id"] !== currentUser()["id"])
	{
		$response["message"] = "Bad client.";
		echo json_encode($response);
		die();
	}

	//Create invoice html
	$fileHash = md5(rand());
	$htmlPath = __DIR__ . "/../../tmp-html/{$fileHash}.html";
	$renderer->invoice = $invoice;
	$file = fopen($htmlPath, "w");
	fwrite($file, "<html>");
	fwrite($file, $renderer->render_string('../helpers/head'));
	fwrite($file, "<body>");
	fwrite($file, $renderer->render_string('invoice'));
	fwrite($file, "</body></html>");
	fclose($file);
	chmod($htmlPath, 0400);
	$response["path"] = $htmlPath;
	//Create invoice PDF
	require __DIR__ . "/../../../vendor/phpmailer/phpmailer/PHPMailerAutoload.php";
	use mikehaertl\wkhtmlto\Pdf;
	$pdf = new Pdf($htmlPath);
	$pdfPath = __DIR__ . "/../../tmp-pdfs/{$fileHash}.pdf";
	$success = $pdf->saveAs($pdfPath);
	//Delete html file
	unlink($htmlPath);
	if (!$success) {
	    $response["message"] = "PDF Failed.";
	    $response["error"] = $pdf->getError();
		echo json_encode($response);
		die();
	}
	//Email invoice pdf
	$mail = new PHPMailer();
	$mail->setFrom("noreply@invoicer.drewpereli.com", "Invoicer");
	$mail->addAddress("drewpereli@gmail.com");
	$mail->addAttachment($pdfPath, "Invoice.pdf");
	$mail->Subject = "New Invoice from Invoicer!";
	$mail->Body = 'You have a new invoice!';
	$success = $mail->send();
	unlink($pdfPath);
	if(!$success) {
	    $response["message"] = 'Message could not be sent.';
	    echo json_encode($response);
	    die();
	}

	//Update it to pending
	$q = "UPDATE invoices SET status=:status WHERE id=:id";
	$st = $db->prepare($q);
	$status = "1";
	$st->bindParam(":status", $status);
	$st->bindParam(":id", $invoice["id"]);
	$st->execute();
	//Respond with success
	$response["message"] = "success";
	$response["success"] = true;
	echo json_encode($response);	
?>