<?php

class Auth extends Controller {
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User($this->db);
        $this->categoryModel = new Category($this->db);
    }

    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $this->setFlash('error', 'Por favor, preencha todos os campos');
                $this->view('auth/login', ['email' => $email]);
                return;
            }

            $user = $this->userModel->findByEmail($email);

            if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                
                $this->redirect('dashboard');
            } else {
                $this->setFlash('error', 'Email ou senha incorretos');
                $this->view('auth/login', ['email' => $email]);
            }
        } else {
            $this->view('auth/login');
        }
    }

    public function register() {
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $errors = [];

            if (empty($name)) {
                $errors[] = 'O nome é obrigatório';
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email inválido';
            }

            if (strlen($password) < 6) {
                $errors[] = 'A senha deve ter no mínimo 6 caracteres';
            }

            if ($password !== $confirmPassword) {
                $errors[] = 'As senhas não coincidem';
            }

            if ($this->userModel->findByEmail($email)) {
                $errors[] = 'Este email já está cadastrado';
            }

            if (empty($errors)) {
                $userId = $this->userModel->createUser($name, $email, $password);
                
                if ($userId) {
                    // Criar categorias padrão para o novo usuário
                    $this->createDefaultCategories($userId);
                    
                    $this->setFlash('success', 'Cadastro realizado com sucesso! Faça login para continuar.');
                    $this->redirect('auth/login');
                } else {
                    $this->setFlash('error', 'Erro ao criar conta. Tente novamente.');
                }
            } else {
                $this->view('auth/register', [
                    'errors' => $errors,
                    'name' => $name,
                    'email' => $email
                ]);
                return;
            }
        }

        $this->view('auth/register');
    }

    public function logout() {
        session_destroy();
        $this->redirect('auth/login');
    }

    public function forgot() {
        // Implementação futura de recuperação de senha
        $this->view('auth/forgot');
    }

    private function createDefaultCategories($userId) {
        // Categorias de Despesa
        $expenseCategories = [
            ['name' => 'Alimentação', 'icon' => 'fas fa-utensils', 'color' => '#FF5722'],
            ['name' => 'Transporte', 'icon' => 'fas fa-car', 'color' => '#2196F3'],
            ['name' => 'Saúde', 'icon' => 'fas fa-heartbeat', 'color' => '#F44336'],
            ['name' => 'Educação', 'icon' => 'fas fa-graduation-cap', 'color' => '#9C27B0'],
            ['name' => 'Lazer', 'icon' => 'fas fa-gamepad', 'color' => '#FF9800'],
            ['name' => 'Moradia', 'icon' => 'fas fa-home', 'color' => '#795548'],
            ['name' => 'Contas', 'icon' => 'fas fa-bolt', 'color' => '#FFC107'],
            ['name' => 'Compras', 'icon' => 'fas fa-shopping-bag', 'color' => '#E91E63'],
            ['name' => 'Roupas', 'icon' => 'fas fa-tshirt', 'color' => '#673AB7'],
            ['name' => 'Outros', 'icon' => 'fas fa-ellipsis-h', 'color' => '#607D8B']
        ];

        // Categorias de Receita
        $incomeCategories = [
            ['name' => 'Salário', 'icon' => 'fas fa-money-bill-wave', 'color' => '#4CAF50'],
            ['name' => 'Freelance', 'icon' => 'fas fa-laptop-code', 'color' => '#00BCD4'],
            ['name' => 'Investimentos', 'icon' => 'fas fa-chart-line', 'color' => '#8BC34A'],
            ['name' => 'Presentes', 'icon' => 'fas fa-gift', 'color' => '#CDDC39'],
            ['name' => 'Outros', 'icon' => 'fas fa-ellipsis-h', 'color' => '#009688']
        ];

        // Inserir categorias de despesa
        foreach ($expenseCategories as $category) {
            $this->categoryModel->create([
                'user_id' => $userId,
                'name' => $category['name'],
                'type' => 'expense',
                'icon' => $category['icon'],
                'color' => $category['color']
            ]);
        }

        // Inserir categorias de receita
        foreach ($incomeCategories as $category) {
            $this->categoryModel->create([
                'user_id' => $userId,
                'name' => $category['name'],
                'type' => 'income',
                'icon' => $category['icon'],
                'color' => $category['color']
            ]);
        }
    }
}
