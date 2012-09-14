<?php

/* Code for the special page that allows the usert to update the database tables
 * associated with the index and stop words of the program. Builds the special page
 * and handles the input from the user interface.
 */

class SmartIndexMaintenance extends SpecialPage {
	# private internal values
	private $caseSen, $trim;
	const DEFAULT_TRIM_CHARS = "#.!'[]{}*\n,\"()=;-?†—:“”„";  
	
	
	public function __construct() {
		parent::__construct( 'SmartIndexMaintenance' );
	}
	
	public function execute ($par) {
		global $wgOut, $wgRequest;
		$wgOut->setPageTitle(wfMsg('smartindexmaintenance'));
		$param = $wgRequest->getText('param');

		#update database table of index words
		if ($wgRequest->getCheck('updateindex')) {
			$this->caseSen = $wgRequest->getCheck('case');
			if (!($wgRequest->getCheck('trimchars'))) $this->trim = $wgRequest->getText('trimchars');
			else $this->trim = self::DEFAULT_TRIM_CHARS;
			
			if ($this->createIndexTable()) {
				$this->getWordData();
				$wgOut->addWikiText(wfMsg('smartindex-index-table-updated'));
			} else {
				$wgOut->addWikiText(wfMsg('smartindex-index-table-update-failed'));
			}
		}
		
		#update database table of stop words
		if($wgRequest->getCheck('updatestop')) {
			$stopWordPage = $wgRequest->getText('stopwords');
			if($this->createStopWordTable($stopWordPage)) {
				if ($this->getStopWords($stopWordPage)) {
					$wgOut->addWikiText(wfMsg('smartindex-stop-words-updated'));
				} else {
					$wgOut->addWikiText(wfMsg('smartindex-stop-words-no-page'));
				}
			} else {
				$wgOut->addWikiText(wfMsg('smartindex-stop-words-table-failed'));
			}
		}
		
		#clear database table of stop words
		if ($wgRequest->getCheck('clearstop')) {
			$dbw = &wfGetDB(DB_MASTER);
			if ($dbw->tableExists('smartindex_stop_words')) {
				$dbw->query('drop table smartindex_stop_words');
				$dbw->commit();
			}
			$wgOut->addWikiText(wfMsg('smartindex-stop-words-cleared'));
		}
	
		
		# build the form for updating the database tables for index and stop words
		$updateWordsForm = Xml::openElement( 'form', array( 'method' => 'post',
			'action' => $this->getTitle()->getLocalUrl( 'action=submit' ) ) );
		$updateWordsForm .= Xml::inputLabel(wfMsg('smartindex-trim-chars-label'), 'trimchars', 'trimchars', 40 ) . '&#160;';
		$updateWordsForm .= Xml::submitButton(wfMsg('smartindex-index-words-submit'), array('name' => 'updateindex')) . '<br />';
		$updateWordsForm .= XML::checkLabel(wfMsg('smartindex-case-sensitive'), 'case', 'case', true). '<br />' . '<br />';
		$updateWordsForm .= XML::inputLabel(wfMsg('smartindex-stop-words-label'), 'stopwords', 'stopwords', 40) . '&#160;';
		$updateWordsForm .= XML::submitButton(wfMsg('smartindex-stop-words-submit'), array('name' => 'updatestop')) . '&#160;';
		$updateWordsForm .= XML::submitButton(wfMsg('smartindex-clear-stop-words'), array('name' => 'clearstop')) . '<br />';
		$updateWordsForm .= Xml::closeElement('form');     
		$wgOut->addHTML($updateWordsForm);	
		
	}


