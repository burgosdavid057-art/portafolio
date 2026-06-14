<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/layout.php';
admin_require_auth();

$db = admin_db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create') {
        $name   = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            redirect('/admin/projects.php', 'error', 'El nombre es obligatorio.');
        }
        $st = $db->prepare(
            'INSERT INTO projects (name, client, description, status, color)
             VALUES (?, ?, ?, ?, ?)'
        );
        $st->execute([
            $name,
            trim((string) ($_POST['client'] ?? '')) ?: null,
            trim((string) ($_POST['description'] ?? '')) ?: null,
            in_array($_POST['status'] ?? '', array_keys(PROJECT_STATUS_LABEL), true)
                ? $_POST['status'] : 'active',
            in_array($_POST['color'] ?? '', PROJECT_COLORS, true) ? $_POST['color'] : 'indigo',
        ]);
        $newId = (int) $db->lastInsertId();
        redirect('/admin/project.php?id=' . $newId, 'success', 'Proyecto creado.');
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            $db->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
            redirect('/admin/projects.php', 'success', 'Proyecto eliminado.');
        }
    }
}
$projects = $db->query(
    'SELECT p.*,
            (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id) AS task_count,
            (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id AND t.status = "done") AS done_count,
            (SELECT COALESCE(SUM(CASE WHEN f.type="income"  THEN f.amount ELSE 0 END), 0)
             FROM finances f WHERE f.project_id = p.id) AS income_total,
            (SELECT COALESCE(SUM(CASE WHEN f.type="expense" THEN f.amount ELSE 0 END), 0)
             FROM finances f WHERE f.project_id = p.id) AS expense_total
       FROM projects p
   ORDER BY CASE p.status WHEN "active" THEN 0 WHEN "paused" THEN 1 WHEN "done" THEN 2 ELSE 3 END,
            p.updated_at DESC'
)->fetchAll();

admin_layout_start('Proyectos', 'projects');
?>

<div class="flex items-end justify-between mb-6 flex-wrap gap-3">
    <p class="text-slate-400 text-sm">
        <?= count($projects) ?> proyecto<?= count($projects) === 1 ? '' : 's' ?> ·
        Tablero kanban, observaciones, finanzas y documentos por proyecto.
    </p>
    <button class="admin-btn-primary" data-open-modal="new-project">+ Nuevo proyecto</button>
</div>

<?php if (!$projects): ?>
    <div class="admin-card text-center py-16">
        <p class="text-slate-400">Aún no tienes proyectos.</p>
        <button class="admin-btn-primary mt-4" data-open-modal="new-project">Crear el primero</button>
    </div>
<?php else: ?>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($projects as $p):
            $pct = $p['task_count'] > 0 ? (int) round(($p['done_count'] / $p['task_count']) * 100) : 0;
            $balance = (float) $p['income_total'] - (float) $p['expense_total'];
        ?>
        <a href="/admin/project.php?id=<?= (int) $p['id'] ?>"
           class="admin-project-card admin-color-<?= e($p['color']) ?>">
            <div class="flex items-start justify-between gap-2">
                <h3 class="font-semibold text-base leading-snug"><?= e($p['name']) ?></h3>
                <span class="admin-chip admin-chip-<?= e($p['status']) ?>">
                    <?= e(PROJECT_STATUS_LABEL[$p['status']] ?? $p['status']) ?>
                </span>
            </div>
            <?php if ($p['client']): ?>
                <p class="text-xs text-slate-500 mt-1"><?= e($p['client']) ?></p>
            <?php endif; ?>
            <?php if ($p['description']): ?>
                <p class="text-sm text-slate-400 mt-3 line-clamp-2"><?= e($p['description']) ?></p>
            <?php endif; ?>
            <div class="mt-4 space-y-2">
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <span><?= (int) $p['done_count'] ?> / <?= (int) $p['task_count'] ?> tareas</span>
                    <div class="flex-1 h-1.5 bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-indigo-500 to-fuchsia-500"
                             style="width: <?= $pct ?>%"></div>
                    </div>
                    <span class="font-semibold text-slate-200"><?= $pct ?>%</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-emerald-400"><?= money((float) $p['income_total']) ?></span>
                    <span class="text-rose-400">-<?= money((float) $p['expense_total']) ?></span>
                    <span class="font-semibold <?= $balance >= 0 ? 'text-emerald-300' : 'text-rose-300' ?>">
                        <?= money($balance) ?>
                    </span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


<div class="admin-modal hidden" id="modal-new-project" data-modal="new-project">
    <div class="admin-modal-backdrop" data-close-modal></div>
    <div class="admin-modal-panel">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold">Nuevo proyecto</h2>
            <button data-close-modal class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
        </div>
        <form method="post" class="space-y-3">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="create">
            <label class="block">
                <span class="text-xs uppercase tracking-wide text-slate-400">Nombre <span class="text-rose-400">*</span></span>
                <input name="name" required maxlength="120" autofocus class="admin-input mt-1">
            </label>
            <label class="block">
                <span class="text-xs uppercase tracking-wide text-slate-400">Cliente</span>
                <input name="client" maxlength="120" class="admin-input mt-1">
            </label>
            <label class="block">
                <span class="text-xs uppercase tracking-wide text-slate-400">Descripción</span>
                <textarea name="description" rows="3" class="admin-input mt-1"></textarea>
            </label>
            <div class="grid grid-cols-2 gap-3">
                <label class="block">
                    <span class="text-xs uppercase tracking-wide text-slate-400">Estado</span>
                    <select name="status" class="admin-input mt-1">
                        <?php foreach (PROJECT_STATUS_LABEL as $k => $v): ?>
                            <option value="<?= e($k) ?>"><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs uppercase tracking-wide text-slate-400">Color</span>
                    <select name="color" class="admin-input mt-1">
                        <?php foreach (PROJECT_COLORS as $c): ?>
                            <option value="<?= e($c) ?>"><?= e(ucfirst($c)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" data-close-modal class="admin-btn-ghost">Cancelar</button>
                <button class="admin-btn-primary">Crear</button>
            </div>
        </form>
    </div>
</div>

<?php admin_layout_end(); ?>
