<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';

admin_session_start();

if (admin_is_setup()) {
    redirect('/admin/login.php');
}

$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pw  = (string) ($_POST['password']  ?? '');
    $pw2 = (string) ($_POST['password2'] ?? '');
    if (strlen($pw) < 8) {
        $err = 'La contraseña debe tener mínimo 8 caracteres.';
    } elseif ($pw !== $pw2) {
        $err = 'Las contraseñas no coinciden.';
    } else {
        admin_set_password($pw);
        admin_login();
        redirect('/admin/projects.php', 'success', 'Cuenta creada. ¡Bienvenido!');
    }
}
?><!DOCTYPE html>
<html lang="es" class="dark">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="robots" content="noindex,nofollow" />
<title>Setup · Admin</title>
<link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg" />
<link rel="stylesheet" href="/assets/css/tailwind.css">
<link rel="stylesheet" href="/admin/assets/admin.css">
</head>
<body class="bg-[#0a0a10] text-slate-100 font-sans min-h-screen grid place-items-center px-4">
<div class="w-full max-w-sm">
    <div class="flex items-center gap-2 font-bold text-lg mb-1 justify-center">
        <span class="inline-block w-7 h-7 rounded-md bg-gradient-to-br from-indigo-500 to-fuchsia-500"></span>
        <span>admin<span class="text-indigo-400">.</span></span>
    </div>
    <p class="text-center text-slate-400 text-sm mb-6">Primera vez — define tu contraseña.</p>
    <form method="post" class="admin-card space-y-4">
        <?= csrf_input() ?>
        <?php if ($err): ?><div class="admin-flash admin-flash-error"><?= e($err) ?></div><?php endif; ?>
        <label class="block">
            <span class="text-xs uppercase tracking-wide text-slate-400">Contraseña</span>
            <input name="password" type="password" required minlength="8" autofocus class="admin-input mt-1">
        </label>
        <label class="block">
            <span class="text-xs uppercase tracking-wide text-slate-400">Repetir contraseña</span>
            <input name="password2" type="password" required minlength="8" class="admin-input mt-1">
        </label>
        <button class="admin-btn-primary w-full">Crear cuenta</button>
    </form>
</div>
</body>
</html>
