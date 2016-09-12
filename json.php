<?php
include 'config.php';
header("Content-type: application/json");

//Get Data
$data = file_get_contents('php://input');

if (!file_exists($storage.'dsdata/')) {
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
	"version": 1.0,
	"status": "Ready"
}}
JSON;

	//Display Response
	echo $string;
}
else {

	//Generate Response
	$string = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<SMSDirectoryData>
	<error>401</error>
	<result>No Data</result>
	<service>ADSync</service>
	<version>1.0</version>
	<status>Ready</status>
</SMSDirectoryData>
XML;

	//Display Response
	$xml = new SimpleXMLElement($string);
	echo $xml->asXML();
}
?>
