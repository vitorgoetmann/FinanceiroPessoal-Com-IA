<?php 
$activeMenu = 'reports';
$pageTitle = 'Exportar Relatório';
ob_start(); 
?>

<div class="export-container">
    <div class="form-header">
        <h2>
            <i class="fas fa-file-export"></i>
            Exportar Relatório
        </h2>
        <a href="<?= BASE_URL ?>reports" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Voltar
        </a>
    </div>

    <form method="GET" action="#" id="exportForm" class="form-card">
        <div class="form-section">
            <h3>
                <i class="fas fa-filter"></i>
                Filtros
            </h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">Data Inicial *</label>
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           class="form-control" 
                           value="<?= $filters['start_date'] ?? '' ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="end_date">Data Final *</label>
                    <input type="date" 
                           id="end_date" 
                           name="end_date" 
                           class="form-control" 
                           value="<?= $filters['end_date'] ?? '' ?>"
                           required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="type">Tipo</label>
                    <select id="type" name="type" class="form-control">
                        <option value="">Todos (Receitas e Despesas)</option>
                        <option value="income" <?= ($filters['type'] ?? '') === 'income' ? 'selected' : '' ?>>Apenas Receitas</option>
                        <option value="expense" <?= ($filters['type'] ?? '') === 'expense' ? 'selected' : '' ?>>Apenas Despesas</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="account_id">Conta/Cartão</label>
                    <select id="account_id" name="account_id" class="form-control">
                        <option value="">Todas as contas</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?= $account['id'] ?>" 
                                    <?= ($filters['account_id'] ?? '') == $account['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($account['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="category_id">Categoria</label>
                <select id="category_id" name="category_id" class="form-control">
                    <option value="">Todas as categorias</option>
                    <?php 
                    $incomeCategories = array_filter($categories, fn($c) => $c['type'] === 'income');
                    $expenseCategories = array_filter($categories, fn($c) => $c['type'] === 'expense');
                    ?>
                    <?php if (!empty($incomeCategories)): ?>
                        <optgroup label="Receitas">
                            <?php foreach ($incomeCategories as $category): ?>
                                <option value="<?= $category['id'] ?>" 
                                        <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                    <?php if (!empty($expenseCategories)): ?>
                        <optgroup label="Despesas">
                            <?php foreach ($expenseCategories as $category): ?>
                                <option value="<?= $category['id'] ?>" 
                                        <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="button-actions">
            <button type="button" onclick="applyFilters()" class="btn btn-primary">
                <i class="fas fa-filter"></i>
                Aplicar Filtros
            </button>
        </div>
    </form>

    <?php if (!empty($transactions)): ?>
        <div class="preview-section">
            <div class="preview-header">
                <h3>
                    <i class="fas fa-eye"></i>
                    Pré-visualização (<?= count($transactions) ?> transações)
                </h3>
                <div class="export-buttons">
                    <button onclick="exportExcel()" class="btn btn-success">
                        <i class="fas fa-file-excel"></i>
                        Exportar Excel (CSV)
                    </button>
                    <button onclick="exportPdf()" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i>
                        Exportar PDF
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th>Categoria</th>
                            <th>Conta</th>
                            <th>Tipo</th>
                            <th class="text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalIncome = 0;
                        $totalExpense = 0;
                        foreach ($transactions as $transaction): 
                            if ($transaction['type'] === 'income') {
                                $totalIncome += $transaction['amount'];
                            } else {
                                $totalExpense += $transaction['amount'];
                            }
                        ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($transaction['transaction_date'])) ?></td>
                                <td><?= htmlspecialchars($transaction['description']) ?></td>
                                <td>
                                    <span class="category-badge" style="background-color: <?= $transaction['category_color'] ?>20; color: <?= $transaction['category_color'] ?>">
                                        <i class="<?= $transaction['category_icon'] ?>"></i>
                                        <?= htmlspecialchars($transaction['category_name']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($transaction['account_name']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $transaction['type'] ?>">
                                        <?= $transaction['type'] === 'income' ? 'Receita' : 'Despesa' ?>
                                    </span>
                                </td>
                                <td class="text-right <?= $transaction['type'] === 'income' ? 'text-success' : 'text-danger' ?>">
                                    R$ <?= number_format($transaction['amount'], 2, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="totals-row">
                            <td colspan="5"><strong>Total de Receitas:</strong></td>
                            <td class="text-right text-success"><strong>R$ <?= number_format($totalIncome, 2, ',', '.') ?></strong></td>
                        </tr>
                        <tr class="totals-row">
                            <td colspan="5"><strong>Total de Despesas:</strong></td>
                            <td class="text-right text-danger"><strong>R$ <?= number_format($totalExpense, 2, ',', '.') ?></strong></td>
                        </tr>
                        <tr class="totals-row balance-row">
                            <td colspan="5"><strong>Saldo:</strong></td>
                            <td class="text-right" style="color: <?= ($totalIncome - $totalExpense) >= 0 ? '#2E7D32' : '#D32F2F' ?>">
                                <strong>R$ <?= number_format($totalIncome - $totalExpense, 2, ',', '.') ?></strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h3>Selecione os filtros</h3>
            <p>Escolha o período e outros filtros para visualizar as transações e exportar o relatório.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function applyFilters() {
    const form = document.getElementById('exportForm');
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;

    if (!startDate || !endDate) {
        alert('Por favor, selecione o período (data inicial e final)');
        return;
    }

    // Constrói URL com filtros
    let url = BASE_URL + 'reports/export?';
    url += 'start_date=' + startDate;
    url += '&end_date=' + endDate;
    
    const type = document.getElementById('type').value;
    if (type) url += '&type=' + type;
    
    const accountId = document.getElementById('account_id').value;
    if (accountId) url += '&account_id=' + accountId;
    
    const categoryId = document.getElementById('category_id').value;
    if (categoryId) url += '&category_id=' + categoryId;

    window.location.href = url;
}

function exportExcel() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    let url = BASE_URL + 'reports/exportExcel?';
    url += 'start_date=' + startDate;
    url += '&end_date=' + endDate;
    
    const type = document.getElementById('type').value;
    if (type) url += '&type=' + type;
    
    const accountId = document.getElementById('account_id').value;
    if (accountId) url += '&account_id=' + accountId;
    
    const categoryId = document.getElementById('category_id').value;
    if (categoryId) url += '&category_id=' + categoryId;

    window.open(url, '_blank');
}

function exportPdf() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    let url = BASE_URL + 'reports/exportPdf?';
    url += 'start_date=' + startDate;
    url += '&end_date=' + endDate;
    
    const type = document.getElementById('type').value;
    if (type) url += '&type=' + type;
    
    const accountId = document.getElementById('account_id').value;
    if (accountId) url += '&account_id=' + accountId;
    
    const categoryId = document.getElementById('category_id').value;
    if (categoryId) url += '&category_id=' + categoryId;

    window.open(url, '_blank');
}
</script>

<style>
.export-container {
    max-width: 1200px;
    margin: 0 auto;
}

.form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.form-header h2 {
    margin: 0;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-card {
    background: var(--card-bg);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.form-section h3 {
    margin: 0 0 1.5rem 0;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border-color);
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--text-primary);
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.2s;
    background: var(--input-bg);
    color: var(--text-primary);
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.button-actions {
    display: flex;
    justify-content: center;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

.preview-section {
    background: var(--card-bg);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border-color);
}

.preview-header h3 {
    margin: 0;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.export-buttons {
    display: flex;
    gap: 1rem;
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: var(--primary-color);
    color: white;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
}

.data-table th.text-right {
    text-align: right;
}

.data-table td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border-color);
}

.data-table tbody tr:hover {
    background: var(--bg-secondary);
}

.category-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 12px;
}

.badge-income {
    background: #E8F5E9;
    color: #2E7D32;
}

.badge-expense {
    background: #FFEBEE;
    color: #D32F2F;
}

.text-right {
    text-align: right;
}

.text-success {
    color: #2E7D32;
    font-weight: 600;
}

.text-danger {
    color: #D32F2F;
    font-weight: 600;
}

.totals-row {
    background: var(--bg-secondary);
    font-size: 1.1rem;
}

.balance-row {
    background: var(--primary-color);
    color: white;
}

.balance-row td {
    border-bottom: none;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }

    .preview-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .export-buttons {
        width: 100%;
        flex-direction: column;
    }

    .export-buttons button {
        width: 100%;
    }
}
</style>

<?php 
$content = ob_get_clean();
include APP_ROOT . '/app/views/layouts/main.php';
?>
