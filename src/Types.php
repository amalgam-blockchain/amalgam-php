<?php

namespace Amalgam;

class Types {
    
    const TYPE_UINT16 = 1;
    const TYPE_UINT32 = 2;
    const TYPE_UINT64 = 3;
    const TYPE_INT16 = 4;
    const TYPE_INT64 = 5;
    const TYPE_STRING = 6;
    const TYPE_STRING_BINARY = 7;
    const TYPE_BYTES = 8;
    const TYPE_BOOL = 9;
    const TYPE_ARRAY = 10;
    const TYPE_STATIC_VARIANT = 11;
    const TYPE_MAP = 12;
    const TYPE_SET = 13;
    const TYPE_PUBLIC_KEY = 14;
    const TYPE_TIME_POINT_SEC = 15;
    const TYPE_OPTIONAL = 16;
    const TYPE_ASSET = 17;
    const TYPE_FUTURE_EXTENSIONS = 18;
    const TYPE_STRUCT = 19;
    
    private $type;
    private $name;
    private $param;
    private $paramEx;
    
    public function __construct($type)
    {
        $this->type = $type;
    }
    
    public static function typeUint16()
    {
        return new Types(self::TYPE_UINT16);
    }
            
    public static function typeUint32()
    {
        return new Types(self::TYPE_UINT32);
    }
            
    public static function typeUint64()
    {
        return new Types(self::TYPE_UINT64);
    }
            
    public static function typeInt16()
    {
        return new Types(self::TYPE_INT16);
    }
            
    public static function typeInt64()
    {
        return new Types(self::TYPE_INT64);
    }
            
    public static function typeString()
    {
        return new Types(self::TYPE_STRING);
    }
            
    public static function typeStringBinary()
    {
        return new Types(self::TYPE_STRING_BINARY);
    }
            
    public static function typeBytes($size)
    {
        $object = new Types(self::TYPE_BYTES);
        $object->param = $size;
        return $object;
    }
            
    public static function typeBool()
    {
        return new Types(self::TYPE_BOOL);
    }
            
    public static function typeArray($dataType)
    {
        $object = new Types(self::TYPE_ARRAY);
        $object->param = $dataType;
        return $object;
    }
            
    public static function typeStaticVariant($array)
    {
        $object = new Types(self::TYPE_STATIC_VARIANT);
        $object->param = $array;
        return $object;
    }
            
    public static function typeMap($keyDataType, $valueDataType)
    {
        $object = new Types(self::TYPE_MAP);
        $object->param = $keyDataType;
        $object->paramEx = $valueDataType;
        return $object;
    }
            
    public static function typeSet($dataType)
    {
        $object = new Types(self::TYPE_SET);
        $object->param = $dataType;
        return $object;
    }
            
    public static function typePublicKey()
    {
        return new Types(self::TYPE_PUBLIC_KEY);
    }
            
    public static function typeTimePointSec()
    {
        return new Types(self::TYPE_TIME_POINT_SEC);
    }
            
    public static function typeOptional($dataType)
    {
        $object = new Types(self::TYPE_OPTIONAL);
        $object->param = $dataType;
        return $object;
    }
            
    public static function typeAsset()
    {
        return new Types(self::TYPE_ASSET);
    }
            
    public static function typeFutureExtensions()
    {
        return new Types(self::TYPE_FUTURE_EXTENSIONS);
    }
    
    public static function typeAuthority()
    {
        $object = new Types(self::TYPE_STRUCT);
        $object->param = [
            'weight_threshold' => Types::typeUint32(),
            'account_auths' => Types::typeMap(Types::typeString(), Types::typeUint16()),
            'key_auths' => Types::typeMap(Types::typePublicKey(), Types::typeUint16())
        ];
        return $object;
    }
    
    public static function typePrice()
    {
        $object = new Types(self::TYPE_STRUCT);
        $object->param = [
            'base' => Types::typeAsset(),
            'quote' => Types::typeAsset()
        ];
        return $object;
    }
    
    public static function typeOperation()
    {
        return Types::typeStaticVariant((new Operations())->operations);
    }
    
    public static function typeNamedOperation($id, $name, $params)
    {
        $object = new Types(self::TYPE_STRUCT);
        $object->name = $name;
        $object->param = $params;
        $object->paramEx = $id;
        return $object;
    }
    
