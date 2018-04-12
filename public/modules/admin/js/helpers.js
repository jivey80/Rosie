"use strict";

if (typeof jQuery === 'undefined') {
    error_call('jQuery is required.');
}


// Global Extensions
+function () {

    // Charting
    $.fn.chartjs = function (data_type) {

        function __chartjs_filter_data(stats, type)
        {
            if (is_defined(stats) && is_defined(type)) {

                switch (type) {

                    case 'date':

                        // Current graphical data
                        var label = stats.label,
                            data = stats.data;


                        // Setup the end of the month
                        var days = stats.days;
                        var last = parseInt(label[label.length - 1]);

                        var _days = (last < days) ? last : days;


                        // Combile graphical data
                        var _stats = {};

                        for (var ctr = 0; ctr < label.length; ctr++) {
                            var _l = label[ctr];
                                _stats[_l] = data[ctr];
                        }


                        // Fill-in the official data
                        // with missing days.
                        for (var d = 1; d <= _days; d++) {

                            var _d = d.toString();

                            if (! in_array(_d, label)) {
                                _stats[_d] = 0;
                            }
                        }


                        // Seperate new stats data
                        stats.label = Object.keys(_stats);
                        stats.data = Object.values(_stats);
                        break;

                    default: break;
                }
            }

            return stats;
        }


        return $(this).each(function (i, o) {

            data_type = is_defined(data_type) ? data_type : 'none';

            var dom = $(o);

            // Insert canvas com
            if (is_defined(dom.attr('data-chartid'))) {

                var canvas_id = dom.data('chartid'),
                    canvas_w = is_defined(dom.attr('data-width')) ? dom.data('width') : 300,
                    canvas_h = is_defined(dom.attr('data-height')) ? dom.data('height') : 100;

                var canvas_dom = '<canvas id="' + canvas_id + '" width="' + canvas_w + '" height="' + canvas_h + '"></canvas>';
                
                dom.html(canvas_dom).promise().done(function () {

                    if (is_defined(dom.attr('data-stats'))) {

                        var stats = __chartjs_filter_data(dom.data('stats'), data_type);

                        var chart_parameters = undefined;

                        switch (stats.type) {
                            case 'line':
                            default:
                                chart_parameters = {

                                    type: 'line',

                                    data: {
                                        labels: stats.label,
                                        datasets: [{
                                            label: stats.tooltip,
                                            data: stats.data,
                                            backgroundColor: 'rgba(187, 82, 68, 0.7)',
                                            
                                            borderColor: 'rgba(187, 82, 68, 1)',
                                            borderWidth: 1,
                                            borderDash: [5, 5],

                                            defaultFontSize: 10
                                        }]
                                    },

                                    options: {

                                        responsive: true,

                                        title: {
                                            display: true,
                                            text: stats.title
                                        },

                                        legend: {
                                            display: false
                                        },

                                        tooltips: {
                                            mode: 'nearest',
                                            intersect: false
                                        },

                                        scales: {
                                            yAxes: [{
                                                ticks: {
                                                    beginAtZero:true,
                                                    callback: function(value, index, values) {

                                                        // Don't need decimal values here
                                                        if (Math.floor(value) === value) {
                                                            return value;
                                                        }
                                                    }
                                                },
                                                scaleLabel: {
                                                    display: true,
                                                    labelString: stats.y_axis
                                                }
                                            }],
                                            xAxes: [{
                                                gridLines: {
                                                    display: false,
                                                    drawOnChartArea: false,
                                                },
                                                ticks: {
                                                    maxRotation: 90,
                                                    minRotation: 90
                                                },
                                                scaleLabel: {
                                                    display: true,
                                                    labelString: stats.x_axis
                                                }
                                            }]
                                        }
                                    }
                                };
                                break;

                            case 'bar': break;
                            case 'pie': break;
                        }


                        var ctx = document.getElementById(canvas_id);
                        
                        return new Chart(ctx, chart_parameters);
                    }
                });
            }
        });
    };


    $.fn.rankjs = function () {

        return $(this).each(function (i, o) {

            var dom = $(o);

            var data = dom.data('response');

            if (typeof data != 'undefined' && data.length) {

                var html = [];


                html.push('<ul class="rosie-list">');

                for (var ctr = 0; ctr < 10; ctr++) {

                    var ranking = ctr + 1;

                    html.push('<li>');

                        if (typeof data[ctr] != 'undefined') {
                            
                            html.push('<span class="rosie-list-' + ctr + '">');
                                html.push('<span class="rosie-list-rank">' + ranking + '</span>');
                                html.push(data[ctr].employee);
                            html.push('</span>');

                        } else {

                            html.push('<span class="rosie-list-vacant"> &nbsp </span>');
                        }
                    
                    html.push('</li>');
                }

                html.push('</ul>');


                dom.html(html.join('\n'));
            }
        });
    };


    // Converts any dom to a dropdown selection
    // based from given data.
    $.fn.toDropdown = function (data, id, value, default_msg, default_selected) {

        return $(this).each(function (i, o) {

            var dom = $(o);

            var dom_id = dom.attr('id');

            var is_default_option_valid = false;


            if (is_defined(id) && is_defined(value) && is_defined(data) && typeof data === 'object') {

                var select_dom = [];

                select_dom.push('<select id="' + dom_id + '" class="form-control form-control-xs" autocomplete="off">');


                // Setup default message
                if (is_defined(default_msg)) {

                    select_dom.push('<option value="null">' + default_msg + '</option>');

                } else {

                    select_dom.push('<option value="null">Choose one...</option>');
                }


                $.each(data, function (index, info) {

                    if (info.hasOwnProperty(id) && info.hasOwnProperty(value)) {

                        is_default_option_valid = (info[id] == default_selected) ? true : is_default_option_valid;

                        select_dom.push('<option value="' + info[id] + '">' + info[value] + '</option>');
                    }
                });

                select_dom.push('</select>');


                // Remove the dom and replace with 
                // the selection dropdown
                dom.replaceWith(select_dom.join(''));


                // Select a default value if any
                if (is_default_option_valid) {
                    $('#' + dom_id + ' option:contains("' + default_selected + '")').prop('selected', true);
                }
            }
        });
    };
}();