	/* Creates the database table for index words. */
	function createIndexTable() {
		$dbw = &wfGetDB(DB_MASTER);
		#drop the table if it already exists
		if ($dbw->tableExists('smartindex_index_words')) {
			$dbw->query('drop table smartindex_index_words');
			$dbw->commit();
		}
		
		#table data
		$tableName = $dbw->tableName('smartindex_index_words');
		$queryStr = <<<QUERYSTR
	create table $tableName (
	token varchar(255) unique not null,
	frequency integer,
	idf float,
	pages varchar(255),
	primary key(token)
	) engine = InnoDB, default charset = binary;
QUERYSTR;
		
		#add table
		if ($dbw->query($queryStr)) {
			$dbw->commit();
			return true;
		} else {
			return false;
		}
	}
	
	
/* Creates the database table for stop words. */
	function createStopWordTable() {
	$dbw = wfGetDB(DB_MASTER);
		#drop the table if it already exists
		if ($dbw->tableExists('smartindex_stop_words')) {
			$dbw->query('drop table smartindex_stop_words');
			$dbw->commit();
		}
		
		#table data
		$tableName = $dbw->tableName('smartindex_stop_words');
		$queryStr = <<<QUERYSTR
	create table $tableName (
	word varchar(255) unique not null
	) engine = InnoDB, default charset = binary;
QUERYSTR;
		
		#add table
		if ($dbw->query($queryStr)) {
			$dbw->commit();
			return true;
		} else {
			return false;
		}
	}
	
	
	/* Builds the database table of stop words from the words listed on $listPage */
	function getStopWords($listPage) {
		#get database and join page title to text
		$dbr = &wfGetDB(DB_SLAVE);
		$res = $dbr->select(array ('page', 'revision', 'text'),
                	array('page_title','old_text'),
               	 	array('page_latest = rev_id', 'rev_text_id = old_id', 'page_namespace = 0'),
                 	'',
                	array('ORDER BY' => 'page_id'));
    
    	#get the text from the page containing the stop words
   		$row = $dbr->fetchObject($res);
    	for ($i = 0; $i < $dbr->numRows($res); ++$i) {
    		if ($row->page_title == $listPage) {
    			$wordText = $row->old_text;
    			break;
    		}
    		$row = $dbr->fetchObject($res);
   		}
    	if ($i == $dbr->numRows($res)) return false;
    	$dbr->freeResult($res);
    	
    	#build stop word list from page
    	$wordText = $row->old_text;
    	$stopWord = strtok($wordText, "\n");
    	$dbw = &wfGetDB(DB_MASTER);
    	while ($stopWord !== false) {
    		$databaseEntry = array('word' => $stopWord);
    		$dbw->insert('smartindex_stop_words', $databaseEntry);
    		$stopWord = strtok("\n");
    	}
    	return true;
	}
	
	
	/* Takes the text form each page and adds the associated information to the 
	 * database.
	 */
	function getWordData() {
		#get database and join page title to text
		$dbr = &wfGetDB(DB_SLAVE);
		$res = $dbr->select(array ('page', 'revision', 'text'),
          	      	array('page_title','old_text'),
            	    array('page_latest = rev_id', 'rev_text_id = old_id', 'page_namespace = 0'),
               	 	'',
               	 	array('ORDER BY' => 'page_id'));
    
   	 	#get word data from each page
    	$pages = $dbr->numRows($res);
    	$wordData = array();
    	for ($i = 0; $i < $pages; $i++) {
    		$row = $dbr->fetchObject($res);
			$title = $row->page_title; 
			$this->getPageData($wordData, $title, $row->old_text);
		}
		$dbr->freeResult($res);
		$this->addToDatabase($wordData, $pages);
 	}
 	
 	
 	/* Goes through the text of a Wiki page character by character to 
 	 * extract all of the words.
 	 */
 	function getPageData(&$wordData, $page, $text) {
    	$textPos = 0;
    	$currChunk = '';
    
    	while (true) {
    		$currChar = $text[$textPos];
    		if (!ctype_space($currChar) and !$this->isSpecialStart($currChar)
    			and $textPos < strlen($text)) {
    			$currChunk .= $currChar;
    		
    		# $currChar indicates a word boundary; $currChunk now forms a word and the
    		# list of words is updated
    		} else {
    			if (preg_match('#[A-Za-z]#', $currChunk)) {
    				$newWord = trim($currChunk, $this->trim);
    				if (!$this->caseSen) {
    					$newWord = mb_strtoupper(mb_substr($newWord, 0, 1)) . mb_substr($newWord, 1);
    				}
    				if(array_key_exists($newWord, $wordData)) {
    					$wordData[$newWord]['count'] += 1;
    					if (!in_array($page, $wordData[$newWord]['pages'])) {
    						$wordData[$newWord]['pages'] [] = $page;
    					}
    				} else {
    					$wordData[$newWord]['pages'] [] = $page;
    					$wordData[$newWord]['count'] = 1;
    				}				
    			}
    
    			# break at the end of the document.
    			if ($textPos >= strlen($text)) break;
    			
    			# handle special cases accordingly.
				if ($this->isSpecialStart($currChar)) {
    				$specialCaseEnd = $this->findLength(substr($text, $textPos));
    				$textPos = $textPos + $specialCaseEnd - 1;
    			}
    			$currChunk = '';
    		}
    			++$textPos;
    	}
	}
	
	
	/* Returns true when a character marks the beginning of 
	 * a template, reference, etc.
	 */
	function isSpecialStart($char) {
		return ($char == '[' or $char == '{' or $char == '<');

	}
	
	
	/* Returns the length of a special element, such as a template or reference.*/
	function findLength($special) {
		$len = 1;
		$brackets = 1;
		for ($i = 1; $i < strlen($special); $i++) {
			if ($brackets == 0 ) break;
			$char = $special[$i];
			if ($char == '[' or $char == '{' or $char == '<') ++$brackets;
			else if ($char == ']' or $char == '}' or $char == '>') --$brackets;
			++$len;
		}
		if ($special[0] == '[') {
			$char = $special[$len];
			while (!ctype_space($char) and !($this->isSpecialStart($char)) and $len < strlen($special)) {
				++$len;
				$char = $special[$len];
			}
		}
		return $len;
	}
	
	
	/* Builds the arrays representing the database entry for each index word
	 * and adds these entries to the database table.
	 */
	function addToDatabase($wordData, $numPages) {
		$dbw = wfGetDB(DB_MASTER);
		$words = array_keys($wordData);
		foreach($words as $word) {
			$wordInfo = array();
			$wordInfo['token'] = $word;
			$wordInfo['frequency'] = $wordData[$word]['count'];
			
			$pages = '';
			foreach ($wordData[$word]['pages'] as $page) $pages .= $page . ' ';
			$wordInfo['pages'] = $pages;
			
			$containingPages = count($wordData[$word]['pages']);
			$proportion = (float) $numPages / (float) $containingPages;
			$idf = log($proportion, 2);
			$wordInfo['idf'] = $idf;
			
			$dbw->insert('smartindex_index_words', $wordInfo);
		}
		$dbw->commit;
	}
};
	
?>