    public static function typeTransaction()
    {
        $object = new Types(self::TYPE_STRUCT);
        $object->param = [
            'ref_block_num' => Types::typeUint16(),
            'ref_block_prefix' => Types::typeUint32(),
            'expiration' => Types::typeTimePointSec(),
            'operations' => Types::typeArray(Types::typeOperation()),
            'extensions' => Types::typeSet(Types::typeFutureExtensions())
        ];
        return $object;
    }
    
    public static function typeChainProperties()
    {
        $object = new Types(self::TYPE_STRUCT);
        $object->param = [
            'account_creation_fee' => Types::typeAsset(),
            'maximum_block_size' => Types::typeUint32(),
            'abd_interest_rate' => Types::typeUint16()
        ];
        return $object;
    }
    
    private function getOperation($value)
    {
        if (is_int($value)) {
            foreach ($this->param as $operation) {
                if ($operation->paramEx == $value) {
                    return $operation;
                }
            }
        } else {
            foreach ($this->param as $operation) {
                if (strcmp($operation->name, $value) == 0) {
                    return $operation;
                }
            }
        }
        throw new \Exception('Unsupported operation: ' . $value);
    }
    
    public function serialize($buffer, $value)
    {
        if ($this->type == self::TYPE_UINT16) {
            $buffer->writeUint16($value);
        } else if ($this->type == self::TYPE_UINT32) {
            $buffer->writeUint32($value);
        } else if ($this->type == self::TYPE_UINT64) {
            $buffer->writeUint64($value);
        } else if ($this->type == self::TYPE_INT16) {
            $buffer->writeInt16($value);
        } else if ($this->type == self::TYPE_INT64) {
            $buffer->writeInt64($value);
        } else if ($this->type == self::TYPE_STRING) {
            $buffer->writeVString($value);
        } else if ($this->type == self::TYPE_STRING_BINARY) {
            $length = strlen($value);
            $buffer->writeVarint32($length);
            $buffer->write($value);
        } else if ($this->type == self::TYPE_BYTES) {
            if ($this->param === null) {
                $length = strlen($value);
                $buffer->writeVarint32($length);
            }
            $buffer->write($value);
        } else if ($this->type == self::TYPE_BOOL) {
            $buffer->writeUint8($value ? 1 : 0);
        } else if ($this->type == self::TYPE_ARRAY) {
            $length = count($value);
            $buffer->writeVarint32($length);
            for ($i = 0; $i < $length; $i++) {
                $this->param->serialize($buffer, $value[$i]);
            }
        } else if ($this->type == self::TYPE_STATIC_VARIANT) {
            $operation = $this->getOperation($value[0]);
            $buffer->writeVarint32($operation->paramEx);
            $operation->serialize($buffer, $value[1]);
        } else if ($this->type == self::TYPE_MAP) {
            $length = count($value);
            $buffer->writeVarint32($length);
            for ($i = 0; $i < $length; $i++) {
                $object = $value[$i];
                $this->param->serialize($buffer, $object[0]);
                $this->paramEx->serialize($buffer, $object[1]);
            }
        } else if ($this->type == self::TYPE_SET) {
            if (empty($value)) {
                $value = [];
            }
            $length = count($value);
            $buffer->writeVarint32($length);
            for ($i = 0; $i < $length; $i++) {
                $this->param->serialize($buffer, $value[$i]);
            }
        } else if ($this->type == self::TYPE_PUBLIC_KEY) {
            $buffer->write(PublicKey::fromString($value)->getEncoded());
        } else if ($this->type == self::TYPE_TIME_POINT_SEC) {
            $buffer->writeUint32((new \DateTime($value, new \DateTimeZone('UTC')))->getTimestamp());
        } else if ($this->type == self::TYPE_OPTIONAL) {
            if (($value !== null) && isset($value)) {
                $buffer->writeUint8(1);
                $this->param->serialize($buffer, $value);
            } else {
                $buffer->writeUint8(0);
            }
        } else if ($this->type == self::TYPE_ASSET) {
            $asset = Asset::fromString($value);
            $buffer->writeInt64($asset->amount);
            $buffer->writeUint8(AssetSymbol::getPrecision($asset->symbol));
            $buffer->write($asset->symbol);
            for ($i = 0; $i < 7 - strlen($asset->symbol); $i++) {
                $buffer->writeUint8(0);
            }
        } else if ($this->type == self::TYPE_FUTURE_EXTENSIONS) {
            throw new \Exception('Unsupported type: future_extensions');
        } else if ($this->type == self::TYPE_STRUCT) {
            foreach ($this->param as $paramKey => $paramValue) {
                $paramValue->serialize($buffer, array_key_exists($paramKey, $value) ? $value[$paramKey] : null);
            }
        }
    }
}
