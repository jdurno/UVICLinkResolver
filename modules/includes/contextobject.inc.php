<?php
	/* parse an OpenURL expressed in KEV format */
	/* curently covers formats: journal, book, dissertation, patent, and dublin core (dc) */
	/* Assume supplied in $_SERVER['QUERY_STRING']; */
	/* Reference: http://alcme.oclc.org/openurl/servlet/OAIHandler?verb=ListRecords&metadataPrefix=oai_dc&set=Core:Metadata+Formats */
	/* NISO Registry for the OpenURL Framework ANSI/NISO Z39.88-2004 */
	/* Also, for version 0.1: http://alcme.oclc.org/openurl/docs/pdf/openurl-01.pdf */
	/* Last modified: 2011-10-05 J. Durno */
	/* Last modified: 2012-01-23 J. BSheaff */

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
	
	

class ContextObject
{

	private $cto_params; //internal array representation of OpenURL parameters
	public $query_string; //OpenURL expressed as an unmodified query string
	public $fieldList = array(); //All the available fields supplied in the OpenURL
	public $labelList = array(); //Human-readable labels for all parameters listed below
	public $adminFieldList = array(); //list of all admin fields supplied in the OpenURL
	public $authorFieldList = array(); //list of all author-related fields supplied in the OpenURL
	
	//administrative params
	public $ctx_ver;
	public $ctx_enc;
	public $rfr_id;
	public $rft_id = array();
	public $fmt;
	
	//params crossing multiple content types	
	public $aulast;
	public $aufirst;
	public $auinit;
	public $auinit1;
	public $auinitm;
	public $ausuffix;
	public $au = array(); //may contain multiple values, so returns an array
	public $aucorp;
	public $title;
	public $atitle;	
	public $genre; 
	public $spage;
	public $epage;
	public $pages;
	public $issn;
	public $date;
	public $externalDBID;
	public $externalDocID;

	
	//book-specific params
	public $btitle;
	public $place;
	public $pub;	
	public $edition;
	public $tpages;
	public $series;
	public $isbn;
	
	
	//journal-specific params
	public $jtitle;
	public $stitle;
	public $chron;
	public $ssn;
	public $quarter;
	public $volume;
	public $part;
	public $issue;
	public $artnum;
	public $eissn;
	public $coden;
	public $sici;
	
	//dissertation-specific params
	public $co;
	public $cc;
	public $inst;
	public $advisor;
	public $degree;
	
	//patent-specfic params
	public $inventor = array();
	public $invlast;
	public $invfirst;
	public $kind;
	public $applcc;
	public $applnumber;
	public $number;
	public $applyear;
	public $appldate;
	public $assignee;
	public $pubdate;
	public $prioritydate;
	
		
	
