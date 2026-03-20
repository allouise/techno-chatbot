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

document.addEventListener('DOMContentLoaded', () => {
    const toggleInput = document.getElementById('techno-admin-toggle-online');
    const toggleLabel = document.getElementById('techno-toggle-label');

    if (toggleInput) {
        updateChatState(toggleInput.checked);
    }

    function updateChatState(isOnline) {
        const chatInput = document.getElementById('techno-admin-chat-input');
        const sendBtn   = document.getElementById('techno-admin-chat-send');

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
});