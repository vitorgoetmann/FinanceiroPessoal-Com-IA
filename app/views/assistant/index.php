<?php
$categories = $categories ?? [];
$accounts = $accounts ?? [];
$activeMenu = 'assistant';
$pageTitle = 'Assistente de I.A';
ob_start();
?>

<div class="dashboard-card">
    <div class="card-header">
        <h3>
            <i class="fas fa-robot"></i>
            Assistente de I.A
        </h3>
    </div>
    <div class="card-body">
        <div class="ai-helper ai-helper-standalone">
            <div class="ai-helper-header">
                <i class="fas fa-comment-dots"></i>
                <strong>Descreva sua transação</strong>
            </div>
            <div class="ai-helper-body">
                <textarea id="ai-text" rows="3" placeholder="Ex: Dia 10 gastei 25 reais na padaria, conta inter"></textarea>
                <div class="ai-helper-actions">
                    <button type="button" class="btn btn-primary" id="ai-parse-btn">
                        <i class="fas fa-magic"></i>
                        Interpretar
                    </button>
                    <span class="ai-helper-hint">O assistente vai pedir confirmação antes de incluir.</span>
                </div>
            </div>
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
                    <span>Status</span>
                    <strong id="ai-summary-status">-</strong>
                </div>
            </div>

            <div class="ai-summary-selects">
                <div class="form-group">
                    <label for="ai-category">Categoria</label>
                    <select id="ai-category">
                        <option value="">Selecione</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" data-name="<?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= $cat['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="ai-account">Conta</label>
                    <select id="ai-account">
                        <option value="">Selecione</option>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>" data-name="<?= htmlspecialchars($acc['name'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= $acc['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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

<script>
let aiParsedData = null;

function mapTypeLabel(type) {
    return type === 'income' ? 'Receita' : 'Despesa';
}

function mapStatusLabel(status) {
    if (status === 'pending') return 'Pendente';
    if (status === 'scheduled') return 'Agendado';
    return 'Pago';
}

function syncSelectByName(selectEl, name) {
    if (!name) {
        selectEl.value = '';
        return;
    }

    const normalize = (value) => value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s]/g, '')
        .replace(/\s+/g, ' ')
        .trim();

    const needle = normalize(name);
    const options = Array.from(selectEl.options);
    let match = options.find(opt => normalize(opt.getAttribute('data-name') || '') === needle);

    if (!match) {
        match = options.find(opt => {
            const candidate = normalize(opt.getAttribute('data-name') || '');
            return candidate.includes(needle) || needle.includes(candidate);
        });
    }

    selectEl.value = match ? match.value : '';
}

function updateConfirmState() {
    const categoryId = document.getElementById('ai-category').value;
    const accountId = document.getElementById('ai-account').value;
    const descriptionInput = document.getElementById('ai-summary-description');
    const warning = document.getElementById('ai-summary-warning');
    const confirmBtn = document.getElementById('ai-confirm-btn');

    if (!categoryId || !accountId || !descriptionInput || descriptionInput.value.trim() === '') {
        warning.textContent = 'Informe descricao, categoria e conta para continuar.';
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
    document.getElementById('ai-summary-status').textContent = mapStatusLabel(data.status);

    syncSelectByName(document.getElementById('ai-category'), data.category_name);
    syncSelectByName(document.getElementById('ai-account'), data.account_name);
    updateConfirmState();

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

document.getElementById('ai-category').addEventListener('change', updateConfirmState);
document.getElementById('ai-account').addEventListener('change', updateConfirmState);
document.getElementById('ai-summary-description').addEventListener('input', updateConfirmState);

document.getElementById('ai-confirm-btn').addEventListener('click', function() {
    if (!aiParsedData) {
        return;
    }

    const btn = this;
    btn.disabled = true;

    const categoryId = document.getElementById('ai-category').value;
    const accountId = document.getElementById('ai-account').value;

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
            category_id: categoryId,
            account_id: accountId,
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
            document.getElementById('ai-text').value = '';
        })
        .catch(() => {
            showToast('Falha ao comunicar com o servidor', 'error');
        })
        .finally(() => {
            btn.disabled = false;
        });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
