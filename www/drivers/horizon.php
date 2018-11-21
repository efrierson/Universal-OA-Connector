<?php
//https://sdws01.sirsidynix.net/egv_ilsws/rest/standard/loginUser?clientID=OpenAthens&login=21250001505045&password=0447
//https://sdws01.sirsidynix.net/egv_ilsws/rest/standard/lookupMyAccountInfo?clientID=OpenAthens&sessionToken=6ace8758-653b-43f9-8836-72dc9ac65045
    session_start();
    $_SESSION["valid"] = "N";
    $_SESSION['returnData'] = "";
    $_SESSION['fullname'] = "";
    $_SESSION['uid'] = "";
    $_SESSION['custid'] = "";
    require_once("../includes/encryption.php");

    $debug = "";

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $post = file_get_contents('php://input');

    ////echo "POST: ".$post."<br/><br/>";
    $user_data = json_decode($post);

    $config_data = json_decode(file_get_contents('../../conf/'.$user_data->custid.'.json'));

    $encrypted_patron_un = $user_data->un;
    //$encrypted_patron_pw = $user_data->pw;

    $codex = new MyEncryption();

    //$codex->decrypt($config_data->un) to decrypt un/pw
    //print_r($user_data);
    $clientID = $codex->decrypt($config_data->un);
    $hdomain = $config_data->hostname;
    $patronID = $codex->decrypt($user_data->un);
    $patronpassword = $codex->decrypt($user_data->pw);
    $returnData = $user_data->rd;
    $custID = $user_data->custid;
    //$patronID = "21250001505045";
    //$patronpassword = "0447";

    if ($patronID != "" && $patronpassword != ""){
      getUser($clientID,$hdomain,$patronpassword,$patronID,$returnData,$custID);
    }
    else{
      $debug .= "BAD USERNAME OR PASSWORD";
    }
      function getUser($clientID,$hdomain,$patronpassword,$patronID,$returnData,$custID){
        $connector_response = [];
        //echo "<strong>Connect Patron ".$patronID."</strong><br/><br/>";

        $targetURL = "https://".$hdomain."/rest/standard/loginUser?clientID=".$clientID."&login=".$patronID."&password=".$patronpassword;

        //
        // Connect to Horizon Service and validate patron
        //
        $ch = curl_init();


        //curl_setopt($ch, CURLOPT_HTTPGET, 1); //See here for the CURLOPT parameters - http://php.net/manual/en/function.curl-setopt.php
        curl_setopt($ch, CURLOPT_URL, $targetURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //
        // Display output data
        //
        $output = curl_exec($ch);
        if($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
        }
        $xmlresult=simplexml_load_string($output);

        $sessionToken = $xmlresult->sessionToken;

        curl_close($ch);
        if ($xmlresult->sessionToken > ""){
          $patronData = [];
          $patronData['patrondata'] = getPatronData($clientID,$hdomain,$patronpassword,$patronID,$sessionToken);
          $connector_response['valid'] = "Y";
          $connector_response['returnData'] = $returnData;
          //print_r($patronData);
          //echo "</br>Name: ".$patronData['patrondata']['name']."</br>";
          $fullName = $patronData['patrondata']['name'];
          $connector_response['fullName'] = $fullName;
          $_SESSION['valid'] = "Y";
          $_SESSION['uid'] = $patronID;
          $_SESSION['custid'] = $custID;
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
function getPatronData($clientID,$hdomain,$patronpassword,$patronID,$sessionToken){
  $patron_response = [];

  //echo "<br \><br \><strong style='color:green;'>Good Patron Data, Starting Lookup...</strong><br \><br \>";

  $targetURL = "https://".$hdomain."/rest/standard/lookupMyAccountInfo?clientID=".$clientID."&sessionToken=".$sessionToken;

  $ch = curl_init();

  //curl_setopt($ch, CURLOPT_HTTPGET, 1); //See here for the CURLOPT parameters - http://php.net/manual/en/function.curl-setopt.php
  curl_setopt($ch, CURLOPT_URL, $targetURL);
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
  $json = json_encode($xmlresult); //NEW
  $patron_response = json_decode($json,TRUE); //NEW
  return $patron_response;
}
    ?>
