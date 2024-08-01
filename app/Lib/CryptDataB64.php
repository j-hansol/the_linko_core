<?php

namespace App\Lib;

use App\Lib\CryptInterface;
use Illuminate\Support\Str;

class CryptDataB64 implements CryptInterface{
    const CIPHER = 'AES-256-CBC';
    const MD5LEN = 32;

    /**
     * 텍스트를 암호화한다.
     * @param string|null $text
     * @param bool $is_base64
     * @return string|null
     */
    public static function encrypt(?string $text, bool $is_base64 = true) : ?string {
        if($text) {
            $ivlen = openssl_cipher_iv_length(self::CIPHER );
            $iv = Str::random( $ivlen );
            $ciphertext_64 = base64_encode(openssl_encrypt( $text, self::CIPHER, self::ENCRYPT_KEY, OPENSSL_RAW_DATA, $iv ));
            $mac = md5( $iv . $ciphertext_64 );
            return $mac . $iv . $ciphertext_64;
        }
        return null;
    }

    /**
     * 암호화 텍스트를 복호화한다.
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
            $mac = substr( $text, 0, self::MD5LEN );
            $iv = substr( $text, self::MD5LEN, $ivlen );
            $ciphertext_64 = substr( $text, $ivlen + self::MD5LEN );
            $calc_mac = md5( $iv . $ciphertext_64 );
            if( $mac != $calc_mac ) return false;
            if((!$t = openssl_decrypt(base64_decode($ciphertext_64), self::CIPHER, self::ENCRYPT_KEY, OPENSSL_RAW_DATA, $iv))) return false;
            return $decrypted_text[$label] = $t;
        }
        else return $decrypted_text[$label] = null;
    }

    public static function encryptArgs($iv, $text) {}

    public static function decryptArgs($piv, $pmac, $text) {}
}
