<?php

/* Globals */
$devices = Array();
$devices[] = "Unknown";
$devices[] = "USB Dongle";
$devices[] = "PCCard";
$devices[] = "PDACard";
$devices[] = "Phone";
$devices[] = "Smart phone";
$devices[] = "Headset";
$devices[] = "Keyboard";
$devices[] = "Mouse";
$devices[] = "Other Pointing Device";
$devices[] = "Desktop workstation";
$devices[] = "Server";
$devices[] = "Laptop";
$devices[] = "Handheld sized PC";
$devices[] = "Palm sized PC";
$devices[] = "Wearable";
$devices[] = "Cordless";
$devices[] = "LAN access device";
$devices[] = "Microphone";
$devices[] = "Loudspeaker";
$devices[] = "Headphones";
$devices[] = "Portable audio";
$devices[] = "Car audio";
$devices[] = "Set-top box";
$devices[] = "Video Camera";
$devices[] = "Gaming/Toy";
$devices[] = "Other";

$dbfile = "bnapbnap.db";

// Don't like that the oui has to be valid
//$thelistquery = 'select distinct(bdaddr0 || ":" || bdaddr1 || ":" || bdaddr2) bdprefix, partnum, manuf, function, vendor from bnap, oui where bdprefix = oui and oui != "" group by bdprefix having count(submitterhost) > 1';

$thelistquery = 'select distinct(bdaddr0 || ":" || bdaddr1 || ":" || bdaddr2) bdprefix, partnum, manuf, function from bnap group by bdprefix having count(submitterhost) > 1';

function response_error($message)
{
	/* XML error response */
	print "<?xml version=\"1.0\" standalone=\"yes\"?>
<response>
  <returnval>failure</returnval>
  <message>$message</message>
</response>
";
	return;
}

function response_success($message)
{
	/* XML success message */
	print "<?xml version=\"1.0\" standalone=\"yes\"?>
<response>
  <returnval>success</returnval>
  <message>$message</message>
</response>
";
	return;
}

function print_header()
{
print "
<html>
<head>

<meta http-equiv=\"content-type\" content=\"text/html; charset=ISO-8859-1\">
<title>BNAP, BNAP; Bluetooth Device Address Collection</title>

</head>
<body>

";
}

function print_trailer()
{
	print "</body></html>";
}

function check_addrbyte($input)
{
	if ($input == "") {
		return -1;
	}

	if (preg_replace("/[^A-Fa-f0-9]/", "", $input) != $input) {
		return -1;
	}

	return 0;
}

function sanitize_manuf($input)
{
	return sanitize_partnum($input);
}

function sanitize_partnum($input)
{
	$input = preg_replace("/[^A-Za-z0-9\- ]/", "", $input);
	$input = preg_replace("/--/", "-", $input);
	return $input;
}

function check_function($input)
{
	global $devices;

	if ($input == "Unknown") {
		return 0;
	}

	foreach($devices as $key=>$value) {
		if ($input == $value) {
			return 0;
		}
	}
	return -1;
}

function errorprint($str)
{
	print "<span style=\"font-weight: bold;\"><font color=\"#FF0000\">" .
			$str . "</font></span><br>";
	return;
}

?>