// Custom Helpers
+function () {

    String.prototype.capitalize = function () {

        var __s = [];

        this.split(' ').forEach(function (__v) {
            __s.push(__v.charAt(0).toUpperCase() + __v.slice(1));
        });

        return __s.join(' ');
    };


	window.notify = function (title, message, callback) {

        var popup = $('#notif_modal');

        if (popup.length > 0) {

        	title = (typeof title == 'undefined') ? 'Title' : title;
        	message = (typeof message == 'undefined') ? 'Body' : message;


            popup.find('.modal-title').html(title);

            popup.find('.modal-body').html(message);

            popup.modal();


            popup.on('hide.bs.modal', function () {

                if (typeof callback == 'function') {
                    callback();
                }
            });
        }
    };

    window.is_error = function (response, check_only) {

        check_only = is_defined(check_only) ? check_only : false;

    	if (
    		typeof response == 'object' 
    		&& response.hasOwnProperty('status') 
    		&& response.status === 'Error'
    	) {

            if (check_only) {

                return true;
            }

			if (response.message === 'Unauthorized') {

				notify('Error', 'Session Timeout. Please relogin', function () {
	                window.location.href = window.location.href;
	            });

	            throw Error(response.message);
			}
		}

        return false;
    };

    window.alert_success = function (message) {

        message = is_defined(message) ? message : '';

        var alert_dom = [
            '<div id="alert_message" class="alert alert-success" role="alert">',
                '<strong>Success!</strong> ' + message,
            '</div>'
        ].join('');

        $('#notif_modal .modal-body').prepend(alert_dom);

        return alert_prepend(alert_dom);
    };

    window.alert_error = function (message) {

        message = is_defined(message) ? message : 'An unexpected error has occured. Please try again.';

        var alert_dom = [
            '<div id="alert_message" class="alert alert-danger" role="alert">',
                '<strong>Ooops!</strong> ' + message,
            '</div>'
        ].join('');

        return alert_prepend(alert_dom);
        
    };

    window.alert_prepend = function (alert_html) {

        var alert_message = $('#alert_message');

        if (alert_message.length) {
            alert_message.remove();
        }

        return $('#notif_modal .modal-body').prepend(alert_html);
    };

    window.datepicker_date_now = function () {

        if (typeof __DATE_NOW != 'undefined') {

            var pdate = __DATE_NOW.split('-');

            return new Date(pdate[0], pdate[1] - 1, pdate[2]);
        }
    };

}(window.Dashboard = window.Dashboard || function () {}, jQuery);