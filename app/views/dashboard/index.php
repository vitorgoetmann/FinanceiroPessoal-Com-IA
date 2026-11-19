<?php 
$activeMenu = 'dashboard';
$pageTitle = 'Dashboard';
ob_start(); 
?>

<!-- Filtros -->
<div class="dashboard-filters">
    <form method="GET" action="<?= BASE_URL ?>dashboard" class="filters-inline">
        <select name="account_id" onchange="this.form.submit()">
            <option value="">Todas as Contas</option>
            <?php foreach ($accounts as $acc): ?>
                <option value="<?= $acc['id'] ?>" <?= $accountFilter == $acc['id'] ? 'selected' : '' ?>>
                    <?= $acc['name'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<!-- Cards de Resumo -->
<div class="summary-cards">
    <div class="card card-income">
        <div class="card-icon">
            <i class="fas fa-arrow-up"></i>
        </div>
        <div class="card-content">
            <h3>Receitas do Mês</h3>
            <p class="card-value">R$ <?= number_format($currentMonthIncome, 2, ',', '.') ?></p>
            <small class="card-change <?= $incomeChange >= 0 ? 'positive' : 'negative' ?>">
                <i class="fas fa-arrow-<?= $incomeChange >= 0 ? 'up' : 'down' ?>"></i>
                <?= number_format(abs($incomeChange), 1) ?>% vs mês anterior
            </small>
        </div>
    </div>

    <div class="card card-expense">
        <div class="card-icon">
            <i class="fas fa-arrow-down"></i>
        </div>
        <div class="card-content">
            <h3>Despesas do Mês</h3>
            <p class="card-value">R$ <?= number_format($currentMonthExpense, 2, ',', '.') ?></p>
            <small class="card-change <?= $expenseChange <= 0 ? 'positive' : 'negative' ?>">
                <i class="fas fa-arrow-<?= $expenseChange >= 0 ? 'up' : 'down' ?>"></i>
                <?= number_format(abs($expenseChange), 1) ?>% vs mês anterior
            </small>
        </div>
    </div>

    <div class="card card-balance">
        <div class="card-icon">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="card-content">
            <h3>Saldo do Mês</h3>
            <p class="card-value <?= $balance >= 0 ? 'positive' : 'negative' ?>">
                R$ <?= number_format($balance, 2, ',', '.') ?>
            </p>
            <small><?= $balance >= 0 ? 'Superávit' : 'Déficit' ?></small>
        </div>
    </div>

    <div class="card card-total">
        <div class="card-icon">
            <i class="fas fa-coins"></i>
        </div>
        <div class="card-content">
            <h3><?= $accountFilter ? 'Saldo da Conta' : 'Saldo Total' ?></h3>
            <p class="card-value">R$ <?= number_format($totalBalance, 2, ',', '.') ?></p>
            <small><?= $accountFilter ? 'Conta selecionada' : 'Em todas as contas' ?></small>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Gráfico de Gastos por Categoria -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie"></i> Gastos por Categoria</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($expensesByCategory)): ?>
                <div style="position: relative; height: 300px;">
                    <canvas id="categoryChart"></canvas>
                </div>
                <div class="category-legend">
                    <?php foreach ($expensesByCategory as $cat): ?>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: <?= $cat['color'] ?>"></span>
                            <span class="legend-label"><?= $cat['name'] ?></span>
                            <span class="legend-value">R$ <?= number_format($cat['total'], 2, ',', '.') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-chart-pie"></i>
                    <p>Nenhum gasto registrado neste mês</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Gráfico de Evolução Mensal -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> Evolução Mensal</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($monthlyEvolution)): ?>
                <div style="position: relative; height: 300px;">
                    <canvas id="evolutionChart"></canvas>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-chart-line"></i>
                    <p>Dados insuficientes para exibir o gráfico</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Transações Recentes -->
    <div class="dashboard-card full-width">
        <div class="card-header">
            <h3><i class="fas fa-history"></i> Transações Recentes</h3>
            <a href="<?= BASE_URL ?>transactions" class="btn btn-sm btn-secondary">Ver todas</a>
        </div>
        <div class="card-body">
            <?php if (!empty($recentTransactions)): ?>
                <div class="transactions-list">
                    <?php foreach ($recentTransactions as $transaction): ?>
                        <div class="transaction-item">
                            <div class="transaction-icon" style="background-color: <?= $transaction['category_color'] ?>20">
                                <i class="<?= $transaction['category_icon'] ?>" style="color: <?= $transaction['category_color'] ?>"></i>
                            </div>
                            <div class="transaction-details">
                                <strong><?= $transaction['description'] ?></strong>
                                <small>
                                    <?= $transaction['category_name'] ?> • <?= $transaction['account_name'] ?>
                                    <?php if ($transaction['installments'] > 1): ?>
                                        • <?= $transaction['installment_number'] ?>/<?= $transaction['installments'] ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <div class="transaction-date">
                                <?= date('d/m/Y', strtotime($transaction['transaction_date'])) ?>
                            </div>
                            <div class="transaction-amount <?= $transaction['type'] === 'income' ? 'income' : 'expense' ?>">
                                <?= $transaction['type'] === 'income' ? '+' : '-' ?> R$ <?= number_format($transaction['amount'], 2, ',', '.') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-receipt"></i>
                    <p>Nenhuma transação registrada</p>
                    <button class="btn btn-primary" onclick="openNewTransactionModal()">
                        <i class="fas fa-plus"></i>
                        Adicionar primeira transação
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Contas e Cartões -->
    <div class="dashboard-card full-width">
        <div class="card-header">
            <h3><i class="fas fa-credit-card"></i> Contas e Cartões</h3>
            <a href="<?= BASE_URL ?>accounts" class="btn btn-sm btn-secondary">Gerenciar</a>
        </div>
        <div class="card-body">
            <?php if (!empty($accounts)): ?>
                <div class="accounts-grid">
                    <?php foreach ($accounts as $account): ?>
                        <div class="account-card" style="border-left: 4px solid <?= $account['color'] ?>">
                            <div class="account-info">
                                <strong><?= $account['name'] ?></strong>
                                <small><?= ucfirst(str_replace('_', ' ', $account['type'])) ?></small>
                            </div>
                            <div class="account-balance">
                                R$ <?= number_format($account['balance'], 2, ',', '.') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-credit-card"></i>
                    <p>Nenhuma conta cadastrada</p>
                    <a href="<?= BASE_URL ?>accounts/new" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Adicionar conta
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Dados para os gráficos
const categoryData = <?= json_encode($expensesByCategory) ?>;
const evolutionData = <?= json_encode($monthlyEvolution) ?>;

