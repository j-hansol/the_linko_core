<?php

namespace App\Lib;

use Illuminate\Support\Str;

class CryptData implements CryptInterface {
    const CIPHER = 'AES-256-CBC';
    const MD5LEN = 32;

    /**
     * 텍스트를 암호화하여 리턴한다.
     * @param string|null $text
     * @param bool $is_base64
     * @return string|null
     */
    public static function encrypt(?string $text, bool $is_base64 = true) : ?string {
        if($text) {
            $ivlen = openssl_cipher_iv_length(self::CIPHER );
            $iv = Str::random( $ivlen );
            $ciphertext_raw = openssl_encrypt( $text, self::CIPHER, self::ENCRYPT_KEY, OPENSSL_RAW_DATA, $iv );
            $mac = md5( $iv . $ciphertext_raw );
            return $is_base64 ? base64_encode( $mac . $iv . $ciphertext_raw ) : $mac . $iv . $ciphertext_raw;
        }
        else return null;
    }

    /**
     * 암호화된 문자열을 복호화하여 리턴한다.
     * @param string|null $text
     * @param string|null $label
     * @param bool $is_base64
     * @return string|bool|null
     */
    public static function decrypt(?string $text, ?string $label = null, bool $is_base64 = true) : string|bool|null {
        static $decrypted_text = [];

        if($label && isset($decrypted_text[$label])) return $decrypted_text[$label];

        if($text) {
            $ivlen = openssl_cipher_iv_length(self::CIPHER );
            $btext = $is_base64 ? base64_decode( $text ) : $text;
            $mac = substr( $btext, 0, self::MD5LEN );
            $iv = substr( $btext, self::MD5LEN, $ivlen );
            $ciphertext_raw = substr( $btext, $ivlen + self::MD5LEN );
            $calc_mac = md5( $iv . $ciphertext_raw );
            if( $mac != $calc_mac ) return false;
            if((!$t = openssl_decrypt($ciphertext_raw, self::CIPHER, self::ENCRYPT_KEY, OPENSSL_RAW_DATA, $iv))) return false;
            return $decrypted_text[$label] = $t;
        }
        else return $decrypted_text[$label] = null;
    }

    /**
     * 전달된 파라미터로 데이터를 암호화한다. 암호화 결과를 배열로 리턴한다.
     * @param $iv
     * @param $text
     * @return array|false
     */
    public static function encryptArgs($iv, $text) : array|bool {
        try {
            $ciphertext_raw = openssl_encrypt( $text, self::CIPHER, self::ENCRYPT_KEY, OPENSSL_RAW_DATA, $iv );
            $mac = md5( $iv . $ciphertext_raw );
            return [
                'text' => base64_encode( $mac . $iv . $ciphertext_raw ),
                'iv' => $iv,
                'mac' => $mac,
                'h_iv' => bin2hex( $iv ),
                'h_raw' => bin2hex( $ciphertext_raw ),
                'key' => self::ENCRYPT_KEY
            ];
        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * 전달된 파리미터로 복호화한다.
     * @param $piv
     * @param $pmac
     * @param $text
     * @return array
     */
    public static function decryptArgs($piv, $pmac, $text) : array {
        $r = [
            'is_success' => false,
            'method' => self::CIPHER,
            'key' => CryptInterface::ENCRYPT_KEY,
            'piv' => $piv,
            'pmac' => $pmac,
            'iv' => '',
            'mac' => '',
            'cmac' => '',
            'text' => $text,
            'dec_text' => ''
        ];
        try {
            $ivlen = openssl_cipher_iv_length(self::CIPHER );
            $btext = base64_decode( $text );
            $r['base64_dec_hexa_text'] = bin2hex( $btext );
            $mac = substr( $btext, 0, self::MD5LEN );
            $iv = substr( $btext, self::MD5LEN, $ivlen );
            $r['iv'] = $iv;
            $r['mac'] = $mac;

            $ciphertext_raw = substr( $btext, $ivlen + self::MD5LEN );
            $calc_mac = md5( $iv . $ciphertext_raw );
            $r['cmac'] = $calc_mac;
            if( $mac != $calc_mac ) return $r;
            $r['is_success'] = true;
            $r['dec_text'] = openssl_decrypt($ciphertext_raw, self::CIPHER, self::ENCRYPT_KEY, OPENSSL_RAW_DATA, $iv);

            return $r;
        } catch ( \Exception $e ) {
            return $r;
        }
    }

    /**
     * 지정 경로의 파일을 암호화하여 지정 경로의 파일에 저장한다.
     * @param string $in_path
     * @param string $out_path
     * @return bool
     */
    public static function encryptFile(string $in_path, string $out_path) : bool {
        $content = file_get_contents($in_path);
        if(!$content) return false;
        $encrypt_content = static::encrypt($content, false);
        if(!($fd = fopen($out_path, 'w'))) return false;
        fwrite($fd, $encrypt_content);
        fclose($fd);
        return true;
    }

    /**
     * 지정 파일을 복호화하여 지정 경로의 파일에 저장한다.
     * @param string $in_path
     * @param string $out_path
     * @return bool
     */
    public static function decryptFile(string $in_path, string $out_path) : bool {
        $content = file_get_contents($in_path);
        if(!$content) return false;
        $decrypt_content = static::decrypt($content, null, false);
        if(!$decrypt_content) return false;
        if(!($fd = fopen($out_path, 'w'))) return false;
        fwrite($fd, $decrypt_content);
        fclose($fd);
        return true;
    }
}
