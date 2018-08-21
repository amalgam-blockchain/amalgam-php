<?php

/*
 * Elliptic Curve Digital Signature Algorithm
 * with secp256k1 curve parameters
 */

namespace Amalgam;

if (!extension_loaded('gmp')) {
    throw new \Exception('GMP extension not installed');
}

class ECDSA
{
    public $a;
    public $b;
    public $p;
    public $n;
    public $G;
    public $infinity;
    public $pOverFour;
    public $pLength;

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
        $this->a = gmp_init('0', 10);
        $this->b = gmp_init('7', 10);
        $this->p = gmp_init('fffffffffffffffffffffffffffffffffffffffffffffffffffffffefffffc2f', 16);
        $this->n = gmp_init('fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364141', 16);
        $this->G = Point::fromAffine(
            gmp_init('79be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798', 16),
            gmp_init('483ada7726a3c4655da4fbfc0e1108a8fd17b448a68554199c47d08ffb10d4b8', 16)
        );
        $this->infinity = new Point(null, null, gmp_init(0, 10));
        // result caching
        $this->pOverFour = gmp_div_q(gmp_add($this->p, gmp_init(1, 10)), gmp_init(4, 10));
        // determine size of p in bytes
        $this->pLength = floor((strlen(gmp_strval($this->p, 2)) + 7) / 8);
    }
    
    public function pointFromX($isOdd, $x)
    {
        $alpha = gmp_mod(gmp_add(gmp_add(gmp_pow($x, 3), gmp_mul($this->a, $x)), $this->b), $this->p);
        $beta = gmp_powm($alpha, $this->pOverFour, $this->p); // XXX: not compatible with all curves
        $y = $beta;
        if ((gmp_strval(gmp_mod($beta, gmp_init(2, 10)), 10) === '0') ^ !$isOdd) {
            $y = gmp_sub($this->p, $y); // -y % p
        }
        return Point::fromAffine($x, $y);
    }
    
    public function isInfinity($Q)
    {
        if ($Q === $this->infinity) {
            return true;
        }
        return gmp_sign($Q->z) === 0 && gmp_sign($Q->y) !== 0;
    }

    public function isOnCurve($Q)
    {
        if ($this->isInfinity($Q)) {
            return true;
        }
        $x = $Q->getAffineX();
        $y = $Q->getAffineY();
        // Check that xQ and yQ are integers in the interval [0, p - 1]
        if (gmp_sign($x) < 0 || gmp_cmp($x, $this->p) >= 0) {
            return false;
        }
        if (gmp_sign($y) < 0 || gmp_cmp($y, $this->p) >= 0) {
            return false;
        }
        // and check that y^2 = x^3 + ax + b (mod p)
        $lhs = gmp_mod(gmp_pow($y, 2), $this->p);
        $rhs = gmp_mod(gmp_add(gmp_add(gmp_pow($x, 3), gmp_mul($this->a, $x)), $this->b), $this->p);
        return gmp_cmp($lhs, $rhs) === 0;
    }

    /**
     * Validate an elliptic curve point.
     *
     * See SEC 1, section 3.2.2.1: Elliptic Curve Public Key Validation Primitive
     */
    public function validate($Q)
    {
        // Check Q != O
        if ($this->isInfinity($Q)) {
            throw new \Exception('Point is at infinity');
        }
        if (!$this->isOnCurve($Q)) {
            throw new \Exception('Point is not on the curve');
        }
        // Check nQ = O (where Q is a scalar multiple of G)
        $nQ = $Q->multiply($this->n);
        if (!$this->isInfinity($nQ)) {
            throw new \Exception('Point is not a scalar multiple of G');
        }
        return true;
    }

    /**
     * Deterministic generation of k value.
     * 
     * https://tools.ietf.org/html/rfc6979#section-3.2
     */
    private function deterministicGenerateK($hash, $d, $nonce, &$r, &$s)
    {
        $e = gmp_import($hash, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        if ($nonce) {
            $hash = hash('sha256', $hash . chr($nonce), true);
        }
        // sanity check
        if (strlen($hash) != 32) {
            throw new \Exception('Hash must be 256 bit');
        }
        $x = Utils::bigIntToString($d, 32);
        // Step B
        $v = str_repeat(chr(1), 32);
        // Step C
        $k = str_repeat(chr(0), 32);
        // Step D
        $k = hash_hmac('sha256', $v . chr(0) . $x . $hash, $k, true);
        // Step E
        $v = hash_hmac('sha256', $v, $k, true);
        // Step F
        $k = hash_hmac('sha256', $v . chr(1) . $x . $hash, $k, true);
        // Step G
        $v = hash_hmac('sha256', $v, $k, true);
        // Step H1/H2a, ignored as tlen === qlen (256 bit)
        // Step H2b
        $v = hash_hmac('sha256', $v, $k, true);
        $T = gmp_import($v, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        // Step H3, repeat until T is within the interval [1, n - 1]
        while ((gmp_sign($T) <= 0) || (gmp_cmp($T, $this->n) >= 0) || !$this->checkSignature($T, $e, $d, $r, $s)) {
            $k = hash_hmac('sha256', $v . chr(0), $k, true);
            $v = hash_hmac('sha256', $v, $k, true);
            // Step H1/H2a, again, ignored as tlen === qlen (256 bit)
            // Step H2b again
            $v = hash_hmac('sha256', $v, $k, true);
            $T = gmp_import($v, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        }
        return $T;
    }
    
    private function checkSignature($k, $e, $d, &$r, &$s)
    {
        // find canonically valid signature
        $Q = $this->G->multiply($k);
        if ($this->isInfinity($Q)) {
            return false;
        }
        $r = gmp_mod($Q->getAffineX(), $this->n);
        if (gmp_sign($r) === 0) {
            return false;
        }
        $s = gmp_mod(gmp_mul(gmp_invert($k, $this->n), gmp_add($e, gmp_mul($d, $r))), $this->n);
        if (gmp_sign($s) === 0) {
            return false;
        }
        return true;
    }
    
    public function createSignature($hash, $d, $nonce)
    {
        $e = gmp_import($hash, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        $r = null;
        $s = null;
        $k = $this->deterministicGenerateK($hash, $d, $nonce, $r, $s);
        $nOverTwo = gmp_div_q($this->n, gmp_init(2, 10));
        // enforce low S values, see bip62: 'low s values in signatures'
        if (gmp_cmp($s, $nOverTwo) > 0) {
            $s = gmp_sub($this->n, $s);
        }
        return new ECSignature($r, $s);
    }
    
    public function verify($e, $signature, $Q)
    {
        $r = $signature->r;
        $s = $signature->s;
        // 1.4.1 Enforce r and s are both integers in the interval [1, n - 1]
        if (gmp_sign($r) <= 0 || gmp_cmp($r, $this->n) >= 0) {
            return false;
        }
        if (gmp_sign($s) <= 0 || gmp_cmp($s, $this->n) >= 0) {
            return false;
        }
        // c = s^-1 mod n
        $c = gmp_invert($s, $this->n);
        // 1.4.4 Compute u1 = es^-1 mod n
        //               u2 = rs^-1 mod n
        $u1 = gmp_mod(gmp_mul($e, $c), $this->n);
        $u2 = gmp_mod(gmp_mul($r, $c), $this->n);
        // 1.4.5 Compute R = (xR, yR) = u1G + u2Q
        $R = $this->G->multiplyTwo($u1, $Q, $u2);
        // 1.4.5 (cont.) Enforce R is not at infinity
        if ($this->isInfinity($R)) {
            return false;
        }
        // 1.4.6 Convert the field element R.x to an integer
        $xR = $R->getAffineX();
        // 1.4.7 Set v = xR mod n
        $v = gmp_mod($xR, $this->n);
        // 1.4.8 If v = r, output "valid", and if v != r, output "invalid"
        return gmp_cmp($v, $r) === 0;
    }
    
    /**
     * Recover a public key from a signature.
     *
     * See SEC 1: Elliptic Curve Cryptography, section 4.1.6, "Public Key Recovery Operation"
     *
     * http://www.secg.org/download/aid-780/sec1-v2.pdf
     */
    private function recoverPubKey($e, $signature, $i)
    {
        if (($i & 3) !== $i) {
            throw new \Exception('Recovery param is more than two bits');
        }
        $r = $signature->r;
        $s = $signature->s;
        if (!(gmp_sign($r) > 0 && gmp_cmp($r, $this->n) < 0)) {
            throw new \Exception('Invalid r value');
        }
        if (!(gmp_sign($s) > 0 && gmp_cmp($s, $this->n) < 0)) {
            throw new \Exception('Invalid s value');
        }
        // A set LSB signifies that the y-coordinate is odd
        $isYOdd = $i & 1;
        // The more significant bit specifies whether we should use the
        // first or second candidate key.
        $isSecondKey = $i >> 1;
        // 1.1 Let x = r + jn
        $x = $isSecondKey ? gmp_add($r, $this->n) : $r;
        $R = $this->pointFromX($isYOdd, $x);
        // 1.4 Check that nR is at infinity
        $nR = $R->multiply($this->n);
        if (!$this->isInfinity($nR)) {
            throw new \Exception('nR is not a valid curve point');
        }
        // Compute -e from e
        $eNeg = gmp_mod(gmp_neg($e), $this->n);
        // 1.6.1 Compute Q = r^-1 (sR -  eG)
        //               Q = r^-1 (sR + -eG)
        $rInv = gmp_invert($r, $this->n);
        $Q = $R->multiplyTwo($s, $this->G, $eNeg)->multiply($rInv);
        $this->validate($Q);
        return $Q;
    }
    
    /**
     * Calculate pubkey extraction parameter.
     *
     * When extracting a pubkey from a signature, we have to
     * distinguish four different cases. Rather than putting this
     * burden on the verifier, Bitcoin includes a 2-bit value with the
     * signature.
     *
     * This function simply tries all four cases and returns the value
     * that resulted in a successful pubkey recovery.
     */
    public function calcPubKeyRecoveryParam($e, $signature, $Q)
    {
        for ($i = 0; $i < 4; $i++) {
            $Qprime = $this->recoverPubKey($e, $signature, $i);
            // 1.6.2 Verify Q
            if ($Qprime->equals($Q)) {
                return $i;
            }
        }
        throw new \Exception('Unable to find valid recovery factor');
    }
}
