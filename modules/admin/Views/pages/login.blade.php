<!doctype html>
<html class="no-js" lang="{{ config('app.locale') }}">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

        <title>Rosie Admin</title>

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

        @if(in_array(ENVIRONMENT, ['local', 'production']))
        <link rel="stylesheet" href="{{ baseurl() }}/assets/vendors/materialicons.css" />
        <link rel="stylesheet" href="{{ baseurl() }}/assets/vendors/bootstrap/css/bootstrap.min.css" />
        <link rel="stylesheet" href="{{ baseurl() }}/assets/vendors/materialize/css/bootstrap-material-design.css" />
        <link rel="stylesheet" href="{{ baseurl() }}/assets/vendors/materialize/css/ripples.min.css" />
        <link rel="stylesheet" href="{{ MODULE_ASSETS_URL }}/css/login.css" />
        @else
        <link rel="stylesheet" href="{{ baseurl() }}/assets/build/vendors.min.css" />
        <link rel="stylesheet" href="{{ MODULE_ASSETS_URL }}/build/console_login.min.css" />
        @endif
    </head>
    <body>
        <!--[if lt IE 8]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->

        <div class="login-border">
            <div class="login-form">
                <div class="login-header">
                    <p>Rosie Booking Console</p>
                    <span>Administrator Login</span>
                </div>

                <div id="login_status" class="login-status">
                    <div class="alert alert-danger alert-login" role="alert">
                        <span></span>
                    </div>
                </div>

                <div class="login-body">
                    <form id="formLogin" class="form-horizontal form-spacing" method="POST" action="{{ baseurl(). 'admin/login' }}" autocomplete="off">
                        <div class="form-group label-floating">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="material-icons">face</i>
                                </span>
                            
                                <label class="control-label" for="inputUsername">USERNAME</label>
                                <input class="form-control" id="inputUsername" type="text" tabindex="1" autofocus="autofocus" autocomplete="off" />
                            </div>
                        </div>

                        <div class="form-group label-floating">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="material-icons">lock</i>
                                </span>
                            
                                <label class="control-label" for="inputPassword">PASSWORD</label>
                                <input class="form-control" id="inputPassword" type="password" tabindex="2" autocomplete="off" />
                            </div>
                        </div>
                        
                        <div class="form-footer login-footer">
                            <button type="button" class="btn btn-block btn-raised btn-info" id="btnlogin">Sign In</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        
        @if(in_array(ENVIRONMENT, ['local', 'production']))
        <script src="{{ baseurl() }}/assets/vendors/jquery/jquery-3.3.1.min.js"></script>
        <script src="{{ baseurl() }}/assets/vendors/bootstrap/js/bootstrap.min.js"></script>
        <script src="{{ baseurl() }}/assets/vendors/materialize/js/ripples.min.js"></script>
        <script src="{{ baseurl() }}/assets/vendors/materialize/js/material.min.js"></script>
        <script src="{{ baseurl() }}/assets/js/plugins.js"></script>
        <script src="{{ MODULE_ASSETS_URL }}/js/login.js"></script>
        @else        
        <script src="{{ baseurl() }}/assets/build/vendors.min.js"></script>
        <script src="{{ MODULE_ASSETS_URL }}/build/console_login.min.js"></script>
        @endif

        <script>
            var __BASE_URL = '{{baseurl() . MODULE}}';
            var __PRJ_INFO = [
                '{{PROJECT_NAME}}',
                '{{PROJECT_VERSION}}'
            ];
            console.log('%c'+__PRJ_INFO.join(' | '), 'padding: 0 14px; border-left: 7px solid #53082a; border-right: 7px solid #53082a; background: #d11569; color: #fff;');

            
            $.material.init();
        </script>
    </body>
</html>