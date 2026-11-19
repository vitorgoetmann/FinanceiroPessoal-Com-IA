<?php

class Account extends Model {
    protected $table = 'accounts';

    public function getUserAccounts($userId) {
        return $this->findAll(['user_id' => $userId, 'is_active' => 1], 'name ASC');
    }

    public function getTotalBalance($userId) {
        $stmt = $this->db->prepare("
            SELECT SUM(balance) as total 
            FROM accounts 
            WHERE user_id = :user_id AND is_active = 1
        ");
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function updateBalance($accountId, $amount, $operation = 'add') {
        $account = $this->findById($accountId);
        if (!$account) return false;

        $newBalance = $operation === 'add' 
            ? $account['balance'] + $amount 
            : $account['balance'] - $amount;

        return $this->update($accountId, ['balance' => $newBalance]);
    }

    public function getAccountsByType($userId, $type) {
        return $this->findAll(['user_id' => $userId, 'type' => $type, 'is_active' => 1]);
    }
}
