<div style="width: 100%; height: 100%; background: #fff;">
    <table style="width: 600px; margin: 0 auto; font-family: 'Century Gothic', Verdana, Tahoma, Arial; font-size: 17px;">
        <tr>
            <td colspan="2">
                <div style="width: 200px; height: 100px; margin: 0 auto;">
                    <img style="max-width:100%; max-height:100%;" src="{{$logo}}" alt="Rosie Services" title="Rosie Services"/>
                </div>
            </td>
        </tr>

        <tr>
            <td style="padding: 30px 10px 10px 10px; border-top: 2px solid #e96656;" colspan="2">
                <p style="padding: 0; margin: 0; text-transform: capitalize;">Hello {{$name}},</p>
                <br />

                <br />
                <p style="padding: 0; margin: 0;">Thank you for choosing us!</p>
                <br />

                <br />
                <p style="padding: 0; margin: 0;">Please confirm your booking by clicking the link below. We will be waiting for your confirmation within 24 hours. If this is not yours, please disregard the email.</p>
                <br />

                <br />
                <h3 style="margin: 0;">Booking Summary</h3>
            </td>
        </tr>

        <tr>
            <td style="padding: 20px 0 20px 30px; border-top: 2px solid #e1e1e1; border-bottom: 2px solid #e1e1e1; text-align: center;">

                <img src="{{$avatar}}" alt="{{$cleaner}}" title="{{$cleaner}}" style="width: 100px; height: 100px; border-radius: 100%;" />

                <p style="text-transform: capitalize; font-size: 15px;">
                    {{$cleaner}}

                    <br />

                    <span style="font-size: 13px;">
                        <b>{{$rating}}/5</b> Rating
                    </span>
                </p>


            </td>
            <td style="padding: 20px 50px 20px 0; border-top: 2px solid #e1e1e1; border-bottom: 2px solid #e1e1e1; text-align: right;">

                <h4 style="text-transform: capitalize;"><b>{{$date}}</b></h4>
                
                <p>{{$time}}</p>
                
                <p>{{$hourly}}</p>
                
                <h3><b>Total: ${{$total}}</b></h3>

            </td>
        </tr>

        <tr>
            <td style="padding: 30px 10px 10px 10px;" colspan="2">
                <br />
                <div style="width: 100%; text-align: center;">
                    <a href="{{$link}}" target="_blank" style="padding: 10px; margin: 0 auto; border-radius: 5px; border: 3px solid #e96656; color: #e96656; text-decoration: none; text-transform: uppercase;">Confirm Booking</a>
                </div>
                <br />
                <br />

                <br />
                <p style="padding: 0; margin: 0;">Rosie Services Support</p>
            </td>
        </tr>
    </table>
</div>