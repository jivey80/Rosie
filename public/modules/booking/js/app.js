"use strict";

if (typeof jQuery === 'undefined') {
	error_call('jQuery is required.');
}

+function(Rosie, $) {

	var __PROGRESS = {
		booking_details: $('#booking_details'),
		booking_schedule: $('#booking_schedule'),
		booking_confirmation: $('#booking_confirmation')
	};


	Rosie.prototype.globals = {

		name: null,
		mail: null,
		needed_hours: null,
		needed_supplies: null,
		instruction: null,

		book_id: null,
		slot_dom: null,
		book_dom: null,

		page: null
	};


	Rosie.prototype.run = function () {
		
		// Set a minimum resolution for mobile
		// and force landscape viewing
		// this.orientation_check();
		
		// Booking page
		this.details(true);

		// Review page
		this.review();

		// Device orientation detection
		if (is_mobile()) {
			  
			window.addEventListener('orientationchange', this.device_orientation_check);
			  
			// Initial execution if needed
			this.device_orientation_check();

		}
	};

	Rosie.prototype.device_orientation_check = function () {

		if (window.matchMedia("(orientation: portrait)").matches || window.innerHeight > window.innerWidth) {

			$('#orientation').show();

		} else {

			$('#orientation').hide();
		}

		$('#orientation').on('click', function () {

			$(this).hide();
		});
	};

	Rosie.prototype.review = function () {

		var self = this;

		var btn_submit_review = $('#submit_review');

		if (btn_submit_review.length > 0) {

			btn_submit_review.on('click', function (e) {

				// Disable button after click to preview spamming
				$(this).attr('disabled', 'disabled');

				var form_star = parseInt($('.rating-stars').data('rating'));
				var form_review = $('#txtReview').val();
				var form_bid = parseInt(btn_submit_review.data('bid'));
				var form_eid = parseInt(btn_submit_review.data('eid'));
				var form_cid = parseInt(btn_submit_review.data('cid'));

				ajax_call(__BASE_URL + '/save_review', {
					str: form_star,
					rvw: form_review,
					bid: form_bid,
					eid: form_eid,
					cid: form_cid
				}, function (response) {

					if (! is_error(response)) {

						$('#review_form').remove();

						$('#review_submit_message').html(response.message);
						$('#review_success').removeClass('hide');

	    			} else {

	    				$('#booking_form').html([
							'<h3 class="booking-error">',
								response.message,
							'</h3>'
						].join("\n"));
	    			}
				});
			});
		}
	};


	Rosie.prototype.details = function (bypass) {

		var self = this;

		if (__PROGRESS.booking_details.length > 0) {

			bypass = typeof bypass == 'undefined' ? false : bypass;


			// Remove alerts if any
			error_call(false);
			success_call(false);

			
			return __PROGRESS.booking_details.booking_steps(function () {

				var form = {
					name: $('#inputName'),
					mail: $('#inputEmail'),

					needed_hours: $('#inputHours'),
					needed_supplies: $('#inputSupplies'),

					btn_instruction: $('#inputInstruction'),
					instruction: $('#txtInstruction'),
				};

				// Activate switchers
		        form.needed_supplies.switcher();
		        $('#inputInstruction').switcher(function (is_checked) {

		            var text = $('#instruction_toggle');

		            if (is_checked) {

		                text.removeClass('hidden').hide().fadeIn(500);

		            } else {

		                text.siblings('#txtInstruction').val('');
		                text.addClass('hidden');
		            }
		        });


		        // Fill-up the form with defaults
		        // if available.
		        if (! empty(self.globals.name)) {
		        	form.name.val(self.globals.name);
		        	form.name.parent('.label-floating').removeClass('is-empty');
		        }

		        if (! empty(self.globals.mail)) {
		        	form.mail.val(self.globals.mail);
		        	form.mail.parent('.label-floating').removeClass('is-empty');
		        }

		        if (! empty(self.globals.needed_hours)) {
		        	form.needed_hours.val(self.globals.needed_hours);
		        }

		        if (! empty(self.globals.needed_supplies)) {
		        	
		        	form.needed_supplies.val(self.globals.needed_supplies);
		        	
		        	if (self.globals.needed_supplies === 1) {
		        		form.needed_supplies.click();
		        	}
		        }

		        if (! empty(self.globals.instruction)) {

		        	form.btn_instruction.click().promise().done(function () {
		        		form.instruction.val(self.globals.instruction);
		        	});
		        }


		        // Footer buttons
		        $('#details_next, #booking_schedule').off('click').click(function (e) {

		        	var tab = __PROGRESS.booking_schedule.attr('class');
		        	var chk = tab.indexOf('booking-disabled');
		        	
		        	if (chk >= 0) {

		        		// Form validation
						var val_name = form.name.val(),
							val_mail = form.mail.val(),
							val_hour = parseFloat(form.needed_hours.val()),
							val_supp = parseInt(form.needed_supplies.val()),
							val_inst = form.instruction.val();


						if (!empty(val_mail)) {

							ajax_call(__BASE_URL + '/verify_email', {

				    			mail: val_mail

				    		}, function (response) {

				    			if (! is_error(response)) {

									// Save to globals
									self.globals.name = (response.hasOwnProperty('name') && !empty(response.name)) ? ucwords(response.name) : ucwords(val_name);
									self.globals.mail = (response.hasOwnProperty('mail') && !empty(response.mail)) ? response.mail : val_mail;

									self.globals.needed_hours = val_hour;
									self.globals.needed_supplies = val_supp;
									self.globals.instruction = val_inst;


						        	// Steps toggle
									__PROGRESS.booking_details.step_disabled();
									__PROGRESS.booking_schedule.step_disabled(false);
									__PROGRESS.booking_confirmation.step_disabled();


									// Step trigger
						        	self.schedule(true);

				    			} else {
				    				
				    				error_call('<a href="javascript:void(0)" class="alert-link">Email</a> is not available.');
				    			}

				    		});

						} else {

							error_call('Please fill-up the <a href="javascript:void(0)" class="alert-link">Email Address</a>.');
						}
		        	}

		        });

			}, bypass);
		}
	};

	Rosie.prototype.schedule = function (bypass, ajax_params) {

		var self = this;

		if (__PROGRESS.booking_schedule) {

			bypass = typeof bypass == 'undefined' ? false : bypass;

			ajax_params = typeof ajax_params == 'undefined' ? {

				hours: parseFloat($('#inputHours').val()),

				mail: self.globals.mail

			} : ajax_params;


			// Remove alerts if any
			error_call(false);
			success_call(false);


			return __PROGRESS.booking_schedule.booking_steps(function () {

				// Activate ratings
				$('.person-rating').stars();


				// Details toggle
				$('.booking-details').on('click', function (e) {

					var dom = $(this);
					var frame_id = dom.attr('id');


					// Check toggle
					var is_toggled = dom.attr('data-toggle');
					var is_toggled_int = parseInt(is_toggled);


					if (typeof is_toggled === 'undefined' || is_toggled_int === 0) {

						dom.attr('data-toggle', 1);
						dom.html('Details <i class="material-icons">arrow_upward</i>');
						dom.addClass('btn-info');
						dom.removeClass('btn-danger');


						var _fid = frame_id.split('_'),
							_did = _fid[1],
							_dom = $('#booking_preloader_' + _did);


						if (dom.length > 0) { _dom.removeClass('hide'); }

						ajax_call(__BASE_URL + '/list_schedule', {

							id: frame_id,

							hours: self.globals.needed_hours,

							mail: self.globals.mail

						}, function (response) {

							if (dom.length > 0) { _dom.addClass('hide'); }

							self.details_extract(frame_id, response);

							if (response.length) {
							
								_fn_slotlist_toggle(frame_id);
							
							} else {

								$('#schedule_5').prop('disabled', true).text('FULLY BOOKED');
							}
						});

					} else if (is_toggled_int === 1) {

						dom.attr('data-toggle', 0);
						dom.html('Details <i class="material-icons">arrow_downward</i>');
						dom.addClass('btn-info');
						dom.removeClass('btn-danger');

						_fn_slotlist_toggle(frame_id);

					} else {

						_fn_confirm_booking();
					}

				});


				// Check if there's already toggled details
				if (is_defined(self.globals.book_dom) && self.globals.book_dom.length > 0) {
					$('#' + self.globals.book_dom.attr('id')).click();
				}


				// Footer buttons
	    	 	$('#schedule_prev, #booking_details').off('click').click(function () {

	    	 		var tab = __PROGRESS.booking_details.attr('class');
		        	var chk = tab.indexOf('booking-disabled');
		        	
		        	if (chk >= 0) {

		    	 		// Steps toggle
						__PROGRESS.booking_details.step_disabled(false);
						__PROGRESS.booking_schedule.step_disabled();
						__PROGRESS.booking_confirmation.step_disabled();


						// Step trigger
		    	 		self.details(true);
		        	}
	    	 	});


	    	 	$('#schedule_next, #booking_confirmation').off('click').click(function () {

	    	 		var tab = __PROGRESS.booking_confirmation.attr('class');
		        	var chk = tab.indexOf('booking-disabled');
		        	
		        	if (chk >= 0) {

						_fn_confirm_booking();
		        	}
	    	 	});


	    	 	function _fn_confirm_booking() {

	    	 		// Booking validation
					if (!empty(self.globals.book_id) && !empty(self.globals.slot_dom)) {

						// Steps toggle
						__PROGRESS.booking_details.step_disabled();
						__PROGRESS.booking_schedule.step_disabled();
						__PROGRESS.booking_confirmation.step_disabled(false);


						// Step trigger
						self.confirmation(true);

					} else {

						error_call('Please select a booking schedule.');
					}
	    	 	}

	    	 	function _fn_slotlist_toggle(frame_id) {

	    	 		var display_id = frame_id.replace('schedule_', ''),
						dom_display_box = $('#booking_option_' + display_id);

					if (dom_display_box.length <= 0) {

						error_call('Cannot find display box ' + frame_id);

					} else {

						dom_display_box.fadeToggle();
					}
	    	 	}

			}, bypass, ajax_params);
		}
	};

	Rosie.prototype.confirmation = function (bypass) {

		var self = this;

		if (__PROGRESS.booking_confirmation) {

			bypass = typeof bypass == 'undefined' ? false : bypass;


			// Remove alerts if any
			error_call(false);
			success_call(false);


			return __PROGRESS.booking_confirmation.booking_steps(function () {

				// Booking information
				if (!empty(self.globals.name) && !empty(self.globals.mail) && !empty(self.globals.book_id)) {

					ajax_call(__BASE_URL + '/booking_data', {

						book_id: self.globals.book_id,

						mail: self.globals.mail

					}, function (response) {

						if (! is_error(response)) {

							var cleaner_name = response.firstname + ' ' + response.lastname;
							$('#cleaner_name').html(cleaner_name);
							$('#cleaner_img').attr('alt', cleaner_name);
							$('#cleaner_img').attr('src', response.avatar);

							$('#cleaner_rating').attr('data-rating', parseFloat(response.rate)).stars();
							$('#cleaner_review').html(response.reviews);

							$('#cleaner_schedule').html(response.date_available);
							$('#cleaner_slot').html(
								strip_leading_zero(response.schedule_start) + 
								' to ' + 
								strip_leading_zero(response.schedule_end)
							);

							$('#cleaner_price').html(response.hours_text);
							$('#booking_total').html('$' + response.price);
							$('#booking_total').html('$' + response.price);

						} else {

							$('#confirmation_page').html([
								'<h3 class="booking-error">',
									response.message,
								'</h3>'
							].join("\n"));

						}
					});
				}


				// Footer buttons
	    	 	$('#confirmation_prev, #booking_schedule').off('click').click(function () {

	    	 		var tab = __PROGRESS.booking_schedule.attr('class');
		        	var chk = tab.indexOf('booking-disabled');
		        	
		        	if (chk >= 0) {
		
		    	 		// Steps toggle
						__PROGRESS.booking_details.step_disabled();
						__PROGRESS.booking_schedule.step_disabled(false);
						__PROGRESS.booking_confirmation.step_disabled();


						// Step trigger
		    	 		self.schedule(true, {

							hours: self.globals.needed_hours,

							mail: self.globals.mail

						});
		        	}
	    	 	});

	    	 	$('#back_to_main').on('click', function () {

	    	 		// Clear global data that is not more 
	    	 		// important.
	    	 		self.globals.needed_hours = null;
	    	 		self.globals.needed_supplies = null;
	    	 		self.globals.instruction = null;
	    	 		
	    	 		self.globals.book_id = null;
	    	 		self.globals.book_dom = null;
	    	 		self.globals.slot_dom = null;


	    	 		// Steps toggle
					__PROGRESS.booking_details.step_disabled(false);
					__PROGRESS.booking_schedule.step_disabled();
					__PROGRESS.booking_confirmation.step_disabled();


	    	 		// Step trigger
	    	 		self.details(true);
	    	 	});

				$('#confirmation_next').on('click', function () {

					ajax_call(__BASE_URL + '/booking_save', {
						
						book_id: self.globals.book_id,

						name: self.globals.name,
						mail: self.globals.mail,

						hours: self.globals.needed_hours,
						supplies: self.globals.needed_supplies,
						instruction: self.globals.instruction

					}, function (response) {

						if (! is_error(response)) {

							// Show success alert
							success_call(response.message);

							// Hide control buttons
							$('#confirmation_prev').hide();
							$('#confirmation_next').hide();

							// Show button back to the details page
							$('#back_to_main').removeClass('hide');

						} else {

							error_call(response.message);
						}

					});

				});

			}, bypass, {

				hours: self.globals.needed_hours

			});
		}
	};

	Rosie.prototype.details_extract = function (frame_id, details_object) {

		var self = this;

		if (typeof details_object == 'object') {

			if (details_object.length > 0) {

				var dom = [];

				for (var i in details_object) {

					var detail = details_object[i];

					dom.push(self.details_html(frame_id, detail));
					
				}

				// Toggle schedule details
				var display_id = frame_id.replace('schedule_', '');


				// Load the slots to the frame
				$('#schedule_frame_' + display_id).html(dom.join("\n"));


				// Attached the scroll events to the arrows
				var frame_box = $('#booking_option_' + display_id),
					frame_container = frame_box.children('.schedule-frame-container'),
					frame_arrows = frame_box.children('.panner');

				if (frame_container.length > 0 && frame_arrows.length > 0) {
					self.scroll_arrow(frame_container, frame_arrows);
				}



				// Attach click event on each schedule.
				$('.schedule-detail').on('click', function (e) {

					var dom = $(this);

					var inf = dom.attr('id').split('_'),
						tid = inf[1],
						sid = inf[2] + '_' + inf[3] + '_' + inf[4];

					var sch_btn_dom = $('#schedule_' + tid);


					function __dom_selected(s_dom, b_dom) {

						s_dom.addClass('schedule-selected');

						self.globals.slot_dom = s_dom;
						self.globals.book_dom = b_dom;
					}

					if (is_defined(self.globals.book_dom) && self.globals.book_dom.length > 0) {

						if (self.globals.book_dom.attr('id') !== 'schedule_' + tid) {
							self.globals.book_dom.attr('data-toggle', 0);
							self.globals.book_dom.click();
						}
					}


					if (! dom.hasClass('schedule-disabled')) {

						if (empty(self.globals.slot_dom)) {

							__dom_selected(dom, sch_btn_dom);
						
						} else {

							if (self.globals.slot_dom.length > 0) {

								self.globals.slot_dom.removeClass('schedule-selected');

								__dom_selected(dom, sch_btn_dom);

							} 
						}
						
						// Picked slot reference
						self.globals.book_id = sid;


						// Toggle to Book state the Detail button.
						if (sch_btn_dom.length > 0) {
							sch_btn_dom.addClass('btn-danger');
							sch_btn_dom.removeClass('btn-info');
							sch_btn_dom.html('Book <i class="material-icons">favorite</i>');

							sch_btn_dom.attr('data-toggle', 2);
						}
					}
				});

				// Check if there's already toggled slots
				if (is_defined(self.globals.slot_dom) && self.globals.slot_dom.length > 0) {

					var delay = setTimeout(function () {
						
						clearTimeout(delay);

						// $('#' + self.globals.slot_dom.attr('id')).click();

					}, 100);
				}
			}

		} else {

			error_call('Invalid response.');
		}
	};

	Rosie.prototype.details_html = function (id, detail_object) {

		var dom = [];

		var cls = [];

		cls.push('schedule-detail');

		if (detail_object.is_cheapest) {
			cls.push('schedule-cheapest');
		}

		if (detail_object.is_booked) {
			cls.push('schedule-disabled');
		}


		dom.push('<div class="' + cls.join(' ') + '" id="' + id + '_' + detail_object.detail_id + '">');
			
			dom.push('<p class="range">' + strip_leading_zero(detail_object.start) + '</p>');
			dom.push('<span>to</span>');
			dom.push('<p class="range">' + strip_leading_zero(detail_object.end) + '</p><br />');

			if (detail_object.is_booked) {

				dom.push('<p class="price"> Not Available </p>');

			} else {

				// @temp
				// Round of price to nearest 2 decimal
				// places while waiting for decision about
				// how multiple hour booking price should be
				// handled.
				var price_per_hour = (detail_object.price / detail_object.hours).toFixed(2);

				dom.push('<p class="price"> $' + price_per_hour + '/hour </p>');
			}

		dom.push('</div>');

		return dom.join("\n");
	};

	/**
	 * Scroller event for slot display arrows
	 * @param  obj _parent Frame container dom
	 * @param  obj _arrows Arrows dom
	 *
	 * @note
	 * needs double checking on mobile for the
	 * touchstart/touchend
	 * 
	 */
	Rosie.prototype.scroll_arrow = function (_parent, _arrows) {

		var scrollHandle = 0,
	        scrollStep = 5,
	        parent = _parent;

	    //Start the scrolling process
	    _arrows.on("mousedown touchstart", function () {
	        var data = $(this).data('scrollModifier'),
	            direction = parseInt(data, 10) * 2;
	        startScrolling(direction, scrollStep);
	    });

	    //Kill the scrolling
	    _arrows.on("mouseup touchend", function () {
	        stopScrolling();
	    });

	    //Actual handling of the scrolling
	    function startScrolling(modifier, step) {
	        if (scrollHandle === 0) {
	            scrollHandle = setInterval(function () {
	                var newOffset = parent.scrollLeft() + (scrollStep * modifier);
	                parent.scrollLeft(newOffset);
	            }, 10);
	        }
	    }

	    function stopScrolling() {
	        clearInterval(scrollHandle);
	        scrollHandle = 0;
	    }
	};

}(window.Rosie = window.Rosie || function(){}, jQuery);

+function (Rosie, $){

	new Rosie().run();

	$('.rating-stars').stars();

}(window.Rosie = window.Rosie || function() {}, jQuery);
