<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: /SmartStock/products/index.php'); exit; }

// Fetch product
$stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) { header('Location: /SmartStock/products/index.php'); exit; }

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name']            ?? '');
    $catId     = (int)($_POST['category_id']    ?? 0);
    $price     = (float)($_POST['price']        ?? 0);
    $critLimit = (int)($_POST['critical_limit'] ?? 5);

    if (!$name || !$catId || $price < 0) {
        $error = __t('product_fill_error');
    } else {
        $upd = $db->prepare(
            'UPDATE products SET name=?, category_id=?, price=?, critical_limit=? WHERE id=?'
        );
        $upd->bind_param('sidii', $name, $catId, $price, $critLimit, $id);
        if ($upd->execute()) {
            $product = array_merge($product, [
                'name' => $name, 'category_id' => $catId,
                'price' => $price, 'critical_limit' => $critLimit
            ]);
            $success = __t('product_updated');
        } else {
            $error = __t('product_update_failed');
        }
    }
}

$categories = $db->query('SELECT id, name FROM categories ORDER BY name');
$pageTitle  = __t('edit_product');
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="d-flex">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="main-wrapper flex-grow-1">
    <div class="topbar">
        <div>
            <p class="topbar-title mb-0"><i class="bi bi-pencil-square me-2" style="color:var(--accent)"></i><?= __t('edit_product') ?></p>
            <p class="topbar-subtitle"><?= htmlspecialchars($product['name']) ?></p>
        </div>
        <a href="/SmartStock/products/index.php" class="btn btn-outline-ss">
            <i class="bi bi-arrow-left me-1"></i> <?= __t('btn_back') ?>
        </a>
    </div>
    <div class="page-content">
        <?php if ($error):  ?><div class="ss-alert ss-alert-danger"  data-auto-dismiss><i class="bi bi-x-circle-fill"></i> <?= $error  ?></div><?php endif; ?>
        <?php if ($success):?><div class="ss-alert ss-alert-success" data-auto-dismiss><i class="bi bi-check-circle-fill"></i> <?= $success ?></div><?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="ss-card">
                    <!-- Current qty (read-only) -->
                    <div class="ss-alert ss-alert-info mb-4">
                        <i class="bi bi-info-circle-fill"></i>
                        Current quantity: <strong><?= $product['quantity'] ?></strong> units.
                        Use <a href="/SmartStock/stock/add.php" style="color:inherit;font-weight:700;">Stock Movement</a> to adjust stock levels.
                    </div>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label"><?= __t('product_name_label') ?></label>
                            <input type="text" name="name" class="form-control" required maxlength="150"
                                   value="<?= htmlspecialchars($product['name']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= __t('category_label') ?></label>
                            <select name="category_id" class="form-select" required>
                                <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label"><?= __t('price_label') ?></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="price" class="form-control" step="0.01" min="0" required
                                           value="<?= number_format($product['price'], 2, '.', '') ?>">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label"><?= __t('critical_limit_label') ?></label>
                                <input type="number" name="critical_limit" class="form-control" min="0" required
                                       value="<?= $product['critical_limit'] ?>">
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-2">
                            <button type="submit" class="btn btn-primary-ss flex-grow-1">
                                <i class="bi bi-save me-1"></i> <?= __t('btn_save') ?>
                            </button>
                            <a href="/SmartStock/products/index.php" class="btn btn-outline-secondary"><?= __t('btn_cancel') ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
