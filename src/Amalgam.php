<?php

namespace Amalgam;

use Yii;
use yii\base\Component;

class Amalgam extends Component {
    
    public $node;
    
    public function validateAccountName($value)
    {
        $suffix = Yii::t('app', 'Account name should ');
        if (empty($value)) {
            return $suffix . Yii::t('app', 'not be empty');
        }
        $length = strlen($value);
        if ($length < 3) {
            return $suffix . Yii::t('app', 'be longer');
        }
        if ($length > 16) {
            return $suffix . Yii::t('app', 'be shorter');
        }
        if (preg_match('/\./', $value)) {
            $suffix = Yii::t('app', 'Each account segment should ');
        }
        $ref = explode('.', $value);
        foreach ($ref as $label) {
            if (!preg_match('/^[a-z]/', $label)) {
                return $suffix . Yii::t('app', 'start with a lowercase letter');
            }
            if (!preg_match('/^[a-z0-9-]*$/', $label)) {
                return $suffix . Yii::t('app', 'have only lowercase letters, digits or dashes');
            }
            if (preg_match('/--/', $label)) {
                return $suffix . Yii::t('app', 'have only one dash in a row');
            }
            if (!preg_match('/[a-z0-9]$/', $label)) {
                return $suffix . Yii::t('app', 'end with a lowercase letter or digit');
            }
            if (!(strlen($label) >= 3)) {
                return $suffix . Yii::t('app', 'be longer');
            }
        }
        return null;
    }
    
    public function executeJson($json)
    {
        $connection = new Connection();
        $connection->nodeUrl = $this->node;
        return $connection->execJson($json);
    }
    
    public function executeJsonExternal($json)
    {
        $connection = new Connection();
        $connection->nodeUrl = $this->node;
        $array = json_decode($json, true);
        if (is_array($array) && array_key_exists('params', $array)) {
            $params = $array['params'];
            if (is_array($params) && (count($params) > 0)) {
                return $connection->execute($params[0], $params[1], $params[2], array_key_exists('id', $array) ? $array['id'] : null);
            }
        }
        return null;
    }
    
    public function execute($apiName, $command, $params = [])
    {
        $connection = new Connection();
        $connection->nodeUrl = $this->node;
        return $connection->exec($apiName, $command, $params);
    }
    
    public function broadcast($wif, $command, $params)
    {
        $connection = new Connection();
        $connection->nodeUrl = $this->node;
        $transaction = new Transaction($connection);
        $transaction->addOperation($command, $params);
        $transaction->sign([$wif]);
        return $connection->exec('network_broadcast_api', 'broadcast_transaction_synchronous', [$transaction->getTx()]);
    }
    
    public function getChainProperties()
    {
        return $this->execute('database_api', 'get_chain_properties');
    }
    
    public function getDynamicGlobalProperties()
    {
        return $this->execute('database_api', 'get_dynamic_global_properties');
    }
    
    public function getAccount($name)
    {
        $result = $this->execute('database_api', 'get_accounts', [[$name]]);
        return ($result != null) && is_array($result) && !empty($result) ? $result[0] : null;
    }
    
    public function getAccountHistory($name, $from, $limit)
    {
        return $this->execute('database_api', 'get_account_history', [$name, $from, $limit]);
    }
    
    public function getContent($author, $permlink)
    {
        return $this->execute('database_api', 'get_content', [$author, $permlink]);
    }
    
    public function getCurrentMedianHistoryPrice()
    {
        return $this->execute('database_api', 'get_current_median_history_price');
    }
    
    public function accountCreate($wif, $fee, $creator, $newAccountName,
            $owner, $active, $posting, $memoKey, $jsonMetadata)
    {
        return $this->broadcast($wif, 'account_create', [
            'fee' => $fee,
            'creator' => $creator,
            'new_account_name' => $newAccountName,
            'owner' => $owner,
            'active' => $active,
            'posting' => $posting,
            'memo_key' => $memoKey,
            'json_metadata' => $jsonMetadata,
        ]);
    }
    
    public function createAccount($registrarWif, $registrarName, $name, $publicKeys)
    {
        $owner = [
            'weight_threshold' => 1,
            'account_auths' => [],
            'key_auths' => [[$publicKeys['owner'], 1]],
        ];
        $active = [
            'weight_threshold' => 1,
            'account_auths' => [],
            'key_auths' => [[$publicKeys['active'], 1]],
        ];
        $posting = [
            'weight_threshold' => 1,
            'account_auths' => [],
            'key_auths' => [[$publicKeys['posting'], 1]],
        ];
        $properties = $this->getChainProperties();
        $fee = Asset::fromString($properties['account_creation_fee']);
        $fee->amount = $fee->amount * 30;
        return $this->accountCreate($registrarWif, $fee->toString(), $registrarName,
                $name, $owner, $active, $posting, $publicKeys['memo'], '');
    }
    
