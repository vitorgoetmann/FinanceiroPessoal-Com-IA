<?php

class Reserves extends Controller {
    
    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->reserveModel = new Reserve($this->db);
        $this->accountModel = new Account($this->db);
    }

    public function index() {
        $userId = $this->getUserId();
        $status = $_GET['status'] ?? 'active';
        
        $reserves = $this->reserveModel->getUserReservesWithAccounts($userId, $status);
        $totalReserved = $this->reserveModel->getTotalReserved($userId);
        
        $data = [
            'reserves' => $reserves,
            'totalReserved' => $totalReserved,
            'statusFilter' => $status
        ];

        $this->view('reserves/index', $data);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $this->getUserId();
            
            $data = [
                'user_id' => $userId,
                'account_id' => $_POST['account_id'] ?? null,
                'name' => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'target_amount' => floatval($_POST['target_amount'] ?? 0),
                'current_amount' => floatval($_POST['current_amount'] ?? 0),
                'deadline' => $_POST['deadline'] ?? null,
                'priority' => $_POST['priority'] ?? 'medium',
                'color' => $_POST['color'] ?? '#4CAF50',
                'status' => 'active'
            ];

            // Validações
            if (empty($data['name'])) {
                $this->setFlash('Nome da reserva é obrigatório', 'error');
                $this->redirect('reserves/form');
                return;
            }

            if (empty($data['account_id'])) {
                $this->setFlash('Selecione uma conta', 'error');
                $this->redirect('reserves/form');
                return;
            }

            if ($data['target_amount'] <= 0) {
                $this->setFlash('Valor da meta deve ser maior que zero', 'error');
                $this->redirect('reserves/form');
                return;
            }

            // Verifica se há valor inicial e se a conta tem saldo suficiente
            if ($data['current_amount'] > 0) {
                $account = $this->accountModel->findById($data['account_id']);
                if (!$account || $account['balance'] < $data['current_amount']) {
                    $this->setFlash('Saldo insuficiente na conta selecionada', 'error');
                    $this->redirect('reserves/form');
                    return;
                }
            }

            if ($this->reserveModel->create($data)) {
                // Se tem valor inicial, desconta da conta
                if ($data['current_amount'] > 0) {
                    $this->accountModel->updateBalance($data['account_id'], $data['current_amount'], 'subtract');
                }
                $this->setFlash('Reserva criada com sucesso!', 'success');
                $this->redirect('reserves');
            } else {
                $this->setFlash('Erro ao criar reserva', 'error');
                $this->redirect('reserves/form');
            }
        } else {
            $this->form();
        }
    }

    public function edit($id) {
        $userId = $this->getUserId();
        $reserve = $this->reserveModel->findById($id);

        if (!$reserve || $reserve['user_id'] != $userId) {
            $this->setFlash('Reserva não encontrada', 'error');
            $this->redirect('reserves');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'target_amount' => floatval($_POST['target_amount'] ?? 0),
                'deadline' => $_POST['deadline'] ?? null,
                'priority' => $_POST['priority'] ?? 'medium',
                'color' => $_POST['color'] ?? '#4CAF50'
            ];

            // Validações
            if (empty($data['name'])) {
                $this->setFlash('Nome da reserva é obrigatório', 'error');
                $this->redirect('reserves/form/' . $id);
                return;
            }

            if ($data['target_amount'] <= 0) {
                $this->setFlash('Valor da meta deve ser maior que zero', 'error');
                $this->redirect('reserves/form/' . $id);
                return;
            }

            if ($this->reserveModel->update($id, $data)) {
                $this->setFlash('Reserva atualizada com sucesso!', 'success');
                $this->redirect('reserves');
            } else {
                $this->setFlash('Erro ao atualizar reserva', 'error');
                $this->redirect('reserves/form/' . $id);
            }
        } else {
            $this->form($id);
        }
    }

    public function form($id = null) {
        $userId = $this->getUserId();
        $accounts = $this->accountModel->getUserAccounts($userId);
        
        $data = [
            'accounts' => $accounts,
            'reserve' => null
        ];

        if ($id) {
            $reserve = $this->reserveModel->findById($id);
            if (!$reserve || $reserve['user_id'] != $userId) {
                $this->setFlash('Reserva não encontrada', 'error');
                $this->redirect('reserves');
                return;
            }
            $data['reserve'] = $reserve;
        }

        $this->view('reserves/form', $data);
    }

    public function delete($id) {
        $userId = $this->getUserId();
        $reserve = $this->reserveModel->findById($id);

        if (!$reserve || $reserve['user_id'] != $userId) {
            $this->setFlash('Reserva não encontrada', 'error');
            $this->redirect('reserves');
            return;
        }

        if ($this->reserveModel->delete($id)) {
            $this->setFlash('Reserva excluída com sucesso!', 'success');
        } else {
            $this->setFlash('Erro ao excluir reserva', 'error');
        }

        $this->redirect('reserves');
    }

    public function transaction($id) {
        $userId = $this->getUserId();
        $reserve = $this->reserveModel->getReserveWithAccount($id, $userId);

        if (!$reserve) {
            $this->setFlash('Reserva não encontrada', 'error');
            $this->redirect('reserves');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = floatval($_POST['amount'] ?? 0);
            $type = $_POST['type'] ?? 'deposit';
            $description = trim($_POST['description'] ?? '');

            if ($amount <= 0) {
                $this->setFlash('Valor deve ser maior que zero', 'error');
                $this->redirect('reserves/transaction/' . $id);
                return;
            }

            if ($type === 'withdraw' && $amount > $reserve['current_amount']) {
                $this->setFlash('Saldo insuficiente na reserva', 'error');
                $this->redirect('reserves/transaction/' . $id);
                return;
            }

            // Se for depósito, verifica se a conta tem saldo
            if ($type === 'deposit') {
                $account = $this->accountModel->findById($reserve['account_id']);
                if (!$account || $account['balance'] < $amount) {
                    $this->setFlash('Saldo insuficiente na conta', 'error');
                    $this->redirect('reserves/transaction/' . $id);
                    return;
                }
            }

            if ($this->reserveModel->addTransaction($id, $userId, $amount, $type, $description)) {
                // Atualiza o saldo da conta
                if ($type === 'deposit') {
                    // Ao depositar na reserva, desconta da conta
                    $this->accountModel->updateBalance($reserve['account_id'], $amount, 'subtract');
                } else {
                    // Ao retirar da reserva, devolve para a conta
                    $this->accountModel->updateBalance($reserve['account_id'], $amount, 'add');
                }
                
                $actionText = $type === 'deposit' ? 'adicionado' : 'retirado';
                $this->setFlash("Valor {$actionText} com sucesso!", 'success');
                $this->redirect('reserves');
            } else {
                $this->setFlash('Erro ao processar transação', 'error');
                $this->redirect('reserves/transaction/' . $id);
            }
        } else {
            $transactions = $this->reserveModel->getReserveTransactions($id);
            
            $data = [
                'reserve' => $reserve,
                'transactions' => $transactions
            ];

            $this->view('reserves/transaction', $data);
        }
    }

    public function updateStatus($id) {
        $userId = $this->getUserId();
        $reserve = $this->reserveModel->findById($id);

        if (!$reserve || $reserve['user_id'] != $userId) {
            echo json_encode(['success' => false, 'message' => 'Reserva não encontrada']);
            return;
        }

        $status = $_POST['status'] ?? null;
        if (!in_array($status, ['active', 'completed', 'cancelled'])) {
            echo json_encode(['success' => false, 'message' => 'Status inválido']);
            return;
        }

        if ($this->reserveModel->update($id, ['status' => $status])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
        }
    }
}
