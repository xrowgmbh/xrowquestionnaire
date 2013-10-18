(function() {

    var methods = {
        init : function(options) {
            // this.attr( 'attribute_id', this.attr( 'id' ).split("_",2)[1] );
            // var id = this.attr( 'id' ).split("_",1);
            // alert(this.attr( 'attribute_id' ));
        },
        submit : function() {
            var id = this.attr('id').split("_", 2)[1];
            var data = this.serialize();
            jQuery.ez('xrowquestionnaire_page::questionnaire', data + "&submit=on", function(
                    result) {
                if (result.content.error == true) {
                    jQuery('#error-msg-' + id).html(result.content.template);
                }
                if (result.content.template) {
                    jQuery('#voting-box-' + id).html(result.content.template);
                }

                if (result.content.captcha) {
                    Recaptcha.create(jQuery('#captcha_key').val(), 'captcha_'
                            + id, {
                        lang : jQuery('#captcha_lang').val(),
                        theme : jQuery('#captcha_theme').val(),
                        callback : Recaptcha.focus_response_field
                    });

                }
            });
        },
        start : function() {
            var id = this.attr('id').split("_", 2)[1];
            var data = this.serialize();
            jQuery.ez('xrowquestionnaire_page::questionnaire', data + "&start=on", function(
                    result) {
                if (result.content.error == true) {
                    jQuery('#error-msg-' + id).html(result.content.template);
                }
                if (result.content.template) {
                    jQuery('#voting-box-' + id).html(result.content.template);
                }
            });
        },
        optin : function() {
            var id = this.attr('id').split("_", 2)[1];
            var data = this.serialize();
            jQuery.ez('xrowquestionnaire_page::optin', data, function(
                    result) {
                if (result.content.error == true) {
                    jQuery('#error-msg-' + id).html(result.content.template);
                }
                
                jQuery('#questionnaire-optin').attr("disabled", true);
                jQuery('#questionnaire-optin').siblings( 'span' ).html( jQuery('#questionnaire-optin').data( 'text-active' ) );
            });
        },
        again : function() {
            var id = this.attr('id').split("_", 2)[1];
            var data = this.serialize();
            jQuery.ez('xrowquestionnaire_page::questionnaire', data + "&again=on", function(
                    result) {
                if (result.content.error == true) {
                    jQuery('#error-msg-' + id).html(result.content.template);
                }
                if (result.content.template) {
                    jQuery('#voting-box-' + id).html(result.content.template);
                }
            });
        },
        next : function() {
            var id = this.attr('id').split("_", 2)[1];
            var data = this.serialize();
            jQuery.ez('xrowquestionnaire_page::questionnaire', data + "&next=on", function(
                    result) {
                if (result.content.error == true) {
                    jQuery('#error-msg-' + id).html(result.content.template);
                }
                if (result.content.template) {

                    jQuery('#voting-box-' + id).html(result.content.template);
                }
            });
        },
        prev : function() {
            var id = this.attr('id').split("_", 2)[1];
            var data = this.serialize();
            jQuery.ez('xrowquestionnaire_page::questionnaire', data + "&prev=on", function(
                    result) {
                if (result.content.error == true) {
                    jQuery('#error-msg-' + id).html(result.content.template);
                }
                if (result.content.template) {

                    jQuery('#voting-box-' + id).html(result.content.template);
                }
            });
        },
        results : function() {
            var id = this.attr('id').split("_", 2)[1];
            var data = this.serialize();
            jQuery.ez('xrowquestionnaire_page::show_result', data, function(
                    result) {
                jQuery('#voting-box-' + id).html(result.content.template);
            });
        }
    };

    jQuery.fn.questionnaire = function(method) {

        // Method calling logic
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(
                    arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            jQuery.error('Method ' + method
                    + ' does not exist on jQuery.questionnaire');
        }

    };

})(jQuery);



/**
 * Javascript for view template
 */

jQuery(document).ready((function() {
	jQuery('form.voting-box').each(function(index, value){
	    jQuery(this).questionnaire( 'submit' );
	});
}));