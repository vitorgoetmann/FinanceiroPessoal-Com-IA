<?php

class Reports extends Controller {
    
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

        // Gastos por categoria
        $expensesByCategory = $this->transactionModel->getCategoryTotals($userId, 'expense', $month, $year);
        
        // Receitas por categoria
        $incomesByCategory = $this->transactionModel->getCategoryTotals($userId, 'income', $month, $year);
        
        // Gastos por conta
        $expensesByAccount = $this->transactionModel->getAccountTotals($userId, 'expense', $month, $year);
        
        // Evolução mensal (últimos 12 meses)
        $monthlyEvolution = $this->transactionModel->getMonthlyEvolution($userId, 12);
        
        // Totais
        $totalIncome = $this->transactionModel->getMonthlyTotal($userId, 'income', $month, $year);
        $totalExpense = $this->transactionModel->getMonthlyTotal($userId, 'expense', $month, $year);
        
        $data = [
            'month' => $month,
            'year' => $year,
            'expensesByCategory' => $expensesByCategory,
            'incomesByCategory' => $incomesByCategory,
            'expensesByAccount' => $expensesByAccount,
            'monthlyEvolution' => $monthlyEvolution,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense
        ];

        $this->view('reports/index', $data);
    }

    public function export() {
        $userId = $this->getUserId();
        
        // Pegar filtros
        $filters = [
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'type' => $_GET['type'] ?? null,
            'category_id' => $_GET['category_id'] ?? null,
            'account_id' => $_GET['account_id'] ?? null
        ];

        // Buscar transações filtradas
        $transactions = $this->transactionModel->getUserTransactions($userId, $filters);
        
        // Buscar todas as contas e categorias para os filtros
        $accounts = $this->accountModel->getUserAccounts($userId);
        $categories = $this->categoryModel->getUserCategories($userId);

        $data = [
            'transactions' => $transactions,
            'accounts' => $accounts,
            'categories' => $categories,
            'filters' => $filters
        ];

        $this->view('reports/export', $data);
    }

    public function exportExcel() {
        $userId = $this->getUserId();
        
        // Pegar filtros
        $filters = [
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'type' => $_GET['type'] ?? null,
            'category_id' => $_GET['category_id'] ?? null,
            'account_id' => $_GET['account_id'] ?? null
        ];

        // Buscar transações filtradas
        $transactions = $this->transactionModel->getUserTransactions($userId, $filters);

        // Nome do arquivo
        $filename = 'relatorio_' . date('Y-m-d_His') . '.csv';

        // Headers para download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Abrir output
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8 (para Excel abrir corretamente)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Título do relatório
        fputcsv($output, ['RELATÓRIO FINANCEIRO - CONTROLE FINANCEIRO'], ';');
        fputcsv($output, ['Gerado em: ' . date('d/m/Y H:i:s')], ';');
        
        // Filtros aplicados
        if ($filters['start_date'] && $filters['end_date']) {
            fputcsv($output, ['Período: ' . date('d/m/Y', strtotime($filters['start_date'])) . ' até ' . date('d/m/Y', strtotime($filters['end_date']))], ';');
        }
        if ($filters['type']) {
            fputcsv($output, ['Tipo: ' . ($filters['type'] === 'income' ? 'Receitas' : 'Despesas')], ';');
        }
        
        // Linha vazia para separação
        fputcsv($output, [], ';');

        // Cabeçalho da tabela
        fputcsv($output, [
            'Data',
            'Descrição',
            'Categoria',
            'Conta/Cartão',
            'Tipo',
            'Valor',
            'Status',
            'Parcela',
            'Observações'
        ], ';');

        // Calcular totais
        $totalIncome = 0;
        $totalExpense = 0;
        
        // Dados
        foreach ($transactions as $transaction) {
            // Acumular totais
            if ($transaction['type'] === 'income') {
                $totalIncome += $transaction['amount'];
            } else {
                $totalExpense += $transaction['amount'];
            }
            
            $installmentInfo = '';
            if ($transaction['installments'] > 1) {
                $installmentInfo = $transaction['installment_number'] . '/' . $transaction['installments'];
            }

            fputcsv($output, [
                date('d/m/Y', strtotime($transaction['transaction_date'])),
                $transaction['description'],
                $transaction['category_name'],
                $transaction['account_name'],
                $transaction['type'] === 'income' ? 'Receita' : 'Despesa',
                number_format($transaction['amount'], 2, ',', '.'),
                $this->getStatusLabel($transaction['status']),
                $installmentInfo,
                $transaction['notes'] ?? ''
            ], ';');
        }
        
        // Adicionar linhas vazias para separar
        fputcsv($output, [], ';');
        fputcsv($output, [], ';');
        
        // Totais
        fputcsv($output, [
            'Total de Receitas',
            'R$ ' . number_format($totalIncome, 2, ',', '.')
        ], ';');
        
        fputcsv($output, [
            'Total de Despesas',
            'R$ ' . number_format($totalExpense, 2, ',', '.')
        ], ';');
        
        fputcsv($output, [
            'Saldo',
            'R$ ' . number_format($totalIncome - $totalExpense, 2, ',', '.')
        ], ';');

        fclose($output);
        exit;
    }

    public function exportPdf() {
        $userId = $this->getUserId();
        
        // Pegar filtros
        $filters = [
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'type' => $_GET['type'] ?? null,
            'category_id' => $_GET['category_id'] ?? null,
            'account_id' => $_GET['account_id'] ?? null
        ];

        // Buscar transações filtradas
        $transactions = $this->transactionModel->getUserTransactions($userId, $filters);

        // Calcular totais
        $totalIncome = 0;
        $totalExpense = 0;
        foreach ($transactions as $transaction) {
            if ($transaction['type'] === 'income') {
                $totalIncome += $transaction['amount'];
            } else {
                $totalExpense += $transaction['amount'];
            }
        }

        // Gerar HTML para impressão/PDF
        $html = $this->generatePdfHtml($transactions, $filters, $totalIncome, $totalExpense);

        // Retornar HTML com script de auto-impressão
        echo $html;
        exit;
    }

    private function generatePdfHtml($transactions, $filters, $totalIncome, $totalExpense) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Relatório Financeiro</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: Arial, sans-serif; 
                    font-size: 12px; 
                    padding: 20px;
                    background: white;
                    color: #000;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 30px; 
                    border-bottom: 3px solid #006064;
                    padding-bottom: 15px;
                }
                .header h1 { 
                    margin: 0 0 10px 0; 
                    color: #006064; 
                    font-size: 28px;
                }
                .header h2 { 
                    margin: 5px 0; 
                    color: #444; 
                    font-size: 20px;
                    font-weight: normal;
                }
                .header p { 
                    margin-top: 8px; 
                    color: #666; 
                    font-size: 11px;
                }
                .filters { 
                    background: #f5f5f5; 
                    padding: 12px 15px; 
                    margin-bottom: 25px; 
                    border-radius: 5px;
                    border: 1px solid #ddd;
                }
                .filters strong { 
                    display: block; 
                    margin-bottom: 8px; 
                    color: #333;
                }
                .filters p { 
                    margin: 3px 0; 
                    color: #555;
                    font-size: 11px;
                }
                table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    margin-bottom: 25px;
                    border: 1px solid #ddd;
                }
                thead tr { 
                    background: #006064 !important;
                }
                th { 
                    background: #006064; 
                    color: white !important; 
                    padding: 12px 10px; 
                    text-align: left;
                    font-weight: 600;
                    border: 1px solid #004d54;
                }
                td { 
                    padding: 10px; 
                    border: 1px solid #ddd;
                    color: #333;
                }
                tbody tr:nth-child(even) { 
                    background: #f9f9f9; 
                }
                tbody tr:nth-child(odd) { 
                    background: white; 
                }
                .income { 
                    color: #2E7D32 !important; 
                    font-weight: bold; 
                }
                .expense { 
                    color: #D32F2F !important; 
                    font-weight: bold; 
                }
                .totals { 
                    background: #f5f5f5; 
                    padding: 20px; 
                    border-radius: 5px;
                    border: 2px solid #006064;
                    margin-top: 20px;
                }
                .totals-grid { 
                    display: table;
                    width: 100%;
                }
                .total-item { 
                    display: table-cell;
                    text-align: center; 
                    padding: 10px;
                    vertical-align: top;
                }
                .total-label { 
                    font-size: 12px; 
                    color: #666; 
                    margin-bottom: 8px;
                    font-weight: 600;
                }
                .total-value { 
                    font-size: 20px; 
                    font-weight: bold; 
                    margin-top: 5px;
                }
                @media print {
                    body { 
                        print-color-adjust: exact; 
                        -webkit-print-color-adjust: exact;
                        padding: 10px;
                    }
                    .header { page-break-after: avoid; }
                    table { page-break-inside: avoid; }
                    .totals { page-break-before: avoid; }
                    thead { display: table-header-group; }
                    a { text-decoration: none; color: inherit; }
                    a[href]:after { content: none !important; }
                }
            </style>
            <script>
                window.onload = function() {
                    // Aguardar um momento para garantir que o conteúdo foi renderizado
                    setTimeout(function() {
                        window.print();
                    }, 500);
                };
            </script>
        </head>
        <body>
            <div class="header">
                <h1>Controle Financeiro</h1>
                <h2>Relatório Financeiro</h2>
                <p>Gerado em: <?= date('d/m/Y H:i:s') ?></p>
            </div>

            <div class="filters">
                <strong>Filtros Aplicados:</strong>
                <?php if ($filters['start_date'] && $filters['end_date']): ?>
                    <p>Período: <?= date('d/m/Y', strtotime($filters['start_date'])) ?> até <?= date('d/m/Y', strtotime($filters['end_date'])) ?></p>
                <?php endif; ?>
                <?php if ($filters['type']): ?>
                    <p>Tipo: <?= $filters['type'] === 'income' ? 'Receitas' : 'Despesas' ?></p>
                <?php endif; ?>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 12%;">Data</th>
                        <th style="width: 28%;">Descrição</th>
                        <th style="width: 20%;">Categoria</th>
                        <th style="width: 18%;">Conta</th>
                        <th style="width: 10%;">Tipo</th>
                        <th style="width: 12%; text-align: right;">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($transaction['transaction_date'])) ?></td>
                            <td><?= htmlspecialchars($transaction['description']) ?></td>
                            <td><?= htmlspecialchars($transaction['category_name']) ?></td>
                            <td><?= htmlspecialchars($transaction['account_name']) ?></td>
                            <td><?= $transaction['type'] === 'income' ? 'Receita' : 'Despesa' ?></td>
                            <td style="text-align: right;" class="<?= $transaction['type'] ?>">
                                R$ <?= number_format($transaction['amount'], 2, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="totals">
                <div class="totals-grid">
                    <div class="total-item">
                        <div class="total-label">Total de Receitas</div>
                        <div class="total-value income">R$ <?= number_format($totalIncome, 2, ',', '.') ?></div>
                    </div>
                    <div class="total-item">
                        <div class="total-label">Total de Despesas</div>
                        <div class="total-value expense">R$ <?= number_format($totalExpense, 2, ',', '.') ?></div>
                    </div>
                    <div class="total-item">
                        <div class="total-label">Saldo</div>
                        <div class="total-value" style="color: <?= ($totalIncome - $totalExpense) >= 0 ? '#2E7D32' : '#D32F2F' ?>">
                            R$ <?= number_format($totalIncome - $totalExpense, 2, ',', '.') ?>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    private function getStatusLabel($status) {
        $labels = [
            'paid' => 'Pago',
            'pending' => 'Pendente',
            'scheduled' => 'Agendado'
        ];
        return $labels[$status] ?? $status;
    }
}

