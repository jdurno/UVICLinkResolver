<?php

//print a standard header & footer for openURL webforms


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

class WebTemplate
{

	public $title;
	public $headline;
	public $fancy = FALSE;
	

	
	
	public function printHeader() {
		
		echo '<html>' . "\n";
		echo '<head>' . "\n";
		echo '<title>' . "\n";
		echo $this->title;
		echo '</title>' . "\n";
		echo '<link rel="stylesheet" type="text/css" href="/extfiles/360Link/includes/main.css" title="style1">';
		echo '<script src="/extfiles/360Link/popitup.js" type="text/javascript"></script>';
		echo '</head>' . "\n";
		
		
		if ($this->fancy) {
			echo '<body style="background-color:eae9e8">' . "\n";	
			//apply some fancy formatting to make it look pretty
			echo '<div id="bounding-box">' . "\n";
			echo '<div id="header-image"><img src="/extfiles/360Link/uvic-shield40.gif" /></div>' . "\n";
			
			
		} else {
			echo '<body>' . "\n";	
		}
		
		echo '<h1>' . $this->headline . '</h1>' . "\n";

		
	}
	
	
	public function printFooter() {
		
		if ($this->fancy) {
			//end div bounding-box
			echo '</div>';	
		}
		
		echo '</body></html>';	
		
	}
	
}

?>
