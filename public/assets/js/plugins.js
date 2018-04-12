/**
 * @author Vainglory07
 * @copy 2016
 *
 */


if (typeof jQuery == 'undefined') {
	throw new Error ('jQuery is required :)');
}

// HTML5 Boilerplate v5.3.0
// Avoid `console` errors in browsers that lack a console.
+function() {
    "use strict";


    var method;
    var noop = function () {};
    var methods = [
        'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
        'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
        'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
        'timeline', 'timelineEnd', 'timeStamp', 'trace', 'warn'
    ];
    var length = methods.length;
    var console = (window.console = window.console || {});

    while (length--) {
        method = methods[length];

        // Only stub undefined methods.
        if (!console[method]) {
            console[method] = noop;
        }
    }
}();


+function () {
	'use strict';
 
	/**
	 * Windows mobile viewport fix
	 *
	 * Copyright 2014-2015 Twitter, Inc.
	 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
	 *
	 */
	if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
		var msViewportStyle = document.createElement('style')
		msViewportStyle.appendChild(
			document.createTextNode(
				'@-ms-viewport{width:auto!important}'
			)
		)

		document.querySelector('head').appendChild(msViewportStyle)
	}

	// Android stock browser fix
	var nua = navigator.userAgent
	var isAndroid = (nua.indexOf('Mozilla/5.0') > -1 && nua.indexOf('Android ') > -1 && nua.indexOf('AppleWebKit') > -1 && nua.indexOf('Chrome') === -1)
	if (isAndroid) {
		$('select.form-control').removeClass('form-control').css('width', '100%')
	}


	// Disable right click	
	$(document).on('contextmenu', function (e) {
		e.preventDefault();
		return false;
	});

}();


