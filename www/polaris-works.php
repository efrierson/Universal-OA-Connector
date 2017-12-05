<!--
//***************************************************************************
// PatronValidate.php
//***************************************************************************
//
// Description:
//      Sample PHP file to show how to build PAPI hash signature and call
//      the PatronValidate method.  User must change the test connection data
//      PAPI keys and IDs.
//
// Created: 
//      12-13-2011 Jeffrey Young 
//
// Modified:
//      10-10-2011 Richard Breiten
//          Corrected curl_setopt to use CURLOPT_HTTPGET
//          Use gmdate()
//
//***************************************************************************
-->
<html>
    <head>
        <title>PHP - Polaris API - Validate Patron Test</title>
    </head>
 
    <body>
     
    <strong>Bib 88</strong><br/>
    <br/>
     
    <?php testHash('ebscoEBSCOPDN',                     // Polaris Access ID
            'cea68e12-3d81-4fec-b88f-3cc900ffb1a0',     // Polaris Access Key
            'GET',
            'https://quantum.polarislibrary.com/PAPIService/REST/public/v1/1033/100/1/bib/88',
            ''); ?> 
 
    <br />
    <br />
 
    <?php
 
    function testHash($accessID, $accessKey, $httpMethod, $uri, $pwd) 
    {
        $httpDate = gmdate('r'); //<---Current call for GMT date/time in RFC-1123
        //
        // Display input parameters
        //
        echo "<strong>PAPI Access ID:</strong> " . $accessID . "<br />";
        echo "<strong>PAPI Access Key:</strong> " . $accessKey . "<br />";
        echo "<strong>HTTP Method:</strong> " . $httpMethod . "<br />";
        echo "<strong>URI:</strong> " . $uri . "<br />";
        echo "<strong>Date:</strong> " . $httpDate . "<br />";
        echo "<strong>Password:</strong> " . $pwd . "<br /><br />";
 
        //
        // Build combined string used to create hash
        //
        $concat = $httpMethod . $uri . $httpDate . $pwd; 
        echo "<strong>Combined String:</strong><br/>";
        echo $concat . "<br />";
 
        //
        // Create the hash using SHA1, the combined string and the PAPI Access Key associated with the PAPI Access ID
        //
        $sha1_sig = base64_encode(hash_hmac('sha1', $concat, $accessKey, true));
     
        //
        // Display hash used for PAPI
        //
        echo "<br /><strong>Hash created using PAPI Access Key:</strong> " . $sha1_sig . "<br />";
         
        //
        // Display HTTP headers
        //
        echo "<br/><strong>Creating the HTTP Headers:</strong><br/>";
        echo "&nbsp;&nbsp;&nbsp;- The PolarisDate must be the same as the date used in the combined string." . "<br />";
        echo "&nbsp;&nbsp;&nbsp;- The Authorization header is created using the PAPI Access ID and the hash." . "<br />";
        echo "<br/><strong>HTTP Headers:</strong><br/>";
        echo "PolarisDate: " . $httpDate . "<br />";
        echo "Authorization: PWS " . $accessID . ":" . $sha1_sig . "<br /><br />";
 
 
        //
        // Connect to PAPI Service and validate patron
        //
        $ch = curl_init();
        $headers = array( 
                "PolarisDate: ". $httpDate, 
                "Authorization: PWS " . $accessID . ":" . $sha1_sig
                ); 
 
 
        curl_setopt($ch, CURLOPT_HTTPGET, 1); //See here for the CURLOPT parameters - http://php.net/manual/en/function.curl-setopt.php
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
 
        //
        // Display output data
        //
        echo "<br /><strong>Result from PAPI Service:</strong><br/>";
        $output = curl_exec($ch);
 
        return ($sha1_sig);
    }
 
    ?>
 
</body>
</html>