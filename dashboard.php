<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/config/database.php';

$db = getDB();

// --- Stats ---
$totalProducts = $db->query('SELECT COUNT(*) c FROM products')->fetch_assoc()['c'];
$totalCategories = $db->query('SELECT COUNT(*) c FROM categories')->fetch_assoc()['c'];
$totalValue = $db->query('SELECT COALESCE(SUM(quantity * price), 0) v FROM products')->fetch_assoc()['v'];
$criticalCount = $db->query('SELECT COUNT(*) c FROM products WHERE quantity <= critical_limit')->fetch_assoc()['c'];

// --- Critical stock items ---
$criticalItems = $db->query(
    'SELECT p.name, p.quantity, p.critical_limit, c.name category
     FROM products p 
     JOIN categories c ON p.category_id = c.id
     WHERE p.quantity <= p.critical_limit
     ORDER BY p.quantity ASC
     LIMIT 8'
);

// --- Recent movements ---
$recentMoves = $db->query(
    'SELECT sm.movement_type, sm.quantity, sm.created_at,
            p.name product_name, u.username
     FROM stock_movements sm
     JOIN products p ON sm.product_id = p.id
     JOIN users u ON sm.user_id = u.id
     ORDER BY sm.created_at DESC
     LIMIT 8'
);

$pageTitle = __t('dashboard_title');
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="d-flex">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <div class="main-wrapper flex-grow-1">

        <div class="topbar">
            <div>
                <p class="topbar-title mb-0">
                    <i class="bi bi-speedometer2 me-2 text-indigo"></i><?= __t('dashboard_title') ?>
                </p>
                <p class="topbar-subtitle"><?= __t('welcome_back') ?>, <?= currentUsername() ?>!</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border" style="font-size:.72rem;">
                    <i class="bi bi-calendar3 me-1"></i><?= date('D, M j Y') ?>
                </span>
            </div>
        </div>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'access_denied'): ?>
        <div class="ss-alert ss-alert-danger" data-auto-dismiss style="margin:1rem 1.5rem 0;">
            <i class="bi bi-shield-lock-fill"></i>
            <?= __t('access_denied_msg') ?>
        </div>
        <?php endif; ?>

        <div class="page-content">

            <div class="row g-3 mb-4">

                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card stat-indigo">
                        <div class="stat-icon"><i class="bi bi-box-seam-fill"></i></div>
                        <div>
                            <div class="stat-value" data-count="<?= $totalProducts ?>">
                                <?= $totalProducts ?>
                            </div>
                            <div class="stat-label"><?= __t('stat_total_products') ?></div>
                        </div>
                    </div>
                </div>

                <?php if (isAdmin()): ?>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card stat-violet">
                        <div class="stat-icon"><i class="bi bi-tag-fill"></i></div>
                        <div>
                            <div class="stat-value" data-count="<?= $totalCategories ?>">
                                <?= $totalCategories ?>
                            </div>
                            <div class="stat-label"><?= __t('stat_categories') ?></div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card stat-emerald">
                        <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
                        <div>
                            <div class="stat-value" data-count="<?= $totalValue ?>" data-prefix="$"
                                style="font-size:1.35rem;">
                                $<?= number_format($totalValue, 2) ?>
                            </div>
                            <div class="stat-label"><?= __t('stat_stock_value') ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card stat-rose">
                        <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                        <div>
                            <div class="stat-value" data-count="<?= $criticalCount ?>">
                                <?= $criticalCount ?>
                            </div>
                            <div class="stat-label"><?= __t('stat_critical_alerts') ?></div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row g-3">

                <div class="col-lg-5">
                    <div class="ss-card h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h3 class="mb-0" style="font-size:1rem;font-weight:700;">
                                <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i><?= __t('critical_stock') ?>
                            </h3>
                            <a href="/SmartStock/products/index.php?filter=critical"
                                class="btn btn-sm btn-outline-ss"><?= __t('view_all') ?></a>
                        </div>

                        <?php if ($criticalItems->num_rows === 0): ?>
                            <div class="empty-state">
                                <i class="bi bi-check-circle"></i>
                                <?= __t('all_healthy') ?>
                            </div>
                        <?php else: ?>
                            <?php while ($item = $criticalItems->fetch_assoc()): ?>
                                <div class="critical-item">
                                    <div>
                                        <div style="font-size:.85rem;font-weight:600;">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </div>
                                        <div style="font-size:.72rem;color:var(--text-muted);">
                                            <?= htmlspecialchars($item['category']) ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div style="font-size:1rem;font-weight:800;color:#dc2626;">
                                            <?= $item['quantity'] ?>
                                        </div>
                                        <div style="font-size:.68rem;color:var(--text-muted);">
                                            / <?= $item['critical_limit'] ?> limit
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (isAdmin()): ?>
                <div class="col-lg-7">
                    <div class="ss-card h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h3 class="mb-0" style="font-size:1rem;font-weight:700;">
                                <i class="bi bi-clock-history me-2" style="color:var(--accent)"></i><?= __t('recent_movements') ?>
                            </h3>
                            <a href="/SmartStock/stock/history.php" class="btn btn-sm btn-outline-ss"><?= __t('view_all') ?></a>
                        </div>

                        <?php if ($recentMoves->num_rows === 0): ?>
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i><?= __t('no_movements') ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="ss-table table">
                                    <thead>
                                        <tr>
                                            <th><?= __t('col_product') ?></th>
                                            <th><?= __t('col_type') ?></th>
                                            <th><?= __t('col_qty') ?></th>
                                            <th><?= __t('col_user') ?></th>
                                            <th><?= __t('col_date') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($m = $recentMoves->fetch_assoc()): ?>
                                            <tr class="<?= $m['movement_type'] === 'in' ? 'stock-in-row' : 'stock-out-row' ?>">
                                                <td class="product-name-cell">
                                                    <?= htmlspecialchars($m['product_name']) ?>
                                                </td>
                                                <td>
                                                    <span class="badge-<?= $m['movement_type'] ?>">
                                                        <i class="bi bi-arrow-<?= $m['movement_type'] === 'in' ? 'down' : 'up' ?>-circle-fill me-1"></i>
                                                        <?= strtoupper($m['movement_type']) ?>
                                                    </span>
                                                </td>
                                                <td class="qty-highlight"><?= $m['quantity'] ?></td>
                                                <td>
                                                    <span style="font-size:.8rem;">
                                                        <?= htmlspecialchars($m['username']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span style="font-size:.75rem;color:var(--text-muted);">
                                                        <?= date('M j, H:i', strtotime($m['created_at'])) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>