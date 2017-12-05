<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Polaris {
    
    protected $ws_host;
    protected $ws_app;
    protected $ws_api_id;
    protected $ws_api_key;
    protected $ws_requestingorgid;

    public function init() {

        $this->ws_host    = 'quantum.polarislibrary.com';
        $this->ws_app     = '100';
        $this->ws_api_id  = 'ebscoEBSCOPDN';
        $this->ws_api_key = 'cea68e12-3d81-4fec-b88f-3cc900ffb1a0';
        $this->ws_requestingorgid    = '1';
        $this->defaultPickUpLocation = null;
    }
    
    function makeRequest($api_query, $http_method = "GET", $patronpassword = "", $json = false) {
        // auth has to be in GMT, otherwise use config-level TZ
        $site_config_TZ = date_default_timezone_get();
        //date_default_timezone_set('GMT');
        $date = date("D, d M Y H:i:s T");

        $url = $api_query;
        $signature_text = $http_method . $url . $date . $patronpassword;
        $signature = base64_encode(
            hash_hmac('sha1', $signature_text, $this->ws_api_key, true)
        );
        $auth_token = "PWS {$this->ws_api_id}:$signature";
        echo $auth_token;
        $http_headers = [
            "Content-type: application/json",
            "Accept: application/json",
            "PolarisDate: $date",
            "Authorization: $auth_token"
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
        
        $server_output = curl_exec($ch);

        $server_output = curl_error($ch);
    
        curl_close ($ch);
        
        return $server_output;
    }
}

echo "<br /><br />1<br /><br />";
$my_polaris = new Polaris();

echo "<br /><br />2<br /><br />";
$my_polaris->init();

echo "<br /><br />3<br /><br />";
$response = $my_polaris->makeRequest("http://quantum.polarislibrary.com/PAPIService/REST/public/v1/1033/100/1/search/bibs/keyword/kw?q=book&sortby=PDTI&bibsperpage=2");

echo "<br /><br />4<br /><br />";
echo $response;
?>