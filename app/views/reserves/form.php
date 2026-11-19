<?php 
$activeMenu = 'reserves';
$pageTitle = $reserve ? 'Editar Reserva' : 'Nova Reserva';
ob_start(); 
?>

<div class="form-container">
    <div class="form-header">
        <h2><?= $reserve ? 'Editar Reserva' : 'Nova Reserva' ?></h2>
        <a href="<?= BASE_URL ?>reserves" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Voltar
        </a>
    </div>

    <form method="POST" action="<?= BASE_URL ?>reserves/<?= $reserve ? 'edit/' . $reserve['id'] : 'create' ?>" class="form-card">
        <div class="form-group">
            <label for="name">Nome da Reserva *</label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   class="form-control" 
                   value="<?= $reserve['name'] ?? '' ?>"
                   placeholder="Ex: Concurso, Jaqueta Nova, Viagem..."
                   required>
        </div>

        <div class="form-group">
            <label for="description">Descrição</label>
            <textarea id="description" 
                      name="description" 
                      class="form-control" 
                      rows="3"
                      placeholder="Adicione detalhes sobre esta reserva..."><?= $reserve['description'] ?? '' ?></textarea>
        </div>

        <?php if (!$reserve): ?>
        <div class="form-group">
            <label for="account_id">Conta/Cartão *</label>
            <select id="account_id" name="account_id" class="form-control" required>
                <option value="">Selecione uma conta</option>
                <?php foreach ($accounts as $account): ?>
                    <option value="<?= $account['id'] ?>">
                        <?= htmlspecialchars($account['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="form-text">A conta de onde virá o dinheiro para esta reserva</small>
        </div>
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label for="target_amount">Valor da Meta *</label>
                <input type="number" 
                       id="target_amount" 
                       name="target_amount" 
                       class="form-control" 
                       value="<?= $reserve['target_amount'] ?? '' ?>"
                       step="0.01"
                       min="0.01"
                       placeholder="0,00"
                       required>
            </div>

            <?php if (!$reserve): ?>
            <div class="form-group">
                <label for="current_amount">Valor Inicial</label>
                <input type="number" 
                       id="current_amount" 
                       name="current_amount" 
                       class="form-control" 
                       value="0"
                       step="0.01"
                       min="0"
                       placeholder="0,00">
                <small class="form-text">Deixe 0 se ainda não tem nada guardado</small>
            </div>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="deadline">Prazo (opcional)</label>
                <input type="date" 
                       id="deadline" 
                       name="deadline" 
                       class="form-control" 
                       value="<?= $reserve['deadline'] ?? '' ?>"
                       min="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label for="priority">Prioridade</label>
                <select id="priority" name="priority" class="form-control">
                    <option value="low" <?= ($reserve['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Baixa</option>
                    <option value="medium" <?= ($reserve['priority'] ?? 'medium') === 'medium' ? 'selected' : '' ?>>Média</option>
                    <option value="high" <?= ($reserve['priority'] ?? '') === 'high' ? 'selected' : '' ?>>Alta</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="color">Cor</label>
            <div class="color-picker">
                <input type="color" 
                       id="color" 
                       name="color" 
                       class="form-control-color" 
                       value="<?= $reserve['color'] ?? '#4CAF50' ?>">
                <input type="text" 
                       id="color-text" 
                       class="form-control" 
                       value="<?= $reserve['color'] ?? '#4CAF50' ?>"
                       readonly>
            </div>
            <div class="color-presets">
                <?php
                $colors = ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#F44336', '#00BCD4', '#FFEB3B', '#795548'];
                foreach ($colors as $color): ?>
                    <button type="button" 
                            class="color-preset" 
                            style="background-color: <?= $color ?>"
                            onclick="selectColor('<?= $color ?>')">
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= BASE_URL ?>reserves" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                <?= $reserve ? 'Atualizar' : 'Criar' ?> Reserva
            </button>
        </div>
    </form>
</div>

<script>
const colorInput = document.getElementById('color');
const colorText = document.getElementById('color-text');

colorInput.addEventListener('input', function() {
    colorText.value = this.value.toUpperCase();
});

function selectColor(color) {
    colorInput.value = color;
    colorText.value = color;
}
</script>

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

.form-card {
    background: var(--card-bg);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.form-text {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.85rem;
    color: var(--text-secondary);
}

.color-picker {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.form-control-color {
    width: 60px;
    height: 45px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    cursor: pointer;
}

.color-presets {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.color-preset {
    width: 40px;
    height: 40px;
    border: 2px solid var(--border-color);
    border-radius: 6px;
    cursor: pointer;
    transition: transform 0.2s, border-color 0.2s;
}

.color-preset:hover {
    transform: scale(1.1);
    border-color: var(--primary-color);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border-color);
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<?php 
$content = ob_get_clean();
include APP_ROOT . '/app/views/layouts/main.php';
?>
