<?php 
$activeMenu = 'reserves';
$pageTitle = 'Reservas de Dinheiro';
ob_start(); 
?>

<div class="page-header-reserves">
    <div class="filter-group">
        <label>Status:</label>
        <select onchange="window.location.href='<?= BASE_URL ?>reserves?status=' + this.value" class="form-select">
            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Ativas</option>
            <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Concluídas</option>
            <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Canceladas</option>
            <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Todas</option>
        </select>
    </div>
    <a href="<?= BASE_URL ?>reserves/form" class="btn btn-primary btn-add">
        <i class="fas fa-plus"></i>
        Nova Reserva
    </a>
</div>

<div class="summary-card mb-4">
    <div class="card-icon">
        <i class="fas fa-piggy-bank"></i>
    </div>
    <div class="card-content">
        <h3>Total Reservado</h3>
        <p class="card-value">R$ <?= number_format($totalReserved, 2, ',', '.') ?></p>
        <small class="text-muted">Soma de todas as reservas ativas</small>
    </div>
</div>

<?php if (empty($reserves)): ?>
    <div class="empty-state">
        <i class="fas fa-piggy-bank"></i>
        <h3>Nenhuma reserva encontrada</h3>
        <p>Crie sua primeira reserva para começar a organizar seu dinheiro!</p>
        <a href="<?= BASE_URL ?>reserves/form" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Criar Reserva
        </a>
    </div>
<?php else: ?>
    <div class="reserves-grid">
        <?php foreach ($reserves as $reserve): ?>
            <?php 
                $progress = $reserve['target_amount'] > 0 
                    ? min(($reserve['current_amount'] / $reserve['target_amount']) * 100, 100) 
                    : 0;
                $remaining = $reserve['target_amount'] - $reserve['current_amount'];
                
                $priorityLabels = [
                    'low' => 'Baixa',
                    'medium' => 'Média',
                    'high' => 'Alta'
                ];
                
                $statusLabels = [
                    'active' => 'Ativa',
                    'completed' => 'Concluída',
                    'cancelled' => 'Cancelada'
                ];
                
                $statusClasses = [
                    'active' => 'status-active',
                    'completed' => 'status-completed',
                    'cancelled' => 'status-cancelled'
                ];
            ?>
            <div class="reserve-card" style="border-left: 4px solid <?= $reserve['color'] ?>">
                <div class="reserve-header">
                    <div>
                        <h3><?= htmlspecialchars($reserve['name']) ?></h3>
                        <span class="badge badge-<?= $reserve['priority'] ?>">
                            <?= $priorityLabels[$reserve['priority']] ?>
                        </span>
                        <span class="badge <?= $statusClasses[$reserve['status']] ?>">
                            <?= $statusLabels[$reserve['status']] ?>
                        </span>
                    </div>
                    <div class="reserve-actions">
                        <a href="<?= BASE_URL ?>reserves/transaction/<?= $reserve['id'] ?>" 
                           class="btn btn-sm btn-success" title="Adicionar/Retirar">
                            <i class="fas fa-exchange-alt"></i>
                        </a>
                        <a href="<?= BASE_URL ?>reserves/form/<?= $reserve['id'] ?>" 
                           class="btn btn-sm btn-primary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="deleteReserve(<?= $reserve['id'] ?>)" 
                                class="btn btn-sm btn-danger" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                <?php if ($reserve['description']): ?>
                    <p class="reserve-description"><?= htmlspecialchars($reserve['description']) ?></p>
                <?php endif; ?>

                <div class="reserve-account">
                    <i class="fas fa-credit-card"></i>
                    <span><?= htmlspecialchars($reserve['account_name']) ?></span>
                </div>

                <div class="reserve-progress">
                    <div class="progress-header">
                        <span>R$ <?= number_format($reserve['current_amount'], 2, ',', '.') ?></span>
                        <span>R$ <?= number_format($reserve['target_amount'], 2, ',', '.') ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $progress ?>%; background-color: <?= $reserve['color'] ?>"></div>
                    </div>
                    <div class="progress-footer">
                        <span><?= number_format($progress, 1) ?>% alcançado</span>
                        <?php if ($remaining > 0): ?>
                            <span class="text-muted">Faltam R$ <?= number_format($remaining, 2, ',', '.') ?></span>
                        <?php else: ?>
                            <span class="text-success">Meta atingida! 🎉</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($reserve['deadline']): ?>
                    <div class="reserve-deadline">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Prazo: <?= date('d/m/Y', strtotime($reserve['deadline'])) ?></span>
                        <?php
                            $daysRemaining = ceil((strtotime($reserve['deadline']) - time()) / 86400);
                            if ($daysRemaining > 0): ?>
                                <span class="text-muted">(<?= $daysRemaining ?> dias)</span>
                            <?php elseif ($daysRemaining == 0): ?>
                                <span class="text-warning">(Vence hoje!)</span>
                            <?php else: ?>
                                <span class="text-danger">(Vencida há <?= abs($daysRemaining) ?> dias)</span>
                            <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function deleteReserve(id) {
    if (confirm('Tem certeza que deseja excluir esta reserva?')) {
        window.location.href = BASE_URL + 'reserves/delete/' + id;
    }
}
</script>

<style>
.reserves-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.reserve-card {
    background: var(--bg-primary);
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.reserve-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.reserve-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.reserve-header h3 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
    font-size: 1.25rem;
}

.reserve-actions {
    display: flex;
    gap: 0.5rem;
}

.reserve-description {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.reserve-account {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 1rem;
    padding: 0.5rem;
    background: var(--bg-secondary);
    border-radius: 4px;
}

.reserve-progress {
    margin: 1rem 0;
}

.progress-header, .progress-footer {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.progress-footer {
    margin-top: 0.5rem;
    margin-bottom: 0;
}

.progress-bar {
    width: 100%;
    height: 12px;
    background: var(--bg-secondary);
    border-radius: 6px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    transition: width 0.3s ease;
    border-radius: 6px;
}

.reserve-deadline {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 12px;
    margin-right: 0.5rem;
}

.badge-low {
    background: #E3F2FD;
    color: #1976D2;
}

.badge-medium {
    background: #FFF3E0;
    color: #F57C00;
}

.badge-high {
    background: #FFEBEE;
    color: #D32F2F;
}

.status-active {
    background: #E8F5E9;
    color: #2E7D32;
}

.status-completed {
    background: #E0F2F1;
    color: #00695C;
}

.status-cancelled {
    background: #ECEFF1;
    color: #546E7A;
}

.summary-card {
    background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.summary-card .card-icon {
    font-size: 3rem;
    opacity: 0.9;
}

.summary-card .card-content h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    opacity: 0.95;
    color: white;
    font-weight: 600;
}

.summary-card .card-value {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
}

.summary-card small {
    opacity: 0.85;
}

.text-success {
    color: #2E7D32;
    font-weight: 600;
}

.text-warning {
    color: #F57C00;
    font-weight: 600;
}

.text-danger {
    color: #D32F2F;
    font-weight: 600;
}
</style>

<?php 
$content = ob_get_clean();
include APP_ROOT . '/app/views/layouts/main.php';
?>