// Destruir gráficos existentes antes de criar novos
let categoryChart = null;
let evolutionChart = null;

// Gráfico de Categoria
<?php if (!empty($expensesByCategory)): ?>
const categoryCtx = document.getElementById('categoryChart');
if (categoryCtx) {
    if (categoryChart) categoryChart.destroy();
    categoryChart = new Chart(categoryCtx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: categoryData.map(c => c.name),
            datasets: [{
                data: categoryData.map(c => parseFloat(c.total)),
                backgroundColor: categoryData.map(c => c.color),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': R$ ' + context.parsed.toFixed(2);
                        }
                    }
                }
            }
        }
    });
}
<?php endif; ?>

// Gráfico de Evolução
<?php if (!empty($monthlyEvolution)): ?>
const evolutionCtx = document.getElementById('evolutionChart');
if (evolutionCtx) {
    if (evolutionChart) evolutionChart.destroy();
    evolutionChart = new Chart(evolutionCtx.getContext('2d'), {
        type: 'line',
        data: {
            labels: evolutionData.map(e => {
                const [year, month] = e.month.split('-');
                const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
                return months[parseInt(month) - 1] + '/' + year.substr(2);
            }),
            datasets: [
                {
                    label: 'Receitas',
                    data: evolutionData.map(e => parseFloat(e.income)),
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2
                },
                {
                    label: 'Despesas',
                    data: evolutionData.map(e => parseFloat(e.expense)),
                    borderColor: '#f44336',
                    backgroundColor: 'rgba(244, 67, 54, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': R$ ' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toFixed(0);
                        }
                    }
                }
            }
        }
    });
}
<?php endif; ?>
</script>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
