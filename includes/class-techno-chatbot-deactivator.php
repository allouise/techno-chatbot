<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://technodreamwebdesign.com/techno-chatbot/
 * @since      1.0.0
 *
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/includes
 * @author     Technodream <al.esilverconnect@gmail.com>
 */
class Techno_Chatbot_Deactivator {

	/**
	 * Plugin deactivate functions
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		/*  
		 * Clear license scheduled checking
		 */
		wp_clear_scheduled_hook( 'techno_chatbot_daily_license_check' );
	}

}
