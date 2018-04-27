"use strict";

if (typeof jQuery === 'undefined') {
	error_call('jQuery is required.');
}


+function (Dashboard, $) {

	const METHODS = {
		add: 	'POST',
		edit: 	'PUT',
		delete: 'DELETE'
	};


	Dashboard.prototype.CRUD = function (action, url, params, success_callback, error_callback) {

		if (METHODS.hasOwnProperty(action)) {

			var get_method = METHODS[action];

			var xhr_handler = function (xhr_response) {

				if (xhr_response.status === 'Success') {

					success_callback(xhr_response);

				} else {

					error_callback(xhr_response);
				}

				return false;
			};


			// Specific to fileupload only
			if (params.hasOwnProperty('avatar') && action !== 'delete') {

				var new_params = new FormData();

				$.each(params, function (prop, val) {
					if (prop === 'avatar') {
						new_params.append(prop, $('#avatar').prop('files')[0]);
					} else {
						new_params.append(prop, val);
					}
				});

				var new_method = (in_array(action, ['add', 'edit'])) ? 'POST' : 'DELETE';

				$.ajax({
					url: 	url, 			// Url to which the request is send
					type: 	new_method, 	// Type of request to be send, called as method

					// Data sent to server, a set of key/value pairs (i.e. form fields and values)
					data: 	new_params, 										

					cache: 			false, 	// To unable request pages to be cached
					processData: 	false, 	// To send DOMDocument or non processed data file it is set to false
					contentType: 	false, 	// The content type used when sending data to the server.
					
					success: function(response) {

						return xhr_handler(response);
					},

					error: function (response) {

						return xhr_handler(response);
					}
				});

			} else {

				ajax_call(url, params, function (response) {
					
					return xhr_handler(response);

				}, get_method);
			}
		}
	};

	Dashboard.prototype.CRUDForm = function () {

		var self = this;

		
		$('.dt-buttons').on('click', function (e) {
			
			var module = $('#datatable_dom').data('module');

			switch(module) {
				case 'list_timetables':
					self.CRUDModules.list_timetables();
					break;

				case 'employees':
					self.CRUDModules.employees();
					break;

				default: break;
			}
		});
	};

	Dashboard.prototype.CRUDModules = {

		list_timetables: function () {

			ajax_call('get_form_data/cleaners', {}, function (xhr_response) {

				$('#fullname').toDropdown(xhr_response, 'employee_id', 'fullname', 'Choose a cleaner');
			});


			$('#date_available').datepicker({
				autoHide: 		true,
				zIndex: 		1100,
				offset: 		-50,
				startDate: 		datepicker_date_now()
			});


			$('#time_in').toDropdown([
	            { time_id: '07:00', time_value: '07:00' },
	            { time_id: '07:30', time_value: '07:30' },
	            { time_id: '08:00', time_value: '08:00' },
	            { time_id: '08:30', time_value: '08:30' },
	            { time_id: '09:00', time_value: '09:00' },
	            { time_id: '09:30', time_value: '09:30' },
	            { time_id: '10:00', time_value: '10:00' },
	            { time_id: '10:30', time_value: '10:30' },
	            { time_id: '11:00', time_value: '11:00' },
	            { time_id: '11:30', time_value: '11:30' },
	            { time_id: '12:00', time_value: '12:00' },
	        ], 'time_id', 'time_value', 'Choose start time', '09:00');

            
            $('#time_out').toDropdown([
	            { time_id: '15:00', time_value: '15:00' },
	            { time_id: '15:30', time_value: '15:30' },
	            { time_id: '16:00', time_value: '16:00' },
	            { time_id: '16:30', time_value: '16:30' },
	            { time_id: '17:00', time_value: '17:00' },
	            { time_id: '17:30', time_value: '17:30' },
	            { time_id: '18:00', time_value: '18:00' },
	            { time_id: '18:30', time_value: '18:30' },
	            { time_id: '19:00', time_value: '19:00' },
	            { time_id: '19:30', time_value: '19:30' },
	            { time_id: '20:00', time_value: '20:00' },
	            { time_id: '20:30', time_value: '20:30' },
	            { time_id: '21:00', time_value: '21:00' },
	            { time_id: '21:30', time_value: '21:30' }
	        ], 'time_id', 'time_value', 'Choose end time', '19:00');
		},

		employees: function () {

			// Modify form to accept file uploads
			$('[name=altEditor-form]').attr('enctype', 'multipart/form-data');
			$('[name=altEditor-form]').attr('method', 'POST');

			// Recreate fileupload dom
			$('#avatar').replaceWith('<input type="file" id="avatar" class="input-avatar" name="avatar" />').promise().done(function () {
				$('#avatar').fileinput({
					showUpload: false,
					showRemove: false,
					layoutTemplates: {
						fileIcon: ''
					},
					fileActionSettings: {
						indicatorNew: '',
						showZoom: false
					}
				});
			});
		}
	};



	function __helper_crud_response(response, success_callback, error_callback)
	{
		if (response.hasOwnProperty('status')) {

			if (response.status === 'Success') {

				success_callback(response);

			} else {

				error_callback(response);
			}
		}
	}

}(window.Dashboard = window.Dashboard || function () {}, jQuery);
