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

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker-alpha', plugin_dir_url( __FILE__ ) . 'js/wp-color-picker-alpha.min.js', array( 'wp-color-picker' ), $this->version, true );
		wp_enqueue_script( 'techno-admin-jquery', plugin_dir_url( __FILE__ ) . 'js/techno-chatbot-jquery.js', array( 'wp-color-picker', 'wp-color-picker-alpha', 'jquery' ), $this->version, true );
		wp_enqueue_script( 'techno-admin-script', plugin_dir_url( __FILE__ ) . 'js/techno-chatbot-admin.js', [], $this->version, true );
		
		$is_livechat_page = is_admin() && isset($_GET['page']) && $_GET['page'] === 'techno-chatbot-livechat';
		$livechat_allowed = techno_chatbot_feature('live_chat');
    	$livechat_allowed = $livechat_allowed['allowed'] === true;
		if( $livechat_allowed && $is_livechat_page ){
			wp_enqueue_script( $this->plugin_name.'-socket-io', plugin_dir_url( __FILE__ ) . 'js/socket.io.min.js', array(), $this->version, true );

			$end_msg = Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_endchat');
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
					'end_message' => $end_msg
				]
			);
		}

		$ai_allowed = techno_chatbot_feature('ai_training');
    	$ai_allowed = $ai_allowed['allowed'] === true;
		if( $ai_allowed ){
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
			'manage_options',
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
		$message_type = isset($_POST['message_type'])? sanitize_text_field($_POST['message_type']) : 'text';
			
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

		/* validate message_type */
		$allowed_types = ['text','image','file','system'];
		if ( ! in_array($message_type, $allowed_types, true) ) {
			$message_type = 'text';
		}

		/* get admin info */
		$current_user = wp_get_current_user();
		$admin_name   = $current_user->display_name;

		/* capture metadata */
		$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? substr( sanitize_text_field($_SERVER['HTTP_USER_AGENT']), 0, 255 ) : null;
		$ip_address = substr( sanitize_text_field($ip), 0, 45 );

        global $wpdb;
        $result = $wpdb->insert(
			$wpdb->prefix . 'techno_livechat_messages',
			[
				'session_id'   => $session_id,
				'sender'       => 'admin',
				'message'      => $message,
				'name'         => $admin_name,
				'message_type' => $message_type,
				'user_agent'   => $user_agent,
				'ip_address'   => $ip_address,
			],
			[
				'%s', // session_id
				'%s', // sender
				'%s', // message
				'%s', // name
				'%s', // message_type
				'%s', // user_agent
				'%s', // ip_address
			]
		);
 
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
				"SELECT sender, message, name, created_at
				 FROM {$wpdb->prefix}techno_livechat_messages
				 WHERE session_id = %s
				 ORDER BY id ASC",
				$session_id
			),
			ARRAY_A
		);
 
		wp_send_json_success( $messages ?: [] );
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

		if (!current_user_can('manage_options')) {
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

		// FETCH
		$response = wp_remote_get($url, [
			'timeout' => 20,
			'user-agent' => 'TechnoChatbotCrawler/1.0',
			'sslverify'  => false
		]);

		if (is_wp_error($response)) {
			wp_send_json_error($response->get_error_message());
		}

		$html = wp_remote_retrieve_body($response);

		// CLEAN CONTENT
		$clean_text = $this->extract_main_content($html);

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

		// remove junk first
		foreach (['script', 'style', 'noscript', 'header', 'footer', 'nav', 'form'] as $tag) {
			$nodes = $dom->getElementsByTagName($tag);
			for ($i = $nodes->length - 1; $i >= 0; $i--) {
				$node = $nodes->item($i);
				$node->parentNode->removeChild($node);
			}
		}

		// prioritize real content containers
		$nodes = $xpath->query("//article | //main");

		$textParts = [];

		if ($nodes->length > 0) {
			foreach ($nodes as $node) {
				$textParts[] = trim($node->textContent);
			}
		} else {
			$body = $dom->getElementsByTagName('body')->item(0);
			$textParts[] = $body ? $body->textContent : '';
		}

		$text = implode(" ", $textParts);

		// normalize whitespace
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
