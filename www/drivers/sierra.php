<?php
session_start();
$_SESSION["valid"] = "N";
$_SESSION['returnData'] = "";
$_SESSION['fullname'] = "";
$_SESSION['uid'] = "";
$_SESSION['custid'] = "";
require_once("../includes/encryption.php");

$debug = "Y";

ini_set("display_errors", 1);
error_reporting(E_ALL);

$user_data = json_decode($post);
$config_data = json_decode(file_get_contents('../../conf/'.$user_data->custid.'.json'));

$encrypted_patron_un = $user_data->un;
$encrypted_patron_pw = $user_data->pw;
$codex = new MyEncryption();

$returnData = $user_data->rd;
//$returnData = "FAKE";
$custID = $user_data->custid;
//$custID = "FAKE";

//set the authkey provided
$authkey = $codex->decrypt($config_data->un);
//$authkey="3zktFioBAYrpmbQl7sfVeqrwz+Q3";

//set the authsecret provided
$authsecret = $codex->decrypt($config_data->pw);
//$authsecret="EBSCO2018USC";

//set baseurl for calls
$$baseurl = $config_data->hostname;
//$baseurl ="https://opac.usc.edu.tt/iii/sierra-api/v5/";



//User's barcode
$barcode = $codex->decrypt($user_data->un);
//$barcode = "28888888888887";

//User's pin
$pin = $codex->decrypt($user_data->pw);
//$pin = "abc12345";

//RUN AUTHENTICATION FUNCTIONS
$authtoken = Authorize($baseurl,$authkey,$authsecret,$debug);
if ($authtoken != "invalid"){
  $isValid = validatePatron($baseurl,$authtoken,$barcode,$pin,$debug);
  if ($isValid == "204"){
    $checkBlocked = checkBlocked($baseurl,$authtoken,$barcode,$debug);
  }
}

//Check that key is authorized
function Authorize($baseurl,$authkey,$authsecret,$debug){

  //Set the target site's api url for auth token
  $authurl = $baseurl."token";

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

  if ($debug = "Y"){
    echo "<b>Output from Service</b>";
    echo "<br><br>";
    echo "Response:".$response;
    echo "<br>";
    echo "Response Code: ".$responsecode;
    echo "<hr>";
  }

  $responseobj = json_decode($response);
  $authtoken = $responseobj->access_token;
  if ($responsecode == "200"){
    return $authtoken;
  }
  else{
    return "invalid";
  }
}

function validatePatron($baseurl,$authtoken,$barcode,$pin,$debug){

  //Set the target URL of validation service
  $patronurl = $baseurl."patrons/validate";

  //create POST Body Object
  $patroninfo = array("barcode" => $barcode,"pin" => $pin);
  $patroninfojson = json_encode($patroninfo);

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

  if ($debug = "Y"){
    echo "<br><b>Output from Patron Service</b>";
    echo "<br>";
    echo "<br>JSON Request: ".$patroninfojson."<br>";
    echo "TargetURL: ".$patronurl."<br>";
    echo "Headers sent: ".json_encode($patronheaders)."<br>";
    echo "Response:".$response;
    echo "<br>";
    echo "Response Code: ".$responsecode;
    echo "<hr>";
  }

  return $responsecode;
}

function checkBlocked($baseurl,$authtoken,$barcode,$debug){

  //Set the target URL of patron data
  $statusurl = $baseurl."patrons/find?varFieldTag=b&varFieldContent=".$barcode."&fields=blockInfo%2CexpirationDate%2Cid%2Cnames%2CpatronCodes";

  //Create POST headers
  $statusheaders = array(
    "Content-Type: application/json",
    "Authorization: Bearer ".$authtoken
  );

  //create curl session to get patron info
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $statusurl);
  curl_setopt($ch, CURLOPT_VERBOSE, 1);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
  curl_setopt($ch, CURLOPT_HTTPHEADER, $statusheaders);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  //get response from curl session
  $response = curl_exec($ch);
  $responsecode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

  curl_close($ch);

  if ($debug = "Y"){
    echo "<br><b>Output from Patron Service</b>";
    echo "<br><br>";
    echo "TargetURL: ".$statusurl."<br>";
    echo "Headers sent: ".json_encode($statusheaders)."<br>";
    echo "Response:".$response;
    $arrayresponse = json_decode($response);
    echo "<br>Blocked Code: ".$arrayresponse->blockInfo->code;
    echo "<br>";
    echo "Response Code: ".$responsecode;
    echo "<br>Name: ".$arrayresponse->names[0];
    echo "<hr>";
  }

  if ($arrayresponse->blockInfo->code == "-"){
    $_SESSION["valid"] = "Y";
    $_SESSION['returnData'] = "";
    $_SESSION['fullname'] = $arrayresponse->names[0];
    //$_SESSION['uid'] = "";
    //$_SESSION['custid'] = "";
  }

}

?>

