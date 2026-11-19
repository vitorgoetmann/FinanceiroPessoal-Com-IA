<?php
$content = $content ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css?v=5">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Overlay para mobile -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-wallet"></i>
            <h2><?= APP_NAME ?></h2>
        </div>

        <nav class="sidebar-nav">
            <a href="<?= BASE_URL ?>dashboard" class="nav-item <?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?= BASE_URL ?>transactions" class="nav-item <?= $activeMenu === 'transactions' ? 'active' : '' ?>">
                <i class="fas fa-exchange-alt"></i>
                <span>Transações</span>
            </a>
            <a href="<?= BASE_URL ?>reserves" class="nav-item <?= $activeMenu === 'reserves' ? 'active' : '' ?>">
                <i class="fas fa-piggy-bank"></i>
                <span>Reservas</span>
            </a>
            <a href="<?= BASE_URL ?>accounts" class="nav-item <?= $activeMenu === 'accounts' ? 'active' : '' ?>">
                <i class="fas fa-credit-card"></i>
                <span>Contas e Cartões</span>
            </a>
            <a href="<?= BASE_URL ?>categories" class="nav-item <?= $activeMenu === 'categories' ? 'active' : '' ?>">
                <i class="fas fa-tags"></i>
                <span>Categorias</span>
            </a>
            <a href="<?= BASE_URL ?>reports" class="nav-item <?= $activeMenu === 'reports' ? 'active' : '' ?>">
                <i class="fas fa-chart-pie"></i>
                <span>Relatórios</span>
            </a>
            <a href="<?= BASE_URL ?>assistant" class="nav-item <?= $activeMenu === 'assistant' ? 'active' : '' ?>">
                <i class="fas fa-robot"></i>
                <span>Assistente de I.A</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <label class="theme-toggle" title="Alternar tema">
                <input type="checkbox" id="theme-checkbox" onchange="toggleTheme()">
                <span class="toggle-slider">
                    <i class="fas fa-sun"></i>
                    <i class="fas fa-moon"></i>
                </span>
            </label>
            
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <div class="user-details">
                    <strong><?= $_SESSION['user_name'] ?></strong>
                    <small><?= $_SESSION['user_email'] ?></small>
                </div>
            </div>
            <a href="<?= BASE_URL ?>auth/logout" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                Sair
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="topbar">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="page-title"><?= $pageTitle ?? 'Dashboard' ?></h1>
            <div class="topbar-actions">
                <button class="btn btn-primary" onclick="openNewTransactionModal()">
                    <i class="fas fa-plus"></i>
                    Nova Transação
                </button>
            </div>
        </div>

        <div class="content-wrapper">
            <?php if ($flash = $this->getFlash()): ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible">
                    <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <?= $flash['message'] ?>
                    <button class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </main>

    <script>
        const BASE_URL = '<?= BASE_URL ?>';
    </script>
    <script src="<?= BASE_URL ?>public/assets/js/app.js"></script>
    <?php if (isset($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html>
