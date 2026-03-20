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

const livechatPage = document.getElementById('techno-livechat-admin');
const toggleInput = document.getElementById('techno-admin-toggle-online');
const toggleLabel = document.getElementById('techno-toggle-label');
const chatInput = document.getElementById('techno-admin-chat-input');
const sendBtn   = document.getElementById('techno-admin-chat-send');
const activeVisitors = document.getElementById('techno-active-visitors');

let currentSession = null;
let adminPollTimer = null;
let adminLastId = 0;

function updateChatState(isOnline) {
    if (!chatInput || !sendBtn) return;

    chatInput.disabled = !isOnline;
    sendBtn.disabled   = !isOnline;

    // Optional UX improvement
    chatInput.placeholder = isOnline 
        ? 'Type a message...' 
        : 'Support is offline...';

    sendBtn.textContent = isOnline 
        ? 'Send' 
        : 'Offline';
}

function openSession(sessionId) {
    currentSession = sessionId;
    adminLastId = 0;
    document.getElementById('techno-admin-chat-window').innerHTML = '';
    clearInterval(adminPollTimer);
    adminPollTimer = setInterval(adminPollMessages, 2000);
    adminPollMessages(); // immediate first fetch
}

function adminPollMessages() {
    fetch(technoLivechat.ajax_url, { method:'POST', body: new URLSearchParams({
        action: 'techno_livechat_poll',
        nonce: technoLivechat.nonce,
        session_id: currentSession,
        after_id: adminLastId
    })})
    .then(r => r.json())
    .then(data => {
        const win = document.getElementById('techno-admin-chat-window');
        data.data.messages.forEach(msg => {
            const div = document.createElement('div');
            div.className = `techno-livechat-msg ${msg.sender}`;
            div.textContent = msg.message;
            win.appendChild(div);
            adminLastId = Math.max(adminLastId, msg.id);
        });
        win.scrollTop = win.scrollHeight;
    });
}

function sendHeartbeat() {
    fetch(technoLivechat.ajax_url, {
        method: 'POST',
        body: new URLSearchParams({
            action: 'techno_admin_heartbeat',
            nonce: technoLivechat.nonce
        })
    });
}

/* Poll active visitor sessions */
if (activeVisitors) {
    setInterval(() => {
        fetch(technoLivechat.ajax_url, { method:'POST', body: new URLSearchParams({
            action: 'techno_livechat_get_sessions', nonce: technoLivechat.nonce
        })})
        .then(r => r.json())
        .then(data => {
            activeVisitors.innerHTML = '';
            data.data.sessions.forEach(sess => {
                const li = document.createElement('li');
                li.textContent = sess;
                li.onclick = () => openSession(sess);
                if (sess === currentSession) li.classList.add('active');
                activeVisitors.appendChild(li);
            });
        });
    }, 5000);
}

/* Support Online toggle */
if (toggleInput) {
    toggleInput.addEventListener('change', () => {

        fetch(technoLivechat.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'techno_toggle_support_online',
                nonce: technoLivechat.nonce
            })
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                alert('Error');
                toggleInput.checked = !toggleInput.checked;
                return;
            }
            const isOnline = data.data.online;
            toggleInput.checked = isOnline;
            toggleLabel.textContent = isOnline ? 'Online' : 'Offline';
            updateChatState(isOnline);
        })
        .catch(() => {
            alert('Network error');
            toggleInput.checked = !toggleInput.checked;
        });

    });
}

/* Heartbeat check support online status */
if( livechatPage ){
    sendHeartbeat();
    setInterval(sendHeartbeat, technoLivechat.heartbeathz);
}

/* Send chat */
sendBtn?.addEventListener('click', () => {
    const msg = chatInput.value.trim();
    if (!msg || !currentSession) return;
    chatInput.value = '';

    fetch(technoLivechat.ajax_url, { method:'POST', body: new URLSearchParams({
        action: 'techno_livechat_admin_send',
        nonce: technoLivechat.nonce,
        session_id: currentSession,
        message: msg
    })});
});

document.addEventListener('DOMContentLoaded', () => {
    if (toggleInput) {
        updateChatState(toggleInput.checked);
    }
});