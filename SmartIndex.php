
<?php

/* Setup file for the SmartIndex MediaWiki Extension. */

if ( !defined( 'MEDIAWIKI' ) ) 
	die( "This is an extension to the MediaWiki package and cannot be run standalone." );

#Set up parser tag
$wgExtensionCredits['parserhook'][] = array(
	'name' => 'SmartIndex',
	'version' => '1.5',
	'author' => "Blaise Bradley",
	'descriptionmsg' => 'smartindex-desc',
	'url' => '',
);

$wgHooks['ParserFirstCallInit'][] = 'registerSmartIndex';

#register parser function
function registerSmartIndex () {
	global $wgParser;
	$wgParser->setHook('smartIndex', 'SmartIndex');
	return true;
}



$dir = dirname(__FILE__) . '/';

require_once($dir . 'SmartIndex_body.php');

#Set up Special Page for maintenance
$wgAutoloadClasses['SmartIndexMaintenance'] = $dir . 'SmartIndexMaintenance.php';
$wgSpecialPages['SmartIndexMaintenance'] = 'SmartIndexMaintenance';

$wgExtensionMessagesFiles['SmartIndex'] = $dir . 'SmartIndex.i18n.php'; # Location of messages file

?>