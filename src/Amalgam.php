<?php

namespace Amalgam;

use Yii;
use yii\base\Component;

class Amalgam extends Component {
    
    public $node;
    
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
        if (is_array($array) && array_key_exists('method', $array) && array_key_exists('params', $array)) {
            return $connection->execute(null, $array['method'], $array['params'], array_key_exists('id', $array) ? $array['id'] : null);
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
        return $connection->exec('network_broadcast_api', 'broadcast_transaction_synchronous', [
            'trx' => $transaction->getTx(),
        ]);
    }
    
    // Database API

    public function getBlockHeader($blockNum)
    {
        return $this->execute('database_api', 'get_block_header', [
            'block_num' => $blockNum,
        ]);
    }
    
    public function getBlock($blockNum)
    {
        return $this->execute('database_api', 'get_block', [
            'block_num' => $blockNum,
        ]);
    }
    
    public function getOpsInBlock($blockNum, $onlyVirtual)
    {
        return $this->execute('database_api', 'get_ops_in_block', [
            'block_num' => $blockNum,
            'only_virtual' => $onlyVirtual,
        ]);
    }
    
    public function getTransaction($id)
    {
        return $this->execute('database_api', 'get_transaction', [
            'id' => $id,
        ]);
    }
    
    public function getConfig()
    {
        return $this->execute('database_api', 'get_config');
    }
    
    public function getVersion()
    {
        return $this->execute('database_api', 'get_version');
    }
    
    public function getDynamicGlobalProperties()
    {
        return $this->execute('database_api', 'get_dynamic_global_properties');
    }
    
    public function getChainProperties()
    {
        return $this->execute('database_api', 'get_chain_properties');
    }
    
    public function getWitnessSchedule()
    {
        return $this->execute('database_api', 'get_witness_schedule');
    }

    public function getReserveRatio()
    {
        return $this->execute('database_api', 'get_reserve_ratio');
    }

    public function getHardforkProperties()
    {
        return $this->execute('database_api', 'get_hardfork_properties');
    }

    public function getCurrentPriceFeed()
    {
        return $this->execute('database_api', 'get_current_price_feed');
    }
    
    public function getFeedHistory()
    {
        return $this->execute('database_api', 'get_feed_history');
    }

    public function listWitnesses($start, $limit, $order)
    {
        return $this->execute('database_api', 'list_witnesses', [
            'start' => $start,
            'limit' => $limit,
            'order' => $order,
        ]);
    }
    
    public function findWitnesses($owners)
    {
        return $this->execute('database_api', 'find_witnesses', [
            'owners' => $owners,
        ]);
    }
    
    public function listWitnessVotes($start, $limit, $order)
    {
        return $this->execute('database_api', 'list_witness_votes', [
            'start' => $start,
            'limit' => $limit,
            'order' => $order,
        ]);
    }
    
    public function getWitnessVotesByAccount($account)
    {
        return $this->execute('database_api', 'get_witness_votes_by_account', [
            'account' => $account,
        ]);
    }
    
    public function getWitnessVotesByWitness($account)
    {
        return $this->execute('database_api', 'get_witness_votes_by_witness', [
            'account' => $account,
        ]);
    }
    
    public function getWitnessesByVote($account, $limit)
    {
        return $this->execute('database_api', 'get_witnesses_by_vote', [
            'account' => $account,
            'limit' => $limit,
        ]);
    }
    
    public function getWitnessCount()
    {
        return $this->execute('database_api', 'get_witness_count');
    }
    
    public function getActiveWitnesses()
    {
        return $this->execute('database_api', 'get_active_witnesses');
    }
    
    public function listAccounts($start, $limit, $order)
    {
        return $this->execute('database_api', 'list_accounts', [
            'start' => $start,
            'limit' => $limit,
            'order' => $order,
        ]);
    }
    
    public function findAccounts($accounts)
    {
        return $this->execute('database_api', 'find_accounts', [
            'accounts' => $accounts,
        ]);
    }
    
    public function getAccountCount()
    {
        return $this->execute('database_api', 'get_account_count');
    }
    
    public function getAccountHistory($account, $start, $limit)
    {
        return $this->execute('database_api', 'get_account_history', [
            'account' => $account,
            'start' => $start,
            'limit' => $limit,
        ]);
    }
    
