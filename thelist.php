<?php
require_once("bnapbnap.inc.php");

$db = sqlite_open($dbfile);
if ($db == NULL) {
	errorprint("Unable to open database.<br>\n");
	exit;
}
$records = sqlite_query($db, $thelistquery);
if ($records == FALSE) {
	errorprint("Unable to retrieve record information.");
	exit;
}

header("Content-type: text/plain");
header("Cache-Control: no-store, no-cache");

print "prefix,partnum,manufacturer,function,ouivendor\n";
while ($record = sqlite_fetch_array($records)) {
	echo $record[0] . "," . $record[1] . "," .
			$record[2] . "," . $record[3] . "," .
			$record[4] . "\n";
}

sqlite_close($db);
exit;
