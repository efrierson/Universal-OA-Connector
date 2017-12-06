
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

    var payload = {loginbutton:loginbutton,logo:logo,barcodelabel:barcodelabel,barcodeplaceholder:barcodeplaceholder,pinlabel:pinlabel,pinplaceholder:pinplaceholder,helptext:helptext,titletext:titletext,custid:custid};    

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
    }
    
    var pwcheck = "includes/config.php";

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

function oalogin () {
    $("#warning").html('<img src="includes/loading_sm.gif" />');

    var encrypt = new JSEncrypt();
    encrypt.setPublicKey($('#pubkey').val());

    var encrypted_un = encrypt.encrypt($('#login-un').val()); 
    var encrypted_pw = encrypt.encrypt($('#login-pw').val());
    var type = $('#type').val();
    var rd = $('#returnData').val();
    var custid = $('#custid').val();
    
    var payload = {pw:encrypted_pw,un:encrypted_un,custid:custid,rd:rd,verbose:"N"};
    
    if ($("#results").length > 0) {
        payload.verbose = "Y";
    }
    console.log(payload);
    
    if (type == "tlc-carl-sip2") {
        var pwcheck = "drivers/sip2-tlc-carl.php";        
    } else if (type == "polaris") {
        var pwcheck = "drivers/polaris.php";
    } else {
        var pwcheck = "unknown";
    }
    
    if (pwcheck == "unknown") {
        $("#warning").html('<div class="warningmessage">Unknown Identity Provider Type</div>');
    } else {
        $.ajax({
            type: "POST",
            url: pwcheck,
            data: JSON.stringify(payload),
            success: function(data) {
                if ($("#results").length == 0) {
                    json_data = JSON.parse(data);
                    if (json_data.valid == "Y") {
                        console.log(json_data);
                        window.location.href="redirect.php";
                    } else {
                        $("#warning").html('<div class="warningmessage">'+json_data.message+'</div>');
                    }
                } else {
                    $("#results").html(data);
                }
            },
            error: function(xhr, status, error) {
                console.log("Broke");
            }
        });        
    }
    

}