	//dublin core specific params
	//all of these are arrays, since they may contain multiple values
	public $creator;
	public $subject;
	public $description;
	public $publisher;
	public $contributor;
	public $type;
	public $format;
	public $identifier;
	public $source;
	public $language;
	public $relation;
	public $coverage;
	public $rights;
	
	
	// the constructor creates an array of values based on the OpenURL query string in the form of $key -> {array}
	// then parses those values into their component elements
	public function buildFromQueryString($qstring = NULL){
		//echo"\n<!-- buildFromQueryString $qstring -->";
		if ($qstring) {
			$this->query_string = $qstring;	
		} else {
			$this->query_string = $_SERVER['QUERY_STRING'];
		}
		
		$query = explode('&', $this->query_string);
		
		$this->cto_params = array();

		foreach( $query as $param ){
		  list($name, $value) = explode('=', $param);
		  
		  $name = urldecode($name);  
		  
		  $name = str_replace ('rft.', '', $name);
		  
		  $this->cto_params[$name][] = urldecode($value);
		}	
	
	
	
	}
	public function __construct($qstring = NULL) {
			
		$this->buildFromQueryString($qstring);	
		
		// added by ben to deal with google scholar not sending a title for a journal
		// first make sure that there is at least one title?  except for article title
		if( !(array_key_exists('title', $this->cto_params)|| 
			array_key_exists('btitle', $this->cto_params)|| 
			array_key_exists('jtitle', $this->cto_params)|| 
			array_key_exists('stitle', $this->cto_params) )){
			// since there was not title see if there is a doi we can look it up with
			$id="not set";
			if (array_key_exists('id', $this->cto_params)) $id = $this->cto_params['id'][0];	
			$pieces = explode(":", $id);
			if($pieces[0]=="doi"){ 
				$doi = $pieces[1];
				include "../includes/crossref.inc.php";
				echo "\n<!-- no title exits but doi $doi -->\n";
				$xref = new crossref($doi);
				$openUrl = $xref->getCrossRefOpenUrl();
				echo "\n<!-- no title exits but openUrl $openUrl -->\n";
				$this->buildFromQueryString($openUrl);
			}else{
				echo "\n<!-- no title exits but also no doi $doi -->\n";
			}
		}
		// end of added by ben to deal with google scholar ...
		
		/* Administrative metadata */
		
		//OpenURL Version
		if (array_key_exists('ctx_ver', $this->cto_params)) {	
			$this->ctx_ver = $this->cto_params['ctx_ver'][0];
			$this->fieldList[] = 'ctx_ver';
			$this->adminFieldList[] = 'ctx_ver';
		}		
		
		//Referer ID
		if (array_key_exists('rfr_id', $this->cto_params)) {	
			$this->rfr_id = $this->cto_params['rfr_id'][0];
			$this->fieldList[] = 'rfr_id';
			$this->adminFieldList[] = 'rfr_id';
		}	

		//Encoding
		if (array_key_exists('ctx_enc', $this->cto_params)) {	
			$this->ctx_enc = $this->cto_params['ctx_enc'][0];
			$this->fieldList[] = 'ctx_enc';
			$this->adminFieldList[] = 'ctx_enc';
		}	

		//Referent ID
		//can be multiple, so returns array
		if (array_key_exists('rft_id', $this->cto_params)) {	
			$this->rft_id = $this->cto_params['rft_id'];
			$this->fieldList[] = 'rft_id';
			$this->adminFieldList[] = 'rft_id';
		}			
		
	
		//Format: book, journal, dissertation,dc
		//looks like info:ofi/fmt:kev:mtx:journal, but we only care about the last bit
		if (array_key_exists('rft_val_fmt', $this->cto_params)) {	
			$fmt = $this->cto_params['rft_val_fmt'][0];
			list ($junk, $junk, $junk, $junk, $fmt) = explode(':', $fmt);
			$this->fmt = $fmt;
			$this->fieldList[] = 'fmt';
		} elseif (array_key_exists('fmt', $this->cto_params)) {
			$this->fmt = $this->cto_params['fmt'][0];
			$this->fieldList[] = 'fmt';
		}

		//external Database Identifier
		if (array_key_exists('externalDBID', $this->cto_params)) {	
			$this->externalDBID = $this->cto_params['externalDBID'][0];
			$this->fieldList[] ='externalDBID';
			$this->adminFieldList[] = 'externalDBID';
		}

		//external Document Identifier
		if (array_key_exists('externalDocID', $this->cto_params)) {	
			$this->externalDocID = $this->cto_params['externalDocID'][0];
			$this->fieldList[] ='externalDocID';
			$this->adminFieldList[] = 'externalDocID';
		}
		

		
		/* parse metadata crossing multiple content types */
		
		//first author's family name
		//Applies to: Book, Journal
		if (array_key_exists('aulast', $this->cto_params)) {
			$nameparts= explode(",", $this->cto_params['aulast'][0]);
			$this->aulast = $nameparts[0];			
			$this->fieldList[] ='aulast';
			$this->authorFieldList[] = 'aulast';
			
		}
		
		//first author's given name or names or initials
		//Applies to: Book, Journal
		if (array_key_exists('aufirst', $this->cto_params)) {	
			$this->aufirst = $this->cto_params['aufirst'][0];
			$this->fieldList[] ='aufirst';
			$this->authorFieldList[] = 'aufirst';
		}	
		
		//First author's first and middle initials
		//Applies to: Book, Journal
		if (array_key_exists('auinit', $this->cto_params)) {	
			$this->auinit = $this->cto_params['auinit'][0];
			$this->fieldList[] ='auinit';
			$this->authorFieldList[] = 'auinit';
		}	
	
		//first author's first initial
		//Applies to: Book, Journal
		if (array_key_exists('auinit1', $this->cto_params)) {	
			$this->auinit1 = $this->cto_params['auinit1'][0];
			$this->fieldList[] ='auinit1';
			$this->authorFieldList[] = 'auinit1';
		}
	
		//first author's middle initial
		//Applies to: Book, Journal
		if (array_key_exists('auinitm', $this->cto_params)) {	
			$this->auinitm = $this->cto_params['auinitm'][0];
			$this->fieldList[] ='auinitm';
			$this->authorFieldList[] = 'auinitm';
		}
	
		//First author's name suffix
		//Applies to: Book, Journal
		if (array_key_exists('ausuffix', $this->cto_params)) {	
			$this->ausuffix = $this->cto_params['ausuffix'][0];
			$this->fieldList[] ='ausuffix';
			$this->authorFieldList[] = 'ausuffix';
		}
	
		//The full name of a single author - note, may contain multiple values! (array)
		//Applies to: Book, Journal
		if (array_key_exists('au', $this->cto_params) ){	
			$this->au = $this->cto_params['au'];
			$this->fieldList[] ='au';
			$this->authorFieldList[] = 'au';
		}
	
		//Corporate author
		//Applies to: Book, Journal
		if (array_key_exists('aucorp', $this->cto_params) ){	
			$this->aucorp = $this->cto_params['aucorp'][0];
			$this->fieldList[] ='aucorp';
			$this->authorFieldList[] = 'aucorp';
		}
	

		//Chapter title (Book) or Article Title (Journal)
		//Applies to: Book, Journal, Dublin Core
		//Note: Dublin Core allows Multiple titles, but we're only going to use the first one here
		if (array_key_exists('atitle', $this->cto_params) ){	
			$this->atitle = $this->cto_params['atitle'][0];
			$this->fieldList[] ='atitle';
		}

		//Book title (Book) or Journal Title (Journal). Alternate forms btitle/jtitle preferred
		//Applies to: Book, Journal
		if (array_key_exists('title', $this->cto_params)) {	
			$this->title = $this->cto_params['title'][0];
			$this->fieldList[] ='title';
			$titleFound=true;
		}


		//Date of publication
		//Applies to: Book, Journal
		if (array_key_exists('date', $this->cto_params)) {	
			$this->date = $this->cto_params['date'][0];
			$this->fieldList[] ='date';
		}	

		
		//Start page
		//Applies to: Book, Journal
		if (array_key_exists('spage', $this->cto_params)) {	
			$this->spage = $this->cto_params['spage'][0];
			$this->fieldList[] ='spage';
		}	

		
		//Ending page
		//Applies to: Book, Journal
		if (array_key_exists('epage', $this->cto_params)) {	
			$this->epage = $this->cto_params['epage'][0];
			$this->fieldList[] ='epage';
		}	

		
		//Start and end pages expressed as a range
		//Applies to: Book, Journal
		if (array_key_exists('pages', $this->cto_params)) {	
			$this->pages = $this->cto_params['pages'][0];
			$this->fieldList[] ='pages';
		}	

		
		//ISSN
		//Applies to: Book, Journal
		if (array_key_exists('issn', $this->cto_params)) {	
			$this->issn = $this->cto_params['issn'][0];
			$this->fieldList[] ='issn';
		}	

		
		//Genre
		//For Book can be: book, bookitem, conference, proceeding, report, document, unknown
		//For Journal can be: journal, issue, article, proceeding, conference, preprint, unknown
		//Applies to: Book, Journal
		if (array_key_exists('genre', $this->cto_params)) {	
			$this->genre = $this->cto_params['genre'][0];
			$this->fieldList[] ='genre';
		}	

		
		
		
		/* parse book-specific metadata */
		
		//Book title
		if (array_key_exists('btitle', $this->cto_params)) {	
			$this->btitle = $this->cto_params['btitle'][0];	
			$this->fieldList[] ='btitle';
			$titleFound=true;
		}	

		
		//Place of publication
		if (array_key_exists('place', $this->cto_params)) {	
			$this->place = $this->cto_params['place'][0];
			$this->fieldList[] ='place';
		}		

		
		//Publisher
		if (array_key_exists('pub', $this->cto_params) ){	
			$this->pub = $this->cto_params['pub'][0];
			$this->fieldList[] ='pub';
		}	
				
		//Title of series
		if (array_key_exists('series', $this->cto_params)) {	
			$this->series = $this->cto_params['series'][0];
			$this->fieldList[] ='series';
		}	

		//Edition
		if (array_key_exists('edition', $this->cto_params)) {	
			$this->edition = $this->cto_params['edition'][0];
			$this->fieldList[] ='edition';
		}	
		
		//Total pages
		if (array_key_exists('tpages', $this->cto_params)) {	
			$this->tpages = $this->cto_params['tpages'][0];	
			$this->fieldList[] ='tpages';		
		}	
		
		
		//ISBN
		if (array_key_exists('isbn', $this->cto_params)) {	
			$this->isbn = $this->cto_params['isbn'][0];
			$this->fieldList[] ='isbn';
		}		


		
		/* parse journal-specific metadata */

		
		//Journal title (preferred over 'title' field)
		if (array_key_exists('jtitle', $this->cto_params)) {	
			$this->jtitle = $this->cto_params['jtitle'][0];	
			$this->fieldList[] ='jtitle';
			$titleFound=true;
		}
	
		
		//Abbreviated or short journal title
		if (array_key_exists('stitle', $this->cto_params)) {	
			$this->stitle = $this->cto_params['stitle'][0];	
			$this->fieldList[] ='stitle';
			$titleFound=true;
		}
	
		
		//non-normalized chronology eg. "1st quarter"
		if (array_key_exists('chron', $this->cto_params)) {	
			$this->chron = $this->cto_params['chron'][0];	
			$this->fieldList[] = 'chron';
		}
	
		
		//Season (chronology). allowed values: spring, summer, fall, winter
		if (array_key_exists('ssn', $this->cto_params)) {	
			$this->ssn = $this->cto_params['ssn'][0];
			$this->fieldList[] = 'ssn';
		}
	
		
		//Quarter (chronology). allowed values: 1,2,3,4
		if (array_key_exists('quarter', $this->cto_params)) {	
			$this->quarter = $this->cto_params['quarter'][0];
			$this->fieldList[] = 'quarter';
		}
	
		
		//Volume
		if (array_key_exists('volume', $this->cto_params)) {	
			$this->volume = $this->cto_params['volume'][0];	
			$this->fieldList[] = 'volume';
		}
	
		
		//subdivision of volume, or highest level division
		if (array_key_exists('part', $this->cto_params)) {	
			$this->part = $this->cto_params['part'][0];
			$this->fieldList[] = 'part';
		}
	
		
		//Issue
		if (array_key_exists('issue', $this->cto_params)) {	
			$this->issue = $this->cto_params['issue'][0];
			$this->fieldList[] = 'issue';
		}
	
		
		//Article number assigned by publisher. May be a URL
		if (array_key_exists('artnum', $this->cto_params)) {	
			$this->artnum = $this->cto_params['artnum'][0];
			$this->fieldList[] = 'artnum';
		}
	
		
		//ISSN for the electronic version of the journal
		if (array_key_exists('eissn', $this->cto_params)) {	
			$this->eissn = $this->cto_params['eissn'][0];
			$this->fieldList[] = 'eissn';
		}
	
		
		//CODEN
		if (array_key_exists('coden', $this->cto_params)) {	
			$this->coden = $this->cto_params['coden'][0];
			$this->fieldList[] = 'coden';
		}
	
		
		//Serial Item and Contribution identifier
		if (array_key_exists('sici', $this->cto_params)) {	
			$this->sici = $this->cto_params['sici'][0];
			$this->fieldList[] = 'sici';
		}
	
		
		/*parse dissertation-specific metadata */
		
		//Country of publication
		if (array_key_exists('co', $this->cto_params)) {	
			$this->co = $this->cto_params['co'][0];
			$this->fieldList[] = 'co';
		}
		
		//Country of publication code in ISO-2 format
		if (array_key_exists('cc', $this->cto_params)) {	
			$this->cc = $this->cto_params['cc'][0];
			$this->fieldList[] = 'cc';
		}
		
		//Institution that issued the dissertation
		if (array_key_exists('inst', $this->cto_params)) {	
			$this->inst = $this->cto_params['inst'][0];
			$this->fieldList[] = 'inst';
		}
		
		//Dissertation advisor
		if (array_key_exists('advisor', $this->cto_params)) {	
			$this->advisor = $this->cto_params['advisor'][0];
			$this->fieldList[] = 'advisor';
		}
		
		//Degree conferred
		if (array_key_exists('degree', $this->cto_params)) {	
			$this->degree = $this->cto_params['degree'][0];	
			$this->fieldList[] = 'degree';
		}
		
		
		
		
		/* parse dublin core-specific metadata */
		/* Multiple values allowed for all of these, so returns arrays */

		
		if (array_key_exists('creator', $this->cto_params)) {	
			$this->creator = $this->cto_params['creator'];	
			$this->fieldList[] = 'creator';
			$this->authorFieldList[] = 'creator';
		}
		
		if (array_key_exists('subject', $this->cto_params)) {	
			$this->subject = $this->cto_params['subject'];
			$this->fieldList[] = 'subject';
		}
		
		if (array_key_exists('description', $this->cto_params)) {	
			$this->description = $this->cto_params['description'];
			$this->fieldList[] = 'description';
		}
		
		if (array_key_exists('publisher', $this->cto_params)) {	
			$this->publisher = $this->cto_params['publisher'];
			$this->fieldList[] = 'publisher';
		}
		
		if (array_key_exists('contributor', $this->cto_params)) {	
			$this->contributor = $this->cto_params['contributor'];
			$this->fieldList[] = 'contributor';
		}
		
		if (array_key_exists('type', $this->cto_params)) {	
			$this->type = $this->cto_params['type'];
			$this->fieldList[] = 'type';
		}
		
		if (array_key_exists('format', $this->cto_params)) {	
			$this->format = $this->cto_params['format'];
			$this->fieldList[] = 'format';
		}
		
		if (array_key_exists('identifier', $this->cto_params)) {	
			$this->identifier = $this->cto_params['identifier'];
			$this->fieldList[] = 'identifier';
		}
		
		if (array_key_exists('source', $this->cto_params)) {	
			$this->source = $this->cto_params['source'];
			$this->fieldList[] ='source';
		}
		
		if (array_key_exists('language', $this->cto_params)) {	
			$this->language = $this->cto_params['language'];
			$this->fieldList[] ='language';
		}
		
		
		if (array_key_exists('relation', $this->cto_params)) {	
			$this->relation = $this->cto_params['relation'];
			$this->fieldList[] ='relation';
		}
		
		
		if (array_key_exists('coverage', $this->cto_params)) {	
			$this->coverage = $this->cto_params['coverage'];
			$this->fieldList[] ='coverage';
		}
		
		
		if (array_key_exists('rights', $this->cto_params)) {	
			$this->rights = $this->cto_params['rights'];
			$this->fieldList[] ='rights';
		}
		
		
		/* patent-specific metadata */
		
		//Inventors names, may contain multiple values, returns array
		if (array_key_exists('inventor', $this->cto_params)) {	
			$this->inventor = $this->cto_params['inventor'];
			$this->fieldList[] ='inventor';
		}
		
		//Inventor last name
		if (array_key_exists('invlast', $this->cto_params)) {	
			$this->invfirst = $this->cto_params['invlast'][0];	
			$this->fieldList[] = 'invlast';
		}
		
		//Inventor first name
		if (array_key_exists('invfirst', $this->cto_params)) {	
			$this->invfirst = $this->cto_params['invfirst'][0];	
			$this->fieldList[] = 'invfirst';
		}
		
		//Patent kind code
		if (array_key_exists('kind', $this->cto_params)) {	
			$this->kind = $this->cto_params['kind'][0];	
			$this->fieldList[] = 'kind';
		}
		
		//Application Country code
		if (array_key_exists('applcc', $this->cto_params)) {	
			$this->applcc = $this->cto_params['applcc'][0];	
			$this->fieldList[] = 'applcc';
		}
		
		//Application number
		if (array_key_exists('applnumber', $this->cto_params)) {	
			$this->applnumber = $this->cto_params[''][0];	
			$this->fieldList[] = 'applnumber';
		}
		
		//Patent number
		if (array_key_exists('number', $this->cto_params)) {	
			$this->number = $this->cto_params['number'][0];	
			$this->fieldList[] = 'number';
		}
		
		//Application year
		if (array_key_exists('applyear', $this->cto_params)) {	
			$this->applyear = $this->cto_params['applyear'][0];	
			$this->fieldList[] = 'applyear';
		}
		
		//Application Date
		if (array_key_exists('appldate', $this->cto_params)) {	
			$this->appldate = $this->cto_params['appldate'][0];	
			$this->fieldList[] = 'appldate';
		}
		
		//Patent assignee
		if (array_key_exists('assignee', $this->cto_params)) {	
			$this->assignee = $this->cto_params['assignee'][0];	
			$this->fieldList[] = 'assignee';
		}
		
		//Patent publication date
		if (array_key_exists('pubdate', $this->cto_params)) {	
			$this->pubdate = $this->cto_params['pubdate'][0];	
			$this->fieldList[] = 'pubdate';
		}
		
		//Priority application date
		if (array_key_exists('prioritydate', $this->cto_params)) {	
			$this->prioritydate = $this->cto_params['prioritydate'][0];	
			$this->fieldList[] = 'prioritydate';
		}
		
		
		
		/* contstruct an array with human-readable labels for the above fields */
		/* order should reflect the order the params are to display in the human readable output */
		
		$this->labelList = array (
			
			//administrative params
			'ctx_ver' => 'OpenURL Version',
			'ctx_enc' => 'OpenURL Encoding',
			'rfr_id' => 'OpenURL Source',
			'rft_id' => 'Referent Identifier',	
			'externalDBID' => 'External Database ID',
			'externalDocID' => 'External Document ID',
			
			//preliminary
			'fmt' => 'Format',
			'genre' => 'Genre', 

			//article or chapter level params
			'atitle' => 'Article or Chapter Title',	
			
			//author params
			'au' => 'Author(s)',
			'aulast' => 'Author Last Name',

			

			//params relating to containing work (book or journal)
			'title' => 'Title',
			
			
			'btitle' => 'Book Title',
			'jtitle' => 'Journal Title', 
			
			'date' => 'Date',
			'spage' => 'Start page',
			'epage' => 'End page',
			'pages' => 'Pages',		
			

			'edition' => 'Edition',
			'tpages' => 'Total pages',
			'series' => 'Series',
			
			  
			'stitle' => 'Short Title',
			'chron' => 'Chronology',
			'ssn' => 'Season',
			'quarter' => 'Quarter',
			'volume' => 'Volume',
			'part' => 'Part',
			'issue' => 'Issue',
			'isbn' => 'ISBN',
			'issn' => 'ISSN',
			
			
			'artnum' => 'Article Number',
			'eissn' => 'eISSN',
			'coden' => 'CODEN',
			'sici' => 'SICI',
			
			'place' => 'Place of Publication',
			'pub' => 'Publisher',	
			
			
			
			//dissertation-specific params
			'co' => 'Country of Publication',
			'cc' => 'Country of Publication Code',
			'inst' => 'Issuing Institution',
			'advisor' => 'Advisor',
			'degree' => 'Degree',
			
			//patent-specfic params
			'inventor' => 'Inventor(s)',
			'invlast' => 'Inventor Last Name',
			'invfirst' => 'Inventor First Name',
			'kind' => 'Patent Kind Code',
			'applcc' => 'Application Country Code',
			'applnumber' => 'Application Number',
			'number' => 'Patent Number',
			'applyear' => 'Application Year',
			'appldate' => 'Application Date',
			'assignee' => 'Assignee',
			'pubdate' => 'Publication Date',
			'prioritydate' => 'Priority Application Date',
			
			
			
			//dublin core specific params
			//all of these are arrays, since they may contain multiple values
			'subject' => 'Subject(s)',
			'description' => 'Description',
			'publisher' => 'Publisher(s)',
			'contributor' => 'Contributor(s)',
			'type' => 'Type(s)',
			'format' => 'Format(s)',
			'identifier' => 'Identifier(s)',
			'source' => 'Source(s)',
			'language' => 'Language(s)',
			'relation' => 'Relation(s)',
			'coverage' => 'Coverage',
			'rights' => 'Rights'
			
		
		);
		
		$this->fineTuneLabels();
	
	}

