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
                <p style="padding: 0; margin: 0;">You recently requested to reset your password. Please click the link below to proceed. The password reset is only valid for the next 30 minutes.</p>
            </td>
        </tr>

        <tr>
            <td style="padding: 30px 10px 10px 10px;" colspan="2">
                <br />
                <div style="width: 100%; text-align: center;">
                    <a href="{{$link}}" target="_blank" style="padding: 10px; margin: 0 auto; border-radius: 5px; border: 3px solid #e96656; color: #e96656; text-decoration: none; text-transform: uppercase;">Reset Password</a>
                    
                </div>
                <br />

                <br />
                <p style="padding: 0; margin: 0;">If you did not request a password reset, please ignore this email.</p>
                <br />


                <br />
                <p style="padding: 0; margin: 0;">Rosie Services Support</p>
            </td>
        </tr>
    </table>
</div>