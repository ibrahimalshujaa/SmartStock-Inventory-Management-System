<?php
// Determine current page for active highlighting
$currentPage = basename(dirname($_SERVER['PHP_SELF'])) . '/' . basename($_SERVER['PHP_SELF']);
function navActive(string $path): string {
    global $currentPage;
    return str_contains($currentPage, $path) ? 'active' : '';
}
?>
<!-- ===== SIDEBAR ===== -->
<nav id="sidebar" class="sidebar d-flex flex-column">
    <!-- Brand -->
    <div class="sidebar-brand d-flex align-items-center gap-2">
        <div class="brand-icon">
            <i class="bi bi-boxes"></i>
        </div>
        <div>
            <span class="brand-name"><?= __t('app_name') ?></span>
            <span class="brand-tagline"><?= __t('app_tagline') ?></span>
        </div>
    </div>

    <!-- User Badge -->
    <div class="sidebar-user">
        <div class="user-avatar">
            <?= strtoupper(substr(currentUsername(), 0, 1)) ?>
        </div>
        <div class="user-info">
            <span class="user-name"><?= currentUsername() ?></span>
            <span class="user-role badge <?= isAdmin() ? 'badge-admin' : 'badge-user' ?>">
                <?= isAdmin() ? __t('role_admin') : __t('role_user') ?>
            </span>
        </div>
    </div>

    <!-- Nav Links -->
    <ul class="sidebar-nav flex-grow-1">
        <li class="nav-section-title"><?= __t('nav_main') ?></li>
        <li>
            <a href="/SmartStock/dashboard.php" class="nav-link <?= navActive('dashboard') ?>">
                <i class="bi bi-speedometer2"></i> <?= __t('nav_dashboard') ?>
            </a>
        </li>

        <?php if (isAdmin()): ?>
        <li class="nav-section-title"><?= __t('nav_catalog') ?></li>
        <li>
            <a href="/SmartStock/categories/index.php" class="nav-link <?= navActive('categories') ?>">
                <i class="bi bi-tag"></i> <?= __t('nav_categories') ?>
            </a>
        </li>
        <?php endif; ?>

        <li class="nav-section-title"><?= __t('nav_inventory') ?></li>
        <li>
            <a href="/SmartStock/products/index.php" class="nav-link <?= navActive('products') ?>">
                <i class="bi bi-box-seam"></i> <?= __t('nav_products') ?>
            </a>
        </li>
        <?php if (isAdmin()): ?>
        <li>
            <a href="/SmartStock/stock/add.php" class="nav-link <?= navActive('stock/add') ?>">
                <i class="bi bi-arrow-left-right"></i> <?= __t('nav_stock_movement') ?>
            </a>
        </li>
        <li>
            <a href="/SmartStock/stock/history.php" class="nav-link <?= navActive('stock/history') ?>">
                <i class="bi bi-clock-history"></i> <?= __t('nav_movement_history') ?>
            </a>
        </li>
        <?php endif; ?>

        <?php if (isAdmin()): ?>
        <li class="nav-section-title"><?= __t('nav_analytics') ?></li>
        <li>
            <a href="/SmartStock/reports/index.php" class="nav-link <?= navActive('reports') ?>">
                <i class="bi bi-bar-chart-line"></i> <?= __t('nav_reports') ?>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <!-- Language Switcher + Logout -->
    <div class="sidebar-footer" style="display:flex;flex-direction:column;gap:.6rem;">
        <!-- Language switcher -->
        <div style="display:flex;gap:.4rem;justify-content:center;">
            <a href="<?= lang_url('en') ?>"
               style="padding:.25rem .65rem;border-radius:6px;font-size:.72rem;font-weight:700;text-decoration:none;
                      <?= current_lang() === 'en'
                          ? 'background:var(--accent);color:#fff;'
                          : 'background:rgba(255,255,255,.08);color:var(--text-muted);' ?>">
                🇬🇧 EN
            </a>
            <a href="<?= lang_url('tr') ?>"
               style="padding:.25rem .65rem;border-radius:6px;font-size:.72rem;font-weight:700;text-decoration:none;
                      <?= current_lang() === 'tr'
                          ? 'background:var(--accent);color:#fff;'
                          : 'background:rgba(255,255,255,.08);color:var(--text-muted);' ?>">
                🇹🇷 TR
            </a>
        </div>
        <!-- Logout -->
        <a href="/SmartStock/auth/logout.php" class="logout-btn">
            <i class="bi bi-box-arrow-left"></i> <?= __t('nav_logout') ?>
        </a>
    </div>
</nav>

<!-- Sidebar Toggle Button (mobile) -->
<button id="sidebarToggle" class="sidebar-toggle-btn d-lg-none">
    <i class="bi bi-list"></i>
</button>
<!-- Overlay -->
<div id="sidebarOverlay" class="sidebar-overlay d-lg-none"></div>