	/* Utility methods */
	
	public function getBookTitle() {
		
		if (!empty($this->btitle)) {
			
			return $this->btitle;	
			
		} elseif (!empty ($this->title)) {
			
			return $this->title;
			
		} else {
			
			return NULL;
			
		}	
		
	}
	

	public function getJournalTitle() {
		
		if (!empty($this->jtitle)) {
			
			return $this->jtitle;	
			
		} elseif (!empty ($this->title)) {
			
			return $this->title;
			
		} else {
			
			return NULL;
			
		}	
		
	}
	
	public function getSN() {
		
		if (!empty($this->issn)) {
			
			return $this->issn;	
			
		} elseif (!empty($this->isbn)) {
			
			return $this->isbn;	
			
		} else {
			
			return NULL;	
			
		}
		
		
	}
	
	
	
	public function getFormat() {
		
		$allowableFormats = array('book','journal','dc','dissertation', 'patent');
		
		//note both Genres include the additional types 'proceeding' and 'unknown', not useful for differentiating Format types
		$bookGenres = array('book','bookitem','conference','report','document');
		$journalGenres = array('journal','issue','article','conference','preprint');
		
		//if item has identified its format correctly, simply return that value
		if (!empty($this->fmt)) {
			
			if (in_array($this->fmt, $allowableFormats)) {
				
				return $this->fmt;	
			
				
			//sometimes OpenURLs contain genres in the format field. I don't think it's legal, but let's allow for it anyhow	
			} elseif (in_array($this->fmt, $bookGenres)) {
				
				return 'book';
				
			} elseif (in_array($this->fmt, $journalGenres)) {
				
				return 'journal';
				
			}
					
		} 
		
		//if no format identified, and there is a genre, attempt to guess format from genre
		if (!empty($this->genre)) {
			
			if (in_array($this->genre, $bookGenres)) {
				
				return 'book';
				
			} elseif (in_array($this->genre, $journalGenres)) {
				
				return 'journal';
				
			}
		
		//if no format and no genre identified, use other fields to attempt to determine the format
		} 
		
		if (!empty($this->jtitle)) {
			
			return 'journal';	
			
		} 
		
		if (!empty( $this->btitle)) {
				
			return 'book';
			
		} 
		
		
		//if all the above fails, return whatever was in $this-fmt to begin with, or else NULL if no value.
		if (!empty($this->fmt)) {
			
			return $this->fmt;
			
		} else {
		
			return NULL;
		
		}
		
			
		
	}
	
	
	//return all authors as a single semi-colon delimited string
	public function getAuthors () {
	
		/* Whole bunch of author-related junk we need to parse through:
			$this->aulast [first author's family name]
			$this->aufirst [first author's given names or intials]
			$this->auinit [first author's first and middle initials]
			$this->auinit1 [first author's first inital]
			$this->auinitm [first author's middle initial]
			$this->ausuffix [first author's name suffix]
			$this->au [full names of authors as individual members of an array]
			$this->aucorp [corporate author]
			$this->creator [dc designation for creator]
		
		*/
		
		
		$authors = NULL;

		
		//first, see if we need to assemble the name of the author from various possible components
		//useless if last name not supplied ...
		if (!empty ($this->aulast)) {
			
			$authors .= $this->aulast;

			if (!empty($this->ausuffix)){
				$authors .= $this->ausuffix;	
			}
			
			$authors .= ', ';
			
			if (!empty($this->aufirst)) {
				$authors .= $this->aufirst . ' ';	
			} elseif (!empty($this->auinit)){
				$authors .= $this->aufirst  . ' ';	
			} elseif (!empty($this->auinit)){
				$authors .= $this->auinit . ' ';
			} elseif ((!empty($this->auinit1))) {
				$authors .= $this->auinit1 . ' ';
				if (!empty($this->auinitm)) {
					$authors .= $this->auinitm . ' ';	
				}	
			}
			
					
		}
		
		
		//now check au array
		if (count($this->au) > 0) {
			
			if (!empty($authors)) {
				$authors .= ' ; ';	
				
			}
			
			for ($i = 0; $i < count($this->au); $i++) {
				$authors .= $this->au[$i];	
				
				if ($i < (count($this->au) - 1)) {
					$authors .= ' ; ';	
				}
				
			}
			
		}

		//this should only apply to dc format, but some vendors like to mix & match format types, apparently
		if (count($this->creator) > 0) {
			
			if (!empty($authors)) {
				$authors .= ' ; ';	
				
			}
			
			for ($i = 0; $i < count($this->creator); $i++) {
				$authors .= $this->creator[$i];	
				
				if ($i < (count($this->creator) - 1)) {
					$authors .= ' ; ';	
				}
				
			}
			
		}


		//throw in the corporate author at the end ...
		if (!empty($this->aucorp)){
			if (!empty($authors)){
				$authors .= ' ; ';					
			}
			
			$authors .= $this->aucorp;
			
			
		}
		
		
		return $authors;
		
	}
	
	
	public function formatArrayAsString($array) {
		
		if (!is_array($array)) {
			return 'function formatArrayAsString cannot proceed: Supplied value is not an array';	
		}

		$string = '';
		
		if (count($array) > 0) {		
			
			for ($i = 0; $i < count($array); $i++) {
				$string .= $array[$i];	
				
				if ($i < (count($array) - 1)) {
					$string .= ' ; ';	
				}
				
			}
			
		}
		
		return $string;
		
		
	}
	
	
	private function fineTuneLabels() {
		
		$format = $this->getFormat();
		
		if ($format == 'book') {
			$this->labelList['atitle'] = "Chapter Title";
			$this->labelList['title'] = "Book Title";
			
		} elseif ($format == 'journal') {
			$this->labelList['atitle'] = "Article Title";
			$this->labelList['title'] = "Journal Title";
			
		}
		
		
	}
	
	
	
	
}




?>
