<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';
$db = getDB();

$msg = '';
$type = '';

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if ($name === '') {
        $msg = __t('cat_name_required');
        $type = 'danger';
    } else {
        $chk = $db->prepare('SELECT id FROM categories WHERE name = ?');
        $chk->bind_param('s', $name);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) {
            $msg = __t('cat_exists', htmlspecialchars($name));
            $type = 'warning';
        } else {
            $ins = $db->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
            $ins->bind_param('ss', $name, $desc);
            $ins->execute() ? ($msg = __t('cat_added')) && ($type = 'success')
                : ($msg = __t('cat_add_failed')) && ($type = 'danger');
        }
    }
}

// Handle Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if (!$id || !$name) {
        $msg = __t('cat_invalid');
        $type = 'danger';
    } else {
        $chk = $db->prepare('SELECT id FROM categories WHERE name = ? AND id != ?');
        $chk->bind_param('si', $name, $id);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) {
            $msg = __t('cat_another_exists');
            $type = 'warning';
        } else {
            $upd = $db->prepare('UPDATE categories SET name = ?, description = ? WHERE id = ?');
            $upd->bind_param('ssi', $name, $desc, $id);
            $upd->execute() ? ($msg = __t('cat_updated')) && ($type = 'success')
                : ($msg = __t('cat_delete_failed')) && ($type = 'danger');
        }
    }
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    // Check if products reference this category
    $chk = $db->prepare('SELECT COUNT(*) c FROM products WHERE category_id = ?');
    $chk->bind_param('i', $id);
    $chk->execute();
    $chk->bind_result($cnt);
    $chk->fetch();
    $chk->close();
    if ($cnt > 0) {
        $msg = __t('cat_has_products', $cnt);
        $type = 'danger';
    } else {
        $del = $db->prepare('DELETE FROM categories WHERE id = ?');
        $del->bind_param('i', $id);
        $del->execute() ? ($msg = __t('cat_deleted')) && ($type = 'success')
            : ($msg = __t('cat_delete_failed')) && ($type = 'danger');
    }
}

// Fetch for edit modal
$editCat = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
    $id = (int) $_GET['edit'];
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $editCat = $stmt->get_result()->fetch_assoc();
}

// List categories with product count
$categories = $db->query(
    'SELECT c.*, COUNT(p.id) product_count
     FROM categories c LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id ORDER BY c.name'
);

$pageTitle = __t('categories_title');
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="d-flex">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="main-wrapper flex-grow-1">
        <div class="topbar">
            <div>
                <p class="topbar-title mb-0"><i class="bi bi-tag me-2"
                        style="color:var(--accent)"></i><?= __t('categories_title') ?></p>
                <p class="topbar-subtitle"><?= __t('categories_subtitle') ?></p>
            </div>
        </div>
        <div class="page-content">
            <?php if ($msg): ?>
                <div class="ss-alert ss-alert-<?= $type ?>" data-auto-dismiss>
                    <i
                        class="bi bi-<?= $type === 'success' ? 'check-circle-fill' : ($type === 'warning' ? 'exclamation-triangle-fill' : 'x-circle-fill') ?>"></i>
                    <?= $msg ?>
                </div>
            <?php endif; ?>

            <div class="row g-3">
                <!-- ADD FORM -->
                <div class="col-lg-4">
                    <div class="ss-card">
                        <h3 style="font-size:1rem;font-weight:700;margin-bottom:1rem;">
                            <i class="bi bi-plus-circle me-2" style="color:var(--accent)"></i><?= __t('add_category') ?>
                        </h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            <div class="mb-3">
                                <label class="form-label"><?= __t('cat_name_label') ?></label>
                                <input type="text" name="name" class="form-control"
                                    placeholder="<?= __t('cat_name_placeholder') ?>" required maxlength="100">
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= __t('cat_desc_label') ?></label>
                                <textarea name="description" class="form-control" rows="3"
                                    placeholder="<?= __t('cat_desc_placeholder') ?>"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary-ss w-100">
                                <i class="bi bi-plus-lg me-1"></i> <?= __t('add_category') ?>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- LIST -->
                <div class="col-lg-8">
                    <div class="ss-table-wrap">
                        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);">
                            <span style="font-size:.95rem;font-weight:700;"><?= __t('all_categories_title') ?></span>
                            <span class="badge bg-light text-secondary border ms-2"><?= $categories->num_rows ?></span>
                        </div>
                        <div class="table-responsive">
                            <table class="ss-table table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th><?= __t('col_name') ?></th>
                                        <th><?= __t('col_description') ?></th>
                                        <th><?= __t('col_products') ?></th>
                                        <th><?= __t('col_created') ?></th>
                                        <th><?= __t('col_actions') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($categories->num_rows === 0): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4"><?= __t('no_categories') ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php $i = 1;
                                        while ($cat = $categories->fetch_assoc()): ?>
                                            <tr>
                                                <td class="text-muted"><?= $i++ ?></td>
                                                <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                                                <td style="color:var(--text-muted);font-size:.82rem;">
                                                    <?= htmlspecialchars($cat['description'] ?: '—') ?></td>
                                                <td>
                                                    <span class="badge-stock-ok"><?= $cat['product_count'] ?>
                                                        <?= __t('items_suffix') ?></span>
                                                </td>
                                                <td style="font-size:.78rem;color:var(--text-muted);">
                                                    <?= date('M j, Y', strtotime($cat['created_at'])) ?></td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                            data-bs-target="#editModal" data-id="<?= $cat['id'] ?>"
                                                            data-name="<?= htmlspecialchars($cat['name']) ?>"
                                                            data-desc="<?= htmlspecialchars($cat['description']) ?>">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                data-confirm="<?= __t('cat_delete_confirm', htmlspecialchars($cat['name'])) ?>">
                                                                <i class="bi bi-trash3"></i>
                                                            </button>
                                                        </form>
                                                    </div>
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
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:var(--radius);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2"
                        style="color:var(--accent)"></i><?= __t('edit_category') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label class="form-label"><?= __t('cat_name_label') ?></label>
                        <input type="text" name="name" id="edit-name" class="form-control" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __t('cat_desc_label') ?></label>
                        <textarea name="description" id="edit-desc" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary"
                        data-bs-dismiss="modal"><?= __t('btn_cancel') ?></button>
                    <button type="submit" class="btn btn-primary-ss"><?= __t('btn_save') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.getElementById('editModal').addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        document.getElementById('edit-id').value = btn.dataset.id;
        document.getElementById('edit-name').value = btn.dataset.name;
        document.getElementById('edit-desc').value = btn.dataset.desc;
    });
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>