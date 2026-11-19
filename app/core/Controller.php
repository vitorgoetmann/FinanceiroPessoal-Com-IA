<?php

class Controller {
    protected $db;
    
    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function model($model) {
        require_once BASE_PATH . '/app/models/' . $model . '.php';
        return new $model($this->db);
    }

    public function view($view, $data = []) {
        extract($data);
        require_once BASE_PATH . '/app/views/' . $view . '.php';
    }

    public function redirect($url) {
        header('Location: ' . BASE_URL . $url);
        exit();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            $this->redirect('auth/login');
        }
    }

    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    public function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    public function setFlash($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    public function getFlash() {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}
