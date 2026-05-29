/* aGo AI, Chat Widget JS */
(function () {
    'use strict';
    var cfg = window.agoaichatWidget || {};
    var container = document.getElementById('ago-ai-widget');
    if (!container) return;

    var i18n = cfg.i18n || {};
    var pos = cfg.position || 'right';
    var offset = parseInt(cfg.offset || 0, 10);
    if (offset < 0 || offset > 2) offset = 0;
    var offsetClass = 'ago-offset-' + offset;
    var color = cfg.color || '#2271b1';
    var isOpen = false;
    var conversationId = null;
    var history = [];
    var iterationCount = 0;
    var maxIterations = cfg.maxIterations || 20;

    render();

    function render() {
        // Toggle button
        container.innerHTML =
            '<button class="ago-ai-toggle ' + pos + ' ' + offsetClass + '" style="background:' + color + '">' +
                '<svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.2L4 17.2V4h16v12z"/></svg>' +
            '</button>' +
            '<div class="ago-ai-window ' + pos + ' ' + offsetClass + '">' +
                '<div class="ago-ai-header" style="background:' + color + '">' +
                    '<img class="ago-ai-avatar" src="' + esc(cfg.avatarUrl) + '" alt="">' +
                    '<div class="ago-ai-header-info"><h3>' + esc(cfg.botName) + '</h3><span>Online</span></div>' +
                    '<button class="ago-ai-close">&times;</button>' +
                '</div>' +
                '<div class="ago-ai-messages" id="ago-ai-messages"></div>' +
                '<div class="ago-ai-input-area">' +
                    '<input class="ago-ai-input" placeholder="' + esc(i18n.placeholder) + '" id="ago-ai-input">' +
                    '<button class="ago-ai-send" style="background:' + color + '">' +
                        '<svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>' +
                    '</button>' +
                '</div>' +
                '<div class="ago-ai-powered">Powered by <a href="https://ago.cl" target="_blank">aGo AI</a></div>' +
            '</div>';

        // Events
        container.querySelector('.ago-ai-toggle').addEventListener('click', toggleChat);
        container.querySelector('.ago-ai-close').addEventListener('click', toggleChat);
        container.querySelector('.ago-ai-send').addEventListener('click', sendMessage);
        container.querySelector('#ago-ai-input').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') sendMessage();
        });

        // Welcome message
        addBotMessage(cfg.welcomeMsg);
    }

    function toggleChat() {
        isOpen = !isOpen;
        var win = container.querySelector('.ago-ai-window');
        win.classList.toggle('open', isOpen);
        if (isOpen) container.querySelector('#ago-ai-input').focus();
    }

    function sendMessage() {
        var input = container.querySelector('#ago-ai-input');
        var text = input.value.trim();
        if (!text) return;
        iterationCount++;
        if (iterationCount > maxIterations) {
            addBotMessage((cfg.i18n && cfg.i18n.cantHelp) || "You've reached the conversation limit.");
            if (cfg.leadCapture) {
                showTaskForm();
            }
            return;
        }
        input.value = '';

        addUserMessage(text);
        var thinkingEl = addBotMessage(i18n.thinking || 'Thinking...', true);

        fetch(cfg.restUrl + '/chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text, history: history, conversation_id: conversationId }),
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            thinkingEl.remove();

            // API error (rate limit anti-abuse, etc)
            if (res.error && !res.text) {
                addBotMessage(i18n.rateLimit && res.error.indexOf('Too many') === 0 ? i18n.rateLimit : (i18n.error || res.error || 'Error'));
                return;
            }

            conversationId = res.conversation_id;
            var response = (res.text || '').trim();

            if (!response) {
                addBotMessage(i18n.error || 'Error');
                return;
            }

            // Check for [CANT_HELP] marker
            if (response.indexOf('[CANT_HELP]') !== -1) {
                var cleanText = response.replace(/\[CANT_HELP\]/g, '').trim();
                if (cleanText) addBotMessage(cleanText);
                if (cfg.leadCapture) {
                    showTaskForm();
                }
            } else {
                addBotMessage(response);
            }

            // Save clean response to history (without marker)
            var cleanForHistory = response.replace(/\[CANT_HELP\]/g, '').trim();
            history.push({ role: 'user', text: text });
            history.push({ role: 'model', text: cleanForHistory });
        })
        .catch(function () {
            thinkingEl.remove();
            addBotMessage(i18n.error || 'Error');
        });
    }

    function addUserMessage(text) {
        var msgs = container.querySelector('#ago-ai-messages');
        var div = document.createElement('div');
        div.className = 'ago-ai-msg user';
        div.style.background = color;
        div.textContent = text;
        msgs.appendChild(div);
        msgs.scrollTop = msgs.scrollHeight;
    }

    function addBotMessage(text, isThinking) {
        var msgs = container.querySelector('#ago-ai-messages');
        var div = document.createElement('div');
        div.className = 'ago-ai-msg bot' + (isThinking ? ' thinking' : '');
        if (isThinking) {
            div.textContent = text;
        } else {
            div.innerHTML = renderMarkdown(text);
        }
        msgs.appendChild(div);
        msgs.scrollTop = msgs.scrollHeight;
        return div;
    }

    function renderMarkdown(text) {
        if (!text) return '';
        var html = esc(text);
        // Bold **text**
        html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        // Italic *text*
        html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');
        // Links [text](url)
        html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener" style="color:inherit;text-decoration:underline">$1</a>');
        // Unordered lists (lines starting with * or -)
        html = html.replace(/^[\*\-]\s+(.+)/gm, '<li>$1</li>');
        html = html.replace(/(<li>.*<\/li>)/gs, '<ul style="margin:4px 0;padding-left:18px">$1</ul>');
        // Headers ### text
        html = html.replace(/^###\s+(.+)/gm, '<strong style="display:block;margin-top:6px">$1</strong>');
        // Line breaks
        html = html.replace(/\n/g, '<br>');
        // Clean double <br> before/after lists
        html = html.replace(/<br><ul/g, '<ul');
        html = html.replace(/<\/ul><br>/g, '</ul>');
        return html;
    }

    function showTaskForm() {
        var msgs = container.querySelector('#ago-ai-messages');
        var form = document.createElement('div');
        form.className = 'ago-ai-task-form';

        var html = '<h4>' + esc(i18n.taskTitle) + '</h4>' +
            '<input type="text" placeholder="' + esc(i18n.taskName) + '" class="ago-task-name">' +
            '<input type="email" placeholder="' + esc(i18n.taskEmail) + '" class="ago-task-email">' +
            '<textarea placeholder="' + esc(i18n.taskMsg) + '" rows="2" class="ago-task-msg"></textarea>' +
            '<div style="position:absolute;left:-9999px"><input type="text" class="ago-task-hp" tabindex="-1" autocomplete="off"></div>' +
            '<button class="ago-task-send-btn" style="background:' + color + '">' + esc(i18n.taskSend) + '</button>';

        // WhatsApp button
        if (cfg.whatsapp) {
            var waUrl = 'https://wa.me/' + cfg.whatsapp.number.replace(/[^0-9]/g, '') + '?text=' + encodeURIComponent(cfg.whatsapp.message);
            html += '<a href="' + waUrl + '" target="_blank" rel="noopener" class="ago-wa-btn" style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:6px;padding:8px;border-radius:6px;background:#25D366;color:#fff;text-decoration:none;font-size:13px;font-weight:500">' +
                '<svg width="16" height="16" viewBox="0 0 24 24" fill="#fff"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>' +
                'WhatsApp</a>';
        }

        form.innerHTML = html;

        form.querySelector('.ago-task-send-btn').addEventListener('click', function () {
            var name = form.querySelector('.ago-task-name').value;
            var email = form.querySelector('.ago-task-email').value;
            var msg = form.querySelector('.ago-task-msg').value;
            var hp = form.querySelector('.ago-task-hp').value;
            if (!name || !email) return;
            fetch(cfg.restUrl + '/tasks', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: name, email: email, message: msg, website: hp }),
            }).then(function () {
                var sentHtml = '<div class="ago-ai-task-sent">' + esc(i18n.taskSent) + '</div>';
                if (cfg.whatsapp) {
                    var waUrl2 = 'https://wa.me/' + cfg.whatsapp.number.replace(/[^0-9]/g, '') + '?text=' + encodeURIComponent(cfg.whatsapp.message);
                    sentHtml += '<a href="' + waUrl2 + '" target="_blank" rel="noopener" style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:8px;padding:8px;border-radius:6px;background:#25D366;color:#fff;text-decoration:none;font-size:13px;font-weight:500">' +
                        '<svg width="16" height="16" viewBox="0 0 24 24" fill="#fff"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>' +
                        'WhatsApp</a>';
                }
                form.innerHTML = sentHtml;
            });
        });

        msgs.appendChild(form);
        msgs.scrollTop = msgs.scrollHeight;
    }

    function esc(s) { var d = document.createElement('div'); d.appendChild(document.createTextNode(s || '')); return d.innerHTML; }
})();
