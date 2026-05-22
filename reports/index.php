<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';
$db = getDB();

// Total stock value
$totalValue = $db->query(
    'SELECT COALESCE(SUM(quantity * price), 0) v FROM products'
)->fetch_assoc()['v'];

// Category summary: name, product count, total qty, total value
$catSummary = $db->query(
    'SELECT c.name, COUNT(p.id) product_count,
            COALESCE(SUM(p.quantity), 0) total_qty,
            COALESCE(SUM(p.quantity * p.price), 0) total_value
     FROM categories c LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id ORDER BY total_value DESC'
);

$catRows = [];
while ($row = $catSummary->fetch_assoc()) $catRows[] = $row;
$maxVal = $catRows ? max(array_column($catRows, 'total_value')) : 1;

// Critical products
$criticals = $db->query(
    'SELECT p.name, p.quantity, p.critical_limit, p.price, c.name category_name
     FROM products p JOIN categories c ON p.category_id = c.id
     WHERE p.quantity <= p.critical_limit
     ORDER BY p.quantity ASC'
);

// Movement summary (in vs out)
$moveSummary = $db->query(
    'SELECT movement_type, SUM(quantity) total, COUNT(*) records
     FROM stock_movements GROUP BY movement_type'
);
$mvStats = ['in' => ['total' => 0, 'records' => 0], 'out' => ['total' => 0, 'records' => 0]];
while ($r = $moveSummary->fetch_assoc()) $mvStats[$r['movement_type']] = $r;

$pageTitle = __t('reports_title');
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="d-flex">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="main-wrapper flex-grow-1">
    <div class="topbar">
        <div>
            <p class="topbar-title mb-0"><i class="bi bi-bar-chart-line me-2" style="color:var(--accent)"></i><?= __t('reports_title') ?></p>
            <p class="topbar-subtitle"><?= __t('reports_subtitle') ?></p>
        </div>
        <span class="badge bg-light text-secondary border" style="font-size:.72rem;">
            <?= __t('generated') ?>: <?= date('M j, Y H:i') ?>
        </span>
    </div>
    <div class="page-content">

        <!-- TOP STATS -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card stat-emerald">
                    <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
                    <div>
                        <div class="stat-value" style="font-size:1.4rem;" data-count="<?= number_format($totalValue, 2) ?>" data-prefix="$">
                            $<?= number_format($totalValue, 2) ?>
                        </div>
                        <div class="stat-label"><?= __t('stat_total_value') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card stat-indigo">
                    <div class="stat-icon"><i class="bi bi-arrow-down-circle-fill"></i></div>
                    <div>
                        <div class="stat-value" data-count="<?= $mvStats['in']['total'] ?>"><?= number_format($mvStats['in']['total']) ?></div>
                        <div class="stat-label"><?= __t('stat_total_in') ?> (<?= $mvStats['in']['records'] ?> <?= __t('records_suffix') ?>)</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card stat-rose">
                    <div class="stat-icon"><i class="bi bi-arrow-up-circle-fill"></i></div>
                    <div>
                        <div class="stat-value" data-count="<?= $mvStats['out']['total'] ?>"><?= number_format($mvStats['out']['total']) ?></div>
                        <div class="stat-label"><?= __t('stat_total_out') ?> (<?= $mvStats['out']['records'] ?> <?= __t('records_suffix') ?>)</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- CATEGORY BREAKDOWN -->
            <div class="col-lg-7">
                <div class="ss-card h-100">
                    <h3 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;">
                        <i class="bi bi-tag me-2" style="color:var(--accent)"></i><?= __t('cat_value_breakdown') ?>
                    </h3>
                    <?php if (empty($catRows)): ?>
                    <div class="empty-state"><i class="bi bi-tag"></i><?= __t('no_cats') ?></div>
                    <?php else: ?>
                    <div class="table-responsive mb-3">
                        <table class="ss-table table">
                            <thead>
                                <tr>
                                    <th><?= __t('col_category') ?></th>
                                    <th><?= __t('col_products') ?></th>
                                    <th><?= __t('col_total_qty') ?></th>
                                    <th><?= __t('col_value') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($catRows as $cat): ?>
                            <tr>
                                <td class="product-name-cell"><?= htmlspecialchars($cat['name']) ?></td>
                                <td><?= $cat['product_count'] ?></td>
                                <td><?= number_format($cat['total_qty']) ?></td>
                                <td style="font-weight:700;color:var(--accent);">$<?= number_format($cat['total_value'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <p style="font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.75rem;">
                            <?= __t('value_distribution') ?>
                        </p>
                        <?php foreach ($catRows as $cat):
                            $pct = $maxVal > 0 ? ($cat['total_value'] / $maxVal) * 100 : 0;
                        ?>
                        <div class="cat-bar-row">
                            <span class="cat-bar-label"><?= htmlspecialchars($cat['name']) ?></span>
                            <div class="cat-bar-track">
                                <div class="cat-bar-fill" style="width:<?= round($pct) ?>%"></div>
                            </div>
                            <span class="cat-bar-val">$<?= number_format($cat['total_value'], 0) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- CRITICAL PRODUCTS -->
            <div class="col-lg-5">
                <div class="ss-card h-100">
                    <h3 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;">
                        <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i><?= __t('critical_low_products') ?>
                    </h3>
                    <?php if ($criticals->num_rows === 0): ?>
                    <div class="empty-state">
                        <i class="bi bi-check-circle" style="color:#22c55e;opacity:1;"></i>
                        <?= __t('all_stock_healthy') ?>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="ss-table table">
                            <thead>
                                <tr>
                                    <th><?= __t('col_product') ?></th>
                                    <th><?= __t('col_qty') ?></th>
                                    <th><?= __t('col_limit') ?></th>
                                    <th><?= __t('col_value_at_risk') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while ($p = $criticals->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="product-name-cell" style="font-size:.82rem;"><?= htmlspecialchars($p['name']) ?></div>
                                    <div style="font-size:.7rem;color:var(--text-muted);"><?= htmlspecialchars($p['category_name']) ?></div>
                                </td>
                                <td class="qty-highlight text-danger"><?= $p['quantity'] ?></td>
                                <td class="text-muted"><?= $p['critical_limit'] ?></td>
                                <td style="font-size:.82rem;font-weight:600;">$<?= number_format($p['quantity'] * $p['price'], 2) ?></td>
                            </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
