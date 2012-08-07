<?php
require_once("bnapbnap.inc.php");

error_reporting(E_ERROR | E_PARSE);
$error = "";

if (isset($_POST['dataset'])) {

	$bdaddr3_set = 0;
	$partnum_set = 0;
	$function_set = 0;
	$manuf_set = 0;

	/* Check for each of the required fields */
	if (!isset($_POST['bdaddr0']) || !isset($_POST['bdaddr1']) ||
			!isset($_POST['bdaddr2'])) {
		$error =  "Missing BD_ADDR content.\n";
	}

	/* Validate each of the address bytes and generate insert SQL */
	if (isset($_POST['bdaddr0']) && 
			(check_addrbyte($_POST['bdaddr0']) == -1)) {
		$error = "Unable to validate the first byte of the BD_ADDR.\n";
	}

	if (isset($_POST['bdaddr1']) && 
			(check_addrbyte($_POST['bdaddr1']) == -1)) {
		$error = "Unable to validate the second byte of the BD_ADDR.\n";
	}
	
	if (isset($_POST['bdaddr2']) && 
			(check_addrbyte($_POST['bdaddr2']) == -1)) {
		$error = "Unable to validate the third byte of the BD_ADDR.\n";
	}

	if (isset($_POST['bdaddr3'])) {
		if (strlen($_POST['bdaddr3']) > 2) {
			$error = "Invalid input in the fourth byte of the " .
					"BD_ADDR.\n";
		} else if (strlen($_POST['bdaddr3']) == 2) {
			if (check_addrbyte($_POST['bdaddr3']) == -1) {
				$error = "Unable to validate the fourth " .
						"byte of the BD_ADDR.\n";
			} else {
				$bdaddr3_set = 1;
			}
		}
	}

	if (isset($_POST['partnum']) && strlen($_POST['partnum']) > 0) {
		/* Sanitize part number information */
		$sanepartnum = sanitize_partnum($_POST['partnum']);
		if ($sanepartnum == "") {
			$error = "Unable to validate the part number.\n";
		} else {
			$partnum_set = 1;
		}
	}

	if (isset($_POST['manuf']) && strlen($_POST['manuf']) > 0) {
		/* Sanitize part number information */
		$sanemanuf = sanitize_manuf($_POST['manuf']);
		if ($sanemanuf == "") {
			$error = "Unable to validate the manufacturer.\n";
		} else {
			$manuf_set = 1;
		}
	}

	if (isset($_POST['function']) && (strlen($_POST['function']) > 0)) {
		if (check_function($_POST['function']) != 0) {
			$error = "Unable to validate the device function.\n";
		} else {
			$function_set = 1;
		}
	}

	header('Content-Type: text/xml');

	if ($error != "") {
		response_error($error);
	} else {
		$sql = "INSERT INTO bnap (bdaddr0, bdaddr1, bdaddr2, " .
				"bdaddr3, submitterhost, partnum, manuf, " .
				"function, timeentered) " .
				"VALUES ('" .
				strtoupper($_POST['bdaddr0']) . "', '" .
				strtoupper($_POST['bdaddr1']) . "', '" .
				strtoupper($_POST['bdaddr2']) . "', '";
		if ($bdaddr3_set) {
			$sql .= strtoupper($_POST['bdaddr3']) . "', '";
		} else {
			$sql .= "\"\"', '";
		}

		$sql .= $_SERVER['REMOTE_ADDR'] . "', '";

		if ($partnum_set) {
			$sql .= strtoupper($sanepartnum) . "', '";
		} else {
			$sql .= "\"\"', '";
		}

		if ($manuf_set) {
			$sql .= $sanemanuf . "', '";
		} else {
			$sql .= "\"\"', '";
		}

		if ($function_set) {
			$sql .= $_POST['function'] . "', ";
		} else {
			$sql .= "Unknown', ";
		}

		$sql .= "DATETIME('NOW')";
		$sql .= ")";

		$db = sqlite_open($dbfile);
		if ($db == NULL) {
			response_error("Unable to open database.  Please try later.\n");
			exit;
		}
		if (sqlite_query($db , $sql) == FALSE) {
			response_error("Error inserting record.  Perhaps you (or someone from your IP address) has already submitted this record?\n");
			exit;
		}
		sqlite_close($db);

		response_success("You rock!  Thanks for the submission.  If you have more addresses to share, enter them now!\n");
	}

	exit;
}

