<?php 
$activeMenu = 'categories';
$pageTitle = $category ? 'Editar Categoria' : 'Nova Categoria';
ob_start(); 
?>

<div class="dashboard-card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h3>
            <i class="fas fa-tags"></i>
            <?= $category ? 'Editar Categoria' : 'Nova Categoria' ?>
        </h3>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>categories/<?= $category ? 'update/' . $category['id'] : 'create' ?>">
            <div class="form-group">
                <label for="name">Nome da Categoria *</label>
                <input type="text" id="name" name="name" value="<?= $category['name'] ?? '' ?>" required>
            </div>

            <div class="form-group">
                <label for="type">Tipo *</label>
                <select id="type" name="type" required>
                    <option value="expense" <?= ($category['type'] ?? '') === 'expense' ? 'selected' : '' ?>>Despesa</option>
                    <option value="income" <?= ($category['type'] ?? '') === 'income' ? 'selected' : '' ?>>Receita</option>
                </select>
            </div>

            <div class="form-group">
                <label for="color">Cor</label>
                <div class="color-selection">
                    <input type="color" id="color" name="color" class="color-picker" value="<?= $category['color'] ?? '#4CAF50' ?>">
                    <input type="text" id="color-text" class="color-text" value="<?= $category['color'] ?? '#4CAF50' ?>" readonly>
                </div>
                <div class="color-presets">
                    <?php
                    $colors = ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#F44336', '#00BCD4', '#FFEB3B', '#795548', '#E91E63', '#009688', '#FF5722', '#3F51B5'];
                    foreach ($colors as $c): ?>
                        <button type="button" class="color-preset-btn" style="background-color: <?= $c ?>" onclick="selectColorPreset('<?= $c ?>')"></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Ícone</label>
                <input type="hidden" id="icon" name="icon" value="<?= $category['icon'] ?? 'fas fa-tag' ?>">
                <div class="icon-preview">
                    <i id="selected-icon" class="<?= $category['icon'] ?? 'fas fa-tag' ?>"></i>
                    <span id="selected-icon-name"><?= $category['icon'] ?? 'fas fa-tag' ?></span>
                </div>
                <div class="icon-grid">
                    <?php
                    $icons = [
                        'fas fa-tag', 'fas fa-shopping-cart', 'fas fa-home', 'fas fa-car', 'fas fa-utensils',
                        'fas fa-shopping-bag', 'fas fa-tshirt', 'fas fa-heartbeat', 'fas fa-pills', 
                        'fas fa-hospital', 'fas fa-briefcase', 'fas fa-laptop', 'fas fa-mobile-alt',
                        'fas fa-wifi', 'fas fa-lightbulb', 'fas fa-bolt', 'fas fa-water', 'fas fa-fire',
                        'fas fa-bus', 'fas fa-taxi', 'fas fa-plane', 'fas fa-gas-pump', 'fas fa-parking',
                        'fas fa-film', 'fas fa-music', 'fas fa-gamepad', 'fas fa-book', 'fas fa-graduation-cap',
                        'fas fa-dumbbell', 'fas fa-futbol', 'fas fa-basketball-ball', 'fas fa-bicycle',
                        'fas fa-coffee', 'fas fa-wine-glass', 'fas fa-beer', 'fas fa-birthday-cake',
                        'fas fa-gift', 'fas fa-credit-card', 'fas fa-money-bill-wave', 'fas fa-piggy-bank',
                        'fas fa-wallet', 'fas fa-coins', 'fas fa-chart-line', 'fas fa-dollar-sign',
                        'fas fa-paw', 'fas fa-dog', 'fas fa-cat', 'fas fa-ellipsis-h',
                        'fas fa-paint-brush', 'fas fa-wrench', 'fas fa-tools', 'fas fa-hammer',
                        'fas fa-phone', 'fas fa-envelope', 'fas fa-globe', 'fas fa-tv'
                    ];
                    foreach ($icons as $icon): ?>
                        <button type="button" class="icon-option" onclick="selectIcon('<?= $icon ?>')" title="<?= $icon ?>">
                            <i class="<?= $icon ?>"></i>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?= BASE_URL ?>categories" class="btn btn-secondary">
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

<script>
function selectIcon(iconClass) {
    document.getElementById('icon').value = iconClass;
    document.getElementById('selected-icon').className = iconClass;
    document.getElementById('selected-icon-name').textContent = iconClass;
    
    // Remove active class from all icons
    document.querySelectorAll('.icon-option').forEach(btn => btn.classList.remove('active'));
    
    // Add active class to selected icon
    event.target.closest('.icon-option').classList.add('active');
}

function selectColorPreset(color) {
    document.getElementById('color').value = color;
    document.getElementById('color-text').value = color;
}

// Update color text when color picker changes
document.addEventListener('DOMContentLoaded', function() {
    const colorPicker = document.getElementById('color');
    const colorText = document.getElementById('color-text');
    
    colorPicker.addEventListener('input', function() {
        colorText.value = this.value.toUpperCase();
    });
    
    // Set active icon on load
    const currentIcon = document.getElementById('icon').value;
    document.querySelectorAll('.icon-option').forEach(btn => {
        const icon = btn.querySelector('i');
        if (icon && icon.className === currentIcon) {
            btn.classList.add('active');
        }
    });
});
</script>

<style>
.color-selection {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-bottom: 1rem;
}

.color-picker {
    width: 80px;
    height: 50px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    cursor: pointer;
}

.color-text {
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-weight: 600;
    width: 120px;
}

.color-presets {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.color-preset-btn {
    width: 40px;
    height: 40px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.color-preset-btn:hover {
    transform: scale(1.15);
    border-color: var(--primary-color);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.icon-preview {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--bg-secondary);
    border: 2px solid var(--primary-color);
    border-radius: 8px;
    margin-bottom: 1rem;
}

.icon-preview i {
    font-size: 2rem;
    color: var(--primary-color);
}

.icon-preview span {
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.icon-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
    gap: 0.5rem;
    max-height: 300px;
    overflow-y: auto;
    padding: 1rem;
    background: var(--bg-secondary);
    border-radius: 8px;
    border: 1px solid var(--border-color);
}

.icon-option {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    background: var(--card-bg);
    cursor: pointer;
    transition: all 0.2s;
}

.icon-option i {
    font-size: 1.5rem;
    color: var(--text-primary);
}

.icon-option:hover {
    background: var(--primary-color);
    border-color: var(--primary-color);
    transform: scale(1.1);
}

.icon-option:hover i {
    color: white;
}

.icon-option.active {
    background: var(--primary-color);
    border-color: var(--primary-color);
}

.icon-option.active i {
    color: white;
}

/* Scrollbar para o grid de ícones */
.icon-grid::-webkit-scrollbar {
    width: 8px;
}

.icon-grid::-webkit-scrollbar-track {
    background: var(--bg-primary);
    border-radius: 4px;
}

.icon-grid::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 4px;
}

.icon-grid::-webkit-scrollbar-thumb:hover {
    background: var(--primary-color);
}
</style>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>

