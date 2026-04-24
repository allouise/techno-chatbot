<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://technodreamwebdesign.com/
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

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker-alpha', plugin_dir_url( __FILE__ ) . 'js/wp-color-picker-alpha.min.js', array( 'wp-color-picker' ), $this->version, true );
		wp_enqueue_script( 'techno-admin-jquery', plugin_dir_url( __FILE__ ) . 'js/techno-chatbot-jquery.js', array( 'wp-color-picker', 'wp-color-picker-alpha', 'jquery' ), $this->version, true );
		wp_enqueue_script( 'techno-admin-script', plugin_dir_url( __FILE__ ) . 'js/techno-chatbot-admin.js', [], $this->version, true );

		$livechat_allowed = techno_chatbot_feature('live_chat');
    	$livechat_allowed = $livechat_allowed['allowed'] === true;
		if( $livechat_allowed ){
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
					'notification_sound' => TECHNO_CHATBOT_FOLDER_URL . '/notification.mp3',
				]
			);
		}

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
			__( 'FAQ - Training Data', 'techno-chatbot' ),
			__( 'FAQ - Training Data', 'techno-chatbot' ),
			'manage_options',
			'edit.php?post_type=techno_chatbot_faq'
		);

		add_submenu_page(
			'techno-chatbot',
			__( 'Chats', 'techno-chatbot' ),
			__( 'Chats', 'techno-chatbot' ),
			'manage_options',
			'techno-chatbot-livechat',
			array( $this, 'display_chats_page' )
		);

		add_submenu_page(
			'techno-chatbot',
			__( 'AI Knowledgebase', 'techno-chatbot' ),
			__( 'AI Knowledgebase', 'techno-chatbot' ),
			'manage_options',
			'techno-chatbot-knowledgebase',
			array( $this, 'display_knowledgebase_page' )
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
	 * Render the AI knowledge page.
	 *
	 * @since    1.0.0
	 */
	public function display_knowledgebase_page() {
		include_once plugin_dir_path( __FILE__ ) . 'partials/techno-chatbot-admin-aiknowledgebase.php';
	}

	/**
	 * Render the Live Chat page.
	 *
	 * @since    1.0.0
	 */
	public function display_chats_page() {
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'livechat';
		switch ($active_tab) {
			case 'livechat':
				$online = (int)get_option('techno_chatbot_support_online', 0);
				$server = techno_wss_check();
				$online = !$server? false : $online;
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

		if (!current_user_can('manage_options')) {
			wp_send_json_error();
		}
		
		$status = get_transient('techno_wss_status');
		if ($status === false) {
			$status = techno_wss_check() ? 1 : 0;
			set_transient('techno_wss_status', $status, 5);
		}

		if( !$status ){
			update_option('techno_chatbot_support_online', 0);
			wp_send_json_success(['online' => 0, 'server_offline' => 1]);
		}
		
		$force = isset($_POST['force_status']) && $_POST['force_status'] == 1 ? 1 : 0;
		if ( $force ) {
			update_option('techno_chatbot_support_online', $force);
			wp_send_json_success(['online' => (bool)$force, 'forced' => 1]);
		}

		$current = get_option('techno_chatbot_support_online', 0);
		$onlinestatus = $current ? 0 : 1;
		update_option('techno_chatbot_support_online', $onlinestatus);

		wp_send_json_success(['online' => (bool)$onlinestatus, 'before' => $current]);
	}

	/**
	 * Save admin chat message
	 *
	 * @since    1.0.0
	 */
	public function save_admin_chat_message() {
        check_ajax_referer( 'techno_chatbot_nonce', 'nonce' );

		if (!current_user_can('manage_options')) {
			wp_send_json_error();
		}
 
        /* ---- rate limit: max 60 saves per minute per IP ---- */
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        $rate_key = 'techno_admin_chat_save_' . md5( $ip );
        $rate_count = (int) get_transient( $rate_key );
        if ( $rate_count >= 60 ) {
            wp_send_json_error( [ 'message' => 'Rate limit exceeded' ], 429 );
        }
        set_transient( $rate_key, $rate_count + 1, 60 );
 
        /* ---- validate inputs ---- */
        $session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : '';
        $message = isset( $_POST['message'] ) ? trim( sanitize_textarea_field( $_POST['message'] ) ) : '';
			
        if ( ! $session_id || ! $message ) {
            wp_send_json_error( [ 'message' => 'Missing required fields' ], 400 );
        }

		if (strlen($message) < 1) {
			wp_send_json_error(['message' => 'Empty message'], 400);
		}
 
        /* session_id must be alphanumeric + dash/underscore only */
        if ( ! preg_match( '/^[a-zA-Z0-9\-_]+$/', $session_id ) ) {
            wp_send_json_error( [ 'message' => 'Invalid session_id format' ], 400 );
        }
 
        /* message length guard */
        if ( mb_strlen( $message ) > 2000 ) {
            wp_send_json_error( [ 'message' => 'Message too long' ], 400 );
        }
 
		$current_user = wp_get_current_user();
		$admin_name = $current_user->display_name;

        global $wpdb;
        $result = $wpdb->insert( $wpdb->prefix . 'techno_livechat_messages', 
			[
				'session_id' => $session_id,
				'sender' => 'admin',
				'message' => $message,
				'name' => $admin_name
        	], 
		[ '%s', '%s', '%s', '%s' ] );
 
        if ( $result === false ) {
            wp_send_json_error( [ 'message' => 'DB error' ], 500 );
        }
        wp_send_json_success( [ 'id' => $wpdb->insert_id ] );
    }

	/**
	 * Return Chat History
	 *
	 * @since    1.0.0
	 */
	public function techno_get_chat_history_ajxfunction() {
		check_ajax_referer( 'techno_chatbot_nonce', 'nonce' );
 
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}
 
		$session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( $_POST['session_id'] ) : '';
		if ( ! $session_id ) {
			wp_send_json_error( [ 'message' => 'Missing session_id' ], 400 );
		}
 
		if ( ! preg_match( '/^[a-zA-Z0-9\-_]+$/', $session_id ) ) {
			wp_send_json_error( [ 'message' => 'Invalid session format' ], 400 );
		}
 
		global $wpdb;
		$messages = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT sender, message
				 FROM {$wpdb->prefix}techno_livechat_messages
				 WHERE session_id = %s
				 ORDER BY id ASC",
				$session_id
			),
			ARRAY_A
		);
 
		wp_send_json_success( $messages ?: [] );
	}
}
