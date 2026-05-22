<?php
// ============================================================
// SmartStock – Language Loader & Helper
// ============================================================

/**
 * Initialise the language system.
 * Must be called after session_start() (auth.php handles that).
 * Switches language when ?lang=xx is in the URL and stores in session.
 */
function lang_init(): void
{
    $supported = ['en', 'tr'];

    // Handle switcher: ?lang=tr or ?lang=en
    if (isset($_GET['lang']) && in_array($_GET['lang'], $supported, true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }

    // Default to 'en' if not set
    if (!isset($_SESSION['lang'])) {
        $_SESSION['lang'] = 'en';
    }

    $file = __DIR__ . '/' . $_SESSION['lang'] . '.php';
    if (!file_exists($file)) {
        $file = __DIR__ . '/en.php'; // fallback
    }

    $GLOBALS['_LANG'] = require $file;
}

/**
 * Translate a key. Supports sprintf-style placeholders.
 *
 * @param  string $key
 * @param  mixed  ...$args  Optional sprintf arguments
 * @return string
 */
function __t(string $key, ...$args): string
{
    $str = $GLOBALS['_LANG'][$key] ?? $key; // fall back to the key itself
    return $args ? sprintf($str, ...$args) : $str;
}

/**
 * Return the currently active language code ('en' or 'tr').
 */
function current_lang(): string
{
    return $_SESSION['lang'] ?? 'en';
}

/**
 * Build a URL that switches the language, preserving the current path & query.
 */
function lang_url(string $lang): string
{
    $params = $_GET;
    $params['lang'] = $lang;
    return '?' . http_build_query($params);
}
