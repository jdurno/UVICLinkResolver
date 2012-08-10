<?php
/* Problem reporting form */
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
$template->title = "Article Linker Problem Report";
$template->headline = "Article Linker Problem Report";
$template->fancy = TRUE;
$template->printHeader();

$ctx_obj = new ContextObject();
$format = $ctx_obj->getFormat();


//determine the title of the Journal or Book
$msgTitle = $ctx_obj->getJournalTitle();
if (!$msgTitle) {
	$msgTitle = $ctx_obj->getBookTitle();	
} 

if (!$msgTitle) {
	$msgTitle = $ctx_obj->getSN();	
}

if (!$msgTitle) {
	$msgTitle = 'No title';
}
	
	

session_start();



if (isset($_SESSION['message'])) {
	
	echo '<div class="msg-box">';
	echo $_SESSION['message'];
	echo '</div>';
	
	unset($_SESSION['message']);
	
}


?>

<!-- helpful message goes here -->

<p>Use this form to report a problem with this service.<br>
Please note that bibliographic details will be sent automatically. You do not need to supply them.<br />
Required fields are marked with an <font color=red> * </font> sign: </p>

<!-- end helpful message -->

<form method="get" action="send.php">
<input type="hidden" name="msgtitle" value="<?php echo $msgTitle; ?>" />
<table class="form">
<tr>
    <td class="form-label"> <font color=red> * </font> Your Name </td>
    <td class-"form-field"><input name="req_name" VALUE="" size="60"></td>
</tr>

<tr>
    <td class="form-label"><font color=red> * </font> Your Email Address </td>
    <td class-"form-field"><input name="req_sender" VALUE="" size="60"></td>
</tr>

<tr>
    <td class="form-label"><font color=red> &nbsp; </font> Your Telephone </td>
    <td class-"form-field"><input name="req_tele" VALUE="" size="60"></td>
</tr>


<tr>
<td class="form-label"> <font color=red> * </font> Connecting from:</td>

    <td class-"form-field"><input type="radio" value="oncampus" name="location">On campus
    <input type="radio" value="offcampus" name="location">Off campus</td>
</tr>

<td class="form-label"> <font color=red> &nbsp; </font>Your Patron Category</td>

    <td class-"form-field">
    <select name="ptype">
    <option value="other">Undefined</option>
    <option value="undergrad">Undergraduate</option>
    <option value="grad">Grad Student</option>
    <option value="faculty">Faculty/Researcher</option>
    <option value="librarian">Librarian</option>
    <option value="staff">Staff</option>
    </select>
    </td>
</tr>

<td class="form-label" colspan="2" style="text-align:left"> <font color=red> * </font>Please describe the problem:<br />
<textarea rows="10" cols="80" name="problem"></textarea>

</td>
<!-- 
<td class="form-label"> Is the article still required?</td>
<td class="form-field"><input type="radio" value="yes" name="required">Yes&nbsp;<input type="radio" value="no" name="required">No</td>
-->
</table>

<?php 
echo '<input type="hidden" name="qstring" value="' . $_SERVER['QUERY_STRING'] .'" />';

?>


<div align="center">

<INPUT TYPE="submit"  NAME="submit" VALUE="Send Message">

</div>

</form>


<?php
$template->printFooter();

?>
