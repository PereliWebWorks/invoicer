
<?php
	$i = new Invoice($_POST["new-invoice"]);
	$attempt = $i->save();
	return $attempt;
?>