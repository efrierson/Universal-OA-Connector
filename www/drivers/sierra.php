<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
//Set the target site's api url for auth token
$authurl="https://opac.usc.edu.tt/iii/sierra-api/v5/token";

//set the authkey provided
$authkey="3zktFioBAYrpmbQl7sfVeqrwz+Q3";

//set the authsecret provided
$authsecret="EBSCO2018USC";

//encode for passing via header to get token
$encodedauth= base64_encode($authkey.":".$authsecret);

//create curl session to get auth token
$ch = curl_init();

//set header for authorization call
$headers = array("Authorization: Basic ".$encodedauth);
curl_setopt($ch, CURLOPT_URL, $authurl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//get response from curl session
$response = curl_exec($ch);
$responsecode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

curl_close($ch);

echo "Output from Service";
echo "<br><br>";
echo "Response:".$response;
echo "<br>";
echo "Response Code: ".$responsecode;
echo "<br>";
$responseobj = json_decode($response);
$authtoken = $responseobj->access_token;

//PATRON API Connection
//User's barcode
$barcode = "28888888888887";
//User's pin
$pin = "abc12345";
//Target URL of service
$patronurl = "https://opac.usc.edu.tt/iii/sierra-api/v5/patrons/validate";
//create POST Body Object
$patroninfo = array("barcode" => $barcode,"pin" => $pin);
$patroninfojson = json_encode($patroninfo);

echo "<br>JSON: ".$patroninfojson."<br>";

//Create POST headers
$patronheaders = array(
  "Content-Type: application/json",
  "Content-Length: " . strlen($patroninfojson),
  "Authorization: Bearer ".$authtoken
);

//create curl session to get patron info
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $patronurl);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $patroninfojson);
curl_setopt($ch, CURLOPT_HTTPHEADER, $patronheaders);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//get response from curl session
$response = curl_exec($ch);
$responsecode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

curl_close($ch);

echo "Output from Patron Service";
echo "<br><br>";
echo "TargetURL: ".$patronurl."<br>";
echo "Headers sent: ".json_encode($patronheaders)."<br>";
echo "Response:".$response;
echo "<br>";
echo "Response Code: ".$responsecode;
echo "<br>";
?>

