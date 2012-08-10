<?php

/* send.php */
/* Send problem reports to those who can deal with them*/
/* Takes user details and citation data and formats them into a useful message */
/* Last modified: Nov. 2 2011 J. Durno*/

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
include_once "../includes/Mailer.class.php";

/* specify where to forward users to */
$forwardTo = 'http://library.uvic.ca/extfiles/360Link/problem/';


session_start();

/* Specify the recipient of the message here */
$toAddress = '';


/* just for testing */
/*
foreach ($_REQUEST as $ind => $var) {
	echo "$ind => $var <br />";	
}
exit;
*/

//array for the requestor's details to live in
$r = array();

//get the requestor's details
$r['Name'] = isset($_REQUEST['req_name']) ? $_REQUEST['req_name'] : NULL;
$r['Email'] = isset($_REQUEST['req_sender']) ? $_REQUEST['req_sender'] : NULL;
$r['Telephone'] = isset($_REQUEST['req_tele']) ? $_REQUEST['req_tele'] : NULL;
$r['Location'] = isset($_REQUEST['location']) ? $_REQUEST['location'] : NULL;
$r['Patron Type'] = isset($_REQUEST['ptype']) ? $_REQUEST['ptype'] : NULL;
$problem = isset($_REQUEST['problem']) ? $_REQUEST['problem'] : NULL;
$qstring = isset($_REQUEST['qstring']) ? $_REQUEST['qstring'] : NULL;
$msgTitle = isset($_REQUEST['msgtitle']) ? $_REQUEST['msgtitle'] : NULL;
$stillReq = isset($_REQUEST['required']) ? $_REQUEST['required'] : NULL;

if ($msgTitle) {
	if ($msgTitle > 50) {
		$WidgetText = substr($string, 0, strrpos(substr($string, 0, 50), ' '));
	}
}



//get some server variables
$referer = $_SERVER['HTTP_REFERER'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$remoteAddr = $_SERVER['REMOTE_ADDR'];


//if no name or email submitted, go away
if (	empty($r['Name']) || empty($r['Email']) || empty($r['Location']) || empty($problem)  ) {

	$message = "Sorry, you need to complete all the required fields, otherwise your request cannot be sent";
	$return_loc = $forwardTo . 'index.php?' . $qstring;
	go_away($message, $return_loc);
}

//if email address does not contain an @ sign, it's probably form spam
//this works because the '@' sign cannot be the first character in the string
if (!strpos($r['Email'], '@')) {
	$message = "Sorry, your email address appears to be invalid. Your form submission cannot be processed.";
	$return_loc = $forwardTo . 'index.php?' . $qstring;
	go_away($message, $return_loc);
}

//initialize the ctx object
$ctx_obj = new ContextObject($qstring);


//create the message body by combining requestor details, item details, and some administrative info



$message_body = '<p><b>Problem Report</b></p>';
$message_body .= nl2br($problem);

$message_body .= '<p><b>User Data</b></p>';
$message_body .= formatRequestorDetails($r);

$message_body .= '<p><b>Metadata</b></p>';
$message_body .= formatRequestedItem();

$message_body .= '<p><b>OpenURL Link</b></p>';
$message_body .= 'http://lg5jh7pa3n.search.serialssolutions.com/?' . $qstring;


$senderInfo = '<p><font size="-1">';
$senderInfo .= '<b>Sent From:</b> ' . $remoteAddr . '<br />';
$senderInfo .= '<b>User Agent:</b> ' . $userAgent . '<br />';
$senderInfo .= '<b>Date &amp; Time</b> ' . date('M j, Y g:i:s a') . '<br />';
$senderInfo .= '</font></p>';
$message_body .= $senderInfo;

//just for testing
/*
echo $message_body;
exit;
*/

$myMailer = new Mailer;
$myMailer->setFromName($r['Name']);
$myMailer->setFromAddress($r['Email']);
$myMailer->setToAddress( $toAddress );
$myMailer->setSubjectLine( '360 Link Problem: ' . $msgTitle );
$myMailer->setContent($message_body);
$myMailer->setHTML(TRUE);

if ( $myMailer->sendEmail() ) {
	$message = 'Thank you. Your problem report has been sent.';
	$return_loc = $forwardTo . 'ack.php';
} else {
	$message = 'Sorry, there was a problem with your form submission. Please try again.';
	$return_loc = $forwardTo . 'index.php?' . $_SERVER['QUERY_STRING'];
}


go_away($message, $return_loc);




function go_away($message = NULL, $location = NULL) {
	
	global $_SESSION;
	global $forwardTo;
	
	if ($message) {
		$_SESSION['message'] = $message;
	}
	
	if($location) {
		header( 'location: ' . $location );
	} else {
		header( 'location: ' . $forwardTo . 'ack.php' );	
	}
	
	exit;
}






//make sure the form parameters aren't too messy
function cleanVar($var) {
	
	
	//trim whitespace 
	$var = trim($var);
	
	//can't contain html
	$var = strip_tags($var);
	
	//can't be more than 400 characters
	if (strlen($var) > 5000) {
		$var = substr($var, 0, 5000);
	}
	
	
	return $var;	
	
}


function formatRequestorDetails ($r) {
	
	$formatted = '<table border="1">';
	
	foreach ($r as $ind => $var) {
		
		if (!is_null($var)) {
		
			$var = cleanVar($var);
			$formatted .= '<tr><td><strong>' . $ind . '</strong></td>';
			$formatted .= '<td>' . $var . '</td></tr>';
		}	
			
	}
	
	$formatted .= '</table>';
	
	return $formatted;
	
	

}

function formatRequestedItem() {
	
	global $ctx_obj;
	
	$formatted = '<table border="1">' . "\n";
	$tackOn = '';

	foreach ($ctx_obj->labelList as $field => $label) {
	
		if (in_array($field, $ctx_obj->fieldList)) {
			
			if (in_array($field, $ctx_obj->adminFieldList)) { 
				
				$tackOn .= '<tr><td><strong>';
		
				$tackOn .= $label;
			
				$tackOn .= '</strong></td><td>';
				
				if (is_array($ctx_obj->$field)) {
					
					$tackOn .= $ctx_obj->formatArrayAsString($ctx_obj->$field) . "\n";
				} else {	
					$tackOn .= $ctx_obj->$field;
				}
							
				$tackOn .= '</td></tr>';
				
				
			} else {
					
				$formatted .= '<tr><td><strong>';
			
				$formatted .= $label;
				
				$formatted .= '</strong></td><td>';
				
				if (is_array($ctx_obj->$field)) {	
					
					$formatted .= $ctx_obj->formatArrayAsString($ctx_obj->$field) . "\n";
				} else {	
					$formatted .= $ctx_obj->$field;
				}
				
				$formatted .=  '</td></tr>' . "\n";
				
			}
		}
					
	}
	
	$formatted .= $tackOn;
	
	$formatted .= '</table>';
	
	return $formatted;
	
}
	






	




?>
