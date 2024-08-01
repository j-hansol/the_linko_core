<?php

namespace App\Lib;

interface CryptInterface {
    const ENCRYPT_KEY = 'UMjOv2ULHRkEhY7OP1rV5dQuk1Ore16f';

    public static function encrypt(?string $text, bool $is_base64 = true) : ?string;
    public static function decrypt(?string $text, ?string $label = null, bool $is_base64 = true) : string|bool|null;
    public static function encryptArgs( $iv, $text );
    public static function decryptArgs( $piv, $pmac, $text );
}
