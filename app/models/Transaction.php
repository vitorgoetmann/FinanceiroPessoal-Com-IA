<?php

class Transaction extends Model {
    protected $table = 'transactions';

    public function getUserTransactions($userId, $filters = []) {
        $sql = "SELECT t.*, a.name as account_name, c.name as category_name, c.color as category_color, c.icon as category_icon
                FROM transactions t
                INNER JOIN accounts a ON t.account_id = a.id
                INNER JOIN categories c ON t.category_id = c.id
                WHERE t.user_id = :user_id";

        $params = ['user_id' => $userId];

        if (!empty($filters['start_date'])) {
            $sql .= " AND t.transaction_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND t.transaction_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND t.type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND t.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['account_id'])) {
            $sql .= " AND t.account_id = :account_id";
            $params['account_id'] = $filters['account_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $params['status'] = $filters['status'];
        }

        $sql .= " ORDER BY t.transaction_date DESC, t.id DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . intval($filters['limit']);
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMonthlyTotal($userId, $type, $month, $year, $accountId = null) {
        $sql = "SELECT SUM(amount) as total
                FROM transactions
                WHERE user_id = :user_id 
                AND type = :type
                AND MONTH(transaction_date) = :month
                AND YEAR(transaction_date) = :year
                AND status = 'paid'";
        
        $params = [
            'user_id' => $userId,
            'type' => $type,
            'month' => $month,
            'year' => $year
        ];
        
        if ($accountId) {
            $sql .= " AND account_id = :account_id";
            $params['account_id'] = $accountId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getCategoryTotals($userId, $type, $month, $year, $accountId = null) {
        $sql = "SELECT c.name, c.color, c.icon, SUM(t.amount) as total
                FROM transactions t
                INNER JOIN categories c ON t.category_id = c.id
                WHERE t.user_id = :user_id 
                AND t.type = :type
                AND MONTH(t.transaction_date) = :month
                AND YEAR(t.transaction_date) = :year
                AND t.status = 'paid'";
        
        $params = [
            'user_id' => $userId,
            'type' => $type,
            'month' => $month,
            'year' => $year
        ];
        
        if ($accountId) {
            $sql .= " AND t.account_id = :account_id";
            $params['account_id'] = $accountId;
        }
        
        $sql .= " GROUP BY c.id, c.name, c.color, c.icon ORDER BY total DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getAccountTotals($userId, $type, $month, $year) {
        $stmt = $this->db->prepare("
            SELECT a.name, a.color, SUM(t.amount) as total
            FROM transactions t
            INNER JOIN accounts a ON t.account_id = a.id
            WHERE t.user_id = :user_id 
            AND t.type = :type
            AND MONTH(t.transaction_date) = :month
            AND YEAR(t.transaction_date) = :year
            AND t.status = 'paid'
            GROUP BY a.id, a.name, a.color
            ORDER BY total DESC
        ");
        $stmt->execute([
            'user_id' => $userId,
            'type' => $type,
            'month' => $month,
            'year' => $year
        ]);
        return $stmt->fetchAll();
    }

    public function createWithInstallments($data) {
        if ($data['installments'] <= 1) {
            return $this->create($data);
        }

        $parentId = null;
        $baseAmount = $data['amount'] / $data['installments'];
        
        for ($i = 1; $i <= $data['installments']; $i++) {
            $installmentDate = date('Y-m-d', strtotime($data['transaction_date'] . " +{$i} months"));
            
            $installmentData = $data;
            $installmentData['amount'] = $baseAmount;
            $installmentData['installment_number'] = $i;
            $installmentData['transaction_date'] = $installmentDate;
            $installmentData['parent_transaction_id'] = $parentId;
            
            $transactionId = $this->create($installmentData);
            
            if ($i === 1) {
                $parentId = $transactionId;
            }
        }
        
        return $parentId;
    }

    public function getMonthlyEvolution($userId, $months = 12) {
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(transaction_date, '%Y-%m') as month,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
            FROM transactions
            WHERE user_id = :user_id 
            AND status = 'paid'
            AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
            GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
            ORDER BY month ASC
        ");
        $stmt->execute(['user_id' => $userId, 'months' => $months]);
        return $stmt->fetchAll();
    }
}
