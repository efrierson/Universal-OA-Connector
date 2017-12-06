<html>
    <head>
        <title>Branding Configuration</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <script type="text/javascript" src="includes/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="includes/jsencrypt.min.js"></script>
        <script type="text/javascript" src="includes/config.js"></script>
        <link rel="stylesheet" href="includes/login.css" />
    </head>
    <body>
        <div id="login">
            <div id="custid-section">
                <h2>Customer/Organization ID</h2>
                <input type="text" id="custid" placeholder="CustID" /><br />
            </div>
            <div id="branding-type">
                <h2>Branding Mode</h2>
                <select id="type" onchange="showConfig();">
                    <option value="none" selected />Select One...</option>
                    <option value="basic" />Basic</option>
                    <option value="custom" />Custom</option>
                </select><br />
            </div>
            <div class="branding-section" id="basic" style="display:none;">
                <h2>Settings</h2>
                <span class="labelfor">Logo</span><br />
                <input type="text" id="logo" placeholder="Full URL to Library Logo" /><br />

                <span class="labelfor">Title / Welcome Text</span><br />
                <input type="text" id="titletext" placeholder="e.g. Login to Library Resources" /><br />

                <span class="labelfor">Label for Barcode/Username</span><br />
                <input type="text" id="barcode-label" placeholder="e.g. Barcode" /><br />

                <span class="labelfor">Example for Barcode/Username (e.g. placeholder text)</span><br />
                <input type="text" id="barcode-placeholder" placeholder="e.g. Enter your 10-digit barcode here" /><br />
                
                <span class="labelfor">Label for PIN/Password Box</span><br />
                <input type="text" id="pin-label" placeholder="e.g. PIN Number" /><br />

                <span class="labelfor">Example for PIN/Password Box</span><br />
                <input type="text" id="pin-placeholder" placeholder="e.g. Enter your PIN here" /><br />

                <span class="labelfor">Login Button Text</span><br />
                <input type="text" id="login-button" placeholder="e.g. Login" /><br />

                <span class="labelfor">HTML for More Information Area</span><br />
                <textarea id="helptext" placeholder="Need help? Contact us at &#x3C;a href=&#x22;mailto:fake@mylibrary.co&#x22;&#x3E;fake@mylibrary.co&#x3C;/a&#x3E;!"></textarea><br />
            </div>
            <div class="branding-section" id="custom" style="display:none;">
                <h2>Custom Login Screen</h2>
                <p><em>Not available yet!</em></p>
            </div>
            <button onclick="brandingconfig();">Submit</button>
        </div>
        <div id="results">
            <strong>Branding Tool Response will appear here.</strong>
        </div>
    </body>
</html>