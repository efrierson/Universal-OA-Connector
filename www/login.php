<?php
session_start();

//If a state variable was passed, need to do oauth login method
if (isset($_GET['state'])){

  //make sure we did get a code back
  if (isset($_GET['code'])){

    $code = $_GET['code'];

    $encodestate = urlencode($_GET['state']);
    header("location: oauthlogin.php?code=".$code."&state=".$encodestate);

  }

  else{
    die("Code was not provided.");
  }
}

if ((!isset($_GET['organization']))) {
    die("Organization ID not set.");
}

$org = $_GET['organization'];

if (!(file_exists('../conf/'.$org.'.json'))) {
    die("Organization ID not found.");
}

$config = json_decode(file_get_contents('../conf/'.$org.'.json'));

$type = $config->type;

if($type == "sierraoauth"){
  require_once("includes/encryption.php");
  $codex = new MyEncryption();

  $redirecturl = $config->redirect;

  if (!isset($_GET['returnData'])) {
      die("ReturnData not set.");
  }
  if (isset($_GET['verbose']) && ($_GET['verbose'] == "Y")){
    $verbose = "Y";
  }
  else{
    $verbose = "N";
  }
  //Build state as json object, then encrypt to send as a variable
  $statearray = array();
  $statearray['org'] = $org;
  $statearray['returnData'] = $_GET['returnData'];
  $statearray['verbose'] = $verbose;
  $statejson = json_encode($statearray);
  $basejson = $codex->encrypt($statejson);

  //must encode any + signs with %2B to properly read later
  $urljson = rawurlencode(str_replace("+","%2B",$basejson));

  //navigate to their login site to get code
  header("location: ".$redirecturl."&state=".$urljson."&response_type=code");

}

if (!isset($_GET['returnData'])) {
    die("ReturnData not set.");
}
if (!(file_exists('../conf/'.$_GET['organization'].'-branding.json'))) {
    $branding = json_decode(file_get_contents('../conf/default-branding.json'));
} else {
    $branding = json_decode(file_get_contents('../conf/'.$_GET['organization'].'-branding.json'));
}
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