+function ($) {

	'use strict';

	/**
	 *	Helper Functions
	 *
	 */
	window.is_defined = function(data) {
		return (typeof data != 'undefined' && typeof data != 'null' && data != null) ? true : false;
	};

	window.is_callback = function (fn) {
		return (fn && typeof(fn) === "function") ? true : false;
	};

	window.is_mobile = function () {
		if( /Android|webOS|iPhone|iPad|iPod|Mobile|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
	        return true;
	    } else {
	        return false;       
	    }
	};

	window.in_array = function(needle, haystack) {
		
		var i;

		if (is_defined(needle) && is_defined(haystack)) {

			if (typeof needle != 'object' && typeof haystack == 'object') {
				
				try {

					i = haystack.indexOf(needle);

				} catch (x) {

					i = haystack.hasOwnProperty(needle) ? 1 : -1;
				}

				return (i >= 0) ? true : false;
			}
		}

		return false;
	};

	window.array_keys = function (arr) {

		if (is_defined(arr)) {

			var r = [];

			for (var k in arr) {

				r.push(k);
			}

			return r;
		}
	};

	window.array_values = function (arr) {

		if (is_defined(arr)) {

			var r = [];

			for (var k in arr) {

				r.push(arr[k]);
			}

			return r;
		}
	};

	window.array_search = function (val, arr) {

		var r = [];

	    for (var i = 0; i < arr.length; i++) {
	    	
	        if (arr[i] == val) r.push(i);
	    }

	    if (r.length > 0) {

	    	if (r.length == 1) {

	    		return r[0];
	    	}

	    	return r;
	    } 

	    return false;
	        
	};

	window.count = function(o) {
		if (typeof o === 'object') {
			return (o.length != 'undefined') ? Object.keys(o).length : o.length;
		} return 0;
	};

	window.strlen = function (s) {
		return is_defined(s) ? count(s.toString().split('')) : 0;
	};

	window.ucfirst = function (string) {
		return string.charAt(0).toUpperCase() + string.slice(1);
	};

	window.ucwords = function (string) {
		return string.toLowerCase().replace(/\b[a-z]/g, function(letter) {
		    return letter.toUpperCase();
		});
	};


	// Added decimal places from given
	// number up to 2 without rounding off
	window.money_format = function (v, n) {

		if (is_defined(v)) {

			n = is_defined(n) ? n : 2;
			
			v = parseFloat(v);

			return (v > 0) ? cut_decimal(v, n) : v;
		
		} return 0;

	};

	// Cuts the number of decimal
	// places without rounding off the value.
	window.cut_decimal = function (v, n) {

		if (is_defined(v)) {

			n = is_defined(n) ? n : 2;

			var str_v = v.toString().split('.');

			if (is_defined(str_v[1])) {
				str_v[1] = str_v[1].substr(0, n);
			}

			var new_v = str_v.join('.');

			return parseFloat(new_v).toFixed(n);

		} return 0;

	};

	// Added comma to separate 
	// thousands
	window.balance_format = function (v) {

	    var parts = v.toString().split('.');
	    
	    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	    
	    return parts.join('.');
	};

	window.mt_rand = function (min, max) { 

		var argc = arguments.length;
		
		if (argc === 0) {
		
			min = 0
			max = 2147483647
		
		} else if (argc === 1) {
		
			throw new Error('Warning: mt_rand() expects exactly 2 parameters, 1 given')
		
		} else {
		
			min = parseInt(min, 10)
			max = parseInt(max, 10)
		}
		
		return Math.floor(Math.random() * (max - min + 1)) + min
	}

	window.extract_args = function(args, vars) {
		var data = {};

		if (is_defined(args) && is_defined(vars)) {
			$.each(vars, function (i, v) {
				if (in_array(i, args)) {
					data[v] = args[i];
				}
			});
		} 

		return count(data) ? data : false;
	};

	window.is_json = function (v) {
		try {
			$.parseJSON(v);
		} catch (e) {
			return false;
		}; return true;
	};

	window.is_error = function(response) {

		if (typeof response == 'object') {

			return (response.hasOwnProperty('status') && (response.status.trim() === 'Error' || response.status.trim() === 'error')) ? true : false;
		}

	};

	window.valid_email = function(email) {

        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;

        return regex.test(email);
    };

    window.empty = function (value) {

        return (
            typeof value == 'undefined' 
            || typeof value == 'null' 
            || value == null 
            || value == ''
        ) ? true : false;
    };

    window.ajax_call = function (url, params, callback, method) {

    	method = is_defined(method) ? method : 'POST';

    	$.ajax({
    		url: 	url,
    		data: 	typeof params == 'object' ? params : {},
    		method: method,
    		success: function (response) {

				if (typeof callback == 'function') {

					callback(response);
				}

				if ((typeof response == 'object') && is_error(response)) {

					throw Error(response.message);
				
				}
    		}
    	});
    };

    window.error_call = function (error_message) {

		var __ERROR = {
			display: $('#error_notif'),
			message: $('#error_message')
		};

		if (is_defined(error_message)) {
			
			__ERROR.message.html(error_message);
			__ERROR.display.fadeIn(500);
		}

		if (error_message === false) {

			__ERROR.display.hide();
		}
    };

    window.success_call = function (success_message) {

		var __SUCCESS = {
			display: $('#success_notif'),
			message: $('#success_message')
		};

		if (is_defined(success_message)) {
			
			__SUCCESS.message.html(success_message);
			__SUCCESS.display.fadeIn(500);
		}

		if (success_message === false) {

			__SUCCESS.display.hide();
		}
    };

    window.strip_leading_zero = function (value) {


    	if (is_defined && typeof value == 'string') {

    		if (value[0] == 0) {

    			return value.substring(1);
    		}
    	}

    	return value;
    };

    window.is_mobile = function () {

		return (/Android|webOS|iPhone|iPad|iPod|Mobile|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) ? true: false;
	};

}(jQuery);


+function ($) {
	'use strict';


	/**
	 * Global AJAX Filters
	 * 
	 */


    // Laravel CSRF Authentication
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


	/*!
	 * Set Request Filter
	 *
	 * Abort succeeding ajax requests
	 * if the previous request is
	 * still waiting for response.
	 * 
	 */
	$.xhrPool = [];
	$.xhrPool.abortAll = function() {
	    $(this).each(function(i, jqXHR) {   //  cycle through list of recorded connection
	        jqXHR.abort();          		//  aborts connection
	        $.xhrPool.splice(i, 1); 		//  removes from list by index

	        $.ajax(jqXHR);
	    });
	}


	$(document).ajaxComplete(function (event, xhr, settings) {
        var response = xhr.getResponseHeader('_TOKEN_');
        $('meta[name="csrf-token"]').attr('content', response);
    });

}(jQuery);


