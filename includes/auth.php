<?php
// ============================================================
// Authentication Helper
// ============================================================
session_start();

// Bootstrap i18n (must run after session_start)
require_once __DIR__ . '/../lang/lang.php';
lang_init();

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /SmartStock/auth/login.php');
        exit;
    }
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isUser(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

/**
 * Require the user to be logged in as admin.
 * Non-admins are redirected to the dashboard with an access_denied error.
 */
function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /SmartStock/dashboard.php?error=access_denied');
        exit;
    }
}

/**
 * Require the user to be logged in (any role).
 * Alias kept for clarity; identical to requireLogin().
 */
function requireUser(): void {
    requireLogin();
}

function currentUserId(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

function currentUsername(): string {
    return htmlspecialchars($_SESSION['username'] ?? '');
}

function currentRole(): string {
    return $_SESSION['role'] ?? 'user';
}
