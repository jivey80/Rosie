@extends('booking::layouts.general')

@section('content')

<p class="page-title"> Booking will be confirmed once the payment was successfully made. </p>

<form id="formLogin" class="form-horizontal form-spacing" method="POST" action="" autocomplete="off">
    <div class="form-group">
        <div class="col-sm-12">
            <div class="form-group label-floating">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="material-icons">credit_card</i>
                    </span>
                
                    <label class="control-label" for="creditcard">CARD NUMBER</label>
                    <input class="form-control" id="creditcard" type="text" tabindex="1" autofocus="autofocus" autocomplete="off" />
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group label-floating">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="material-icons">date_range</i>
                    </span>
                
                    <label class="control-label" for="expiration">MM/YY</label>
                    <input class="form-control" id="expiration" type="text" tabindex="2" autocomplete="off" />
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group label-floating">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="material-icons">fingerprint</i>
                    </span>
                
                    <label class="control-label" for="cvc">CVC</label>
                    <input class="form-control" id="cvc" type="password" tabindex="3" autocomplete="off" />
                </div>
            </div>
        </div>
    </div>
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


<!-- <div class="booking-divider text-center">
    <a href="javascript:void(0)" class="btn btn-raised btn-info btn-lg">CONFIRM PAYMENT</a>
</div> -->


<!-- <form action="payment" method="POST">
    <script
        src="https://checkout.stripe.com/checkout.js" class="stripe-button"
        data-key="{{ APIKEY_STRIPE_PKEY }}"
        data-amount="2500"
        data-name="Rosie Services"
        data-description="Booking Payment"
        data-image="{{ ASSET_LOGO }}"
        data-locale="auto"
        data-email="client_a@gmail.com"
        data-allow-remember-me="false">
    </script>
</form> -->



@endsection

@section('script')
<script src="{{ MODULE_ASSETS_URL }}/js/card.js"></script>
@endsection
