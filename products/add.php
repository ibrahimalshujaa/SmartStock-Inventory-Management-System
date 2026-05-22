<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';
$db = getDB();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name']           ?? '');
    $catId     = (int)($_POST['category_id']   ?? 0);
    $quantity  = (int)($_POST['quantity']      ?? 0);
    $price     = (float)($_POST['price']       ?? 0);
    $critLimit = (int)($_POST['critical_limit']?? 5);

    if (!$name || !$catId || $price < 0 || $quantity < 0) {
        $error = __t('product_fill_error');
    } else {
        $ins = $db->prepare(
            'INSERT INTO products (name, category_id, quantity, price, critical_limit) VALUES (?,?,?,?,?)'
        );
        $ins->bind_param('siidi', $name, $catId, $quantity, $price, $critLimit);
        if ($ins->execute()) {
            $success = __t('product_added', htmlspecialchars($name));
        } else {
            $error = __t('product_add_failed');
        }
    }
}

$categories = $db->query('SELECT id, name FROM categories ORDER BY name');
$pageTitle  = __t('add_product');
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="d-flex">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="main-wrapper flex-grow-1">
    <div class="topbar">
        <div>
            <p class="topbar-title mb-0"><i class="bi bi-plus-circle me-2" style="color:var(--accent)"></i><?= __t('add_product') ?></p>
            <p class="topbar-subtitle"><?= __t('add_product_subtitle') ?></p>
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
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label"><?= __t('product_name_label') ?></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Laptop Dell XPS" required maxlength="150"
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?= __t('category_label') ?></label>
                            <select name="category_id" class="form-select" required>
                                <option value=""><?= __t('select_category') ?></option>
                                <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?= $cat['id'] ?>" <?= (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label"><?= __t('quantity_label') ?></label>
                                <input type="number" name="quantity" class="form-control" min="0" required
                                       value="<?= htmlspecialchars($_POST['quantity'] ?? '0') ?>">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label"><?= __t('price_label') ?></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="price" class="form-control" step="0.01" min="0" required
                                           value="<?= htmlspecialchars($_POST['price'] ?? '0.00') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label"><?= __t('critical_limit_label') ?>
                                <span class="text-muted fw-normal"><?= __t('critical_limit_hint') ?></span>
                            </label>
                            <input type="number" name="critical_limit" class="form-control" min="0" required
                                   value="<?= htmlspecialchars($_POST['critical_limit'] ?? '5') ?>">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary-ss flex-grow-1">
                                <i class="bi bi-plus-lg me-1"></i> <?= __t('add_product') ?>
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
