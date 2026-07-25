/* ---------- Admin WebSocket Section ---------- */
const livechatPage = document.getElementById('techno-livechat-admin');
const toggleInput = document.getElementById('techno-admin-toggle-online');
const toggleLabel = document.getElementById('techno-toggle-label');
const chatInput = document.getElementById('techno-admin-chat-input');
const sendBtn = document.getElementById('techno-admin-chat-send');
const endBtn = document.getElementById('techno-admin-chat-end');
const endIdleBtn = document.getElementById('techno-admin-chat-end-idle');
const chatOptions = document.getElementById('chat-options');
const activeVisitors = document.getElementById('techno-active-visitors');
const chatMsgWindow = document.getElementById('techno-livechat-admin-chatmsgs');
const chatWindow = document.getElementById('techno-admin-chat-window');
const chatToggle = document.getElementById('techno-support-switch');
const notifToggle = document.getElementById('techno-notification-toggle');
const chatHeader = document.getElementById('techno-admin-chat-header');
const chatMessages = document.getElementById('techno-admin-chat-messages');
const sessionMessages = {};

let currentSession = null, 
    adminLastId = 0, 
    socket = null, 
    sessionMap = {}, 
    sessionMapMeta = {},
    audioUnlocked = false,
    pendingNotification = false,
    initialLoadDone = false;

function initAdminSocket() {
    socket = io(technoLivechat.ws_url, { 
        transports: ['polling', 'websocket'],
        secure: true,
        reconnection: true,
        reconnectionAttempts: Infinity,
        reconnectionDelay: 1000,
        reconnectionDelayMax: 5000,
        timeout: 20000,
        auth: {
            site: technoLivechat.site_id,
            token: technoLivechat.token
        }
    });
    
    /* On Error */
    socket.on("connect_error", () => {
        console.log("WebSocket server is OFF");
        socket.disconnect();
        loadActiveVisitorsDB();
        updateSupportStatus();
        if (!document.getElementById("ws-off-warning")) {
            livechatPage.insertAdjacentHTML( "afterbegin",
                '<div id="ws-off-warning" style="background:#ffe5e5;color:#a00000;padding:12px;border:1px solid #ffb3b3;border-radius:6px;font-weight:600;position:absolute;z-index:2;top:15vh;left:0;right:0;margin:auto;width:625px;max-width:100%;font-size:13px;">WebSocket is turned off. Please try refreshing the page. If the problem persists, contact the administrator.</div>' );
        }
        alert('Websocket is turned off, Please try refreshing the page. If Problem persists contact administrator.');
    });

    /* On Connect */
    socket.on("connect", () => {
        loadActiveVisitorsDB();
        document.getElementById("ws-off-warning")?.remove();
        chatToggle?.classList.add('active');
        toggleInput.disabled = false;
        if (toggleInput?.checked) {
            socket.emit("register-support");
        }
        if (toggleLabel){
            toggleLabel.textContent = (toggleInput?.checked)? "Online" : "Offline";
        }
    });

    /*
     * new-session: fires when a visitor joins.
     * Server sends { session_id, visitor_name } (or legacy plain string).
     */
    socket.on("new-session", (data) => {
        const sessionId = typeof data === 'object' ? data.session_id : data;
        const visitorName = typeof data === 'object' ? (data.visitor_name || sessionId) : sessionId;
        const isNew = !sessionMap[sessionId];
        sessionMap[sessionId] = visitorName;

        if (isNew) notifyNewSession(sessionId, visitorName);
        renderActiveVisitors();
    });

    /*
     * active-sessions: full list from server.
     * Server sends array of { session_id, visitor_name } (or legacy plain strings).
     */
    socket.on("active-sessions", (sessions) => {
        Object.keys(sessionMapMeta).forEach(sid => {
            sessionMapMeta[sid].active = false;
        });

        sessions.forEach(chat => {
            if (sessionMap[chat.session_id] && sessionMapMeta[chat.session_id]) {
                sessionMapMeta[chat.session_id].active = chat.active;
            } else {
                sessionMap[chat.session_id] = chat.visitor_name || chat.session_id;
                sessionMapMeta[chat.session_id] = {
                    active: chat.active
                };
            }
        });
        renderActiveVisitors();
    });

    /*
     * receive-message: incoming message for the open session.
     * Show visitor messages in real time on the admin side.
     */
    socket.on('receive-message', (msg) => {
        if (msg.sender !== 'visitor') return;

        /* Cache for every session regardless of which is open */
        if (!sessionMessages[msg.session_id]) sessionMessages[msg.session_id] = [];
        sessionMessages[msg.session_id].push({ sender: 'visitor', message: msg.message });

        /* Render only if this is the currently open session */
        if (msg.session_id === currentSession) {
            renderMessage({ sender: 'visitor', message: msg.message });
        }
    });

}

