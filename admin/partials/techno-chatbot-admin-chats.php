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

    <?php if( $active_tab === 'livechat' ){
        include plugin_dir_path( __FILE__ ) . 'techno-chatbot-admin-livechat.php';
    } ?>

    <?php } else { techno_chatbot_msgformat($plans['message']); } ?>
</div>