    public function getAccountBandwidth($account, $type)
    {
        return $this->execute('database_api', 'get_account_bandwidth', [
            'account' => $account,
            'type' => $type,
        ]);
    }
    
    public function listOwnerHistories($start, $limit)
    {
        return $this->execute('database_api', 'list_owner_histories', [
            'start' => $start,
            'limit' => $limit,
        ]);
    }
    
    public function findOwnerHistories($owner)
    {
        return $this->execute('database_api', 'find_owner_histories', [
            'owner' => $owner,
        ]);
    }

    public function listAccountRecoveryRequests($start, $limit, $order)
    {
        return $this->execute('database_api', 'list_account_recovery_requests', [
            'start' => $start,
            'limit' => $limit,
            'order' => $order,
        ]);
    }
    
    public function findAccountRecoveryRequests($accounts)
    {
        return $this->execute('database_api', 'find_account_recovery_requests', [
            'accounts' => $accounts,
        ]);
    }
    
    public function listChangeRecoveryAccountRequests($start, $limit, $order)
    {
        return $this->execute('database_api', 'list_change_recovery_account_requests', [
            'start' => $start,
            'limit' => $limit,
            'order' => $order,
        ]);
    }
    
    public function findChangeRecoveryAccountRequests($accounts)
    {
        return $this->execute('database_api', 'find_change_recovery_account_requests', [
            'accounts' => $accounts,
        ]);
    }
    
    public function listEscrows($start, $limit, $order)
    {
        return $this->execute('database_api', 'list_escrows', [
            'start' => $start,
            'limit' => $limit,
            'order' => $order,
        ]);
    }
    
    public function findEscrows($from)
    {
        return $this->execute('database_api', 'find_escrows', [
            'from' => $from,
        ]);
    }
    
    public function getEscrow($from, $escrowId)
    {
        return $this->execute('database_api', 'get_escrow', [
            'from' => $from,
            'escrow_id' => $escrowId,
        ]);
    }
    
    public function listWithdrawVestingRoutes($start, $limit, $order)
    {
        return $this->execute('database_api', 'list_withdraw_vesting_routes', [
            'start' => $start,
            'limit' => $limit,
            'order' => $order,
        ]);
    }
    
    public function findWithdrawVestingRoutes($account, $order)
    {
        return $this->execute('database_api', 'find_withdraw_vesting_routes', [
            'account' => $account,
            'order' => $order,
        ]);
    }
    
    public function listSavingsWithdrawals($start, $limit, $order)
    {
        return $this->execute('database_api', 'list_savings_withdrawals', [
            'start' => $start,
            'limit' => $limit,
            'order' => $order,
        ]);
    }
    
    public function findSavingsWithdrawalsFrom($account)
    {
        return $this->execute('database_api', 'find_savings_withdrawals_from', [
            'account' => $account,
        ]);
    }
    
    public function findSavingsWithdrawalsTo($account)
    {
        return $this->execute('database_api', 'find_savings_withdrawals_to', [
            'account' => $account,
        ]);
    }
    
    public function listVestingDelegations($start, $limit, $order)
    {
        return $this->execute('database_api', 'list_vesting_delegations', [
            'start' => $start,
            'limit' => $limit,
            'order' => $order,
        ]);
    }
    
    public function findVestingDelegations($account)
    {
        return $this->execute('database_api', 'find_vesting_delegations', [
            'account' => $account,
        ]);
    }
    
    public function listVestingDelegationExpirations($start, $limit, $order)
    {
        return $this->execute('database_api', 'list_vesting_delegation_expirations', [
            'start' => $start,
            'limit' => $limit,
            'order' => $order,
        ]);
    }
    
    public function findVestingDelegationExpirations($account)
    {
        return $this->execute('database_api', 'find_vesting_delegation_expirations', [
            'account' => $account,
        ]);
    }
    
    public function listAbdConversionRequests($start, $limit, $order)
    {
        return $this->execute('database_api', 'list_abd_conversion_requests', [
            'start' => $start,
            'limit' => $limit,
            'order' => $order,
        ]);
    }
    
