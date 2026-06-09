<?php

/**
 * @link              https://technodreamwebdesign.com/techno-chatbot/
 * @since             1.0.0
 * @package           Techno_Chatbot
 *
 * @wordpress-plugin
 * Plugin Name:       Techno Chatbot
 * Plugin URI:        https://technodreamwebdesign.com/techno-chatbot
 * Description:       Technodream Chatbot
 * Version:           1.0.2
 * Author:            Technodream
 * Author URI:        https://technodreamwebdesign.com/techno-chatbot/
 * Text Domain:       techno-chatbot
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'TECHNO_CHATBOT_VERSION', '1.0.2' );
define( 'TECHNO_CHATBOT_FILEBASE', plugin_basename( __FILE__ ) );
define( 'TECHNO_CHATBOT_FOLDER_URL', plugins_url( '', __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_techno_chatbot() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-techno-chatbot-activator.php';
	Techno_Chatbot_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_techno_chatbot() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-techno-chatbot-deactivator.php';
	Techno_Chatbot_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_techno_chatbot' );
register_deactivation_hook( __FILE__, 'deactivate_techno_chatbot' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-techno-chatbot.php';

/**
 * Helpers
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-techno-chatbot-helpers.php';

/**
 * Plugin Update Checker
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-techno-chatbot-updater.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_techno_chatbot() {

	$plugin = new Techno_Chatbot();
	$plugin->run();

}
run_techno_chatbot();
