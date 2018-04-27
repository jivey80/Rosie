<!doctype html>
<html class="no-js" lang="{{ config('app.locale') }}">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

        <title>Rosie -- Booking</title>

        <meta name="description" content="" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />


        <!-- For IE 9 and below. ICO should be 32x32 pixels in size -->
        <!--[if IE]><link rel="shortcut icon" href="{{ baseurl() }}/assets/images/icons/favicon.ico"><![endif]-->

        <!-- Touch Icons - iOS and Android 2.1+ 180x180 pixels in size. --> 
        <link rel="apple-touch-icon-precomposed" href="{{ baseurl() }}/assets/images/icons/favicon180.png">

        <!-- Firefox, Chrome, Safari, IE 11+ and Opera. 196x196 pixels in size. -->
        <link rel="icon" href="{{ baseurl() }}/assets/images/icons/favicon192.png">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ baseurl() }}/assets/images/icons/favicon32.png">


        <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet">

        @if(ENVIRONMENT === 'local')
        
        <link rel="stylesheet" href="{{ baseurl() }}/assets/vendors/materialicons.css" />
        <link rel="stylesheet" href="{{ baseurl() }}/assets/vendors/bootstrap/css/bootstrap.min.css" />
        <link rel="stylesheet" href="{{ baseurl() }}/assets/vendors/materialize/css/bootstrap-material-design.css" />
        <link rel="stylesheet" href="{{ baseurl() }}/assets/vendors/materialize/css/ripples.min.css" />
        <link rel="stylesheet" href="{{ MODULE_ASSETS_URL }}/css/app.css" />

        @else

        <link rel="stylesheet" href="{{ baseurl() }}/assets/build/vendors.min.css" />
        <link rel="stylesheet" href="{{ MODULE_ASSETS_URL }}/build/app.min.css" />

        @endif
    </head>
    <body>
        <!--[if lt IE 8]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->


        <div>
            <div class="booking-panel">
                <div class="booking-header">
                    <h2>Setup a Booking Schedule</h2>
                </div>

                <div class="booking-process" id="booking_process">
                    
                    <div class="booking-steps" id="booking_details" data-stepurl="{{ MODULE }}/details">
                        <i class="material-icons">assignment</i> <span>Details</span>
                    </div>
                    
                    <div class="booking-steps booking-disabled" id="booking_schedule" data-stepurl="{{ MODULE }}/schedule">
                        <i class="material-icons">schedule</i> <span>Choose Day</span>
                    </div>
                    
                    <div class="booking-steps booking-disabled" id="booking_confirmation" data-stepurl="{{ MODULE }}/confirmation">
                        <i class="material-icons">priority_high</i> <span>Confirm</span>
                    </div>

                    <div class="clearfix"></div>
                </div>
                

                <div class="booking-form">
                    <div id="error_notif" class="alert alert-dismissible alert-danger" style="display: none;">
                        
                        <strong>Ooops!</strong>

                        <span id="error_message"></span>
                    </div>

                    <div id="success_notif" class="alert alert-dismissible alert-success" style="display: none;">
                        
                        <strong>Success!</strong>

                        <span id="success_message"></span>
                    </div>

                    <div id="booking_form">
                        @yield('content')
                    </div>
                </div>
            </div>

            <div class="text-center">
                <a href="https://stripe.com/" target="_new">
                    <img src="{{ ASSET_IMG . '/powered_by_stripe.png' }}" class="stripe-img" width="120" />
                </a>
            </div>
        </div>

        <div id="orientation" class="orientation">
            <div class="orientation-info">
                <span class="material-icons">perm_device_information</span>
                <p>Please rotate the device to landscape for better experience.</p>
            </div>
        </div>


        <script>
            var __BASE_URL = '{{ MODULE_BASE_URL }}';
            var __PRJ_INFO = [
                '{{PROJECT_NAME}}',
                '{{PROJECT_VERSION}}'
            ];
            console.log('%c'+__PRJ_INFO.join(' | '), 'padding: 0 14px; border-left: 7px solid #53082a; border-right: 7px solid #53082a; background: #d11569; color: #fff;');
        </script>
        
        @if(ENVIRONMENT === 'local')
            <script src="{{ baseurl() }}/assets/vendors/jquery/jquery-3.3.1.min.js"></script>
            <script src="{{ baseurl() }}/assets/vendors/bootstrap/js/bootstrap.min.js"></script>
            <script src="{{ baseurl() }}/assets/vendors/materialize/js/ripples.min.js"></script>
            <script src="{{ baseurl() }}/assets/vendors/materialize/js/material.min.js"></script>
            <script src="{{ baseurl() }}/assets/js/plugins.js"></script>
            <script src="{{ MODULE_ASSETS_URL }}/js/app.js"></script>
        @else        
            <script src="{{ baseurl() }}/assets/build/vendors.min.js"></script>
            <script src="{{ MODULE_ASSETS_URL }}/build/app.min.js"></script>
        @endif


        @yield('script')


        <!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->
        <script>
            // (function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]=
            // function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;
            // e=o.createElement(i);r=o.getElementsByTagName(i)[0];
            // e.src='https://www.google-analytics.com/analytics.js';
            // r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));
            // ga('create','UA-XXXXX-X','auto');ga('send','pageview');
        </script>
    </body>
</html>