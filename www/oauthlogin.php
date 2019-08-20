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
$decodestate = $codex->decrypt($state);
$stateobj = json_decode($decodestate);

$code = $_GET['code'];
$org = $stateobj->org;
$returnData = $stateobj->returnData;
$verbose = $stateobj->verbose;

if (!(file_exists('../conf/'.$org.'.json'))) {
    die("Organization ID not found.");
}

$config = json_decode(file_get_contents('../conf/'.$org.'.json'));

$type = $config->type;
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
 <input type="hidden" id="custid" value="<?php echo $org; ?>" />
 <input type="hidden" id="returnData" value="<?php echo $returnData; ?>" />
 <input type="hidden" id="type" value="<?php echo $type; ?>" />
<input type="hidden" id="code" value="<?php echo $code; ?>" />
<?php
if ($verbose == "Y") {
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