function updateChatState2(exists, ended = false) {
    if (!chatInput || !sendBtn || !chatWindow || !chatMessages || !chatHeader) return;

    if( ended == true ){
        chatInput.placeholder = exists ? 'Type a message...' : 'Conversation Ended';
        sendBtn.textContent = exists ? 'Send' : 'Ended';
    }else{
        chatInput.placeholder = exists ? 'Type a message...' : 'Conversation Error';
        sendBtn.textContent = exists ? 'Send' : 'Error';
    }
    
    const opened = activeVisitors.querySelector('.open');
    if( exists && !opened ) exists = false;
    chatInput.disabled = !exists;
    sendBtn.disabled = !exists;
    chatWindow.classList.toggle('disabled', !exists);
    endBtn.classList.toggle('disabled', !exists);
}


function updateChatState(isOnline) {
    if (!chatInput || !sendBtn || !chatWindow || !chatMessages || !chatHeader) return;
    chatInput.placeholder = isOnline ? 'Type a message...' : 'Support is offline...';
    sendBtn.textContent = isOnline ? 'Send' : 'Offline';
    const opened = activeVisitors.querySelector('.open');
    if( isOnline && !opened ) isOnline = false;
    chatInput.disabled = !isOnline;
    sendBtn.disabled = !isOnline;
    chatWindow.classList.toggle('disabled', !isOnline);
}

function updateSupportStatus(force = null) {
    force = (force === 1)? 1 : 0;
    const opened = activeVisitors.querySelector('.open');
    if( opened ){ 
        updateChatState(false);
    }
    fetch(technoLivechat.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'techno_toggle_support_online',
            nonce: technoLivechat.nonce,
            force_status: force 
        })
    })
    .then(res => res.json())
    .then(res => {
        if(res.success) {
            const online = res.data.online;
            if (toggleInput){
                toggleInput.checked = online ? true : false;
            }
            if (toggleLabel) {
                toggleLabel.textContent = online ? 'Online' : ( res.data.server_offline ? 'Server Offline' : 'Offline');
            }
            if (chatToggle) {
                if(res.data.server_offline){
                    chatToggle.classList.remove('active');
                }else{
                    chatToggle.classList.add('active');
                }
            }
            updateChatState(online);
        }
    });
}

function openSession(sessionId) {
    currentSession = sessionId;
    socket.emit('join-session', { session_id: sessionId });
    adminLastId = 0;
    chatMsgWindow.style.display = 'flex';

    /* Update header with visitor's display name */
    if (chatHeader) {
        const name    = sessionMap[sessionId] || sessionId;
        const safeName = document.createElement('span');
        safeName.textContent = name;
        chatHeader.innerHTML = 'Chatting with: <strong>' + safeName.innerHTML + '</strong>';
    }

    /* Highlight active list item */
    activeVisitors.querySelectorAll('li').forEach(li => {
        li.classList.toggle('open', li.dataset.session === sessionId);
    });

    /* Load history (cache-first, then AJAX) */
    loadSessionHistory(sessionId);
    
    /* Enable / disable input based on online state */
    if (toggleInput?.checked) updateChatState(true);
    else updateChatState(false);
}

