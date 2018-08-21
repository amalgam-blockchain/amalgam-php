<?php

namespace Amalgam;

class ByteBuffer {

    const DEFAULT_CAPACITY = null;

    public $buffer;
    private $currentOffset = 0;
    private $littleEndian;

    public function __construct($capacity = self::DEFAULT_CAPACITY, $littleEndian = true)
    {
        if ($capacity === self::DEFAULT_CAPACITY) {
            $this->buffer = [];
        } else {
            $this->buffer = new \SplFixedArray($capacity);
        }
        $this->littleEndian = $littleEndian;
    }

    public function length()
    {
        return count($this->buffer);
    }

    public function read($offset, $length)
    {
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $this->buffer[$offset + $i];
        }
        return $result;
    }

    public function write($value, $offset = null)
    {
        if ($offset === null) {
            $offset = $this->currentOffset;
        }
        for ($i = 0; $i < strlen($value); $i++) {
            $this->buffer[$offset++] = $value[$i];
        }
        $this->currentOffset = $offset;
    }
    
    private function readInt($offset, $size)
    {
        $r = 0;
        if ($this->littleEndian) {
            for ($i = 0; $i < $size; $i++) {
                $r |= ord($this->buffer[$offset + $i]) << ($i * 8);
            }
        } else {
            for ($i = 0; $i < $size; $i++) {
                $r |= ord($this->buffer[$offset + $size - 1 - $i]) << ($i * 8);
            }
        }
        return $r;
    }

    private function writeInt($value, $size, $offset = null)
    {
        if ($offset === null) {
            $offset = $this->currentOffset;
        }
        if ($this->littleEndian) {
            for ($i = 0; $i < $size; $i++) {
                $bits = $i * 8;
                $this->buffer[$offset + $i] = chr(($value & (0xff << $bits)) >> $bits);
            }
        } else {
            for ($i = 0; $i < $size; $i++) {
                $bits = $i * 8;
                $this->buffer[$offset + $size - 1 - $i] = chr(($value & (0xff << $bits)) >> $bits);
            }
        }
        $this->currentOffset = $offset + $size;
    }

    public function readUint8($offset)
    {
        return $this->readInt($offset, 1);
    }

    public function readUint32($offset)
    {
        return $this->readInt($offset, 4);
    }

    public function writeUint8($value, $offset = null)
    {
        $this->writeInt($value, 1, $offset);
    }

    public function writeUint16($value, $offset = null)
    {
        $this->writeInt($value, 2, $offset);
    }

    public function writeUint32($value, $offset = null)
    {
        $this->writeInt($value, 4, $offset);
    }

    public function writeUint64($value, $offset = null)
    {
        $this->writeInt($value, 8, $offset);
    }

    public function writeInt16($value, $offset = null)
    {
        $this->writeUint16($value, $offset);
    }

    public function writeInt64($value, $offset = null)
    {
        $this->writeUint64($value, $offset);
    }
    
    public function writeVarint32($value, $offset = null)
    {
        if ($offset === null) {
            $offset = $this->currentOffset;
        }
        $value >>= 0;
        while ($value >= 0x80) {
            $this->buffer[$offset++] = chr(($value & 0x7f) | 0x80);
            $value >>= 7;
        }
        $this->buffer[$offset++] = chr($value);
        $this->currentOffset = $offset;
    }
    
    public function writeVString($value, $offset = null)
    {
        $this->writeVarint32(strlen($value), $offset);
        $this->write($value);
    }
}
