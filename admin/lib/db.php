<?php
/**
 * SQLite connection + schema bootstrap.
 *
 * The DB file and uploads live OUTSIDE httpdocs/ — at the user's home,
 * one level above the web root — so they're never directly downloadable.
 *
 *   /var/www/vhosts/davidburgos.dev/
 *   ├── httpdocs/
 *   │   └── admin/  ← code
 *   └── admin-data/ ← created on first run
 *       ├── admin.sqlite
 *       └── uploads/<project_id>/<file>
 */
declare(strict_types=1);

function admin_data_dir(): string {
    // From admin/lib/db.php: dirname(__DIR__, 3) = home dir (above httpdocs/).
    $dir = dirname(__DIR__, 3) . '/admin-data';
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    return $dir;
}

function admin_uploads_dir(): string {
    $dir = admin_data_dir() . '/uploads';
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    return $dir;
}

function admin_db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $path = admin_data_dir() . '/admin.sqlite';
    $isNew = !file_exists($path);

    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');

    if ($isNew) {
        admin_db_schema($pdo);
    }
    return $pdo;
}

function admin_db_schema(PDO $pdo): void {
    $pdo->exec(<<<SQL
        CREATE TABLE IF NOT EXISTS settings (
            key   TEXT PRIMARY KEY,
            value TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS projects (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            name        TEXT NOT NULL,
            client      TEXT,
            description TEXT,
            status      TEXT NOT NULL DEFAULT 'active',
            color       TEXT NOT NULL DEFAULT 'indigo',
            created_at  TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at  TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS tasks (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            project_id  INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
            title       TEXT NOT NULL,
            description TEXT,
            status      TEXT NOT NULL DEFAULT 'todo',
            priority    TEXT NOT NULL DEFAULT 'medium',
            position    INTEGER NOT NULL DEFAULT 0,
            due_date    TEXT,
            created_at  TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at  TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        CREATE INDEX IF NOT EXISTS idx_tasks_project ON tasks(project_id, status, position);

        CREATE TABLE IF NOT EXISTS observations (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
            task_id    INTEGER REFERENCES tasks(id) ON DELETE CASCADE,
            body       TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS finances (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            project_id  INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
            type        TEXT NOT NULL,
            amount      REAL NOT NULL,
            currency    TEXT NOT NULL DEFAULT 'COP',
            description TEXT,
            date        TEXT NOT NULL,
            created_at  TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS documents (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            project_id    INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
            stored_name   TEXT NOT NULL,
            original_name TEXT NOT NULL,
            mime_type     TEXT,
            size          INTEGER,
            notes         TEXT,
            created_at    TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
SQL);
}

function admin_setting_get(string $key): ?string {
    $st = admin_db()->prepare('SELECT value FROM settings WHERE key = ?');
    $st->execute([$key]);
    $v = $st->fetchColumn();
    return $v === false ? null : $v;
}

function admin_setting_set(string $key, string $value): void {
    $st = admin_db()->prepare(
        'INSERT INTO settings (key, value) VALUES (?, ?)
         ON CONFLICT(key) DO UPDATE SET value = excluded.value'
    );
    $st->execute([$key, $value]);
}