function scrollToBottom() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function handleVisitorLeft(sessionId){
    delete sessionMessages[sessionId];
    /* if admin currently viewing */
    if (currentSession === sessionId){
        currentSession = null;
        updateChatState(false);
        if (!chatMessages) return;
        const div = document.createElement('div');
        div.className = `techno-livechat-msg system-error`;
        div.textContent = 'Visitor Restarted/Deleted the session';
        chatMessages.appendChild(div);
    }
}

/* 
 * Browser Notifications & sound 
 */
if(livechatPage){
    document.addEventListener('click', unlockAudio, { once: true });
}
function requestNotificationPermission() {
    if (!('Notification' in window)) {
        console.log("Browser does not support notifications");
        return;
    }
    if (Notification.permission === 'default') {
        Notification.requestPermission()
        .then(permission => {
            console.log("Notification permission:", permission);
            syncNotificationCheckbox();
        });
    }
}
function unlockAudio() {
    if (audioUnlocked) return;
    const audio = new Audio(technoLivechat.notification_sound);
    audio.volume = 0;
    audio.play().then(() => {
        audio.pause();
        audioUnlocked = true;
        audio.currentTime = 0;
        if (pendingNotification) {
            playNotification();
            pendingNotification = false;
        }
    }).catch(() => {});
}
function playNotification() {
    const audio = new Audio(technoLivechat.notification_sound);
    audio.volume = 1; 
    audio.play().catch(e => console.log("Audio error:", e));
}
function notifyNewSession(sessionId, visitorName) {
    if (document.visibilityState === 'visible') { 
        if (audioUnlocked) {
            playNotification();
        } else {
            pendingNotification = true;
        }
    }
    if ('Notification' in window && Notification.permission === 'granted') {
        const notif = new Notification(technoLivechat.site_name + ' Live Chat Request', {
            body: visitorName + ' has joined the live chat',
        });
        setTimeout(() => notif.close(), 5000);
        notif.onclick = () => {
            window.focus();
            notif.close();
        };
    }
}
function syncNotificationCheckbox() {
    if (!('Notification' in window)) {
        notifToggle.disabled = true;
        return;
    }
    notifToggle.checked = Notification.permission === 'granted';
}
if(notifToggle){
    notifToggle.addEventListener('change', () => {
        unlockAudio();
        if (notifToggle.checked) {
            requestNotificationPermission();
            setTimeout(() => {
                syncNotificationCheckbox();
            }, 300);
        } else {
            alert(
                "To disable notifications, change your browser settings."
            );
            syncNotificationCheckbox();
        }
    });
}

/* 
 * Render visitor list
 */
function renderActiveVisitors() {
    if (!activeVisitors) return;
    activeVisitors.innerHTML = '';
    const entries = Object.entries(sessionMap);

    if (entries.length === 0) {
        const empty = document.createElement('li');
        empty.textContent      = 'No active visitors';
        empty.style.opacity    = '0.5';
        empty.style.cursor     = 'default';
        empty.style.pointerEvents = 'none';
        activeVisitors.appendChild(empty);
        return;
    }

    entries.forEach(([sid, name]) => {
        const li = document.createElement('li');
        li.dataset.session = sid;
        li.onclick = () => openSession(sid);
        if (sid === currentSession) li.classList.add('open');

        const sessionData = sessionMapMeta[sid];
        if (sessionData && !sessionData.active) li.classList.add('inactive');

        const nameSpan = document.createElement('span');
        nameSpan.className = 'techno-visitor-name';
        nameSpan.textContent = name;
        nameSpan.title = name;

        const idSpan = document.createElement('span');
        idSpan.className = 'techno-visitor-sid';
        idSpan.textContent = `ID: ${sid}`;
        idSpan.title = sid;

        li.appendChild(nameSpan);
        li.appendChild(idSpan);
        activeVisitors.prepend(li);
    });
}
function loadActiveVisitorsDB() {
    fetch(technoLivechat.ajax_url, {
        method: "POST",
        headers: {
            "Content-Type":"application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            action: "techno_get_active_livechats",
            nonce: technoLivechat.nonce
        })
    })
    .then(r => r.json())
    .then(res => {
        if(!res.success) return;
        sessionMap = {};
        sessionMapMeta = {};

        res.data.forEach(chat => {
            sessionMap[chat.socket_id] = chat.name;
            sessionMapMeta[chat.socket_id] = {
                active: chat.active
            };
        });
        if(socket && socket.connected){
            socket.emit("get-active-sessions");
        }else{
            renderActiveVisitors();
        }
    })
    .catch(console.error);
}

