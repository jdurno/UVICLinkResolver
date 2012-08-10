<?php
/*
	Forward user to Relais ILL system via OpenURL

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

include "../includes/contextobject.inc.php";


$ctx_obj = new ContextObject();

$thereisaJtitle=false;
$thereisaBtitle=false;

foreach ($ctx_obj->fieldList as $field) {

	if (is_array($ctx_obj->$field)) {
		//print $ctx_obj->formatArrayAsString($ctx_obj->$field);
	} else {
		//print $ctx_obj->$field;
	}
}



$urlrelais="https://cabra.comp.uvic.ca:8443/ILL/prelogin.jsp?UT=P";

if($ctx_obj->genre){
	$urlrelais.="&genre=".$ctx_obj->genre;
}

if($ctx_obj->fmt){
// commented out because it said a book was a book and that broke relais accepting oclc number

	//$urlrelais.="&fmt=".$ctx_obj->fmt;
}

if($ctx_obj->spage){
	$urlrelais.="&spage=".$ctx_obj->spage;
}
if($ctx_obj->aulast){
	//$urlrelais.="&aulast=".$ctx_obj->aulast;
	$urlrelais.="&aulast=".urlencode ($ctx_obj->aulast);
	if($ctx_obj->aufirst){
		$urlrelais.="&aufirst=".urlencode ($ctx_obj->aufirst);
	}
}else{
	if($ctx_obj->getAuthors()	){
		//$urlrelais.="&aulast=".$ctx_obj->getAuthors();
		$urlrelais.="&aulast=".urlencode ($ctx_obj->getAuthors());
		//$urlrelais.="&aufirst=".$ctx_obj->getAuthors();
	}
}

if($ctx_obj->date){
	$urlrelais.="&date=".$ctx_obj->date;
}

if($ctx_obj->volume){
	$urlrelais.="&volume=".$ctx_obj->volume;
}

if($ctx_obj->issue){
	$urlrelais.="&issue=".$ctx_obj->issue;
}
if($ctx_obj->atitle){
	$newtitle = replaceANDsymbol($ctx_obj->atitle);
	$urlrelais.="&atitle=".$newtitle;

}
// title of the journal  could use jtitle
if($ctx_obj->jtitle){
	$thereisaJtitle=true;
	$newtitle = replaceANDsymbol($ctx_obj->jtitle);
	$urlrelais.="&jtitle=".$newtitle;
}
if($ctx_obj->btitle){
	$thereisaBtitle=true;
	$newtitle = replaceANDsymbol($ctx_obj->btitle);
	$urlrelais.="&btitle=".$newtitle;
}
if($ctx_obj->title){
	if(!$thereisaJtitle){
		if(!$thereisaBtitle){
			$newtitle = replaceANDsymbol($ctx_obj->title);
			$urlrelais.="&title=".$newtitle;
		}
	}
}

if($ctx_obj->issn){
// added by ben feb 10 2012 to add dashes so relais searching works without them manually having to add a dash
	$containsdash = strstr($ctx_obj->issn,"-"); 
	if(!$containsdash){
		 $issndash =  substr_replace($ctx_obj->issn, '-', 4,0);
		 $urlrelais.="&issn=".$issndash;
	}else{
		$urlrelais.="&issn=".$ctx_obj->issn;
	}
}
if($ctx_obj->eissn){
	$urlrelais.="&eissn=".$ctx_obj->eissn;
}
if($ctx_obj->isbn){

// added by ben Jan 24 2012 to deal with relais only accepting 13 characters in the isbn field.  Therefor remove the dashes
	$urlrelais.="&isbn=".str_replace("-", "", $ctx_obj->isbn);
}
if($ctx_obj->rft_id){
	// comes in like  rft_id=info%3Aoclcnum%2F180923174
	// if oclcnum then needs to go out like
	//&bibsrc=FirstSearch:WorldCat&verify=180923174
	if (is_array($ctx_obj->rft_id)){ 

		 
		$formattedIDField = $ctx_obj->formatArrayAsString($ctx_obj->rft_id);
	}else{
		$formattedIDField = $ctx_obj->rft_id;
	}
	$oclcsub = strstr($formattedIDField, 'oclcnum');
	if($oclcsub){
		// looks like oclcnum/180923174
		$oclcNum = substr(strstr($formattedIDField, '/'), 1);
		$urlrelais.="&bibsrc=FirstSearch:WorldCat&verify=" . $oclcNum;
	}
}
// Feb 10 2012 added by ben to try to include the source of the request rfr_id comes from summon and sid was used by one of the databases I tested
if( isset($_REQUEST['rfr_id']) ){
	$urlrelais.="&bibsrc=" . $_REQUEST['rfr_id'];
}
if( isset($_REQUEST['sid']) ){
	$urlrelais.="&bibsrc=" . $_REQUEST['sid'];
}

/*  testing stuff
	echo "\n<!-- ";
	print_r($_REQUEST);
	echo "-->\n";
	echo"<br><a href='$urlrelais'>$urlrelais</a> <br>";
	exit;
*/
header("Location: $urlrelais");

// added by ben Feb 10 2012 to deal with & sybmol in titles then going into the url and breakin the title
function replaceANDsymbol($in) {
	$out	=  str_replace("&", "%26", $in);
	return $out;
}


?>
