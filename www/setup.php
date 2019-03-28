<html>
    <head>
        <title>Connector Configuration</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <script type="text/javascript" src="includes/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="includes/jsencrypt.min.js"></script>
        <script type="text/javascript" src="includes/jquery-ui.js"></script>
        <script>
            $( function() {
                $( document ).tooltip();
            } );
        </script>
        <script type="text/javascript" src="includes/config.js"></script>
        <link rel="stylesheet" href="includes/login.css" />
    </head>
    <body>
        <div id="login">
            <div id="custid-section">
                <h2>Customer/Organization ID</h2>
                <input type="text" id="custid" placeholder="OpenAthens CustID" /> <img src="includes/info.png" title="It is highly recommended you use the customer's OpenAthens account ID." /><br /><br />
                <button onclick="oaloadconfig();">Load Existing Configuration</button>
            </div>
            <div id="ils-type">
                <h2>ILS Type</h2>
                <select id="type" onchange="showConfig();">
                    <option value="none" selected />Select One...</option>
                    <option value="horizon" />Horizon</option>
                    <option value="polaris" />Polaris</option>
                    <option value="sierra" />Sierra</option>
                    <option value="tlc-carl-sip2" />TLC Carl.x - SIP2</option>
                </select> <img src="includes/info.png" title="If you don't see it here, we don't have a connector for it.  Please ask an LSE!" /><br />
            </div>
            <div class="ils-section" id="tlc-carl-sip2" style="display:none;">
                <h2>SIP2 Information</h2>
                <span class="labelfor">SIP2 Username for Application</span><br />
                <input type="text" id="sip2-app-un" placeholder="Username" /> <img src="includes/info.png" title="Provided by Library or TLC" /><br />

                <span class="labelfor">SIP2 Password for Application</span><br />
                <input type="password" id="sip2-app-pw" placeholder="Password" /> <img src="includes/info.png" title="Provided by Library or TLC" /><br />

                <span class="labelfor">SIP2 ILS Location ID</span><br />
                <input type="text" id="sip2-location" placeholder="Location" /> <img src="includes/info.png" title="Provided by Library or TLC" /><br />

                <span class="labelfor">SIP2 ILS Hostname / IP Address</span><br />
                <input type="text" id="sip2-hostname" placeholder="localhost" /> <img src="includes/info.png" title="Provided by LSE Team. LSE team will need IP addresses and ports to the TLC SSL Tunnel for this customer." /><br />

                <span class="labelfor">SIP2 ILS Port Number</span><br />
                <input type="text" id="sip2-port" placeholder="Port (e.g., 6001)" /> <img src="includes/info.png" title="Provided by LSE Team. LSE team will need IP addresses and ports to the TLC SSL Tunnel for this customer." /><br />

            </div>
            <div class="ils-section" id="polaris" style="display:none;">
                <h2>Polaris Information</h2>
                <span class="labelfor">PAPI Access ID</span><br />
                <input type="text" id="polaris-access-id" placeholder="Username" /> <img src="includes/info.png" title="Provided by Library or III" /><br />

                <span class="labelfor">PAPI Access Key</span><br />
                <input type="password" id="polaris-access-key" placeholder="Password" /> <img src="includes/info.png" title="Provided by Library or III" /><br />

                <span class="labelfor">Polaris ILS Domain or IP Address</span><br />
                <input type="text" id="polaris-hostname" placeholder="Domain" /> <img src="includes/info.png" title="Provided by Library or III" /><br />
            </div>
            <div class="ils-section" id="sierra" style="display:none;">
                <h2>Sierra Information</h2>
                <span class="labelfor">Sierra API AuthKey</span><br />
                <input type="text" id="sierra-authkey" placeholder="authkey" /> <img src="includes/info.png" title="Provided by Library or III" /><br />

                <span class="labelfor">Sierra API AuthSecret</span><br />
                <input type="password" id="sierra-authsecret" placeholder="authsecret" /> <img src="includes/info.png" title="Provided by Library or III" /><br />

                <span class="labelfor">Sierra Full API URL</span><br />
                <input type="text" id="sierra-hostname" placeholder="https://opac.example.edu.tt/iii/sierra-api/v5/" /> <img src="includes/info.png" title="Provided by Library or III" /><br />

                <span class="labelfor">OPTIONAL: Sierra Patron Blocked Message</span><br />
                <input type="text" id="sierra-blockedmessage" placeholder="Patron status is blocked.  Please contact the library for assistance." /> <img src="includes/info.png" title="OPTIONAL: Provided by Library if Needed" /><br />

                <span class="labelfor">OPTIONAL: Sierra Invalid Patron Message</span><br />
                <input type="text" id="sierra-invalidmessage" placeholder="Invalid Username or Barcode." /> <img src="includes/info.png" title="OPTIONAL: Provided by Library if Needed" /><br />

            </div>
            <div class="ils-section" id="horizon" style="display:none;">
                <h2>Horizon Information</h2>
                <span class="labelfor">Horizon ClientID</span><br />
                <input type="text" id="horizon-client-id" placeholder="clientID" /> <img src="includes/info.png" title="Provided by Library or SIRSI Dynix" /><br />

                <span class="labelfor">Horizon Domain</span><br />
                <input type="text" id="horizon-hostname" placeholder="server.sirsidynix.net/libcode_ilsws" /> <img src="includes/info.png" title="Provided by Library or SIRSI Dynix" /><br />
            </div>
            <div id="oa-section" class="openAthens">
                <h2>OpenAthens Information</h2>
                <span class="labelfor">OpenAthens API Key<br />
                <input type="text" id="oaapikey" placeholder="e.g., 17dfaf90-befb-4d83-81ad-d31c59c8b863" /> <img src="includes/info.png" title="Found in OpenAthens Admin, under the Management menu, in API keys.  If there isn't one there, create one." /></span><br />

                <span class="labelfor">OpenAthens Connection ID<br />
                <input type="text" id="oaconnectionid" placeholder="OA Connection ID" /> <img src="includes/info.png" title="Found in OpenAthens Admin, under the Management menu, in Connections.  Click on the connector you are configuring on the left, and you will find the Connection ID.  If there is no connector, create one, and fill in a dummy Callback URL.  The correct Callback URL will be provided to you once you submit this form." /></span><br />

                <span class="labelfor">OpenAthens Connection URI<br />
                <input type="text" id="oaapiendpoint" placeholder="Full API Endpoint (include HTTPS://)" /> <img src="includes/info.png" title="Found in OpenAthens Admin, under the Management menu, in Connections.  Click on the connector you are configuring on the left, and you will find the Connection URI.  If there is no connector, create one, and fill in a dummy Callback URL.  The correct Callback URL will be provided to you once you submit this form." /></span><br />
            </div>
            <button onclick="oaconfig();">Submit</button>
        </div>
        <div id="results">
            <strong>Config Tool Response will appear here.</strong>
        </div>

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