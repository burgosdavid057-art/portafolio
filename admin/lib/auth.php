<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function admin_session_start(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/admin/',
            'samesite' => 'Lax',
            'httponly' => true,
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        ]);
        session_name('admin_sess');
        session_start();
    }
}

function admin_is_setup(): bool {
    return admin_setting_get('password_hash') !== null;
}

function admin_set_password(string $plain): void {
    admin_setting_set('password_hash', password_hash($plain, PASSWORD_DEFAULT));
}

function admin_verify_password(string $plain): bool {
    $hash = admin_setting_get('password_hash');
    if (!$hash) return false;
    return password_verify($plain, $hash);
}

function admin_login(): void {
    $_SESSION['admin_authed'] = true;
    $_SESSION['admin_login_at'] = time();
    session_regenerate_id(true);
}

function admin_logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'] ?? '', $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function admin_is_authed(): bool {
    return !empty($_SESSION['admin_authed']);
}


function admin_require_auth(): void {
    admin_session_start();
    if (!admin_is_setup()) {
        redirect('/admin/setup.php');
    }
    if (!admin_is_authed()) {
        redirect('/admin/login.php');
    }
}
