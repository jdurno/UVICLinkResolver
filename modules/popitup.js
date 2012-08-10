/*
# javascript functions to add a proxy prefix to Refworks, and open supporting modules.
# J Durno, B Sheaff 2011,2012
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


$(document).ready(function () {
	//$('#exportCitationSubmit').click(function() { return false; }); // Adds another click event
	//$('#exportCitationSubmit').off('click');
$('#exportCitationSubmit').hide();
 $('#exportCitationButtons').append('<input id="exportCitationUVicSubmit" name="export" type="button" value="Export" /> ');

	$('#exportCitationUVicSubmit').click(function() {
	//alert('Handler for .click() called.');
	var selection = document.exportCitationForm.itemExportType;

		for (i=0; i<selection.length; i++){

			if (selection[i].checked == true){

				//alert(selection[i].value );
				if(selection[i].value=='refworks'){
					//alert('yes'+document.exportCitationForm.action );
					document.exportCitationForm.action = "https://login.ezproxy.library.uvic.ca/form?qurl="+encodeURI("http://lg5jh7pa3n.search.serialssolutions.com/exportCitation");
					//alert('yes'+document.exportCitationForm.action );
				}else{
					document.exportCitationForm.action = "./exportCitation";
				}
			}
		//desktop
		}
		exportFormSubmit(); 
		return false;
	});



  //document.exportCitationForm.action = "https://login.ezproxy.library.uvic.ca/form?qurl="+encodeURI("http://lg5jh7pa3n.search.serialssolutions.com/exportCitation");
});


function popitup(service) {
	var url = 'http://library.uvic.ca/extfiles/360Link';
	var qstring = location.search.substring(1);
	var height = 800;
	var width = 800;
	
	switch(service) {
		case 'catalogue':
			url = url + '/voyager/index.php?';
			width=900;
			break;
		case 'illo':
			url = url + '/relais/index.php?';
			break;	
		case 'lawillo':
			url = url + '/lawillo/index.php?';
			break;	
		case 'infoline':
			url = url + '/infoline/index.php?';
			break;				
		case 'persistent':
			url = url + '/persistent/index.php?';
			height = 400;
			break;
		case 'google':
			url = url + '/google/index.php?';
			break;	
		case 'problem':
			url = url + '/problem/index.php?';
			height = 600;
			break;


		default:
			return false;
	}
	
	newwindow=window.open(url+qstring,'name','height='+height+',width='+width+',resizable=1,scrollbars=1,menubar=1,location=1,toolbar=1');
	
	if (window.focus) {
		newwindow.focus();
	}
	//return false;
}

