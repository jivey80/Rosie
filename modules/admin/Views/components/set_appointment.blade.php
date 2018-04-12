<div class="panel panel-rosie" id="appointment_form" data-options="{{ $available_dates }}">
	<div class="panel-heading">
		<h3 class="panel-title">Book Appointment</h3>
	</div>
	<div class="panel-body">

		<form class="form-horizontal" autocomplete="off">

			<div class="form-section">
				<div class="form-group">
					<label for="email" class="col-lg-2 control-label form-label">Email</label>

					<div class="col-lg-10">
						<input type="email" class="form-control form-control-xs" id="email" autocomplete="off" />
					</div>
				</div>
			</div>

			<div class="form-section">
				<div class="form-group">
					<label for="date" class="col-lg-2 control-label form-label">Date</label>

					<div class="col-lg-10">
						<select id="date" class="form-control form-control-xs" tabindex="1" disabled="disabled"  autocomplete="off">
							<option value="undefined">Choose Available Date</option>
						</select>
					</div>
				</div>

				<div class="form-group">
					<label for="cleaner" class="col-lg-2 control-label form-label">Cleaner</label>

					<div class="col-lg-10">
						<select id="cleaner" class="form-control form-control-xs" disabled="disabled" autocomplete="off">
							<option value="undefined">Choose a Cleaner</option>
						</select>
					</div>
				</div>

				<div class="form-group">
					<label for="time_start" class="col-lg-2 control-label form-label">Start Time</label>

					<div class="col-lg-10">
						<input type="text" class="form-control form-control-xs" id="time_start" disabled="disabled" autocomplete="off" />
					</div>
				</div>

				<div class="form-group">
					<label for="time_end" class="col-lg-2 control-label form-label">End Time</label>

					<div class="col-lg-10">
						<input type="text" class="form-control form-control-xs" id="time_end" disabled="disabled" autocomplete="off" />
					</div>
				</div>
			</div>

			<div class="form-section">
				<div class="form-group">
					<label for="price" class="col-lg-2 control-label form-label">Price</label>

					<div class="col-lg-10">
						<input type="text" class="form-control form-control-xs" id="price" disabled="disabled" autocomplete="off" />
					</div>
				</div>

				<div class="form-group">
					<label for="price" class="col-lg-2 control-label form-label">Need Supplies</label>

					<div class="col-lg-10 togglebutton">
						<label>
							<input type="checkbox" disabled="disabled" id="supplies" value="0" autocomplete="off" />
						</label>
					</div>
				</div>

				<div class="form-group">
					<label for="price" class="col-lg-2 control-label form-label">Special Instructions</label>

					<div class="col-lg-10 togglebutton">
						<label>
							<input type="checkbox" disabled="disabled" id="instructions" value="0" autocomplete="off" />
						</label>
					</div>
				</div>

				<div class="form-group hidden" id="instruction_area">
					<div class="col-lg-offset-2 col-lg-10">
						<textarea class="form-control form-control-xs" rows="5" id="instruction_area" placeholder="Special Instructions" autocomplete="off" placeholder="Free text for your special instructions"></textarea>
					</div>
				</div>
			</div>

			<div class="form-group">
				<div class="col-lg-10 col-lg-offset-2">
					<button type="button" class="btn btn-primary btn-raised" disabled="disabled" id="save_booking">Save</button>
				</div>
			</div>
		</form>

	</div>
</div>
