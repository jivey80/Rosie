"use strict";

if (typeof jQuery === 'undefined') {
	error_call('jQuery is required.');
}

+function (Dashboard, $) {

	var __ACTIVE_NAV = undefined;

	Dashboard.prototype.init = function () {

		// Disable any back button access
		// in the frontend.
		this.secure_access();


		// Render sidebar controls and
		// default to one module.
		this.ui_navigation('#startup');


		// Render other controls.
		this.ctrl_schedules();
		this.ctrl_account();
		this.ctrl_logout();
	};


	Dashboard.prototype.secure_access = function () {
		
		ajax_call('console', {}, function (response) {

			if (response.hasOwnProperty('status') && response.status === 'Error') {

				window.location.href = window.location.href;
			}

		}, 'GET');
	};


	Dashboard.prototype.ctrl_menu = function (dom) {

		var self = this;

		if (is_defined(dom) && dom.length > 0) {

			var url = dom.data('url');

			if (is_defined(url)) {

				var __url = url.toLowerCase();

				ajax_call(__url, {}, function (response) {

					is_error(response);

					var module_name = __url;


					switch (__url) {
						case 'dashboard':
							$('#main_content').html(response).promise().done(function () {
								
								// Booking statistics graph
								$('.chartjs').chartjs('date');

								// Booking summary grid view
								var _bsum_response = $('#booking_summary').data('response');
								self.ui_table(module_name, 'Booking Summary for this Month', _bsum_response, undefined, '#booking_summary');
								
								// Top cleaners
								$('#top_cleaner').rankjs();
							});
							break;

						case 'list_timetables':
							self.ui_table(module_name, 'timetables', response, ['add']);
							break;

						case 'set_appointment':
							self.module_set_appointment(response);
							break;

						case 'administrators':
							self.ui_table(module_name, __url, response, ['add', 'reset_password']);
							break;

						case 'employees':
						case 'clients':
							self.ui_table(module_name, __url, response, ['add', 'edit', 'delete']);
							break;

						case 'settings':
							self.ui_table(module_name, __url, response, ['edit']);
							break;

						default:
							$('#main_content').html(response);
							$.material.init();
							break;
					}
				});
			}
		}
	};


	Dashboard.prototype.ctrl_schedules = function () {

		$('#nav_schedules').on('click', function (e) {

	        var messagebar = $('#messagebar'),
	        	messagebar_content = $('#messagebar_content');

	        messagebar.addClass('active');
	        messagebar_content.animate({ right: 0 }, 300);

	        messagebar.append('<div id="messagebar_overlay" class="messagebar-overlay"></div>');


	        if (parseInt(window.innerWidth) <= 820) {
	        	messagebar_content.width(parseInt(window.innerWidth) - 30);
	        }


	        $('#messagebar_overlay, #messagebar_close').on('click', function (e) {

	        	var target = $(e.target);

	        	if (target.is('#messagebar_overlay')) {
	        	
	        		$(this).remove();
	        	
	        	} else {

	        		$('#messagebar_overlay').remove();
	        	}

	        	messagebar.removeClass('active');
	        	messagebar_content.width(450).animate({ right: '-500px' }, 300);
	        });
	    });
	};


	Dashboard.prototype.ctrl_account = function () {

		var icon = $('#nav_account');

		icon.on('click', function (e) {

			e.preventDefault();

			var modal_button = $('#modal_ok');


			var change_password_form = [
				'<form class="form-horizontal" autocomplete="off">',
                    '<div class="form-section">',
                        '<div class="form-group">',
                            '<label for="old_password" class="col-lg-2 control-label form-label">Old password</label>',

                            '<div class="col-lg-10">',
                                '<input type="password" class="form-control form-control-xs" id="old_password" autocomplete="off" />',
                            '</div>',
                        '</div>',
                    '</div>',

                    '<div class="form-section">',
                        '<div class="form-group">',
                            '<label for="new_password" class="col-lg-2 control-label form-label">New Password</label>',

                            '<div class="col-lg-10">',
                                '<input type="password" class="form-control form-control-xs" id="new_password" autocomplete="off" />',
                            '</div>',
                        '</div>',

                        '<div class="form-group">',
                            '<label for="confirm_password" class="col-lg-2 control-label form-label">Confirm Password</label>',

                            '<div class="col-lg-10">',
                                '<input type="password" class="form-control form-control-xs" id="confirm_password" autocomplete="off" />',
                            '</div>',
                        '</div>',
                    '</div>',
                '</form>'
			];


			// Create custom modal
			notify('Change Password', change_password_form.join('\n'), function () {

			});

			// Temporary remove modal dismiss
			modal_button.removeAttr('data-dismiss').html('Proceed');

			// Custom modal event
			modal_button.on('click',function (e) {

				e.preventDefault();


				var cpf_value = {
					old_password: $('#old_password').val().toString(),
					new_password: $('#new_password').val().toString(),
					confirm_pass: $('#confirm_password').val().toString()
				};
				
				if (cpf_value.old_password && cpf_value.new_password && cpf_value.confirm_pass) {

					if (cpf_value.old_password !== cpf_value.new_password) {

						if (cpf_value.new_password === cpf_value.confirm_pass) {

							ajax_call('change_password', cpf_value, function (response) {

								if (is_error(response, true)) {

									alert_error(response.message);

								} else {

									alert_success(response.message);

									modal_button.attr('data-dismiss', 'modal').html('Okay');
								}

							});

						} else {

							alert_error('Passwords didn\'t matched');
						}

					} else {

						alert_error('Please get a new password.');
					}
				
				} else {

					alert_error('All fields are required.');
				}
			});
		});
	};


	Dashboard.prototype.ctrl_logout = function () {

		$('#nav_logout').on('click', function (e) {

	        e.preventDefault();

	        window.location = __BASE_URL + '/logout';
	    });
	};


	Dashboard.prototype.ui_navigation = function (default_load) {

		var self = this;

		var __dom = {
			sidebar: 		$('#sidebar'),
			navibar: 		$('#navibar'),
			content: 		$('#content'),

			sidebar_toggle: $('#sidebar_toggle'),
			sidebar_nav: $('.sidebar-nav > li'),
			sidebar_submenu: $('.sidebar-nav-sub'),

			main_content: $('#main_content')
		};

		var __fn_menu_toggle = function () {

			__dom.sidebar.toggleClass('sidebar-toggle');
			__dom.navibar.toggleClass('navibar-toggle');
			__dom.content.toggleClass('content-toggle');
		};

		if (parseInt(window.innerWidth) <= 820) {
			__fn_menu_toggle();
		}

		__dom.sidebar_toggle.on('click', function () {
			__fn_menu_toggle();
		});


		var __fn_submenu_toggle = function (d, toggle) {

			toggle = is_defined(toggle) ? toggle : true;

			var arrow = toggle ? 'expand_less' : 'expand_more';


			if (d.hasClass('sidebar-nav-menu')) {

				d.children('.icon-right').html(arrow);

				if (toggle) {

					d.next('.sidebar-nav-sub').slideDown();
				
				} else {

					d.next('.sidebar-nav-sub').slideUp();
				}
			}
		};


		// Main sidebar options content load
		__dom.sidebar_nav.on('click', function (e) {

			e.preventDefault();

			var dom = $(this);

			if (dom.hasClass('active')) {

				if (is_defined(__ACTIVE_NAV) && dom.is(__ACTIVE_NAV)) {

					__fn_submenu_toggle(dom, false);

					dom.removeClass('active');
					__ACTIVE_NAV = undefined;
				}

			} else {

				if (is_defined(__ACTIVE_NAV)) {

					__fn_submenu_toggle(__ACTIVE_NAV, false);

					if (__ACTIVE_NAV.hasClass('active')) {
						__ACTIVE_NAV.removeClass('active');
						__ACTIVE_NAV = undefined;
					}
				}

				dom.addClass('active');
				__ACTIVE_NAV = dom;


				// Toggle sub menu if any
				__fn_submenu_toggle(dom);


				// Load page content from AJAX response
				self.ctrl_menu(dom);
			}
		});


		// Sub sidebar options content load
		__dom.sidebar_submenu.children('li').on('click', function (e) {

			e.preventDefault();

			// Load page content from AJAX response
			self.ctrl_menu($(this));

		});


		if (is_defined(default_load) && $(default_load).length) {
			$(default_load).click();
		}
	};


	Dashboard.prototype.ui_table = function (module, url, response, operations, target) {

		var self = this;


		if (is_defined(url) && is_defined(response)) {

			/**
			 * Fetch json data via AJAX
			 * then load to the DataTable.
			 * 
			 * This method allows faster and
			 * easy way of customizing the 
			 * output tables.
			 */
			if (is_defined(response) && typeof response == 'object') {


				// Initialize table container
				var table_data = __helper_create_table(url, response);


				// Check if any target dom is provided
				var target_dom = (is_defined(target) && $(target).length > 0) ? $(target) : $('#main_content');


				// Inject dom container
				target_dom.html(table_data).promise().done(function () {

					// Check if there are any rows returned
					// and show a custom display whenever the 
					// datagrid is empty.
					if (count(response)) {

						var table_id = '#datatable_dom';

						// Prevent dom stacking of alt-editor
						if ($('#altEditor-modal').length) {
							$('#altEditor-modal').remove();
						}

						// Check the required operations.
						var buttons = [];
						var button_settings = {

							// Basic CRUD Operations
							add: {
								name: 'add',
								text: '<i class="material-icons">create</i> New Record'
							},

							edit: {
								extend: 'selected',
								name: 'edit',
								text: '<i class="material-icons">build</i> Update'
							},

							delete: {
								extend: 'selected',
								name: 'delete',
								text: '<i class="material-icons">delete_forever</i> Delete'
							},


							// Custom/Extra Operations
							reset_password: {
								extend: 'selected',
								name: 'reset_password',
								text: '<i class="material-icons" id="crud_reset_password">security</i> Reset Password'
							}
						};
						
						if (is_defined(operations)) {
							
							$.each(operations, function (i, operation) {

								if (button_settings.hasOwnProperty(operation)) {

									buttons.push(button_settings[operation]);
								}
							});
						}


						// Datatable default column definitions
						var columnDefs = [

							// Automatically removes the first
							// column (expected to be the PK)
							{
								targets: [0],
								visible: false,
								searchable: false
							},

							// Assigns editable property to
							// the cells.
							{ 
								targets: response.editable,
								className: 'editable_cell'
							}
						];

						// Datatable custom column rendering.
						// 
						// @note: `targets` values are staticcally defined and
						// is based on the position as described on response.header.
						var customDefs = {
							employees: [
								{
									// Avatar
									targets: 1,
									render: function (data, type, row) {
										return '<img src="' + __AVATAR_URL + '/' + data + '" class="datatable-avatar" />';
									}
								}
							]
						};

						if (customDefs.hasOwnProperty(url)) {

							$.each(customDefs[url], function (i, custom_rendering) {
								columnDefs.push(custom_rendering);
							});
						}



						var table_dom = $(table_id);

						// Attach module identity to the table. 
						table_dom.data('module', module);


						// Create datatable
						table_dom.DataTable({

							data: response.rows,
							columns: response.header,
							aaSorting: [],

							lengthMenu: [
								[10, 25, 50], 
								[10, 25, 50, 'All']
							],
							dom: '<"search"f>Btipr',

							columnDefs: columnDefs,

							select: 'single',
							responsive: true,
							altEditor: true,
							buttons: buttons

						}).on('savedata', function (e, action, rowdata, success, error) {

							if (in_array(action, ['add', 'edit', 'delete'])) {

								var rest_url = url + '/' + action;

								// Get editable column names
								var editable_params = [];
								$.each(response.header, function (i, v) {

									if (in_array(i, response.editable)) {

										var title_params = v.title,
											parse_params = (title_params.toLowerCase()).split(' ').join('_')

										editable_params.push(parse_params);
									}
								});


								var params = __helper_clean_params(rowdata, editable_params);

								if (rowdata.hasOwnProperty('id')) {
									params.id = rowdata.id;
								}


								self.CRUD(action, rest_url, params, success, error);
							}
						});


						// Add toolbar
						$('.dt-button').addClass('btn btn-xs btn-raised btn-danger toolbar-btn-fix').removeClass('dt-button');

						$('div.dataTables_filter').addClass('form-group form-filter');
						$('div.dataTables_filter input').addClass('form-control');
						$('div.dataTables_filter input').removeClass('input-sm');
						$('div.dataTables_filter input').attr('autofocus', 'autofocus');

						$('.dataTables_wrapper > .search').append('<div class="clearfix"></div>');


						// Activate module specific plugins
						self.CRUDForm();


						// Activate material design to the
						// dynamically added components.
						$.material.init();

					} else {

						$('#main_content .panel-body').html('<h4 class="text-center">No available data to display.</h4>');
					}
				});
			
			} else {

				throw Error('Cannot convert data to table.');
			}
		}
	};


	Dashboard.prototype.module_set_appointment = function (response) {
		
		var self = this;

		$('#main_content').html(response);
		$.material.init();


		// Extract initial booking data
		var data = $('#appointment_form').data('options'),
			info = {},
			dom = {
				email: 				$('#email'),
				date: 				$('#date'),
				cleaner: 			$('#cleaner'),
				time_start: 		$('#time_start'),
				time_end: 			$('#time_end'),
				price: 				$('#price'),
				supplies: 			$('#supplies'),
				instructions: 		$('#instructions'),
				instruction_area: 	$('#instruction_area'),
				save_booking: 		$('#save_booking')
			};

		if (is_defined(data) && data.length > 0) {

			$.each(data, function (i, v) {

				var k_date_available = v.date_available;

				var k_cleaner_fullname = [
					v.employee_id,
					v.firstname,
					v.lastname
				].join('|');


				if (typeof info[k_date_available] == 'undefined') {

					info[k_date_available] = [];
					info[k_date_available].push(k_cleaner_fullname);

				} else {

					info[k_date_available].push(k_cleaner_fullname);
				}
			});

		} else {

			notify('Cleaner not Available', 'No available cleaners at the moment. Please set up one on <b>Booking >> Timetables</b>.');

			throw Error('Cannot start the operation with incomplete data.');
		}


		// Email of client the admin wish to
		// book in behalf.
		dom.email.on('keyup', function (e) {

			var get_email = $(this).val();

			if (valid_email(get_email)) {

				dom.date.html('<option value="undefined">Choose Available Date</option>');

				// Load data to Date Selection
				var date_selection = [];
				$.each(info, function (i, v) {
					date_selection.push('<option value="' + i + '">' + i + '</option>');
				});

				dom.date.removeAttr('disabled').append(date_selection.join(''));

				__mfn_select_date(info);
			}
		});		


		function __mfn_select_date(info) 
		{

			return dom.date.on('change', function (e) {

				e.preventDefault();

				var date_dom = $(this);
				var date_selected = date_dom.find(":selected").attr('value');

				if (typeof date_selected != 'undefined') {

					dom.cleaner.removeAttr('disabled', false);

					__mfn_select_cleaner(date_selected, info);

				} else {

					dom.cleaner.prop('disabled', 'disabled');

					__mfn_select_cleaner();
				}
			});
		}
		

		// Load data to Cleaner Selection
		function __mfn_select_cleaner(date, info) 
		{
			dom.cleaner.html('<option value="undefined">Choose a Cleaner</option>');

			if (is_defined(date) && is_defined(info)) {

				if (typeof info[date] != 'undefined') {

					var cleaners = info[date],
						cleaner_selection = [];

					$.each(cleaners, function (i, cleaner) {

						var cleaner_data = cleaner.split('|');

						cleaner_selection.push('<option value="'+ cleaner_data[0] +'">');
						cleaner_selection.push([
							cleaner_data[1],
							cleaner_data[2]
						].join(' '));
						cleaner_selection.push('</option>');
					});

					dom.cleaner.append(cleaner_selection.join(''));


					return dom.cleaner.on('change', function (e) {

						e.preventDefault();

						var cleaner_dom = $(this);
						var cleaner_selected = cleaner_dom.find(":selected").attr('value');

						if (typeof cleaner_selected != 'undefined') {

							dom.cleaner.removeAttr('disabled', false);

							__mfn_time_start();

						} else {

							dom.cleaner.prop('disabled', 'disabled');

							__mfn_time_start(false);

							__mfn_allow_submit(false);	
						}
					});
				}
			}

			return false;
		}


		// Free form start and end time
		function __mfn_time_start(toggle)
		{
			toggle = is_defined(toggle) ? toggle : true;


			if (toggle) {

				dom.time_start.removeAttr('disabled');

				dom.time_start.timepicker({
	                minuteStep: 	1,
	                showSeconds: 	false,
	                showMeridian: 	false,
	                defaultTime: 	false,
	                explicitMode: 	true,
	                template: 		false
	            });

				dom.time_start.on('keyup', function (e) {

					var value = $(this).val().length;

					if (value > 0) {

						__mfn_time_end();
					
					} else {

						__mfn_time_end(false);

						__mfn_allow_submit(false);
					}
				});

			} else {

				dom.time_start.prop('disabled', 'disabled');
			}
		}

		function __mfn_time_end(toggle)
		{
			toggle = is_defined(toggle) ? toggle : true;

			if (toggle) {

				dom.time_end.removeAttr('disabled');

				dom.time_end.timepicker({
	                minuteStep: 	1,
	                showSeconds: 	false,
	                showMeridian: 	false,
	                defaultTime: 	false,
	                explicitMode: 	true,
	                template: 		false
	            });

				dom.time_end.on('keyup', function (e) {

					var value = $(this).val().length;

					if (value > 0) {

						__mfn_text_price();
					
					} else {

						__mfn_text_price(false);

						__mfn_allow_submit(false);
					}
				});

			} else {

				dom.time_end.prop('disabled', 'disabled');
			}
		}



		// Free form price
		function __mfn_text_price(toggle) 
		{
			toggle = is_defined(toggle) ? toggle : true;

			if (toggle) {

				dom.price.removeAttr('disabled');

				dom.price.on('keyup', function (e) {

					var value = $(this).val(),
						length = value.length;

					var float = parseFloat(value);


					if (!isNaN(float) && float > 0 && length > 0) {

						__mfn_checkboxes();
					
					} else {
						
						dom.price.val(0);

						__mfn_checkboxes(false);

						__mfn_allow_submit(false);
					}
				});

			} else {

				dom.price.prop('disabled', 'disabled');
			}
		}
		

		// Enable supplies and special instructions
		function __mfn_checkboxes(toggle)
		{
			toggle = is_defined(toggle) ? toggle : true;

			if (toggle) {

				dom.supplies.removeAttr('disabled');
				dom.instructions.removeAttr('disabled');

				__mfn_allow_submit();


				// Check boxes toggle cosmetics
				dom.supplies.switcher();
				dom.instructions.switcher(function (is_checked) {

		            var text = dom.instruction_area;

		            if (is_checked) {

		                text.removeClass('hidden').hide().fadeIn(500);

		            } else {

		                text.siblings('#instruction_area').val('');
		                text.addClass('hidden');
		            }
		        });

			} else {

				dom.supplies.prop('disabled', 'disabled');
				dom.instructions.prop('disabled', 'disabled');

				__mfn_allow_submit(false);
			}
		}

		function __mfn_allow_submit(toggle)
		{
			toggle = is_defined(toggle) ? toggle : true;

			if (toggle) {

				dom.save_booking.removeAttr('disabled');

			} else {

				dom.save_booking.prop('disabled', 'disabled');
			}
		}


		dom.save_booking.on('click', function () {

			var data = {};

			$.each(dom, function (i, o) {

				if (i !== 'save_booking') {

					var get_value = (i === 'instruction_area') ? $('textarea#instruction_area').val() : o.val();

					data[i] = $.trim(get_value);
				}
			});

			ajax_call('xpressbook', data, function (response) {

				if (response.hasOwnProperty('status')) {

					notify(response.status, response.message);
				}
			});
		});
	};

	function __helper_clean_params(data, editable)
	{
		var _data = {};

		if (is_defined(data) && is_defined(editable)) {

			$.each(data, function (i, v) {

				var __i = i.toLowerCase();

				if (in_array(__i, editable) || __i === 'id') {

					var column_name = (i.toLowerCase()).split(' ').join('_');

					_data[column_name] = v;
				}
			});
		}

		return _data;
	}

	function __helper_create_table(title, data)
	{
		title = is_defined(title) ? title : '';


		var html = [];

		html.push('<div class="panel panel-rosie">');
			html.push('<div class="panel-heading">');
				html.push('<h1 class="panel-title">' + title + '</h1>');
			html.push('</div>');

			html.push('<div class="panel-body">');
				html.push('<table id="datatable_dom" class="table table-responsive table-condensed table-striped table-bordered" width="100%">');

				if (data.hasOwnProperty('columns')) {
					html.push('<thead>');
			            html.push('<tr>');
							$.each(data.columns, function (i, column) {
								var __column = column.replace('_', ' ');

				                html.push('<th>' + __column.toUpperCase() + '</th>');
							});
			            html.push('</tr>');
			        html.push('</thead>');
				}

				html.push('</table>');
			html.push('</div>');
		html.push('</div>');

		return html.join('\n');
	}


	// Run the app!
	(new Dashboard).init();

}(window.Dashboard = window.Dashboard || function () {}, jQuery);
