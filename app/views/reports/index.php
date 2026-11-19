<?php 
$activeMenu = 'reports';
$pageTitle = 'Relatórios';
ob_start(); 
?>

<div class="reports-header">
    <form method="GET" action="<?= BASE_URL ?>reports" class="period-selector">
        <select name="month" onchange="this.form.submit()">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= $month == $m ? 'selected' : '' ?>>
                    <?= strftime('%B', mktime(0, 0, 0, $m, 1)) ?>
                </option>
            <?php endfor; ?>
        </select>
        
        <select name="year" onchange="this.form.submit()">
            <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
    </form>
    
    <a href="<?= BASE_URL ?>reports/export" class="btn btn-primary">
        <i class="fas fa-file-export"></i>
        Exportar Relatório
    </a>
</div>

<div class="summary-cards">
    <div class="card card-income">
        <div class="card-icon">
            <i class="fas fa-arrow-up"></i>
        </div>
        <div class="card-content">
            <h3>Total de Receitas</h3>
            <p class="card-value">R$ <?= number_format($totalIncome, 2, ',', '.') ?></p>
        </div>
    </div>

    <div class="card card-expense">
        <div class="card-icon">
            <i class="fas fa-arrow-down"></i>
        </div>
        <div class="card-content">
            <h3>Total de Despesas</h3>
            <p class="card-value">R$ <?= number_format($totalExpense, 2, ',', '.') ?></p>
        </div>
    </div>

    <div class="card card-balance">
        <div class="card-icon">
            <i class="fas fa-equals"></i>
        </div>
        <div class="card-content">
            <h3>Saldo do Período</h3>
            <p class="card-value <?= ($totalIncome - $totalExpense) >= 0 ? 'positive' : 'negative' ?>">
                R$ <?= number_format($totalIncome - $totalExpense, 2, ',', '.') ?>
            </p>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Despesas por Categoria -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie"></i> Despesas por Categoria</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($expensesByCategory)): ?>
                <canvas id="expenseCategoryChart" height="250"></canvas>
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
                    <p>Nenhuma despesa no período</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Receitas por Categoria -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie"></i> Receitas por Categoria</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($incomesByCategory)): ?>
                <canvas id="incomeCategoryChart" height="250"></canvas>
                <div class="category-legend">
                    <?php foreach ($incomesByCategory as $cat): ?>
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
                    <p>Nenhuma receita no período</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Evolução Mensal -->
    <div class="dashboard-card full-width">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> Evolução dos Últimos 12 Meses</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($monthlyEvolution)): ?>
                <canvas id="evolutionChart" height="300"></canvas>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-chart-line"></i>
                    <p>Dados insuficientes</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Despesas por Conta -->
    <div class="dashboard-card full-width">
        <div class="card-header">
            <h3><i class="fas fa-credit-card"></i> Despesas por Conta</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($expensesByAccount)): ?>
                <canvas id="accountChart" height="200"></canvas>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-credit-card"></i>
                    <p>Nenhuma despesa no período</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.reports-header {
    margin-bottom: 24px;
}

.period-selector {
    display: flex;
    gap: 12px;
}

.period-selector select {
    padding: 10px 16px;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    font-size: 14px;
    background-color: white;
}
</style>

<script>
const expenseCategoryData = <?= json_encode($expensesByCategory) ?>;
const incomeCategoryData = <?= json_encode($incomesByCategory) ?>;
const expenseAccountData = <?= json_encode($expensesByAccount) ?>;
const evolutionData = <?= json_encode($monthlyEvolution) ?>;

// Destruir gráficos existentes
let expenseChart = null;
let incomeChart = null;
let evolutionChartReport = null;
let accountChart = null;

// Gráfico de Despesas por Categoria
<?php if (!empty($expensesByCategory)): ?>
const expenseCtx = document.getElementById('expenseCategoryChart');
if (expenseCtx) {
    if (expenseChart) expenseChart.destroy();
    expenseChart = new Chart(expenseCtx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: expenseCategoryData.map(c => c.name),
            datasets: [{
                data: expenseCategoryData.map(c => c.total),
                backgroundColor: expenseCategoryData.map(c => c.color),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1.5,
            plugins: { legend: { display: false } }
        }
    });
}
<?php endif; ?>

// Gráfico de Receitas por Categoria
<?php if (!empty($incomesByCategory)): ?>
const incomeCtx = document.getElementById('incomeCategoryChart');
if (incomeCtx) {
    if (incomeChart) incomeChart.destroy();
    incomeChart = new Chart(incomeCtx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: incomeCategoryData.map(c => c.name),
            datasets: [{
                data: incomeCategoryData.map(c => c.total),
                backgroundColor: incomeCategoryData.map(c => c.color),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1.5,
            plugins: { legend: { display: false } }
        }
    });
}
<?php endif; ?>

// Gráfico de Evolução
<?php if (!empty($monthlyEvolution)): ?>
const evolutionReportCtx = document.getElementById('evolutionChart');
if (evolutionReportCtx) {
    if (evolutionChartReport) evolutionChartReport.destroy();
    evolutionChartReport = new Chart(evolutionReportCtx.getContext('2d'), {
        type: 'line',
        data: {
            labels: evolutionData.map(e => e.month),
            datasets: [
                {
                    label: 'Receitas',
                    data: evolutionData.map(e => e.income),
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Despesas',
                    data: evolutionData.map(e => e.expense),
                    borderColor: '#f44336',
                    backgroundColor: 'rgba(244, 67, 54, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 2.5,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true } }
        }
    });
}
<?php endif; ?>

// Gráfico de Contas
<?php if (!empty($expensesByAccount)): ?>
const accountCtx = document.getElementById('accountChart');
if (accountCtx) {
    if (accountChart) accountChart.destroy();
    accountChart = new Chart(accountCtx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: expenseAccountData.map(a => a.name),
            datasets: [{
                label: 'Despesas',
                data: expenseAccountData.map(a => a.total),
                backgroundColor: '#00897B',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 3,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
}
<?php endif; ?>
</script>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
