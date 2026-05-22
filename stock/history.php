<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';
$db = getDB();

// Filters
$filterType  = $_GET['type']       ?? '';
$filterDate  = $_GET['date']       ?? '';
$filterProd  = (int)($_GET['product'] ?? 0);

$where  = [];
$params = [];
$types  = '';

if (in_array($filterType, ['in', 'out'])) {
    $where[] = 'sm.movement_type = ?';
    $params[] = $filterType;
    $types .= 's';
}
if ($filterDate) {
    $where[] = 'DATE(sm.created_at) = ?';
    $params[] = $filterDate;
    $types .= 's';
}
if ($filterProd > 0) {
    $where[] = 'sm.product_id = ?';
    $params[] = $filterProd;
    $types .= 'i';
}

$sql = 'SELECT sm.*, p.name product_name, u.username, c.name category_name
        FROM stock_movements sm
        JOIN products p ON sm.product_id = p.id
        JOIN users u ON sm.user_id = u.id
        JOIN categories c ON p.category_id = c.id';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY sm.created_at DESC';

$stmt = $db->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$movements = $stmt->get_result();

$products = $db->query('SELECT id, name FROM products ORDER BY name');

$pageTitle = __t('history_title');
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="d-flex">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="main-wrapper flex-grow-1">
    <div class="topbar">
        <div>
            <p class="topbar-title mb-0"><i class="bi bi-clock-history me-2" style="color:var(--accent)"></i><?= __t('history_title') ?></p>
            <p class="topbar-subtitle"><?= __t('history_subtitle') ?></p>
        </div>
        <a href="/SmartStock/stock/add.php" class="btn btn-primary-ss">
            <i class="bi bi-plus-lg me-1"></i> <?= __t('btn_new_movement') ?>
        </a>
    </div>
    <div class="page-content">
        <!-- Filters -->
        <div class="ss-card mb-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label"><?= __t('col_type') ?></label>
                    <select name="type" class="form-select">
                        <option value=""><?= __t('filter_all') ?></option>
                        <option value="in"  <?= $filterType === 'in'  ? 'selected' : '' ?>><?= __t('stock_in') ?></option>
                        <option value="out" <?= $filterType === 'out' ? 'selected' : '' ?>><?= __t('stock_out') ?></option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= __t('col_date') ?></label>
                    <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($filterDate) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label"><?= __t('col_product') ?></label>
                    <select name="product" class="form-select">
                        <option value=""><?= __t('filter_all') ?> <?= __t('nav_products') ?></option>
                        <?php while ($p = $products->fetch_assoc()): ?>
                        <option value="<?= $p['id'] ?>" <?= $filterProd == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['name']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-ss flex-grow-1"><?= __t('btn_filter') ?></button>
                        <a href="/SmartStock/stock/history.php" class="btn btn-outline-secondary"><?= __t('btn_reset') ?></a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="ss-table-wrap">
            <div class="d-flex align-items-center justify-content-between" style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);">
                <span style="font-size:.95rem;font-weight:700;">
                    <?= __t('records_title') ?>
                    <span class="badge bg-light text-secondary border ms-2"><?= $movements->num_rows ?></span>
                </span>
            </div>
            <div class="table-responsive">
                <table class="ss-table table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?= __t('col_product') ?></th>
                            <th><?= __t('col_category') ?></th>
                            <th><?= __t('col_type') ?></th>
                            <th><?= __t('col_qty') ?></th>
                            <th><?= __t('col_user') ?></th>
                            <th><?= __t('col_notes') ?></th>
                            <th><?= __t('col_datetime') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($movements->num_rows === 0): ?>
                    <tr><td colspan="8"><div class="empty-state"><i class="bi bi-inbox"></i><?= __t('no_records') ?></div></td></tr>
                    <?php else: ?>
                    <?php $i = 1; while ($m = $movements->fetch_assoc()): ?>
                    <tr class="<?= $m['movement_type'] === 'in' ? 'stock-in-row' : 'stock-out-row' ?>">
                        <td class="text-muted"><?= $i++ ?></td>
                        <td class="product-name-cell"><?= htmlspecialchars($m['product_name']) ?></td>
                        <td><span style="font-size:.78rem;color:var(--text-muted);"><?= htmlspecialchars($m['category_name']) ?></span></td>
                        <td>
                            <?php if ($m['movement_type'] === 'in'): ?>
                                <span class="badge-in"><i class="bi bi-arrow-down-circle-fill me-1"></i>IN</span>
                            <?php else: ?>
                                <span class="badge-out"><i class="bi bi-arrow-up-circle-fill me-1"></i>OUT</span>
                            <?php endif; ?>
                        </td>
                        <td class="qty-highlight"><?= $m['quantity'] ?></td>
                        <td><?= htmlspecialchars($m['username']) ?></td>
                        <td style="font-size:.8rem;color:var(--text-muted);max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= htmlspecialchars($m['notes'] ?: '—') ?>
                        </td>
                        <td style="font-size:.78rem;white-space:nowrap;">
                            <span class="text-muted"><?= date('Y-m-d', strtotime($m['created_at'])) ?></span>
                            <span style="color:var(--accent)"> <?= date('H:i', strtotime($m['created_at'])) ?></span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
