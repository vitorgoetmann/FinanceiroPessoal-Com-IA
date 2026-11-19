<?php

class Transactions extends Controller {
    protected $transactionModel;
    protected $accountModel;
    protected $categoryModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->transactionModel = new Transaction($this->db);
        $this->accountModel = new Account($this->db);
        $this->categoryModel = new Category($this->db);
    }

    public function index() {
        $userId = $this->getUserId();
        
        $filters = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
            'end_date' => $_GET['end_date'] ?? date('Y-m-t'),
            'type' => $_GET['type'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'account_id' => $_GET['account_id'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];

        $transactions = $this->transactionModel->getUserTransactions($userId, $filters);
        $accounts = $this->accountModel->getUserAccounts($userId);
        $categories = $this->categoryModel->getUserCategories($userId);
        
        $this->view('transactions/index', [
            'transactions' => $transactions,
            'accounts' => $accounts,
            'categories' => $categories,
            'filters' => $filters
        ]);
    }

    public function new() {
        $userId = $this->getUserId();
        $accounts = $this->accountModel->getUserAccounts($userId);
        $categories = $this->categoryModel->getUserCategories($userId);
        
        $this->view('transactions/form', [
            'transaction' => null,
            'accounts' => $accounts,
            'categories' => $categories
        ]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('transactions');
            return;
        }

        $userId = $this->getUserId();
        
        $data = [
            'user_id' => $userId,
            'account_id' => $_POST['account_id'],
            'category_id' => $_POST['category_id'],
            'type' => $_POST['type'],
            'amount' => str_replace(',', '.', str_replace('.', '', str_replace('R$ ', '', $_POST['amount']))),
            'description' => filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING),
            'transaction_date' => $_POST['transaction_date'],
            'status' => $_POST['status'] ?? 'paid',
            'is_recurring' => isset($_POST['is_recurring']) ? 1 : 0,
            'recurring_frequency' => $_POST['recurring_frequency'] ?? null,
            'installments' => $_POST['installments'] ?? 1,
            'notes' => $_POST['notes'] ?? ''
        ];

        if ($data['installments'] > 1) {
            $transactionId = $this->transactionModel->createWithInstallments($data);
            
            // Atualizar saldo apenas da primeira parcela se status for 'paid'
            if ($data['status'] === 'paid') {
                $firstInstallmentAmount = $data['amount'] / $data['installments'];
                if ($data['type'] === 'expense') {
                    $this->accountModel->updateBalance($data['account_id'], $firstInstallmentAmount, 'subtract');
                } else {
                    $this->accountModel->updateBalance($data['account_id'], $firstInstallmentAmount, 'add');
                }
            }
        } else {
            $transactionId = $this->transactionModel->create($data);
            
            // Atualizar saldo da conta apenas se status for 'paid'
            if ($data['status'] === 'paid') {
                if ($data['type'] === 'expense') {
                    $this->accountModel->updateBalance($data['account_id'], $data['amount'], 'subtract');
                } else {
                    $this->accountModel->updateBalance($data['account_id'], $data['amount'], 'add');
                }
            }
        }

        if ($transactionId) {
            
            $this->setFlash('success', 'Transação criada com sucesso!');
        } else {
            $this->setFlash('error', 'Erro ao criar transação');
        }

        $this->redirect('transactions');
    }

    public function parseText() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Metodo nao permitido'], 405);
            return;
        }

        $text = trim($_POST['text'] ?? '');
        if ($text === '') {
            $this->json(['success' => false, 'message' => 'Texto nao informado'], 400);
            return;
        }

        if (!defined('AI_API_KEY') || AI_API_KEY === '') {
            $this->json(['success' => false, 'message' => 'Chave da API de IA nao configurada'], 500);
            return;
        }

        $userId = $this->getUserId();
        $accounts = $this->accountModel->getUserAccounts($userId);
        $categories = $this->categoryModel->getUserCategories($userId);

        $categoryNames = array_map(function($cat) {
            return $cat['name'];
        }, $categories);
        $accountNames = array_map(function($acc) {
            return $acc['name'];
        }, $accounts);

        $today = date('Y-m-d');

        $systemPrompt = "Voce e um parser de transacoes financeiras. Responda SOMENTE com JSON valido, sem markdown. Use as chaves: type, amount, description, transaction_date, category_name, account_name, status, notes. type deve ser expense ou income. transaction_date deve ser YYYY-MM-DD. Se o usuario informar apenas o dia (ex: 'dia 17'), use o mes e o ano atuais. Se informar dia e mes sem ano, use o ano atual. status: paid, pending, scheduled (padrao: paid). category_name e account_name devem ser escolhidos exatamente das listas fornecidas; se nao for possivel, use null. description deve ser um resumo curto e claro da transacao.";

        $userPrompt = "Texto: {$text}\nCategorias: " . implode(', ', $categoryNames) . "\nContas: " . implode(', ', $accountNames);

        $payload = [
            'model' => AI_MODEL,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => 0.2
        ];

        $response = $this->callAiApi($payload);
        if (!$response['success']) {
            $this->json(['success' => false, 'message' => $response['message']], 502);
            return;
        }

        $content = $response['content'] ?? '';
        $parsed = $this->extractJson($content);
        if (!is_array($parsed)) {
            $this->json(['success' => false, 'message' => 'Falha ao interpretar a resposta da IA'], 502);
            return;
        }

        $normalized = $this->normalizeParsedResult($parsed);
        $inferredDate = $this->inferDateFromText($text);
        if ($inferredDate) {
            $normalized['transaction_date'] = $inferredDate;
        } else {
            $normalized['transaction_date'] = date('Y-m-d');
        }
        $categoryId = $this->findBestMatchId($normalized['category_name'], $categories);
        $accountId = $this->findBestMatchId($normalized['account_name'], $accounts);

        $normalized['category_id'] = $categoryId;
        $normalized['account_id'] = $accountId;

        $this->json([
            'success' => true,
            'data' => $normalized
        ]);
    }

    public function createFromAi() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Metodo nao permitido'], 405);
            return;
        }

        $userId = $this->getUserId();
        $amount = $this->normalizeAmount($_POST['amount'] ?? null);
        $categoryId = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
        $accountId = isset($_POST['account_id']) ? (int) $_POST['account_id'] : 0;
        $type = $_POST['type'] ?? 'expense';
        $status = $_POST['status'] ?? 'paid';
        $transactionDate = $this->normalizeDate($_POST['transaction_date'] ?? null);
        $description = filter_var($_POST['description'] ?? '', FILTER_SANITIZE_STRING);
        $notes = filter_var($_POST['notes'] ?? '', FILTER_SANITIZE_STRING);

        if (!$amount || $amount <= 0) {
            $this->json(['success' => false, 'message' => 'Valor invalido'], 422);
            return;
        }

        if ($categoryId <= 0 || $accountId <= 0) {
            $this->json(['success' => false, 'message' => 'Categoria ou conta nao informada'], 422);
            return;
        }

        if (trim($description) === '') {
            $this->json(['success' => false, 'message' => 'Descricao obrigatoria'], 422);
            return;
        }

        if (!in_array($type, ['expense', 'income'])) {
            $type = 'expense';
        }

        if (!in_array($status, ['paid', 'pending', 'scheduled'])) {
            $status = 'paid';
        }

        $data = [
            'user_id' => $userId,
            'account_id' => $accountId,
            'category_id' => $categoryId,
            'type' => $type,
            'amount' => $amount,
            'description' => $description,
            'transaction_date' => $transactionDate,
            'status' => $status,
            'is_recurring' => 0,
            'recurring_frequency' => null,
            'installments' => 1,
            'notes' => $notes
        ];

        $transactionId = $this->transactionModel->create($data);
        if ($transactionId) {
            if ($status === 'paid') {
                if ($type === 'expense') {
                    $this->accountModel->updateBalance($accountId, $amount, 'subtract');
                } else {
                    $this->accountModel->updateBalance($accountId, $amount, 'add');
                }
            }

            $this->json(['success' => true, 'id' => $transactionId]);
            return;
        }

        $this->json(['success' => false, 'message' => 'Erro ao criar transacao'], 500);
    }

    public function edit($id) {
        $userId = $this->getUserId();
        $transaction = $this->transactionModel->findById($id);

        if (!$transaction || $transaction['user_id'] != $userId) {
            $this->setFlash('error', 'Transação não encontrada');
            $this->redirect('transactions');
            return;
        }

        $accounts = $this->accountModel->getUserAccounts($userId);
        $categories = $this->categoryModel->getUserCategories($userId);

        $this->view('transactions/form', [
            'transaction' => $transaction,
            'accounts' => $accounts,
            'categories' => $categories
        ]);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('transactions');
            return;
        }

        $userId = $this->getUserId();
        $transaction = $this->transactionModel->findById($id);

        if (!$transaction || $transaction['user_id'] != $userId) {
            $this->setFlash('error', 'Transação não encontrada');
            $this->redirect('transactions');
            return;
        }

        // Reverter saldo da transação antiga (apenas se estava paga)
        if ($transaction['status'] === 'paid') {
            if ($transaction['type'] === 'expense') {
                $this->accountModel->updateBalance($transaction['account_id'], $transaction['amount'], 'add');
            } else {
                $this->accountModel->updateBalance($transaction['account_id'], $transaction['amount'], 'subtract');
            }
        }

        $data = [
            'account_id' => $_POST['account_id'],
            'category_id' => $_POST['category_id'],
            'type' => $_POST['type'],
            'amount' => str_replace(',', '.', str_replace('.', '', str_replace('R$ ', '', $_POST['amount']))),
            'description' => filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING),
            'transaction_date' => $_POST['transaction_date'],
            'status' => $_POST['status'] ?? 'paid',
            'notes' => $_POST['notes'] ?? ''
        ];

        if ($this->transactionModel->update($id, $data)) {
            // Aplicar novo saldo (apenas se status for 'paid')
            if ($data['status'] === 'paid') {
                if ($data['type'] === 'expense') {
                    $this->accountModel->updateBalance($data['account_id'], $data['amount'], 'subtract');
                } else {
                    $this->accountModel->updateBalance($data['account_id'], $data['amount'], 'add');
                }
            }
            
            $this->setFlash('success', 'Transação atualizada com sucesso!');
        } else {
            $this->setFlash('error', 'Erro ao atualizar transação');
        // Reverter saldo da conta
        if ($transaction['type'] === 'expense') {
            $this->accountModel->updateBalance($transaction['account_id'], $transaction['amount'], 'add');
        } else {
            $this->accountModel->updateBalance($transaction['account_id'], $transaction['amount'], 'subtract');
        }

        }

        $this->redirect('transactions');
    }

    public function delete($id) {
        $userId = $this->getUserId();
        $transaction = $this->transactionModel->findById($id);

        if (!$transaction || $transaction['user_id'] != $userId) {
            $this->json(['success' => false, 'message' => 'Transação não encontrada'], 404);
            return;
        }

        // Reverter saldo antes de excluir (apenas se estava paga)
        if ($transaction['status'] === 'paid') {
            if ($transaction['type'] === 'expense') {
                $this->accountModel->updateBalance($transaction['account_id'], $transaction['amount'], 'add');
            } else {
                $this->accountModel->updateBalance($transaction['account_id'], $transaction['amount'], 'subtract');
            }
        }

        if ($this->transactionModel->delete($id)) {
            $this->setFlash('success', 'Transação excluída com sucesso!');
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'message' => 'Erro ao excluir transação'], 500);
        }
    }

    private function callAiApi($payload) {
        $ch = curl_init(AI_API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . AI_API_KEY
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $raw = curl_exec($ch);
        $error = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            return ['success' => false, 'message' => 'Erro de conexao: ' . $error];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return ['success' => false, 'message' => 'Resposta invalida da API'];
        }

        if ($status < 200 || $status >= 300) {
            $apiMessage = $decoded['error']['message'] ?? 'Erro na API';
            return ['success' => false, 'message' => $apiMessage];
        }

        $content = $decoded['choices'][0]['message']['content'] ?? '';
        return ['success' => true, 'content' => $content];
    }

    private function extractJson($content) {
        $content = trim($content);
        if ($content === '') {
            return null;
        }

        $parsed = json_decode($content, true);
        if (is_array($parsed)) {
            return $parsed;
        }

        if (preg_match('/\{.*\}/s', $content, $match)) {
            $parsed = json_decode($match[0], true);
            if (is_array($parsed)) {
                return $parsed;
            }
        }

        return null;
    }

    private function normalizeParsedResult($parsed) {
        $type = in_array($parsed['type'] ?? '', ['expense', 'income']) ? $parsed['type'] : 'expense';
        $status = in_array($parsed['status'] ?? '', ['paid', 'pending', 'scheduled']) ? $parsed['status'] : 'paid';
        $amount = $this->normalizeAmount($parsed['amount'] ?? null);
        $date = $this->normalizeDate($parsed['transaction_date'] ?? null);

        return [
            'type' => $type,
            'amount' => $amount,
            'description' => trim($parsed['description'] ?? ''),
            'transaction_date' => $date,
            'status' => $status,
            'category_name' => $parsed['category_name'] ?? null,
            'account_name' => $parsed['account_name'] ?? null,
            'notes' => $parsed['notes'] ?? ''
        ];
    }

    private function normalizeAmount($value) {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = str_replace(['R$', ' '], '', (string) $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (float) $value : null;
    }

    private function normalizeDate($value) {
        if ($value === null || trim($value) === '') {
            return date('Y-m-d');
        }

        $value = trim($value);

        if (preg_match('/^\d{1,2}$/', $value)) {
            $day = str_pad($value, 2, '0', STR_PAD_LEFT);
            return date('Y-m-') . $day;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        if (preg_match('/^\d{1,2}\/\d{1,2}(\/\d{2,4})?$/', $value)) {
            $parts = explode('/', $value);
            $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
            $month = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
            $year = $parts[2] ?? date('Y');
            if (strlen($year) === 2) {
                $year = '20' . $year;
            }
            return $year . '-' . $month . '-' . $day;
        }

        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return date('Y-m-d');
    }

    private function findBestMatchId($name, $items) {
        if (!$name) {
            return null;
        }

        $needle = $this->normalizeText($name);
        $bestId = null;
        $bestScore = PHP_INT_MAX;

        foreach ($items as $item) {
            $candidate = $this->normalizeText($item['name']);
            if ($candidate === $needle) {
                return $item['id'];
            }

            if (strpos($candidate, $needle) !== false || strpos($needle, $candidate) !== false) {
                $distance = abs(strlen($candidate) - strlen($needle));
                if ($distance < $bestScore) {
                    $bestScore = $distance;
                    $bestId = $item['id'];
                }
            }
        }

        return $bestId;
    }

    private function normalizeText($value) {
        $value = trim($value);
        if (function_exists('mb_strtolower')) {
            $value = mb_strtolower($value, 'UTF-8');
        } else {
            $value = strtolower($value);
        }

        $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT', $value);
        if ($transliterated !== false) {
            $value = $transliterated;
        }

        $value = preg_replace('/[^a-z0-9\s]/', '', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    private function inferDateFromText($text) {
        if (!$text) {
            return null;
        }

        $normalized = $this->normalizeText($text);

        if (preg_match('/\bhoje\b/', $normalized)) {
            return date('Y-m-d');
        }

        if (preg_match('/\bontem\b/', $normalized)) {
            return date('Y-m-d', strtotime('-1 day'));
        }

        if (preg_match('/\b(\d{1,2})[\/\-](\d{1,2})(?:[\/\-](\d{2,4}))?\b/', $normalized, $match)) {
            $day = str_pad($match[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($match[2], 2, '0', STR_PAD_LEFT);
            $year = $match[3] ?? date('Y');
            if (strlen($year) === 2) {
                $year = '20' . $year;
            }
            return $year . '-' . $month . '-' . $day;
        }

        $monthMap = [
            'janeiro' => '01',
            'fevereiro' => '02',
            'marco' => '03',
            'abril' => '04',
            'maio' => '05',
            'junho' => '06',
            'julho' => '07',
            'agosto' => '08',
            'setembro' => '09',
            'outubro' => '10',
            'novembro' => '11',
            'dezembro' => '12'
        ];

        foreach ($monthMap as $name => $month) {
            if (strpos($normalized, $name) === false) {
                continue;
            }

            if (preg_match('/\b(\d{1,2})\s*(de\s*)?' . $name . '\b/', $normalized, $match)) {
                $day = str_pad($match[1], 2, '0', STR_PAD_LEFT);
                return date('Y') . '-' . $month . '-' . $day;
            }

            if (preg_match('/\b' . $name . '\s*(\d{1,2})\b/', $normalized, $match)) {
                $day = str_pad($match[1], 2, '0', STR_PAD_LEFT);
                return date('Y') . '-' . $month . '-' . $day;
            }
        }

        if (preg_match('/\bdia\s+(\d{1,2})\b/', $normalized, $match)) {
            $day = str_pad($match[1], 2, '0', STR_PAD_LEFT);
            return date('Y-m-') . $day;
        }

        return null;
    }
}
