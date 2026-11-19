<?php 
$activeMenu = 'reserves';
$pageTitle = 'Movimentar Reserva';
ob_start(); 
?>

<div class="form-container">
    <div class="form-header">
        <h2>Movimentar Reserva</h2>
        <a href="<?= BASE_URL ?>reserves" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Voltar
        </a>
    </div>

    <div class="reserve-info-card" style="border-left: 4px solid <?= $reserve['color'] ?>">
        <h3><?= htmlspecialchars($reserve['name']) ?></h3>
        <div class="reserve-details">
            <div class="detail-item">
                <i class="fas fa-credit-card"></i>
                <span><?= htmlspecialchars($reserve['account_name']) ?></span>
            </div>
            <div class="detail-item">
                <i class="fas fa-piggy-bank"></i>
                <span>Guardado: R$ <?= number_format($reserve['current_amount'], 2, ',', '.') ?></span>
            </div>
            <div class="detail-item">
                <i class="fas fa-bullseye"></i>
                <span>Meta: R$ <?= number_format($reserve['target_amount'], 2, ',', '.') ?></span>
            </div>
        </div>
        
        <?php 
            $progress = $reserve['target_amount'] > 0 
                ? min(($reserve['current_amount'] / $reserve['target_amount']) * 100, 100) 
                : 0;
        ?>
        <div class="progress-bar-container">
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $progress ?>%; background-color: <?= $reserve['color'] ?>"></div>
            </div>
            <span class="progress-text"><?= number_format($progress, 1) ?>%</span>
        </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>reserves/transaction/<?= $reserve['id'] ?>" class="form-card">
        <div class="transaction-type-selector">
            <label class="radio-card">
                <input type="radio" name="type" value="deposit" checked>
                <div class="radio-content">
                    <i class="fas fa-arrow-down"></i>
                    <span>Adicionar Dinheiro</span>
                </div>
            </label>
            <label class="radio-card">
                <input type="radio" name="type" value="withdraw">
                <div class="radio-content">
                    <i class="fas fa-arrow-up"></i>
                    <span>Retirar Dinheiro</span>
                </div>
            </label>
        </div>

        <div class="form-group">
            <label for="amount">Valor *</label>
            <input type="number" 
                   id="amount" 
                   name="amount" 
                   class="form-control" 
                   step="0.01"
                   min="0.01"
                   placeholder="0,00"
                   required
                   autofocus>
        </div>

        <div class="form-group">
            <label for="description">Descrição (opcional)</label>
            <textarea id="description" 
                      name="description" 
                      class="form-control" 
                      rows="3"
                      placeholder="Adicione uma observação sobre esta movimentação..."></textarea>
        </div>

        <div class="form-actions">
            <a href="<?= BASE_URL ?>reserves" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check"></i>
                Confirmar
            </button>
        </div>
    </form>

    <?php if (!empty($transactions)): ?>
        <div class="transactions-history">
            <h3>
                <i class="fas fa-history"></i>
                Histórico de Movimentações
            </h3>
            <div class="transactions-list">
                <?php foreach ($transactions as $transaction): ?>
                    <div class="transaction-item <?= $transaction['type'] ?>">
                        <div class="transaction-icon">
                            <i class="fas fa-arrow-<?= $transaction['type'] === 'deposit' ? 'down' : 'up' ?>"></i>
                        </div>
                        <div class="transaction-info">
                            <strong><?= $transaction['type'] === 'deposit' ? 'Adicionado' : 'Retirado' ?></strong>
                            <small><?= date('d/m/Y', strtotime($transaction['transaction_date'])) ?></small>
                            <?php if ($transaction['description']): ?>
                                <p><?= htmlspecialchars($transaction['description']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="transaction-amount">
                            <span class="<?= $transaction['type'] ?>">
                                <?= $transaction['type'] === 'deposit' ? '+' : '-' ?>
                                R$ <?= number_format($transaction['amount'], 2, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.form-container {
    max-width: 800px;
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
}

.reserve-info-card {
    background: var(--card-bg);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.reserve-info-card h3 {
    margin: 0 0 1rem 0;
    color: var(--text-primary);
}

.reserve-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
}

.progress-bar-container {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.progress-bar {
    flex: 1;
    height: 12px;
    background: var(--bg-secondary);
    border-radius: 6px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    transition: width 0.3s ease;
}

.progress-text {
    font-weight: 600;
    color: var(--text-primary);
}

.form-card {
    background: var(--card-bg);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.transaction-type-selector {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.radio-card {
    position: relative;
    cursor: pointer;
}

.radio-card input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.radio-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1.5rem;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    transition: all 0.2s;
    background: var(--input-bg);
}

.radio-content i {
    font-size: 2rem;
    color: var(--text-secondary);
}

.radio-card input[type="radio"]:checked + .radio-content {
    border-color: var(--primary-color);
    background: rgba(76, 175, 80, 0.1);
}

.radio-card input[type="radio"]:checked + .radio-content i {
    color: var(--primary-color);
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

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border-color);
}

.transactions-history {
    margin-top: 2rem;
    background: var(--card-bg);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.transactions-history h3 {
    margin: 0 0 1.5rem 0;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.transactions-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.transaction-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--bg-secondary);
    border-radius: 8px;
    border-left: 3px solid;
}

.transaction-item.deposit {
    border-left-color: #4CAF50;
}

.transaction-item.withdraw {
    border-left-color: #F44336;
}

.transaction-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 1.2rem;
}

.transaction-item.deposit .transaction-icon {
    background: rgba(76, 175, 80, 0.1);
    color: #4CAF50;
}

.transaction-item.withdraw .transaction-icon {
    background: rgba(244, 67, 54, 0.1);
    color: #F44336;
}

.transaction-info {
    flex: 1;
}

.transaction-info strong {
    display: block;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.transaction-info small {
    color: var(--text-secondary);
    font-size: 0.85rem;
}

.transaction-info p {
    margin: 0.5rem 0 0 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.transaction-amount span {
    font-size: 1.1rem;
    font-weight: 600;
}

.transaction-amount .deposit {
    color: #4CAF50;
}

.transaction-amount .withdraw {
    color: #F44336;
}
</style>

<?php 
$content = ob_get_clean();
include APP_ROOT . '/app/views/layouts/main.php';
?>
