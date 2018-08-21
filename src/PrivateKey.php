<?php

namespace Amalgam;

if (!extension_loaded('gmp')) {
    throw new \Exception('GMP extension not installed');
}

class PrivateKey {
    
    public $d;
    
    private $publicKey;

    // Private key has to be passed as a GMP big integer
    public function __construct($d)
    {
        $this->d = $d;
    }
    
    public static function fromWif($privateWif)
    {
        $buffer = new ByteBuffer();
        $buffer->write(Base58::getInstance()->decode($privateWif));
        $version = $buffer->readUint8(0);
        if ($version !== 128) {
            throw new \Exception('Expected version 128, instead got ' . $version);
        }
        $private_key = $buffer->read(0, $buffer->length() - 4);
        $checksum = $buffer->read($buffer->length() - 4, 4);
        $new_checksum = hash('sha256', $private_key, true);
        $new_checksum = hash('sha256', $new_checksum, true);
        $new_checksum = substr($new_checksum, 0, 4);
        if ($new_checksum !== $checksum) {
            throw new \Exception('Invalid WIF key (checksum did not match)');
        }
        $private_key = substr($private_key, 1);
        $length = strlen($private_key);
        if ($length !== 32) {
            throw new \Exception('Expecting 32 bytes for private_key, instead got ' . $length);
        }
        return new PrivateKey(gmp_import($private_key, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN));
    }
    
    public static function isWif($privateWif)
    {
        try {
            self::fromWif($privateWif);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function toWif()
    {
        $private_key = chr(0x80) . Utils::bigIntToString($this->d, 32);
        $checksum = hash('sha256', $private_key, true);
        $checksum = hash('sha256', $checksum, true);
        $checksum = substr($checksum, 0, 4);
        return Base58::getInstance()->encode($private_key . $checksum);
    }
    
    public function toString()
    {
        return $this->toWif();
    }
    
    public function toPublicKey()
    {
        if ($this->publicKey !== null) {
            return $this->publicKey;
        }
        $ecdsa = ECDSA::getInstance();
        $this->publicKey = new PublicKey($ecdsa->G->multiply($this->d));
        return $this->publicKey;
    }
}
