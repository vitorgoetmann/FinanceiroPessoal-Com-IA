<?php

class User extends Model {
    protected $table = 'users';

    public function createUser($name, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $userId = $this->create([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword
        ]);

        // Criar categorias padrão para o novo usuário
        if ($userId) {
            $this->createDefaultCategories($userId);
        }

        return $userId;
    }

    public function findByEmail($email) {
        return $this->findOne(['email' => $email]);
    }

    public function verifyPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }

    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }

    private function createDefaultCategories($userId) {
        $stmt = $this->db->prepare("
            INSERT INTO categories (user_id, name, type, color, icon, is_default)
            SELECT :user_id, name, type, color, icon, TRUE
            FROM categories
            WHERE user_id = 0
        ");
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
    }
}
