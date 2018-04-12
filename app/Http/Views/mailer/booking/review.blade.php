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
            <td style="padding: 30px 10px 10px 10px; border-top: 2px solid #e96656;">
                <p style="padding: 0; margin: 0; text-transform: capitalize;">Hello {{$name}},</p>
                <br />

                <br />
                <p style="padding: 0; margin: 0;">
                Thank you for choosing Rosie!
                </p>
                <br />

                <br />
                <p style="padding: 0; margin: 0;">How did cleaning go today with <b>{{$cleaner}}</b>? Please rate your experience below, we would love to hear some feedback from you.</p>
                <br />

                <br />
                <div style="text-align: center;">
                    <p style="padding: 0; margin: 0;">
                        <b>Simply click the stars to get started!</b>
                    </p>

                    <br />
                    {!!$rating_urls!!}
                </div>
            </td>
        </tr>

        <tr>
            <td style="padding: 30px 10px 10px 10px;">
                <br />
                <p style="padding: 0; margin: 0;">Rosie Services Support</p>
            </td>
        </tr>
    </table>
</div>