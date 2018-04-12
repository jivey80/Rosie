<form class="form-horizontal form-spacing">

	<div class="form-group" id="confirmation_page">

		<div class="col-sm-5">
			<div class="booking-person">
				<img src="" alt="Martha" class="img-circle img-responsive confirmation-img" id="cleaner_img">
				<p class="booking-person-name" id="cleaner_name"></p>
				
				<span class="person-rating" id="cleaner_rating"></span>

				<p class="person-review" id="cleaner_review">0 Review</p>
			</div>
		</div>

		<div class="col-sm-7">
			<div class="booking-info">
				<h4><b><span id="cleaner_schedule"></span></b></h4>
				<p id="cleaner_slot"></p>
				<p id="cleaner_price">1 hour for $00.00</p>
				
				<span class="booking-total-divider"></span>

				<h4><b>Total: <span id="booking_total"></span></b></h4>
			</div>
		</div>

	</div>
    

    <div class="form-footer">
        <div class="pull-right">
            <button type="button" class="btn btn-raised btn-info" id="confirmation_next">Proceed Booking</button>
        </div>

        <div class="text-center">
            <button type="button" class="btn btn-raised btn-info hide" id="back_to_main">Book another slot</button>
        </div>

        <div class="pull-left">
            <button type="button" class="btn btn-raised btn-info" id="confirmation_prev">Back</button>
        </div>

        <div class="clearfix"></div>
    </div>
</form>