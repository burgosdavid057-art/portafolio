<?php
declare(strict_types=1);

function admin_layout_start(string $title, string $active = ''): void {
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex,nofollow" />
    <title><?= e($title) ?> · Admin · davidburgos.dev</title>
    <link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg" />
    <?php
        $asset = function (string $rel): string {
            $abs = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($rel, '/');
            $v   = is_file($abs) ? filemtime($abs) : time();
            return '/' . ltrim($rel, '/') . '?v=' . $v;
        };
    ?>
    <link rel="stylesheet" href="<?= e($asset('assets/css/tailwind.css')) ?>">
    <link rel="stylesheet" href="<?= e($asset('admin/assets/admin.css')) ?>">
</head>
<body class="bg-[#0a0a10] text-slate-100 font-sans antialiased min-h-screen">
<div class="flex min-h-screen">
    
    <aside class="admin-sidebar">
        <a href="/admin/" class="block px-4 pt-5 pb-6">
            <div class="flex items-center gap-2 font-bold text-lg">
                <span class="inline-block w-7 h-7 rounded-md bg-gradient-to-br from-indigo-500 to-fuchsia-500"></span>
                <span>admin<span class="text-indigo-400">.</span></span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">davidburgos.dev</p>
        </a>
        <nav class="px-2 space-y-0.5">
            <a href="/admin/projects.php" class="admin-nav-link <?= $active === 'projects' ? 'is-active' : '' ?>">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                <span>Proyectos</span>
            </a>
            <a href="/" class="admin-nav-link" target="_blank" rel="noopener">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7 0l4-4a5 5 0 0 0-7-7l-1 1"></path><path d="M14 11a5 5 0 0 0-7 0l-4 4a5 5 0 0 0 7 7l1-1"></path></svg>
                <span>Ver sitio</span>
            </a>
        </nav>
        <div class="mt-auto px-2 pb-4">
            <form method="post" action="/admin/logout.php">
                <?= csrf_input() ?>
                <button class="admin-nav-link w-full text-left text-rose-400 hover:text-rose-300">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    <span>Cerrar sesión</span>
                </button>
            </form>
        </div>
    </aside>

    
    <main class="flex-1 min-w-0">
        <header class="admin-topbar">
            <h1 class="text-xl font-bold tracking-tight"><?= e($title) ?></h1>
        </header>

        <div class="px-6 py-6 max-w-7xl">
        <?php foreach (flash_pop() as $f): ?>
            <div class="admin-flash admin-flash-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
        <?php endforeach; ?>
<?php }

function admin_layout_end(): void {
?>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js" defer></script>
<?php
    $abs = $_SERVER['DOCUMENT_ROOT'] . '/admin/assets/admin.js';
    $v = is_file($abs) ? filemtime($abs) : time();
?>
<script src="/admin/assets/admin.js?v=<?= $v ?>" defer></script>
</body>
</html>
<?php }
