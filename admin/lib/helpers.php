<?php
declare(strict_types=1);

function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): void {
    $sent = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) $sent)) {
        http_response_code(419);
        exit('CSRF token mismatch');
    }
}

function csrf_input(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function flash_set(string $type, string $msg): void {
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function flash_pop(): array {
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function redirect(string $url, string $flash_type = '', string $flash_msg = ''): void {
    if ($flash_msg !== '') flash_set($flash_type, $flash_msg);
    header('Location: ' . $url);
    exit;
}

function money(float $amount, string $currency = 'COP'): string {
    $fmt = number_format($amount, 0, ',', '.');
    return $currency === 'COP' ? "$ {$fmt}" : "{$currency} " . number_format($amount, 2, '.', ',');
}

function ago(string $ts): string {
    $t = strtotime($ts);
    if ($t === false) return $ts;
    $d = time() - $t;
    if ($d < 60) return 'hace un momento';
    if ($d < 3600) return 'hace ' . (int)($d / 60) . ' min';
    if ($d < 86400) return 'hace ' . (int)($d / 3600) . ' h';
    if ($d < 604800) return 'hace ' . (int)($d / 86400) . ' días';
    return date('d M Y', $t);
}

const PRIORITY_LABEL = ['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta'];
const STATUS_LABEL   = [
    'todo'   => 'Por hacer',
    'doing'  => 'Haciendo',
    'review' => 'Revisión',
    'done'   => 'Hecho',
];
const STATUS_ORDER   = ['todo', 'doing', 'review', 'done'];
const PROJECT_STATUS_LABEL = [
    'active'   => 'Activo',
    'paused'   => 'En pausa',
    'done'     => 'Terminado',
    'archived' => 'Archivado',
];
const PROJECT_COLORS = ['indigo','emerald','amber','rose','sky','violet','fuchsia','cyan'];
