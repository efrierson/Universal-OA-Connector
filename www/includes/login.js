
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