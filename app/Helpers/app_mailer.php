<?php

if (! function_exists('json_to_htmail')) {

    /**
     * Converts an array to html table
     * for error mail notifications.
     * 
     * @param  array  $data
     * 
     * @return string HTML table
     * 
     */
    function json_to_htmail($data = array())
    {

        $dom = '';

        if ($data and is_array($data)) {

            $css = [
                'border-pad' => 'border: solid 1px #000; padding: 3px 6px;',
                'bold-align' => 'text-align: right; font-weight: bold;',
                'align' => 'text-align: left;'
            ];

            $dom .= '<table style="border-collapse: collapse;">';

            foreach ($data as $key => $value) {

                if (! is_array($value)) {

                    $value = is_object($value) ? json_encode($value) : $value;

                    $dom .= '<tr>';
                        $dom .= "<td style=\"{$css['border-pad']} {$css['bold-align']}\">{$key}</td>";
                        $dom .= "<td style=\"{$css['border-pad']} {$css['align']}\">{$value}</td>";
                    $dom .= '</tr>';

                } else {

                    $encode = json_encode($value);

                    $dom .= '<tr>';
                        $dom .= "<td style=\"{$css['border-pad']} {$css['bold-align']}\">{$key}</td>";
                        $dom .= "<td style=\"{$css['border-pad']} {$css['align']}\">{$encode}</td>";
                    $dom .= '</tr>';
                }
            }

            $dom .= '</table>';
        }

        return $dom;
    }
}


if (! function_exists('error_mailer')) {

    /**
     * Send error notification to developer
     * Email recipients can be configured on
     * mail.developers
     * 
     * @param  string $subject      Email Subject
     * @param  string $content      Data to send
     * @param  array $recipients    List of email addresses as recipients
     *
     */
    function error_mailer($subject = 'Head', $content = 'Body', $recipients = array())
    {
        if (is_string($subject) and is_string($content)) {

            $app = defined('PROJECT_NAME') ? PROJECT_NAME : 'SHIT';
            $env = defined('ENVIRONMENT') ? ENVIRONMENT : 'PROD';


            if (! $recipients and defined('DEV_EMAIL')) {

                $recipients = array(DEV_EMAIL);

            } else if (is_string($recipients)) {

                $recipients = (array) $recipients;

            } 


            if (is_array($recipients)) {

                $email_subject = strtoupper("[{$app}@{$env}] {$subject}");
                
                array_map(function ($recipient) use ($email_subject, $content) {

                    emailer($recipient, $email_subject, $content);
                
                }, $recipients);
            }


            if (! $recipients) {

                trigger_error('Recipient was not defined.');
            }
        }
    }
}

if (! function_exists('emailer')) {

    function emailer($recipient, $subject, $content, $bcc = '')
    {
        if (defined('ENVIRONMENT') and ENVIRONMENT === 'production') {

            $headers =  'MIME-Version: 1.0' . PHP_EOL .
                        'Content-type: text/html; charset=iso-8859-1' . PHP_EOL .
                        'From: info@rosieservices.com' . PHP_EOL .
                        'Reply-To: info@rosieservices.com' . PHP_EOL;


            // Check if BCC was provided
            $bcc_emails = filter_email($bcc);
            if ($bcc_emails) {
                $headers .= "Bcc: {$bcc_emails}";
            }


            if (is_string($content)) {
                mail($recipient, $subject, $content, $headers);
            }
        }
    }
}

if (! function_exists('filter_email')) {

    function filter_email($emails)
    {
        $recipients = array();

        // Passed argument is expected to be
        // one or more emails, comma separated
        // if many.
        if ($emails and is_string($emails)) {

            $emails_clean = str_replace(' ', '', $emails);
            $email_list = explode(',', $emails_clean);

            foreach ($email_list as $email) {

                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

                    $recipients[] = $email;
                }
            }
        }

        return implode(',', $recipients);
    }
}
