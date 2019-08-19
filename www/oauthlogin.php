<?php

session_start();
if ((!isset($_GET['organization']))) {
    die("Organization ID not set.");
}
if ((!isset($_GET['code']))) {
    die("Code not set.");
}
if ((!isset($_GET['returnData']))) {
    die("ReturnData not set.");
}
if (!(file_exists('../conf/'.$_GET['organization'].'.json'))) {
    die("Organization ID not found.");
}

$config = json_decode(file_get_contents('../conf/'.$_GET['organization'].'.json'));

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
 <input type="hidden" id="custid" value="<?php echo $_GET['organization']; ?>" />
 <input type="hidden" id="returnData" value="<?php echo $_GET['returnData']; ?>" />
 <input type="hidden" id="type" value="<?php echo $type; ?>" />
<input type="hidden" id="code" value="<?php echo $_GET['code']; ?>" />
<?php
if (isset($_GET['verbose']) && ($_GET['verbose'] == "Y")) {
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