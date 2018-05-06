@extends('booking::layouts.general')


@section('content')

    <br />
    <br />
    <br />
    
    <div class="text-center">
        <p class="page-title">
            <b>{{ $title }}</b> {!! $message !!}
        </p>

        <br />
        <br />
        <br />

        @if($is_error)
        <a href="http://localhost/freelance/rosies/booking" class="back-link">Go Back to Booking Page</a>
        @else
        <a href="{{ $link }}" class="btn btn-raised btn-info btn-rosie btn-lg">PROCEED TO BOOKING</a>
        @endif
    </div>

    @if(! $is_error)
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
    @endif
@endsection
