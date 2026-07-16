<div id="techno-history-admin">
    <div id="techno-history-admin-visitors">
        <h3>Chat History</h3>
        <div class="techno-chathistory-picker">
            <label>From: <input type="date" id="historyget-from" value="<?php echo esc_attr($from); ?>" placeholder="From Date"/></label>
            <label>To: <input type="date" id="historyget-to" value="<?php echo esc_attr($today); ?>" placeholder="From To"/></label>
            <button type="button" id="historyget">Go</button>
        </div>
        <ul id="techno-visitors">
        </ul>
        <div id="history-export-control" class="disabled">
            <label>Select All 
                <input type="checkbox" id="history-select-all"/>
            </label>
            <button type="button" id="delete-history">Delete</button>
            <button type="button" id="export-history">Export</button>
        </div>
    </div>

    <!-- Right panel: chat messages -->
    <div id="techno-history-admin-chatmsgs">
        <div id="techno-admin-chat-window">
            <div id="techno-admin-chat-header">History: N/A</div>
            <div id="techno-admin-chat-messages"></div>
        </div>
    </div>

    <div class="techno-chat-admin-loader">Loading...</div>
</div>