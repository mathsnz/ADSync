<?php
include 'config.php';
header("Content-type: application/json");
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);

//Get Data
$data = file_get_contents('php://input');

if (!file_exists($storage)) {
    mkdir($storage, 0777, true);
}

//Check for data
if(isset($data)) {
  //create a random string to avoid duplicate files being created.
  $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
  //Write xml file
  $file = fopen($storage.time().$randomString.".json", "w") or die("Unable to open file!");
	fwrite($file, $data);
	fclose($file);

	//Generate Response
	$string = <<<JSON
  {"SMSDirectoryData": {
      "error": 0,
      "result": "OK",
      "service": "ADSync",
      "version": 1.1,
      "status": "Ready",
      "options": {
          "ics": false,
          "students": {
              "details": true,
              "passwords": true,
              "photos": false,
              "groups": false,
              "timetables": false,
              "attendance": false,
              "assessments": false,
              "awards": false,
              "pastoral": false,
              "learningsupport": false,
              "fields": {
                "required": "uniqueid;firstname;lastname;email;username;password;yearlevel;startingdate;leavingdate;networkaccess"
                }
              },
          "staff": {
              "details": true,
              "passwords": true,
              "photos": false,
              "timetables": false,
              "fields": {
                "required": "uniqueid;firstname;lastname;email;username;password"
                }
              },
          "common": {
              "subjects": false,
              "notices": false,
              "calendar": false,
              "bookings": false
              }
          }
      }
  }
JSON;

	//Display Response
	echo $string;
} else {

	//Generate Response
	$string = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<SMSDirectoryData>
	<error>401</error>
	<result>No Data</result>
	<service>ADSync</service>
	<version>1.1</version>
	<status>Ready</status>
</SMSDirectoryData>
XML;

	//Display Response
	$xml = new SimpleXMLElement($string);
	echo $xml->asXML();
}
?>
