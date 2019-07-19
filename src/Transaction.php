<?php

namespace Amalgam;

class Transaction {
    
    const CHAIN_ID = '46179a8f45db072e7848bf0a478446dd257dd5ad8e16069ad9852ea280f65591';
    
    private $tx;
    
    public function __construct($connection)
    {
        $properties = $connection->exec('database_api', 'get_dynamic_global_properties');
        $block = $connection->exec('database_api', 'get_block_header', [
            'block_num' => $properties['last_irreversible_block_num']
        ]);
        $buf = new ByteBuffer();
        $buf->write(hex2bin($block['previous']));
        $this->tx = [
            'ref_block_num' => ($properties['last_irreversible_block_num'] - 1) & 0xffff,
            'ref_block_prefix' => $buf->readUint32(4),
            'expiration' => (new \DateTime($properties['time']))->add(new \DateInterval('PT10M'))->format('Y-m-d\TH:i:s'),
            'operations' => [],
            'extensions' => [],
            'signatures' => []
        ];
    }
    
    public function getTx()
    {
        return $this->tx;
    }

    public function addOperation($name, $params)
    {
        $this->tx['operations'][] = [
            'type' => $name . Operations::SUFFIX,
            'value' => $params
        ];
    }
    
    public function sign($privateWIFs)
    {
        $buffer = new ByteBuffer();
        Types::typeTransaction()->serialize($buffer, $this->tx);
        $msg = hex2bin(self::CHAIN_ID) . $buffer->read(0, $buffer->length());
        foreach ($privateWIFs as $privateWif) {
            $this->tx['signatures'][] = Signature::sign($msg, $privateWif)->toString();
        }
    }
}
