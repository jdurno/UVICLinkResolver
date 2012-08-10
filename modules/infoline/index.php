<?php
/* Infoline doc del request form */

/*
    copyright 2011,2012 University of Victoria Libraries

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

include_once "../includes/contextobject.inc.php";
include_once "../includes/webtemplate.inc.php";


$template = new WebTemplate();
$template->title = "Infoline Request";
$template->headline = "Infoline Request";
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

<p><em>Please note:</em> Use of this service is restricted to registered distance<br>
 education students living  outside the Greater Victoria area</p>
<p>In order to complete the request, please enter the required fields <br>
marked with an <font color=red> * </font> sign: <br>



<!-- end helpful message -->
<p style="font-weight:bold">Contact Information</p>

<form method="get" action="send.php">


<table class="form">
<tr>
    <td class="form-label"> <font color=red> * </font> Your Name </td>
    <td class-"form-field"><input name="req_name" VALUE="" size="60"></td>
</tr>



<tr>
   <td class="form-label"> <font color=red> * </font> Student Number </td>

    <td class-"form-field"><input name="req_id" VALUE="" size="60"></td>
</tr>


<tr>
    <td class="form-label"> <font color=red> * </font> Mailing Address  </td>
    <td class-"form-field"><input name="req_address" VALUE="" size="60"></td>
</tr>

<tr>
    <td class="form-label"> <font color=red> * </font> City/Province </td>
    <td class-"form-field"><input name="req_cityprov" VALUE="" size="60"></td>
</tr>


<tr>
    <td class="form-label"> <font color=red> * </font> Postal Code </td>

    <td class-"form-field"><input name="req_postalcode" VALUE="" size="60"></td>
</tr>

<tr>
    <td class="form-label"> <font color=red>  </font> Your Email Address </td>
    <td class-"form-field"><input name="req_sender" VALUE="" size="60"></td>
</tr>


<tr>
   <td class="form-label"> Course Name </td>
    <td class-"form-field"><input name="req_coursenm" VALUE="" size="60"></td>
</tr>


<tr>
    <td class="form-label">Course Number </td>
    <td class-"form-field"><input name="req_courseno" VALUE="" size="60"></td>
</tr>


<tr>
    <td class="form-label">Phone Number </td>
    <td class-"form-field"><input name="req_phone" VALUE="" size="60"></td>
</tr>

<tr>
    <td class="form-label"> <font color=red>  </font> First Note </td>
    <td class-"form-field"><input name="req_note1" VALUE="" size="60"></td>
</tr>


<tr>
    <td class="form-label"> <font color=red>  </font> Second Note </td>

    <td class-"form-field"><input name="req_note2" VALUE="" size="60"></td>
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
			
			print '</td><td class-"form-field">';
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
