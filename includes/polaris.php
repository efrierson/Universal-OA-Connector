<?php
    session_start();
    $_SESSION["valid"] = "N";
    $_SESSION['returnData'] = "";
    $_SESSION['fullname'] = "";
    $_SESSION['uid'] = "";
    $_SESSION['custid'] = "";
    require_once("encryption.php");

    $debug = "";

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once("encryption.php");

    $post = file_get_contents('php://input');

    ////echo "POST: ".$post."<br/><br/>";
    $user_data = json_decode($post);

    $config_data = json_decode(file_get_contents('../conf/'.$user_data->custid.'.json'));

    $encrypted_patron_un = $user_data->un;
    $encrypted_patron_pw = $user_data->pw;

    $codex = new MyEncryption();

    //$codex->decrypt($config_data->un) to decrypt un/pw
    //print_r($user_data);
    $polarisAccessID = $codex->decrypt($config_data->un);
    $PAPIKey = $codex->decrypt($config_data->pw);
    $Pdomain = $config_data->hostname;
    $patronID = $codex->decrypt($user_data->un);
    $patronpassword = $codex->decrypt($user_data->pw);
    $returnData = $user_data->rd;
    //$patronID = "21668012345678";
    //$patronpassword = "1234";

    if ($patronID != "" && $patronpassword != ""){
      getUser($polarisAccessID,$PAPIKey,$Pdomain,$patronpassword,$patronID,$returnData);
    }
    else{
      $debug .= "BAD USERNAME OR PASSWORD";
    }

    function getHash($accessID, $accessKey, $httpMethod, $uri, $pwd,$pid,$httpDate)
    {


        //
        // Display input parameters
        //
        //echo "<strong>GENERATING HASH...</strong><br />";
        //echo "<strong>PAPI Access ID:</strong> " . $accessID . "<br />";
        //echo "<strong>PAPI Access Key:</strong> " . $accessKey . "<br />";
        //echo "<strong>HTTP Method:</strong> " . $httpMethod . "<br />";
        //echo "<strong>URI:</strong> " . $uri . "<br />";
        //echo "<strong>Date:</strong> " . $httpDate . "<br />";
        //echo "<strong>Password:</strong> " . $pwd . "<br /><br />";

        //
        // Build combined string used to create hash
        //
        $concat = $httpMethod . $uri . $httpDate . $pwd;
        //echo "<strong>Combined String:</strong><br/>";
        //echo $concat . "<br />";

        //
        // Create the hash using SHA1, the combined string and the PAPI Access Key associated with the PAPI Access ID
        //
        $sha1_sig = base64_encode(hash_hmac('sha1', $concat, $accessKey, true));

        //
        // Display hash used for PAPI
        //
        //echo "<br /><strong>Hash created using PAPI Access Key:</strong> " . $sha1_sig . "<br />";
        return $sha1_sig;
      }
      function getUser($polarisAccessID,$PAPIKey,$Pdomain,$patronpassword,$patronID,$returnData){
        $connector_response = [];
        //echo "<strong>Connect Patron ".$patronID."</strong><br/><br/>";

        $targetURL = "https://".$Pdomain."/PAPIService/REST/public/v1/1033/100/1/authenticator/patron/";
        $method = 'POST';
        $httpDate = gmdate('r'); //<---Current call for GMT date/time in RFC-1123

        $sha1_sig = getHash($polarisAccessID,
                $PAPIKey,
                $method,
                 $targetURL,
                "",$patronID,$httpDate);
        //
        // Display HTTP headers
        //
        //echo "<br/><strong>Creating the HTTP Headers:</strong><br/>";
        //echo "&nbsp;&nbsp;&nbsp;- The PolarisDate must be the same as the date used in the combined string." . "<br />";
        //echo "&nbsp;&nbsp;&nbsp;- The Authorization header is created using the PAPI Access ID and the hash." . "<br />";
        //echo "<br/><strong>HTTP Headers:</strong><br/>";
        //echo "PolarisDate: " . $httpDate . "<br />";
        //echo "Authorization: PWS " . $polarisAccessID . ":" . $sha1_sig . "<br /><br />";


        //
        // Connect to PAPI Service and validate patron
        //
        $xml = "<PatronAuthenticationData><Barcode>".$patronID."</Barcode><Password>".$patronpassword."</Password></PatronAuthenticationData>";
        //echo "<textarea>XML Request: ".$xml."</textarea>";
        $ch = curl_init();
        $headers = array(
                "Date: ". $httpDate,
                "Authorization: PWS " . $polarisAccessID . ":" . $sha1_sig,
                "Content-Type: text/xml"
                );


        //curl_setopt($ch, CURLOPT_HTTPGET, 1); //See here for the CURLOPT parameters - http://php.net/manual/en/function.curl-setopt.php
        curl_setopt($ch, CURLOPT_URL, $targetURL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //
        // Display output data
        //
        //echo "<br /><strong>Result from PAPI Service:</strong><br/>";
        $output = curl_exec($ch);
        //echo "<textarea height='200' width='100%'>".$output."</textarea>";
        if($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            //echo "cURL error ({$errno}):\n {$error_message}";
        }
        $xmlresult=simplexml_load_string($output);
        //echo "<br>Access Token: ".$xmlresult->AccessToken;
        $polarisPatronID = $xmlresult->PatronID;
        curl_close($ch);
        if ($xmlresult->AccessToken > ""){
          $patronData = [];
          $patronData['patrondata'] = getPatronData($polarisAccessID,$PAPIKey,$Pdomain,$patronpassword,$patronID,$polarisPatronID);
          $connector_response['valid'] = "Y";
          $connector_response['returnData'] = $returnData;
          //print_r($patronData['patrondata']);
          $fullName = $patronData['patrondata'][0]->PatronBasicData->NameFirst . " " . $patronData['patrondata'][0]->PatronBasicData->NameLast;
          $connector_response['fullName'] = $fullName;
          $_SESSION['valid'] = "Y";
          $_SESSION['uid'] = $patronID;
          $_SESSION['fullname'] = $fullName;
          $_SESSION['returnData'] = $returnData;
        }
        else {
          $_SESSION['valid'] = "N";
          $_SESSION['uid'] = $patronID;
          $_SESSION['returnData'] = $returnData;
          $connector_response['valid'] = "N";
          $connector_response['message'] = (string)$xmlresult->ErrorMessage;

          $connector_response['returnData'] = $returnData;
        }
        $connector_response = json_encode($connector_response);
        echo $connector_response;
    }
function getPatronData($polarisAccessID,$PAPIKey,$Pdomain,$patronpassword,$patronID,$polarisPatronID){
  $patron_response = [];

  //echo "<br \><br \><strong style='color:green;'>Good Patron Data, Starting Lookup...</strong><br \><br \>";

  $httpDate = gmdate('r'); //<---Current call for GMT date/time in RFC-1123
  $method = "GET";
  $targetURL = "https://".$Pdomain."/PAPIService/REST/public/v1/1033/100/1/patron/".$patronID."/basicdata";

  $sha1_sig = getHash($polarisAccessID,
          $PAPIKey,
          $method,
           $targetURL,
          $patronpassword,$patronID,$httpDate);


  //echo "<strong>Using Target URL: </strong>".$targetURL."<br>";

  $ch = curl_init();

  $headers = array(
          "Date: ". $httpDate,
          "Authorization: PWS " . $polarisAccessID . ":" . $sha1_sig
          );


  //curl_setopt($ch, CURLOPT_HTTPGET, 1); //See here for the CURLOPT parameters - http://php.net/manual/en/function.curl-setopt.php
  curl_setopt($ch, CURLOPT_URL, $targetURL);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  //echo "<br /><strong>Result from Call to Patron Data:</strong><br/>";
  $output = curl_exec($ch);
  //echo "<textarea height='200' width='100%'>".$output."</textarea>";
  if($errno = curl_errno($ch)) {
      $error_message = curl_strerror($errno);
      //echo "cURL error ({$errno}):\n {$error_message}";
  }
  //$xmlresult=simplexml_load_string($output);

  curl_close($ch);
  $xmlresult=simplexml_load_string($output);
  $patron_response[] = $xmlresult;
  return $patron_response;
}
    ?>