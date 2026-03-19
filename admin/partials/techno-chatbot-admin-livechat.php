<?php

/**
 * Admin Settings view: Live Chat
 *
 * @link       https://technodreamwebdesign.com/
 * @since      1.0.0
 *
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/admin/partials
 */

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'livechat';
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Techno Chatbot Settings', 'techno-chatbot' ); ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="?page=techno-chatbot-livechat&tab=livechat" class="nav-tab <?php echo $active_tab === 'livechat' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e( 'Live Chat', 'techno-chatbot' ); ?>
        </a>
        <a href="?page=techno-chatbot-livechat&tab=history" class="nav-tab <?php echo $active_tab === 'history' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e( 'History', 'techno-chatbot' ); ?>
        </a>
    </h2>

    <?php $plans = techno_chatbot_feature('live_chat');
    if( $plans['allowed'] == true ){ ?>

    <?php if( $active_tab === 'livechat' ) : ?>
        <div id="techno-livechat-admin">
            
            <!-- Left panel: active visitors -->
            <div id="techno-livechat-admin-visitors">
                <h3>Active Visitors</h3>
                <ul id="techno-active-visitors"></ul>
                <button id="techno-admin-toggle-online">Go Online</button>
            </div>

            <!-- Right panel: chat messages -->
            <div id="techno-livechat-admin-chatmsgs">
                <div id="techno-admin-chat-window"></div>
                <div style="display:flex; margin-top:10px;">
                    <input type="text" id="techno-admin-chat-input"/>
                    <button id="techno-admin-chat-send">Send</button>
                </div>
            </div>

        </div>
    <?php endif; ?>

    <?php } else { techno_chatbot_msgformat($plans['message']); } ?>
</div>