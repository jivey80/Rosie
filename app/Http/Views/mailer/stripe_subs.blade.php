<div style="width: 100%; height: 100%; background: #fff;">
    <table style="border-collapse: collapse; width: 600px; margin: 0 auto; font-family: 'Century Gothic', Verdana, Tahoma, Arial; font-size: 17px;">
        <tr>
            <td colspan="2">
                <div style="width: 200px; height: 100px; margin: 0 auto;">
                    <img style="max-width:100%; max-height:100%;" src="{{$logo}}" alt="Rosie Services" title="Rosie Services"/>
                </div>
            </td>
        </tr>

        <tr>
            <td style="padding: 30px 10px 10px 10px; border-top: 2px solid #e96656;" colspan="2">
                <p style="padding: 0; margin: 0; text-transform: capitalize;">Hello {{$username}},</p>
                <br />

                <br />
                <p style="padding: 0; margin: 0;">
                    To make your future booking at ease, we provided you the 
                    easiest and secured payment from <a href="https://stripe.com/" target="_blank">Stripe</a>.
                    The link is provided below.
                </p>
                <br />

                <br />
                <div style="width: 100%; text-align: center;">
                    <a href="{{$link}}" target="_blank" style="padding: 10px; margin: 0 auto; border-radius: 5px; border: 3px solid #e96656; color: #e96656; text-decoration: none; text-transform: uppercase;">Subscribe to Stripe</a>
                </div>
            </td>
        </tr>

        <tr>
            <td style="padding: 30px 10px 10px 10px;" colspan="2">

                <br />
                <p style="padding: 0; margin: 0;">Rosie Services Support</p>
            </td>
        </tr>
    </table>
</div>