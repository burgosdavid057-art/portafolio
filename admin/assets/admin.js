/* ===== Admin frontend — modals + kanban drag-drop ===== */
(() => {
    'use strict';

    /* ---------- Generic modal handler ---------- */
    const openModal = (name) => {
        const m = document.querySelector(`[data-modal="${name}"]`);
        if (!m) return;
        m.classList.remove('hidden');
        const focusable = m.querySelector('input,textarea,select,button');
        focusable && focusable.focus();
    };
    const closeAnyModal = () => {
        document.querySelectorAll('.admin-modal').forEach(m => m.classList.add('hidden'));
    };

    document.addEventListener('click', (e) => {
        const opener = e.target.closest('[data-open-modal]');
        if (opener) { e.preventDefault(); openModal(opener.dataset.openModal); return; }
        if (e.target.closest('[data-close-modal]')) { e.preventDefault(); closeAnyModal(); }
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeAnyModal();
    });

    /* ---------- Kanban ---------- */
    const board = document.querySelector('.kanban-board');
    if (!board) return;

    const projectId = board.dataset.projectId;
    const csrf      = board.dataset.csrf;
    const taskModal = document.querySelector('[data-modal="task"]');
    const taskForm  = document.getElementById('task-form');
    const titleEl   = document.querySelector('[data-task-modal-title]');
    const deleteBtn = taskForm.querySelector('[data-task-delete]');

    const updateCount = (col) => {
        const status = col.dataset.status;
        const head   = board.querySelector(`.kanban-column[data-status="${status}"] [data-count]`);
        if (head) head.textContent = String(col.children.length);
    };

    /* SortableJS (loaded via CDN, init when ready) */
    const initSortable = () => {
        if (typeof Sortable === 'undefined') {
            setTimeout(initSortable, 50);
            return;
        }
        board.querySelectorAll('.kanban-tasks').forEach((list) => {
            Sortable.create(list, {
                group: 'kanban',
                animation: 160,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onAdd:    (evt) => persistMove(evt.item, evt.to, evt.newIndex),
                onUpdate: (evt) => persistMove(evt.item, evt.to, evt.newIndex),
            });
        });
    };

    async function persistMove(item, listEl, newIndex) {
        const id     = item.dataset.taskId;
        const status = listEl.dataset.status;
        const body = new URLSearchParams({
            action: 'task-move',
            id,
            status,
            position: String(newIndex),
            csrf,
        });
        const r = await fetch('/admin/api.php', { method: 'POST', body });
        if (!r.ok) {
            alert('No se pudo guardar el cambio.');
        }
        board.querySelectorAll('.kanban-tasks').forEach(updateCount);
    }

    initSortable();

    /* ---------- Open task modal for create / edit ---------- */
    const setStatusInput = (status) => { taskForm.elements.status.value = status; };
    const resetTaskForm = () => {
        taskForm.reset();
        taskForm.elements.id.value = '';
        deleteBtn.classList.add('hidden');
    };

    document.addEventListener('click', (e) => {
        const addBtn = e.target.closest('[data-add-task]');
        if (addBtn) {
            e.preventDefault();
            resetTaskForm();
            setStatusInput(addBtn.dataset.addTask);
            titleEl.textContent = 'Nueva tarea';
            openModal('task');
            return;
        }
        const taskEl = e.target.closest('.kanban-task');
        if (taskEl && !e.target.closest('[data-add-task]')) {
            try {
                const t = JSON.parse(taskEl.dataset.task);
                resetTaskForm();
                taskForm.elements.id.value          = t.id;
                taskForm.elements.title.value       = t.title;
                taskForm.elements.description.value = t.description || '';
                taskForm.elements.priority.value    = t.priority || 'medium';
                taskForm.elements.due_date.value    = t.due_date || '';
                taskForm.elements.status.value      = t.status;
                titleEl.textContent = 'Editar tarea';
                deleteBtn.classList.remove('hidden');
                openModal('task');
            } catch (_) {}
        }
    });

    /* ---------- Task form submit (create + update via fetch) ---------- */
    taskForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(taskForm);
        fd.set('project_id', projectId);
        fd.set('csrf', csrf);
        fd.set('action', fd.get('id') ? 'task-update' : 'task-create');

        const r = await fetch('/admin/api.php', { method: 'POST', body: fd });
        const data = await r.json().catch(() => ({}));
        if (data.ok) {
            location.reload();   // simplest reliable refresh of the board
        } else {
            alert(data.msg || 'Error al guardar.');
        }
    });

    deleteBtn.addEventListener('click', async () => {
        const id = taskForm.elements.id.value;
        if (!id) return;
        if (!confirm('¿Eliminar tarea?')) return;
        const body = new URLSearchParams({ action: 'task-delete', id, csrf });
        const r = await fetch('/admin/api.php', { method: 'POST', body });
        const data = await r.json().catch(() => ({}));
        if (data.ok) location.reload();
        else alert(data.msg || 'Error.');
    });
})();
