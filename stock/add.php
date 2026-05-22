<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';
$db = getDB();

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int)($_POST['product_id']    ?? 0);
    $type      = $_POST['movement_type']       ?? '';
    $qty       = (int)($_POST['quantity']      ?? 0);
    $notes     = trim($_POST['notes']          ?? '');
    $userId    = currentUserId();

    if (!$productId || !in_array($type, ['in','out']) || $qty <= 0) {
        $error = __t('stock_fill_error');
    } else {
        // Fetch current qty
        $stmt = $db->prepare('SELECT quantity FROM products WHERE id = ?');
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $stmt->bind_result($currentQty);
        $stmt->fetch();
        $stmt->close();

        if ($type === 'out' && $qty > $currentQty) {
            $error = __t('stock_insufficient', $qty, $currentQty);
        } else {
            $newQty = $type === 'in' ? $currentQty + $qty : $currentQty - $qty;
            $db->begin_transaction();
            try {
                $upd = $db->prepare('UPDATE products SET quantity = ? WHERE id = ?');
                $upd->bind_param('ii', $newQty, $productId);
                $upd->execute();

                $ins = $db->prepare(
                    'INSERT INTO stock_movements (product_id, user_id, movement_type, quantity, notes) VALUES (?,?,?,?,?)'
                );
                $ins->bind_param('iisis', $productId, $userId, $type, $qty, $notes);
                $ins->execute();
                $db->commit();
                $success = __t($type === 'in' ? 'stock_added' : 'stock_removed', $newQty);
            } catch (Exception $e) {
                $db->rollback();
                $error = __t('stock_tx_failed');
            }
        }
    }
}

$products = $db->query(
    'SELECT p.id, p.name, p.quantity, c.name cat
     FROM products p JOIN categories c ON p.category_id = c.id
     ORDER BY p.name'
);

$pageTitle = __t('stock_movement_title');
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="d-flex">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="main-wrapper flex-grow-1">
    <div class="topbar">
        <div>
            <p class="topbar-title mb-0"><i class="bi bi-arrow-left-right me-2" style="color:var(--accent)"></i><?= __t('stock_movement_title') ?></p>
            <p class="topbar-subtitle"><?= __t('stock_movement_subtitle') ?></p>
        </div>
        <a href="/SmartStock/stock/history.php" class="btn btn-outline-ss">
            <i class="bi bi-clock-history me-1"></i> <?= __t('history_link') ?>
        </a>
    </div>
    <div class="page-content">
        <?php if ($error):  ?><div class="ss-alert ss-alert-danger"  data-auto-dismiss><i class="bi bi-x-circle-fill"></i> <?= $error  ?></div><?php endif; ?>
        <?php if ($success):?><div class="ss-alert ss-alert-success" data-auto-dismiss><i class="bi bi-check-circle-fill"></i> <?= $success ?></div><?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="ss-card">
                    <form method="POST" id="stockForm">
                        <div class="mb-4">
                            <label class="form-label"><?= __t('col_product') ?> *</label>
                            <select name="product_id" id="productSelect" class="form-select" required onchange="updateQty(this)">
                                <option value=""><?= __t('select_product') ?></option>
                                <?php while ($p = $products->fetch_assoc()): ?>
                                <option value="<?= $p['id'] ?>"
                                    data-qty="<?= $p['quantity'] ?>"
                                    data-cat="<?= htmlspecialchars($p['cat']) ?>"
                                    <?= (($_POST['product_id'] ?? '') == $p['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['name']) ?> [<?= htmlspecialchars($p['cat']) ?>]
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Current stock display -->
                        <div id="currentStockBox" class="ss-alert ss-alert-info mb-4" style="display:none;">
                            <i class="bi bi-info-circle-fill"></i>
                            Current stock: <strong id="currentQtyDisplay">—</strong> <?= __t('units') ?>
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><?= __t('movement_type_label') ?></label>
                            <div class="d-flex gap-3">
                                <label class="d-flex align-items-center gap-2 cursor-pointer flex-grow-1">
                                    <input type="radio" name="movement_type" value="in"
                                           <?= (($_POST['movement_type'] ?? 'in') === 'in') ? 'checked' : '' ?> required>
                                    <span class="badge-in d-flex align-items-center gap-1 w-100 justify-content-center" style="padding:.5em 1em;font-size:.85rem;">
                                        <i class="bi bi-arrow-down-circle-fill"></i> <?= __t('stock_in') ?>
                                    </span>
                                </label>
                                <label class="d-flex align-items-center gap-2 cursor-pointer flex-grow-1">
                                    <input type="radio" name="movement_type" value="out"
                                           <?= (($_POST['movement_type'] ?? '') === 'out') ? 'checked' : '' ?>>
                                    <span class="badge-out d-flex align-items-center gap-1 w-100 justify-content-center" style="padding:.5em 1em;font-size:.85rem;">
                                        <i class="bi bi-arrow-up-circle-fill"></i> <?= __t('stock_out') ?>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><?= __t('quantity_label') ?></label>
                            <input type="number" name="quantity" id="qtyInput" class="form-control"
                                   min="1" required value="<?= (int)($_POST['quantity'] ?? 1) ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label"><?= __t('notes_label') ?> <span class="text-muted fw-normal"><?= __t('notes_optional') ?></span></label>
                            <textarea name="notes" class="form-control" rows="2"
                                      placeholder="<?= __t('notes_placeholder') ?>"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary-ss w-100">
                            <i class="bi bi-save me-1"></i> <?= __t('btn_record') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script>
function updateQty(sel) {
    const opt = sel.options[sel.selectedIndex];
    const qty = opt.dataset.qty;
    const box = document.getElementById('currentStockBox');
    const disp = document.getElementById('currentQtyDisplay');
    if (qty !== undefined && sel.value) {
        disp.textContent = qty;
        box.style.display = 'flex';
    } else {
        box.style.display = 'none';
    }
}
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('productSelect');
    if (sel) updateQty(sel);
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
