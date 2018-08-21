<?php

namespace Amalgam;

class PublicKey {
    
    const ADDRESS_PREFIX = 'AML';
    
    public $Q;

    // Public key has to be passed as a Point
    public function __construct($Q)
    {
        $this->Q = $Q;
    }
    
    public static function fromString($publicKey)
    {
        $prefix = substr($publicKey, 0, strlen(self::ADDRESS_PREFIX));
        if (strcmp(self::ADDRESS_PREFIX, $prefix) != 0) {
            throw new \Exception('Expecting key to begin with ' . self::ADDRESS_PREFIX . ', instead got ' . $prefix);
        }
        $publicKey = substr($publicKey, strlen(self::ADDRESS_PREFIX));
        $buffer = new ByteBuffer();
        $buffer->write(Base58::getInstance()->decode($publicKey));
        $public_key = $buffer->read(0, $buffer->length() - 4);
        $checksum = $buffer->read($buffer->length() - 4, 4);
        $new_checksum = hash('ripemd160', $public_key, true);
        $new_checksum = substr($new_checksum, 0, 4);
        if ($new_checksum !== $checksum) {
            throw new \Exception('Invalid public key (checksum did not match)');
        }
        return new PublicKey(Point::decodeFrom($public_key));
    }

    public static function isValid($publicKey)
    {
        try {
            self::fromString($publicKey);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function toString()
    {
        $pubBuf = $this->getEncoded();
        $checksum = hash('ripemd160', $pubBuf, true);
        $addy = $pubBuf . substr($checksum, 0, 4);
        return self::ADDRESS_PREFIX . Base58::getInstance()->encode($addy);
    }
    
    public function getEncoded($compressed = null)
    {
        if ($compressed == null) {
            $compressed = $this->Q->compressed;
        }
        return $this->Q->getEncoded($compressed);
    }
}
