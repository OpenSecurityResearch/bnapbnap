<?php
require_once("bnapbnap.inc.php");

$db = new SQLite3($dbfile);
if ($db == NULL) {
	errorprint("Unable to open database.<br>\n");
	exit;
}
$records = $db->query($thelistquery);
if ($records == FALSE) {
	errorprint("Unable to retrieve record information.");
	exit;
}

header("Content-type: text/plain");
header("Cache-Control: no-store, no-cache");

print "prefix,partnum,manufacturer,function,ouivendor\n";
while ($record = $records->fetchArray()) {
	$oui_stmt = "select vendor from oui where oui=\"" . $record[0] . "\";";
	$oui_query = $db->querySingle($oui_stmt);
	echo $record[0] . "," . $record[1] . "," . $record[2] . "," . $record[3] . ",";
			
	if ($oui_query == FALSE || $oui_query == "") {
		echo "Unknown\n";
	} else {
		echo $oui_query . "\n";
	}
}
$db->close();

exit;
