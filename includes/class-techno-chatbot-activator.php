<?php

/**
 * Fired during plugin activation
 *
 * @link       https://technodreamwebdesign.com/techno-chatbot/
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

		self::create_cb_messages_table();
		self::create_cb_conversation_table();
		self::techno_chatbot_add_role();
		self::techno_chatbot_add_admin_capability();

		if ( defined( 'TECHNO_CHATBOT_VERSION' ) ) {
			update_option( 'techno_chatbot_version', TECHNO_CHATBOT_VERSION );
		}
	}

	/**
	 * Create messages table
	 *
	 * @since    1.0.0
	 */
	public static function create_cb_messages_table() {
		global $wpdb;
		$table   = $wpdb->prefix . 'techno_cb_messages';
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table} (
			id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			conversation_id   BIGINT UNSIGNED DEFAULT NULL,
			sender            ENUM('visitor','admin','bot') NOT NULL,
			message           TEXT NOT NULL,
			message_type      VARCHAR(64) NOT NULL DEFAULT 'text',
			prompt_tokens 	  INT UNSIGNED DEFAULT NULL,
			completion_tokens INT UNSIGNED DEFAULT NULL,
			created_at        DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			INDEX idx_conversation_created (conversation_id, created_at)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Create conversation table
	 *
	 * @since    1.0.0
	 */
	public static function create_cb_conversation_table() {
		global $wpdb;
		$table   = $wpdb->prefix . 'techno_cb_conversations';
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table} (
			id             	BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			socket_id		VARCHAR(64) DEFAULT NULL,
			session_id     	VARCHAR(64) NOT NULL,
			user_id        	BIGINT UNSIGNED DEFAULT NULL,
			name           	VARCHAR(100) DEFAULT NULL,
			title           VARCHAR(100) DEFAULT NULL,
			metas      		TEXT DEFAULT NULL,
			created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
			ended_at        DATETIME DEFAULT NULL,
			PRIMARY KEY (id),
			INDEX idx_socket (socket_id),
			INDEX idx_session (session_id),
			INDEX idx_created (created_at),
			INDEX idx_ended (ended_at)
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
