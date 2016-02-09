Review.prototype.nextStep = function(transport){
    if (transport && transport.responseText) {
        try{
            response = eval('(' + transport.responseText + ')');
        }
        catch (e) {
            response = {};
        }
        if (response.redirect) {
            this.isSuccess = true;
            location.href = response.redirect;
            return;
        }
        if (response.success) {
            this.isSuccess = true;
            window.location=this.successUrl;
        }
        else{
            var msg = response.error_messages;
            if (typeof(msg)=='object') {
                msg = msg.join("\n");
            }
            if (msg) {
                msg = msg.replace(new RegExp((';').replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1"), 'g'), '<br />');
                if ($('checkout-step-review') != null) {
                    $('checkout-step-review').update('<div class="error postpal_error_wrapper" id="checkoutError">'+msg+'<span class="postpal_error_close">x</span></div>'+$('checkout-step-review').innerHTML);
                }
                else {
                    alert(msg);
                }
            }
        }

        if (response.update_section) {
            $('checkout-'+response.update_section.name+'-load').update(response.update_section.html);
        }

        if (response.goto_section) {
            checkout.gotoSection(response.goto_section, true);
        }
    }
}


$(document).on('click', '.section .button, .section .btn-checkout, .postpal_error_close', function() {
    PostPal_Close_Error_Message();
});

function PostPal_Close_Error_Message() {

    if($('checkout-step-review') != null && $('checkoutError') != null)
        $('checkout-step-review').removeChild($('checkoutError'));

}