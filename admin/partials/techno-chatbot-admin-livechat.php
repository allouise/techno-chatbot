<div id="techno-livechat-admin" <?php echo ($online == 1)? 'class="online"' : ''; ?>>
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
                    echo ($online == 1) ? 'Online' : 'Offline'; 
                }else{
                    echo 'Server Offline';
                }
                
                ?>
            </span>
        </div>
        <h3>Active Visitors</h3>
        <ul id="techno-active-visitors"></ul>
    </div>

    <!-- Right panel: chat messages -->
    <div id="techno-livechat-admin-chatmsgs">
        <div id="techno-admin-chat-window"></div>
        <div style="display:flex; margin-top:10px;">
            <input type="text" id="techno-admin-chat-input" disabled/>
            <button id="techno-admin-chat-send" disabled>Send</button>
        </div>
    </div>

</div>