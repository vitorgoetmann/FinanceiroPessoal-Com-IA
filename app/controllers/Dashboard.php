<?php

class Dashboard extends Controller {
    
    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->transactionModel = new Transaction($this->db);
        $this->accountModel = new Account($this->db);
        $this->categoryModel = new Category($this->db);
    }

    public function index() {
        $userId = $this->getUserId();
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        $accountFilter = $_GET['account_id'] ?? '';

        // Dados do mês atual
        $currentMonthIncome = $this->transactionModel->getMonthlyTotal($userId, 'income', $month, $year, $accountFilter);
        $currentMonthExpense = $this->transactionModel->getMonthlyTotal($userId, 'expense', $month, $year, $accountFilter);
        $balance = $currentMonthIncome - $currentMonthExpense;

        // Dados do mês anterior
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }
        $prevMonthIncome = $this->transactionModel->getMonthlyTotal($userId, 'income', $prevMonth, $prevYear, $accountFilter);
        $prevMonthExpense = $this->transactionModel->getMonthlyTotal($userId, 'expense', $prevMonth, $prevYear, $accountFilter);

        // Calcular percentuais de variação
        $incomeChange = $prevMonthIncome > 0 ? (($currentMonthIncome - $prevMonthIncome) / $prevMonthIncome) * 100 : 0;
        $expenseChange = $prevMonthExpense > 0 ? (($currentMonthExpense - $prevMonthExpense) / $prevMonthExpense) * 100 : 0;

        // Transações recentes
        $filters = [
            'start_date' => date('Y-m-01', strtotime("$year-$month-01")),
            'end_date' => date('Y-m-t', strtotime("$year-$month-01")),
            'limit' => 10
        ];
        if ($accountFilter) {
            $filters['account_id'] = $accountFilter;
        }
        $recentTransactions = $this->transactionModel->getUserTransactions($userId, $filters);

        // Gastos por categoria
        $expensesByCategory = $this->transactionModel->getCategoryTotals($userId, 'expense', $month, $year, $accountFilter);

        // Gastos por conta
        $expensesByAccount = $this->transactionModel->getAccountTotals($userId, 'expense', $month, $year);

        // Evolução mensal
        $monthlyEvolution = $this->transactionModel->getMonthlyEvolution($userId, 6);

        // Contas do usuário
        $accounts = $this->accountModel->getUserAccounts($userId);
        
        // Se tem filtro de conta, mostrar o saldo apenas daquela conta
        if ($accountFilter) {
            $accountData = $this->accountModel->findById($accountFilter);
            $totalBalance = $accountData ? $accountData['balance'] : 0;
        } else {
            $totalBalance = $this->accountModel->getTotalBalance($userId);
        }

        $data = [
            'currentMonth' => $month,
            'currentYear' => $year,
            'accountFilter' => $accountFilter,
            'currentMonthIncome' => $currentMonthIncome,
            'currentMonthExpense' => $currentMonthExpense,
            'balance' => $balance,
            'incomeChange' => $incomeChange,
            'expenseChange' => $expenseChange,
            'recentTransactions' => $recentTransactions,
            'expensesByCategory' => $expensesByCategory,
            'expensesByAccount' => $expensesByAccount,
            'monthlyEvolution' => $monthlyEvolution,
            'accounts' => $accounts,
            'totalBalance' => $totalBalance
        ];

        $this->view('dashboard/index', $data);
    }
}
