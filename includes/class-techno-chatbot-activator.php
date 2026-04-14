<?php

/**
 * Fired during plugin activation
 *
 * @link       https://technodreamwebdesign.com/
 * @since      1.0.0
 *
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/includes
 * @author     Technodream <al.esilverconnect@gmail.com>
 */
class Techno_Chatbot_Activator {

	/**
	 * Plugin activate functions
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		/*  
		 * Scheduled license checking
		 */
		if ( ! wp_next_scheduled( 'techno_chatbot_daily_license_check' ) ) {
			wp_schedule_event( time(), 'daily', 'techno_chatbot_daily_license_check' );
		}

		/*
		 * Create live chat messages table
		 */
		self::create_livechat_table();
	}

	/**
	 * Create the live chat messages DB table
	 *
	 * @since    1.0.0
	 */
	public static function create_livechat_table() {
		global $wpdb;
		$table   = $wpdb->prefix . 'techno_livechat_messages';
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table} (
			id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			session_id   VARCHAR(64)     NOT NULL,
			sender       ENUM('visitor','admin','bot') NOT NULL,
			message      TEXT            NOT NULL,
			visitor_name VARCHAR(100)    DEFAULT NULL,
			created_at   DATETIME        DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			INDEX idx_session (session_id),
			INDEX idx_created (created_at)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

}