// Custom jQuery plugins
+function ($) {
    "use strict";

    $.fn.has_data = function (attr) {

		var attr_filter = attr.replace('data-', ''),
			attr_fix = 'data' + '-' + attr_filter;

		return $(this)[0].hasAttribute(attr_fix);
	};

	$.fn.add_data = function () {

		var args = count(arguments),
			attr = {};

		if (args === 1) {

			$.each(arguments[0], function (i, v) {
				attr['data-' + i] = v;
			});
		} else if (args === 2) {

			attr['data-' + arguments[0]] = arguments[1];
		}


		return this.each(function (i, v) {
			$.each(attr, function (a_i, a_v) {

				$(v).attr(a_i, a_v);
			});
		});
	};


    // Checkbox switcher label updater
    $.fn.switcher = function (callback) {

        return this.each(function (i, dom) {

            var o = $(dom);

            // Expected <span> after checkbox for text
            // display update. Create if not existing.
            var s = o.siblings('span.check-value');

            if (s.length === 0) {

                var t = o.siblings('span.toggle');
                t.after('<span class="check-value">None</span>');
                s = t.siblings('span.check-value');
            }

            o.change(function () {
                
                if (this.checked) {

                    o.val(1);

                    s.html('Yes');

                } else {

                    o.val(0);

                    s.html('None');
                }

                if (typeof callback != 'undefined' && typeof callback === 'function') {
                    callback(this.checked);
                }
            });

        });
    };


    // Booking Steps
    $.fn.booking_steps = function (callback, bypass, ajax_params) {

        bypass = typeof bypass == 'undefined' ? false : bypass;

        ajax_params = typeof ajax_params == 'undefined' ? {} : ajax_params;

        return this.each(function (i, dom) {

            var cls_disabled = 'booking-disabled';

            var o = $(dom);
            var d = $('#booking_form');

            var ajax_url = o.data('stepurl');


            if (ajax_url.length > 0) {

                if (o.hasClass(cls_disabled) === false || bypass === true ) {

                	d.html('<div class="preloader"></div>');

                    $.post(
                        ajax_url,
                        ajax_params,
                        function (response_html) {

                            d.html(response_html).promise().done(function () {
                                $.material.init();

                                if (typeof callback === 'function') {
                                    callback();
                                }
                            });
                        }
                    );
                }
            }

        });
    };

    $.fn.step_disabled = function (is_disabled) {

        is_disabled = typeof is_disabled == 'undefined' ? true : is_disabled;

        return this.each(function (i, dom) {

            var o = $(dom);
            var cls_disabled = 'booking-disabled';

            if (o.length > 0) {

                if (is_disabled === true) {

                    if (! o.hasClass(cls_disabled)) {

                        o.addClass(cls_disabled);
                        
                    }

                } else {
                    
                    if (o.hasClass(cls_disabled)) {

                        o.removeClass(cls_disabled);
                    }                    
                }
            }

            return o;
        });
    };

    // Rating Plugin
    $.fn.stars = function () {
        
        return this.each(function (i, dom) {

            var o = $(dom);
            var r = parseFloat($(o).data('rating'));
            var c = Math.ceil(r);
            var m = 5 - c;


            var d = [];

            for (var i = 0; i < 5; i++) {

                if (r > 0) {

                    if (r < 1 && r > 0) {

                        d.push('<i class="material-icons">star_half</i>');

                    } else {

                        d.push('<i class="material-icons">star</i>');
                    }

                }

                r--;
            }

            for (var ctr = 0; ctr < m; ctr++) {

                d.push('<i class="material-icons">star_border</i>');
            }

            return o.html(d.join("\n"));
        });
    };

}(jQuery);
