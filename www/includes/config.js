
function oaloadbranding () {
    var custid = $('#custid').val();
    if (custid.length <= 0) {
        alert("Enter in an ID, and try again.");
        return;
    }
    $.ajax({
        url:'includes/loadconfig.php?organization='+custid+'-branding',
        type: 'GET',
        dataType: 'json',
        error: function()
        {
            alert("Sorry, can't find a branding configuration for "+custid);
            return;
        },
        success: function(configfile)
        {
            if (configfile.status) {
                alert("Sorry, can't find a branding configutation for "+custid);
                return;
            }
            $("#logo").val(configfile.logo);
            $("#titletext").val(configfile.titletext);
            $("#barcode-label").val(configfile.barcodelabel);
            $("#barcode-placeholder").val(configfile.barcodeplaceholder);
            $("#pin-label").val(configfile.pinlabel);
            $("#pin-placeholder").val(configfile.pinplaceholder);
            $("#login-button").val(configfile.loginbutton);
            $("#helptext").val(configfile.helptext);
            $("#type").val(configfile.type);
            showConfig();
        }
    });
}

function oaloadconfig () {

    var encrypt = new JSEncrypt();
    encrypt.setPublicKey($('#pubkey').val());

    var custid = $('#custid').val();
    if (custid.length <= 0) {
        alert("Enter in an ID, and try again.");
        return;
    }
    $.ajax({
        url:'includes/loadconfig.php?organization='+custid,
        type: 'GET',
        dataType: 'json',
        error: function()
        {
            alert("Sorry, can't find a configuration for "+custid);
            return;
        },
        success: function(configfile)
        {
            if (configfile.status) {
                alert("Sorry, can't find a configutation for "+custid);
                return;
            }
            $("#oaapiendpoint").val(configfile.oaendpoint);
            $("#oaconnectionid").val(configfile.oaconnectionid);
            $("#oaapikey").val(configfile.oaapikey);
            if (configfile.type == "polaris") {
                $("#polaris-access-id").val(configfile.un);
                $("#polaris-access-key").val(configfile.pw);
                $("#polaris-hostname").val(configfile.hostname);
            }
            if (configfile.type == "sierra") {
                $("#sierra-authkey").val(configfile.un);
                $("#sierra-authsecret").val(configfile.pw);
                $("#sierra-hostname").val(configfile.hostname);
                $("#sierra-blockedmessage").val(configfile.blocked);
                $("#sierra-invalidmessage").val(configfile.invalid);
            }
            if (configfile.type == "horizon") {
                $("#horizon-client-id").val(configfile.un);
                $("#horizon-hostname").val(configfile.hostname);
            }
            if (configfile.type == "tlc-carl-sip2") {
                $("#sip2-app-un").val(configfile.un);
                $("#sip2-app-pw").val(configfile.pw);
                $("#sip2-location").val(configfile.location);
                $("#sip2-hostname").val(configfile.hostname);
                $("#sip2-port").val(configfile.port);
            }
            $("#type").val(configfile.type);
            showConfig();
        }
    });
}

function brandingconfig () {
    $("#results").html('<img src="includes/loading_sm.gif" />');

    var custid = $('#custid').val();
    var logo = $('#logo').val();
    var barcodelabel = $('#barcode-label').val();
    var barcodeplaceholder = $('#barcode-placeholder').val();
    var pinlabel = $('#pin-label').val();
    var pinplaceholder = $('#pin-placeholder').val();
    var titletext = $('#titletext').val();
    var helptext = $('#helptext').val();
    var loginbutton = $('#login-button').val();
    var type = $('#type').val();

    var payload = {type:type,loginbutton:loginbutton,logo:logo,barcodelabel:barcodelabel,barcodeplaceholder:barcodeplaceholder,pinlabel:pinlabel,pinplaceholder:pinplaceholder,helptext:helptext,titletext:titletext,custid:custid};

    var url = "includes/config-branding.php";

    $.ajax({
        type: "POST",
        url: url,
        data: JSON.stringify(payload),
        success: function(data) {
            $("#results").html(data);
        },
        error: function(xhr, status, error) {
            var err = eval("(" + xhr.responseText + ")");
            console.log(err.Message);
        }
    });

}

function oaconfig () {
    $("#results").html('<img src="includes/loading_sm.gif" />');
    var encrypt = new JSEncrypt();
    encrypt.setPublicKey($('#pubkey').val());

    var custid = $('#custid').val();
    var endpoint = $('#oaapiendpoint').val();
    var apikey = $('#oaapikey').val();
    var connectionid = $('#oaconnectionid').val();
    var oatype = $('#type').val();

    // Driver-specific Configuration
    if (oatype == "tlc-carl-sip2") {
        var encrypted_un = encrypt.encrypt($('#sip2-app-un').val());
        var encrypted_pw = encrypt.encrypt($('#sip2-app-pw').val());
        var hostname = $('#sip2-hostname').val();
        var port = $('#sip2-port').val();
        var location = $('#sip2-location').val();

        var payload = {pw:encrypted_pw,un:encrypted_un,hostname:hostname,port:port,location:location,custid:custid,oaendpoint:endpoint,oaapikey:apikey,oaconnectionid:connectionid,type:oatype};
    } else if (oatype == "polaris") {
        var encrypted_un = encrypt.encrypt($('#polaris-access-id').val());
        var encrypted_pw = encrypt.encrypt($('#polaris-access-key').val());
        var hostname = $('#polaris-hostname').val();

        var payload = {pw:encrypted_pw,un:encrypted_un,hostname:hostname,custid:custid,oaendpoint:endpoint,oaapikey:apikey,oaconnectionid:connectionid,type:oatype};
    } else if (oatype == "sierra") {
        var encrypted_un = encrypt.encrypt($('#sierra-authkey').val());
        var encrypted_pw = encrypt.encrypt($('#sierra-authsecret').val());
        var hostname = $('#sierra-hostname').val();
        var blockedmsg = $("#sierra-blockedmessage").val();
        var invalidmsg = $("#sierra-invalidmessage").val();

        var payload = {pw:encrypted_pw,un:encrypted_un,hostname:hostname,custid:custid,oaendpoint:endpoint,oaapikey:apikey,oaconnectionid:connectionid,type:oatype,invalid:invalidmsg,blocked:blockedmsg};
    } else if (oatype == "horizon") {
        var encrypted_un = encrypt.encrypt($('#horizon-client-id').val());
        var hostname = $('#horizon-hostname').val();

        var payload = {un:encrypted_un,hostname:hostname,custid:custid,oaendpoint:endpoint,oaapikey:apikey,oaconnectionid:connectionid,type:oatype};
    }


    var pwcheck = "includes/config.php";
    //console.log(payload);
    $.ajax({
        type: "POST",
        url: pwcheck,
        data: JSON.stringify(payload),
        success: function(data) {
            $("#results").html(data);
        },
        error: function(xhr, status, error) {
            var err = eval("(" + xhr.responseText + ")");
            console.log(err.Message);
        }
    });

}

function showConfig () {
    var currentSelection = $("#type").val();
    $(".ils-section").css("display","none");
    $("#"+currentSelection).css("display","");
}
