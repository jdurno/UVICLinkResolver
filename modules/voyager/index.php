<?php
/* Convert an openURL into a Voyager search */

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

//Voyager search URL, search is appended below
$voyURL="http://voyager.library.uvic.ca/vwebv/search?";

//try to determine the format. This depends on metadata in the OpenURL, so it won't always work.
//the getFormat() function attempts to guess the format based on clues in the openurl metadata
$format = $ctx_obj->getFormat();

//some books have ISSNs, and we don't want to search on ISSNs for books
if ($format !== 'book') {
	//if there is a issn or eissn just use that 

	if($ctx_obj->issn || $ctx_obj->eissn){
		if($ctx_obj->issn){
			$withdashissn=$ctx_obj->issn;
			if(strlen($ctx_obj->issn)<9){
				$orig_string=$ctx_obj->issn;
				$insert_string="-";
				$position=4;
				$withdashissn=substr_replace($orig_string, $insert_string, $position, 0);
			}
			$voyURL.="searchArg1=".$withdashissn."&argType1=phrase&searchCode1=ISBN";
		} else if($ctx_obj->eissn){
			$withdasheissn=$ctx_obj->issn;
			if(strlen($ctx_obj->eissn)<9){
				$orig_string=$ctx_obj->eissn;
				$insert_string="-";
				$position=4;
				$withdasheissn=substr_replace($orig_string, $insert_string, $position, 0);
			}
			$voyURL.="searchArg1=".withdasheissn."&argType1=phrase&searchCode1=ISBN";
		}
		$voyURL.="&recCount=25&searchType=2&page.search.search.button=Search";
		header("Location: $voyURL");
		exit;
	}

}

// if its a journal JALL (begins with or JKEY^* (contains)  just to a journal keyword search and if there is an issn do an issn search
// if the item has a jtitle its a journal or if 
//if the title has the label Journal Title its a journal, 

if($ctx_obj->jtitle){
	$jtitle.=$ctx_obj->jtitle;
	$voyURL.="searchArg=".$jtitle."&searchCode=JKEY^*&recCount=25&searchType=1&page.search.search.button=Search";
}else if($ctx_obj->labelList[title]=="Journal Title"){
	$jtitle.=$ctx_obj->title;
	$voyURL.="searchArg=".$jtitle."&searchCode=JKEY^*&recCount=25&searchType=1&page.search.search.button=Search";
} else{
// not a journal so do title keyword and if there is one: author 
//TKEY (contains) do title keyword, author, not isbn, 

	if($ctx_obj->btitle){
		$tempTitle = $ctx_obj->btitle;	
	}elseif($ctx_obj->title){
		$tempTitle = $ctx_obj->title;
	} elseif ($ctx_obj->isbn){ //no title, but there is an ISBN? See if you can get a title & author from Worldcat
		$isbn = $ctx_obj->isbn;
		
		$voyURL = useWorldCat($isbn);
		
		//if WorldCat search failed, just use the ISBN as a last-ditch attempt
		if (!$voyURL) {
			$voyURL = 'http://voyager.library.uvic.ca/vwebv/search?searchArg1=' . $isbn . '&argType1=phrase&searchCode1=ISBN&recCount=25&searchType=2&page.search.search.button=Search';
	
		}
		//send either the WorldCat data or the ISBN, depending on what we got back from WorldCat
		header("location: $voyURL");
		exit;
	}
	
	
	//split the title on a colon
	$partsOftempTitle 	= explode(":", $tempTitle);
	// now if the first part contains a ( as in something like (3rd ed.) remove that from the title
	$partsOfTitle	 	= explode("(", $partsOftempTitle[0]);
	$partsOfTitle2 = array();
	if (array_key_exists(1, $partsOftempTitle)) {
		$partsOfTitle2	 	= explode("(", $partsOftempTitle[1]);
	}
	
	$searchTitle = $partsOfTitle[0];
	$searchTitle = trim($searchTitle);
	$searchTitle = rtrim($searchTitle,'.');
	$searchTitle = str_replace('. ', ' ', $searchTitle);
	
	$searchTitle2 = NULL;
	
	if (count($partsOfTitle2) > 0) {
		$searchTitle2 = $partsOfTitle2[0];
		$searchTitle2 = trim($searchTitle2);
		$searchTitle2 = rtrim($searchTitle2,'.');
		$searchTitle2 = str_replace('. ',' ', $searchTitle2);
	}
	
	
	$voyURL.="searchArg1=".$searchTitle."&argType1=phrase&searchCode1=TKEY";//TALL
	
	//track whether we've used the 'combine2' argument; will need to know when we tack on ISBN
	$used2 = FALSE;
	//send the part after the semi-colon as a second title phrase search
	if ($searchTitle2) {
		$voyURL.="&combine2=and&searchArg2=".$searchTitle2."&argType2=phrase&searchCode2=TKEY";//TALL
		$used2 = TRUE;
		
	} elseif($ctx_obj->getAuthors()){
		$authorName = FALSE;
		if (!empty($ctx_obj->aulast)) {
			$authorName = $ctx_obj->aulast;
		} elseif (count($ctx_obj->au) > 0){
			$authorName = $ctx_obj->au[0];
		}
		//can't use author for books if it's chapter metadata. Use the presence of the 'atitle' parameter to determine this
		if($authorName && !isset($ctx_obj->atitle)){
			$voyURL.="&combine2=and&searchArg2=".$authorName."&argType2=all&searchCode2=NKEY";
			$used2 = TRUE;
		} 
	}
	
	//sometimes the ISBN will match when the title won't, so append the ISBN as an OR search
	
	if (!empty($ctx_obj->isbn)) {
		if ($used2) {
			$num = 3;
		} else { 
			$num = 2; 
		}
		
		$voyURL .= '&combine' . $num . '=or&searchArg' . $num . '=' . $ctx_obj->isbn . '&argType' . $num . '=phrase&searchCode' . $num . '=ISBN';
	}
	

	

	$voyURL.="&recCount=25&searchType=2&page.search.search.button=Search";

}

header("Location: $voyURL");


//get Title and Author info from Worldcat if you have an ISBN
function useWorldCat($isbn) {

	 $wskey = ''; //need an authorization key
	
	//construct the query against the basic search API
	$url = 'http://www.worldcat.org/webservices/catalog/search/opensearch?q=' . $isbn . '&count=1&wskey=' . $wskey;

	$data = get_data($url);

	//parse
	$bibData = new SimpleXMLElement($data);
	
	if (isset($bibData->entry[0]->title)) {
		$bookTitle = $bibData->entry[0]->title;
	} else {
		//no title? Useless ...
		return NULL;
	}
	
	
	$authorName = $bibData->entry[0]->author->name;

	$authorBits = explode(',', $authorName);
	$authorLastName = $authorBits[0];

	//oddly, can't count on colons to separate main & subtitle, so need to do title keyword instead
	//fortunately, we know the author name info is reliable

	$voyagerURL = 'http://voyager.library.uvic.ca/vwebv/search?searchArg1=' . $bookTitle . '&argType1=all&searchCode1=TKEY&combine2=and&searchArg2=' . $authorLastName . '&argType2=all&searchCode2=NKEY&recCount=25&searchType=2&page.search.search.button=Search';

	return $voyagerURL;

}

//wrapper around PHP curl, used by the WorldCat function
function get_data($url)
{
  $ch = curl_init();
  $timeout = 5;
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}

?>
