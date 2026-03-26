(function( $ ) {
'use strict';
    /* Color Picker */
    $('.techno-color-field').wpColorPicker();

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

let currentSession = null;
let adminLastId = 0;
let socket = null;

function initAdminSocket() {
    socket = io(technoLivechat.ws_url, { transports: ['websocket'] });
    socket.on("connect", () => {
        console.log("Admin WS connected:", socket.id);
        socket.emit("register-support");
        loadActiveVisitors();
    });
    socket.on("receive-message", (msg) => {
        if (msg.session_id !== currentSession) return;
        addAdminMessage(msg);
    });
    socket.on("support-status", (data) => {
        const online = !!data.online;
        toggleInput.checked = online;
        toggleLabel.textContent = online ? 'Online' : 'Offline';
        updateChatState(online);
    });
    socket.on("active-sessions", (sessions) => {
        renderActiveVisitors(sessions);
    });
}

/* ---------- Helpers ---------- */
function updateChatState(isOnline) {
    if (!chatInput || !sendBtn) return;

    chatInput.disabled = !isOnline;
    sendBtn.disabled   = !isOnline;
    chatInput.placeholder = isOnline ? 'Type a message...' : 'Support is offline...';
    sendBtn.textContent = isOnline ? 'Send' : 'Offline';
}

function updateSupportStatus() {
    fetch(technoLivechat.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'techno_toggle_support_online',
            nonce: technoLivechat.nonce
        })
    })
    .then(res => res.json())
    .then(res => {
        if(res.success) {
            const online = res.data.online;
            toggleInput.checked = online;
            toggleLabel.textContent = online ? 'Online' : 'Offline';
            updateChatState(online);
        }
    });
}

function openSession(sessionId) {
    currentSession = sessionId;
    socket.emit("join-session", { session_id: sessionId }); 
    adminLastId = 0;
    if(chatWindow) chatWindow.innerHTML = '';
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

/* ---------- Active visitors ---------- */
function renderActiveVisitors(sessions) {
    if(!activeVisitors) return;
    activeVisitors.innerHTML = '';

    sessions.forEach(sess => {
        const li = document.createElement('li');
        li.textContent = sess;
        li.onclick = () => openSession(sess);

        if (sess === currentSession) {
            li.classList.add('active');
        }

        activeVisitors.appendChild(li);
    });
}

function loadActiveVisitors() {
    if(!socket) return;
    socket.emit("get-active-sessions");
}

/* ---------- Send chat ---------- */
sendBtn?.addEventListener('click', () => {
    const msg = chatInput.value.trim();
    if (!msg || !currentSession || !socket) return;
    chatInput.value = '';

    socket.emit("send-message", {
        session_id: currentSession,
        message: msg,
        sender: "admin"
    });

    addAdminMessage({ sender: 'admin', message: msg });
});

/* ---------- Support online toggle ---------- */
if(toggleInput) {
    toggleInput.addEventListener('change', () => {
        if(!socket) return;
        updateSupportStatus();
        if(toggleInput.checked) {
            socket.emit("register-support");
        } else {
            socket.emit("unregister-support");
        }
    });
}

/* ---------- Auto-offline if admin closes tab ---------- */
window.addEventListener('beforeunload', () => {
    if(socket) {
        socket.emit("unregister-support");
    }
});

/* ---------- Init ---------- */
document.addEventListener('DOMContentLoaded', () => {
    if(toggleInput) updateChatState(toggleInput.checked);
    if(livechatPage) initAdminSocket();
});