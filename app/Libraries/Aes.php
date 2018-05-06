<?php

namespace App\Libraries;


class Aes
{
    private static $m = 'AES-256-CFB';
    private static $ts = 'Y-m-d H:i:s';

    private static function timestamp()
    {
        return strtotime(date(self::$ts));
    }

    public static function encrypt($data = '', $pass = '')
    {
        $data = (is_array($data) or is_object($data)) ? json_encode($data) : $data;
        $en = openssl_encrypt(self::bl($data), self::$m, self::password($pass), OPENSSL_NO_PADDING, self::iv());
        return base64_encode($en);
    }
    
    public static function decrypt($data = '', $pass = '')
    {
        $de = openssl_decrypt(base64_decode($data), self::$m, self::password($pass), OPENSSL_NO_PADDING, self::iv());
        return $de;
    }

    public static function payload($params, $pass = '') {

        $data = array(
            'ts' => self::timestamp()
        );

        if (is_array($params)) {

            $data = array_merge($data, $params);

        } else {

            $data['dt'] = $params;
        }


        $recode = self::encrypt(json_encode($data), $pass);

        return urlencode($recode);
    }

    public static function verify_payload($payload = '', $expiry = 0, $pass = '')
    {
        $de = self::decrypt($payload, $pass);
        
        $pl = json_decode($de);

        $dt = is_object($pl) ? $pl : $de;

        if (isset($dt->ts)) {

            $offset = self::timestamp() - $dt->ts;

            if ($expiry == 0 or $offset <= $expiry) {

                return $de;
            }

            return false;
        }

        return $de;
    }


    private static function password($pass = '')
    {
        return hash('SHA256', $pass);
    }
    
    private static function bl($raw = '')
    {
        $bl = 16;
        $pd = 0;
        
        $ln = strlen($raw);
        
        if ($ln > $bl) {
            $md = $ln % $bl;
            $dv = (int) ($ln / $bl);
            $ch = ($bl * $dv) + $bl;
            
            $rw = str_pad(bin2hex($raw), $ch, $pd);
        } else {
            $rw = str_pad(bin2hex($raw), $bl, $pd);
        }
        
        return hex2bin($rw);
    }
    
    private static function iv()
    {
        return hex2bin('000102030405060708090A0B0C0D0E0F');
    }
}
