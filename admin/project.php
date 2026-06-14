<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/layout.php';
admin_require_auth();

$db = admin_db();
$id = (int) ($_GET['id'] ?? 0);

$project = null;
if ($id) {
    $st = $db->prepare('SELECT * FROM projects WHERE id = ?');
    $st->execute([$id]);
    $project = $st->fetch();
}
if (!$project) {
    redirect('/admin/projects.php', 'error', 'Proyecto no encontrado.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'edit') {
        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            redirect('/admin/project.php?id=' . $id, 'error', 'Nombre obligatorio.');
        }
        $db->prepare(
            'UPDATE projects
                SET name=?, client=?, description=?, status=?, color=?, updated_at=CURRENT_TIMESTAMP
              WHERE id=?'
        )->execute([
            $name,
            trim((string) ($_POST['client'] ?? '')) ?: null,
            trim((string) ($_POST['description'] ?? '')) ?: null,
            in_array($_POST['status'] ?? '', array_keys(PROJECT_STATUS_LABEL), true) ? $_POST['status'] : 'active',
            in_array($_POST['color'] ?? '', PROJECT_COLORS, true) ? $_POST['color'] : 'indigo',
            $id,
        ]);
        redirect('/admin/project.php?id=' . $id, 'success', 'Proyecto actualizado.');
    }

    if ($action === 'delete-project') {
        $db->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
        redirect('/admin/projects.php', 'success', 'Proyecto eliminado.');
    }
}
$tab = (string) ($_GET['tab'] ?? 'kanban');
if (!in_array($tab, ['kanban', 'obs', 'fin', 'docs'], true)) $tab = 'kanban';
$tasksStmt = $db->prepare(
    'SELECT * FROM tasks WHERE project_id = ? ORDER BY status, position, id'
);
$tasksStmt->execute([$id]);
$tasksByStatus = ['todo' => [], 'doing' => [], 'review' => [], 'done' => []];
foreach ($tasksStmt->fetchAll() as $t) {
    $tasksByStatus[$t['status']][] = $t;
}
$obs = [];
$financeRows = [];
$documents = [];
$incomeTotal = $expenseTotal = 0.0;

if ($tab === 'obs') {
    $st = $db->prepare(
        'SELECT o.*, t.title AS task_title
           FROM observations o
      LEFT JOIN tasks t ON t.id = o.task_id
          WHERE o.project_id = ?
       ORDER BY o.created_at DESC'
    );
    $st->execute([$id]);
    $obs = $st->fetchAll();
}
if ($tab === 'fin') {
    $st = $db->prepare('SELECT * FROM finances WHERE project_id = ? ORDER BY date DESC, id DESC');
    $st->execute([$id]);
    $financeRows = $st->fetchAll();
    foreach ($financeRows as $f) {
        if ($f['type'] === 'income')  $incomeTotal  += (float) $f['amount'];
        if ($f['type'] === 'expense') $expenseTotal += (float) $f['amount'];
    }
}
if ($tab === 'docs') {
    $st = $db->prepare('SELECT * FROM documents WHERE project_id = ? ORDER BY created_at DESC');
    $st->execute([$id]);
    $documents = $st->fetchAll();
}

admin_layout_start($project['name'], 'projects');
?>


<div class="flex flex-wrap items-start justify-between gap-3 mb-2">
    <div>
        <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
            <a href="/admin/projects.php" class="hover:text-white">← Proyectos</a>
            <span>·</span>
            <span class="admin-chip admin-chip-<?= e($project['status']) ?>">
                <?= e(PROJECT_STATUS_LABEL[$project['status']] ?? $project['status']) ?>
            </span>
            <?php if ($project['client']): ?>
                <span>·</span><span><?= e($project['client']) ?></span>
            <?php endif; ?>
        </div>
        <?php if ($project['description']): ?>
            <p class="text-slate-400 text-sm max-w-2xl"><?= nl2br(e($project['description'])) ?></p>
        <?php endif; ?>
    </div>
    <div class="flex gap-2">
        <button class="admin-btn-ghost" data-open-modal="edit-project">Editar</button>
        <form method="post" class="contents"
              onsubmit="return confirm('¿Eliminar el proyecto y TODOS sus datos? Esta acción no se puede deshacer.');">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="delete-project">
            <button class="admin-btn-danger">Eliminar</button>
        </form>
    </div>