/* GET */
print_header();
print "

<script language=\"JavaScript\">

/* Global */
var http = new XMLHttpRequest();

function validate_macbyte(val)
{
	if (val == \"\" || val.match(/[^a-fA-F0-9]/)) {
		return false;
	}
	return true;
}

function validate_devicefunc(devicefunc)
{
	var i=0;
	var devices = new Array();
";

	/* print out the devices array in jscript format */
	foreach($devices as $key=>$value) {
		print "\tdevices[$key] = \"$value\";\n";
	}

print "

	for(i=0; i < devices.length; i++) {
		if (devices[i] == devicefunc.options[devicefunc.selectedIndex].value) {
			return true;
		}
	}
	return false;
}

function handleHttpResponse()
{
	if (http.readyState == 4) {
		if (http.responseXML.getElementsByTagName('returnval').item(0).firstChild.data == \"failure\") {
			document.getElementById('message').innerHTML = \"Failure: \" + http.responseXML.getElementsByTagName('message').item(0).firstChild.data;
		} if (http.responseXML.getElementsByTagName('returnval').item(0).firstChild.data == \"success\") {
			document.getElementById('message').innerHTML = \"Success: \" + http.responseXML.getElementsByTagName('message').item(0).firstChild.data;
			document.getElementById('bdaddr0').value = \"\";
			document.getElementById('bdaddr1').value = \"\";
			document.getElementById('bdaddr2').value = \"\";
			document.getElementById('bdaddr3').value = \"\";
			document.getElementById('manuf').value = \"\";
			document.getElementById('partnum').value = \"\";
			document.getElementById('function').options.selectedIndex = 0;
		}

	}
}

