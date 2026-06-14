<?php

$supported = ['es', 'en'];

if (isset($_GET['lang']) && in_array($_GET['lang'], $supported, true)) {
    $lang = $_GET['lang'];
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
function lang_switch_url(string $current_lang): string {
    $other = $current_lang === 'es' ? 'en' : 'es';
    $qs    = $_GET;
    $qs['lang'] = $other;
    return '?' . http_build_query($qs);
}
