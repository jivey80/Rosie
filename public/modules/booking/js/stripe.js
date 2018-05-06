+function () {
    "use strict";

    function __secret(form_dom, attr, value)
    {
        if (form_dom.length && attr && value) {
            form_dom.append([
                '<input',
                    'type="hidden"',
                    'name="' + attr + '"',
                    'value="' + value + '"',
                '/>'
            ].join(' '));
        }
    }


    var script = document.querySelector('script[data-stripe]'),
        strkey = script.getAttribute('data-stripe'),
        strtyp = script.getAttribute('data-ftype'),
        streml = script.getAttribute('data-email');

    if (strkey) {

        // Stripe API Key
        var stripe = Stripe(strkey);
        var elements = stripe.elements();

        // Custom Styling
        var style = {
            base: {
                color: '#32325d',
                lineHeight: '24px',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '13px',
                '::placeholder': {
                    color: '#b71c1c'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };

        // Create an instance of the card Element
        var card = elements.create('card', {style: style});

        // Add an instance of the card Element into the `card-element` <div>
        card.mount('#creditcard');

        // Handle real-time validation errors from the card Element.
        $('#payment_submit').on('click', function () {

            stripe.createToken(card).then(function(result) {
                    
                if (result.error) {

                    // Inform the user if there was an error
                    $('#payment_error').html('Error: ' + result.error.message);

                    return false;

                } else {

                    var form_dom = $('#payment_form'),
                        form_token = result.token.id;


                    __secret(form_dom, 'stripeToken', form_token);
                    __secret(form_dom, 'stripeEmail', streml);
                    __secret(form_dom, 'stripeType', strtyp);

                    return form_dom.submit();
                }
            });
        });
    }
}();