    public function accountCreateWithDelegation($wif, $fee, $delegation, $creator, $newAccountName,
            $owner, $active, $posting, $memoKey, $jsonMetadata, $extensions)
    {
        return $this->broadcast($wif, 'account_create_with_delegation', [
            'fee' => $fee,
            'delegation' => $delegation,
            'creator' => $creator,
            'new_account_name' => $newAccountName,
            'owner' => $owner,
            'active' => $active,
            'posting' => $posting,
            'memo_key' => $memoKey,
            'json_metadata' => $jsonMetadata,
            'extensions' => $extensions,
        ]);
    }
    
    public function createAccountWithDelegation($registrarWif, $registrarName, $registrarDelegation, $name, $publicKeys)
    {
        $owner = [
            'weight_threshold' => 1,
            'account_auths' => [],
            'key_auths' => [[$publicKeys['owner'], 1]],
        ];
        $active = [
            'weight_threshold' => 1,
            'account_auths' => [],
            'key_auths' => [[$publicKeys['active'], 1]],
        ];
        $posting = [
            'weight_threshold' => 1,
            'account_auths' => [],
            'key_auths' => [[$publicKeys['posting'], 1]],
        ];
        $properties = $this->getChainProperties();
        if (Utils::endsWith($registrarDelegation, AssetSymbol::AMLV)) {
            $delegation = Asset::fromString($registrarDelegation);
        } else if (Utils::endsWith($registrarDelegation, AssetSymbol::AML)) {
            $price = Utils::getVestingSharePrice($this->getDynamicGlobalProperties());
            $delegation = Asset::fromString($registrarDelegation)->multiply($price);
        } else {
            $delegation = new Asset(0, AssetSymbol::AMLV);
        }
        $fee = Asset::fromString($properties['account_creation_fee']);
        return $this->accountCreateWithDelegation($registrarWif, $fee->toString(),
                $delegation->toString(), $registrarName, $name, $owner, $active, $posting, $publicKeys['memo'], '', []);
    }
    
    public function accountUpdate($wif, $account,
            $owner, $active, $posting, $memoKey, $jsonMetadata)
    {
        return $this->broadcast($wif, 'account_update', [
            'account' => $account,
            'owner' => $owner,
            'active' => $active,
            'posting' => $posting,
            'memo_key' => $memoKey,
            'json_metadata' => $jsonMetadata,
        ]);
    }
    
    public function updateAccount($wif, $name, $publicKeys)
    {
        $owner = array_key_exists('owner', $publicKeys) ? [
            'weight_threshold' => 1,
            'account_auths' => [],
            'key_auths' => [[$publicKeys['owner'], 1]],
        ] : null;
        $active = array_key_exists('active', $publicKeys) ? [
            'weight_threshold' => 1,
            'account_auths' => [],
            'key_auths' => [[$publicKeys['active'], 1]],
        ] : null;
        $posting = array_key_exists('posting', $publicKeys) ? [
            'weight_threshold' => 1,
            'account_auths' => [],
            'key_auths' => [[$publicKeys['posting'], 1]],
        ] : null;
        $memo = array_key_exists('memo', $publicKeys) ? $publicKeys['memo'] : null;
        return $this->accountUpdate($wif, $name, $owner, $active, $posting, $memo, '');
    }
    
    public function witnessUpdate($wif, $owner, $url, $block_signing_key, $props, $fee)
    {
        return $this->broadcast($wif, 'witness_update', [
            'owner' => $owner,
            'url' => $url,
            'block_signing_key' => $block_signing_key,
            'props' => $props,
            'fee' => $fee,
        ]);
    }
    
    public function updateWitness($wif, $owner, $url, $block_signing_key)
    {
        return $this->witnessUpdate($wif, $owner, $url, $block_signing_key, $this->getChainProperties(),
                (new Asset(0, AssetSymbol::AML))->toString());
    }

    public function transfer($wif, $from, $to, $amount, $memo)
    {
        return $this->broadcast($wif, 'transfer', [
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
            'memo' => $memo,
        ]);
    }
    
    public function transferToVesting($wif, $from, $to, $amount)
    {
        return $this->broadcast($wif, 'transfer_to_vesting', [
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
        ]);
    }
    
    public function withdrawVesting($wif, $account, $vestingShares)
    {
        return $this->broadcast($wif, 'withdraw_vesting', [
            'account' => $account,
            'vesting_shares' => $vestingShares,
        ]);
    }
    
    public function feedPublish($wif, $publisher, $exchangeRateBase, $exchangeRateQuote)
    {
        return $this->broadcast($wif, 'feed_publish', [
            'publisher' => $publisher,
            'exchange_rate' => [
                'base' => $exchangeRateBase,
                'quote' => $exchangeRateQuote,
            ]
        ]);
    }
    
    public function claimRewardBalance($wif, $account, $rewardAmalgam, $rewardABD, $rewardVests)
    {
        return $this->broadcast($wif, 'claim_reward_balance', [
            'account' => $account,
            'reward_amalgam' => $rewardAmalgam,
            'reward_abd' => $rewardABD,
            'reward_vests' => $rewardVests,
        ]);
    }
}
