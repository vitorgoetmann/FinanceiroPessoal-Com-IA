<?php 
$activeMenu = 'accounts';
$pageTitle = 'Contas e Cartões';
ob_start(); 
?>

<div class="page-header">
    <a href="<?= BASE_URL ?>accounts/new" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Nova Conta
    </a>
</div>

<div class="dashboard-card">
    <div class="card-header">
        <h3><i class="fas fa-credit-card"></i> Minhas Contas e Cartões</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($accounts)): ?>
            <div class="accounts-grid">
                <?php foreach ($accounts as $account): ?>
                    <div class="account-card" style="border-left: 4px solid <?= $account['color'] ?>">
                        <div class="account-header">
                            <div>
                                <h3><?= htmlspecialchars($account['name']) ?></h3>
                                <span class="badge <?= $account['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $account['is_active'] ? 'Ativa' : 'Inativa' ?>
                                </span>
                                <span class="badge" style="background: rgba(25, 118, 210, 0.1); color: var(--primary);">
                                    <?= ucfirst(str_replace('_', ' ', $account['type'])) ?>
                                </span>
                            </div>
                            <div class="account-actions">
                                <a href="<?= BASE_URL ?>accounts/edit/<?= $account['id'] ?>" class="btn-icon" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteAccount(<?= $account['id'] ?>)" class="btn-icon btn-danger" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="account-balance">
                            <small>Saldo</small>
                            <div class="amount">R$ <?= number_format($account['balance'], 2, ',', '.') ?></div>
                        </div>

                        <?php if ($account['credit_limit'] || $account['closing_day']): ?>
                        <div class="account-details">
                            <?php if ($account['credit_limit']): ?>
                            <div class="detail-item">
                                <i class="fas fa-credit-card"></i>
                                <span>Limite: R$ <?= number_format($account['credit_limit'], 2, ',', '.') ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($account['closing_day']): ?>
                            <div class="detail-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Fechamento: Dia <?= $account['closing_day'] ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-credit-card"></i>
                <p>Nenhuma conta cadastrada</p>
                <a href="<?= BASE_URL ?>accounts/new" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Criar primeira conta
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.page-header {
    margin-bottom: 24px;
    display: flex;
    justify-content: flex-end;
}

.accounts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.account-card {
    background: var(--bg-secondary);
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.account-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.account-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.account-header h3 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
    font-size: 1.1rem;
}

.account-actions {
    display: flex;
    gap: 0.25rem;
}

.account-balance {
    margin-top: 1rem;
    padding: 1rem;
    background: var(--bg-tertiary);
    border-radius: 8px;
}

.account-balance small {
    color: var(--text-secondary);
    font-size: 0.85rem;
    display: block;
    margin-bottom: 0.25rem;
}

.account-balance .amount {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
}

.account-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.badge-success {
    background-color: rgba(76, 175, 80, 0.1);
    color: var(--success);
}

.badge-danger {
    background-color: rgba(244, 67, 54, 0.1);
    color: var(--danger);
}

.btn-icon {
    background: none;
    border: none;
    cursor: pointer;
    padding: 6px;
    color: var(--text-secondary);
    transition: color 0.3s;
}

.btn-icon:hover {
    color: var(--primary);
}

.btn-icon.btn-danger:hover {
    color: var(--danger);
}


</style>

<script>
function deleteAccount(id) {
    if (confirm('Tem certeza que deseja excluir esta conta?')) {
        fetch(`<?= BASE_URL ?>accounts/delete/${id}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao excluir conta');
            }
        });
    }
}
</script>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
