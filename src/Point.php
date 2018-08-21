<?php

namespace Amalgam;

if (!extension_loaded('gmp')) {
    throw new \Exception('GMP extension not installed');
}

class Point {
    
    public $x;
    public $y;
    public $z;
    private $_zInv;
    public $compressed;
    private $three;
    
    public function __construct($x, $y, $z)
    {
        if (!isset($z)) {
            throw new \Exception('Missing Z coordinate');
        }
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->_zInv = null;
        $this->compressed = true;
        $this->three = gmp_init(3, 10);
    }
    
    public function getZInv()
    {
        if ($this->_zInv === null) {
            $this->_zInv = gmp_invert($this->z, ECDSA::getInstance()->p);
        }
        return $this->_zInv;
    }
    
    public function getAffineX()
    {
        return gmp_mod(gmp_mul($this->x, $this->getZInv()), ECDSA::getInstance()->p);
    }
    
    public function getAffineY()
    {
        return gmp_mod(gmp_mul($this->y, $this->getZInv()), ECDSA::getInstance()->p);
    }
    
    public static function fromAffine($x, $y)
    {
        return new Point($x, $y, gmp_init(1, 10));
    }
    
    public function equals($other)
    {
        if ($other === $this) {
            return true;
        }
        $ecdsa = ECDSA::getInstance();
        if ($ecdsa->isInfinity($this)) {
            return $ecdsa->isInfinity($other);
        }
        if ($ecdsa->isInfinity($other)) {
            return $ecdsa->isInfinity($this);
        }
        // u = Y2 * Z1 - Y1 * Z2
        $u = gmp_mod(gmp_sub(gmp_mul($other->y, $this->z), gmp_mul($this->y, $other->z)), $ecdsa->p);
        if (gmp_sign($u) !== 0) {
            return false;
        }
        // v = X2 * Z1 - X1 * Z2
        $v = gmp_mod(gmp_sub(gmp_mul($other->x, $this->z), gmp_mul($this->x, $other->z)), $ecdsa->p);
        return gmp_sign($v) === 0;
    }

    public function negate()
    {
        $y = gmp_sub(ECDSA::getInstance()->p, $this->y);
        return new Point($this->x, $y, $this->z);
    }
    
    public function add($b)
    {
        $ecdsa = ECDSA::getInstance();
        if ($ecdsa->isInfinity($this)) {
            return $b;
        }
        if ($ecdsa->isInfinity($b)) {
            return $this;
        }
        $x1 = $this->x;
        $y1 = $this->y;
        $x2 = $b->x;
        $y2 = $b->y;
        // u = Y2 * Z1 - Y1 * Z2
        $u = gmp_mod(gmp_sub(gmp_mul($y2, $this->z), gmp_mul($y1, $b->z)), $ecdsa->p);
        // v = X2 * Z1 - X1 * Z2
        $v = gmp_mod(gmp_sub(gmp_mul($x2, $this->z), gmp_mul($x1, $b->z)), $ecdsa->p);
        if (gmp_sign($v) === 0) {
            if (gmp_sign($u) === 0) {
                return $this->twice(); // this == b, so double
            }
            return $ecdsa->infinity; // this = -b, so infinity
        }
        $v2 = gmp_pow($v, 2);
        $v3 = gmp_mul($v2, $v);
        $x1v2 = gmp_mul($x1, $v2);
        $zu2 = gmp_mul(gmp_pow($u, 2), $this->z);
        // x3 = v * (z2 * (z1 * u^2 - 2 * x1 * v^2) - v^3)
        $x3 = gmp_mod(gmp_mul(gmp_sub(gmp_mul(gmp_sub($zu2, gmp_mul($x1v2, gmp_init(2, 10))), $b->z), $v3), $v), $ecdsa->p);
        // y3 = z2 * (3 * x1 * u * v^2 - y1 * v^3 - z1 * u^3) + u * v^3
        $y3 = gmp_mod(gmp_add(gmp_mul(gmp_sub(gmp_sub(gmp_mul(gmp_mul($x1v2, $this->three), $u), gmp_mul($y1, $v3)), gmp_mul($zu2, $u)), $b->z), gmp_mul($u, $v3)), $ecdsa->p);
        // z3 = v^3 * z1 * z2
        $z3 = gmp_mod(gmp_mul(gmp_mul($v3, $this->z), $b->z), $ecdsa->p);
        return new Point($x3, $y3, $z3);
    }

