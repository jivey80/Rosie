"use strict";

+function () {

	$('#btnlogin').on('click', function (e) {
        e.preventDefault();

        var username = $('#inputUsername').val(),
        	password = $('#inputPassword').val();

    	if (is_defined(username) && is_defined(password)) {

            var form = $('#formLogin');

            if (form.length > 0) {

                var login_url = form.attr('action');

                $.post(login_url, {

                    username: username,

                    password: password

                }, function (response) {

                    if (response.hasOwnProperty('status') && response.status === 'Success') {

                        window.location = __BASE_URL + '/console';
                    
                    } else {

                        var err_message = response.message,
                            err_alert = $('#login_status');

                        err_alert.removeClass('hidden');
                        err_alert.find('span').html(err_message);
                        err_alert.find('.alert-login').addClass('alert-login-active');

                        $('#inputPassword').val('');

                        throw Error(err_message);
                    }
                });
            }
    	}
    });

    $(document).keypress(function (e) {

        if (e.which === 13 || e.keyCode === 13) {

            $('#btnlogin').click();
        }
    });
}();
