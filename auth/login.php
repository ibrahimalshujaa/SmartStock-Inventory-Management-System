<?php
require_once __DIR__ . '/../includes/auth.php';
if (isLoggedIn()) {
    header('Location: /SmartStock/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/database.php';
    $db = getDB();

    $login    = trim($_POST['login']    ?? '');
    $password = $_POST['password']      ?? '';

    if (!$login || !$password) {
        $error = __t('login_error_empty');
    } else {
        $stmt = $db->prepare('SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->bind_param('ss', $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            header('Location: /SmartStock/dashboard.php');
            exit;
        } else {
            $error = __t('login_error_invalid');
        }
    }
}

$pageTitle = __t('login_title');
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo"><i class="bi bi-boxes"></i></div>
        <h1 class="auth-title"><?= __t('login_heading') ?></h1>
        <p class="auth-subtitle"><?= __t('login_subtitle') ?></p>

        <?php if ($error): ?>
        <div class="ss-alert ss-alert-danger" data-auto-dismiss>
            <i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'access_denied'): ?>
        <div class="ss-alert ss-alert-warning" data-auto-dismiss>
            <i class="bi bi-shield-exclamation"></i> <?= __t('login_access_denied') ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" class="auth-form" id="loginForm">
            <div class="mb-3">
                <label for="login-user" class="form-label"><?= __t('login_username_label') ?></label>
                <input type="text" id="login-user" name="login" class="form-control"
                       placeholder="<?= __t('login_username_placeholder') ?>" required
                       value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
            </div>
            <div class="mb-4">
                <label for="login-pass" class="form-label"><?= __t('login_password_label') ?></label>
                <input type="password" id="login-pass" name="password" class="form-control"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="auth-btn btn mb-3">
                <i class="bi bi-box-arrow-in-right me-1"></i> <?= __t('login_btn') ?>
            </button>
            <p class="text-center" style="color:#64748b;font-size:.85rem;margin:0;">
                <?= __t('login_no_account') ?>
                <a href="/SmartStock/auth/register.php" class="auth-link"><?= __t('login_register_link') ?></a>
            </p>
        </form>

        <!-- Demo credentials hint -->
        <div style="margin-top:1.5rem;padding:.75rem;background:rgba(99,102,241,.1);border-radius:8px;border:1px solid rgba(99,102,241,.2);">
            <p style="font-size:.72rem;color:#a5b4fc;margin:0;text-align:center;">
                <i class="bi bi-info-circle me-1"></i>
                <?= __t('login_demo') ?>: <strong>admin</strong> / <strong>password</strong>
            </p>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
