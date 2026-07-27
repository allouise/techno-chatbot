document.addEventListener('DOMContentLoaded', () => {
    const con = document.getElementById('techno-history-admin');
    const visitorList = document.getElementById('techno-visitors');
    const header = document.getElementById('techno-admin-chat-header');
    const messages = document.getElementById('techno-admin-chat-messages');
    const loader = document.querySelector('.techno-chat-admin-loader');
    const fromDate = document.getElementById('historyget-from');
    const toDate = document.getElementById('historyget-to');
    const getHistoryList = document.getElementById('historyget');
    const visitorListControl = document.getElementById('history-export-control');
    const deleteHistory = document.getElementById('delete-history');
    const exportHistory = document.getElementById('export-history');
    const selectAllVisitor = document.getElementById('history-select-all');

    if (visitorList){
        visitorList.addEventListener('click', async (event) => {
            const visitor = event.target.closest('[data-session]');
            if (!visitor)  return;

            document.querySelectorAll('.open-history').forEach(item => item.classList.remove('active'));
            visitor.classList.add('active');

            const session = visitor.dataset.session;
            const name = visitor.dataset.name || session;
            const email = visitor.dataset.email || '';

            header.innerHTML = `History: <strong>${escapeHtml(name)}</strong>${email ? `<br>${escapeHtml(email)}` : ''}`;
            con.classList.add('loading');
            messages.innerHTML = '';

            try {
                const formData = new FormData();
                formData.append('action', 'techno_chat_history_messages');
                formData.append('nonce', technoHistory.nonce);
                formData.append('session', session);

                const response = await fetch(technoHistory.ajax_url, {
                    method: 'POST',
                    body: formData
                });

                const json = await response.json();
                con.classList.remove('loading');

                if (!json.success) {
                    messages.innerHTML = '<p>Unable to load messages.</p>';
                    return;
                }
                renderMessages(json.data);
            }
            catch (err) {
                con.classList.remove('loading');
                messages.innerHTML = '<p>Error loading chat.</p>';
                console.error(err);
            }
        });
    }

    if(getHistoryList){
        getHistoryList.addEventListener('click', loadVisitors);
    }

    if (selectAllVisitor && visitorList) {
        selectAllVisitor.addEventListener('change', () => {
            const checkboxes = visitorList.querySelectorAll('[name="selected-visitors[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllVisitor.checked;
            });
        });
    }

    if (deleteHistory) {
        deleteHistory.addEventListener('click', async () => {
            const sessions = getSelectedSessions();
            if (sessions.length === 0) {
                alert('Please select at least one chat history.');
                return;
            }

            const confirmed = prompt(`You are about to permanently delete ${sessions.length} chat histor${sessions.length === 1 ? 'y' : 'ies'}. This will affect getting statistics.\nThis action cannot be undone.` + `\n\nType DELETE to confirm.`);
            if (confirmed === null) return;
            if (confirmed.trim().toUpperCase() !== 'DELETE') {
                alert('Confirmation failed. Nothing was deleted.');
                return;
            }

            con.classList.add('loading');
            try {
                const form = new FormData();
                form.append('action', 'techno_delete_chat_history');
                form.append('nonce', technoHistory.nonce);
                sessions.forEach(session => {
                    form.append('sessions[]', session);
                });
                const response = await fetch(technoHistory.ajax_url, {
                    method: 'POST',
                    body: form
                });
                const json = await response.json();
                con.classList.remove('loading');
                if (!json.success) {
                    alert(json.data || 'Unable to delete chat history.');
                    return;
                }

                alert(`Deleted ${json.data.deleted} chat histor${json.data.deleted === 1 ? 'y' : 'ies'}.`);
                selectAllVisitor.checked = false;
                messages.innerHTML = '';
                header.innerHTML = 'History: N/A';
                loadVisitors();
            } catch (err) {
                con.classList.remove('loading');
                console.error(err);
                alert('An error occurred while deleting chat history.');
            }
        });
    }
    
    if (exportHistory) {
        exportHistory.addEventListener('click', () => {
            const sessions = getSelectedSessions();
            if (sessions.length === 0) {
                alert('Please select at least one chat history.');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = technoHistory.ajax_url;
            form.style.display = 'none';

            form.appendChild(createHidden('action', 'techno_export_chat_history'));
            form.appendChild(createHidden('nonce', technoHistory.nonce));

            sessions.forEach(session => {
                form.appendChild(createHidden('sessions[]', session));
            });

            document.body.appendChild(form);
            form.submit();
            form.remove();

        });
    }

    async function loadVisitors(){
        if(!fromDate || !toDate) return;
        if( !fromDate.value || !toDate.value ) return;

        visitorList.innerHTML='';
        con.classList.add('loading');

        const form = new FormData();
        form.append('action','techno_chat_history_list');
        form.append('nonce',technoHistory.nonce);
        form.append('from',fromDate.value);
        form.append('to',toDate.value);

        const response = await fetch(
            technoHistory.ajax_url,
            {
                method:'POST',
                body:form
            }
        );

        const json = await response.json();
        con.classList.remove('loading');
        if(!json.success){
            if( visitorListControl ) visitorListControl.classList.add('disabled');
            return;
        }
        if(json.data.length === 0){
            if( visitorListControl ) visitorListControl.classList.add('disabled');
            visitorList.innerHTML=
            `<li class="techno-history-empty">
                No chat history found from
                ${formatDate(fromDate.value)}
                to
                ${formatDate(toDate.value)}.
            </li>`;
            messages.innerHTML = '';
            header.innerHTML = 'History: N/A';
            return;
        }
        
        if( visitorListControl ) visitorListControl.classList.remove('disabled');
        json.data.forEach(chat=>{
            visitorList.insertAdjacentHTML(
                'beforeend',
                visitorHtml(chat)
            );
        });
    }

    function createHidden(name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        return input;
    }

    function getSelectedSessions() {
        if( !visitorList ) return [];
        return [...visitorList.querySelectorAll('[name="selected-visitors[]"]:checked')]
            .map(cb => cb.value);
    }

    function visitorHtml(chat){
        let first = new Date(chat.created_at);
        let last = new Date(chat.ended_at);
        let span = formatChatSpan(first,last);
        let name = chat.name ? titleCase(chat.name) : chat.session_id;

        return `<li class="techno-history-visitor">
                <input type="checkbox" name="selected-visitors[]" value="${chat.id}"/>
                <span class="open-history" data-session="${chat.id}" data-name="${name}">
                    <strong title="${escapeHtml(name)}">
                        ${escapeHtml(name)}
                    </strong>
                    <small>${span}</small>
                </span>
            </li>`;

    }

    function formatChatSpan(first,last){
        const same = first.toDateString()===last.toDateString();
        const options = {
            month:'short',
            day:'numeric',
            year:'numeric',
            hour:'numeric',
            minute:'2-digit'
        };

        if(same) return first.toLocaleString([],options);
        return first.toLocaleString([],options) +' - '+ last.toLocaleString([],options);
    }

    function formatDate(date){
        return new Date(date).toLocaleDateString([],{
            month:'short',
            day:'numeric',
            year:'numeric'
        });
    }

    function titleCase(str) {
        if (!str) return '';
        return str.toLowerCase().replace(/\b\w/g, char => char.toUpperCase());
    }

    function renderMessages(chatMessages) {
        messages.innerHTML = '';
        chatMessages.forEach(message => {
            const div = document.createElement('div');
            div.className = `techno-history-msg ${message.sender}`;
            div.innerHTML = `
                ${escapeHtml(message.message)}
                <div class="techno-chatbot-time">${escapeHtml(message.created_at)}</small>
            `;
            messages.appendChild(div);
        });
        messages.scrollTop = messages.scrollHeight;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    loadVisitors();
});