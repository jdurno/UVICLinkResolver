<?php

/*
	Reformat an OpenURL string as a Google Scholar query

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



/* Google sample url

http://scholar.google.ca/scholar?as_q=history
&num=50
&btnG=Search+Scholar
&as_occt=title
&as_sauthors=Elena+Kagan
&as_publication=Princeton+University+Press
&as_ylo=1900
&as_yhi=2011

*/
//genre	book
//Genre	article

$googleUrl="http://ezproxy.library.uvic.ca/login?url=http://scholar.google.ca/scholar?";
$googleUrlJournalTitle="";
$ctx_obj = new ContextObject();

if($ctx_obj->atitle){ // this is as_q the main query
	$googleUrl.="as_q=".$ctx_obj->atitle;
}else if($ctx_obj->btitle){
	$googleUrl.="as_q=".$ctx_obj->btitle;
}

if($ctx_obj->title){
	if($ctx_obj->labelList[title]=="Journal Title"){
		$googleUrlJournalTitle="&as_publication=".$ctx_obj->title;
	}else{ // assume its a book title? could break if btitle also exists...
	//echo "here else",$ctx_obj->labelList[title];
		if(!strstr($googleUrl, 'as_q=')){
			$googleUrl.="as_q=".$ctx_obj->title;
		}
	}
}else if($ctx_obj->jtitle){
	$googleUrlJournalTitle="&as_publication=".$ctx_obj->jtitle;
}

$googleUrl.="&num=50&btnG=Search+Scholar&as_occt=title";
// now author if no journal title
if($googleUrlJournalTitle==""){
	if($ctx_obj->aulast){
		$googleUrl.="&as_sauthors=".$ctx_obj->aulast;
	}else if($ctx_obj->getAuthors()	){
		$googleUrl.="&as_sauthors=".$ctx_obj->getAuthors();
	}
}

// then publisher
$googleUrl.=$googleUrlJournalTitle;

//echo "<br>$googleUrl<br><a href='$googleUrl'>google search</a>\n<br>\n";
header("Location: $googleUrl");
// now dates, lets just get the year and subtract 1 and add 1 to get the range google scholar wants ylo and yhi


?>
