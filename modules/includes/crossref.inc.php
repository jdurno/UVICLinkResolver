<?php
/*Get bib data from crossref using a DOI and construct an OpenURL string*/

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



class crossref{

	private $user 		= ""; //crossref user name goes here
	private $password 	= ""; //crossref pwd goes here
	private $format		= "unixref";
	
	private $doi;
	private $url;
	
	public function __construct($doiIn) {
		//echo "$doiIn start of constructor <br>";
		$this->doi 	= $doiIn;

		
		$this->url		="http://doi.crossref.org/servlet/query?pid="
						.$this->user.":".$this->password."&id=".$this->doi."&format=".$this->format."";
		//echo $this->url."  End of constructor <br>";
	}
	
	
	
	public function getCrossRefOpenUrl(){

		$result= NULL;

		$ch2 = curl_init();
		curl_setopt($ch2, CURLOPT_URL, $this->url	);
		//return the transfer as a string
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
		// $output contains the output string
		$legends = curl_exec($ch2);
		//echo "<br><hr><br>legend= $legends";
		$xml = simplexml_load_string($legends);

		if ($this->format	== "xsd_xml"){
			$result = handle_xsd_xml($xml);
			
		}

		if ($this->format	== "unixref") $result = $this->handle_unixref($xml,$this->doi);

		return $result;
	}
	
	
	function handle_unixref($xml,$doi){
		//echo "start of handle_unixref $doi";
		$openUrl ="";
		$results = $xml->doi_record->crossref->journal;
		$count = count($results->children());
		if($count >0){
			//echo $count ." journal results<br>";
			$openUrl = $this->handle_unixref_journal($results,$doi);
		}else{
			$results = $xml->doi_record->crossref->conference; 
			$count = count($results->children());
			if($count >0){
				//echo $count ." conference results<br>";
				$openUrl = $this->handle_unixref_conference($results,$doi);
				
			}else{
				$results = $xml->doi_record->crossref->book; 
				$count = count($results->children());
				if($count >0){
					//echo $count ." book results<br>";
					$openUrl = $this->handle_unixref_book($results,$doi);
				}else{
					//echo "ERROR::Unhandled crossref type <pre>";
					//print_r($xml->doi_record->crossref);
					
				}
			}
		}
		//echo "end of handle_unixref $openUrl";
		return $openUrl;
	 }
	 
	 function handle_unixref_journal($xml,$doi){
		//echo "handle_unixref_journal<br>";
		$jtitle	= $xml->journal_metadata->full_title;
		//could be more than one issn in an array so just get the first?
		$issn	= $xml->journal_metadata->issn;
		if( is_array($issn) ) $issn	= $issn[0];
		
		$year	= $xml->journal_issue->publication_date->year;
		$month	= $xml->journal_issue->publication_date->month;
		$volume	= $xml->journal_issue->journal_volume->volume;
		$issue	= $xml->journal_issue->issue;
		
		$atitle			= $xml->journal_article->titles->title;
		$person_name	= $xml->journal_article->contributors->person_name;
		
		
		$lname 			= $person_name->surname;
		if( is_array($person_name) ) $lname	= $person_name[0]->surname;
		
		$fname 			= $person_name->given_name;
		if( is_array($person_name) ) $fname	= $person_name[0]->given_name;
		
		$first_page	= $xml->journal_article->pages->first_page;
		$last_page	= $xml->journal_article->pages->last_page;
		
		//echo"jtitle $jtitle<br>atitle $atitle<br>lname $lname<br>fname $fname<br>volume $volume<br>issue $issue<br>first_page $first_page<br>last_page $last_page<br>";
		//echo"year $year<br>month $month<br>issn $issn<br>";
		$openURl="&issn=".$issn."&spage=".$first_page."&epage".$last_page."&aulast="
				.$lname."&aufirst=".$fname."&date=".$year."-".$month."&volume=".$volume."&issue="
				.$issue."&atitle=".$atitle."&jtitle=".$jtitle."&rft_id=info:doi/".$doi;
		return $openURl;
	 }
	 
	 function handle_unixref_conference($xml,$doi){

		//proceedings_metadata isbn proceedings_title publication_date->year

		$conferencetitle 		= $xml->conference_paper->titles->title;
		$conferencesubtitle 	= $xml->conference_paper->titles->subtitle;
		$person_name			= $xml->conference_paper->contributors->person_name;
		$isbn					= $xml->proceedings_metadata->isbn;
		$issn					= $xml->proceedings_metadata->issn;
		$publisher				= $xml->proceedings_metadata->publisher->publisher_name;
		
		$lname 			= $person_name->surname;
		if( is_array($person_name) ) $lname	= $person_name[0]->surname;
		
		$fname 			= $person_name->given_name;
		if( is_array($person_name) ) $fname	= $person_name[0]->given_name;
		$year	= $xml->conference_paper->publication_date->year;
		$fpage	= $xml->conference_paper->pages->first_page;
		$lpage	= $xml->conference_paper->pages->last_page;
		

		
		$openURl="&rft.genre=book&rft.isbn=".$isbn."&rft.issn=".$issn."&rft.publisher=".$publisher."&rft.aulast="
				.$lname."&rft.aufirst=".$fname."&rft.date="
				.$year."&rft.btitle=".$conferencetitle.":".$conferencesubtitle
				."&spage=".$fpage."&epage".$lpage."&rft_id=info:doi/".$doi;
		return $openURl;
	 }
	 
	 function handle_unixref_book($xml,$doi){
		//echo "handle_unixref_book<br> ";
		$btitle 		= $xml->book_metadata->titles->title;
		$publisher		= $xml->book_metadata->publisher->publisher_name;
		$year			= $xml->book_metadata->publication_date->year;
		$isbns			= $xml->book_metadata->isbn;
		$isbn 			= $isbns;
		if( is_array($isbns) ) $isbn	= $isbns[0];
		
		$person_name	= $xml->book_metadata->contributors->person_name;
		$lname 			= $person_name->surname;
		if( is_array($person_name) ) $lname	= $person_name[0]->surname;
		
		$fname 			= $person_name->given_name;
		if( is_array($person_name) ) $fname	= $person_name[0]->given_name;
		
		//echo"btitle $btitle<br>isbn $isbn<br>lname $lname<br>fname $fname<br>publisher $publisher<br>year $year<br>";

		$openURl="&rft.genre=book&rft.isbn=".$isbn."&rft.publisher=".$publisher."&rft.aulast="
				.$lname."&rft.aufirst=".$fname."&rft.date=".$year."&rft.btitle=".$btitle."&rft_id=info:doi/".$doi;
		return $openURl;
	 }
	 
	 
	 // did not impliment this
	function handle_xsd_xml($xml){
	//echo "handle_xsd_xml<br>";
		$results = $xml->query_result->body->query;
		$aulast		= $results->author;
		$volume		= $results->author;
		$jtitle		= $results->author;
		$title		= $results->author;
		$issue		= $results->author;
		$fp			= $results->author;
		$year		= $results->author;
		/*
		echo $results->doi." <br>\n";
		echo $results->issn." <br>\n"; // could be an array
		echo $results->isbn." <br>\n";
		echo $results->journal_title." <br>\n";
		echo $results->author." <br>\n";
		echo $results->volume." <br>\n";
		echo $results->volume_title." <br>\n";
		echo $results->issue." <br>\n";
		echo $results->first_page." <br>\n";
		echo $results->year." <br>\n";
		echo $results->publication_type." <br>\n";
		*/

	}
	
}


?>
