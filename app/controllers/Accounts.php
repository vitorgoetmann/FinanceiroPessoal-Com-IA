<?php

class Accounts extends Controller {
    
    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->accountModel = new Account($this->db);
    }

    public function index() {
        $userId = $this->getUserId();
        $accounts = $this->accountModel->findAll(['user_id' => $userId], 'name ASC');
        
        $this->view('accounts/index', ['accounts' => $accounts]);
    }

    public function new() {
        $this->view('accounts/form', ['account' => null]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('accounts');
            return;
        }

        $userId = $this->getUserId();
        
        $data = [
            'user_id' => $userId,
            'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING),
            'type' => $_POST['type'],
            'balance' => str_replace(',', '.', str_replace('.', '', str_replace('R$ ', '', $_POST['balance']))),
            'credit_limit' => !empty($_POST['credit_limit']) ? str_replace(',', '.', str_replace('.', '', str_replace('R$ ', '', $_POST['credit_limit']))) : null,
            'closing_day' => !empty($_POST['closing_day']) ? $_POST['closing_day'] : null,
            'due_day' => !empty($_POST['due_day']) ? $_POST['due_day'] : null,
            'color' => $_POST['color'] ?? '#00897B',
            'is_active' => 1
        ];

        if ($this->accountModel->create($data)) {
            $this->setFlash('success', 'Conta criada com sucesso!');
        } else {
            $this->setFlash('error', 'Erro ao criar conta');
        }

        $this->redirect('accounts');
    }

    public function edit($id) {
        $userId = $this->getUserId();
        $account = $this->accountModel->findById($id);

        if (!$account || $account['user_id'] != $userId) {
            $this->setFlash('error', 'Conta não encontrada');
            $this->redirect('accounts');
            return;
        }

        $this->view('accounts/form', ['account' => $account]);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('accounts');
            return;
        }

        $userId = $this->getUserId();
        $account = $this->accountModel->findById($id);

        if (!$account || $account['user_id'] != $userId) {
            $this->setFlash('error', 'Conta não encontrada');
            $this->redirect('accounts');
            return;
        }

        $data = [
            'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING),
            'type' => $_POST['type'],
            'balance' => str_replace(',', '.', str_replace('.', '', str_replace('R$ ', '', $_POST['balance']))),
            'credit_limit' => !empty($_POST['credit_limit']) ? str_replace(',', '.', str_replace('.', '', str_replace('R$ ', '', $_POST['credit_limit']))) : null,
            'closing_day' => !empty($_POST['closing_day']) ? $_POST['closing_day'] : null,
            'due_day' => !empty($_POST['due_day']) ? $_POST['due_day'] : null,
            'color' => $_POST['color'] ?? '#00897B',
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        if ($this->accountModel->update($id, $data)) {
            $this->setFlash('success', 'Conta atualizada com sucesso!');
        } else {
            $this->setFlash('error', 'Erro ao atualizar conta');
        }

        $this->redirect('accounts');
    }

    public function delete($id) {
        $userId = $this->getUserId();
        $account = $this->accountModel->findById($id);

        if (!$account || $account['user_id'] != $userId) {
            $this->json(['success' => false, 'message' => 'Conta não encontrada'], 404);
            return;
        }

        if ($this->accountModel->delete($id)) {
            $this->setFlash('success', 'Conta excluída com sucesso!');
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'message' => 'Erro ao excluir conta'], 500);
        }
    }
}
