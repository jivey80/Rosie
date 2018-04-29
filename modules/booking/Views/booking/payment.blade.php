@extends('booking::layouts.general')

@section('content')

<p class="page-title"> Booking will be confirmed once the payment was successfully made. </p>

<br />


<form id="payment_form" class="form-horizontal form-spacing" method="POST" action="payment" autocomplete="off">
    <div class="text-danger stripe-error" id="payment_error"></div>
    <div id="creditcard"></div>
    <button type="button" class="btn btn-sm btn-raised btn-info btn-rosie btn-block" id="payment_submit">PROCEED</button>
</form>

<br />

<div class="booking-divider">
    <div class="row payment-footer">
        <div class="col-sm-8">
            Rest assured that we do not store any credit card information on our servers. All credit card data is sent to our third party processor <a href="https://stripe.com/" target="_new">www.stripe.com</a>.
        </div>
        <div class="col-sm-4 text-center">
            <a href="https://stripe.com/" target="_new">
                <img src="{{ ASSET_IMG . '/powered_by_stripe.png' }}" class="stripe-img" />
            </a>
        </div>
    </div>
</div>


@endsection

@section('script')
<script src="{{ MODULE_ASSETS_URL }}/js/card.js"></script>
<script src="https://js.stripe.com/v3/"></script>
<script>

    +function () {

        // Stripe API Key
        var stripe = Stripe('{{ APIKEY_STRIPE_PKEY }}');
        var elements = stripe.elements();

        // Custom Styling
        var style = {
            base: {
                color: '#32325d',
                lineHeight: '24px',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#ef9a9a'
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

                    form_dom.append([
                        '<input',
                            'type="hidden"',
                            'name="stripeToken"',
                            'value="' + form_token + '"',
                        '/>'
                    ].join(' '));

                    return form_dom.submit();
                }
            });
        });
    }();
</script>
@endsection