</div>


<nav class="flex gap-1 mt-6 mb-5 border-b border-slate-800/80 -mx-6 px-6">
    <?php
        $tabs = [
            'kanban' => ['Kanban',        count(array_merge(...array_values($tasksByStatus)))],
            'obs'    => ['Observaciones', null],
            'fin'    => ['Finanzas',      null],
            'docs'   => ['Documentos',    null],
        ];
        foreach ($tabs as $k => [$label, $count]):
            $isActive = ($tab === $k);
    ?>
        <a href="?id=<?= $id ?>&tab=<?= e($k) ?>"
           class="admin-tab <?= $isActive ? 'is-active' : '' ?>">
            <?= e($label) ?>
            <?php if ($count !== null): ?>
                <span class="ml-1.5 text-xs text-slate-500"><?= $count ?></span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</nav>

<?php if ($tab === 'kanban'): ?>
    
    <div class="kanban-board" data-project-id="<?= $id ?>" data-csrf="<?= e(csrf_token()) ?>">
        <?php foreach (STATUS_ORDER as $col): ?>
            <div class="kanban-column" data-status="<?= e($col) ?>">
                <div class="kanban-column-head">
                    <span class="kanban-column-title"><?= e(STATUS_LABEL[$col]) ?></span>
                    <span class="kanban-column-count" data-count><?= count($tasksByStatus[$col]) ?></span>
                </div>
                <ul class="kanban-tasks" data-status="<?= e($col) ?>">
                    <?php foreach ($tasksByStatus[$col] as $task): ?>
                        <li class="kanban-task priority-<?= e($task['priority']) ?>"
                            data-task-id="<?= (int) $task['id'] ?>"
                            data-task='<?= e(json_encode($task)) ?>'>
                            <div class="kanban-task-title"><?= e($task['title']) ?></div>
                            <?php if ($task['description']): ?>
                                <div class="kanban-task-desc"><?= e(mb_strimwidth($task['description'], 0, 80, '…')) ?></div>
                            <?php endif; ?>
                            <div class="kanban-task-meta">
                                <span class="kanban-priority-dot"></span>
                                <span class="text-xs text-slate-500"><?= e(PRIORITY_LABEL[$task['priority']]) ?></span>
                                <?php if ($task['due_date']): ?>
                                    <span class="text-xs text-slate-500">· <?= e($task['due_date']) ?></span>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <button class="kanban-add-btn" data-add-task="<?= e($col) ?>">+ Tarea</button>
            </div>
        <?php endforeach; ?>
    </div>

    
    <div class="admin-modal hidden" id="modal-task" data-modal="task">
        <div class="admin-modal-backdrop" data-close-modal></div>
        <div class="admin-modal-panel">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold" data-task-modal-title>Nueva tarea</h2>
                <button data-close-modal class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
            </div>
            <form id="task-form" class="space-y-3">
                <input type="hidden" name="id">
                <input type="hidden" name="status" value="todo">
                <label class="block">
                    <span class="text-xs uppercase tracking-wide text-slate-400">Título <span class="text-rose-400">*</span></span>
                    <input name="title" required maxlength="200" class="admin-input mt-1">
                </label>
                <label class="block">
                    <span class="text-xs uppercase tracking-wide text-slate-400">Descripción</span>
                    <textarea name="description" rows="3" class="admin-input mt-1"></textarea>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="block">
                        <span class="text-xs uppercase tracking-wide text-slate-400">Prioridad</span>
                        <select name="priority" class="admin-input mt-1">
                            <option value="low">Baja</option>
                            <option value="medium" selected>Media</option>
                            <option value="high">Alta</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs uppercase tracking-wide text-slate-400">Vence</span>
                        <input type="date" name="due_date" class="admin-input mt-1">
                    </label>
                </div>
                <div class="flex items-center justify-between pt-2">
                    <button type="button" data-task-delete class="admin-btn-danger hidden">Eliminar</button>
                    <div class="flex gap-2 ml-auto">
                        <button type="button" data-close-modal class="admin-btn-ghost">Cancelar</button>
                        <button class="admin-btn-primary">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

