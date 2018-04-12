<div class="booking-container">

	<div class="booking-list">
	@if ($schedules)
		@foreach ($schedules as $schedule)
			<div class="booking-group">

				<!-- Preloader display for timeslots -->
				<div class="booking-preloader hide" id="booking_preloader_{{ $schedule['timetable_id'] }}"></div>
				
				<!-- Cleaner timeslot information -->
				<div class="row booking-title">
					<div class="col-sm-3 col-xs-5">
						<div class="booking-person">
							<img src="{{ $schedule['avatar'] }}" alt="{{ $schedule['firstname'] }}" class="img-circle img-responsive schedule-img">
							<p class="booking-person-name">{{ $schedule['firstname'] }}</p>
							
							<span class="person-rating" data-rating="{{ $schedule['rating'] }}"></span>

							<p class="person-review">{{ $schedule['reviews'] }} Reviews</p>
						</div>
					</div>

					<div class="col-sm-9 col-xs-7 row">
						<div class="">
							<div class="col-sm-8">
								<div class="booking-schedule">
									<h4><b>{{ $schedule['date_available'] }}</b></h4>
								</div>
							</div>

							<div class="col-sm-4">
								<div class="booking-detail">
									
									@if($schedule['cheapest'] == 0)
										<h4><b>Sold Out!</b></h4>
									@else
										<h4><b>${{ $schedule['cheapest'] }}/hour</b></h4>
										<button type="button" class="btn btn-raised btn-info btn-xs booking-details" id="schedule_{{ $schedule['timetable_id'] }}" data-toggle="0">
											Details <i class="material-icons">arrow_downward</i>
										</button>
									@endif

								</div>
							</div>		
						</div>
					</div>

				</div>

				<!-- Time slots list -->
				<div class="booking-options" id="booking_option_{{ $schedule['timetable_id'] }}">
					<div class="arrow-left panner" data-scroll-modifier="-1">
						<a href="javascript:void(0)" class="btn btn-fab-mini btn-info btn-fab arrow-icons">
							<i class="material-icons">keyboard_arrow_left</i>
						</a>
					</div>

					<div class="schedule-frame-container">
						<div class="schedule-frame" id="schedule_frame_{{ $schedule['timetable_id'] }}"></div>
					</div>

					<div class="arrow-right panner" data-scroll-modifier="1">
						<a href="javascript:void(0)" class="btn btn-fab-mini btn-info btn-fab arrow-icons">
							<i class="material-icons">keyboard_arrow_right</i>
						</a>
					</div>
				</div>

			</div>
		@endforeach
	@else
		<h3 class="booking-error">
			No available slot as of this moment. <br /> Please try again later.
		</h3>
	@endif
	</div>


	<div class="booking-divider">
		@if ($schedules)
	    <div class="pull-right">
	        <button type="button" class="btn btn-raised btn-info" id="schedule_next">Next</button>
	    </div>
	    @endif

	    <div class="pull-left">
	        <button type="button" class="btn btn-raised btn-info" id="schedule_prev">Review</button>
	    </div>

	    <div class="clearfix"></div>
	</div>

</div>