function validate_submit()
{
	var err = \"\";
	var retfocus = \"\";
	var bdaddr0 = document.getElementById('bdaddr0');
	var bdaddr1 = document.getElementById('bdaddr1');
	var bdaddr2 = document.getElementById('bdaddr2');
	var bdaddr3 = document.getElementById('bdaddr3');
	var partnum = document.getElementById('partnum');
	var manuf = document.getElementById('manuf');
	var devicefunc = document.getElementById('function');
	var url = \"index.php\";
	var params = \"\";

	if (validate_macbyte(bdaddr0.value) == false) {
		err += \"Invalid input in the first byte of the address<br>\";
		retfocus = bdaddr0;
	}
	if (validate_macbyte(bdaddr1.value) == false) {
		err += \"Invalid input in the second byte of the address<br>\";
		if (retfocus == \"\") {
			retfocus = bdaddr1;
		}
	}
	if (validate_macbyte(bdaddr2.value) == false) {
		err += \"Invalid input in the third byte of the address<br>\";
		if (retfocus == \"\") {
			retfocus = bdaddr2;
		}
	}

	/* 4th byte can be empty */
	if (bdaddr3.value.match(/[^a-fA-A0-9]/)) {
		err += \"Invalid input in the fourth byte of the address<br>\";
		if (retfocus == \"\") {
			retfocus = bdaddr3;
		}
	}

	if (validate_devicefunc(devicefunc) == false) {
		err += \"Invalid input in the selected function<br>\";
		if (retfocus == \"\") {
			retfocus = devicefunc;
		}
	}

	/* Sanitize remaining free-form fields */
	partnum.value = partnum.value.replace(/[^A-Za-z0-9- ]/g, \"\");
	partnum.value = partnum.value.replace(/[--]/g, \"\");
	manuf.value = manuf.value.replace(/[^A-Za-z0-9- ]/g, \"\");
	manuf.value = manuf.value.replace(/[--]/g, \"\");



	if (err != \"\" || retfocus != \"\") {
		document.getElementById('message').innerHTML = err;
		document.getElementById('message').innerHTML += \"One or more errors were detected; can you fix them and try again?\\n\";
		retfocus.focus();
		return false;
	}

	params = \"dataset=dataset&bdaddr0=\" + bdaddr0.value + \"&bdaddr1=\" + bdaddr1.value + \"&bdaddr2=\" + bdaddr2.value + \"&bdaddr3=\" + bdaddr3.value + \"&partnum=\" + partnum.value + \"&manuf=\" + manuf.value + \"&function=\" + devicefunc.options[devicefunc.selectedIndex].value;

	http.open(\"POST\", url, true); 
	http.setRequestHeader(\"Content-type\", \"application/x-www-form-urlencoded\");
	http.setRequestHeader(\"Content-length\", params.length);
	http.setRequestHeader(\"Connection\", \"close\");
	http.onreadystatechange = handleHttpResponse;
	http.send(params);

}
</script>

<div style=\"text-align: center; font-family: Verdana;\">
<small>
<a href=\"readme.html#ABOUT\" target=\"_blank\">About</a> | 
<a href=\"readme.html#PRIVACY\" target=\"_blank\">Privacy</a> | 
<a href=\"thelist.php\" target=\"_blank\">The List</a> | 
<a href=\"code.html\" target=\"_blank\">Code</a> | 
<a href=\"mailto:jwright@hasborg.com\">Contact</a>
<br>
</small>

<table style=\"text-align: left; width: 100%;\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\">

  <tbody>

    <tr>

      <td><img style=\"width: 320px; height: 163px;\" alt=\"Purple Smurf\" src=\"images/gnapsmurf.jpg\"></td>

      <td style=\"text-align: center;\"><big><big style=\"font-weight: bold;\"><big><big><big>BNAP, BNAP</big></big></big></big><br>

      Collecting Bluetooth Device Addresses, one device at a time</td>

    </tr>

  </tbody>
</table>

<hr>
</div>

<form method=POST name=main>
  <input name=\"dataset\" value=\"dataset\" type=\"hidden\">
  <br style=\"font-family: Verdana;\">

  <table style=\"text-align: left; width: 100%; font-family: Verdana;\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\">

    <tbody>

      <tr>

        <td valign=\"top\" style=\"text-align: right;\">Address</td>

        <td>
        <table style=\"text-align: left; font-family: Verdana;\">

          <tbody>

            <tr>

              <td><input maxlength=\"2\" size=\"2\" id=\"bdaddr0\" name=\"bdaddr0\">:</td>

              <td><input maxlength=\"2\" size=\"2\" id=\"bdaddr1\" name=\"bdaddr1\">:</td>

              <td><input maxlength=\"2\" size=\"2\" id=\"bdaddr2\" name=\"bdaddr2\">:</td>

              <td><input maxlength=\"2\" size=\"2\" id=\"bdaddr3\" name=\"bdaddr3\">:</td>

              <td>XX:</td>

              <td>XX</td>

            </tr>

	    
            <tr>
              <td valign=top style=\"text-align: center;\"><font color=\"#FF0000\">*</td>
              <td valign=top style=\"text-align: center;\"><font color=\"#FF0000\">*</td>
              <td valign=top style=\"text-align: center;\"><font color=\"#FF0000\">*</td>

              <td>&nbsp;</td>

              <td>&nbsp;</td>

              <td>&nbsp;</td>

            </tr>

          </tbody>
        </table>

        </td>

      </tr>

      <tr>

        <td style=\"text-align: right;\">Part Number</td>

        <td><input maxlength=\"32\" size=\"20\" name=\"partnum\" id=\"partnum\"></td>

      </tr>

      <tr>

        <td style=\"text-align: right;\">Manufacturer</td>

        <td><input maxlength=\"32\" size=\"20\" name=\"manuf\" id=\"manuf\"></td>

      </tr>

      <tr>

        <td style=\"text-align: right;\">Function</td>

        <td>
        <select name=\"function\" id=\"function\">
        <option name=\"Unknown\" selected>Unknown</option>
";
	foreach($devices as $key=>$value) {
		print "<option name=\"$value\">$value</option>\n";
	}
print "
        </select>

        </td>

      </tr>

    </tbody>
  </table>

  <p align=\"center\">
  <table border=\"0\" width=\"50%\"><tr align=\"center\"><td>
  <font color=\"#FF0000\">
  <span style=\"font-weight: bold;\" id=\"message\">
  </span>
  </font>
  </td></tr></table>
  </p>

  <p align=\"center\"><input name=\"Submit\" value=\"Submit\" type=button onClick=\"validate_submit();\">
&nbsp;&nbsp;
  <input type=\"reset\"></p>

</form><br>
<font color=\"#FF0000\">*</font>&nbsp;Indicates a required field.</font>
";
print_trailer();



?>
