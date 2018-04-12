@extends('booking::layouts.general')

@section('content')

@if($status === 'Success')
    
    <div id="review_form">
        <p class="page-title">{!! $message !!}</p>

        <div class="form-group booking-divider">

        	<h4>Rate:</h4>
        	<div class="rating-stars" data-rating="{{$stars}}"></div>

        	<br />

        	<h4>Review:</h4>
        	<textarea class="form-control" rows="6" id="txtReview" placeholder="Enter review here..." autofocus="autofocus"></textarea>
        	<h4 class="help-block"><i>Your review will be used anonymously.</i></h4>
        </div>
    	
    	<div class="text-center booking-divider">
    		<button type="button" class="btn btn-raised btn-info" id="submit_review" data-bid="{{$booking_id}}" data-cid="{{$client_id}}" data-eid="{{$employee_id}}">
                Submit Review
            </button>
    	</div>
    </div>

    <div id="review_success" class="hide">

        <div id="review_submit_message" class="page-error"></div>

        <div class="text-center">
            <a href="{{$link}}" class="back-link">Go Back to Booking Page</a>
        </div>
    </div>
@else

    <p class="page-error">{!! $message !!}</p>

    <div class="text-center">
        <a href="{{$link}}" class="back-link">Go Back to Booking Page</a>
    </div>

@endif

@endsection