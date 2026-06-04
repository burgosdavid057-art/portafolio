<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
admin_require_auth();

$db = admin_db();
$action = (string) ($_REQUEST['action'] ?? '');

/** Send a JSON response and exit. */
function json_out($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/** Either redirect back (form-based) or return JSON (AJAX). */
function done(string $type, string $msg, ?array $jsonExtra = null): void {
    $redir = (string) ($_REQUEST['redirect'] ?? '');
    if ($redir && str_starts_with($redir, '/admin/')) {
        redirect($redir, $type, $msg);
    }
    $resp = ['ok' => $type === 'success', 'msg' => $msg];
    if ($jsonExtra) $resp += $jsonExtra;
    json_out($resp);
}

// All mutating actions require CSRF
csrf_check();

switch ($action) {

    /* ========== TASKS ========== */

    case 'task-create': {
        $pid = (int) ($_REQUEST['project_id'] ?? 0);
        $title = trim((string) ($_REQUEST['title'] ?? ''));
        if (!$pid || $title === '') json_out(['ok' => false, 'msg' => 'Datos inválidos'], 400);

        $status = in_array($_REQUEST['status'] ?? '', STATUS_ORDER, true) ? $_REQUEST['status'] : 'todo';
        $priority = in_array($_REQUEST['priority'] ?? '', array_keys(PRIORITY_LABEL), true) ? $_REQUEST['priority'] : 'medium';

        // New tasks go to the end of their column
        $pos = (int) $db->query("SELECT COALESCE(MAX(position),-1)+1 FROM tasks
                                  WHERE project_id={$pid} AND status='" . str_replace("'", '', $status) . "'")->fetchColumn();

        $st = $db->prepare(
            'INSERT INTO tasks (project_id, title, description, status, priority, position, due_date)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([
            $pid, $title,
            trim((string) ($_REQUEST['description'] ?? '')) ?: null,
            $status, $priority, $pos,
            (string) ($_REQUEST['due_date'] ?? '') ?: null,
        ]);
        $id = (int) $db->lastInsertId();
        $row = $db->query("SELECT * FROM tasks WHERE id={$id}")->fetch();
        json_out(['ok' => true, 'task' => $row]);
    }

    case 'task-update': {
        $id = (int) ($_REQUEST['id'] ?? 0);
        if (!$id) json_out(['ok' => false, 'msg' => 'Falta id'], 400);
        $title = trim((string) ($_REQUEST['title'] ?? ''));
        if ($title === '') json_out(['ok' => false, 'msg' => 'Título obligatorio'], 400);

        $priority = in_array($_REQUEST['priority'] ?? '', array_keys(PRIORITY_LABEL), true) ? $_REQUEST['priority'] : 'medium';

        $db->prepare(
            'UPDATE tasks
                SET title=?, description=?, priority=?, due_date=?, updated_at=CURRENT_TIMESTAMP
              WHERE id=?'
        )->execute([
            $title,
            trim((string) ($_REQUEST['description'] ?? '')) ?: null,
            $priority,
            (string) ($_REQUEST['due_date'] ?? '') ?: null,
            $id,
        ]);
        $row = $db->query("SELECT * FROM tasks WHERE id={$id}")->fetch();
        json_out(['ok' => true, 'task' => $row]);
    }

    case 'task-move': {
        // Drag-drop: reorder + possibly change column.
        // Payload: { id, status, position, project_id }
        $id  = (int) ($_REQUEST['id'] ?? 0);
        $newStatus = in_array($_REQUEST['status'] ?? '', STATUS_ORDER, true) ? $_REQUEST['status'] : null;
        $newPos    = max(0, (int) ($_REQUEST['position'] ?? 0));
        if (!$id || !$newStatus) json_out(['ok' => false, 'msg' => 'Datos inválidos'], 400);

        $row = $db->query("SELECT project_id, status, position FROM tasks WHERE id={$id}")->fetch();
        if (!$row) json_out(['ok' => false, 'msg' => 'No existe'], 404);
        $pid = (int) $row['project_id'];

        $db->beginTransaction();
        try {
            // Take the task out of its old column ordering
            $db->prepare(
                'UPDATE tasks SET position = position - 1
                  WHERE project_id=? AND status=? AND position > ?'
            )->execute([$pid, $row['status'], $row['position']]);

            // Make room in the new column at newPos
            $db->prepare(
                'UPDATE tasks SET position = position + 1
                  WHERE project_id=? AND status=? AND position >= ? AND id <> ?'
            )->execute([$pid, $newStatus, $newPos, $id]);

            $db->prepare(
                'UPDATE tasks SET status=?, position=?, updated_at=CURRENT_TIMESTAMP WHERE id=?'
            )->execute([$newStatus, $newPos, $id]);

            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            json_out(['ok' => false, 'msg' => 'DB error'], 500);
        }
        json_out(['ok' => true]);
    }

    case 'task-delete': {
        $id = (int) ($_REQUEST['id'] ?? 0);
        if (!$id) json_out(['ok' => false, 'msg' => 'Falta id'], 400);
        $db->prepare('DELETE FROM tasks WHERE id = ?')->execute([$id]);
        json_out(['ok' => true]);
    }

    /* ========== OBSERVATIONS ========== */

    case 'obs-create': {
        $pid = (int) ($_REQUEST['project_id'] ?? 0);
        $body = trim((string) ($_REQUEST['body'] ?? ''));
        if (!$pid || $body === '') done('error', 'Datos inválidos');
        $tid = (int) ($_REQUEST['task_id'] ?? 0) ?: null;
        $db->prepare(
            'INSERT INTO observations (project_id, task_id, body) VALUES (?, ?, ?)'
        )->execute([$pid, $tid, $body]);
        done('success', 'Observación agregada.');
    }

    case 'obs-delete': {
        $id = (int) ($_REQUEST['id'] ?? 0);
        if ($id) $db->prepare('DELETE FROM observations WHERE id = ?')->execute([$id]);
        done('success', 'Observación eliminada.');
    }

    /* ========== FINANCES ========== */

    case 'fin-create': {
        $pid = (int) ($_REQUEST['project_id'] ?? 0);
        $type = (string) ($_REQUEST['type'] ?? '');
        $amount = (float) ($_REQUEST['amount'] ?? 0);
        $date = (string) ($_REQUEST['date'] ?? '');
        if (!$pid || !in_array($type, ['income', 'expense'], true) || $amount <= 0 || $date === '') {
            done('error', 'Datos inválidos');
        }
        $currency = strtoupper((string) ($_REQUEST['currency'] ?? 'COP'));
        if (!preg_match('/^[A-Z]{3}$/', $currency)) $currency = 'COP';

        $db->prepare(
            'INSERT INTO finances (project_id, type, amount, currency, description, date)
             VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([
            $pid, $type, $amount, $currency,
            trim((string) ($_REQUEST['description'] ?? '')) ?: null,
            $date,
        ]);
        done('success', 'Movimiento registrado.');
    }

    case 'fin-delete': {
        $id = (int) ($_REQUEST['id'] ?? 0);
        if ($id) $db->prepare('DELETE FROM finances WHERE id = ?')->execute([$id]);
        done('success', 'Movimiento eliminado.');
    }

    /* ========== DOCUMENTS ========== */

    case 'doc-upload': {
        $pid = (int) ($_REQUEST['project_id'] ?? 0);
        if (!$pid) done('error', 'Proyecto inválido');
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            done('error', 'Error al subir el archivo.');
        }
        $f = $_FILES['file'];
        if ($f['size'] > 20 * 1024 * 1024) done('error', 'El archivo supera 20 MB.');

        $orig = basename((string) $f['name']);
        $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        // Soft denylist for obviously dangerous types
        if (in_array($ext, ['php', 'phtml', 'phar', 'html', 'htm', 'js', 'exe', 'bat', 'sh'], true)) {
            done('error', 'Extensión no permitida (' . htmlspecialchars($ext) . ').');
        }

        $dir = admin_uploads_dir() . '/' . $pid;
        if (!is_dir($dir)) mkdir($dir, 0750, true);

        $stored = bin2hex(random_bytes(8)) . ($ext ? '.' . $ext : '');
        $dest   = $dir . '/' . $stored;
        if (!move_uploaded_file($f['tmp_name'], $dest)) {
            done('error', 'No se pudo guardar el archivo.');
        }

        $db->prepare(
            'INSERT INTO documents (project_id, stored_name, original_name, mime_type, size, notes)
             VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([
            $pid, $stored, $orig,
            (string) ($f['type'] ?? ''),
            (int) $f['size'],
            trim((string) ($_REQUEST['notes'] ?? '')) ?: null,
        ]);
        done('success', 'Documento subido.');
    }

    case 'doc-delete': {
        $id = (int) ($_REQUEST['id'] ?? 0);
        $row = $db->query("SELECT project_id, stored_name FROM documents WHERE id={$id}")->fetch();
        if ($row) {
            $path = admin_uploads_dir() . '/' . $row['project_id'] . '/' . $row['stored_name'];
            if (is_file($path)) @unlink($path);
            $db->prepare('DELETE FROM documents WHERE id = ?')->execute([$id]);
        }
        done('success', 'Documento eliminado.');
    }

    case 'doc-serve': {
        // Auth-gated file serving — files live OUTSIDE httpdocs, so PHP streams them.
        $id = (int) ($_REQUEST['id'] ?? 0);
        $row = $db->query("SELECT * FROM documents WHERE id={$id}")->fetch();
        if (!$row) { http_response_code(404); exit('Not found'); }
        $path = admin_uploads_dir() . '/' . $row['project_id'] . '/' . $row['stored_name'];
        if (!is_file($path)) { http_response_code(404); exit('Missing'); }

        header('Content-Type: ' . ($row['mime_type'] ?: 'application/octet-stream'));
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: inline; filename="' . addslashes($row['original_name']) . '"');
        readfile($path);
        exit;
    }

    default:
        json_out(['ok' => false, 'msg' => 'Acción desconocida'], 400);
}