/* 
 * Send Admin Message
 */
sendBtn?.addEventListener('click', sendAdminMessage);
chatInput?.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') sendAdminMessage();
});
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
async function sendAdminMessage(save = true, _msg = '', _type = 'text') {
    const msg = (_msg != '')? _msg : chatInput.value.trim();
    
    if (!msg || !currentSession || !socket) return false;
    chatInput.value = '';

    socket.emit("send-message", {
        session_id: currentSession,
        message: msg,
        sender: 'admin',
        type: _type,
    });

    if(save){
        addAdminMessage({ sender: 'admin', message: msg, type: _type });
    }
    return true;
}
async function addAdminMessage(msg) {
    if (!chatMessages) return;

    /* Cache first so a tab-switch immediately after will show it */
    if (currentSession) {
        if (!sessionMessages[currentSession]) sessionMessages[currentSession] = [];
        sessionMessages[currentSession].push({ sender: msg.sender, message: msg.message });
    }

    /* Save TO DB */
    fetch(technoLivechat.ajax_url, {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            action: "techno_save_admin_chat_message",
            nonce: technoLivechat.nonce,
            session_id: currentSession,
            sender: msg.sender,
            message: msg.message,
            type: msg.type
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            renderMessage(msg);
        }
    })
    .catch(console.error);
    
}
function renderMessage(msg) {
    if (!chatMessages) return;
    const div = document.createElement('div');
    div.className = `techno-livechat-msg ${escapeHtml(msg.sender)}`;
    if( msg.sender == 'bot' ){
        div.innerHTML = msg.message;
    }else{
        div.textContent = msg.message;
    }
    chatMessages.appendChild(div);
    scrollToBottom();
    adminLastId = Math.max(adminLastId, msg.id || 0);
}
function renderMessageBatch(messages) {
    if (!chatMessages || !messages.length) return;
    const frag = document.createDocumentFragment();
    messages.forEach(msg => {
        const div = document.createElement('div');
        div.className = `techno-livechat-msg ${escapeHtml(msg.sender)}`;
        if( msg.sender == 'bot' ){
            div.innerHTML = msg.message;
        }else{
            div.textContent = msg.message;
        }

        /* Time */
        if( msg.created_at ){
            const time = document.createElement('div');
            time.className = 'techno-chatbot-time';
            const date = new Date(msg.created_at.replace(' ', 'T'));
            time.textContent = date.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            div.appendChild(time);
        }
        frag.appendChild(div);
        adminLastId = Math.max(adminLastId, msg.id || 0);
    });
    chatMessages.appendChild(frag);
    scrollToBottom();
}

/* 
 * End Chat
 */
