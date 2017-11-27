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

class MyEncryption
{

    public $pubkey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDLa7lVQ9kYoqrrqPIUv2dhDvyg
hraW4lgquGOLM59+G03F65uSXtom+lOVt/Wam2ROtrdW/JOpIIk7KUuk+byBBO1a
e0YZof7Q5YHIRGvMbLC2Z+fbTd/a0fp4SY3HZH5GDv8dcxJR8ZhSMBhy0x+VaLdO
M68I/cdG7IQrXDXXYQIDAQAB
-----END PUBLIC KEY-----';
    public $privkey = '-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDLa7lVQ9kYoqrrqPIUv2dhDvyghraW4lgquGOLM59+G03F65uS
Xtom+lOVt/Wam2ROtrdW/JOpIIk7KUuk+byBBO1ae0YZof7Q5YHIRGvMbLC2Z+fb
Td/a0fp4SY3HZH5GDv8dcxJR8ZhSMBhy0x+VaLdOM68I/cdG7IQrXDXXYQIDAQAB
AoGAMMGVHlawxjLW/Lz1qPtnb+ADtQYU5X1C3JptYYPyCmvI7FNYanDJoOYG+q+o
8nGkTSmGMBdB3RurSL7RHq2s/GGvDm5Z//mY06ewhIEj24nuN6O44KWug35WK1bX
83AeWc4Ncu8Kwf83Ok9RsLOKqzozkvCqrzIPiv3h3N587TECQQD6jUmzhXa2bAgZ
mPETDAwOEtoxncvUThzBrooHvOpQVTwYudGM4FJtBVkst40i3i0MuKeCGJbCYt3O
Wr7HFzaDAkEAz9gT854+VswhguTtEdD5k7e3Bbbr1u9FQNX5phbXFIW1FBMVlpIz
gdDxeZFEhPrTxx73dbeQmSfVDHtB8VV1SwJBAMaIEfBYPvrJm5l84PlwwFSeh5pt
KMfvpUWrYeBDx38kKtyE0RDJ50ZPyJtwTjtkxVmhL8ocZcldwdfze9wR/rUCQHHs
TDNSX3UP+qZWeKM1WjdfkZAuTWLIT7tUDby99DIpf7F7LHAVvum+7zzlJRuGqKIS
FS2O6lEohhyLSv/PCbUCQHWoaM6cYgYVGIugtI/bQTPLFnK0JXZOW6KINj8IQil4
ktYD1mqvPPOoThl5q08dsUDdM9qWy4wFttJRVxlc7qw=
-----END RSA PRIVATE KEY-----';

    public function encrypt($data)
    {
        if (openssl_public_encrypt($data, $encrypted, $this->pubkey))
            $data = base64_encode($encrypted);
        else
            throw new Exception('Unable to encrypt data. Perhaps it is bigger than the key size?');

        return $data;
    }

    public function decrypt($data)
    {
        if (openssl_private_decrypt(base64_decode($data), $decrypted, $this->privkey))
            $data = $decrypted;
        else
            $data = '';

        return $data;
    }
}

require_once("sip2.class.php");
$mysip = new sip2;

$post = file_get_contents('php://input');

//echo "POST: ".$post."<br/><br/>";
$json_data = json_decode($post);

$config = json_decode(file_get_contents('../conf/'.$json_data->custid.'.json'));

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

if (isset($response["variable"]["BL"][0]) && isset($response["variable"]["CQ"][0]) && ($response["variable"]["CQ"][0] == "Y") && ($response["variable"]["BL"][0] == "Y")) {
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
