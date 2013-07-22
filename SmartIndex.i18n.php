<?php
/**
 * Internationalisation for SmartIndex
 *
 * @file
 * @ingroup Extensions
 */
$messages = array();
 
/** English
 * @author Blaise Bradley
 */
$messages['en'] = array(
        'smartindex' => 'Smart Index',
        'smartindexmaintenance' => 'Smart Index Maintenance',
        'smartindex-desc' => "Tag extension to create a customizable index on any desired page",
        'smartindex-no-index-table' => "Index table not found in database. Please run database maintenance before using the parser tag.",
        'smartindex-index-table-updated' => "Index table successfully created. " . '<br>',
        'smartindex-index-table-update-failed' => "Index table update failed." . '<br>',
        'smartindex-stop-words-updated' => "Stop words successfully updated." . '<br>',
        'smartindex-stop-words-no-page' => "Unable to access the Wiki page with the entered name. Please be sure this page exists in this wiki." . '<br>',
        'smartindex-stop-words-table-failed' => "Unable to create stop word table in the database." . '<br>',
        'smartindex-trim-chars-label' => "Characters to trim: ",
        'smartindex-case-sensitive' => "Case sensitive",
        'smartindex-index-words-submit' => "Update Index Words",
        'smartindex-stop-words-label' => "Stop Word Page: ",
        'smartindex-stop-words-submit' => "Update Stop Words",
        'smartindex-clear-stop-words' => "Clear Stop Words",
        'smartindex-stop-words-cleared' => "Stop words cleared." . '<br>'
);

/** German (Deutsch)
 * @author Blaise Bradley
 */
$messages['de'] = array(
		'smartindex' => 'Smart Index',
        'smartindexmaintenance' => 'Smart Index Maintenance',
        'smartindex-desc' => "Tag Extension zur Erzeugung eines anpassparen Index auf einer beliebigen Wikiseite",
        'smartindex-no-index-table' => "Indextabelle wurde nicht in der Datenbank gefunden. Bitte Datenbankverwaltung vor der Benutzung des Parsertags aufrufen.",
        'smartindex-index-table-updated' => "Indextabelle erfolgreich erzeugt. " . '<br>',
        'smartindex-index-table-update-failed' => "Aktualisierung der Indextabelle gescheitert." . '<br>',
        'smartindex-stop-words-updated' => "Stoppwortliste erfolgreich aktualisiert." . '<br>',
        'smartindex-stop-words-no-page' => "Zugriff auf eine Wikiseite mit dem angegebenen Namen nicht möglich. Bitte stellen Sie sicher, dass die Seite in diesem Wiki existiert." . '<br>',
        'smartindex-stop-words-table-failed' => "Erzeugung der Stoppworttabelle in der Datenbank nicht möglich." . '<br>',
        'smartindex-trim-chars-label' => "Worttrennzeichen: ",
        'smartindex-case-sensitive' => "Beachtung von Groß- und Kleinschreibung",
        'smartindex-index-words-submit' => "Aktualisierung des Wortindex",
        'smartindex-stop-words-label' => "Stoppwortseite: ",
        'smartindex-stop-words-submit' => "Aktualisierung der Stoppwortliste",
        'smartindex-clear-stop-words' => "Leeren der Stoppworttabelle",
        'smartindex-stop-words-cleared' => "Stopppworttabelle geleert."
);

 
/** Message documentation
 * @author Blaise Bradley
 */
$messages['qqq'] = array(
		'smartindex' => "This message is the name of the extension.",
		'smartindexmaintenance' => "This is the name for the extensions associated maintenance page.",
        'smartindex-desc' => "Description of the parser tag that appears on the Version page",
        'smartindex-no-index-table' => "This message appears when the table of index words is not found in the database.",
        'smartindex-index-table-updated' => "Notifies the user that the table of index words was updated.",
        'smartindex-index-table-update-failed' => "Notifies the user that attempt to update index words failed.",
        'smartindex-stop-words-updated' => "Notifies the user that the stop word table was succesfully updated.",
        'smartindex-stop-words-no-page' => "Notifies the user the page of stop words passed in was not found.",
        'smartindex-stop-words-table-failed' => "Notifies the user that the table of stop words could not be created.",
        'smartindex-trim-chars-label' => "Label for the input box for the characters to trim from index words.",
        'smartindex-case-sensitive' => "Label for the check box for case sensitivity on the maintenance page.",
        'smartindex-index-words-submit' => "Label for the update button for index words.",
        'smartindex-stop-words-label' => "Label for the input box for the page containing stop words.",
        'smartindex-stop-words-submit' => "Label for the update button for stop words.",
        'smartindex-clear-stop-words' => "Label for the button to clear stop words.",
        'smartindex-stop-words-cleared' => "Notifies the user that the stop words were cleared."
);

