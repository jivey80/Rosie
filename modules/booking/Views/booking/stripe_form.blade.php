@extends('booking::layouts.general')


@section('content')
    <p class="page-title"> {{ $title }} </p>

    <form id="payment_form" class="form-horizontal" method="POST" action="stripe" autocomplete="off">

        <div class="form-group form-group-slim">
            <div class="col-sm-6">
                <label for="fullname" class="control-label">FULL NAME</label>
                <input class="form-control" id="fullname" type="text" disabled="disabled" value="{{ $fname }}" />
            </div>
            <div class="col-sm-6">
                <label for="email" class="control-label">EMAIL ADDRESS</label>
                <input class="form-control" id="email" type="email" disabled="disabled" value="{{ $email }}" />
            </div>
        </div>

        <div class="form-group form-group-slim">
            <div class="col-sm-12">
                <label for="creditcard" class="control-label">CARD DETAILS</label>
                <div class="text-danger stripe-error" id="payment_error"></div>
                <div id="creditcard"></div>
            </div>
        </div>

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
    <script src="https://js.stripe.com/v3/"></script>

    @if(ENVIRONMENT === 'local' and FORCE_MIN_ASSETS === false)
    <script src="{{ MODULE_ASSETS_URL }}/js/stripe.js" data-stripe="{{ APIKEY_STRIPE_PKEY }}" data-ftype="{{ $ftype }}" data-email="{{ $email }}"></script>
    @else
    <script src="{{ MODULE_ASSETS_URL }}/build/stripe.min.js" data-stripe="{{ APIKEY_STRIPE_PKEY }}" data-ftype="{{ $ftype }}" data-email="{{ $email }}"></script>
    @endif
@endsection
