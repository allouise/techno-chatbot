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
 * Version:           1.1.7
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
define( 'TECHNO_CHATBOT_VERSION', '1.1.7' );
define( 'TECHNO_CHATBOT_FILEBASE', plugin_basename( __FILE__ ) );
define( 'TECHNO_CHATBOT_FOLDER_URL', plugins_url( '', __FILE__ ) );
define( 'TECHNO_CHATBOT_SUPPORT_EMAIL', 'customersupport2@technodreamcenter.com' );
define ( 'TECHNO_CHATBOT_SUPPORTED_LANGUAGE', [ 'Afrikaans' => 'af', 'Albanian' => 'sq', 'Amharic' => 'am', 'Arabic' => 'ar', 'Armenian' => 'hy', 'Azerbaijani' => 'az', 'Basque' => 'eu', 'Belarusian' => 'be', 'Bengali' => 'bn', 'Bosnian' => 'bs', 'Bulgarian' => 'bg', 'Burmese' => 'my', 'Catalan' => 'ca', 'Cebuano' => 'ceb', 'Chinese (Simplified)' => 'zh-CN', 'Chinese (Traditional)' => 'zh-TW', 'Cantonese' => 'yue', 'Croatian' => 'hr', 'Czech' => 'cs', 'Danish' => 'da', 'Dutch' => 'nl', 'Esperanto' => 'eo', 'Estonian' => 'et', 'Filipino (Tagalog)' => 'tl', 'Finnish' => 'fi', 'French' => 'fr', 'Galician' => 'gl', 'Georgian' => 'ka', 'German' => 'de', 'Greek' => 'el', 'Gujarati' => 'gu', 'Haitian Creole' => 'ht', 'Hausa' => 'ha', 'Hebrew' => 'he', 'Hindi' => 'hi', 'Hungarian' => 'hu', 'Icelandic' => 'is', 'Igbo' => 'ig', 'Ilocano' => 'ilo', 'Indonesian' => 'id', 'Irish' => 'ga', 'Italian' => 'it', 'Japanese' => 'ja', 'Javanese' => 'jv', 'Kannada' => 'kn', 'Kazakh' => 'kk', 'Khmer' => 'km', 'Kinyarwanda' => 'rw', 'Korean' => 'ko', 'Kurdish' => 'ku', 'Kyrgyz' => 'ky', 'Lao' => 'lo', 'Latin' => 'la', 'Latvian' => 'lv', 'Lithuanian' => 'lt', 'Luxembourgish' => 'lb', 'Macedonian' => 'mk', 'Malay' => 'ms', 'Malayalam' => 'ml', 'Maltese' => 'mt', 'Maori' => 'mi', 'Marathi' => 'mr', 'Mongolian' => 'mn', 'Nepali' => 'ne', 'Norwegian' => 'no', 'Odia' => 'or', 'Pashto' => 'ps', 'Persian (Farsi)' => 'fa', 'Polish' => 'pl', 'Portuguese' => 'pt', 'Punjabi' => 'pa', 'Romanian' => 'ro', 'Russian' => 'ru', 'Samoan' => 'sm', 'Scottish Gaelic' => 'gd', 'Serbian' => 'sr', 'Shona' => 'sn', 'Sindhi' => 'sd', 'Sinhala' => 'si', 'Slovak' => 'sk', 'Slovenian' => 'sl', 'Somali' => 'so', 'Spanish' => 'es', 'Sundanese' => 'su', 'Swahili' => 'sw', 'Swedish' => 'sv', 'Tamil' => 'ta', 'Tatar' => 'tt', 'Telugu' => 'te', 'Thai' => 'th', 'Turkish' => 'tr', 'Turkmen' => 'tk', 'Ukrainian' => 'uk', 'Urdu' => 'ur', 'Uzbek' => 'uz', 'Vietnamese' => 'vi', 'Welsh' => 'cy', 'Xhosa' => 'xh', 'Yiddish' => 'yi', 'Yoruba' => 'yo', 'Zulu' => 'zu', ] );

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

/**
 * Check if the plugin database/settings need an update on load.
 */
function techno_chatbot_check_for_updates() {
    $installed_version = get_option( 'techno_chatbot_version' );

    if ( $installed_version !== TECHNO_CHATBOT_VERSION ) {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-techno-chatbot-activator.php';
        Techno_Chatbot_Activator::activate();
    }
}
add_action( 'plugins_loaded', 'techno_chatbot_check_for_updates' );

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
