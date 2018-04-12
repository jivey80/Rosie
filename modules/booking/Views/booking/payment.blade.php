@extends('booking::layouts.general')

@section('content')

<p class="page-title"> Booking will be confirmed once the payment was successfully made. </p>

<!-- <form id="formLogin" class="form-horizontal form-spacing" method="POST" action="" autocomplete="off">
    <div class="form-group label-floating">
        <div class="col-sm-12">
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="material-icons">credit_card</i>
                </span>
            
                <label class="control-label" for="creditcard">CARD NUMBER</label>
                <input class="form-control" id="creditcard" type="text" tabindex="1" autofocus="autofocus" autocomplete="off" />
            </div>
        </div>
    </div>

    <div class="form-group label-floating">
        <div class="col-sm-6">
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="material-icons">date_range</i>
                </span>
            
                <label class="control-label" for="expiration">MM/YY</label>
                <input class="form-control" id="expiration" type="text" tabindex="2" autocomplete="off" />
            </div>
        </div>

        <div class="col-sm-6">
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="material-icons">fingerprint</i>
                </span>
            
                <label class="control-label" for="cvc">CVC</label>
                <input class="form-control" id="cvc" type="password" tabindex="3" autocomplete="off" />
            </div>
        </div>
    </div>
</form> -->


<div class="form-group confirmation-box">
    <div class="col-sm-5">
        <div class="booking-person">
            <img src="http://localhost/freelance/rosies/public/assets/images/avatar/3785b0337d4c250cb271b869caf64b64.jpeg" alt="Martha" class="img-circle img-responsive confirmation-img" id="cleaner_img">
            <p class="booking-person-name">Tony Stark</p>

            <p class="">
                Has <b>5</b> Rating <br />
                from <b>2354</b>
            </p>
        </div>
    </div>

    <div class="col-sm-7">
        <div class="booking-info">
            <h4><b>Saturday, March 3</b></h4>
            <p id="cleaner_slot">9:00 AM to 10:00 AM</p>
            <p id="cleaner_price">1 hour at $25/hour</p>
            
            <span class="booking-total-divider"></span>

            <h4><b>Total: $25</b></h4>
        </div>
    </div>
</div>

<div class="clearfix"></div>

<!-- <div class="booking-divider text-center">
    <a href="javascript:void(0)" class="btn btn-raised btn-info btn-lg">CONFIRM PAYMENT</a>
</div> -->


<form action="payment" method="POST">
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
</form>



@endsection

@section('script')
<script src="{{ MODULE_ASSETS_URL }}/js/card.js"></script>
@endsection
