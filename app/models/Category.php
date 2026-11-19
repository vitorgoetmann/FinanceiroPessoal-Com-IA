<?php

class Category extends Model {
    protected $table = 'categories';

    public function getUserCategories($userId, $type = null) {
        $conditions = ['user_id' => $userId];
        if ($type) {
            $conditions['type'] = $type;
        }
        return $this->findAll($conditions, 'name ASC');
    }

    public function getExpenseCategories($userId) {
        return $this->getUserCategories($userId, 'expense');
    }

    public function getIncomeCategories($userId) {
        return $this->getUserCategories($userId, 'income');
    }
}
