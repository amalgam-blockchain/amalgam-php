<?php

namespace Amalgam;

if (!extension_loaded('gmp')) {
    throw new \Exception('GMP extension not installed');
}

class Signature {
    
    public $r;
    public $s;
    public $i;
    
    public function __construct($r, $s, $i)
    {
        if ($r == null || $s == null || $i == null) {
            throw new \Exception('Missing parameter');
        }
        $this->r = $r;
        $this->s = $s;
        $this->i = $i;
    }
    
    public static function fromString($value)
    {
        $value = hex2bin($value);
        if (strlen($value) != 65) {
            throw new \Exception('Invalid signature length');
        }
        $i = ord($value[0]);
        if (($i - 27) != (($i - 27) & 7)){
            throw new \Exception('Invalid signature parameter');
        }
        $r = gmp_import(substr($value, 1, 32), 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        $s = gmp_import(substr($value, 33), 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        return new Signature($r, $s, $i);
    }
    
    public function toString()
    {
        return bin2hex(chr($this->i) . Utils::bigIntToString($this->r, 32) . Utils::bigIntToString($this->s, 32));
    }
    
    public static function sign($msg, $privateWif)
    {
        $hash = hash('sha256', $msg, true);
        $e = gmp_import($hash, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        $privateKey = PrivateKey::fromWif($privateWif);
        $publicKey = $privateKey->toPublicKey();
        $i = null;
        $nonce = 1;
        $ecdsa = ECDSA::getInstance();
        while (true) {
            $ecsignature = $ecdsa->createSignature($hash, $privateKey->d, $nonce++);
            $der = $ecsignature->toDER();
            $lenR = ord($der[3]);
            $lenS = ord($der[5 + $lenR]);
            if ($lenR === 32 && $lenS === 32) {
                $i = $ecdsa->calcPubKeyRecoveryParam($e, $ecsignature, $publicKey->Q);
                $i += 4; // compressed
                $i += 27; // compact // 24 or 27 : (forcing odd-y 2nd key candidate)
                break;
            }
        }
        return new Signature($ecsignature->r, $ecsignature->s, $i);
    }
    
    public static function verify($msg, $signature, $publicKey)
    {
        try {
            $s = Signature::fromString($signature);
            $hash = hash('sha256', $msg, true);
            $e = gmp_import($hash, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
            $key = PublicKey::fromString($publicKey);
        } catch (\Exception $e) {
            return false;
        }
        return ECDSA::getInstance()->verify($e, new ECSignature($s->r, $s->s), $key->Q);
    }
}
