<?php
/* Request acknowledgement form */

include_once "../includes/webtemplate.inc.php";


$template = new WebTemplate();
$template->title = "Law Interlibrary Loan Request";
$template->headline = "Law Interlibrary Loan Request";
$template->fancy = TRUE;
$template->printHeader();


session_start();

if (isset($_SESSION['message'])) {
	
	echo $_SESSION['message'];
	unset($_SESSION['message']);
	
}

?>

<p>&nbsp;</p>
<div style="align:center">
<form action="javascript:window.close()"><input type="submit" value="Close Window"></form>
</div>

<?php

$template->printFooter();

?>
