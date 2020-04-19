<?php

namespace App\Utiles;

class Cifrar
{
    private const METODO_CIFRADO = 'aes128';
    private const KEYLENGTH = 16;
    private const CLAVE = '1234567890abcdef';
    
    static public function encode($dato)
    {
        $clave = substr(
            hash('sha256', self::CLAVE),
            0,
            self::KEYLENGTH
        );
        $size = openssl_cipher_iv_length(self::METODO_CIFRADO);
        $niv = substr($clave, 0, $size);
        return base64_encode(openssl_encrypt($dato, self::METODO_CIFRADO, $clave, 0, $niv));
    }

    static public function decode($dato)
    {
        $clave = substr(
            hash('sha256', self::CLAVE),
            0,
            self::KEYLENGTH
        );
        $size = openssl_cipher_iv_length(self::METODO_CIFRADO);
        $niv = substr($clave, 0, $size);
        return  openssl_decrypt(base64_decode($dato), self::METODO_CIFRADO, $clave, 0, $niv);
    }
}