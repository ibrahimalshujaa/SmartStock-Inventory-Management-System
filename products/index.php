<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../config/database.php';
$db = getDB();

// --- Filters ---
$search   = trim($_GET['search'] ?? '');
$catId    = (int)($_GET['category'] ?? 0);
$filter   = $_GET['filter'] ?? '';

// --- Build query ---
$where  = [];
$params = [];
$types  = '';

if ($search !== '') {
    $where[] = 'p.name LIKE ?';
    $params[] = '%' . $search . '%';
    $types .= 's';
}
if ($catId > 0) {
    $where[] = 'p.category_id = ?';
    $params[] = $catId;
    $types .= 'i';
}
if ($filter === 'critical') {
    $where[] = 'p.quantity <= p.critical_limit';
}

$sql = 'SELECT p.*, c.name category_name
        FROM products p JOIN categories c ON p.category_id = c.id';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY p.name';

$stmt = $db->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result();

// All categories for filter
$categories = $db->query('SELECT id, name FROM categories ORDER BY name');

$pageTitle = __t('products_title');
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="d-flex">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="main-wrapper flex-grow-1">
    <div class="topbar">
        <div>
            <p class="topbar-title mb-0"><i class="bi bi-box-seam me-2" style="color:var(--accent)"></i><?= __t('products_title') ?></p>
            <p class="topbar-subtitle"><?= __t('products_subtitle') ?></p>
        </div>
        <?php if (isAdmin()): ?>
        <a href="/SmartStock/products/add.php" class="btn btn-primary-ss">
            <i class="bi bi-plus-lg me-1"></i> <?= __t('add_product') ?>
        </a>
        <?php endif; ?>
    </div>
    <div class="page-content">
        <!-- Filters -->
        <div class="ss-card mb-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label"><?= __t('label_search') ?></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-color:var(--border);">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="<?= __t('search_placeholder') ?>"
                               value="<?= htmlspecialchars($search) ?>" style="border-left:none;">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><?= __t('col_category') ?></label>
                    <select name="category" class="form-select">
                        <option value=""><?= __t('all_categories') ?></option>
                        <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>" <?= $catId == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-ss flex-grow-1"><?= __t('btn_filter') ?></button>
                        <a href="/SmartStock/products/index.php" class="btn btn-outline-secondary"><?= __t('btn_reset') ?></a>
                    </div>
                </div>
                <?php if ($filter === 'critical'): ?>
                <div class="col-12">
                    <div class="ss-alert ss-alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?= __t('critical_filter_msg') ?>
                        <a href="/SmartStock/products/index.php" class="ms-2" style="color:inherit;"><?= __t('clear_filter') ?></a>
                    </div>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Products Table -->
        <div class="ss-table-wrap">
            <div class="table-responsive">
                <table class="ss-table table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?= __t('col_product_name') ?></th>
                            <th><?= __t('col_category') ?></th>
                            <th><?= __t('col_quantity') ?></th>
                            <th><?= __t('col_price') ?></th>
                            <th><?= __t('col_stock_value') ?></th>
                            <th><?= __t('col_status') ?></th>
                            <?php if (isAdmin()): ?><th><?= __t('col_actions') ?></th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($products->num_rows === 0): ?>
                    <tr><td colspan="8"><div class="empty-state"><i class="bi bi-box"></i><?= __t('no_products') ?></div></td></tr>
                    <?php else: ?>
                    <?php $i = 1; while ($p = $products->fetch_assoc()):
                        $isLow   = $p['quantity'] <= $p['critical_limit'];
                        $isVeryLow = $p['quantity'] <= floor($p['critical_limit'] / 2);
                    ?>
                    <tr>
                        <td class="text-muted"><?= $i++ ?></td>
                        <td class="product-name-cell"><?= htmlspecialchars($p['name']) ?></td>
                        <td>
                            <span style="background:#f1f5f9;padding:.2em .7em;border-radius:20px;font-size:.78rem;font-weight:500;color:var(--text-secondary);">
                                <?= htmlspecialchars($p['category_name']) ?>
                            </span>
                        </td>
                        <td class="qty-highlight <?= $isLow ? 'text-danger' : 'text-success' ?>">
                            <?= $p['quantity'] ?>
                        </td>
                        <td>$<?= number_format($p['price'], 2) ?></td>
                        <td style="font-weight:600;">$<?= number_format($p['quantity'] * $p['price'], 2) ?></td>
                        <td>
                            <?php if ($isVeryLow): ?>
                                <span class="badge-stock-crit"><i class="bi bi-exclamation-circle-fill me-1"></i><?= __t('status_critical') ?></span>
                            <?php elseif ($isLow): ?>
                                <span class="badge-stock-low"><i class="bi bi-exclamation-triangle-fill me-1"></i><?= __t('status_low') ?></span>
                            <?php else: ?>
                                <span class="badge-stock-ok"><i class="bi bi-check-circle-fill me-1"></i><?= __t('status_ok') ?></span>
                            <?php endif; ?>
                        </td>
                        <?php if (isAdmin()): ?>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/SmartStock/products/edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="/SmartStock/products/delete.php" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                        data-confirm="<?= __t('product_delete_confirm', htmlspecialchars($p['name'])) ?>">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        <?php endif; ?>
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
