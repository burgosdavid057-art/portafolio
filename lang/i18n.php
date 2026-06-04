<?php
/**
 * i18n bootstrap.
 *
 * Resolves the current language from (in priority order):
 *   1. ?lang= query param (also persists it as a cookie)
 *   2. lang= cookie set on a previous visit
 *   3. 'es' default
 *
 * Loads the matching translations file and returns it. Callers should:
 *
 *   require __DIR__ . '/lang/i18n.php';
 *   // exposed: $lang (string), $t (array)
 */
$supported = ['es', 'en'];

if (isset($_GET['lang']) && in_array($_GET['lang'], $supported, true)) {
    $lang = $_GET['lang'];
    // Persist for ~1 year so future visits don't need the query param
    setcookie('lang', $lang, [
        'expires'  => time() + 86400 * 365,
        'path'     => '/',
        'samesite' => 'Lax',
    ]);
} elseif (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], $supported, true)) {
    $lang = $_COOKIE['lang'];
} else {
    $lang = 'es';
}

$t_file = __DIR__ . "/{$lang}.php";
if (!is_file($t_file)) {
    $t_file = __DIR__ . '/es.php';
}
$t = require $t_file;

// Helper: build a URL pointing to the same page with the OTHER language.
// Preserves the existing query string except for `lang`.
function lang_switch_url(string $current_lang): string {
    $other = $current_lang === 'es' ? 'en' : 'es';
    $qs    = $_GET;
    $qs['lang'] = $other;
    return '?' . http_build_query($qs);
}
