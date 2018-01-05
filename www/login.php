<?php
session_start();
if ((!isset($_GET['organization'])) || (!isset($_GET['returnData']))) {
    die("Organization ID or returnData not set.");
}
if (!(file_exists('../conf/'.$_GET['organization'].'.json'))) {
    die("Organization ID not found.");
}

$config = json_decode(file_get_contents('../conf/'.$_GET['organization'].'.json'));

if (!(file_exists('../conf/'.$_GET['organization'].'-branding.json'))) {
    $branding = json_decode(file_get_contents('../conf/default-branding.json'));    
} else {
    $branding = json_decode(file_get_contents('../conf/'.$_GET['organization'].'-branding.json'));
}

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
        <?php
            if (strlen($branding->logo) > 0) {
            ?>
                <div id="logo">
                    <img src="<?php echo $branding->logo; ?>" />
                </div>
            <?php
            }
            if (strlen($branding->titletext) > 0) {
            ?>
            <h2><?php echo $branding->titletext; ?></h2>
            <?php
            }
            ?>
            <input type="hidden" id="custid" value="<?php echo $_GET['organization']; ?>" />
            <input type="hidden" id="returnData" value="<?php echo $_GET['returnData']; ?>" />
            <input type="hidden" id="type" value="<?php echo $type; ?>" />
            <?php
            if (strlen($branding->barcodelabel) > 0) {
                echo '<span class="labelfor">'.$branding->barcodelabel.'</span><br />';
            }
            if (strlen($branding->barcodeplaceholder) > 0) {
                $barcodeplaceholder = $branding->barcodeplaceholder;
            } else {
                $barcodeplaceholder = "";
            }
            ?>
            <input class="login-text" type="text" id="login-un" placeholder="<?php echo htmlentities($barcodeplaceholder); ?>" /><br />
            <?php
            if (strlen($branding->pinlabel) > 0) {
                echo '<span class="labelfor">'.$branding->pinlabel.'</span><br />';
            }
            if (strlen($branding->pinplaceholder) > 0) {
                $pinplaceholder = $branding->pinplaceholder;
            } else {
                $pinplaceholder = "";
            }
            ?>
            <input class="login-text" type="password" id="login-pw" placeholder="<?php echo htmlentities($pinplaceholder); ?>" /><br />
            <div id="warning"></div>
            <?php
            if (strlen($branding->loginbutton) > 0) {
                $loginbutton = $branding->loginbutton;
            } else {
                $loginbutton = "Login";
            }
            ?>
            <button onclick="oalogin();"><?php echo $loginbutton; ?></button>
        </div>
        <?php if (strlen($branding->helptext) > 0) {
            echo '<div id="helptext">'.$branding->helptext.'</div>';
        } ?>
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
    <script type="text/javascript">
        $(".login-text").on('keyup', function (e) {
            if (e.keyCode == 13) {
                oalogin();
            }
        });
    </script>
</html>
