<?php
/* Law doc del request form */

include_once "../includes/contextobject.inc.php";
include_once "../includes/webtemplate.inc.php";


$template = new WebTemplate();
$template->title = "Law Interlibrary Loan Request";
$template->headline = "Law Interlibrary Loan Request";
$template->fancy = TRUE;
$template->printHeader();

$ctx_obj = new ContextObject();
$format = $ctx_obj->getFormat();

session_start();



if (isset($_SESSION['message'])) {
	
	echo '<div class="msg-box">';
	echo $_SESSION['message'];
	echo '</div>';
	
	unset($_SESSION['message']);
	
}


?>

<!-- helpful message goes here -->

<p>In order to complete the request, please enter the required fields <br>
marked with an <font color=red> * </font> sign: <br>

<!-- end helpful message -->
<p style="font-weight:bold">Contact Information</p>

<form method="get" action="send.php">


<table class="form">
<tr>
    <td class="form-label"> <font color=red> * </font> Your Name </td>
    <td class="form-field"><input name="req_name" VALUE="" size="60"></td>
</tr>

<tr>
    <td class="form-label">  Your Email Address </td>
   <td class="form-field"><input name="req_sender" VALUE="" size="60"></td>
</tr>

<tr>
    <td class="form-label">  First Note </td>
    <td class="form-field"><input name="req_note1" VALUE="" size="60"></td>
</tr>


<tr>
    <td class="form-label"> Second Note </td>

    <td class="form-field"><input name="req_note2" VALUE="" size="60"></td>
</tr>


</table>

<p style="font-weight:bold">Item Requested</p>

<?php

print '<table class="form">' . "\n";

foreach ($ctx_obj->labelList as $field => $label) {
	
	if (in_array($field, $ctx_obj->fieldList) || $field == 'au') {
	

		if (in_array($field, $ctx_obj->adminFieldList)) {
			
			
			print '<input type="hidden" name="'. $field . '" ';
			
			if (is_array($ctx_obj->$field)) {	
				print 'value="' . $ctx_obj->formatArrayAsString($ctx_obj->$field) . '"  size="60" />' . "\n";
			} else {
			
				print 'value="' . $ctx_obj->$field . '" size="60" />' . "\n";
			}
			
			
			
		} else {	
		
		
			print '<tr><td class="form-label">';
		
			print $label;
			
			print '</td><td class="form-field">';
			print '<input type="text" name="'. $field . '" ';
			
			if ($field == 'au') {
				
				print 'value="' . $ctx_obj->getAuthors() . '" size="60" />' . "\n";
				
			} elseif ($field == 'fmt') {
			
				print 'value="' . $ctx_obj->getFormat() . '" size="60" />' . "\n";
				
			} elseif (is_array($ctx_obj->$field)) {	
				
				print 'value="' . $ctx_obj->formatArrayAsString($ctx_obj->$field) . '"  size="60" />' . "\n";
				
			} else {
				
				print 'value="' . $ctx_obj->$field . '" size="60" />' . "\n";
			}
			
			
			print '</td></tr>' . "\n";
		
		}
	
	}
	
}

?>




</table>

<br />
<div align="center">

<INPUT TYPE="submit"  NAME="submit" VALUE="Send Request">

</div>

</form>


<?php
$template->printFooter();

?>
