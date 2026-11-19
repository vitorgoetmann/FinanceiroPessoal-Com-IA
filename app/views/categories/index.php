<?php 
$activeMenu = 'categories';
$pageTitle = 'Categorias';
ob_start(); 
?>

<div class="page-header-simple">
    <h2 class="page-title-main">Gerenciar Categorias</h2>
    <a href="<?= BASE_URL ?>categories/new" class="btn btn-primary btn-add">
        <i class="fas fa-plus"></i>
        Nova Categoria
    </a>
</div>

<div class="categories-container">
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-arrow-down"></i> Despesas</h3>
        </div>
        <div class="card-body">
            <?php 
            $expenses = array_filter($categories, fn($c) => $c['type'] === 'expense');
            if (!empty($expenses)): 
            ?>
                <div class="categories-grid">
                    <?php foreach ($expenses as $cat): ?>
                        <div class="category-item" style="border-left-color: <?= $cat['color'] ?>">
                            <div class="category-icon" style="background-color: <?= $cat['color'] ?>20; color: <?= $cat['color'] ?>">
                                <i class="<?= $cat['icon'] ?>"></i>
                            </div>
                            <div class="category-info">
                                <strong><?= $cat['name'] ?></strong>
                                <?php if ($cat['is_default']): ?>
                                    <span class="badge badge-default">Padrão</span>
                                <?php endif; ?>
                            </div>
                            <div class="category-actions">
                                <a href="<?= BASE_URL ?>categories/edit/<?= $cat['id'] ?>" class="btn-icon btn-edit" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if (!$cat['is_default']): ?>
                                    <button onclick="deleteCategory(<?= $cat['id'] ?>)" class="btn-icon btn-delete" title="Excluir">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center">Nenhuma categoria de despesa cadastrada</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-arrow-up"></i> Receitas</h3>
        </div>
        <div class="card-body">
            <?php 
            $incomes = array_filter($categories, fn($c) => $c['type'] === 'income');
            if (!empty($incomes)): 
            ?>
                <div class="categories-grid">
                    <?php foreach ($incomes as $cat): ?>
                        <div class="category-item" style="border-left-color: <?= $cat['color'] ?>">
                            <div class="category-icon" style="background-color: <?= $cat['color'] ?>20; color: <?= $cat['color'] ?>">
                                <i class="<?= $cat['icon'] ?>"></i>
                            </div>
                            <div class="category-info">
                                <strong><?= $cat['name'] ?></strong>
                                <?php if ($cat['is_default']): ?>
                                    <span class="badge badge-default">Padrão</span>
                                <?php endif; ?>
                            </div>
                            <div class="category-actions">
                                <a href="<?= BASE_URL ?>categories/edit/<?= $cat['id'] ?>" class="btn-icon btn-edit" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if (!$cat['is_default']): ?>
                                    <button onclick="deleteCategory(<?= $cat['id'] ?>)" class="btn-icon btn-delete" title="Excluir">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center">Nenhuma categoria de receita cadastrada</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.categories-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.categories-grid {
    display: grid;
    gap: 12px;
}

.category-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background-color: var(--bg-tertiary);
    border-radius: var(--border-radius);
    border-left: 4px solid;
}

.category-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.category-info {
    flex: 1;
}

.category-info strong {
    display: block;
    margin-bottom: 4px;
}

.badge-default {
    background-color: var(--gray-200);
    color: var(--text-secondary);
    font-size: 11px;
}

.category-actions {
    display: flex;
    gap: 4px;
}

@media (max-width: 768px) {
    .categories-container {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function deleteCategory(id) {
    if (confirm('Tem certeza que deseja excluir esta categoria?')) {
        fetch(`<?= BASE_URL ?>categories/delete/${id}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao excluir categoria');
            }
        });
    }
}
</script>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
