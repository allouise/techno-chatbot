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

		self::create_livechat_table();
		self::techno_chatbot_add_role();
		self::techno_chatbot_add_admin_capability();
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
			id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			session_id     VARCHAR(64)     NOT NULL,
			sender         ENUM('visitor','admin','bot') NOT NULL,
			message        TEXT            NOT NULL,
			name           VARCHAR(100)    DEFAULT NULL,
			message_type   ENUM('text','image','file','system') DEFAULT 'text',
			viewed_at      DATETIME DEFAULT NULL,
			user_agent 	   VARCHAR(255) DEFAULT NULL,
			ip_address     VARCHAR(45) DEFAULT NULL,
			created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			INDEX idx_session (session_id),
			INDEX idx_created (created_at)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Create the live chat messages DB table
	 *
	 * @since    1.0.0
	 */
	public static function techno_chatbot_add_role() {
		add_role(
			'chat_support',
			'Chat Support',
			[
				'read' => true,
				'techno_chat_support' => true,
			]
		);
	}

	/**
	 * Assign 
	 *
	 * @since    1.0.0
	 */
	public static function techno_chatbot_add_admin_capability()
	{
		$admin = get_role('administrator');
		if ($admin && !$admin->has_cap('techno_chat_support')) {
			$admin->add_cap('techno_chat_support');
		}
	}
}
