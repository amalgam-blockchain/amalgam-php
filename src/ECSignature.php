<?php

namespace Amalgam;

class ECSignature {
    
    public $r;
    public $s;
    
    public function __construct($r, $s)
    {
        $this->r = $r;
        $this->s = $s;
    }
    
    public function toDER()
    {
        $r = Utils::bigIntToDER($this->r);
        $s = Utils::bigIntToDER($this->s);
        $sequence = chr(2) . chr(strlen($r)) . $r . chr(2) . chr(strlen($s)) . $s;
        return chr(0x30) . chr(strlen($sequence)) . $sequence;
    }
}