<?php elseif ($tab === 'obs'): ?>
    
    <form method="post" action="/admin/api.php" class="admin-card space-y-3 mb-6">
        <?= csrf_input() ?>
        <input type="hidden" name="action" value="obs-create">
        <input type="hidden" name="project_id" value="<?= $id ?>">
        <input type="hidden" name="redirect" value="/admin/project.php?id=<?= $id ?>&tab=obs">
        <label class="block">
            <span class="text-xs uppercase tracking-wide text-slate-400">Nueva observación</span>
            <textarea name="body" rows="3" required maxlength="2000" placeholder="Notas, decisiones, riesgos…" class="admin-input mt-1"></textarea>
        </label>
        <div class="flex justify-end">
            <button class="admin-btn-primary">Agregar</button>
        </div>
    </form>

    <?php if (!$obs): ?>
        <div class="admin-card text-center py-10 text-slate-500">Aún no hay observaciones.</div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($obs as $o): ?>
                <div class="admin-card">
                    <div class="flex items-start justify-between gap-3">
                        <div class="text-xs text-slate-500">
                            <?= e(ago($o['created_at'])) ?>
                            <?php if ($o['task_title']): ?>
                                · Tarea: <span class="text-slate-300"><?= e($o['task_title']) ?></span>
                            <?php endif; ?>
                        </div>
                        <form method="post" action="/admin/api.php" class="contents"
                              onsubmit="return confirm('¿Eliminar esta observación?');">
                            <?= csrf_input() ?>
                            <input type="hidden" name="action" value="obs-delete">
                            <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                            <input type="hidden" name="redirect" value="/admin/project.php?id=<?= $id ?>&tab=obs">
                            <button class="text-xs text-slate-500 hover:text-rose-400">Eliminar</button>
                        </form>
                    </div>
                    <p class="mt-2 text-sm text-slate-200 whitespace-pre-wrap"><?= e($o['body']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php elseif ($tab === 'fin'): ?>
    
    <?php $balance = $incomeTotal - $expenseTotal; ?>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="admin-card">
            <p class="text-xs uppercase tracking-wide text-slate-400">Ingresos</p>
            <p class="text-2xl font-bold text-emerald-400 mt-1"><?= money($incomeTotal) ?></p>
        </div>
        <div class="admin-card">
            <p class="text-xs uppercase tracking-wide text-slate-400">Gastos</p>
            <p class="text-2xl font-bold text-rose-400 mt-1">-<?= money($expenseTotal) ?></p>
        </div>
        <div class="admin-card">
            <p class="text-xs uppercase tracking-wide text-slate-400">Balance</p>
            <p class="text-2xl font-bold <?= $balance >= 0 ? 'text-emerald-300' : 'text-rose-300' ?> mt-1">
                <?= money($balance) ?>
            </p>
        </div>
    </div>

    <form method="post" action="/admin/api.php" class="admin-card mb-6">
        <?= csrf_input() ?>
        <input type="hidden" name="action" value="fin-create">
        <input type="hidden" name="project_id" value="<?= $id ?>">
        <input type="hidden" name="redirect" value="/admin/project.php?id=<?= $id ?>&tab=fin">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <label class="block">
                <span class="text-xs uppercase tracking-wide text-slate-400">Tipo</span>
                <select name="type" class="admin-input mt-1">
                    <option value="income">Ingreso</option>
                    <option value="expense">Gasto</option>
                </select>
            </label>
            <label class="block">
                <span class="text-xs uppercase tracking-wide text-slate-400">Monto</span>
                <input type="number" step="0.01" min="0" name="amount" required class="admin-input mt-1">
            </label>
            <label class="block">
                <span class="text-xs uppercase tracking-wide text-slate-400">Moneda</span>
                <select name="currency" class="admin-input mt-1">
                    <option>COP</option><option>USD</option><option>EUR</option>
                </select>
            </label>
            <label class="block">
                <span class="text-xs uppercase tracking-wide text-slate-400">Fecha</span>
                <input type="date" name="date" required value="<?= e(date('Y-m-d')) ?>" class="admin-input mt-1">
            </label>
            <label class="block col-span-2 md:col-span-1">
                <span class="text-xs uppercase tracking-wide text-slate-400">&nbsp;</span>
                <button class="admin-btn-primary mt-1 w-full">Agregar</button>
            </label>
        </div>
        <label class="block mt-3">
            <span class="text-xs uppercase tracking-wide text-slate-400">Descripción</span>
            <input name="description" maxlength="200" class="admin-input mt-1" placeholder="Anticipo cliente, hosting mensual, etc.">
        </label>
    </form>

    <?php if (!$financeRows): ?>
        <div class="admin-card text-center py-10 text-slate-500">Aún no hay movimientos.</div>
    <?php else: ?>
        <div class="admin-card overflow-x-auto p-0">
            <table class="w-full text-sm">
                <thead class="text-xs uppercase text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="text-left p-3">Fecha</th>
                        <th class="text-left p-3">Tipo</th>
                        <th class="text-left p-3">Descripción</th>
                        <th class="text-right p-3">Monto</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($financeRows as $f): ?>
                    <tr class="border-b border-slate-800/60 last:border-0">
                        <td class="p-3 text-slate-300"><?= e($f['date']) ?></td>
                        <td class="p-3">
                            <span class="admin-chip <?= $f['type'] === 'income' ? 'admin-chip-active' : 'admin-chip-paused' ?>">
                                <?= $f['type'] === 'income' ? 'Ingreso' : 'Gasto' ?>
                            </span>
                        </td>
                        <td class="p-3 text-slate-300"><?= e($f['description']) ?></td>
                        <td class="p-3 text-right font-semibold <?= $f['type'] === 'income' ? 'text-emerald-400' : 'text-rose-400' ?>">
                            <?= $f['type'] === 'income' ? '' : '-' ?><?= money((float) $f['amount'], $f['currency']) ?>
                        </td>
                        <td class="p-3 text-right">
                            <form method="post" action="/admin/api.php" class="contents"
                                  onsubmit="return confirm('¿Eliminar movimiento?');">
                                <?= csrf_input() ?>
                                <input type="hidden" name="action" value="fin-delete">
                                <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                                <input type="hidden" name="redirect" value="/admin/project.php?id=<?= $id ?>&tab=fin">
                                <button class="text-xs text-slate-500 hover:text-rose-400">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

<?php elseif ($tab === 'docs'): ?>
    
    <form method="post" action="/admin/api.php" enctype="multipart/form-data" class="admin-card mb-6">
        <?= csrf_input() ?>
        <input type="hidden" name="action" value="doc-upload">
        <input type="hidden" name="project_id" value="<?= $id ?>">
        <input type="hidden" name="redirect" value="/admin/project.php?id=<?= $id ?>&tab=docs">
        <div class="grid sm:grid-cols-2 gap-3">
            <label class="block">
                <span class="text-xs uppercase tracking-wide text-slate-400">Archivo</span>
                <input type="file" name="file" required class="admin-input mt-1 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-500 file:text-white file:px-3 file:py-1.5 file:text-xs file:cursor-pointer">
            </label>
            <label class="block">
                <span class="text-xs uppercase tracking-wide text-slate-400">Notas (opcional)</span>
                <input name="notes" maxlength="200" class="admin-input mt-1">
            </label>
        </div>
        <div class="mt-3 flex items-center justify-between">
            <p class="text-xs text-slate-500">Max 20 MB · PDFs, imágenes, .zip, .docx, etc.</p>
            <button class="admin-btn-primary">Subir</button>
        </div>
    </form>

    <?php if (!$documents): ?>
        <div class="admin-card text-center py-10 text-slate-500">Aún no hay documentos.</div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <?php foreach ($documents as $d): ?>
                <div class="admin-card flex items-start gap-3">
                    <div class="w-10 h-10 shrink-0 rounded-md bg-slate-800 flex items-center justify-center text-slate-300 text-xs uppercase">
                        <?= e(pathinfo($d['original_name'], PATHINFO_EXTENSION) ?: 'FILE') ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <a href="/admin/api.php?action=doc-serve&id=<?= (int) $d['id'] ?>"
                           target="_blank" rel="noopener"
                           class="font-medium text-slate-100 hover:text-indigo-300 break-all">
                            <?= e($d['original_name']) ?>
                        </a>
                        <p class="text-xs text-slate-500 mt-0.5">
                            <?= number_format((int) $d['size'] / 1024, 1) ?> KB · <?= e(ago($d['created_at'])) ?>
                        </p>
                        <?php if ($d['notes']): ?>
                            <p class="text-xs text-slate-400 mt-1"><?= e($d['notes']) ?></p>
                        <?php endif; ?>
                    </div>
                    <form method="post" action="/admin/api.php" class="contents"
                          onsubmit="return confirm('¿Eliminar archivo?');">
                        <?= csrf_input() ?>
                        <input type="hidden" name="action" value="doc-delete">
                        <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                        <input type="hidden" name="redirect" value="/admin/project.php?id=<?= $id ?>&tab=docs">
                        <button class="text-xs text-slate-500 hover:text-rose-400">Eliminar</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>


<div class="admin-modal hidden" id="modal-edit-project" data-modal="edit-project">
    <div class="admin-modal-backdrop" data-close-modal></div>
    <div class="admin-modal-panel">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold">Editar proyecto</h2>
            <button data-close-modal class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
        </div>
        <form method="post" class="space-y-3">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="edit">
            <label class="block">
                <span class="text-xs uppercase tracking-wide text-slate-400">Nombre</span>
                <input name="name" required maxlength="120" value="<?= e($project['name']) ?>" class="admin-input mt-1">
            </label>
            <label class="block">
                <span class="text-xs uppercase tracking-wide text-slate-400">Cliente</span>
                <input name="client" maxlength="120" value="<?= e($project['client']) ?>" class="admin-input mt-1">
            </label>
            <label class="block">
                <span class="text-xs uppercase tracking-wide text-slate-400">Descripción</span>
                <textarea name="description" rows="3" class="admin-input mt-1"><?= e($project['description']) ?></textarea>
            </label>
            <div class="grid grid-cols-2 gap-3">
                <label class="block">
                    <span class="text-xs uppercase tracking-wide text-slate-400">Estado</span>
                    <select name="status" class="admin-input mt-1">
                        <?php foreach (PROJECT_STATUS_LABEL as $k => $v): ?>
                            <option value="<?= e($k) ?>" <?= $project['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs uppercase tracking-wide text-slate-400">Color</span>
                    <select name="color" class="admin-input mt-1">
                        <?php foreach (PROJECT_COLORS as $c): ?>
                            <option value="<?= e($c) ?>" <?= $project['color'] === $c ? 'selected' : '' ?>><?= e(ucfirst($c)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" data-close-modal class="admin-btn-ghost">Cancelar</button>
                <button class="admin-btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<?php admin_layout_end(); ?>
