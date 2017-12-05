<html>
    <head>
        <title>Connector Configuration</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <script type="text/javascript" src="includes/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="includes/jsencrypt.min.js"></script>
        <script type="text/javascript" src="includes/login.js"></script>
        <link rel="stylesheet" href="includes/login.css" />
    </head>
    <body>
        <div id="login">
            <div id="custid-section">
                <h2>Customer/Organization ID</h2>
                <input type="text" id="custid" placeholder="CustID" /><br />
            </div>
            <div id="ils-type">
                <h2>ILS Type</h2>
                <select id="type" onchange="showConfig();">
                    <option value="none" selected />Select One...</option>
                    <option value="polaris" />Polaris</option>
                    <option value="tlc-carl-sip2" />TLC Carl.x - SIP2</option>
                </select><br />
            </div>
            <div class="ils-section" id="tlc-carl-sip2" style="display:none;">
                <h2>SIP2 Information</h2>
                <span class="labelfor">SIP2 Username for Application</span><br />
                <input type="text" id="sip2-app-un" placeholder="Username" /><br />

                <span class="labelfor">SIP2 Password for Application</span><br />
                <input type="password" id="sip2-app-pw" placeholder="Password" /><br />

                <span class="labelfor">SIP2 ILS Hostname / IP Address</span><br />
                <input type="text" id="sip2-hostname" placeholder="Hostname" /><br />
                
                <span class="labelfor">SIP2 ILS Port Number</span><br />
                <input type="text" id="sip2-port" placeholder="Port (e.g., 6001)" /><br />

                <span class="labelfor">SIP2 ILS Location ID</span><br />
                <input type="text" id="sip2-location" placeholder="Location" /><br />
            </div>
            <div class="ils-section" id="polaris" style="display:none;">
                <h2>Polaris Information</h2>
                <span class="labelfor">PAPI Access ID</span><br />
                <input type="text" id="polaris-access-id" placeholder="Username" /><br />

                <span class="labelfor">PAPI Access Key</span><br />
                <input type="password" id="polaris-access-key" placeholder="Password" /><br />

                <span class="labelfor">Polaris ILS Domain or IP Address</span><br />
                <input type="text" id="polaris-hostname" placeholder="Domain" /><br />
            </div>
            <div id="oa-section" class="openAthens">
                <h2>OpenAthens Information</h2>
                <span class="labelfor">OpenAthens API Endpoint</span><br />
                <input type="text" id="oaapiendpoint" placeholder="Full API Endpoint (include HTTPS://)" /><br />
                
                <span class="labelfor">OpenAthens Connection ID</span><br />
                <input type="text" id="oaconnectionid" placeholder="OA Connection ID" /><br />
                
                <span class="labelfor">OpenAthens API Key</span><br />
                <input type="text" id="oaapikey" placeholder="e.g., 17dfaf90-befb-4d83-81ad-d31c59c8b863" /><br />
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