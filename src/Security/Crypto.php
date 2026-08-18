<?php

declare(strict_types=1);

namespace SiteForgeAI\Security;

/**
 * Class Crypto
 * we use the aes-256-cbc algorithm for encryption and decryption of sensitive data.
 * The encryption key is generated during plugin activation and stored securely in the WordPress options table.
 * @package SiteForgeAI\Security
 */

class Crypto
{
    private const CIPHER = 'aes-256-cbc';

    public static function encrypt(string $plain_text): string
    {
        // get the encryption key from the options table
        $key = (string) get_option('siteforge_ai_encryption_key', '');
        if (empty($key) || empty($plain_text)) {
            return '';
        }
        // generate a random IV 16 bytes long for AES-256-CBC
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER));

        // encrypt the data
        $encrypted = openssl_encrypt(
            $plain_text,
            self::CIPHER,
            hex2bin($key),
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {
            return '';
        }

        // Pack: First 16 bytes = IV, Remaining bytes = Encrypted data
        return base64_encode($iv . $encrypted);
    }


    public static function decrypt(string $encrypted_text): string
    {

        $key = (string) get_option('siteforge_ai_encryption_key', '');

        if (empty($key) || empty($encrypted_text)) {
            return '';
        }

        $raw_data = base64_decode($encrypted_text, true);
        $iv_length = openssl_cipher_iv_length(self::CIPHER);

        // If the string is too short to even contain the IV, it's invalid
        if ($raw_data === false || strlen($raw_data) <= $iv_length) {
            return '';
        }

        // Extract the IV (first 16 bytes) and the ciphertext (everything else)
        $iv = substr($raw_data, 0, $iv_length);
        $ciphertext = substr($raw_data, $iv_length);
        $decrypted = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            hex2bin($key),
            OPENSSL_RAW_DATA,
            $iv
        );
        return $decrypted === false ? '' : $decrypted;
    }
}
