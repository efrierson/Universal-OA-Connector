<?php
session_start();
if ((!isset($_GET['organization'])) || (!isset($_GET['returnData']))) {
    die("Organization ID or returnData not set.");
}
$config = json_decode(file_get_contents('../conf/'.$_GET['organization'].'.json'));
$type = $config->type;

?>
<html>
    <head>
        <title>Login to Library Resources</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <script type="text/javascript" src="includes/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="includes/jsencrypt.min.js"></script>
        <script type="text/javascript" src="includes/login.js"></script>
        <link rel="stylesheet" href="includes/login.css" />
    </head>
    <body>
        <div id="login">
            <div id="warning"></div>
            <input type="hidden" id="custid" value="<?php echo $_GET['organization']; ?>" />
            <input type="hidden" id="returnData" value="<?php echo $_GET['returnData']; ?>" />
            <input type="hidden" id="type" value="<?php echo $type; ?>" />
            <input type="text" id="login-un" placeholder="Barcode / Username" /><br />
            <input type="password" id="login-pw" placeholder="Password" /><br />
            <button onclick="oalogin();">Login</button>
        </div>
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

        <div style="display:none;">
            <label for="pubkey">Public Key</label><br/>
            <textarea id="pubkey" rows="15" style="width:100%" readonly="readonly">-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDLa7lVQ9kYoqrrqPIUv2dhDvyg
hraW4lgquGOLM59+G03F65uSXtom+lOVt/Wam2ROtrdW/JOpIIk7KUuk+byBBO1a
e0YZof7Q5YHIRGvMbLC2Z+fbTd/a0fp4SY3HZH5GDv8dcxJR8ZhSMBhy0x+VaLdO
M68I/cdG7IQrXDXXYQIDAQAB
-----END PUBLIC KEY-----</textarea>
        </div>
    </body>
</html>
