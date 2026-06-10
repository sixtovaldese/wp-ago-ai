/* aGo AI Admin JS */
(function () {
    'use strict';
    var $ = document.querySelector.bind(document);
    var $$ = document.querySelectorAll.bind(document);
    var restUrl = (window.agoaichatAdmin || {}).restUrl || '';
    var nonce = (window.agoaichatAdmin || {}).nonce || '';

    /* ───── Settings Page ───── */
    var saveBtn = $('#ago-save-settings');
    if (saveBtn) {
        saveBtn.addEventListener('click', function () {
            var data = {
                enabled: ($('#ago-enabled') || {}).checked || false,
                api_key: ($('#ago-api-key') || {}).value || '',
                model: ($('#ago-model') || {}).value || '',
                bot_name: ($('#ago-bot-name') || {}).value || '',
                welcome_message: ($('#ago-welcome-msg') || {}).value || '',
                tone: ($('#ago-tone') || {}).value || 'friendly',
                system_prompt: ($('#ago-system-prompt') || {}).value || '',
                avatar_url: ($('#ago-avatar-url') || {}).value || '',
                widget_position: ($('#ago-widget-position') || {}).value || 'right',
                widget_offset: parseInt(($('#ago-widget-offset') || {}).value || '0', 10),
                widget_color: ($('#ago-widget-color') || {}).value || '#2271b1',
                max_input_tokens: parseInt(($('#ago-max-input-tokens') || {}).value || '1000', 10),
                max_output_tokens: parseInt(($('#ago-max-output-tokens') || {}).value || '1000', 10),
                response_style: ($('#ago-response-style') || {}).value || 'friendly_emoji',
                rate_limit_per_minute: parseInt(($('#ago-rate-limit') || {}).value || '60', 10),
            };
            saveBtn.disabled = true; saveBtn.textContent = 'Saving...';
            fetch(restUrl + '/settings', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce }, body: JSON.stringify(data) })
            .then(function (r) { return r.json(); })
            .then(function (res) { showStatus(res.saved ? 'success' : 'error', res.saved ? 'Settings saved.' : (res.error || 'Error.')); })
            .catch(function (e) { showStatus('error', e.message); })
            .finally(function () { saveBtn.disabled = false; saveBtn.textContent = 'Save Settings'; });
        });

        // Avatar upload via WP Media Library
        var avatarBtn = $('#ago-avatar-upload');
        var avatarRemove = $('#ago-avatar-remove');
        if (avatarBtn) {
            avatarBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (!wp || !wp.media) { alert('Media library not available'); return; }
                var frame = wp.media({ title: 'Select Avatar', multiple: false, library: { type: 'image' } });
                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    if (attachment.filesizeInBytes > 1048576) { alert('Max 1MB'); return; }
                    var url = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                    $('#ago-avatar-url').value = attachment.url;
                    $('#ago-avatar-preview').src = url;
                    if (avatarRemove) avatarRemove.style.display = '';
                });
                frame.open();
            });
        }
        if (avatarRemove) {
            avatarRemove.addEventListener('click', function () {
                $('#ago-avatar-url').value = '';
                $('#ago-avatar-preview').src = '';
                avatarRemove.style.display = 'none';
            });
        }

        // Refresh models
        var refreshBtn = $('#ago-refresh-models');
        if (refreshBtn) {
            // Auto-load models on page load
            loadModels();
            refreshBtn.addEventListener('click', loadModels);
        }

        function loadModels() {
            var select = $('#ago-model');
            var status = $('#ago-models-status');
            var currentVal = select.value;
            if (status) status.textContent = 'Loading...';

            fetch(restUrl + '/models', { headers: { 'X-WP-Nonce': nonce } })
            .then(function (r) { return r.json(); })
            .then(function (models) {
                if (!models.length) { if (status) status.textContent = 'No models found. Check API key.'; return; }
                select.innerHTML = '';
                models.forEach(function (m) {
                    var opt = document.createElement('option');
                    opt.value = m.id;
                    opt.textContent = m.label + ', ' + m.tier;
                    if (m.id === currentVal) opt.selected = true;
                    select.appendChild(opt);
                });
                if (status) status.textContent = models.length + ' models loaded';
            })
            .catch(function () { if (status) status.textContent = 'Error loading models'; });
        }

        // File upload
        var uploadBtn = $('#ago-upload-file');
        if (uploadBtn) {
            uploadBtn.addEventListener('click', function () {
                var input = $('#ago-file-input');
                if (!input.files.length) return;
                var formData = new FormData();
                formData.append('file', input.files[0]);
                var statusEl = $('#ago-upload-status');
                statusEl.textContent = 'Uploading...';
                uploadBtn.disabled = true;
                fetch(restUrl + '/files/upload', { method: 'POST', headers: { 'X-WP-Nonce': nonce }, body: formData })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.ok) {
                        statusEl.textContent = 'Uploaded!';
                        statusEl.style.color = '#00a32a';
                        var f = res.file;
                        var tbody = $('#ago-files-tbody');
                        var tr = document.createElement('tr');
                        tr.setAttribute('data-name', f.name);
                        tr.innerHTML = '<td>' + esc(f.display_name) + '</td><td>' + esc(f.mime_type) + '</td><td>' + esc(f.uploaded_at) + '</td><td><button class="button ago-delete-file" data-name="' + esc(f.name) + '">Delete</button></td>';
                        tbody.appendChild(tr);
                        $('#ago-files-table').style.display = 'table';
                        $('#ago-no-files').style.display = 'none';
                        bindDeleteButtons();
                        input.value = '';
                    } else {
                        statusEl.textContent = res.error || 'Error';
                        statusEl.style.color = '#d63638';
                    }
                })
                .catch(function (e) { statusEl.textContent = e.message; statusEl.style.color = '#d63638'; })
                .finally(function () { uploadBtn.disabled = false; });
            });
        }

        bindDeleteButtons();
        function bindDeleteButtons() {
            $$('.ago-delete-file').forEach(function (btn) {
                btn.onclick = function () {
                    var name = btn.getAttribute('data-name');
                    if (!confirm('Delete this file?')) return;
                    fetch(restUrl + '/files/' + name, { method: 'DELETE', headers: { 'X-WP-Nonce': nonce } })
                    .then(function () {
                        var row = btn.closest('tr');
                        if (row) row.remove();
                        var tbody = $('#ago-files-tbody');
                        if (tbody && !tbody.children.length) {
                            $('#ago-files-table').style.display = 'none';
                            $('#ago-no-files').style.display = '';
                        }
                    });
                };
            });
        }
    }

    function showStatus(type, msg) {
        var box = $('#ago-status');
        if (!box) return;
        box.style.display = 'block'; box.className = type; box.textContent = msg;
        setTimeout(function () { box.style.display = 'none'; }, 3000);
    }

    /* ───── Conversations Page ───── */
    var convList = $('#ago-conv-list');
    if (convList) {
        var convPage = 1; var convId = 0;
        loadConversations();

        var closeModal = $('.ago-modal-close');
        if (closeModal) closeModal.addEventListener('click', function () { $('#ago-conv-modal').style.display = 'none'; });
        var modal = $('#ago-conv-modal');
        if (modal) modal.addEventListener('click', function (e) { if (e.target === modal) modal.style.display = 'none'; });

        var delConv = $('.ago-delete-conv');
        if (delConv) delConv.addEventListener('click', function () {
            if (!confirm('Delete?')) return;
            fetch(restUrl + '/conversations/' + convId, { method: 'DELETE', headers: { 'X-WP-Nonce': nonce } })
            .then(function () { modal.style.display = 'none'; loadConversations(); });
        });

        function loadConversations() {
            var loading = convList.querySelector('.ago-loading');
            var table = convList.querySelector('table');
            var empty = convList.querySelector('.ago-empty');
            if (loading) loading.style.display = 'block';
            if (table) table.style.display = 'none';
            if (empty) empty.style.display = 'none';

            fetch(restUrl + '/conversations?page=' + convPage, { headers: { 'X-WP-Nonce': nonce } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (loading) loading.style.display = 'none';
                var items = data.items || [];
                if (!items.length) { if (empty) empty.style.display = 'block'; return; }
                var tbody = $('#ago-conv-tbody');
                tbody.innerHTML = '';
                items.forEach(function (item) {
                    var tr = document.createElement('tr');
                    tr.innerHTML = '<td>' + item.id + '</td><td>' + esc((item.first_message || '').substring(0, 60)) + '</td><td>' + (item.message_count || 0) + '</td><td>' + esc(item.created_at) + '</td><td><button class="ago-view-btn" data-id="' + item.id + '">View</button></td>';
                    tbody.appendChild(tr);
                });
                if (table) table.style.display = 'table';
                $$('.ago-view-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () { openConversation(parseInt(btn.getAttribute('data-id'), 10)); });
                });
            });
        }

        function openConversation(id) {
            convId = id;
            fetch(restUrl + '/conversations/' + id, { headers: { 'X-WP-Nonce': nonce } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var body = $('#ago-conv-modal-body');
                body.innerHTML = '';
                (data.messages || []).forEach(function (msg) {
                    body.innerHTML += '<div class="ago-chat-msg ' + msg.role + '">' + esc(msg.content) + '</div>';
                });
                $('#ago-conv-modal').style.display = 'flex';
            });
        }
    }

    /* ───── Tasks Page ───── */
    var tasksList = $('#ago-tasks-list');
    if (tasksList) {
        var taskFilter = ''; var taskPage = 1; var taskId = 0;

        $$('.ago-filter').forEach(function (btn) {
            btn.addEventListener('click', function () {
                $$('.ago-filter').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                taskFilter = btn.getAttribute('data-status');
                taskPage = 1;
                loadTasks();
            });
        });

        var taskModal = $('#ago-task-modal');
        var closeTaskModal = taskModal ? taskModal.querySelector('.ago-modal-close') : null;
        if (closeTaskModal) closeTaskModal.addEventListener('click', function () { taskModal.style.display = 'none'; });
        if (taskModal) taskModal.addEventListener('click', function (e) { if (e.target === taskModal) taskModal.style.display = 'none'; });

        $$('.ago-task-status-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                fetch(restUrl + '/tasks/' + taskId + '/status', {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                    body: JSON.stringify({ status: btn.getAttribute('data-status') })
                }).then(function () { taskModal.style.display = 'none'; loadTasks(); });
            });
        });

        var delTask = $('.ago-delete-task');
        if (delTask) delTask.addEventListener('click', function () {
            if (!confirm('Delete?')) return;
            fetch(restUrl + '/tasks/' + taskId, { method: 'DELETE', headers: { 'X-WP-Nonce': nonce } })
            .then(function () { taskModal.style.display = 'none'; loadTasks(); });
        });

        loadTasks();

        function loadTasks() {
            var loading = tasksList.querySelector('.ago-loading');
            var table = tasksList.querySelector('table');
            var empty = tasksList.querySelector('.ago-empty');
            if (loading) loading.style.display = 'block';
            if (table) table.style.display = 'none';
            if (empty) empty.style.display = 'none';

            var url = restUrl + '/tasks?page=' + taskPage;
            if (taskFilter) url += '&status=' + taskFilter;

            fetch(url, { headers: { 'X-WP-Nonce': nonce } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (loading) loading.style.display = 'none';
                var items = data.items || [];
                if (!items.length) { if (empty) empty.style.display = 'block'; return; }
                var tbody = $('#ago-tasks-tbody');
                tbody.innerHTML = '';
                items.forEach(function (item) {
                    var tr = document.createElement('tr');
                    tr.innerHTML = '<td><span class="status-badge ' + esc(item.status) + '">' + esc(item.status) + '</span></td><td>' + esc(item.name) + '</td><td>' + esc(item.email) + '</td><td>' + esc((item.message || '').substring(0, 50)) + '</td><td>' + esc(item.created_at) + '</td><td><button class="ago-view-btn" data-id="' + item.id + '">View</button></td>';
                    tbody.appendChild(tr);
                });
                if (table) table.style.display = 'table';
                $$('.ago-view-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () { openTask(parseInt(btn.getAttribute('data-id'), 10)); });
                });
            });
        }

        function openTask(id) {
            taskId = id;
            fetch(restUrl + '/tasks/' + id, { headers: { 'X-WP-Nonce': nonce } })
            .then(function (r) { return r.json(); })
            .then(function (item) {
                var body = $('#ago-task-modal-body');
                body.innerHTML = '';
                var fields = { name: 'NAME', email: 'EMAIL', message: 'MESSAGE', ip_address: 'IP', created_at: 'DATE', status: 'STATUS' };
                for (var key in fields) {
                    if (!item[key]) continue;
                    var cls = key === 'message' ? 'ago-detail-message' : 'ago-detail-value';
                    body.innerHTML += '<div class="ago-detail-row"><div class="ago-detail-label">' + fields[key] + '</div><div class="' + cls + '">' + esc(item[key]) + '</div></div>';
                }
                taskModal.style.display = 'flex';
            });
        }
    }

    function esc(s) { var d = document.createElement('div'); d.appendChild(document.createTextNode(s || '')); return d.innerHTML; }
})();
