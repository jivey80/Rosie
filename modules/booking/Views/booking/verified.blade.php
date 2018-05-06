@extends('booking::layouts.general')

@section('content')

@if($status === 'Success')

    <p class="page-title">{!! $message !!}</p>

    <div class="form-group booking-divider confirmation-box">
        <div class="col-sm-5">
            <div class="booking-person">
                <img src="{{ $avatar }}" alt="Martha" class="img-circle img-responsive confirmation-img" id="cleaner_img">
                <p class="booking-person-name">{{ $firstname }} {{ $lastname }}</p>

                <p class="">
                    Has <b>{{ $rate }}</b> Rating <br />
                    from <b>{{ $reviews }}</b>
                </p>
            </div>
        </div>

        <div class="col-sm-7">
            <div class="booking-info">
                <h4><b>{{ $date_available }}</b></h4>
                <p id="cleaner_slot">{{ $schedule_start }} to {{ $schedule_end }}</p>
                <p id="cleaner_price">{{ $hours_text }}</p>
                
                <span class="booking-total-divider"></span>

                <h4><b>Total: ${{ $price }}</b></h4>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="booking-divider text-center">
        <a href="{{ $link }}" class="btn btn-raised btn-info btn-rosie btn-lg">Book another Slot</a>
    </div>


@else

    <p class="page-error">{!! $message !!}</p>

    <div class="text-center">
        <a href="{{ $link }}" class="back-link">Go Back to Booking Page</a>
    </div>

@endif

@endsection