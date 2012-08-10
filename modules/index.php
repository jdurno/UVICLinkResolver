<?php

/*
	Test the application to see if it's doing what we want it to.
	Needs to be called with an OpenURL to work

*/

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



include "includes/contextobject.inc.php";
include "includes/webtemplate.inc.php";

$wt = new WebTemplate();
$wt->title = 'OpenURL Test page';
$wt->headline = 'OpenURL Test Page';
$wt->printHeader();

$ctx_obj = new ContextObject();


print '<p>' . $ctx_obj->getFormat() . '</p>';

print '<table border="1">' . "\n";

foreach ($ctx_obj->fieldList as $field) {
	print '<tr><td>';
	
	print $field;
	
	print '</td><td>';

	print $ctx_obj->labelList[$field];
	
	print '</td><td>';
	
	if (is_array($ctx_obj->$field)) {
		print $ctx_obj->formatArrayAsString($ctx_obj->$field);
	} else {
		print $ctx_obj->$field;
	}
	
	
	print '</td></tr>' . "\n";
}
	
	
print '</table>';

?>


<p>
<a href="javascript:popitup('catalogue')">Check the UVic Libraries Catalogue</a>
</p>


<p>
<a href="javascript:popitup('illo')">Request from another library</a>
</p>

<p>
<a href="javascript:popitup('lawillo')">Request via Law Interlibrary loan</a>
</p>

<p>
<a href="javascript:popitup('infoline')">Request from Infoline</a>
</p>

<p>
<a href="javascript:popitup('persistent')">View persistent link for this item</a>
</p>

<p>
<a href="javascript:popitup('google')">Search for this item in Google Scholar</a>
</p>

<p>
<a href="javascript:popitup('problem')">Problems? Let us know</a>
</p>





<?php
$wt->printFooter();
?>
