"use strict";

+function ($) {

	var Stripe = function (form) {

		var self = this;


		$.each(form, function (id, properties) {
			
			var dom = $('#' + id);

			/**
			 * 0 Type of expected input
			 * 1 Max character/input length
			 */
			var _properties = properties.split('|'),
				type = _properties[0],
				maxl = _properties[1];


			// Setting up other defined properties
			// ...
			// Append max length property if defined
			if (typeof maxl != 'undefined' && typeof dom.attr('maxlength')) {
				dom.prop('maxlength', maxl);
			}


			// Append custom events
			if (dom.length) {

				dom.on('change keyup paste', function (e) {
					e.stopPropagation();

					if (e.which !== 8 && e.which !== 46) {
						var value = dom.val();

						switch (type) {

							case 'card':
							default:
								return self.card(dom, value);

							case 'expiry':
								return self.expiry(dom, value, maxl);

							case 'cvc':
								return self.cvc(dom, value, maxl);
						};

						return false;
					}
				});
			}
		});
	};

	Stripe.prototype.card = function (dom, data) {

		console.log(data);
	};

	Stripe.prototype.expiry = function (dom, data, max_length) {

		// Auto-adjust length
		this.adjust_to_maxlength(dom, data, max_length);

		// Remove auto-added `/`
		var _data = data.replace('/', '');


		// Input must be an integer
		if (! this.is_numeric(_data)) {
			dom.val('');
		}


		// Auto-add `/` to separate month and year
		var intdata = parseInt(_data),
			lendata = _data.length;

		var date = '';

		switch (lendata) {
			case 1:
				if (intdata > 0) {
					date += '0' + intdata.toString();
					date += '/';
				}
				break;

			case 2:
				if (intdata > 0 && intdata < 10) {
				
					date += '0' + intdata.toString();
				
				} else if (intdata >= 10) {

					date += (intdata > 12) ? '12' : intdata.toString();
				}
				
				date += '/';
				break;
			
			case 3:
			case 4:
				var m = _data.substring(0, 2);
					date += m + '/';

				var y = _data.substring(2);
					date += y;

				this.is_expired(date);
				break;
		};

		// Modify the given dom with correct
		// date value.
		dom.val(date);

		return date;

	};

	Stripe.prototype.cvc = function (dom, data, max_length) {

		// Auto-adjust length
		this.adjust_to_maxlength(dom, data, max_length);


		// Input must be an integer
		if (! this.is_numeric(data)) {
			dom.val('');
		}
	};


	Stripe.prototype.adjust_to_maxlength = function (dom, data, length) {

		var _data = data.toString(),
			_dlen = strlen(_data);

		if (_dlen > length) {
			dom.val(_data.substring(0, length));
		}

		return _dlen;
	};

	Stripe.prototype.is_numeric = function (value) {

		return isNaN(parseInt(value)) ? false : true;
	};

	Stripe.prototype.is_expired = function (date) {

		var m = parseInt(date.substring(0, 2)),
			y = parseInt('20' + date.substring(3));

		var now = new Date(),
			now_m = now.getMonth(),
			now_y = now.getFullYear();


		if (y >= now_y) {

			if (y === now_y) {

				if (m > now_m) {

					console.log('Valid.');

				} else {

					console.log('Expired by month.');
				}
			}

		} else {

			console.log('Expired by year.');
		}
	};



	new Stripe({
		creditcard: 'card',
		expiration: 'expiry|5',
		cvc: 		'cvc|3'
	});

}(jQuery);
