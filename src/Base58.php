<?php

namespace Amalgam;

if (!extension_loaded('gmp')) {
    throw new \Exception('GMP extension not installed');
}

class Base58 {
    
    private $base;
    private $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    private $alphabetMap = [];
    private $leader;
    
    private static $instance;

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
  
    private function __construct()
    {
        $length = strlen($this->alphabet);
        $this->base = gmp_init($length, 10);
        for ($i = 0; $i < $length; $i++) {
            $this->alphabetMap[$this->alphabet[$i]] = $i;
        }
        $this->leader = $this->alphabet[0];
    }
    
    public function encode($data)
    {
        $res = '';
        $dataIntVal = gmp_import($data, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        $zero = gmp_init(0, 10);
        while (gmp_cmp($dataIntVal, $zero) > 0) {
            $qr = gmp_div_qr($dataIntVal, $this->base);
            $dataIntVal = $qr[0];
            $res = $this->alphabet[gmp_strval($qr[1])] . $res;
        }
        // deal with leading zeros
        $i = 0;
        $length = strlen($data);
        while ($i < $length && $data[$i] === chr(0)) {
            $res = $this->leader . $res;
            $i++;
        }
        return $res;
    }

    public function decode($encodedData)
    {
        $res = gmp_init(0, 10);
        $length = strlen($encodedData);
        for ($i = 0; $i < $length; $i++) {
            $res = gmp_add(gmp_mul($res, $this->base), gmp_init($this->alphabetMap[$encodedData[$i]], 10));
        }
        $res = gmp_export($res, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        // deal with leading zeros
        $i = 0;
        while ($i < $length && $encodedData[$i] === $this->leader) {
            $res = chr(0) . $res;
            $i++;
        }
        return $res;
    }
}