    public function findAbdConversionRequests($account)
    {
        return $this->execute('database_api', 'find_abd_conversion_requests', [
            'account' => $account,
        ]);
    }
    
    public function listDeclineVotingRightsRequests($start, $limit, $order)
    {
        return $this->execute('database_api', 'list_decline_voting_rights_requests', [
            'start' => $start,
            'limit' => $limit,
            'order' => $order,
        ]);
    }
    
    public function findDeclineVotingRightsRequests($accounts)
    {
        return $this->execute('database_api', 'find_decline_voting_rights_requests', [
            'accounts' => $accounts,
        ]);
    }
    
    public function listLimitOrders($start, $limit, $order)
    {
        return $this->execute('database_api', 'list_limit_orders', [
            'start' => $start,
            'limit' => $limit,
            'order' => $order,
        ]);
    }
    
    public function findLimitOrders($account)
    {
        return $this->execute('database_api', 'find_limit_orders', [
            'account' => $account,
        ]);
    }
    
    public function getTransactionHex($trx)
    {
        return $this->execute('database_api', 'get_transaction_hex', [
            'trx' => $trx,
        ]);
    }
    
    public function getRequiredSignatures($trx, $availableKeys)
    {
        return $this->execute('database_api', 'get_required_signatures', [
            'trx' => $trx,
            'available_keys' => $availableKeys,
        ]);
    }
    
    public function getPotentialSignatures($trx)
    {
        return $this->execute('database_api', 'get_potential_signatures', [
            'trx' => $trx,
        ]);
    }
    
    public function verifyAuthority($trx)
    {
        return $this->execute('database_api', 'verify_authority', [
            'trx' => $trx,
        ]);
    }
    
    public function verifyAccountAuthority($account, $signers)
    {
        return $this->execute('database_api', 'verify_account_authority', [
            'account' => $account,
            'signers' => $signers,
        ]);
    }
    
    public function verifySignatures($hash, $signatures, $requiredOwner, $requiredActive, $requiredPosting, $requiredOther)
    {
        return $this->execute('database_api', 'verify_signatures', [
            'hash' => $hash,
            'signatures' => $signatures,
            'required_owner' => $requiredOwner,
            'required_active' => $requiredActive,
            'required_posting' => $requiredPosting,
            'required_other' => $requiredOther,
        ]);
    }
    
    // Account By Key API
    
    public function getKeyReferences($keys)
    {
        return $this->execute('account_by_key_api', 'get_key_references', [
            'keys' => $keys,
        ]);
    }

    // Network Broadcast API
    
    public function broadcastTransaction($trx)
    {
        return $this->execute('network_broadcast_api', 'broadcast_transaction', [
            'trx' => $trx,
        ]);
    }

    public function broadcastTransactionSynchronous($trx)
    {
        return $this->execute('network_broadcast_api', 'broadcast_transaction_synchronous', [
            'trx' => $trx,
        ]);
    }

    public function broadcastBlock($block)
    {
        return $this->execute('network_broadcast_api', 'broadcast_block', [
            'block' => $block,
        ]);
    }

    // Market History API

    public function getTicker()
    {
        return $this->execute('market_history_api', 'get_ticker');
    }

    public function getVolume()
    {
        return $this->execute('market_history_api', 'get_volume');
    }

    public function getOrderBook($limit)
    {
        return $this->execute('market_history_api', 'get_order_book', [
            'limit' => $limit,
        ]);
    }

    public function getTradeHistory($start, $end, $limit)
    {
        return $this->execute('market_history_api', 'get_trade_history', [
            'start' => $start,
            'end' => $end,
            'limit' => $limit,
        ]);
    }

    public function getRecentTrades($limit)
    {
        return $this->execute('market_history_api', 'get_recent_trades', [
            'limit' => $limit,
        ]);
    }

