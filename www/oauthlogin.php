<?php
session_start();
require_once("includes/encryption.php");
$codex = new MyEncryption();
if ((!isset($_GET['state']))) {
    die("State was not sent.");
}

if ((!isset($_GET['code']))) {
    die("Code not set.");
}

//decode state variable, then decrypt and create a PHP object
$state = rawurldecode($_GET['state']);
$decodestate = base64_decode($state);
if ($decodestate == "" || $decodestate == undefined){
  die("There was an error with your state value.  Please send this error to your library.");
}
$stateobj = json_decode($decodestate);

//set variables, then send request to driver
$code = $_GET['code'];
$org = $stateobj->org;
$returnData = $stateobj->returnData;
$verbose = $stateobj->verbose;

if (!(file_exists('../conf/'.$org.'.json'))) {
    die("Organization ID not found.");
}

$config = json_decode(file_get_contents('../conf/'.$org.'.json'));

$type = $config->type;

// Function to get the client`s IP address
function getIPAddress()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'] ))
        $ip=$_SERVER['HTTP_CLIENT_IP'];
    elseif (!empty($_SERVER ['HTTP_X_FORWARDED_FOR'] ))
        $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    else
        $ip=$_SERVER['REMOTE_ADDR'];
    return $ip;
}
//Check if the ip address is valid
function checkIP(){
        //Get the ip
        $ip = getIPAddress();
        //Check if it belongs to the EDS range or if it is an INTRANET/NAT IP address
        if ( ( !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) || substr($ip, 0, 13 ) == "140.234.255.9" || substr($ip, 0, 13 ) == "140.234.253.9" || substr($ip, 0, 13 ) == "192.231.246.6") && filter_var($ip, FILTER_VALIDATE_IP) ) {
            return true;
        }
        else{
            return false;
        }
}
 ?>
 <html>
     <head>
 <meta http-equiv="content-type" content="text/html; charset=utf-8" />
 <script type="text/javascript" src="includes/jquery-3.2.1.min.js"></script>
 <script type="text/javascript" src="includes/jsencrypt.min.js"></script>
 <script type="text/javascript" src="includes/oauthlogin.js"></script>
 <link rel="stylesheet" href="includes/login.css" />
</head>
</body>
<p><b>Processing Your Request...</b></p>
<div id="warning"></div>
 <input type="hidden" id="custid" value="<?php echo $org; ?>" />
 <input type="hidden" id="returnData" value="<?php echo $returnData; ?>" />
 <input type="hidden" id="type" value="<?php echo $type; ?>" />
<input type="hidden" id="code" value="<?php echo $code; ?>" />
<?php
if ($verbose == "Y" && checkIP() == true) {
?>
<div id="redirect">
    <a href="redirect.php?verbose=Y">Verbose Redirect</a>
</div>
<div id="results">
    <strong>SIP2 Response will appear here.</strong>
</div>
<?php
}
?>
<script>
oauthlogin();
</script>
</body>