<div id="techno-livechat-admin" <?php echo $online == 1 ? 'class="' . esc_attr('online') . '"' : ''; ?>>
    <a id="notifHowTo"><span>ℹ️</span> How to enable <strong>Desktop & Browser Notification</strong></a>
    <!-- Left panel: active visitors -->
    <div id="techno-livechat-admin-visitors">
        <div id="techno-support-switch">
            <span>Live Chat</span>
            <label class="techno-switch">
                <input type="checkbox" id="techno-admin-toggle-online" <?php checked($online, 1); ?>>
                <span class="techno-slider"></span>
            </label>
            <span id="techno-toggle-label">
                <?php 
                if( $server ){
                    echo esc_html($online == 1 ? 'Online' : 'Offline');
                }else{
                    echo esc_html('Server Offline');
                }
                
                ?>
            </span>
        </div>
        <h3>Active Visitors</h3>
        <ul id="techno-active-visitors"></ul>
    </div>

    <!-- Right panel: chat messages -->
    <div id="techno-livechat-admin-chatmsgs">
        <div id="techno-admin-chat-window" class="disabled">
            <div id="techno-admin-chat-header">Chatting with: N/A</div>
            <div id="techno-admin-chat-messages"></div>
        </div>
        <div style="display:flex; margin-top:10px; gap: 10px;">
            <input type="text" id="techno-admin-chat-input" disabled/>
            <button id="techno-admin-chat-send" disabled>Offline</button>
        </div>
    </div>

</div>