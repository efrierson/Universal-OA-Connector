
function oauthlogin () {
    $("#warning").html('<img src="includes/loading_sm.gif" />');

    var type = $('#type').val();
    var rd = $('#returnData').val();
    var custid = $('#custid').val();

    if (type == "sierraoauth") {
        var pwcheck = "drivers/sierraoauth.php";
        var code = $('#code').val();
        var payload = {custid:custid,rd:rd,code:code,verbose:"N"};
    }
    else {
        var pwcheck = "unknown";
    }

    if ($("#results").length > 0) {
        payload.verbose = "Y";
    }
    console.log(payload);

    if (pwcheck == "unknown") {
        $("#warning").html('<div class="warningmessage">Unknown Identity Provider Type</div>');
    } else {
        $.ajax({
            type: "POST",
            url: pwcheck,
            data: JSON.stringify(payload),
            success: function(data) {
                if ($("#results").length == 0) {
                    try {
                        json_data = JSON.parse(data);
                        if (json_data.valid == "Y") {
                            console.log(json_data);
                            window.location.href="redirect.php";
                        } else {
                            $("#warning").html('<div class="warningmessage">'+json_data.message+'</div>');
                        }
                    } catch (err) {
                        $("#warning").html('<div class="warningmessage">Sorry, something went wrong.  Please let us know at <a href="mailto:support@ebsco.com">support@ebsco.com</a>, and reference LSE-OA-Error-2.</div>');
                        console.log(err);
                        console.log(data);
                    }
                } else {
                    $("#results").html(data);
                }
            },
            error: function(xhr, status, error) {
                $("#warning").html('<div class="warningmessage">Sorry, something went wrong.  Please let us know at <a href="mailto:support@ebsco.com">support@ebsco.com</a>, and reference LSE-OA-Error-1.</div>');
            }
        });
    }
}