async function endChat(_type = 'endchat') {
    if (!currentSession || !socket) return;
    
    livechatPage.classList.add('loading');
    chatInput.value = _type;
    let msg = technoLivechat.endChatMsg;

    if(_type == 'endchat1'){
        msg = technoLivechat.endIdleChatMsg;
        await sendAdminMessage(true, msg, 'end_idlelive');
    }else{
        await sendAdminMessage(true, msg, 'system_end');
    }

    /* End Chat */
    try {
        const sessionId = currentSession;
        const response = await fetch(technoLivechat.ajax_url, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
                action: "techno_end_chat",
                nonce: technoLivechat.nonce,
                end_type: _type,
                session_id: sessionId
            })
        });

        const res = await response.json();

        if (res.success) {
            finishEndChat(sessionId);
        } else {
            console.error(res.data);
            if (_type === 'endchat') {
                finishEndChat(sessionId, true);
            }
        }
    } catch (err) {
        console.error(err);
    } finally {
        livechatPage.classList.remove('loading');
    }
}
function finishEndChat(sessionId, skip_socket = false) {
    const _session = activeVisitors.querySelector('[data-session="'+ sessionId +'"]');

    if( skip_socket === true ){
        socket.emit("end-chat", { session_id: sessionId });
    }

    delete sessionMessages[sessionId];
    delete sessionMap[sessionId];
    delete sessionMapMeta[sessionId];

    currentSession = null;
    adminLastId = 0;
    chatInput.value = '';

    updateChatState(false);
    renderActiveVisitors();
}
endBtn?.addEventListener('click', () => {
    const confirmed = confirm(
        "Are you sure you want to end this chat?\n\nThis action is Irreversible and will archive the conversation and disconnect the visitor."
    );
    if (!confirmed) {
        return;
    }else{
        endChat('endchat');
        chatMsgWindow.removeAttribute('style');
    }
});
endIdleBtn?.addEventListener('click', () => {
    const confirmed = confirm(
        "Are you sure you want to end this chat?\n\nThis action is Irreversible and will archive the conversation and disconnect the visitor."
    );
    if (!confirmed) {
        return;
    }else{
        endChat('endchat1');
        chatMsgWindow.removeAttribute('style');
    }
});

/*
 * Chat Options
 */
if( chatOptions && chatOptions.querySelector('.options-btn') ){
    chatOptions.querySelector('.options-btn')?.addEventListener('click', function(e){
        e.stopPropagation();
        chatOptions.classList.toggle('active');
    });
}
if( chatOptions ){
    document.addEventListener('click', function (e) {
        if (!chatOptions.contains(e.target)) {
            chatOptions.classList.remove('active');
        }
    });
}

/* 
 * History
 */
function loadSessionHistory(sessionId) {
    if (!chatMessages) return;

    chatMessages.innerHTML = '';
    adminLastId = 0;
    updateChatState2(true);
    if (sessionMessages[sessionId] && sessionMessages[sessionId].length) {
        renderMessageBatch(sessionMessages[sessionId]);
        return;
    }
    livechatPage.classList.add('loading');
    fetch(technoLivechat.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'techno_get_chat_history',
            nonce:  technoLivechat.nonce,
            session_id: sessionId
        })
    })
    .then(res => {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    })
    .then(data => {
        livechatPage.classList.remove('loading');
        if (!data.success || !Array.isArray(data.data.messages)) return;
        
        const messages = data.data.messages.map(row => ({
            sender:  row.sender  || 'visitor',
            message: row.message || '',
            created_at: row.created_at || ''
        })).filter(m => m.message);

        sessionMessages[sessionId] = messages.slice();
        if (chatMessages && currentSession === sessionId) {
            renderMessageBatch(messages);
        }

        if(data.data.ended_at != null){
            updateChatState2(false, true);
        }
    })
    .catch(err => {
        updateChatState2(false);
        console.error('[Techno Chatbot] Failed to load history:', err);
        livechatPage.classList.remove('loading');
    });
}

/* ---------- Support online toggle ---------- */
if(toggleInput) {
    toggleInput.addEventListener('change', () => {
        if(!socket) return;
        
        if(toggleInput.checked) {
            socket.emit("register-support");
            updateSupportStatus(1);
            requestNotificationPermission();
        } else {
            socket.emit("unregister-support");
            updateSupportStatus();
        }
    });
}

/* ---------- Init ---------- */
document.addEventListener('DOMContentLoaded', () => {
    if (livechatPage) {
        initAdminSocket();
        requestNotificationPermission();
        syncNotificationCheckbox();
    }
});