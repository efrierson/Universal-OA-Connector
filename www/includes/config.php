<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    $post = file_get_contents('php://input');
    $json_data = json_decode($post);

    //echo var_export($json_data,TRUE);
    $custid = $json_data->custid;
    $filename = "../../conf/".$custid.".json";
    $filewrite = file_put_contents($filename, $post);
    $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $actual_link = str_replace("includes/config.php","login.php?organization=".$custid,$actual_link);
    
    echo "<h2>Callback URL (put this into the OpenAthens Connector configuration)</h2><p>".$actual_link."</p>";
    echo "<h2>Need to configure branding?</h2><p><a href='setup-branding.php?organization=".$custid."' target='_blank'>Configure Branding for ".$custid."</a></p>";
    //echo "<strong>File Created / Updated:</strong> <br/><br/>".file_get_contents($filename);
?>
