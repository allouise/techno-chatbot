(function( $ ) {
'use strict';
    /* Color Picker */
    $('.techno-color-field').wpColorPicker({ palettes: true });

    /* Media Uploader */
    $('.techno-upload-button').click(function (e) {
        e.preventDefault();
        var button = $(this);
        var input = button.prev('input');
        var mediaUploader = wp.media({
            title: 'Select Chatbot Icon',
            button: { text: 'Use this icon' },
            multiple: false
        });

        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            input.val(attachment.url);
        });
        mediaUploader.open();
    });

})( jQuery );

/* ---------- Admin WebSocket Section ---------- */
const livechatPage = document.getElementById('techno-livechat-admin');
const toggleInput = document.getElementById('techno-admin-toggle-online');
const toggleLabel = document.getElementById('techno-toggle-label');
const chatInput = document.getElementById('techno-admin-chat-input');
const sendBtn   = document.getElementById('techno-admin-chat-send');
const activeVisitors = document.getElementById('techno-active-visitors');
const chatWindow = document.getElementById('techno-admin-chat-window');
const chatToggle = document.getElementById('techno-support-switch');
const chatHeader = document.getElementById('techno-admin-chat-header'); //asd

let currentSession = null, 
    adminLastId = 0, 
    socket = null, 
    sessionMap = {}, 
    sessionMapMeta = {};

function initAdminSocket() {
    socket = io(technoLivechat.ws_url, { 
        transports: ['websocket'], 
        /* reconnection: false, */
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
        if (toggleLabel) toggleLabel.textContent = "Server Offline";

        updateChatState(false);
        updateSupportStatus(0);
    });

    /* On Connect */
    socket.on("connect", () => {
        loadActiveVisitors();
        chatToggle?.classList.add('active');
        if (toggleInput?.checked) {
            socket.emit("register-support");
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
            incomingMeta[sid] = { active: s.active ?? true }; // ← store active flag
        });

        Object.entries(incoming).forEach(([sid, name]) => {
            if (!sessionMap[sid]) notifyNewSession(sid, name);
        });

        sessionMap = incoming;
        sessionMapMeta = incomingMeta; // ← update meta
        renderActiveVisitors();
    });

    /*
     * receive-message: incoming message for the open session.
     * Show visitor messages in real time on the admin side.
     */
    socket.on("receive-message", (msg) => {
        if (msg.session_id !== currentSession) return;
        if (msg.sender === 'visitor') addAdminMessage({ sender: 'visitor', message: msg.message });
    });
}

/* 
 * Helpers
 */
function updateChatState(isOnline) {
    if (!chatInput || !sendBtn) return;
    chatInput.disabled = !isOnline;
    sendBtn.disabled   = !isOnline;
    chatInput.placeholder = isOnline ? 'Type a message...' : 'Support is offline...';
    sendBtn.textContent = isOnline ? 'Send' : 'Offline';
}

function updateSupportStatus(force = null) {
    force = (force == 1)? 1 : 0;
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
            console.log(res);
            const online = res.data.online;
            if (toggleInput) toggleInput.checked = online;
            if (toggleLabel) {
                toggleLabel.textContent = online 
                    ? 'Online' 
                    : ( res.data.server_offline ? 'Server Offline' : 'Offline');
            }
            updateChatState(online);
        }
    });
}

/*
 * openSession — join the session room, clear the chat window,
 * update the header with the visitor's name, highlight the list item.
 */
function openSession(sessionId) {
    currentSession = sessionId;
    socket.emit("join-session", { session_id: sessionId }); 
    adminLastId = 0;

    /* Clear window & update header */
    if (chatWindow) chatWindow.innerHTML = '';
    if (chatHeader) {
        const name = sessionMap[sessionId] || sessionId;
        chatHeader.textContent = 'Chat with: ' + name;
    }

    /* Highlight active list item */
    document.querySelectorAll('#techno-active-visitors li').forEach(li => {
        li.classList.toggle('open', li.dataset.session === sessionId);
    });

    /* Enable input when a session is open and support is online */
    if (toggleInput?.checked) updateChatState(true);
}

function addAdminMessage(msg) {
    if(!chatWindow) return;
    const div = document.createElement('div');
    div.className = `techno-livechat-msg ${msg.sender}`;
    div.textContent = msg.message;
    chatWindow.appendChild(div);
    chatWindow.scrollTop = chatWindow.scrollHeight;
    adminLastId = Math.max(adminLastId, msg.id || 0);
}

/*
 * notifyNewSession — log
 */
function notifyNewSession(sessionId, visitorName) {
    console.log("New visitor:", visitorName, "("+sessionId+")");
    /* Optional: new Audio('/path/notification.mp3').play(); */
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

        const idSpan = document.createElement('span');
        idSpan.className = 'techno-visitor-sid';
        idSpan.textContent = sid;
        idSpan.title = sid;

        li.appendChild(nameSpan);
        li.appendChild(idSpan);
        activeVisitors.appendChild(li);
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

function sendAdminMessage() {
    const msg = chatInput.value.trim();
    if (!msg || !currentSession || !socket) return;
    chatInput.value = '';

    socket.emit("send-message", {
        session_id: currentSession,
        message: msg,
        sender: "admin"
    });
    addAdminMessage({ sender: 'admin', message: msg });
}

/* ---------- Support online toggle ---------- */
if(toggleInput) {
    toggleInput.addEventListener('change', () => {
        if(!socket) return;
        
        if(toggleInput.checked) {
            socket.emit("register-support");
            updateSupportStatus(1);
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
    if(livechatPage) initAdminSocket();
});