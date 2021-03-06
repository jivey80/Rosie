<div class="row row-fix">
	<div class="col-md-8 col-fix">
		<div class="panel panel-rosie">
			<div class="panel-heading">
				<h3 class="panel-title">Booking Statistics</h3>
			</div>
			<div class="panel-body">
				@if (isset($booking_stats) and $booking_stats)
				<div class="chartjs" data-chartid="stats_booking" data-width="600" data-height="180" data-stats="{{ $booking_stats }}"></div>
				@else
				<h4 class="text-center">No available data to display.</h4>
				@endif
			</div>
		</div>

		<div id="booking_summary" data-response="{{ $booking_summary }}"></div>
	</div>

	<div class="col-md-4 col-fix">
		<div class="panel panel-rosie">
			<div class="panel-heading">
				<h3 class="panel-title">{{ $top_cleaner_title }}</h3>
			</div>
			<div class="panel-body" id="top_cleaner" data-response="{{ $top_cleaner }}"></div>
		</div>
	</div>
</div>