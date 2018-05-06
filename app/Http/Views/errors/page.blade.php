<!DOCTYPE html>
<html>
    <head>
        <title>Be right back.</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                color: #333;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 72px;
                margin-bottom: 40px;
            }

            .back-link {
                padding: 10px 15px;
                border-radius: 3px;
                background: #e96656;
                color: #fff;
                font-size: 18px;
                text-decoration: none;
                text-transform: uppercase;
            }
            .back-link:hover,
            .back-link:active,
            .back-link:visited,
            .back-link:focus {
                text-decoration: none;
                color: #eee;
                background: #cb4332;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">Ooops! The page can’t be found.</div>
                <h1>It looks like nothing was found at this location.</h1>
                <br /><br />
                <a href="http://rosieservices.com" class="back-link">Go back to Rosie Services</a>
                
                @if(config('app.debug') === true)
                <div style="margin-top: 30px; padding: 5px 10px; background: #666; color: #fff; font-family: consolas;">
                    {{ $message }} <br /> {{ isset($file) ? "on {$file}" : '' }} {{ isset($line) ? "[Line: {$line }]" : '' }} 
                </div>
                @endif
            </div>
        </div>
    </body>
</html>
