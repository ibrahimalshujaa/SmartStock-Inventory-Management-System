<?php
require_once __DIR__ . '/../includes/auth.php';
if (isLoggedIn()) {
    header('Location: /SmartStock/dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/database.php';
    $db = getDB();

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirm']       ?? '';
    $role     = 'user'; // default; admin accounts created via DB

    if (!$username || !$email || !$password) {
        $error = __t('register_error_required');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = __t('register_error_email');
    } elseif (strlen($password) < 8) {
        $error = __t('register_error_length');
    } elseif ($password !== $confirm) {
        $error = __t('register_error_match');
    } else {
        // Check duplicates
        $stmt = $db->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = __t('register_error_duplicate');
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $ins  = $db->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
            $ins->bind_param('ssss', $username, $email, $hash, $role);
            if ($ins->execute()) {
                $success = __t('register_success');
            } else {
                $error = __t('register_error_failed');
            }
        }
    }
}

$pageTitle = __t('register_title');
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo"><i class="bi bi-boxes"></i></div>
        <h1 class="auth-title"><?= __t('register_heading') ?></h1>
        <p class="auth-subtitle"><?= __t('register_subtitle') ?></p>

        <?php if ($error): ?>
        <div class="ss-alert ss-alert-danger" data-auto-dismiss>
            <i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="ss-alert ss-alert-success" data-auto-dismiss>
            <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" class="auth-form" id="registerForm" novalidate>
            <div class="mb-3">
                <label for="reg-username" class="form-label"><?= __t('register_username_label') ?></label>
                <input type="text" id="reg-username" name="username" class="form-control"
                       placeholder="johndoe" maxlength="80" required
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="reg-email" class="form-label"><?= __t('register_email_label') ?></label>
                <input type="email" id="reg-email" name="email" class="form-control"
                       placeholder="john@example.com" maxlength="150" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="reg-password" class="form-label"><?= __t('register_password_label') ?> <span style="color:#64748b;font-weight:400;"><?= __t('register_password_hint') ?></span></label>
                <input type="password" id="reg-password" name="password" class="form-control"
                       placeholder="••••••••" required minlength="8">
            </div>
            <div class="mb-4">
                <label for="reg-confirm" class="form-label"><?= __t('register_confirm_label') ?></label>
                <input type="password" id="reg-confirm" name="confirm" class="form-control"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="auth-btn btn mb-3">
                <i class="bi bi-person-plus-fill me-1"></i> <?= __t('register_btn') ?>
            </button>
            <p class="text-center" style="color:#64748b;font-size:.85rem;margin:0;">
                <?= __t('register_have_account') ?>
                <a href="/SmartStock/auth/login.php" class="auth-link"><?= __t('register_login_link') ?></a>
            </p>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
