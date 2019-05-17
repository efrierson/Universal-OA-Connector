<?php
session_start();
$_SESSION["valid"] = "N";
$_SESSION['returnData'] = "";
$_SESSION['fullname'] = "";
$_SESSION['uid'] = "";
$_SESSION['custid'] = "";
require_once("../includes/encryption.php");
$finalresponse = array();
ini_set("display_errors", 1);
error_reporting(E_ALL);
$post = file_get_contents('php://input');
$user_data = json_decode($post);
$config_data = json_decode(file_get_contents('../../conf/'.$user_data->custid.'.json'));

$debug = $user_data->verbose;
$encrypted_patron_un = $user_data->un;
$encrypted_patron_pw = $user_data->pw;
$codex = new MyEncryption();

$returnData = $user_data->rd;

$custID = $user_data->custid;

//set the authkey provided
$authkey = $codex->decrypt($config_data->un);
//$authkey="3zktFioBAYrpmbQl7sfVeqrwz+Q3";

//set the authsecret provided
$authsecret = $codex->decrypt($config_data->pw);

if (isset($config_data->blocked)){
  $blockedmessage = $config_data->blocked;
}
else {
  $blockedmessage = "";
}
if (isset($config_data->invalid)){
  $invalid = $config_data->invalid;
}
else {
  $invalid = "";
}
//set baseurl for calls
$baseurl = $config_data->hostname;

//User's barcode
$barcode = $codex->decrypt($user_data->un);

//User's pin
$pin = $codex->decrypt($user_data->pw);

//RUN AUTHENTICATION FUNCTIONS
$authtoken = Authorize($baseurl,$authkey,$authsecret,$debug);
if ($authtoken != "invalid"){
  $isValid = validatePatron($baseurl,$authtoken,$barcode,$pin,$debug);
  if ($isValid == "204"){
    $checkBlocked = checkBlocked($baseurl,$authtoken,$barcode,$returnData,$custID,$finalresponse,$debug,$blockedmessage);
  }
  else{
    if ($invalid != ""){
      $finalresponse['message'] = $invalid;
    }
    else{
      $finalresponse['message'] = $isValid;
    }
    echo json_encode($finalresponse);
  }
}
else{
  $finalresponse['message'] = "Sierra authentication token issue.  Please contact your library.";
  echo json_decode($finalresponse);
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

  if ($debug == "Y"){
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

  if ($debug == "Y"){
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

  if ($responsecode == "204"){
      return $responsecode;
  }
  else {
    $responsearray = json_decode($response);
    return $responsearray->description;
  }

}

function checkBlocked($baseurl,$authtoken,$barcode,$returnData,$custID,$finalresponse,$debug,$blockedmessage){

  //Set the target URL of patron data
  $statusurl = $baseurl."patrons/find?varFieldTag=b&varFieldContent=".$barcode."&fields=blockInfo%2CexpirationDate%2Cid%2Cnames%2CpatronCodes%2Cemails%2ChomeLibraryCode%2CvarFields%2CfixedFields";

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
  $arrayresponse = json_decode($response);

  curl_close($ch);

  if ($arrayresponse->blockInfo->code == "-"){
    $_SESSION["valid"] = "Y";
    $_SESSION['returnData'] = $returnData;
    $_SESSION['fullname'] = $arrayresponse->names[0];
    $_SESSION['custid'] = $custID;
    $_SESSION['uid'] = $arrayresponse->id;
    $_SESSION['attributes'] = array();
    //ADD PCODES
    foreach($arrayresponse->patronCodes as $key => $value){
        $_SESSION['attributes'][$key] = $value;
    }
    $aname = explode(",",$arrayresponse->names[0]);

    $firstName = (isset($aname[1]) ? trim($aname[1]) : "");
    $_SESSION['attributes']['firstName'] = $firstName;

    $lastName = (isset($aname[0]) ? trim($aname[0]) : "");
    $_SESSION['attributes']['lastName'] = $lastName;

    $email = (isset($arrayresponse->emails[0]) ? $arrayresponse->emails[0] : "");
    $_SESSION['attributes']['email'] = $email;

    $homeLibraryCode = (isset($arrayresponse->homeLibraryCode) ? $arrayresponse->homeLibraryCode : "");
    $_SESSION['attributes']['homeLibraryCode'] = $homeLibraryCode;

    $agency = (isset($arrayresponse->fixedFields->{'86'}->display) ? $arrayresponse->fixedFields->{'86'}->display : "");
    $_SESSION['attributes']['agency'] = $agency;

    $patagency = (isset($arrayresponse->fixedFields->{'158'}->value) ? $arrayresponse->fixedFields->{'158'}->value : "");
    $_SESSION['attributes']['patagency'] = $patagency;

    $finalresponse['valid'] = "Y";
    $finalresponse['returnData'] = $returnData;
  }
  else {
    if ($blockedmessage != ""){
      $finalresponse['message'] = $blockedmessage;
    }
    else{
      $finalresponse['message'] = "Patron status is blocked.  Please contact the library for assistance.";
    }
  }

  if ($debug == "Y"){
    echo "<br><b>Output from Patron Service</b>";
    echo "<br><br>";
    echo "TargetURL: ".$statusurl."<br>";
    echo "Headers sent: ".json_encode($statusheaders)."<br>";
    echo "Response:".$response;
    echo "<br>Blocked Code: ".$arrayresponse->blockInfo->code;
    echo "<br>";
    echo "Response Code: ".$responsecode;
    echo "<br>Name: ".$arrayresponse->names[0];
    echo "<br>First Name:".$firstName;
    echo "<br>Last Name:".$lastName;
    echo "<br>Email: ".$email;
    echo "<br>Home Library Code: ".$homeLibraryCode;
    echo "<br>Agency: ".$agency;
    echo "<br>Pat Agency: ".$patagency;
    echo "<hr>";
    echo "<br><b>SESSION VARIABLES</b><br>";
    print_r($_SESSION);
  }

  echo json_encode($finalresponse);
}
?>

