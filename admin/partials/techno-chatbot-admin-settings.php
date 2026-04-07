<?php

/**
 * Admin Settings area view for the plugin
 *
 * @link       https://technodreamwebdesign.com/
 * @since      1.0.0
 *
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/admin/partials
 */

$active_tab = isset($_GET['tab']) ? sanitize_key( $_GET['tab'] ) : 'general';
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Techno Chatbot Settings', 'techno-chatbot' ); ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="?page=techno-chatbot&tab=general" 
           class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">
           General
        </a>

        <a href="?page=techno-chatbot&tab=behaviors" 
           class="nav-tab <?php echo $active_tab == 'behaviors' ? 'nav-tab-active' : ''; ?>">
           Behaviors
        </a>

        <a href="?page=techno-chatbot&tab=texts" 
           class="nav-tab <?php echo $active_tab == 'texts' ? 'nav-tab-active' : ''; ?>">
           Messages/Texts
        </a>

        <a href="?page=techno-chatbot&tab=styles" 
           class="nav-tab <?php echo $active_tab == 'styles' ? 'nav-tab-active' : ''; ?>">
           Styles
        </a>

        <a href="?page=techno-chatbot&tab=license" 
           class="nav-tab <?php echo $active_tab == 'license' ? 'nav-tab-active' : ''; ?>">
           License/API
        </a>
    </h2>
    <?php settings_errors(); ?>
    <form method="post" action="options.php">
        <?php
        
        if ($active_tab == 'general') {
            settings_fields('techno_chatbot_general_group');
            do_settings_sections('techno-chatbot-general');
        } else if ($active_tab == 'behaviors') {
            settings_fields('techno_chatbot_behaviors_group');
            do_settings_sections('techno-chatbot-behaviors');
        } else if ($active_tab == 'texts') {
            settings_fields('techno_chatbot_texts_group');
            do_settings_sections('techno-chatbot-texts');
        }else if ($active_tab == 'styles') {
            settings_fields('techno_chatbot_styles_group');
            do_settings_sections('techno-chatbot-styles');
        }else if ($active_tab == 'license') {
            settings_fields('techno_chatbot_license_group');
            do_settings_sections('techno-chatbot-license');
        }

        submit_button();
        ?>
    </form>
</div>