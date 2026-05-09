<?php

/**
 * Core plugin class
 *
 * @link       https://technodreamwebdesign.com/
 * @since      1.0.0
 *
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/includes
 */

/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/includes
 * @author     Technodream <al.esilverconnect@gmail.com>
 */
class Techno_Chatbot {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Techno_Chatbot_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Post types
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Techno_Chatbot_Post_Types $post_types Handles custom post types.
	 */
	protected $post_types;

	/**
	 * License
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Techno_Chatbot_License_Manager $license Handles license.
	 */
	protected $license;

	/**
	 * Websocket
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Techno_Chatbot_Websocket $websocket Handles websocket.
	 */
	protected $websocket;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'TECHNO_CHATBOT_VERSION' ) ) {
			$this->version = TECHNO_CHATBOT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'techno-chatbot';

		$this->load_dependencies();
		$this->post_types = new Techno_Chatbot_Post_Types();
		$this->license = new Techno_Chatbot_License_Manager();
		$this->websocket = new Techno_Chatbot_Websocket();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-techno-chatbot-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-techno-chatbot-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-techno-chatbot-admin.php';

		/**
		 * The class responsible for Licensing.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-techno-chatbot-license-manager.php';

		/**
		 * The class responsible for defining all CPTs.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-techno-chatbot-post-types.php';

		/**
		 * The class responsible for general fields.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-techno-chatbot-admin-fields-general.php';

		/**
		 * The class responsible for behavior fields.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-techno-chatbot-admin-fields-behaviors.php';

		/**
		 * The class responsible for text fields.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-techno-chatbot-admin-fields-texts.php';

		/**
		 * The class responsible for styles fields.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-techno-chatbot-admin-fields-styles.php';

		/**
		 * The class responsible for license fields.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-techno-chatbot-admin-fields-license.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-techno-chatbot-public.php';

		/**
		 * The class responsible for websocket.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-techno-chatbot-websocket.php';

		$this->loader = new Techno_Chatbot_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Techno_Chatbot_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Techno_Chatbot_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Techno_Chatbot_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
    	$this->loader->add_action( 'init', $this->post_types, 'register_post_types' ); 
		$this->loader->add_action( 'init', $this->post_types, 'register_taxonomies' );
		$this->loader->add_action( 'add_meta_boxes', $this->post_types, 'add_meta_boxes' );
    	$this->loader->add_action( 'save_post_techno_chatbot_faq', $this->post_types, 'save_faq_meta' );
		$this->loader->add_action( 'save_post_techno_chatbot_aidb', $this->post_types, 'save_aidb_meta' );

		$this->loader->add_filter( 'plugin_action_links_' . TECHNO_CHATBOT_FILEBASE, $plugin_admin, 'add_settings_link' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
		
		$this->loader->add_action( 'wp_ajax_techno_toggle_support_online', $plugin_admin, 'toggle_support_online' );
		$this->loader->add_action( 'wp_ajax_techno_save_admin_chat_message', $plugin_admin, 'save_admin_chat_message' );
		$this->loader->add_action( 'wp_ajax_techno_get_chat_history', $plugin_admin, 'techno_get_chat_history_ajxfunction' );

		$this->loader->add_action( 'wp_ajax_techno_chatbot_crawl_page', $plugin_admin, 'crawl_page' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Techno_Chatbot_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'render_chatbot_icon' );
		$this->loader->add_action( 'wp_ajax_nopriv_send_history_admin', $plugin_public, 'send_history_admin' );
		$this->loader->add_action( 'wp_ajax_send_history_admin', $plugin_public, 'send_history_admin' );
		$this->loader->add_action( 'techno_chatbot_daily_license_check', $plugin_public, 'validate_license' );

		$this->loader->add_action( 'wp_ajax_techno_check_support_online', $plugin_public, 'check_support_online' );
		$this->loader->add_action( 'wp_ajax_nopriv_techno_check_support_online', $plugin_public, 'check_support_online' );

		$this->loader->add_action( 'wp_ajax_nopriv_techno_save_chat_message', $plugin_public, 'save_chat_message' );
		$this->loader->add_action( 'wp_ajax_techno_save_chat_message', $plugin_public, 'save_chat_message' );

		$this->loader->add_action( 'wp_ajax_nopriv_techno_bot_to_live', $plugin_public, 'techno_bot_to_live' );
		$this->loader->add_action( 'wp_ajax_techno_bot_to_live', $plugin_public, 'techno_bot_to_live' );

		$this->loader->add_action( 'wp_ajax_nopriv_end_live_chat', $plugin_public, 'end_live_chat' );
		$this->loader->add_action( 'wp_ajax_end_live_chat', $plugin_public, 'end_live_chat' );

		$this->loader->add_action( 'wp_ajax_nopriv_send_transcript', $plugin_public, 'send_transcript' );
		$this->loader->add_action( 'wp_ajax_send_transcript', $plugin_public, 'send_transcript' );

		$this->loader->add_action( 'wp_ajax_techno_chatbot_ask_ai', $plugin_public, 'ask_ai' );
		$this->loader->add_action( 'wp_ajax_nopriv_techno_chatbot_ask_ai', $plugin_public, 'ask_ai' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Techno_Chatbot_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
