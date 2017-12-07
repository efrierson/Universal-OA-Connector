<?php
    require_once("../includes/encryption.php");
    $codex = new MyEncryption();
    $json_response = array();
    if (!(file_exists('../../conf/'.$_GET['organization'].'.json'))) {
        $json_response["status"] = "0";
        die (json_encode($json_response));
    }
    $json_config = json_decode(file_get_contents('../../conf/'.$_GET['organization'].'.json'));
    if (isset($json_config->un)) {
        $json_config->un = $codex->decrypt($json_config->un);
        $json_config->pw = $codex->decrypt($json_config->pw);
    }
    $json_response = json_encode($json_config);
    echo $json_response;
?>