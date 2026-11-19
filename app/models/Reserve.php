<?php

class Reserve extends Model {
    protected $table = 'reserves';

    public function getUserReserves($userId, $status = 'active') {
        if ($status === 'all') {
            return $this->findAll(['user_id' => $userId], 'created_at DESC');
        }
        return $this->findAll(['user_id' => $userId, 'status' => $status], 'created_at DESC');
    }

    public function getReserveWithAccount($reserveId, $userId) {
        $stmt = $this->db->prepare("
            SELECT r.*, a.name as account_name, a.color as account_color
            FROM reserves r
            INNER JOIN accounts a ON r.account_id = a.id
            WHERE r.id = :id AND r.user_id = :user_id
        ");
        $stmt->bindValue(':id', $reserveId);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getUserReservesWithAccounts($userId, $status = 'active') {
        $query = "
            SELECT r.*, a.name as account_name, a.color as account_color
            FROM reserves r
            INNER JOIN accounts a ON r.account_id = a.id
            WHERE r.user_id = :user_id
        ";
        
        if ($status !== 'all') {
            $query .= " AND r.status = :status";
        }
        
        $query .= " ORDER BY r.priority DESC, r.deadline ASC, r.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $userId);
        if ($status !== 'all') {
            $stmt->bindValue(':status', $status);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalReserved($userId) {
        $stmt = $this->db->prepare("
            SELECT SUM(current_amount) as total 
            FROM reserves 
            WHERE user_id = :user_id AND status = 'active'
        ");
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getReservesByAccount($userId, $accountId) {
        return $this->findAll([
            'user_id' => $userId, 
            'account_id' => $accountId, 
            'status' => 'active'
        ]);
    }

    public function addAmount($reserveId, $amount) {
        $reserve = $this->findById($reserveId);
        if (!$reserve) return false;

        $newAmount = $reserve['current_amount'] + $amount;
        
        // Se atingiu ou ultrapassou a meta, marca como completada
        $status = $reserve['status'];
        if ($newAmount >= $reserve['target_amount'] && $status === 'active') {
            $status = 'completed';
        }

        return $this->update($reserveId, [
            'current_amount' => $newAmount,
            'status' => $status
        ]);
    }

    public function withdrawAmount($reserveId, $amount) {
        $reserve = $this->findById($reserveId);
        if (!$reserve) return false;

        $newAmount = $reserve['current_amount'] - $amount;
        if ($newAmount < 0) return false; // Não permite saldo negativo

        // Se estava completa e removeu dinheiro, volta para ativa
        $status = $reserve['status'];
        if ($status === 'completed' && $newAmount < $reserve['target_amount']) {
            $status = 'active';
        }

        return $this->update($reserveId, [
            'current_amount' => $newAmount,
            'status' => $status
        ]);
    }

    public function getProgress($reserveId) {
        $reserve = $this->findById($reserveId);
        if (!$reserve || $reserve['target_amount'] == 0) return 0;
        
        $progress = ($reserve['current_amount'] / $reserve['target_amount']) * 100;
        return min($progress, 100); // Máximo de 100%
    }

    public function getReserveTransactions($reserveId) {
        $stmt = $this->db->prepare("
            SELECT * FROM reserve_transactions 
            WHERE reserve_id = :reserve_id 
            ORDER BY transaction_date DESC, created_at DESC
        ");
        $stmt->bindValue(':reserve_id', $reserveId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function addTransaction($reserveId, $userId, $amount, $type, $description = '') {
        $stmt = $this->db->prepare("
            INSERT INTO reserve_transactions (reserve_id, user_id, amount, type, description, transaction_date)
            VALUES (:reserve_id, :user_id, :amount, :type, :description, CURDATE())
        ");
        
        $stmt->bindValue(':reserve_id', $reserveId);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':amount', $amount);
        $stmt->bindValue(':type', $type);
        $stmt->bindValue(':description', $description);
        
        if ($stmt->execute()) {
            // Atualiza o valor da reserva
            if ($type === 'deposit') {
                return $this->addAmount($reserveId, $amount);
            } else {
                return $this->withdrawAmount($reserveId, $amount);
            }
        }
        
        return false;
    }
}
