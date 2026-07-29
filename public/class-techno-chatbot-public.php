<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://technodreamwebdesign.com/techno-chatbot/
 * @since      1.0.0
 *
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/public
 * @author     Technodream <al.esilverconnect@gmail.com>
 */
class Techno_Chatbot_Public {

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
	 * AI assitance limit.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      int    $ai_assist_limit    AI assitance limit.
	 */
	private $ai_assist_limit;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->ai_assist_limit = (int) get_option('techno_chatbot_ai_assitance_limit', 0);

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		$enabled = get_option( 'techno_chatbot_enabled', 1 );
		$basic_chat = techno_chatbot_feature('basic_chat');
		if ( ! $enabled || $basic_chat['allowed'] != true ) return;

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/techno-chatbot-public.css', array(), $this->version, 'all' );
		$custom_css = $this->generate_dynamic_css();
		wp_add_inline_style( $this->plugin_name, $custom_css );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$enabled = get_option( 'techno_chatbot_enabled', 1 );
		$basic_chat = techno_chatbot_feature('basic_chat');
		if ( ! $enabled || $basic_chat['allowed'] != true ) return;

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/techno-chatbot-public.js', array(), $this->version, true );

		$aitraining_plan = techno_chatbot_feature('ai_training');
    	$aitraining_enabled = $aitraining_plan['allowed'] === true;
		$livechat_plan = techno_chatbot_feature('live_chat');
    	$livechat_enabled = $livechat_plan['allowed'] === true;
		$script_array = array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('techno_chatbot_nonce'),
			'disclaimerEnabled' => Techno_Chatbot_Admin_Fields_General::get_value('techno_chatbot_disclaimer'),
			// 'disclaimerMsg' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_disclaimermsg'),
			'welcomeMessage' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_welcomemsg'),
			'timeToCallTxt' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_timetocall_txt'),
			'noAnswer' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_no_answer_message'),
			'nextStepMsg' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_next_step'),
			'offlineSupport' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_offline_agents_message'),
			'idleSupport' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_idle_agents_message'),
			'transferredToSupport' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_transferred_live_message'),
			'getName' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_getname'),
			'liveChatGetName' => Techno_Chatbot_Admin_Fields_Behaviors::get_value('techno_chatbot_livechatgetname'),
			'noAnswerFinalDefault' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_no_answer_message_final_default'),
			'getContactThxMsg' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_getcontact_finish'),
			'askEmail' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_askemail'),
			'spamLimitMsg' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_submissionspam_limit'),
			'errorMsg' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_error'),
			'cerrorMsg' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_criticalerror'),
			'phoneError' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_invalid_phone'),
			'emailError' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_invalid_email'),
			'cPhoneLabel' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_cphoneLabel'),
			'cEmailLabel' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_cemailLabel'),
			'menuLivechat' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_menulivechat'),
			'menuCall' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_menucall'),
			'menuEmail' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_menuemail'),
			'menuReset' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_menureset'),
			'menuHistorySend' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_menuhistorysend'),
			'menuLeave' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_menuleave'),
			'historySent' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_historysent'),
			'endChatMsg' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_endchatmsg'),
			'inputtxt' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_inputtext'),
			'end_msg' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_endchat'),
			'end_msgidleguest' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_idle_guests_message'),
			'noAnswerTrigger' => Techno_Chatbot_Admin_Fields_Behaviors::get_value('techno_chatbot_no_answer_trigger'),
			'idleTimer' => Techno_Chatbot_Admin_Fields_Behaviors::get_value('techno_chatbot_idle_support'),
			'timeToCall' => get_option('techno_chatbot_timetocall'),
			'transferKeywords' => explode(',', get_option( 'techno_chatbot_transfer_trigger_keyword' )),
			'faq' => $this->get_faq_data()
		);

		if( $aitraining_enabled == true ){
			$script_array['aiEnabled'] = Techno_Chatbot_Admin_Fields_General::get_value('techno_chatbot_aireplies');
		}

		if( $livechat_enabled == true ){
			wp_enqueue_script( $this->plugin_name.'-socket-io', plugin_dir_url( __FILE__ ) . 'js/socket.io.min.js', array(), $this->version, true
			);

			$ws = techno_chatbot_websocket();
			$site = get_site_url();
			$script_array['ws_url'] = $ws->get_url();
			$script_array['site_id'] = $site;
			$script_array['token'] = $ws->get_token($site);
			$script_array['liveChatEnabled'] = $livechat_enabled;
			$script_array['supportOnline'] = techno_wss_check() ? (int) get_user_meta( get_current_user_id(), 'techno_chat_online', true ) : false;
			$script_array['transferLiveChatKeywords'] = explode(',', get_option( 'techno_chatbot_live_chat_trigger' ));
		}
		
		wp_localize_script(
			$this->plugin_name, 'technoChatbot',
			$script_array
		);

	}

	/**
	 * Get Client IP
	 *
	 * @since    1.1.0
	 */
	private function get_client_ip() {
		if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) return trim($_SERVER['HTTP_CF_CONNECTING_IP']);

		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$first_ip = trim($ips[0]);
			if (!empty($first_ip)) {
				return $first_ip;
			}
		}

		return $_SERVER['REMOTE_ADDR'] ?? '';
	}

	/**
	 * Detect Browser, OS, and Device Type without external libraries.
	 *
	 * @since    1.1.0
	 */
	private function parse_user_agent( $ua_string = '' ) {
		$browser = 'Unknown';
		$os      = 'Unknown';
		$device  = 'Desktop';

		if ( empty( $ua_string ) ) {
			return compact('browser', 'os', 'device');
		}

		// Browser
		if ( preg_match( '/Edg/i', $ua_string ) )           { $browser = 'Microsoft Edge'; }
		elseif ( preg_match( '/OPR|Opera/i', $ua_string ) )  { $browser = 'Opera'; }
		elseif ( preg_match( '/Vivaldi/i', $ua_string ) )    { $browser = 'Vivaldi'; }
		elseif ( preg_match( '/Brave/i', $ua_string ) )      { $browser = 'Brave'; }
		elseif ( preg_match( '/Chrome/i', $ua_string ) )     { $browser = 'Google Chrome'; }
		elseif ( preg_match( '/Safari/i', $ua_string ) )     { $browser = 'Safari'; }
		elseif ( preg_match( '/Firefox/i', $ua_string ) )    { $browser = 'Mozilla Firefox'; }
		elseif ( preg_match( '/MSIE|Trident/i', $ua_string ) ) { $browser = 'Internet Explorer'; }

		// OS
		if ( preg_match( '/iphone/i', $ua_string ) )         { $os = 'iOS (iPhone)'; }
		elseif ( preg_match( '/ipad/i', $ua_string ) )       { $os = 'iOS (iPad)'; }
		elseif ( preg_match( '/android/i', $ua_string ) )    { $os = 'Android'; }
		elseif ( preg_match( '/win/i', $ua_string ) )        { $os = 'Windows'; }
		elseif ( preg_match( '/mac/i', $ua_string ) )        { $os = 'macOS'; }
		elseif ( preg_match( '/linux/i', $ua_string ) )      { $os = 'Linux'; }
		elseif ( preg_match( '/cros/i', $ua_string ) )       { $os = 'ChromeOS'; }

		// Device Type
		if ( preg_match( '/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua_string ) ) {
			$device = 'Tablet';
		} elseif ( preg_match( '/Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Kindle|NetFront|Silk-Accelerated|(hpw|web)OS|Fennec|Minimo|Opera M(obi|ini)|Blazer|Dolfin|Dolphin|Skyfire|Zune/i', $ua_string ) ) {
			$device = 'Mobile';
		}

		return compact('browser', 'os', 'device');
	}

	/**
	 * Render the floating chatbot icon on the frontend.
	 *
	 * Hooked into wp_footer
	 *
	 * @since    1.0.0
	 */
	public function render_chatbot_icon() {
		$enabled = get_option( 'techno_chatbot_enabled', 1 );
		$basic_chat = techno_chatbot_feature('basic_chat');
		if ( ! $enabled || $basic_chat['allowed'] != true ) return;

		$headertxt = Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_header');
		$icontxt = Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_icontext');
		$chaticontxt = Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_chaticontext');
		$chaticontype = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_chatbot_icontype');
		$inputtxt = Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_inputtext');
		$sendbtn = Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_sendbtn');
		$menutranscripttxt = Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_menuhistorysend');
		$menuresettxt = Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_menureset');
		$disclaimerEnabled = Techno_Chatbot_Admin_Fields_General::get_value('techno_chatbot_disclaimer');
		$disclaimer = Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_disclaimermsg');
		$disclaimerFullMsg = Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_disclaimerfullmsg');

		$chaticonval = get_option( 'techno_chatbot_icon' );
		$chaticon = !empty($chaticonval)? "<img src='$chaticonval' alt='".__( 'Techno chatbot Icon', 'techno-chatbot' )."'/>" : '💬';
		$livechat_plan = techno_chatbot_feature('live_chat');
    	$livechat_enabled = $livechat_plan['allowed'] === true;
		include plugin_dir_path( __FILE__ ) . 'partials/techno-chatbot-public-chatbot.php';
	}

	/**
	 * Generate dynamic CSS variables from admin settings
	 *
	 * @since 1.0.0
	 */
	private function generate_dynamic_css() {

		$loader_bg = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_loader_bg_color');
		$loader_icon = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_loader_icon_color');
		$chaticon_bg = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_chaticon_bg_color');
		$chaticon_text = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_chaticon_text_color');
		$floatingtxt_bg = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_floatingtxt_bg_color');
		$floatingtxt_text = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_floatingtxt_text_color');
		$header_bg = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_header_bg_color');
		$header_text = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_header_text_color');
		$chatbox_bg = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_chatbox_bg_color');
		$admin_bg = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_admin_bubble_bg_color');
		$admin_text = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_admin_bubble_text_color');
		$visitor_bg = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_visitor_bubble_bg_color');
		$visitor_text = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_visitor_bubble_text_color');
		$input_bg = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_input_bg');
		$input_txt = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_input_txt');
		$chatoptionbtn_bg = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_optionbtn_bg');
		$chatoptionbtn_txt = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_optionbtn_txt');
		$sendbtn_bg = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_sendbtn_bg');
		$sendbtn_txt = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_sendbtn_txt');
		$dsclaimer_overlay = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_disclaimeroverlay');
		$dsclaimer_bg = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_disclaimerbg');
		$dsclaimer_txt = Techno_Chatbot_Admin_Fields_Styles::get_value('techno_disclaimertxt');

		$height = absint( Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_height' ) );
		$width = absint( Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_width' ) );
		$offset_x = floatval( Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_offset_x' ) );
		$offset_y = floatval( Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_offset_y' ) );
		$icon_distance = floatval( Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_distance' ) );
		$icon_height = absint( Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_icon_height' ) );
		$icon_width = absint( Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_icon_width' ) );
		$icon_offset_y = $offset_y + $icon_distance;
		$icon_offset_x = $offset_x + $icon_distance;
		$floating_offset_y = $offset_y + 5 + $icon_height;
		$floating_offset_x = $offset_x + 5 + $icon_width;
		$position = Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_position' );
		$zindex = Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_zindex' );
		$iconsize = Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_iconsize' );
		$icontextsize = Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_icontextsize' );

		$headingsize = Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_heading_size' );
		$chatmenusize = Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_heading_menu_size' );
		$chatmsgsize = Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_chatmsg_size' );
		$inputtxtsize = Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_inputtxt_size' );
		$sendbtnsize = Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_sendbtn_size' );
		$floatingtxtsize = Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_floatingtxt_size' );

		$position_css = "#techno-chatbot-floating-icon{ bottom: {$offset_y}px; right: {$offset_x}px; } #techno-chatbot-window{ bottom: {$icon_offset_y}px; right: {$offset_x}px; max-height: calc(100% - {$offset_y}px); max-width: calc(100% - {$offset_x}px ); } #techno-chatbot-floating-text{ bottom: {$offset_y}px; right: {$floating_offset_x}px; border-radius: 10px 0 10px 10px; }";
		if( $position == 'upper left' ){
			$position_css = "#techno-chatbot-floating-icon{ top: {$offset_y}px; left: {$offset_x}px; } #techno-chatbot-window{ top: {$icon_offset_y}px; left: {$offset_x}px; max-height: calc(100% - {$offset_y}px); max-width: calc(100% - {$offset_x}px ); } #techno-chatbot-floating-text{ top: {$offset_y}px; left: {$floating_offset_x}px; border-radius: 10px 10px 10px 0; }";
		}elseif ( $position == 'top center' ){
			$position_css = "#techno-chatbot-floating-icon{ top: {$offset_y}px; left: 50%; transform: translateX(-50%); } #techno-chatbot-window{ top: {$icon_offset_y}px; left: 50%; transform: translateX(-50%); max-height: calc(100% - {$offset_y}px); max-width: calc(100% - {$offset_x}px ); } #techno-chatbot-floating-text{ top: {$floating_offset_y}px; left: 50%; transform: translateX(-50%); border-radius: 10px; }";
		}elseif ( $position == 'upper right' ){
			$position_css = "#techno-chatbot-floating-icon{ top: {$offset_y}px; right: {$offset_x}px; } #techno-chatbot-window{ top: {$icon_offset_y}px; right: {$offset_x}px; max-height: calc(100% - {$offset_y}px); max-width: calc(100% - {$offset_x}px ); } #techno-chatbot-floating-text{ top: {$offset_y}px; right: {$floating_offset_x}px; border-radius: 10px 10px 0 10px; }";
		}elseif ( $position == 'left' ){
			$position_css = "#techno-chatbot-floating-icon{ top: 50%; transform: translateY(-50%); left: {$offset_x}px; } #techno-chatbot-window{ top: 50%; transform: translateY(-50%); left: {$icon_offset_x}px; max-height: calc(100% - {$offset_y}px); max-width: calc(100% - {$offset_x}px ); } #techno-chatbot-floating-text{ top: 50%; transform: translateY(-50%); left: {$floating_offset_x}px; border-radius: 10px 10px 10px 0; }";
		}elseif ( $position == 'right' ){
			$position_css = "#techno-chatbot-floating-icon{ top: 50%; transform: translateY(-50%); right: {$offset_x}px; } #techno-chatbot-window{ top: 50%; transform: translateY(-50%); right: {$icon_offset_x}px; max-height: calc(100% - {$offset_y}px); max-width: calc(100% - {$offset_x}px ); } #techno-chatbot-floating-text{ top: 50%; transform: translateY(-50%); right: {$floating_offset_x}px; border-radius: 10px 10px 0 10px; }";
		}elseif ( $position == 'bottom left' ){
			$position_css = "#techno-chatbot-floating-icon{ bottom: {$offset_y}px; left: {$offset_x}px; } #techno-chatbot-window{ bottom: {$icon_offset_y}px; left: {$offset_x}px; max-height: calc(100% - {$offset_y}px); max-width: calc(100% - {$offset_x}px ); } #techno-chatbot-floating-text{ bottom: {$offset_y}px; left: {$floating_offset_x}px; border-radius: 10px 10px 10px 0; }";
		}elseif ( $position == 'bottom center' ){
			$position_css = "#techno-chatbot-floating-icon{ bottom: {$offset_y}px; left: 50%; transform: translateX(-50%); } #techno-chatbot-window{ bottom: {$icon_offset_y}px; left: 50%; transform: translateX(-50%); max-height: calc(100% - {$offset_y}px); max-width: calc(100% - {$offset_x}px ); } #techno-chatbot-floating-text{ bottom: {$floating_offset_y}px; left: 50%; transform: translateX(-50%); border-radius: 10px; ";
		}

		$css = "
		:root{
			--techno-loader-bg: {$loader_bg};
			--techno-loader-icon: {$loader_icon};
			--techno-chaticon-bg: {$chaticon_bg};
			--techno-chaticon-text: {$chaticon_text};
			--techno-floatingtxt-bg: {$floatingtxt_bg};
			--techno-floatingtxt-text: {$floatingtxt_text};
			--techno-header-bg: {$header_bg};
			--techno-header-text: {$header_text};
			--techno-chatbox-bg: {$chatbox_bg};
			--techno-admin-bubble-bg: {$admin_bg};
			--techno-admin-bubble-text: {$admin_text};
			--techno-visitor-bubble-bg: {$visitor_bg};
			--techno-visitor-bubble-text: {$visitor_text};
			--techno-input-bg: {$input_bg};
			--techno-input-txt: {$input_txt};
			--techno-chatoptionbtn-bg: {$chatoptionbtn_bg};
			--techno-chatoptionbtn-txt: {$chatoptionbtn_txt};
			--techno-sendbtn-bg: {$sendbtn_bg};
			--techno-sendbtn-txt: {$sendbtn_txt};
			--techno-dsclaimer_overlay: {$dsclaimer_overlay};
			--techno-dsclaimer_bg: {$dsclaimer_bg};
			--techno-dsclaimer_txt: {$dsclaimer_txt};

			--techno-chatbot-height: {$height}px;
			--techno-chatbot-width: {$width}px;
			--techno-chatbot-offset-x: {$offset_x}px;
			--techno-chatbot-offset-y: {$offset_y}px;
			--techno-chatbot-z-index: {$zindex};
			--techno-chatbot-iconsize: {$iconsize};
			--techno-chatbot-icontextsize: {$icontextsize}px;
			--techno-chatbot-iconheight: {$icon_height}px;
			--techno-chatbot-iconwidth: {$icon_width}px;
			
			--techno-chatbot-headingsize: {$headingsize}px;
			--techno-chatbot-chatmenusize: {$chatmenusize}px;
			--techno-chatbot-chatmsgsize: {$chatmsgsize}px;
			--techno-chatbot-inputtxtsize: {$inputtxtsize}px;
			--techno-chatbot-sendbtnsize: {$sendbtnsize}px;
			--techno-chatbot-floatingtxtsize: {$floatingtxtsize}px;
		}
		$position_css
		";

		return $css;
	}

	/**
	 * Get FAQ
	 *
	 * @since 1.0.0
	 */
	private function get_faq_data() {
		$args = array(
			'post_type'      => 'techno_chatbot_faq',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_key'       => '_faq_priority',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
		);
		$query = new WP_Query( $args );
		$faqs = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$questions = get_post_meta( get_the_ID(), '_possible_questions', true );
				$answer    = get_post_meta( get_the_ID(), '_faq_answer', true );
				$priority  = get_post_meta( get_the_ID(), '_faq_priority', true );
				$faqs[] = array(
					'questions' => array_map( 'trim', explode( ',', strtolower( $questions ) ) ),
					'answer'    => wp_kses_post( $answer ),
					'priority'  => intval( $priority ),
				);
			}
			wp_reset_postdata();
		}
		return $faqs;
	}

	/**
	 * Scheduled Validate License
	 *
	 * @since    1.0.0
	 */
	public function validate_license() {
		techno_chatbot_license()->validate_license( techno_chatbot_license()->get_license() );
	}

	/**
	 * Check Support if Online
	 *
	 * @since    1.0.0
	 */
	public function check_support_online() {
		$plan = techno_chatbot_feature('live_chat');
		if ( $plan['allowed'] !== true ) {
			wp_send_json_success(['online' => false]);
			return;
		}
		$toggle = (int) get_user_meta( get_current_user_id(), 'techno_chat_online', true );
		wp_send_json_success(['online' => $toggle]);
	}

	/**
	 * Create new conversation
	 *
	 * @since    1.1.0
	 */
	public function create_conversation() {
		check_ajax_referer( 'techno_chatbot_nonce', 'nonce' );

		/* ---- rate limit: max 60 saves per minute per IP ---- */
        $ip = $this->get_client_ip();
        $rate_key = 'techno_create_convo_' . md5( $ip );
        $rate_count = (int) get_transient( $rate_key );
        if ( $rate_count >= 60 ) {
            wp_send_json_error( [ 'message' => 'Rate limit exceeded' ], 429 );
        }
        set_transient( $rate_key, $rate_count + 1, 60 );
 
        /* ---- validate inputs ---- */
        $session_id = isset($_POST['session_id'])? sanitize_text_field($_POST['session_id']) : '';
		$message = isset($_POST['message'])? trim( sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) ) : null;

		/* Check Session ID */
        if ( ! $session_id ) {
            wp_send_json_error( [ 'message' => 'Missing Session' ], 400 );
        }
        /* session_id must be alphanumeric + dash/underscore only */
        if ( ! preg_match( '/^[a-zA-Z0-9\-_]+$/', $session_id ) ) {
            wp_send_json_error( [ 'message' => 'Invalid session_id format' ], 400 );
        }
	
		$user_id = get_current_user_id();
		$user_id = $user_id ?: null;

		$raw_user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 255 ) : '';
		$device_info = $this->parse_user_agent( $raw_user_agent );
		$metas_data = [
			'ip'         => $this->get_client_ip(),
			'user_agent' => $raw_user_agent,
			'browser'    => $device_info['browser'],
			'os'         => $device_info['os'],
			'device'     => $device_info['device'],
		];

        global $wpdb;
        $result = $wpdb->insert( $wpdb->prefix . 'techno_cb_conversations', [
			'session_id' => $session_id,
			'user_id' => $user_id,
			'metas' => wp_json_encode( $metas_data ),
		], [
			'%s', // session_id
			'%d', // user_id
			'%s', // metas
		] );
        if ( $result === false ) {
            wp_send_json_error( [ 'message' => 'Failed to create conversation. Please contact administrator.' ], 500 );
        }
		$conversation_id = $wpdb->insert_id;

		/* If has message */
		if( $message ){
			$welcome_msg = $wpdb->insert( $wpdb->prefix . 'techno_cb_messages', [
				'conversation_id' => $conversation_id,
				'sender' => 'bot',
				'message' => $message,
			], [
				'%d', // conversation_id
				'%s', // sender
				'%s', // message
			] );
			if ( $welcome_msg === false ) {
				error_log( 'Failed to add Welcome Message: ' . $wpdb->last_error );
			}
		}

        wp_send_json_success( [ 'id' => $conversation_id ] );
	}

	/**
	 * Update conversation
	 *
	 * @since    1.1.0
	 */
	public function update_conversation() {
		check_ajax_referer( 'techno_chatbot_nonce', 'nonce' );

		/* ---- Rate Limit: Max 60 saves per minute per IP ---- */
		$ip         = $this->get_client_ip();
		$rate_key   = 'techno_update_convo_' . md5( $ip );
		$rate_count = (int) get_transient( $rate_key );
		if ( $rate_count >= 60 ) {
			wp_send_json_error( [ 'message' => 'Rate limit exceeded' ], 429 );
		}
		set_transient( $rate_key, $rate_count + 1, 60 );

		/* ---- Check Conversation & Session ID ---- */
		$conversation_id = isset( $_POST['conversation_id'] ) ? absint( $_POST['conversation_id'] ) : 0;
		$session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
		$socket_id = isset( $_POST['socket_id'] ) ? sanitize_text_field( wp_unslash( $_POST['socket_id'] ) ) : '';
		$name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : null;
		if ( ! $conversation_id || empty( $session_id ) || empty( $socket_id ) ) {
			wp_send_json_error( [ 'message' => 'Missing Parameters' ], 400 );
		}
		if ( ! preg_match( '/^[a-zA-Z0-9\-_]+$/', $session_id ) || ! preg_match( '/^[a-zA-Z0-9\-_]+$/', $socket_id ) ) {
			wp_send_json_error( [ 'message' => 'Invalid session formats' ], 400 );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'techno_cb_conversations';
		$updated = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET socket_id = %s, name = %s WHERE id = %d AND session_id = %s AND ended_at IS NULL",
				$socket_id,
				$name,
				$conversation_id,
				$session_id
			)
		);

		if ( false === $updated ) wp_send_json_error( [ 'message' => 'Database error while updating conversation' ], 500 );
		if ( 0 === $updated ) wp_send_json_error( [ 'message' => 'Conversation not found or already updated' ], 404 );

		wp_send_json_success( [ 'message' => 'Conversation updated successfully' ] );
	}
	
	/**
	 * End conversation
	 *
	 * @since    1.1.0
	 */
	public function end_conversation() {
		check_ajax_referer( 'techno_chatbot_nonce', 'nonce' );

		/* ---- rate limit: max 60 saves per minute per IP ---- */
        $ip = $this->get_client_ip();
        $rate_key = 'techno_end_convo_' . md5( $ip );
        $rate_count = (int) get_transient( $rate_key );
        if ( $rate_count >= 60 ) {
            wp_send_json_error( [ 'message' => 'Rate limit exceeded' ], 429 );
        }
        set_transient( $rate_key, $rate_count + 1, 60 );

		/* Check Conversation & Session ID */
		$conversation_id = isset($_POST['conversation_id'])? sanitize_text_field($_POST['conversation_id']) : null;
		$session_id = isset( $_POST['session_id'] )? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
        if ( ! $conversation_id || ! $session_id ) {
			wp_send_json_error( [ 'message' => 'Missing session or conversation parameters' ], 400 );
		}
        /* session_id must be alphanumeric + dash/underscore only */
        if ( ! preg_match( '/^[a-zA-Z0-9\-_]+$/', $session_id ) ) {
            wp_send_json_error( [ 'message' => 'Invalid session_id format' ], 400 );
        }

		global $wpdb;
		$table = $wpdb->prefix . 'techno_cb_conversations';
		$updated = $wpdb->query(
			$wpdb->prepare( "UPDATE {$table} SET ended_at = %s WHERE id = %d AND session_id = %s", current_time( 'mysql' ), $conversation_id, $session_id )
		);

		if ( false === $updated ) wp_send_json_error( [ 'message' => 'Database error while ending conversation' ], 500 );
		if ( 0 === $updated ) wp_send_json_error( [ 'message' => 'Conversation not found or already ended' ], 404 );

		// --- Retrieve messages to send transcript ---
		$chat_data = $this->get_chat_messages( $session_id );
		if ( ! empty( $chat_data['success'] ) && ! empty( $chat_data['messages'] ) ) {
			$messages = $chat_data['messages'];
			$message_types = array_column( $messages, 'message_type' );

			// 1. Send lead notification to Admin if contact info exists
			$required_types = [ 'email_input_answer', 'phone_input_answer' ];
			$has_contact_info = ! empty( array_intersect( $required_types, $message_types ) );

			if ( $has_contact_info ) {
				$emails_option = get_option( 'techno_chatbot_emails' );
				$admin_emails  = [ sanitize_email( get_option( 'admin_email' ) ) ];

				if ( ! empty( $emails_option ) ) {
					$parsed_emails = array_filter( array_map( 'sanitize_email', array_map( 'trim', explode( ',', $emails_option ) ) ) );
					if ( ! empty( $parsed_emails ) ) {
						$admin_emails = $parsed_emails;
					}
				}
				$subject = sprintf( __( '[New Lead] Chat Conversation #%d Transcript', 'techno-chatbot' ), $conversation_id );
				$this->send_email_transcript( $admin_emails, $messages, $subject );
			}

			// 2. Send transcript to user if they provided an end-of-chat email
			foreach ( $messages as $msg ) {
				if ( isset( $msg['message_type'] ) && 'email_end_input_answer' === $msg['message_type'] ) {
					// Adjust 'message' key to match wherever the email string is stored in your array
					$user_email = sanitize_email( $msg['message'] ?? $msg['content'] ?? '' );

					if ( is_email( $user_email ) ) {
						// Omitting the 3rd parameter lets send_email_transcript use its default subject
						$this->send_email_transcript( [ $user_email ], $messages );
					}
					break; // Stop after finding the first matching message
				}
			}
		}

		wp_send_json_success( [ 'message' => 'Conversation ended successfully' ] );
	}

	/**
	 * Get conversation
	 *
	 * @since    1.1.0
	 */
	public function get_conversation() {
		check_ajax_referer( 'techno_chatbot_nonce', 'nonce' );

		/* ---- rate limit: max 60 saves per minute per IP ---- */
        $ip = $this->get_client_ip();
        $rate_key = 'techno_get_convo_' . md5( $ip );
        $rate_count = (int) get_transient( $rate_key );
        if ( $rate_count >= 60 ) {
            wp_send_json_error( [ 'message' => 'Rate limit exceeded' ], 429 );
        }
        set_transient( $rate_key, $rate_count + 1, 60 );

		/* Check Session ID */
		$session_id = isset( $_POST['session_id'] )? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
        if ( ! $session_id || $session_id == '' ) {
            wp_send_json_error( [ 'message' => 'Missing Session' ], 400 );
        }
        /* session_id must be alphanumeric + dash/underscore only */
        if ( ! preg_match( '/^[a-zA-Z0-9\-_]+$/', $session_id ) ) {
            wp_send_json_error( [ 'message' => 'Invalid session_id format' ], 400 );
        }

    	$data = $this->get_chat_messages( $session_id );
		if ( empty( $data['success'] ) || ( !is_null( $data['ended_at'] ) && $data['ended_at'] !== '0000-00-00 00:00:00' ) ) {
			wp_send_json_error( [ 'message' => 'Conversation not found or has ended. Please start a new session.' ], 400 );
		}

		wp_send_json_success( [
			'conversation' => $data['conversation_id'],
			'socket'       => $data['socket_id'],
			'visitor_name' => $data['visitor_name'],
			'messages'     => $data['messages']
		] );
	}

	/**
	 * Save chat message
	 *
	 * @since    1.1.0
	 */
	public function save_chat_message() {
        check_ajax_referer( 'techno_chatbot_nonce', 'nonce' );

        /* ---- rate limit: max 60 saves per minute per IP ---- */
        $ip = $this->get_client_ip();
        $rate_key = 'techno_chat_save_' . md5( $ip );
        $rate_count = (int) get_transient( $rate_key );
        if ( $rate_count >= 60 ) {
            wp_send_json_error( [ 'message' => 'Rate limit exceeded' ], 429 );
        }
        set_transient( $rate_key, $rate_count + 1, 60 );
 
        /* ---- validate inputs ---- */
		$session_id = isset( $_POST['session_id'] )? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : null;
		$conversation_id = ! empty( $_POST['conversation_id'] ) ? absint( wp_unslash( $_POST['conversation_id'] ) ) : null;
		$sender = isset($_POST['sender'])? sanitize_text_field($_POST['sender']) : '';
		$type = isset($_POST['message_type'])? sanitize_text_field($_POST['message_type']) : 'text';
		$raw_message = isset( $_POST['message'] ) ? trim( wp_unslash( $_POST['message'] ) ) : '';
        if ( ! $conversation_id || ! $sender || ! $raw_message || ! $session_id ) {
            wp_send_json_error( [ 'message' => 'Missing required fields' ], 400 );
        }
        /* session_id must be alphanumeric + dash/underscore only */
        if ( ! preg_match( '/^[a-zA-Z0-9\-_]+$/', $session_id ) ) {
            wp_send_json_error( [ 'message' => 'Invalid session_id format' ], 400 );
        }
 
        /* sender must be one of the allowed enum values */
        if ( ! in_array( $sender, [ 'visitor', 'bot' ], true ) ) {
            wp_send_json_error( [ 'message' => 'Invalid sender' ], 400 );
        }

		/* type must be one of the allowed enum values */
        if ( !in_array( $type, [ 'text', 'phone_input', 'email_input', 'time_input', 'name_input', 'email_end_input', 'email_end_input_answer', 'phone_input_answer', 'email_input_answer', 'time_input_answer', 'name_input_answer', 'system' ], true ) ) {
            wp_send_json_error( [ 'message' => 'Invalid message type' ], 400 );
        }

		/* Check sender and allow html for bot messages */
		if ( $sender === 'bot' ) {
			$allowed_html = [
				'p'      => [],
				'strong' => [],
				'b'      => [],
				'em'     => [],
				'i'      => [],
				'ul'     => [],
				'ol'     => [],
				'li'     => [],
				'br'     => [],
			];
			$message = wp_kses( $raw_message, $allowed_html );
		} else {
			$message = sanitize_textarea_field( $raw_message );
		}
 
        /* message length guard */
		if (strlen($message) <= 1) {
			wp_send_json_error(['message' => 'Empty message'], 400);
		}
        if ( mb_strlen( $message ) > 2000 ) {
            wp_send_json_error( [ 'message' => 'Message too long' ], 400 );
        }

		$tokens = null;
		if ( ! empty( $_POST['token'] ) ) $tokens = json_decode( wp_unslash( $_POST['token'] ), true );
 
        global $wpdb;

        /* ---- Ownership & Active Status Check ---- */
        $conversations_table = $wpdb->prefix . 'techno_cb_conversations';
		$conversation = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT ended_at FROM {$conversations_table} WHERE id = %d AND session_id = %s",
				$conversation_id,
				$session_id
			)
		);
        if ( null === $conversation ) {
            wp_send_json_error( [ 'message' => 'Invalid conversation or session mismatch' ], 403 );
        }
        if ( isset($conversation->ended_at) && $conversation->ended_at !== '0000-00-00 00:00:00' ) {
            wp_send_json_error( [ 'message' => 'Conversation has already ended. Please start a new session.' ], 400 );
        }

        $result = $wpdb->insert( $wpdb->prefix . 'techno_cb_messages', [
			'conversation_id' => $conversation_id,
			'sender' => $sender,
			'message' => $message,
			'message_type' => $type,
			'prompt_tokens' => $tokens !== null ? (int) ( $tokens['prompt_tokens'] ?? 0 ) : null,
			'completion_tokens' => $tokens !== null ? (int) ( $tokens['completion_tokens'] ?? 0 ) : null,
		], [
			'%d', // conversation_id
			'%s', // sender
			'%s', // message
			'%s', // message_type
			'%d', // prompt_tokens
			'%d', // completion_tokens
		] );
 
        if ( $result === false ) {
            wp_send_json_error( [ 'message' => $wpdb->last_error ], 500 );
        }
        wp_send_json_success( [ 'id' => $wpdb->insert_id ] );
    }

	/**
	 * Get chat history
	 *
	 * @since 1.1.0
	 */
	private function get_chat_messages( $session_id ) {
		global $wpdb;

		$table_conversations = $wpdb->prefix . 'techno_cb_conversations';
		$table_messages = $wpdb->prefix . 'techno_cb_messages';

		// Query full conversation details in a single query
		$conversation = $wpdb->get_row( 
			$wpdb->prepare( 
				"SELECT id, socket_id, name, ended_at FROM {$table_conversations} WHERE session_id = %s LIMIT 1", 
				$session_id 
			) 
		);

		if ( ! $conversation || $wpdb->last_error ) {
			return [ 
				'success' => false, 
				'message' => 'Conversation not found' 
			];
		}

		$messages = $wpdb->get_results(
			$wpdb->prepare( 
				"SELECT sender, message, message_type, created_at FROM {$table_messages} WHERE conversation_id = %d ORDER BY created_at ASC", 
				$conversation->id 
			), 
			ARRAY_A
		); 

		return [ 
			'success' => true, 
			'conversation_id' => (int) $conversation->id,
			'socket_id' => $conversation->socket_id,
			'visitor_name' => $conversation->name,
			'ended_at' => $conversation->ended_at,
			'messages' => $messages ?: []
		];
	}

	/**
	 * Get and Send History to Customer
	 *
	 * @since 1.1.0
	 */
	public function send_transcript() {
		check_ajax_referer( 'techno_chatbot_nonce', 'nonce' );

		/* ---- Rate Limit: Max 60 requests per minute per IP ---- */
		$ip = $this->get_client_ip();
		$rate_key   = 'techno_sendtranscript_' . md5( $ip );
		$rate_count = (int) get_transient( $rate_key );
		if ( $rate_count >= 60 ) {
			wp_send_json_error( [ 'message' => 'Rate limit exceeded' ], 429 );
		}
		set_transient( $rate_key, $rate_count + 1, 60 );

		/* Check Session ID */
		$session_id = isset( $_POST['session_id'] ) ? sanitize_text_field( wp_unslash( $_POST['session_id'] ) ) : '';
		if ( empty( $session_id ) ) {
			wp_send_json_error( [ 'message' => 'Missing Session' ], 400 );
		}
		if ( ! preg_match( '/^[a-zA-Z0-9\-_]+$/', $session_id ) ) {
			wp_send_json_error( [ 'message' => 'Invalid session_id format' ], 400 );
		}

		$chat_data = $this->get_chat_messages( $session_id );
		if ( empty( $chat_data['success'] ) ) {
			wp_send_json_error( [ 'message' => 'Conversation not found' ], 400 );
		}

		$messages = $chat_data['messages'];
		$conversation_id = $chat_data['conversation_id'];
		$target_email = '';

		// Search backward for the latest 'email_input_answer' message type
		for ( $i = count( $messages ) - 1; $i >= 0; $i-- ) {
			if ( isset( $messages[$i]['message_type'] ) && 'email_input_answer' === $messages[$i]['message_type'] ) {
				$sanitized_email = sanitize_email( $messages[$i]['message'] );
				if ( is_email( $sanitized_email ) ) {
					$target_email = $sanitized_email;
					break;
				}
			}
		}
		if ( empty( $target_email ) ) {
			wp_send_json_error( [ 'message' => "No valid email found for conversation #{$conversation_id}" ], 400 );
		}

		$sent = $this->send_email_transcript( $target_email, $messages );
		if ( $sent ) {
			wp_send_json_success( [ 'message' => 'Transcript sent successfully' ] );
		} else {
			wp_send_json_error( [ 'message' => 'Email sending error' ], 500 );
		}
	}

	/**
	 * Send chat messages
	 *
	 * @since 1.1.0
	 */
	private function send_email_transcript( $target_email, array $messages, $subject = '' ) {
		// 1. Sanitize and filter target emails into a valid list
		$recipients = [];
		
		if ( is_array( $target_email ) ) {
			foreach ( $target_email as $email ) {
				$sanitized = sanitize_email( $email );
				if ( is_email( $sanitized ) ) {
					$recipients[] = $sanitized;
				}
			}
		} else {
			$sanitized = sanitize_email( $target_email );
			if ( is_email( $sanitized ) ) {
				$recipients[] = $sanitized;
			}
		}

		// Fail early if no valid recipients or messages exist
		if ( empty( $recipients ) || empty( $messages ) ) {
			return false;
		}

		// Remove duplicates
		$recipients = array_unique( $recipients );

		// 2. Build or fallback the subject line
		$site_name = get_bloginfo( 'name' );
		
		if ( empty( trim( $subject ) ) ) {
			$subject = sprintf( __( 'Your %s Chat Transcript', 'techno-chatbot' ), $site_name );
		} else {
			$subject = sanitize_text_field( $subject );
		}

		// 3. Build plain-text email body
		$email_body = "Chatbot Conversation Transcript\n";
		$email_body .= "----------------------------------------\n\n";

		foreach ( $messages as $msg ) {
			if ( empty( $msg['sender'] ) || ! isset( $msg['message'] ) ) {
				continue;
			}

			$sender = ucfirst( sanitize_text_field( $msg['sender'] ) );
			$text = sanitize_textarea_field( $msg['message'] );
			$timestamp = ! empty( $msg['created_at'] ) ? " [{$msg['created_at']}]" : '';
			$email_body .= "{$sender}{$timestamp}:\n{$text}\n\n";
		}

		// 4. Send email (wp_mail handles array of recipients natively)
		return wp_mail( $recipients, $subject, $email_body );
	}

	/**
	 * Limit Token Context
	 *
	 * @since    1.0.0
	 */
	private function limit_context_tokens($text, $maxChars = 1200) {
		return mb_substr($text, 0, $maxChars);
	}

	/**
	 * Cosine Similarity
	 *
	 * @since    1.0.0
	 */
	private function cosine_similarity($a, $b) {

		$dot = 0;
		$normA = 0;
		$normB = 0;
		$len = min(count($a), count($b));
		if ($len === 0) {
			return 0;
		}

		for ($i = 0; $i < $len; $i++) {
			$dot += $a[$i] * $b[$i];
			$normA += $a[$i] * $a[$i];
			$normB += $b[$i] * $b[$i];
		}

		if ($normA == 0 || $normB == 0) {
			return 0;
		}

		return $dot / (sqrt($normA) * sqrt($normB));
	}

	/**
	 * Create Embedding
	 *
	 * @since    1.0.0
	 */
	private function create_embedding($text) {
		$api_key = get_option('techno_chatbot_openai_secret');
		if (!$api_key) return false;

		$response = wp_remote_post(
			'https://api.openai.com/v1/embeddings',
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				],
				'body' => wp_json_encode([
					'model' => 'text-embedding-3-small',
					'input' => $text
				]),
				'timeout' => 60
			]
		);

		if (is_wp_error($response)) {
			error_log( 'Embedding Error: ' . $response->get_error_message());
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body($response), true );
		if (!isset($body['data'][0]['embedding'])) {
			error_log( 'Embedding API Response: ' . print_r($body, true) );
			return false;
		}

		return $body['data'][0]['embedding'];
	}

	/**
	 * AI Find Relevant Chunks
	 *
	 * @since    1.0.0
	 */
	private function find_relevant_chunks($question, $limit = 3) {
		$question_embedding = $this->create_embedding($question);

		if (!$question_embedding) return [];

		$results = [];
		$posts = get_posts([
			'post_type'   => 'techno_chatbot_aidb',
			'numberposts' => -1,
			'post_status' => 'publish'
		]);

		foreach ($posts as $post) {
			$chunks = get_post_meta($post->ID, '_ai_embeddings', true);
			
			if (is_string($chunks)) $chunks = maybe_unserialize($chunks);
			if (empty($chunks)) continue;

			foreach ($chunks as $chunk) {
				if ( empty($chunk['embedding']) || !is_array($chunk['embedding']) || empty($chunk['text']) ) continue;

				$similarity = $this->cosine_similarity(
					$question_embedding,
					$chunk['embedding']
				);
				$lengthPenalty = 1 / (1 + (strlen($chunk['text']) / 1000));
				$results[] = [
					'text'       => $chunk['text'],
					'similarity' => $similarity,
					'score'      => $similarity * $lengthPenalty,
				];
			}
		}
		if (empty($results)) return [];

		/* Find highest similarity */
		$bestSimilarity = max(array_column($results, 'similarity'));
		/* Set Threshold 
		Can set a lower absolute minimum (e.g., 0.10 or 0.15) to prevent trash results */
		$minimumFloor = 0.12; 
		$threshold = max($minimumFloor, $bestSimilarity * 0.80);
		/* Keep only relevant chunks */
		$results = array_filter($results, function ($chunk) use ($threshold) {
			return $chunk['similarity'] >= $threshold;
		});
		usort($results, fn($a, $b) => $b['score'] <=> $a['score']);
		return array_slice($results, 0, $limit);
	}

	/**
	 * OpeanAI request
	 *
	 * @since    1.0.0
	 */
	private function ask_openai($question, $context_chunks) {
		$api_key = get_option('techno_chatbot_openai_secret');

		if (!$api_key) {
			error_log('TechnoChatbot OpenAI API key not configured.');
			return [
				'answer' => 'NO_ANSWER',
				'tokens' => 0,
				'prompt_tokens' => 0,
				'completion_tokens' => 0
			];
		}

		if (empty($context_chunks)) {
			return [
				'answer' => 'NO_ANSWER',
				'tokens' => 0,
				'prompt_tokens' => 0,
				'completion_tokens' => 0
			];
		}

		$context_text = '';
		foreach ($context_chunks as $chunk) {
			$text = $this->limit_context_tokens($chunk['text'], 800);
			$context_text .= "SOURCE:\n" . $text . "\n\n";
		}

		/* AI Cache */
		$cache_key = 'techno_ai_ans_' . md5( strtolower(trim($question)) . '|' . md5($context_text) );
		$cached = get_transient($cache_key);
		if ($cached !== false) {
			$cached['cached'] = true;
			return $cached;
		}

		$prompt = "You are a helpful customer support assistant.
		Instructions:
		- Use the provided context to answer the user's question naturally and conversationally.
		- Always produce a complete, self-contained answer.
		- Keep answers direct, concise, informative, and avoid repeating information.
		- Do NOT assume the user has seen previous messages or context.
		- Do NOT refer to previous answers, earlier messages, or the provided context.
		- Do NOT use wording that depends on prior conversation unless the current question clearly defines it.
		- If multiple facts are relevant, combine them into one clear explanation.
		- Build your answer using only these HTML tags: <p>, <strong>, <em>, <ul>, <ol>.
		- Use <strong> and <em> only for emphasis, and <ul>/<ol> when listing information improves readability.
		- If the information is not available, respond only with: 'NO_ANSWER', don't add any HTML tag to 'NO_ANSWER' response.
		Context:
		$context_text
		Question:
		$question";
	
		$response = wp_remote_post(
			'https://api.openai.com/v1/chat/completions',
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				],
				'body' => wp_json_encode([
					'model' => 'gpt-4o-mini',
					'messages' => [
						[
							'role' => 'user',
							'content' => $prompt
						]
					],
					'temperature' => 0
				]),
				'timeout' => 60
			]
		);

		if (is_wp_error($response)) {
			error_log('TechnoChatbot Error contacting AI.' . $response->get_error_message());
			return [
				'answer' => 'NO_ANSWER',
				'tokens' => 0,
				'prompt_tokens' => 0,
				'completion_tokens' => 0
			];
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);
		$result = [
			'answer' => $body['choices'][0]['message']['content'] ?? 'NO_ANSWER',
			'tokens' => $body['usage']['total_tokens'] ?? 0,
			'prompt_tokens' => $body['usage']['prompt_tokens'] ?? 0,
			'completion_tokens' => $body['usage']['completion_tokens'] ?? 0,
			'cached' => false,
		];

		/* Cache for 30 days */
		set_transient( $cache_key, $result, 30 * DAY_IN_SECONDS );
		return $result;
	}

	/**
	 * OpeanAI request
	 *
	 * @since    1.0.0
	 */
	public function ask_ai() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'techno_chatbot_nonce')) {
			wp_send_json_error('Invalid nonce');
		}
		$question = sanitize_text_field($_POST['question']);
		if (!$question) {
			wp_send_json_error('Empty question');
		}
		$chunks = $this->find_relevant_chunks($question);
		$result = $this->ask_openai($question, $chunks);

		wp_send_json_success([
			'answer' => $result['answer'],
			'prompt_tokens' => $result['prompt_tokens'],
			'completion_tokens' => $result['completion_tokens'],
			/* 'results' => $result, */
		]);
	}

	/**
	 * Check AI usage on every page load.
	 *
	 * @since 1.1.0
	 */
	public function get_ai_assisted_history() {
		global $wpdb;
		$table = $wpdb->prefix . 'techno_cb_messages';
		
		// Count messages where AI was used
		$count = (int) $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$table}
			WHERE prompt_tokens > 0 OR completion_tokens > 0"
		);

		// If limit is NOT reached, ensure notification flag is cleared and exit early
		if ( $count < $this->ai_assist_limit ) {
			delete_option( 'techno_chatbot_limit_notified' );
			return false;
		}

		// 1. Set AI replies option to 0 (instead of deleting)
		update_option( 'techno_chatbot_aireplies', 0 );

		// 2. Check if we already sent the notifications
		if ( get_option( 'techno_chatbot_limit_notified' ) ) {
			return true; // Limit is reached, but emails were already sent once
		}

		/*
		* Get recipient emails.
		*/
		$emails_option = get_option( 'techno_chatbot_emails', '' );
		$recipients    = [];

		if ( ! empty( $emails_option ) ) {
			$emails = preg_split( '/[\r\n,]+/', $emails_option );

			foreach ( $emails as $email ) {
				$email = sanitize_email( trim( $email ) );

				if ( is_email( $email ) ) {
					$recipients[] = $email;
				}
			}
		}

		// Fallback to admin email.
		if ( empty( $recipients ) ) {
			$admin_email = sanitize_email( get_option( 'admin_email' ) );

			if ( is_email( $admin_email ) ) {
				$recipients[] = $admin_email;
			}
		}

		/*
		* Notify site admin.
		*/
		if ( ! empty( $recipients ) ) {
			wp_mail(
				$recipients,
				'AI Chat Limit Reached',
				'Your limit has reached ....'
			);
		}

		/*
		* Notify Techno.
		*/
		$site_url = home_url();

		wp_mail(
			'contact@techno.com',
			'Client AI Chat Limit Reached',
			sprintf(
				'Our client has reached the AI-assisted chat limit.%sSite: %s',
				PHP_EOL . PHP_EOL,
				$site_url
			)
		);

		// 3. Mark notification as sent so it only runs ONCE
		update_option( 'techno_chatbot_limit_notified', 1 );

		return true;
	}

	public function testAI(){
		$test = wp_remote_get( 'https://api.openai.com/v1/models', [
			'headers' => [ 'Authorization' => 'Bearer ' . get_option('techno_chatbot_openai_secret') ],
			'timeout' => 10,
		] );

		if ( is_wp_error( $test ) ) {
			error_log( 'OpenAI Direct Test Failed: ' . $test->get_error_message() );
		} else {
			error_log( 'OpenAI Direct Test Succeeded!' );
		}
	}
}
