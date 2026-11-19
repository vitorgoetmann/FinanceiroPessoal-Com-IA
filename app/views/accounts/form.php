<?php 
$activeMenu = 'accounts';
$pageTitle = $account ? 'Editar Conta' : 'Nova Conta';
ob_start(); 
?>

<div class="dashboard-card">
    <div class="card-header">
        <h3>
            <i class="fas fa-credit-card"></i>
            <?= $account ? 'Editar Conta' : 'Nova Conta' ?>
        </h3>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>accounts/<?= $account ? 'update/' . $account['id'] : 'create' ?>">
            <div class="form-row">
                <div class="form-group col-md-8">
                    <label for="name">Nome da Conta *</label>
                    <input type="text" id="name" name="name" value="<?= $account['name'] ?? '' ?>" required>
                </div>
                
                <div class="form-group col-md-4">
                    <label for="color">Cor</label>
                    <input type="color" id="color" name="color" class="color-picker" value="<?= $account['color'] ?? '' ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="type">Tipo *</label>
                    <select id="type" name="type" required onchange="toggleCardFields()">
                        <option value="bank_account" <?= ($account['type'] ?? '') === 'bank_account' ? 'selected' : '' ?>>Conta Bancária</option>
                        <option value="credit_card" <?= ($account['type'] ?? '') === 'credit_card' ? 'selected' : '' ?>>Cartão de Crédito</option>
                        <option value="debit_card" <?= ($account['type'] ?? '') === 'debit_card' ? 'selected' : '' ?>>Cartão de Débito</option>
                        <option value="cash" <?= ($account['type'] ?? '') === 'cash' ? 'selected' : '' ?>>Dinheiro</option>
                        <option value="meal_voucher" <?= ($account['type'] ?? '') === 'meal_voucher' ? 'selected' : '' ?>>Vale Refeição</option>
                        <option value="food_voucher" <?= ($account['type'] ?? '') === 'food_voucher' ? 'selected' : '' ?>>Vale Alimentação</option>
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label for="balance">Saldo Atual *</label>
                    <input type="text" id="balance" name="balance" value="<?= isset($account['balance']) ? number_format($account['balance'], 2, ',', '.') : '0,00' ?>" required>
                </div>
            </div>

            <div id="card-fields" style="display: none;">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="credit_limit">Limite</label>
                        <input type="text" id="credit_limit" name="credit_limit" value="<?= isset($account['credit_limit']) ? number_format($account['credit_limit'], 2, ',', '.') : '' ?>">
                    </div>

                    <div class="form-group col-md-4">
                        <label for="closing_day">Dia de Fechamento</label>
                        <input type="number" id="closing_day" name="closing_day" min="1" max="31" value="<?= $account['closing_day'] ?? '' ?>">
                    </div>

                    <div class="form-group col-md-4">
                        <label for="due_day">Dia de Vencimento</label>
                        <input type="number" id="due_day" name="due_day" min="1" max="31" value="<?= $account['due_day'] ?? '' ?>">
                    </div>
                </div>
            </div>

            <?php if ($account): ?>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" <?= $account['is_active'] ? 'checked' : '' ?>>
                        Conta Ativa
                    </label>
                </div>
            <?php endif; ?>

            <div class="form-actions">
                <a href="<?= BASE_URL ?>accounts" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 0;
}

.col-md-4 {
    flex: 0 0 calc(33.33% - 14px);
}

.col-md-6 {
    flex: 0 0 calc(50% - 10px);
}

.col-md-8 {
    flex: 0 0 calc(66.66% - 10px);
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid var(--border-color);
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }
    
    .col-md-4, .col-md-6, .col-md-8 {
        flex: 1 0 100%;
    }
}
</style>

<script>
function toggleCardFields() {
    const type = document.getElementById('type').value;
    const cardFields = document.getElementById('card-fields');
    
    if (type === 'credit_card') {
        cardFields.style.display = 'block';
    } else {
        cardFields.style.display = 'none';
    }
}

// Verificar ao carregar
toggleCardFields();

// Máscaras de input
document.getElementById('balance').addEventListener('input', function() {
    maskCurrency(this);
});

document.getElementById('credit_limit').addEventListener('input', function() {
    maskCurrency(this);
});
</script>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
