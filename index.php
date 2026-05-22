<?php
// Root index – redirect to login or dashboard
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) {
    header('Location: /SmartStock/dashboard.php');
} else {
    header('Location: /SmartStock/auth/login.php');
}
exit;
