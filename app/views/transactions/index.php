<?php 
$activeMenu = 'transactions';
$pageTitle = 'Transações';
ob_start(); 
?>

<div class="transactions-header">
    <div class="filters-bar">
        <form method="GET" action="<?= BASE_URL ?>transactions" class="filters-form">
            <input type="date" name="start_date" value="<?= $filters['start_date'] ?>">
            <input type="date" name="end_date" value="<?= $filters['end_date'] ?>">
            
            <select name="type">
                <option value="">Todos os tipos</option>
                <option value="income" <?= $filters['type'] === 'income' ? 'selected' : '' ?>>Receitas</option>
                <option value="expense" <?= $filters['type'] === 'expense' ? 'selected' : '' ?>>Despesas</option>
            </select>
            
            <select name="category_id">
                <option value="">Todas as categorias</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $filters['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                        <?= $cat['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="account_id">
                <option value="">Todas as contas</option>
                <?php foreach ($accounts as $acc): ?>
                    <option value="<?= $acc['id'] ?>" <?= $filters['account_id'] == $acc['id'] ? 'selected' : '' ?>>
                        <?= $acc['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn btn-secondary btn-sm">
                <i class="fas fa-filter"></i>
                Filtrar
            </button>
        </form>
    </div>
    
    <a href="<?= BASE_URL ?>transactions/new" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Nova Transação
    </a>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <?php if (!empty($transactions)): ?>
            <div class="transactions-list">
                <?php foreach ($transactions as $trans): ?>
                    <div class="transaction-item">
                        <div class="transaction-icon" style="background-color: <?= $trans['category_color'] ?>20">
                            <i class="<?= $trans['category_icon'] ?>" style="color: <?= $trans['category_color'] ?>"></i>
                        </div>
                        <div class="transaction-details">
                            <strong><?= $trans['description'] ?></strong>
                            <small>
                                <?= $trans['category_name'] ?> • <?= $trans['account_name'] ?>
                                <?php if ($trans['installments'] > 1): ?>
                                    • Parcela <?= $trans['installment_number'] ?>/<?= $trans['installments'] ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <div class="transaction-date">
                            <?= date('d/m/Y', strtotime($trans['transaction_date'])) ?>
                        </div>
                        <div class="transaction-amount <?= $trans['type'] === 'income' ? 'income' : 'expense' ?>">
                            <?= $trans['type'] === 'income' ? '+' : '-' ?> R$ <?= number_format($trans['amount'], 2, ',', '.') ?>
                        </div>
                        <div class="transaction-actions">
                            <a href="<?= BASE_URL ?>transactions/edit/<?= $trans['id'] ?>" class="btn-icon btn-edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="deleteTransaction(<?= $trans['id'] ?>)" class="btn-icon btn-delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-receipt"></i>
                <p>Nenhuma transação encontrada</p>
                <a href="<?= BASE_URL ?>transactions/new" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Nova Transação
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.transactions-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.filters-bar {
    flex: 1;
}

.filters-form {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.filters-form input,
.filters-form select {
    padding: 8px 12px;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    font-size: 14px;
}

.filters-form input[type="date"] {
    min-width: 150px;
}

.filters-form select {
    min-width: 180px;
}

.transaction-actions {
    display: flex;
    gap: 4px;
}

@media (max-width: 768px) {
    .transactions-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filters-form {
        flex-direction: column;
    }
    
    .filters-form input,
    .filters-form select {
        width: 100%;
    }
}
</style>

<script>
function deleteTransaction(id) {
    if (confirm('Tem certeza que deseja excluir esta transação?')) {
        fetch(`<?= BASE_URL ?>transactions/delete/${id}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao excluir transação');
            }
        });
    }
}
</script>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
