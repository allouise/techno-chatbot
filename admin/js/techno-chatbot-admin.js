/* ---------- Admin WebSocket Section ---------- */
const livechatPage = document.getElementById('techno-livechat-admin');
const toggleInput = document.getElementById('techno-admin-toggle-online');
const toggleLabel = document.getElementById('techno-toggle-label');
const chatInput = document.getElementById('techno-admin-chat-input');
const sendBtn = document.getElementById('techno-admin-chat-send');
const endBtn = document.getElementById('techno-admin-chat-end');
const chatOptions = document.getElementById('chat-options');
const activeVisitors = document.getElementById('techno-active-visitors');
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
        if (toggleInput) {
            toggleInput.checked = false;
            toggleInput.disabled = true;
        }
        if (chatToggle) chatToggle.classList.remove('active');
        if (toggleLabel) toggleLabel.textContent = "Server Offline";

        updateChatState(false);
        updateSupportStatus(0);
    });

    /* On Connect */
    socket.on("connect", () => {
        loadActiveVisitors();
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
        const incoming = {};
        const incomingMeta = {};
        sessions.forEach(s => {
            const sid  = typeof s === 'object' ? s.session_id : s;
            const name = typeof s === 'object' ? (s.visitor_name || sid) : sid;
            incoming[sid] = name;
            incomingMeta[sid] = { active: s.active ?? true };
        });

        /* detect removed sessions */
        Object.keys(sessionMap).forEach(oldSid => {
            if (!incoming[oldSid]) {
                console.log( "Visitor left:", oldSid );
                handleVisitorLeft(oldSid);
            }
        });

        if (initialLoadDone) {
            Object.entries(incoming).forEach(([sid, name]) => {
                if (!sessionMap[sid]) notifyNewSession(sid, name);
            });
        }

        sessionMap = incoming;
        sessionMapMeta = incomingMeta;
        initialLoadDone = true; 
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

function updateChatState(isOnline) {
    if (!chatInput || !sendBtn || !endBtn  || !chatWindow || !chatMessages || !chatHeader) return;
    chatInput.placeholder = isOnline ? 'Type a message...' : 'Support is offline...';
    sendBtn.textContent = isOnline ? 'Send' : 'Offline';
    const opened = activeVisitors.querySelector('.open');
    if( isOnline && !opened ) isOnline = false;
    chatInput.disabled = !isOnline;
    sendBtn.disabled = !isOnline;
    endBtn.disabled = !isOnline;
    chatWindow.classList.toggle('disabled', !isOnline);
    if( !isOnline ){
        chatMessages.innerHTML = "";
    }
}

function updateSupportStatus(force = null) {
    force = (force == 1)? 1 : 0;
    const opened = activeVisitors.querySelector('.open');
    if( opened ){ 
        opened.classList.remove('open');
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
            if (toggleInput) toggleInput.checked = online;
            if (toggleLabel) {
                toggleLabel.textContent = online ? 'Online' : ( res.data.server_offline ? 'Server Offline' : 'Offline');
            }
            updateChatState(online);
        }
    });
}

function openSession(sessionId) {
    currentSession = sessionId;
    socket.emit('join-session', { session_id: sessionId });
    adminLastId = 0;

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
    /* console.log("New visitor:", visitorName, "("+sessionId+")"); */
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
        if (sid === currentSession) li.classList.add('active');

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
function loadActiveVisitors() {
    if(!socket) return;
    socket.emit("get-active-sessions");
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
function sendAdminMessage() {
    const msg = chatInput.value.trim();
    if (!msg || !currentSession || !socket) return;
    chatInput.value = '';

    socket.emit("send-message", {
        session_id: currentSession,
        message: msg,
        sender: 'admin'
    });
    addAdminMessage({ sender: 'admin', message: msg });
}
function addAdminMessage(msg) {
    if (!chatMessages) return;

    /* Cache first so a tab-switch immediately after will show it */
    if (currentSession) {
        if (!sessionMessages[currentSession]) sessionMessages[currentSession] = [];
        sessionMessages[currentSession].push({ sender: msg.sender, message: msg.message });
    }

    /* Save TO DB */
    const body = new URLSearchParams({
        action: 'techno_save_admin_chat_message',
        nonce: technoLivechat.nonce,
        session_id: currentSession,
        sender: msg.sender,
        message: msg.message
    });

    fetch(technoLivechat.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body,
        keepalive: true,
    }).catch(err =>
        console.error('[Techno Chatbot] DB save failed:', err)
    );

    renderMessage(msg);
}
function renderMessage(msg) {
    if (!chatMessages) return;
    const div = document.createElement('div');
    div.className = `techno-livechat-msg ${escapeHtml(msg.sender)}`;
    div.textContent = msg.message;
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
        div.textContent = msg.message;

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
endBtn?.addEventListener('click', function(){
    if (!currentSession || !socket) return;
    chatInput.value = '/endchat';
    sendAdminMessage();
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
        if (!data.success || !Array.isArray(data.data)) return;
        const messages = data.data.map(row => ({
            sender:  row.sender  || 'visitor',
            message: row.message || '',
            created_at: row.created_at || ''
        })).filter(m => m.message);

        sessionMessages[sessionId] = messages.slice();
        if (chatMessages && currentSession === sessionId) {
            renderMessageBatch(messages);
        }
    })
    .catch(err => {
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
            updateSupportStatus(0);
        }
    });
}

/* ---------- Auto-offline if admin closes tab ---------- */
/* window.addEventListener('beforeunload', () => {
    if(socket) {
        socket.emit("unregister-support");
    }
}); */

/* ---------- Init ---------- */
document.addEventListener('DOMContentLoaded', () => {
    if (livechatPage) {
        initAdminSocket();
        requestNotificationPermission();
        syncNotificationCheckbox();
    }
});