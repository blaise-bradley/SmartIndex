<?php

/* Main function. */
function SmartIndex($input, $args, Parser $parser, PPFrame $frame) {
	#determine user options from input
	$options = array('scoreMode' => 'default',  'IDFCutoff' => NULL, 'displayMode' => 'list',  
					'template' => NULL, 'scoreFilter' => false, 'freqCutoff' => NULL);
	parseParams($input, $options);
	
	
	#get database and join page title to text
	$dbr = &wfGetDB(DB_SLAVE);
	if ($dbr->tableExists('smartindex_index_words')) {
		$res = $dbr->select('smartindex_index_words', array('token', 'frequency', 'idf', 'pages'), 
						'', '', array("ORDER BY token COLLATE 'latin1_general_ci'"));
						
	$stopWords = retrieveStopWords($dbr);
						
    	#Display words
    	if ($options['displayMode'] == 'table') $display = 
    											displayWordsTable($dbr, $res, $options, $stopWords);
    	
    	else $display = displayWordsList($dbr, $res, $options, $stopWords);
   		return $parser->recursiveTagParse($display, $frame);
   	}
   	else return wfMsg('smartindex-no-index-table');
}


/* Extracts user options from the input text. */
function parseParams($paramText, &$options) {
	$paramValPair = strtok($paramText, " \n");
	while (!$paramValPair == false) {
		#get new parameter  values
		$equals = strpos($paramValPair, '=');
		$param = substr($paramValPair, 0, $equals);
		$value = substr($paramValPair, $equals + 1);
		
		#convert value to correct type and update options array
		if ($value == 'true') $value = true;
		else if ($value == 'false') $value = false;
		else if (is_int($value)) $value = (int) $value;
		else if (is_float($value)) $value = (float) $value;
		$options[$param] = $value;
		$paramValPair = strtok(" \n");
	}
}


/* Returns an array of stop words retrieved from the database. */
function retrieveStopWords($dbr) {
	$stopWords = array();
	if ($dbr->tableExists('smartindex_stop_words')) {
		$res = $dbr->select('smartindex_stop_words', 'word');
		for ($i = 0; $i < $dbr->numRows($res); ++$i) {
			$row = $dbr->fetchObject($res);
			$stopWords[] = $row->word;
		}
	}
	return $stopWords;
}
	

/* Prints the title of each page and then a table containing each unique
 * word or link appearing in the article and its frequency in the page.
 */
function displayWordsTable($dbr, $res, $options, $stopWords) {
	#begin table	
	$markTableBegin =  "\n{|class='wikitable sortable'";
	$markTableEnd = "\n|}";
	$markTableNewRow = "\n|-";
	$result = $markTableBegin . $markTableNewRow . "\n!Word" . "\n!Pages";
	
	#if the current mode calls for it, add scores to table
	if ($options['scoreMode'] !== 'default') { 
		$result .= "\n! data-sort-type='numeric' | Score";
	}
	
	#add words to table
	
	$numWords = $dbr->numRows($res);
	for ($i = 0; $i < $numWords; ++$i) {
		$row = $dbr->fetchObject($res);
		if (!filterWord($row, $stopWords, $options)) {
			$result .= $row->blemish . ' ';
			$result .= $markTableNewRow . "\n";
			$result .= "|" . $row->token . "\n|";
		
			$pageList = makePageList($row->pages);
			$result .= $pageList;
		
			if ($options['scoreMode'] !== 'default') {
				$result .= "\n|";
				if ($options['scoreMode'] == 'frequency') $result .= $row->frequency;
				else $result .= $row->idf;
			}
		}
	}
		#end table
		$result .= $markTableEnd;
		$result .= '<br>' . $options['freqCutoff'];
		return $result;		
}


/* Creates an alphabetic index of each word with a collapsible list of links to the pages 
 * in which it appears.
 */
function displayWordsList($dbr, $res, $options, $stopWords) {
	$numWords = $dbr->numRows($res);
	$wordsPerCol = $numWords/3 + 1;
	settype($wordsPerCol, "integer");
	$currColEntries = 0;
	$currLetter = NULL;
	
	#build index
	$headers = array();
	$index = "<table><td valign='top'>";
	for ($i = 0; $i < $numWords; ++$i) {
		$row = $dbr->fetchObject($res);
		$word = $row->token;
		
		if(!filterWord($row, $stopWords, $options)) {
		
			#new index letter?
			if (mb_substr($word, 0, 1) !== $currLetter) {
				$currLetter = mb_substr($word, 0, 1);
				$index .= "<h3>" . $currLetter . "</h3>";
				$headers[] = $currLetter;
			}
			
			$index .= "<ul><li>";
			#if the user provided a template, use it. if not, produce standard elements
			if ($template !== NULL) $index .= templateIndexElement($dbr, $row, 
										   $options['scoreMode'], $template);
			else $index .= standardIndexElement($dbr, $row, $options['scoreMode']);
			$index .= "</li></ul>";
		
			#start new column?
			++$currColEntries;
			if ($currColEntries == $wordsPerCol) {
				$index .= "</td><td valign='top'>";
				$currColEntries = 0;
			}
		}
	}
	$index .= "</table>";
	
	$result = '';
	foreach ($headers as $header) {
		$result .= "[[{{PAGENAME}}#" . $header . "|" . $header . "]] ";
	}
	$result .= '<br>' . $index;
	return $result;
}


/* Filters out words based on current score mode */	
function filterWord($row, $stopWords, $options) {
	
	#checks if the word represented by row is a stop word
	if (in_array($row->token, $stopWords)) return true;
	
	#filter words by score acccording to user options
	if ($options['IDFCutoff'] !== NULL) {
		if ($row->idf <= $options['IDFCutoff']) return true;
		else return false;
	} else if ($options['freqCutoff'] !== NULL) {
		if ($row->frequency >= $options['freqCutoff']) return true;
		else return false;
	} else {
		return false;
	}
}	
    

/* Returns a list of links to the pages, extracted from the $pages string pulled from
 * the database.
 */
function makePageList($pages) {
	$list = '';
	$page = strtok($pages, ' ');
	while($page !== false) {
		$list .= "[[" . $page . "]] ";
		$page = strtok(' ');
	}
	return $list;
}


/* Produces a default output element for the index table. */
function standardIndexElement($data, $row, $scoreMode) {
	if ($scoreMode == 'frequency') $score = $row->frequency;
	else $score = $row->idf;
	$element = $row->token . "  (" . $score . ")";
	$element .= "<span class='mw-collapsible mw-collapsed' data-expandtext='&#8658;'>";
	$element .= makePageList($row->pages);
	$element .= "</span>";
	return $element;
}


/* Produces output elements based on a template given as a parameter by the user. */
function templateIndexElement($data, $row, $scoreMode, $templateName) {
	if ($scoreMode == 'frequency') $score = $row->frequency;
	else $score = $row->idf;
	$element = "{{" . $templateName;
	$element .= "\n| Word = " . $row->token;
	$element .= "\n| Score = " . $score;
	$element .= "\n| Pages = " . makePageList($row->pages);
	$element .= "}}";
	return $element;
}

?>