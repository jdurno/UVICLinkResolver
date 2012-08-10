<?php

/* send.php */
/* Send requests to the Law Interlibrary Loan Office*/
/* Takes user details and citation data and formats them into a useful message */
/* Last modified: Nov. 9 2011 J. Durno*/

include_once "../includes/contextobject.inc.php";
include_once "../includes/Mailer.class.php";

/* specify where to forward users to */
$forwardTo = 'http://library.uvic.ca/extfiles/360Link/lawillo/';

$ctx_obj = new ContextObject();

session_start();

/* Specify the recipient of the message here */
$toAddress = '';


/* just for testing */
/*
foreach ($_REQUEST as $ind => $var) {
	echo "$ind => $var <br />";	
}
*/

//array for the requestor's details to live in
$r = array();

//get the requestor's details
$r['Name'] = isset($_REQUEST['req_name']) ? $_REQUEST['req_name'] : NULL;
$r['Email'] = isset($_REQUEST['req_sender']) ? $_REQUEST['req_sender'] : NULL;
$r['Note1'] = isset($_REQUEST['req_note1']) ? $_REQUEST['req_note1'] : NULL;
$r['Note2'] = isset($_REQUEST['req_note2']) ? $_REQUEST['req_note2'] : NULL;

//get some server variables
$referer = $_SERVER['HTTP_REFERER'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$remoteAddr = $_SERVER['REMOTE_ADDR'];

//email is not required, but we need something there
if (empty($r['Email'])) {
	$r['Email'] = 'DoNotReplyToMe@uvic.ca';
}



//if no name or email submitted, go away
if (	empty($r['Name']) ) {

	$message = "Sorry, you need to complete all the required fields, otherwise your request cannot be sent";
	$return_loc = $forwardTo . 'index.php?' . $_SERVER['QUERY_STRING'];
	go_away($message, $return_loc);
}

//if email address does not contain an @ sign, it's probably form spam
//this works because the '@' sign cannot be the first character in the string
if (!strpos($r['Email'], '@')) {
	$message = "Sorry, your email address appears to be invalid. Your form submission cannot be processed.";
	$return_loc = $forwardTo . 'index.php?' . $_SERVER['QUERY_STRING'];
	go_away($message, $return_loc);
}


//create the message body by combining requestor details, item details, and some administrative info

$message_body = '<p><b>Requested Item</b></p>';
$message_body .= formatRequestedItem();
$message_body .= "\n\n";
$message_body .= '<p><b>Requestor Info</b></p>';
$message_body .= formatRequestorDetails($r);




$senderInfo = '<p><font size="-1">';
$senderInfo .= '<b>Sent From:</b> ' . $remoteAddr . '<br />';
$senderInfo .= '<b>User Agent:</b> ' . $userAgent . '<br />';
$senderInfo .= '<b>Date &amp; Time</b> ' . date('M j, Y g:i a') . '<br />';
$senderInfo .= '</font></p>';
$message_body .= $senderInfo;

/*
// Just for testing
echo $message_body;
exit;
*/

$myMailer = new Mailer;
$myMailer->setFromName($r['Name']);
$myMailer->setFromAddress($r['Email']);
$myMailer->setToAddress( $toAddress );
$myMailer->setSubjectLine( 'Law ILL Request' );
$myMailer->setContent($message_body);
$myMailer->setHTML(TRUE);

if ( $myMailer->sendEmail() ) {
	$message = 'Thank you. Your request has been sent to Law Interlibrary Loan.';
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
	if (strlen($var) > 400) {
		$var = substr($var, 0, 400);
	}
	
	
	return $var;	
	
}


function formatRequestorDetails ($r) {
	
	$formatted = '<table border="1">';
	
	foreach ($r as $ind => $var) {
		
		if (!is_null($var)) {
		
			$var = cleanVar($var);
			$formatted .= '<tr><td><strong>' . $ind . '</strong></td>';
			$formatted .= '<td>' . $var . '</td></tr>'  . "\n";
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
					
					$tackOn .= $ctx_obj->formatArrayAsString($ctx_obj->$field);
				} else {	
					$tackOn .= $ctx_obj->$field;
				}
							
				$tackOn .= '</td></tr>' . "\n";
				
				
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