    public function getMarketHistory($bucketSeconds, $start, $end)
    {
        return $this->execute('market_history_api', 'get_market_history', [
            'bucket_seconds' => $bucketSeconds,
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function getMarketHistoryBuckets()
    {
        return $this->execute('market_history_api', 'get_market_history_buckets');
    }

    // Operations
    
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
    
    public function limitOrderCreate($wif, $owner, $orderId, $amountToSell, $minToReceive, $fillOrKill, $expiration)
    {
        return $this->broadcast($wif, 'limit_order_create', [
            'owner' => $owner,
            'orderid' => $orderId,
            'amount_to_sell' => $amountToSell,
            'min_to_receive' => $minToReceive,
            'fill_or_kill' => $fillOrKill,
            'expiration' => $expiration,
        ]);
    }
    
    public function limitOrderCancel($wif, $owner, $orderId)
    {
        return $this->broadcast($wif, 'limit_order_cancel', [
            'owner' => $owner,
            'orderid' => $orderId,
        ]);
    }
    
    public function feedPublish($wif, $publisher, $exchangeRateBase, $exchangeRateQuote)
    {
        return $this->broadcast($wif, 'feed_publish', [
            'publisher' => $publisher,
            'exchange_rate' => [
                'base' => $exchangeRateBase,
                'quote' => $exchangeRateQuote,
            ],
        ]);
    }
    
    public function convert($wif, $owner, $requestId, $amount)
    {
        return $this->broadcast($wif, 'convert', [
            'owner' => $owner,
            'requestid' => $requestId,
            'amount' => $amount,
        ]);
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
    
    public function witnessUpdate($wif, $owner, $url, $blockSigningKey, $props, $fee)
    {
        return $this->broadcast($wif, 'witness_update', [
            'owner' => $owner,
            'url' => $url,
            'block_signing_key' => $blockSigningKey,
            'props' => $props,
            'fee' => $fee,
        ]);
    }
    
    public function accountWitnessVote($wif, $account, $witness, $approve)
    {
        return $this->broadcast($wif, 'account_witness_vote', [
            'account' => $account,
            'witness' => $witness,
            'approve' => $approve,
        ]);
    }
    
    public function accountWitnessProxy($wif, $account, $proxy)
    {
        return $this->broadcast($wif, 'account_witness_proxy', [
            'account' => $account,
            'proxy' => $proxy,
        ]);
    }
    
    public function setWithdrawVestingRoute($wif, $fromAccount, $toAccount, $percent, $autoVest)
    {
        return $this->broadcast($wif, 'set_withdraw_vesting_route', [
            'from_account' => $fromAccount,
            'to_account' => $toAccount,
            'percent' => $percent,
            'auto_vest' => $autoVest,
        ]);
    }
    
    public function limitOrderCreate2($wif, $owner, $orderId, $amountToSell, $exchangeRateBase, $exchangeRateQuote, $fillOrKill, $expiration)
    {
        return $this->broadcast($wif, 'limit_order_create2', [
            'owner' => $owner,
            'orderid' => $orderId,
            'amount_to_sell' => $amountToSell,
            'exchange_rate' => [
                'base' => $exchangeRateBase,
                'quote' => $exchangeRateQuote,
            ],
            'fill_or_kill' => $fillOrKill,
            'expiration' => $expiration,
        ]);
    }
    
    public function requestAccountRecovery($wif, $recoveryAccount, $accountToRecover, $newOwnerAuthority, $extensions)
    {
        return $this->broadcast($wif, 'request_account_recovery', [
            'recovery_account' => $recoveryAccount,
            'account_to_recover' => $accountToRecover,
            'new_owner_authority' => $newOwnerAuthority,
            'extensions' => $extensions,
        ]);
    }
    
    public function recoverAccount($wif, $accountToRecover, $newOwnerAuthority, $recentOwnerAuthority, $extensions)
    {
        return $this->broadcast($wif, 'recover_account', [
            'account_to_recover' => $accountToRecover,
            'new_owner_authority' => $newOwnerAuthority,
            'recent_owner_authority' => $recentOwnerAuthority,
            'extensions' => $extensions,
        ]);
    }
    
    public function changeRecoveryAccount($wif, $accountToRecover, $newRecoveryAccount, $extensions)
    {
        return $this->broadcast($wif, 'change_recovery_account', [
            'account_to_recover' => $accountToRecover,
            'new_recovery_account' => $newRecoveryAccount,
            'extensions' => $extensions,
        ]);
    }
    
    public function escrowTransfer($wif, $from, $to, $amountABD, $amountAmalgam, $escrowId, $agent, $fee,
            $jsonMeta, $ratificationDeadline, $escrowExpiration)
    {
        return $this->broadcast($wif, 'escrow_transfer', [
            'from' => $from,
            'to' => $to,
            'abd_amount' => $amountABD,
            'amalgam_amount' => $amountAmalgam,
            'escrow_id' => $escrowId,
            'agent' => $agent,
            'fee' => $fee,
            'json_meta' => $jsonMeta,
            'ratification_deadline' => $ratificationDeadline,
            'escrow_expiration' => $escrowExpiration,
        ]);
    }
    
    public function escrowDispute($wif, $from, $to, $agent, $who, $escrowId)
    {
        return $this->broadcast($wif, 'escrow_dispute', [
            'from' => $from,
            'to' => $to,
            'agent' => $agent,
            'who' => $who,
            'escrow_id' => $escrowId,
        ]);
    }
    
    public function escrowRelease($wif, $from, $to, $agent, $who, $receiver, $escrowId, $amountABD, $amountAmalgam)
    {
        return $this->broadcast($wif, 'escrow_release', [
            'from' => $from,
            'to' => $to,
            'agent' => $agent,
            'who' => $who,
            'receiver' => $receiver,
            'escrow_id' => $escrowId,
            'abd_amount' => $amountABD,
            'amalgam_amount' => $amountAmalgam,
        ]);
    }
    
    public function escrowApprove($wif, $from, $to, $agent, $who, $escrowId, $approve)
    {
        return $this->broadcast($wif, 'escrow_approve', [
            'from' => $from,
            'to' => $to,
            'agent' => $agent,
            'who' => $who,
            'escrow_id' => $escrowId,
            'approve' => $approve,
        ]);
    }
    
    public function transferToSavings($wif, $from, $to, $amount, $memo)
    {
        return $this->broadcast($wif, 'transfer_to_savings', [
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
            'memo' => $memo,
        ]);
    }
    
    public function transferFromSavings($wif, $from, $requestId, $to, $amount, $memo)
    {
        return $this->broadcast($wif, 'transfer_from_savings', [
            'from' => $from,
            'request_id' => $requestId,
            'to' => $to,
            'amount' => $amount,
            'memo' => $memo,
        ]);
    }
    
    public function cancelTransferFromSavings($wif, $from, $requestId)
    {
        return $this->broadcast($wif, 'cancel_transfer_from_savings', [
            'from' => $from,
            'request_id' => $requestId,
        ]);
    }
    
    public function declineVotingRights($wif, $account, $decline)
    {
        return $this->broadcast($wif, 'decline_voting_rights', [
            'account' => $account,
            'decline' => $decline,
        ]);
    }
    
    public function delegateVestingShares($wif, $delegator, $delegatee, $vestingShares)
    {
        return $this->broadcast($wif, 'delegate_vesting_shares', [
            'delegator' => $delegator,
            'delegatee' => $delegatee,
            'vesting_shares' => $vestingShares,
        ]);
    }
    
    public function witnessSetProperties($wif, $owner, $props, $extensions)
    {
        return $this->broadcast($wif, 'witness_set_properties', [
            'owner' => $owner,
            'props' => $props,
            'extensions' => $extensions,
        ]);
    }
    
    // Helper functions
    
    public function getAccount($name)
    {
        $result = $this->findAccounts([$name]);
        return ($result != null) && is_array($result) && !empty($result) ? $result[0] : null;
    }
    
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
    
    public function generateKey($name, $password, $role)
    {
        $seed = $name . $role . $password;
        $brainKey = trim($seed);
        $hashSha256 = hash('sha256', $brainKey, true);
        $d = gmp_import($hashSha256, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        $private_key = new PrivateKey($d);
        return $private_key->toPublicKey()->toString();
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
        return $this->accountCreate($registrarWif, $properties['account_creation_fee'], $registrarName,
                $name, $owner, $active, $posting, $publicKeys['memo'], '');
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
    
    public function updateWitness($wif, $owner, $url, $block_signing_key)
    {
        return $this->witnessUpdate($wif, $owner, $url, $block_signing_key, $this->getChainProperties(),
                (new Asset(0, AssetSymbol::AML))->toString());
    }
}
