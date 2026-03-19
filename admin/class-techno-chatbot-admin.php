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
		wp_enqueue_script(
			'techno-admin-script',
			plugin_dir_url( __FILE__ ) . 'js/techno-chatbot-admin.js',
			array( 'wp-color-picker', 'jquery' ),
			$this->version,
			true
		);

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
			__( 'Live Chat', 'techno-chatbot' ),
			__( 'Live Chat', 'techno-chatbot' ),
			'manage_options',
			'techno-chatbot-livechat',
			array( $this, 'display_livechat_page' )
		);
		
		add_submenu_page(
			'techno-chatbot',
			__( 'Chat History', 'techno-chatbot' ),
			__( 'Chat History', 'techno-chatbot' ),
			'manage_options',
			'techno-chatbot-history',
			array( $this, 'display_chathistory_page' )
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
	public function display_livechat_page() {
		include_once plugin_dir_path( __FILE__ ) . 'partials/techno-chatbot-admin-livechat.php';
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

}
