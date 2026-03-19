<?php

/**
 * Admin Settings view: Knowledgebase
 *
 * @link       https://technodreamwebdesign.com/
 * @since      1.0.0
 *
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/admin/partials
 */

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'openai';
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Techno Chatbot Settings', 'techno-chatbot' ); ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="?page=techno-chatbot-knowledgebase&tab=openai"
           class="nav-tab <?php echo $active_tab === 'openai' ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e( 'Open AI', 'techno-chatbot' ); ?>
        </a>
    </h2>
    <?php settings_errors(); ?>
    <form method="post" action="options.php">
        <h3>Upgrade to our Master Plan to access this.</h3>
        <a href="#" class="btn button btn-primary">Upgrade Now!</a>
        <?php
        
        /* if ($active_tab == 'general') {
            settings_fields('techno_chatbot_settings_group');
            do_settings_sections('techno-chatbot-general');
        } else if ($active_tab == 'styles') {
            settings_fields('techno_chatbot_styles_group');
            do_settings_sections('techno-chatbot-styles');
        }

        submit_button(); */
        ?>
    </form>
</div>