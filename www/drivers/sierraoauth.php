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
$code = $user_data->code;

//redirect
$redirect = $config_data->redirect;

//Custom fields
$varFields = array();
if (isset($config_data->varFields)){
  $varFields = explode("|",$config_data->varFields);
}

$fixedFields = array();
if (isset($config_data->fixedFields)){
  $fixedFields = explode("|",$config_data->fixedFields);
}

//GET PATRON AUTH USING CODE
$patronAuthKey = getPatronAuth($baseurl,$authkey,$authsecret,$code,$custID,$redirect,$debug);
if ($patronAuthKey != "invalid"){
  //GET PATRON ID
  $patronId = getPatronId($baseurl,$authkey,$authsecret,$patronAuthKey,$debug);
  if ($patronId != "invalid"){
    //RUN AUTHENTICATION FUNCTIONS
    $authtoken = Authorize($baseurl,$authkey,$authsecret,$debug);
    if ($authtoken != "invalid"){
      $checkBlocked = checkBlocked($baseurl,$authtoken,$patronId,$returnData,$custID,$finalresponse,$debug,$blockedmessage,$varFields,$fixedFields);
    }
    else{
      $finalresponse['message'] = "Sierra authentication token issue.  Please contact your library.";
      echo json_decode($finalresponse);
    }
  }
}

function getPatronAuth($baseurl,$authkey,$authsecret,$code,$custID,$redirect,$debug){

  //Set the target site's api url for auth token
  $url = $baseurl."token";

  //encode for passing via header to get token
  $encodedauth= base64_encode($authkey.":".$authsecret);

  //create curl session to get auth token
  $ch = curl_init();

  //set header for authorization call
  $http_headers = [
        "Content-type: application/x-www-form-urlencoded",
        "Accept: application/json",
        "Authorization: Basic ".$encodedauth
    ];

  $data_string = "grant_type=authorization_code&code=".$code."&redirect_uri=https%3A%2F%2Foaconnector.ebsco-gss.net%2Flogin.php%3Forganization%3D".$custID;

  //Debug Data
  if ($debug == "Y"){
    echo "</br>URL is: ".$url;
    echo "</br>Auth is:".$encodedauth;
    echo "</br>Data to send is: ".$data_string;
  }

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
  curl_setopt($ch, CURLOPT_URL,$url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);

  $server_output = curl_exec($ch);

  if ($debug == "Y"){
    echo "</br></br><b>PATRON AUTHENTICATION SERVER RESPONSE:</b></br>".$server_output;
  }
  curl_close ($ch);
  $data = json_decode($server_output);
  if (isset($data->access_token)){
      return $data->access_token;
  }
  else{
    return "invalid";
  }

}

function getPatronId($baseurl,$authkey,$authsecret,$patronAuthKey,$debug){
  $url=$baseurl."info/token";
  $http_headers = [
      "Content-type: application/x-www-form-urlencoded",
      "Accept: application/json",
      "Authorization: Bearer ".$patronAuthKey,
      "User-Agent: OpenAthens Connector"
  ];
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL,$url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
  $server_output = curl_exec($ch);

  curl_close ($ch);
  $infojson = json_decode($server_output);
  if ($debug == "Y"){
    echo "</br><b>PATRON ID OUTPUT:</b></br>".$server_output;
  }
  if (isset($infojson->patronId)){
    return $infojson->patronId;
  }
  else {
    return "invalid";
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

function checkBlocked($baseurl,$authtoken,$patronId,$returnData,$custID,$finalresponse,$debug,$blockedmessage,$varFields,$fixedFields){

  //Set the target URL of patron data
  $statusurl = $baseurl."patrons/?id=".$patronId."&fields=blockInfo%2CexpirationDate%2Cid%2Cnames%2CpatronCodes%2Cemails%2ChomeLibraryCode%2CvarFields%2CfixedFields";

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
  $arrayresponse = $arrayresponse->entries[0];

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

    //Warning: It's possible for multiple of the same tag to exist.
    //Currently that will cause the varField value we return to be overwritten
    //With the last instance of the tag.
    foreach($varFields as $key => $value){
      foreach($arrayresponse->varFields as $k => $v){
        if($v->fieldTag == $value){
          $_SESSION['attributes']['varField'.$value] = $v->content;
        }
      }
    }

    foreach($fixedFields as $key => $value){
      if( isset($arrayresponse->fixedFields->{$value}->value)) {
        $_SESSION['attributes'][urlencode($arrayresponse->fixedFields->{$value}->label)] = $arrayresponse->fixedFields->{$value}->value;
      }
    }


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