    public function twice()
    {
        $ecdsa = ECDSA::getInstance();
        if ($ecdsa->isInfinity($this)) {
            return $this;
        }
        if (gmp_sign($this->y) === 0) {
            return $ecdsa->infinity;
        }
        $x1 = $this->x;
        $y1 = $this->y;
        $y1z1 = gmp_mod(gmp_mul($y1, $this->z), $ecdsa->p);
        $y1sqz1 = gmp_mod(gmp_mul($y1z1, $y1), $ecdsa->p);
        $a = $ecdsa->a;
        // w = 3 * x1^2 + a * z1^2
        $w = gmp_mul(gmp_pow($x1, 2), $this->three);
        if (gmp_sign($a) !== 0) {
          $w = gmp_add($w, gmp_mul(gmp_pow($this->z, 2), $a));
        }
        $w = gmp_mod($w, $ecdsa->p);
        // x3 = 2 * y1 * z1 * (w^2 - 8 * x1 * y1^2 * z1)
        $x3 = gmp_mod(gmp_mul(gmp_mul(gmp_sub(gmp_pow($w, 2), gmp_mul(gmp_mul($x1, gmp_init(8, 10)), $y1sqz1)), gmp_init(2, 10)), $y1z1), $ecdsa->p);
        // y3 = 4 * y1^2 * z1 * (3 * w * x1 - 2 * y1^2 * z1) - w^3
        $y3 = gmp_mod(gmp_sub(gmp_mul(gmp_mul(gmp_sub(gmp_mul(gmp_mul($w, $this->three), $x1), gmp_mul($y1sqz1, gmp_init(2, 10))), gmp_init(4, 10)), $y1sqz1), gmp_pow($w, 3)), $ecdsa->p);
        // z3 = 8 * (y1 * z1)^3
        $z3 = gmp_mod(gmp_mul(gmp_pow($y1z1, 3), gmp_init(8, 10)), $ecdsa->p);
        return new Point($x3, $y3, $z3);
    }
    
    private static function testBit($value, $n)
    {
        $n = strlen($value) - 1 - $n;
        if ($n < 0 || $n >= strlen($value)) {
            return false;
        }
        return $value[$n] == '1';
    }
    
    // Simple NAF (Non-Adjacent Form) multiplication algorithm
    public function multiply($k)
    {
        $ecdsa = ECDSA::getInstance();
        if ($ecdsa->isInfinity($this)) {
            return $this;
        }
        if (gmp_sign($k) === 0) {
            return $ecdsa->infinity;
        }
        $e = gmp_strval($k, 2);
        $h = gmp_strval(gmp_mul($k, $this->three), 2);
        $neg = $this->negate();
        $R = $this;
        for ($i = strlen($h) - 2; $i > 0; --$i) {
            $hBit = self::testBit($h, $i);
            $eBit = self::testBit($e, $i);
            $R = $R->twice();
            if ($hBit !== $eBit) {
                $R = $R->add($hBit ? $this : $neg);
            }
        }
        return $R;
    }

    // Compute this*j + x*k (simultaneous multiplication)
    public function multiplyTwo($j, $x, $k)
    {
        $j = gmp_strval($j, 2);
        $k = gmp_strval($k, 2);
        $i = max(strlen($j), strlen($k)) - 1;
        $R = ECDSA::getInstance()->infinity;
        $both = $this->add($x);
        while ($i >= 0) {
            $jBit = self::testBit($j, $i);
            $kBit = self::testBit($k, $i);
            $R = $R->twice();
            if ($jBit) {
                if ($kBit) {
                    $R = $R->add($both);
                } else {
                    $R = $R->add($this);
                }
            } else if ($kBit) {
                $R = $R->add($x);
            }
            --$i;
        }
        return $R;
    }

    public function getEncoded($compressed = null)
    {
        if ($compressed == null) {
            $compressed = $this->compressed;
        }
        $ecdsa = ECDSA::getInstance();
        if ($ecdsa->isInfinity($this)) {
            return chr(0);
        }
        $x = $this->getAffineX();
        $y = $this->getAffineY();
        $byteLength = $ecdsa->pLength;
        $x = Utils::bigIntToString($x, $byteLength);
        if ($compressed) {
            if (gmp_strval(gmp_mod($y, gmp_init(2, 10))) === '0') {
                return chr(2) . $x; // if $y is even
            } else {
                return chr(3) . $x; // if $y is odd
            }
        } else {
            return chr(4) . $x . Utils::bigIntToString($this->y, $byteLength);
        }
    }

    public static function decodeFrom($binaryString)
    {
        $type = ord(substr($binaryString, 0, 1));
        $compressed = ($type !== 4);
        $byteLength = ECDSA::getInstance()->pLength;
        $x = gmp_import(substr($binaryString, 1, $byteLength), 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        if ($compressed) {
            if (strlen($binaryString) != $byteLength + 1) {
                throw new \Exception('Invalid sequence length');
            }
            if (($type != 2) && ($type != 3)) {
                throw new \Exception('Invalid sequence tag');
            }
            $isOdd = ($type === 3);
            $Q = ECDSA::getInstance()->pointFromX($isOdd, $x);
        } else {
            if (strlen($binaryString) != $byteLength + $byteLength + 1) {
                throw new \Exception('Invalid sequence length');
            }
            $y = gmp_import(substr($binaryString, $byteLength + 1), 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
            $Q = self::fromAffine($x, $y);
        }
        $Q->compressed = $compressed;
        return $Q;
    }
}
