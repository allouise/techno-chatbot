<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://technodreamwebdesign.com/techno-chatbot/
 * @since      1.0.0
 *
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/admin
 * @author     Technodream <al.esilverconnect@gmail.com>
 */
class Techno_Chatbot_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Required Classes
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Classes Related Classes
	 */
	protected $general_fields;
    protected $behaviors_fields;
    protected $texts_fields;
    protected $styles_fields;
    protected $license_fields;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->general_fields = new Techno_Chatbot_Admin_Fields_General( $plugin_name, $version );
		$this->behaviors_fields = new Techno_Chatbot_Admin_Fields_Behaviors( $plugin_name, $version );
		$this->texts_fields = new Techno_Chatbot_Admin_Fields_Texts( $plugin_name, $version );
    	$this->styles_fields  = new Techno_Chatbot_Admin_Fields_Styles( $plugin_name, $version );
		$this->license_fields  = new Techno_Chatbot_Admin_Fields_License( $plugin_name, $version );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/techno-chatbot-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker-alpha', plugin_dir_url( __FILE__ ) . 'js/wp-color-picker-alpha.min.js', array( 'wp-color-picker' ), $this->version, true );
		wp_enqueue_script( 'techno-admin-jquery', plugin_dir_url( __FILE__ ) . 'js/techno-chatbot-jquery.js', array( 'wp-color-picker', 'wp-color-picker-alpha', 'jquery' ), $this->version, true );

		$is_history_page = current_user_can('techno_chat_support') && isset($_GET['page']) && $_GET['page'] === 'techno-chatbot-livechat' && isset($_GET['tab']) && $_GET['tab'] === 'history';
		if ($is_history_page) {
			wp_enqueue_script( 'techno-admin-history', plugin_dir_url(__FILE__) . 'js/techno-chatbot-history.js', [], $this->version, true );
			wp_localize_script(
				'techno-admin-history',
				'technoHistory',
				[
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce'    => wp_create_nonce('techno_chatbot_nonce'),
				]
			);
		}
		
		$livechat_allowed = techno_chatbot_feature('live_chat');
    	$livechat_allowed = $livechat_allowed['allowed'] === true;
		if( $livechat_allowed && !$is_history_page ){
			wp_enqueue_script( 'techno-admin-script', plugin_dir_url( __FILE__ ) . 'js/techno-chatbot-livechat.js', [], $this->version, true );
			wp_enqueue_script( $this->plugin_name.'-socket-io', plugin_dir_url( __FILE__ ) . 'js/socket.io.min.js', array(), $this->version, true );

			$ws = techno_chatbot_websocket();
			$site = get_site_url();
			wp_localize_script(
				'techno-admin-script',
				'technoLivechat',
				[
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('techno_chatbot_nonce'),
					'ws_url'   => $ws->get_url(),
					'site_id'  => $site,
					'token'    => $ws->get_token($site),
					'site_name' => get_bloginfo('name'),
					'notification_sound' => TECHNO_CHATBOT_FOLDER_URL . '/notification.mp3'
				]
			);
		}

		$ai_allowed = techno_chatbot_feature('ai_training');
    	$ai_allowed = $ai_allowed['allowed'] === true;
		if( $ai_allowed && $screen && $screen->post_type === 'techno_chatbot_aidb' ){
			wp_enqueue_script( 'techno-aidb-script', plugin_dir_url( __FILE__ ) . 'js/techno-chatbot-aidb.js', [], $this->version, true );
			wp_localize_script(
				'techno-aidb-script',
				'technoaidb',
				[
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce'    => wp_create_nonce('techno_aidb_nonce'),
					'post_id'  => get_the_ID(),
				]
			);
		}

	}

	/**
	 * Hide All Menus from Chat Support
	 *
	 * @since    1.0.0
	 */
	public function hide_everything_from_support() {
		$user = wp_get_current_user();
		if ( ! in_array('chat_support', (array) $user->roles, true) ) return;

		global $menu;
		$allowed = [
			'techno-chatbot-livechat'
		];

		foreach ($menu as $item) {
			if (!isset($item[2])) continue;
			if (!in_array($item[2], $allowed, true)) {
				remove_menu_page($item[2]);
			}
		}
	}

	/**
	 * Block All Menu's page from Chat Support
	 *
	 * @since    1.0.0
	 */
	public function block_everything_from_support() {
		$user = wp_get_current_user();
		if ( ! in_array('chat_support', (array) $user->roles, true) ) return;

		if ( wp_doing_ajax() ) return;
		$allowed_page = 'techno-chatbot-livechat';
		if ( isset($_GET['page']) && $_GET['page'] === $allowed_page ) return;

		wp_redirect( admin_url('admin.php?page=techno-chatbot-livechat') );
		exit;
	}

	/**
	 * Register the administration menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		add_menu_page(
			__( 'Techno Chatbot', 'techno-chatbot' ),
			__( 'Techno Chatbot', 'techno-chatbot' ),
			'manage_options',
			'techno-chatbot',
			array( $this, 'display_settings_page' ),
			'dashicons-format-chat',
			26
		);

		add_submenu_page(
			'techno-chatbot',
			__( 'Settings', 'techno-chatbot' ),
			__( 'Settings', 'techno-chatbot' ),
			'manage_options',
			'techno-chatbot',
			array( $this, 'display_settings_page' )
		);

		add_submenu_page(
			'techno-chatbot',
			__( 'Chats', 'techno-chatbot' ),
			__( 'Chats', 'techno-chatbot' ),
			'techno_chat_support',
			'techno-chatbot-livechat',
			array( $this, 'display_chats_page' )
		);

		add_submenu_page(
			'techno-chatbot',
			__( 'FAQ - Training Data', 'techno-chatbot' ),
			__( 'FAQ - Training Data', 'techno-chatbot' ),
			'manage_options',
			'edit.php?post_type=techno_chatbot_faq'
		);

		add_submenu_page(
			'techno-chatbot',
			__( 'AI Knowledgebase', 'techno-chatbot' ),
			__( 'AI Knowledgebase', 'techno-chatbot' ),
			'manage_options',
			'edit.php?post_type=techno_chatbot_aidb'
		);
	}

	/**
	 * Add settings link in plugin list page.
	 *
	 * @since    1.0.0
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="' . admin_url( 'admin.php?page=techno-chatbot' ) . '">Settings</a>';
		array_push( $links, $settings_link );
		return $links;
	}

	/**
	 * Register the administration settings.
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {
		$this->general_fields->register( 'techno-chatbot-general' );
		$this->behaviors_fields->register( 'techno-chatbot-behaviors' );
		$this->texts_fields->register( 'techno-chatbot-texts' );
		$this->styles_fields->register( 'techno-chatbot-styles' );
		$this->license_fields->register( 'techno-chatbot-license' );
	}

	/**
	 * Render the settings page.
	 *
	 * @since    1.0.0
	 */
	public function display_settings_page() {
		include_once plugin_dir_path( __FILE__ ) . 'partials/techno-chatbot-admin-settings.php';
	}

	/**
	 * Render the Live Chat page.
	 *
	 * @since    1.0.0
	 */
	public function display_chats_page() {
		if (!current_user_can('techno_chat_support')) {
			wp_die('Unauthorized');
		}
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'livechat';
		switch ($active_tab) {
			case 'livechat':
				$online = (int) get_user_meta( get_current_user_id(), 'techno_chat_online', true );
				$server = techno_wss_check();
				$online = !$server? false : $online;
			break;
			case 'history':
				$today = current_time('Y-m-d');
				$from  = date('Y-m-d', strtotime('-29 days', strtotime($today)));
			break;
		}
		include_once plugin_dir_path( __FILE__ ) . 'partials/techno-chatbot-admin-chats.php';
	}

	/**
	 * Toggle Support Online
	 *
	 * @since    1.0.0
	 */
	public function toggle_support_online() {
		check_ajax_referer('techno_chatbot_nonce', 'nonce');

		if (!current_user_can('techno_chat_support')) {
			wp_send_json_error();
		}
		
		$userID = get_current_user_id();
		$status = get_transient('techno_wss_status');
		if ($status === false) {
			$status = techno_wss_check() ? 1 : 0;
			set_transient('techno_wss_status', $status, 5);
		}

		if( !$status ){
			update_user_meta( $userID, 'techno_chat_online', 0);
			wp_send_json_success(['online' => 0, 'server_offline' => 1]);
		}
		
		$force = isset($_POST['force_status']) && $_POST['force_status'] == 1 ? 1 : 0;
		if ( isset($_POST['force_status']) ) {
			update_user_meta( $userID, 'techno_chat_online', $force);
			wp_send_json_success(['online' => (bool)$force, 'forced' => 1]);
		}

		$current = (int) get_user_meta( $userID, 'techno_chat_online', true );
		$onlinestatus = $current ? 0 : 1;
		update_user_meta( $userID, 'techno_chat_online', $onlinestatus);

		wp_send_json_success(['online' => (bool)$onlinestatus, 'before' => $current]);
	}

	/**
	 * Save admin chat message
	 *
	 * @since    1.0.0
	 */
	public function save_admin_chat_message() {
		check_ajax_referer( 'techno_chatbot_nonce', 'nonce' );

		if ( ! current_user_can( 'techno_chat_support' ) ) {
			wp_send_json_error( 'Permission denied', 403 );
		}

		/* ---- Validate Inputs ---- */
		$socket_id = isset( $_POST['socket_id'] ) ? sanitize_text_field( wp_unslash( $_POST['socket_id'] ) ) : ( isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '' );
		$message = isset( $_POST['message'] ) ? trim( sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) ) : '';
		/* $message_type = isset( $_POST['message_type'] ) ? sanitize_text_field( wp_unslash( $_POST['message_type'] ) ) : 'text'; */

		if ( empty( $socket_id ) || empty( $message ) ) {
			wp_send_json_error( 'Missing required fields', 400 );
		}

		/* Socket ID / Session ID format check */
		if ( ! preg_match( '/^[a-zA-Z0-9\-_]+$/', $socket_id ) ) {
			wp_send_json_error( 'Invalid socket_id format', 400 );
		}

		/* Message length guard */
		if ( mb_strlen( $message ) > 2000 ) {
			wp_send_json_error( 'Message too long', 400 );
		}

		/* Validate message_type whitelist */
		/* $allowed_types = [ 
			'text', 'phone_input', 'email_input', 'time_input', 'name_input', 
			'phone_input_answer', 'email_input_answer', 'time_input_answer', 
			'name_input_answer', 'system' 
		];

		if ( ! in_array( $message_type, $allowed_types, true ) ) {
			$message_type = 'text';
		} */

		global $wpdb;
		$table_conversations = $wpdb->prefix . 'techno_cb_conversations';
		$table_messages = $wpdb->prefix . 'techno_cb_messages';

		// 1. Fetch conversation ID using socket_id or session_id
		$conversation = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, ended_at FROM {$table_conversations} WHERE socket_id = %s OR session_id = %s LIMIT 1",
				$socket_id,
				$socket_id
			)
		);

		if ( ! $conversation || $wpdb->last_error ) {
			wp_send_json_error( 'Conversation not found', 404 );
		}

		if ( ! is_null( $conversation->ended_at ) ) {
			wp_send_json_error( 'Cannot send message to a closed conversation', 400 );
		}
		
		$conversation_id = (int) $conversation->id;
		
		// 2. Insert admin message with resolved conversation_id
		$result = $wpdb->insert(
			$table_messages,
			[
				'conversation_id' => $conversation_id,
				'sender' => 'admin',
				'message' => $message,
			],
			[
				'%d', // conversation_id
				'%s', // sender
				'%s', // message
			]
		);

		if ( false === $result ) {
			wp_send_json_error( [ 'message' => 'Database error saving message' ], 500 );
		}

		wp_send_json_success( [ 
			'id' => $wpdb->insert_id,
			'conversation_id' => $conversation_id 
		] );
	}

	/**
	 * Return Chat History
	 *
	 * @since    1.0.0
	 */
	public function get_chat_history() {
		check_ajax_referer( 'techno_chatbot_nonce', 'nonce' );

		if ( ! current_user_can( 'techno_chat_support' ) ) {
			wp_send_json_error( 'Permission denied', 403 );
		}

		$socket_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
		if ( ! $socket_id ) {
			wp_send_json_error( 'Missing session_id', 400 );
		}

		if ( ! preg_match( '/^[a-zA-Z0-9\-_]+$/', $socket_id ) ) {
			wp_send_json_error( 'Invalid session format', 400 );
		}

		global $wpdb;
		$table_conversations = $wpdb->prefix . 'techno_cb_conversations';
		$table_messages      = $wpdb->prefix . 'techno_cb_messages';

		// 1. Fetch the conversation row first
		$conversation = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, socket_id, session_id, name, ended_at 
				FROM {$table_conversations} 
				WHERE socket_id = %s OR session_id = %s 
				LIMIT 1",
				$socket_id,
				$socket_id
			)
		);

		// If conversation doesn't exist, exit early
		if ( ! $conversation || $wpdb->last_error ) {
			wp_send_json_error( 'Converastion not found', 404 );
		}

		// 2. Fetch the messages using the conversation ID
		$messages = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT sender, message, message_type, created_at 
				FROM {$table_messages} 
				WHERE conversation_id = %d 
				ORDER BY id ASC",
				$conversation->id
			),
			ARRAY_A
		);

		// 3. Return both metadata and messages
		wp_send_json_success( [
			'visitor_name' => $conversation->name,
			'ended_at' => $conversation->ended_at,
			'messages' => $messages ?: []
		] );
	}

	/**
	 * Get Live Chat Visitors
	 *
	 * @since    1.1.0
	 */
	public function get_active_livechats() {
		check_ajax_referer( 'techno_chatbot_nonce', 'nonce' );
		if ( ! current_user_can( 'techno_chat_support' ) ) wp_send_json_error( [ 'message' => 'Permission denied' ], 403 );

		global $wpdb;
		$table = $wpdb->prefix . 'techno_cb_conversations';
		$rows = $wpdb->get_results("SELECT * FROM {$table} WHERE socket_id IS NOT NULL AND socket_id != '' AND ended_at IS NULL ORDER BY created_at DESC", ARRAY_A);

		wp_send_json_success($rows);
	}

	/**
	 * End chat and archive messages
	 *
	 * @since 1.0.7
	 */
	public function end_chat() {
		check_ajax_referer( 'techno_chatbot_nonce', 'nonce' );
		if ( ! current_user_can( 'techno_chat_support' ) ) wp_send_json_error( [ 'message' => 'Permission denied' ], 403 );


		/* ---- Check Conversation & Session ID ---- */
		$socket_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
		if ( empty( $socket_id ) ) {
			wp_send_json_error( [ 'message' => 'Missing Parameters' ], 400 );
		}
		if ( ! preg_match( '/^[a-zA-Z0-9\-_]+$/', $socket_id ) ) {
			wp_send_json_error( [ 'message' => 'Invalid session formats' ], 400 );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'techno_cb_conversations';
		$updated = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET socket_id = %s AND ended_at IS NULL",
				$socket_id,
			)
		);

		if ( false === $updated ) wp_send_json_error( [ 'message' => 'Database error while ending chat' ], 500 );
		if ( 0 === $updated ) wp_send_json_error( [ 'message' => 'Conversation not found or already ended' ], 404 );

		wp_send_json_success( [ 'message' => 'Conversation ended successfully' ] );

		// $endIdleChatMsg = Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_idle_guests_message');
	}
	
	/**
	 * Get Archived List
	 *
	 * @since 1.0.7
	 */
	public function get_archived_chat_list(){
		check_ajax_referer('techno_chatbot_nonce','nonce');
		if ( ! current_user_can( 'techno_chat_support' ) ) wp_send_json_error( [ 'message' => 'Permission denied' ], 403 );
			
		$raw_from = isset( $_POST['from'] ) ? sanitize_text_field( wp_unslash( $_POST['from'] ) ) : '';
    	$raw_to = isset( $_POST['to'] ) ? sanitize_text_field( wp_unslash( $_POST['to'] ) ) : '';

		if ( empty( $raw_from ) || empty( $raw_to ) ) {
			wp_send_json_error( [ 'message' => 'Missing required date parameters' ], 400 );
		}

		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw_from ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw_to ) ) {
			wp_send_json_error( [ 'message' => 'Invalid date format. Expected YYYY-MM-DD' ], 400 );
		}
		$start_date = $raw_from . ' 00:00:00';
		$end_date = $raw_to . ' 23:59:59';

		global $wpdb;
		$table = $wpdb->prefix.'techno_cb_conversations';
		$rows = $wpdb->get_results(
        $wpdb->prepare(
				"SELECT * FROM {$table} 
				WHERE created_at >= %s 
				AND created_at <= %s 
				AND ended_at IS NOT NULL 
				ORDER BY created_at DESC",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		if ( $wpdb->last_error ) {
			wp_send_json_error( [ 'message' => 'Database error retrieving archived chats' ], 500 );
		}

		wp_send_json_success( $rows ?: [] );
	}

	/**
	 * Get Archive Chat by session id
	 *
	 * @since 1.0.7
	 */
	public function get_archived_chat() {
		check_ajax_referer( 'techno_chatbot_nonce', 'nonce' );
		if ( ! current_user_can( 'techno_chat_support' ) ) wp_send_json_error( [ 'message' => 'Permission denied' ], 403 );

		global $wpdb;
		$session = sanitize_text_field($_POST['session']);
		$table = $wpdb->prefix.'techno_chat_history';
		$messages = $wpdb->get_results($wpdb->prepare(
			"SELECT *
			FROM {$table}
			WHERE session_id=%s
			ORDER BY created_at ASC",
			$session
		));
		wp_send_json_success($messages);
	}
	
	/**
	 * Delete Chat History
	 *
	 * @since 1.0.7
	 */
	public function delete_chat_history() {
		check_ajax_referer( 'techno_chatbot_nonce', 'nonce' );
		if ( ! current_user_can( 'techno_chat_support' ) ) wp_send_json_error( [ 'message' => 'Permission denied' ], 403 );

		$sessions = isset( $_POST['sessions'] ) ? (array) $_POST['sessions'] : [];
		$sessions = array_filter( array_map( 'sanitize_text_field', $sessions ) );

		if ( empty( $sessions ) ) wp_send_json_error( 'No sessions selected.' );

		global $wpdb;
		$table = $wpdb->prefix . 'techno_chat_history';
		$placeholders = implode( ',', array_fill( 0, count( $sessions ), '%s' ) );

		$query = $wpdb->prepare(
			"DELETE FROM {$table} WHERE session_id IN ($placeholders)", ...$sessions
		);
		$wpdb->query( $query );
		wp_send_json_success( ['deleted' => count( $sessions )] );
	}
	
	/**
	 * Export Chat History
	 *
	 * @since 1.0.7
	 */
	public function export_chat_history() {
		check_ajax_referer( 'techno_chatbot_nonce', 'nonce' );
		if ( ! current_user_can( 'techno_chat_support' ) ) wp_send_json_error( [ 'message' => 'Permission denied' ], 403 );

		$sessions = isset( $_POST['sessions'] ) ? (array) $_POST['sessions'] : [];
		$sessions = array_filter( array_map(
			static function ( $id ) {
				$id = sanitize_text_field( $id );
				return preg_match( '/^[A-Za-z0-9_-]{1,64}$/', $id ) ? $id : ''; },
			(array) $_POST['sessions']
		));
		if ( empty( $sessions ) ) wp_die( 'No sessions selected.' );

		global $wpdb;
		$table = $wpdb->prefix . 'techno_chat_history';
		$placeholders = implode(',', array_fill( 0, count( $sessions ), '%s' ));
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					session_id,
					user_id,
					name,
					sender,
					message,
					message_type,
					tokens,
					viewed_at,
					ip_address,
					user_agent,
					created_at
				FROM {$table}
				WHERE session_id IN ($placeholders)
				ORDER BY session_id ASC, created_at ASC
				",
				...$sessions
			),
			ARRAY_A
		);

		if ( empty( $rows ) ) wp_die( 'No chat history found.' );

		$filename = 'chat-history-' . date( 'Y-m-d-H-i-s' ) . '.csv';
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		$output = fopen( 'php://output', 'w' );
		fprintf( $output, chr(0xEF).chr(0xBB).chr(0xBF) );
		fputcsv( $output, [
			'Session ID',
			'Guest ID',
			'Visitor Name',
			'Date',
			'Sender',
			'Message Type',
			'Message',
			'Viewed At',
			'IP Address',
			'User Agent'
		] );

		foreach ( $rows as $row ) {
			fputcsv( $output, [
				$row['session_id'],
				$row['user_id'],
				$row['name'],
				$row['created_at'],
				ucfirst( $row['sender'] ),
				ucfirst( $row['message_type'] ),
				$row['message'],
				$row['viewed_at'],
				$row['ip_address'],
				$row['user_agent']
			] );
		}
		fclose( $output );
		exit;
	}
	
	/**
	 * Crawl Page
	 *
	 * @since    1.0.0
	 */
	public function crawl_page() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'techno_aidb_nonce')) {
			wp_send_json_error('Invalid nonce');
		}

		if (!current_user_can('techno_chat_support')) {
			wp_send_json_error('Unauthorized');
		}

		$ai_allowed = techno_chatbot_feature('ai_training');
    	$ai_allowed = $ai_allowed['allowed'] === true;

		if( !$ai_allowed ){
			wp_send_json_error('Invalid Plan');
		}

		$post_id = intval($_POST['post_id']);
		$url = get_post_meta($post_id, '_page_url', true);

		if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
			wp_send_json_error('Invalid URL');
		}

		// FETCH NEW Using Content Only
		$wp_post_id = url_to_postid($url);
		if ($wp_post_id) {
			$wp_post = get_post($wp_post_id);
			if (!$wp_post) wp_send_json_error('Post not found');
			// Get rendered WP content
			$content = apply_filters('the_content', $wp_post->post_content);
			// Convert HTML to clean text
			$clean_text = wp_strip_all_tags($content);
			// Normalize spaces
			$clean_text = preg_replace('/\s+/', ' ', $clean_text);
			$clean_text = trim($clean_text);
		} else {
			// EXTERNAL URL FALLBACK
			$response = wp_remote_get($url, [
				'timeout' => 20,
				'user-agent' => 'TechnoChatbotCrawler/1.0',
			]);
			if (is_wp_error($response)) {
				wp_send_json_error($response->get_error_message());
			}
			$html = wp_remote_retrieve_body($response);
			$clean_text = $this->extract_main_content($html);
		}

		// CHUNK CONTENT
		$chunks = $this->chunk_text($clean_text);

		// EMBEDDING
		$results = $this->create_embeddings_batch($chunks);
		if (!$results) {
			wp_send_json_error('Embedding failed');
		}
		$embedded_chunks = [];
		foreach ($results as $item) {
			if (!isset($item['embedding'])) {
				continue;
			}

			$index = $item['index'];

			$embedded_chunks[] = [
				'text' => $chunks[$index] ?? '',
				'embedding' => $item['embedding']
			];
		}

		// SAVE
		update_post_meta($post_id, '_crawled_content', $clean_text);
		update_post_meta($post_id, '_ai_clean_text', $clean_text);
		update_post_meta($post_id, '_ai_embeddings', $embedded_chunks);
		update_post_meta($post_id, '_ai_last_crawled', current_time('mysql'));
		update_post_meta($post_id, '_ai_status', 'crawled');

		wp_send_json_success([
			'message' => 'Crawled successfully',
			'chunks' => count($chunks)
		]);
	}
	
	/**
	 * Get only main content
	 *
	 * @since    1.0.0
	 */
	private function extract_main_content($html) {

		libxml_use_internal_errors(true);

		$dom = new DOMDocument();
		$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);

		// Remove unwanted elements
		$removeQueries = [
			'//script',
			'//style',
			'//noscript',
			'//header',
			'//footer',
			'//nav',
			'//form',
			'//*[contains(@class,"header")]',
			'//*[contains(@class,"footer")]',
			'//*[contains(@class,"menu")]',
			'//*[contains(@class,"sidebar")]',
			'//*[contains(@id,"header")]',
			'//*[contains(@id,"footer")]',
			'//*[contains(@id,"menu")]',
			'//*[contains(@id,"sidebar")]',
		];

		foreach ($removeQueries as $query) {
			$nodes = $xpath->query($query);

			foreach ($nodes as $node) {
				$node->parentNode->removeChild($node);
			}
		}

		// Prefer article/main content
		$nodes = $xpath->query('//article | //main');
		$textParts = [];

		if ($nodes->length > 0) {
			foreach ($nodes as $node) {
				$textParts[] = trim($node->textContent);
			}
		} else {
			// fallback to body
			$body = $dom->getElementsByTagName('body')->item(0);
			$textParts[] = $body ? $body->textContent : '';
		}

		libxml_clear_errors();
		$text = implode(' ', $textParts);
		$text = preg_replace('/\s+/', ' ', $text);
		return trim($text);
	}
	
	/**
	 * AI Chunking
	 *
	 * @since    1.0.0
	 */
	private function chunk_text($text, $maxLength = 800) {

		$sentences = preg_split('/(?<=[.!?])\s+/', $text);

		$chunks = [];
		$current = '';

		foreach ($sentences as $sentence) {

			if (strlen($current . ' ' . $sentence) > $maxLength) {
				$chunks[] = trim($current);
				$current = $sentence;
			} else {
				$current = $current ? $current . ' ' . $sentence : $sentence;
			}
		}

		if (trim($current) !== '') {
			$chunks[] = trim($current);
		}

		return $chunks;
	}
	
	/**
	 * Open AI Embedding
	 *
	 * @since    1.0.0
	 */
	private function create_embeddings_batch($chunks) {

		$api_key = get_option('techno_chatbot_openai_secret');

		if (!$api_key || empty($chunks)) {
			return false;
		}

		$response = wp_remote_post(
			'https://api.openai.com/v1/embeddings',
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				],
				'body' => json_encode([
					'model' => 'text-embedding-3-small',
					'input' => $chunks
				]),
				'timeout' => 60
			]
		);

		if (is_wp_error($response)) {
			return false;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (!isset($body['data'])) {
			return false;
		}

		return $body['data'];
	}
}
