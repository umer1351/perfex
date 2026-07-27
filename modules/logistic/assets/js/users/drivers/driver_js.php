<script>
(function($){
"use strict";
validate_driver_form();


})(jQuery);

function validate_driver_form() {
    "use strict";
    appValidateForm('#driver_form-form', {
        first_name: 'required',
        last_name: 'required',
        password: {
            required: {
                depends: function(element) {

                    var $sentSetPassword = $('input[name="send_set_password_email"]');

                    if ($('#contact input[name="driverid"]').val() == '' && $sentSetPassword.prop(
                            'checked') == false) {
                        return true;
                    }
                }
            }
        },
        email: {
            required: true,
            email: true,
            remote: {
                url: admin_url + "logistic/driver_email_exists",
                type: 'post',
                data: {
                    email: function() {
                        return $('#contact input[name="email"]').val();
                    },
                    driverid: function() {
                        return $('body').find('input[name="driverid"]').val();
                    }
                }
            }
        },
        username: {
            required: true,
            remote: {
                url: admin_url + "logistic/driver_username_exists",
                type: 'post',
                data: {
                    username: function() {
                        return $('#contact input[name="username"]').val();
                    },
                    driverid: function() {
                        return $('body').find('input[name="driverid"]').val();
                    }
                }
            }

        },

    });
}	

</script>