<?php
/*Display a persistent link*/

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


include_once "../includes/webtemplate.inc.php";


$template = new WebTemplate();
$template->title = "Persistent URL";
$template->fancy = TRUE;
$template->printHeader();
?>


<p><b>Persistent URL for this item:</b></p>

<p><a href="http://lg5jh7pa3n.search.serialssolutions.com/?<?php echo $_SERVER['QUERY_STRING']?>">http://lg5jh7pa3n.search.serialssolutions.com/?<?php echo $_SERVER['QUERY_STRING']?></a></p>

<div style="align:center">
<form action="javascript:window.close()"><input type="submit" value="Close Window"></form>
</div>

<?php
$template->printFooter();

?>


