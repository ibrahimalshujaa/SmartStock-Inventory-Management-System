<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $del = $db->prepare('DELETE FROM products WHERE id = ?');
        $del->bind_param('i', $id);
        $del->execute();
    }
}
header('Location: /SmartStock/products/index.php');
exit;
