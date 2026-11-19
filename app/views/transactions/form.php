<?php 
$transaction = $transaction ?? null;
$categories = $categories ?? [];
$accounts = $accounts ?? [];
$activeMenu = 'transactions';
$pageTitle = $transaction ? 'Editar Transação' : 'Nova Transação';
ob_start(); 
?>

<div class="transaction-form-container">
    <div class="dashboard-card">
        <div class="card-header">
            <h3>
                <i class="fas fa-exchange-alt"></i>
                <?= $transaction ? 'Editar Transação' : 'Nova Transação' ?>
            </h3>
        </div>
        <div class="card-body">
            <div class="ai-helper">
                <div class="ai-helper-header">
                    <i class="fas fa-robot"></i>
                    <strong>Adicionar por texto</strong>
                </div>
                <div class="ai-helper-body">
                    <textarea id="ai-text" rows="2" placeholder="Ex: gastei 30 reais na adega com bebida dia 2/05 no cartao nubank"></textarea>
                    <div class="ai-helper-actions">
                        <button type="button" class="btn btn-secondary btn-sm" id="ai-parse-btn">
                            <i class="fas fa-magic"></i>
                            Interpretar
                        </button>
                        <span class="ai-helper-hint">Confira no modal antes de incluir.</span>
                    </div>
                </div>
            </div>

            <div id="ai-confirm-modal" class="modal" aria-hidden="true">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>
                            <i class="fas fa-robot"></i>
                            Confirmar transacao
                        </h3>
                        <button type="button" class="modal-close" onclick="closeModal('ai-confirm-modal')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="ai-summary">
                            <div class="ai-summary-row ai-summary-input">
                                <span>Descricao</span>
                                <input type="text" id="ai-summary-description" placeholder="Ex: Cerveja no mercado">
                            </div>
                            <div class="ai-summary-row">
                                <span>Valor</span>
                                <strong id="ai-summary-amount">-</strong>
                            </div>
                            <div class="ai-summary-row">
                                <span>Data</span>
                                <strong id="ai-summary-date">-</strong>
                            </div>
                            <div class="ai-summary-row">
                                <span>Tipo</span>
                                <strong id="ai-summary-type">-</strong>
                            </div>
                            <div class="ai-summary-row">
                                <span>Categoria</span>
                                <strong id="ai-summary-category">-</strong>
                            </div>
                            <div class="ai-summary-row">
                                <span>Conta</span>
                                <strong id="ai-summary-account">-</strong>
                            </div>
                            <div class="ai-summary-row">
                                <span>Status</span>
                                <strong id="ai-summary-status">-</strong>
                            </div>
                        </div>
                        <div id="ai-summary-warning" class="ai-summary-warning"></div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('ai-confirm-modal')">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-primary" id="ai-confirm-btn">
                            <i class="fas fa-check"></i>
                            Incluir transacao
                        </button>
                    </div>
                </div>
            </div>

            <form method="POST" action="<?= BASE_URL ?>transactions/<?= $transaction ? 'update/' . $transaction['id'] : 'create' ?>" class="transaction-form">
                
                <!-- Linha 1: Tipo e Valor lado a lado -->
                <div class="form-grid">
                    <!-- Seleção de Tipo com Botões -->
                    <div class="form-group">
                        <label><i class="fas fa-exchange-alt"></i> Tipo de Transação *</label>
                        <div class="type-buttons">
                            <button type="button" class="type-btn <?= ($transaction['type'] ?? 'expense') === 'expense' ? 'active' : '' ?>" data-type="expense" onclick="selectType('expense')">
                                <i class="fas fa-arrow-down"></i>
                                <span>DESPESA</span>
                            </button>
                            <button type="button" class="type-btn <?= ($transaction['type'] ?? '') === 'income' ? 'active' : '' ?>" data-type="income" onclick="selectType('income')">
                                <i class="fas fa-arrow-up"></i>
                                <span>RECEITA</span>
                            </button>
                        </div>
                        <input type="hidden" id="type" name="type" value="<?= $transaction['type'] ?? 'expense' ?>" required>
                    </div>

                    <!-- Valor -->
                    <div class="form-group">
                        <label for="amount"><i class="fas fa-dollar-sign"></i> Valor *</label>
                        <input type="text" id="amount" name="amount" placeholder="R$ 0,00" value="<?= isset($transaction['amount']) ? number_format($transaction['amount'], 2, ',', '.') : '' ?>" required style="font-size: 18px; font-weight: 600;">
                    </div>
                </div>

                <!-- Linha 2: Descrição -->
                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Descrição *</label>
                    <input type="text" id="description" name="description" placeholder="Ex: Supermercado, Salário, etc." value="<?= $transaction['description'] ?? '' ?>" required>
                </div>

                <!-- Linha 3: Categoria, Conta e Status -->
                <div class="form-grid-3">
                    <div class="form-group">
                        <label for="category_id"><i class="fas fa-tag"></i> Categoria *</label>
                        <select id="category_id" name="category_id" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" data-type="<?= $cat['type'] ?>" <?= ($transaction['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= $cat['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="account_id"><i class="fas fa-credit-card"></i> Conta *</label>
                        <select id="account_id" name="account_id" required>
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>" <?= ($transaction['account_id'] ?? '') == $acc['id'] ? 'selected' : '' ?>>
                                    <?= $acc['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status"><i class="fas fa-check-circle"></i> Status *</label>
                        <select id="status" name="status" required>
                            <option value="paid" <?= ($transaction['status'] ?? 'paid') === 'paid' ? 'selected' : '' ?>>Pago</option>
                            <option value="pending" <?= ($transaction['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pendente</option>
                            <option value="scheduled" <?= ($transaction['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Agendado</option>
                        </select>
                    </div>
                </div>

                <!-- Linha 4: Data e Parcelamento -->
                <div class="form-grid">
                    <div class="form-group">
                        <label for="transaction_date"><i class="fas fa-calendar"></i> Data *</label>
                        <input type="date" id="transaction_date" name="transaction_date" value="<?= $transaction['transaction_date'] ?? date('Y-m-d') ?>" required>
                    </div>

                    <?php if (!$transaction): ?>
                        <div class="form-group">
                            <label for="installments"><i class="fas fa-credit-card"></i> Parcelamento</label>
                            <input type="number" id="installments" name="installments" placeholder="1" value="1" min="1" max="60">
                            <small class="form-hint">Número de parcelas (1 = à vista)</small>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Linha 5: Observações -->
                <div class="form-group">
                    <label for="notes"><i class="fas fa-comment-alt"></i> Observações</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Adicione observações adicionais (opcional)"><?= $transaction['notes'] ?? '' ?></textarea>
                </div>

                <!-- Botões de Ação -->
                <div class="form-actions">
                    <a href="<?= BASE_URL ?>transactions" class="btn btn-secondary">
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
</div>

<script>
// Selecionar tipo de transação
function selectType(type) {
    document.getElementById('type').value = type;
    
    // Atualizar botões ativos
    document.querySelectorAll('.type-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`.type-btn[data-type="${type}"]`).classList.add('active');
    
    // Atualizar categorias
    updateCategories();
}

// Filtrar categorias por tipo
function updateCategories() {
    const type = document.getElementById('type').value;
    const categorySelect = document.getElementById('category_id');
    const options = categorySelect.querySelectorAll('option');
    
    options.forEach(option => {
        const optionType = option.getAttribute('data-type');
        if (optionType === type || !optionType) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    });
    
    // Selecionar a primeira opção visível
    const firstVisible = Array.from(options).find(opt => opt.style.display !== 'none');
    if (firstVisible) {
        categorySelect.value = firstVisible.value;
    }
}

// Aplicar filtro ao carregar
updateCategories();

// Máscara de valor
document.getElementById('amount').addEventListener('input', function() {
    maskCurrency(this);
});

// Interpretar texto livre com IA
let aiParsedData = null;

function mapTypeLabel(type) {
    return type === 'income' ? 'Receita' : 'Despesa';
}

function mapStatusLabel(status) {
    if (status === 'pending') return 'Pendente';
    if (status === 'scheduled') return 'Agendado';
    return 'Pago';
}

function updateAiConfirmState() {
    const warning = document.getElementById('ai-summary-warning');
    const confirmBtn = document.getElementById('ai-confirm-btn');
    const descriptionInput = document.getElementById('ai-summary-description');

    if (!aiParsedData) {
        confirmBtn.disabled = true;
        return;
    }

    let warningText = '';

    if (!aiParsedData.category_id) {
        warningText = 'Categoria nao identificada. Ajuste no formulario.';
    } else if (!aiParsedData.account_id) {
        warningText = 'Conta nao identificada. Ajuste no formulario.';
    }

    if (descriptionInput && descriptionInput.value.trim() === '') {
        warningText = 'Descricao obrigatoria. Ajuste antes de incluir.';
    }

    if (warningText) {
        warning.textContent = warningText;
        warning.classList.add('visible');
        confirmBtn.disabled = true;
        return;
    }

    warning.textContent = '';
    warning.classList.remove('visible');
    confirmBtn.disabled = false;
}

function showAiSummary(data) {
    const descriptionInput = document.getElementById('ai-summary-description');
    if (descriptionInput) {
        descriptionInput.value = data.description || '';
    }
    document.getElementById('ai-summary-amount').textContent = data.amount !== null ? formatCurrency(data.amount) : '-';
    document.getElementById('ai-summary-date').textContent = data.transaction_date ? formatDate(data.transaction_date) : '-';
    document.getElementById('ai-summary-type').textContent = mapTypeLabel(data.type);
    document.getElementById('ai-summary-category').textContent = data.category_name || 'Nao identificado';
    document.getElementById('ai-summary-account').textContent = data.account_name || 'Nao identificado';
    document.getElementById('ai-summary-status').textContent = mapStatusLabel(data.status);

    updateAiConfirmState();

    openModal('ai-confirm-modal');
}

document.getElementById('ai-parse-btn').addEventListener('click', function() {
    const btn = this;
    const text = document.getElementById('ai-text').value.trim();
    if (!text) {
        showToast('Informe um texto para interpretar', 'error');
        return;
    }

    btn.disabled = true;
    btn.classList.add('loading');

    fetch(BASE_URL + 'transactions/parseText', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({ text })
    })
        .then(response => response.json())
        .then(payload => {
            if (!payload.success) {
                showToast(payload.message || 'Erro ao interpretar texto', 'error');
                return;
            }

            aiParsedData = payload.data || {};
            showAiSummary(aiParsedData);
        })
        .catch(() => {
            showToast('Falha ao comunicar com a IA', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.classList.remove('loading');
        });
});

document.getElementById('ai-confirm-btn').addEventListener('click', function() {
    if (!aiParsedData) {
        return;
    }

    const btn = this;
    btn.disabled = true;

    fetch(BASE_URL + 'transactions/createFromAi', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            type: aiParsedData.type,
            amount: aiParsedData.amount,
            description: (document.getElementById('ai-summary-description') || {}).value || '',
            transaction_date: aiParsedData.transaction_date,
            status: aiParsedData.status,
            category_id: aiParsedData.category_id || '',
            account_id: aiParsedData.account_id || '',
            notes: aiParsedData.notes || ''
        })
    })
        .then(response => response.json())
        .then(payload => {
            if (!payload.success) {
                showToast(payload.message || 'Erro ao criar transacao', 'error');
                return;
            }

            closeModal('ai-confirm-modal');
            showToast('Transacao criada com sucesso!', 'success');
            setTimeout(() => {
                window.location.href = BASE_URL + 'transactions';
            }, 600);
        })
        .catch(() => {
            showToast('Falha ao comunicar com o servidor', 'error');
        })
        .finally(() => {
            btn.disabled = false;
        });
});

document.getElementById('ai-summary-description').addEventListener('input', updateAiConfirmState);
</script>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
