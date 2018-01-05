<?php
session_start();
$_SESSION["valid"] = "N";
$_SESSION['returnData'] = "";
$_SESSION['fullname'] = "";
$_SESSION['uid'] = "";
$_SESSION['custid'] = "";

$debug = "";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../includes/encryption.php");
require_once("../includes/sip2.class.php");
$mysip = new sip2;

$post = file_get_contents('php://input');

//echo "POST: ".$post."<br/><br/>";
$json_data = json_decode($post);

$config = json_decode(file_get_contents('../../conf/'.$json_data->custid.'.json'));

$encrypted_un = $json_data->un;
$encrypted_pw = $json_data->pw;

$codex = new MyEncryption();

if ($json_data->verbose == "Y") {
    $mysip->debug = true;
} else {
    $mysip->debug = false;    
}
$mysip->hostname = $config->hostname;
$mysip->port = $config->port;

$mysip->scLocation = $config->location;

if (!$mysip->connect()) {
    $mysip->disconnect();
    throw new Exception("Cannot connect to SIP server");
}

$in=$mysip->msgLogin($codex->decrypt($config->un), $codex->decrypt($config->pw));

$debug .= "<br/><strong>Sending: </strong>".$in."<br/>";

$msg_result = $mysip->get_message($in);
$msg_result =str_replace(array("\r\n","\r","\n"), "", $msg_result);
if (!preg_match("/^941/", $msg_result)) {
    $mysip->disconnect();
    echo "<br/><strong>Error: </strong>940 message indicates failed login response to 93 message.<br/>";
    die();
}

$debug .= "<br/><strong>".$msg_result."</strong><br/>";

// user auth
$mysip->patron = $codex->decrypt($encrypted_un);
$mysip->patronpwd = $codex->decrypt($encrypted_pw);

$in=$mysip->msgPatronStatusRequest();
$msg_result = $mysip->get_message($in);
$msg_result =str_replace(array("\r\n","\r","\n"), "", $msg_result);
$response=$mysip->parsePatronInfoResponse($msg_result);

$mysip->disconnect();

$connectorResponse = [];

if (isset($response["variable"]["BL"][0]) && isset($response["variable"]["CQ"][0]) && ($response["variable"]["CQ"][0] == "Y") && ($response["variable"]["BL"][0] == "Y") && !(isset($response["variable"]["AF"][0]))) {
    if (isset($json_data->rd)) {
        $returnData = $json_data->rd;
        $_SESSION['returnData'] = $returnData;
    } else {
        $_SESSION['returnData'] = "";
    }
    $_SESSION["valid"] = "Y";
    $_SESSION["fullname"] = "";
    if (isset($response["variable"]["AE"][0])) {
        $_SESSION["fullname"] = $response["variable"]["AE"][0];
    }

    $_SESSION["attributes"] = [];
    if (isset($response["variable"]["XA"][0])) {
        $_SESSION["attributes"]["department"] = $response["variable"]["XA"][0];
    }
    if (isset($response["variable"]["BD"][0])) {
        $_SESSION["attributes"]["postalAddress"] = trim($response["variable"]["BD"][0]);
    }    
    $_SESSION["uid"] = $codex->decrypt($encrypted_un);
    $_SESSION["custid"] = $json_data->custid;
    $connectorResponse["valid"] = "Y";
    $connectorResponse["returnData"] = $_SESSION['returnData'];
    echo json_encode($connectorResponse);
} else {
    $connectorResponse["valid"] = "N";
    if (isset($response["variable"]["AF"][0])) {
        $connectorResponse["message"] = $response["variable"]["AF"][0];
    } else {
        $connectorResponse["message"] = "";
    }
    echo json_encode($connectorResponse);
}
$debug .= "<br /><strong>Debug: </strong>".var_export($_SESSION,TRUE)."<br/>";
if ($json_data->verbose == "Y") {
    echo $debug;
}
?>
