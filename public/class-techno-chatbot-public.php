<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://technodreamwebdesign.com/
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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/techno-chatbot-public.js', array(), $this->version, true );

		$livechat_plan   = techno_chatbot_feature('live_chat');
    	$livechat_enabled = $livechat_plan['allowed'] === true;
		$script_array = array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('techno_chatbot_nonce'),
			'supportOnline' => techno_wss_check() ? (int) get_user_meta( get_current_user_id(), 'techno_chat_online', true ) : false,
			'liveChatEnabled' => $livechat_enabled,
			'disclaimerEnabled' => Techno_Chatbot_Admin_Fields_General::get_value('techno_chatbot_disclaimer'),
			'aiEnabled' => Techno_Chatbot_Admin_Fields_General::get_value('techno_chatbot_aireplies'),
			'transferLiveChatKeywords' => explode(',', get_option( 'techno_chatbot_live_chat_trigger' )),
			'disclaimerMsg' => Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_disclaimermsg'),
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
			'noAnswerTrigger' => Techno_Chatbot_Admin_Fields_Behaviors::get_value('techno_chatbot_no_answer_trigger'),
			'idleTimer' => Techno_Chatbot_Admin_Fields_Behaviors::get_value('techno_chatbot_idle_support'),
			'timeToCall' => get_option('techno_chatbot_timetocall'),
			'transferKeywords' => explode(',', get_option( 'techno_chatbot_transfer_trigger_keyword' )),
			'faq' => $this->get_faq_data()
		);

		if( $livechat_enabled == true ){
			wp_enqueue_script( $this->plugin_name.'-socket-io', plugin_dir_url( __FILE__ ) . 'js/socket.io.min.js', array(), $this->version, true
			);

			$ws = techno_chatbot_websocket();
			$site = get_site_url();
			$script_array['ws_url'] = $ws->get_url();
			$script_array['site_id'] = $site;
			$script_array['token'] = $ws->get_token($site);
		}
		
		wp_localize_script(
			$this->plugin_name, 'technoChatbot',
			$script_array
		);

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
		if ( ! $enabled ) {
			return;
		}

		$headertxt = Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_header');
		$icontxt = Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_icontext');
		$inputtxt = Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_inputtext');
		$sendbtn = Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_sendbtn');

		$disclaimerEnabled = Techno_Chatbot_Admin_Fields_General::get_value('techno_chatbot_disclaimer');
		$disclaimerFullMsg = Techno_Chatbot_Admin_Fields_Texts::get_value('techno_chatbot_disclaimerfullmsg');

		$chaticon = get_option( 'techno_chatbot_icon' );
		$chaticon = !empty($chaticon)? "<img src='$chaticon' alt='".__( 'Techno chatbot Icon', 'techno-chatbot' )."'/>" : '💬';
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

		$height   = absint( Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_height' ) );
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

		$headingsize = Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_heading_size' );
		$chatmsgsize = Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_chatmsg_size' );
		$inputtxtsize = Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_inputtxt_size' );
		$sendbtnsize = Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_sendbtn_size' );
		$floatingtxtsize = Techno_Chatbot_Admin_Fields_Styles::get_value( 'techno_chatbot_floatingtxt_size' );

		$position_css = "#techno-chatbot-floating-icon{ bottom: {$offset_y}px; right: {$offset_x}px; } #techno-chatbot-window{ bottom: {$icon_offset_y}px; right: {$offset_x}px; } #techno-chatbot-floating-text{ bottom: {$offset_y}px; right: {$floating_offset_x}px; border-radius: 10px 0 10px 10px; }";
		if( $position == 'upper left' ){
			$position_css = "#techno-chatbot-floating-icon{ top: {$offset_y}px; left: {$offset_x}px; } #techno-chatbot-window{ top: {$icon_offset_y}px; left: {$offset_x}px; } #techno-chatbot-floating-text{ top: {$offset_y}px; left: {$floating_offset_x}px; border-radius: 10px 10px 10px 0; }";
		}elseif ( $position == 'top center' ){
			$position_css = "#techno-chatbot-floating-icon{ top: {$offset_y}px; left: 50%; transform: translateX(-50%); } #techno-chatbot-window{ top: {$icon_offset_y}px; left: 50%; transform: translateX(-50%); } #techno-chatbot-floating-text{ top: {$floating_offset_y}px; left: 50%; transform: translateX(-50%); border-radius: 10px; }";
		}elseif ( $position == 'upper right' ){
			$position_css = "#techno-chatbot-floating-icon{ top: {$offset_y}px; right: {$offset_x}px; } #techno-chatbot-window{ top: {$icon_offset_y}px; right: {$offset_x}px; } #techno-chatbot-floating-text{ top: {$offset_y}px; right: {$floating_offset_x}px; border-radius: 10px 10px 0 10px; }";
		}elseif ( $position == 'left' ){
			$position_css = "#techno-chatbot-floating-icon{ top: 50%; transform: translateY(-50%); left: {$offset_x}px; } #techno-chatbot-window{ top: 50%; transform: translateY(-50%); left: {$icon_offset_x}px; } #techno-chatbot-floating-text{ top: 50%; transform: translateY(-50%); left: {$floating_offset_x}px; border-radius: 10px 10px 10px 0; }";
		}elseif ( $position == 'right' ){
			$position_css = "#techno-chatbot-floating-icon{ top: 50%; transform: translateY(-50%); right: {$offset_x}px; } #techno-chatbot-window{ top: 50%; transform: translateY(-50%); right: {$icon_offset_x}px; } #techno-chatbot-floating-text{ top: 50%; transform: translateY(-50%); right: {$floating_offset_x}px; border-radius: 10px 10px 0 10px; }";
		}elseif ( $position == 'bottom left' ){
			$position_css = "#techno-chatbot-floating-icon{ bottom: {$offset_y}px; left: {$offset_x}px; } #techno-chatbot-window{ bottom: {$icon_offset_y}px; left: {$offset_x}px; } #techno-chatbot-floating-text{ bottom: {$offset_y}px; left: {$floating_offset_x}px; border-radius: 10px 10px 10px 0; }";
		}elseif ( $position == 'bottom center' ){
			$position_css = "#techno-chatbot-floating-icon{ bottom: {$offset_y}px; left: 50%; transform: translateX(-50%); } #techno-chatbot-window{ bottom: {$icon_offset_y}px; left: 50%; transform: translateX(-50%); } #techno-chatbot-floating-text{ bottom: {$floating_offset_y}px; left: 50%; transform: translateX(-50%); border-radius: 10px; ";
		}

		$css = "
		:root{
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
			--techno-chatbot-offset-x: {$offset_x}px;
			--techno-chatbot-offset-y: {$offset_y}px;
			--techno-chatbot-z-index: {$zindex};
			--techno-chatbot-iconsize: {$iconsize};
			--techno-chatbot-iconheight: {$icon_height}px;
			--techno-chatbot-iconwidth: {$icon_width}px;
			
			--techno-chatbot-headingsize: {$headingsize}px;
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
	 * Get and Send History
	 *
	 * @since 1.0.0
	 */
	public function send_history_admin(){
		check_ajax_referer('techno_chatbot_nonce','nonce');

		// Rate limit per IP
		$ip = $_SERVER['REMOTE_ADDR'];
		$transient_key = 'techno_chatbot_rate_' . md5($ip);
		$count = (int) get_transient($transient_key);

		if( $count >= 10 ){
			wp_send_json_error('Too many requests');
		}

		set_transient($transient_key, $count + 1, 60);
		if( empty($_POST['history']) ){
			wp_send_json_error();
		}
		$history = json_decode( stripslashes($_POST['history']), true );

		if( !is_array($history) ){
			wp_send_json_error();
		}

		// Limit messages to prevent abuse
		$history = array_slice($history, -30);
		$emails_option = get_option('techno_chatbot_emails');
		$admin_email = sanitize_email(get_option('admin_email'));
		if( !empty($emails_option) ){
			$emails = array_map('trim', explode(',', $emails_option));
			$admin_email = array_filter(array_map('sanitize_email', $emails));
		}

		$message = "Chatbot Conversation\n\n";
		foreach($history as $msg){

			if(!isset($msg['sender']) || !isset($msg['text'])){
				continue;
			}

			$sender = sanitize_text_field($msg['sender']);
			$text   = sanitize_textarea_field($msg['text']);
			$label = $sender === 'visitor' ? 'Visitor' : 'Bot';
			$message .= "{$label}: {$text}\n";
		}

		wp_mail(
			$admin_email,
			'New Chatbot Contact',
			$message
		);

		wp_send_json_success();

	}

	/**
	 * Get and Send History
	 *
	 * @since 1.0.0
	 */
	public function end_live_chat(){
		check_ajax_referer('techno_chatbot_nonce','nonce');

		// Rate limit per IP
		$ip = $_SERVER['REMOTE_ADDR'];
		$transient_key = 'techno_endchat_' . md5($ip);
		$count = (int) get_transient($transient_key);

		if( $count >= 10 ){
			wp_send_json_error('Too many requests');
		}

		set_transient($transient_key, $count + 1, 60);
		if( empty($_POST['history']) ){
			wp_send_json_error();
		}
		$history = json_decode( stripslashes($_POST['history']), true );

		if( !is_array($history) ){
			wp_send_json_error();
		}

		// Limit messages to prevent abuse
		$history = array_slice($history, -30);
		$emails_option = get_option('techno_chatbot_emails');
		$admin_email = sanitize_email(get_option('admin_email'));
		if( !empty($emails_option) ){
			$emails = array_map('trim', explode(',', $emails_option));
			$admin_email = array_filter(array_map('sanitize_email', $emails));
		}

		$message = "Chatbot Conversation\n\n";
		foreach($history as $msg){

			if(!isset($msg['sender']) || !isset($msg['text'])){
				continue;
			}

			$sender = sanitize_text_field($msg['sender']);
			$text   = sanitize_textarea_field($msg['text']);
			$label = $sender === 'visitor' ? 'Visitor' : 'Bot';
			$message .= "{$label}: {$text}\n";
		}

		$admin_mail = wp_mail( $admin_email, 'New Chatbot Contact', $message );
		$email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

		if( !$email && $admin_mail ){
			wp_send_json_success();
		}elseif( !$admin_mail ){
			wp_send_json_error('Admin Email Error');
		}
		
		$site_name = get_bloginfo('name');
		if( $email ){
			$client_mail = wp_mail( $email, "$site_name Chat Transcript", $message );
			if( $client_mail ){
				wp_send_json_success();
			}else{
				wp_send_json_error('Email Error');
			}
		}
	}

	/**
	 * Get and Send History to Customer
	 *
	 * @since 1.0.0
	 */
	public function send_transcript(){
		check_ajax_referer('techno_chatbot_nonce','nonce');

		// Rate limit per IP
		$ip = $_SERVER['REMOTE_ADDR'];
		$transient_key = 'techno_send_transcript_' . md5($ip);
		$count = (int) get_transient($transient_key);

		if( $count >= 10 ){
			wp_send_json_error('Too many requests');
		}

		set_transient($transient_key, $count + 1, 60);
		if( empty($_POST['history']) ){
			wp_send_json_error();
		}
		$history = json_decode( stripslashes($_POST['history']), true );
		if( !is_array($history) ){
			wp_send_json_error();
		}
		$email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
		if( !$email || empty($email) || $email == '' ){
			wp_send_json_error();
		}

		$history = array_slice($history, -30);
		$message = "Chatbot Conversation\n\n";
		foreach($history as $msg){

			if(!isset($msg['sender']) || !isset($msg['text'])){
				continue;
			}

			$sender = sanitize_text_field($msg['sender']);
			$text   = sanitize_textarea_field($msg['text']);
			$label = $sender === 'visitor' ? 'Visitor' : 'Bot';
			$message .= "{$label}: {$text}\n";
		}

		$site_name = get_bloginfo('name');
		if( $email ){
			$client_mail = wp_mail( $email, "$site_name Chat Transcript", $message );
			if( $client_mail ){
				wp_send_json_success();
			}else{
				wp_send_json_error('Email Error');
			}
		}
	}

	/**
	 * Scheduled Validate License
	 *
	 * @since    1.0.0
	 */
	public function validate_license() {
		$free = techno_chatbot_check_plan('free');
		if( $free === true ) return;
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
	 * Save chat message
	 *
	 * @since    1.0.0
	 */
	public function save_chat_message() {
        check_ajax_referer( 'techno_chatbot_nonce', 'nonce' );
 
        /* ---- rate limit: max 60 saves per minute per IP ---- */
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        $rate_key = 'techno_chat_save_' . md5( $ip );
        $rate_count = (int) get_transient( $rate_key );
        if ( $rate_count >= 60 ) {
            wp_send_json_error( [ 'message' => 'Rate limit exceeded' ], 429 );
        }
        set_transient( $rate_key, $rate_count + 1, 60 );
 
        /* ---- validate inputs ---- */
        $session_id = isset($_POST['session_id'])? sanitize_text_field($_POST['session_id']) : '';
		$sender = isset($_POST['sender'])? sanitize_text_field($_POST['sender']) : '';
		$message = isset($_POST['message'])? trim( sanitize_textarea_field($_POST['message']) ) : '';
		$visitor_name = isset($_POST['visitor_name'])? sanitize_text_field($_POST['visitor_name']) : null;
		$message_type = isset($_POST['message_type'])? sanitize_text_field($_POST['message_type']) : 'text';
 
        if ( ! $session_id || ! $sender || ! $message ) {
            wp_send_json_error( [ 'message' => 'Missing required fields' ], 400 );
        }

		if (strlen($message) < 1) {
			wp_send_json_error(['message' => 'Empty message'], 400);
		}
 
        /* session_id must be alphanumeric + dash/underscore only */
        if ( ! preg_match( '/^[a-zA-Z0-9\-_]+$/', $session_id ) ) {
            wp_send_json_error( [ 'message' => 'Invalid session_id format' ], 400 );
        }
 
        /* sender must be one of the allowed enum values */
        $allowed_senders = [ 'visitor', 'bot' ];
        if ( ! in_array( $sender, $allowed_senders, true ) ) {
            wp_send_json_error( [ 'message' => 'Invalid sender' ], 400 );
        }
 
        /* message length guard */
        if ( mb_strlen( $message ) > 2000 ) {
            wp_send_json_error( [ 'message' => 'Message too long' ], 400 );
        }

		/* message type validation */
		$allowed_types = ['text','image','file','system'];
		if ( ! in_array($message_type, $allowed_types, true) ) {
			$message_type = 'text';
		}

		/* metadata */
		$user_agent = isset($_SERVER['HTTP_USER_AGENT'])? substr( sanitize_text_field($_SERVER['HTTP_USER_AGENT']), 0, 255 ) : null;
		$ip_address = substr( sanitize_text_field($ip), 0, 45 );
 
        global $wpdb;
        $table = $wpdb->prefix . 'techno_livechat_messages';
        $data = [
			'session_id'   => $session_id,
			'sender'       => $sender,
			'message'      => $message,
			'message_type' => $message_type,
			'user_agent'   => $user_agent,
			'ip_address'   => $ip_address,
		];
		$format = [
			'%s', // session_id
			'%s', // sender
			'%s', // message
			'%s', // message_type
			'%s', // user_agent
			'%s', // ip_address
		];
 
        /* attach visitor_name when provided (visitor messages only) */
        if ( $visitor_name !== null && $sender === 'visitor' ) {
            $data['name'] = $visitor_name;
            $format[] = '%s';
        }
        $result = $wpdb->insert( $table, $data, $format );
 
        if ( $result === false ) {
            wp_send_json_error( [ 'message' => 'DB error' ], 500 );
        }
 
        wp_send_json_success( [ 'id' => $wpdb->insert_id ] );
    }

	/**
	 * Save chat message
	 *
	 * @since    1.0.0
	 */
	public function techno_bot_to_live(){
		check_ajax_referer( 'techno_chatbot_nonce', 'nonce' );

		$livechat_plan   = techno_chatbot_feature('live_chat');
    	$livechat_enabled = $livechat_plan['allowed'] === true;
		if( $livechat_enabled != true ){
			wp_send_json_error();
		}

		global $wpdb;
		$table = $wpdb->prefix . 'techno_livechat_messages';
		$session_id = sanitize_text_field( $_POST['session_id'] ?? '' );
		$history = wp_unslash( $_POST['history'] ?? '' );
		if (!$session_id || !$history){
			wp_send_json_error();
		}

		$messages = json_decode( $history, true );
		if (!is_array($messages)){
			wp_send_json_error();
		}

		/* metadata */
		$user_agent = isset($_SERVER['HTTP_USER_AGENT'])? substr( sanitize_text_field($_SERVER['HTTP_USER_AGENT']), 0, 255 ) : null;
		$ip = $_SERVER['HTTP_CF_CONNECTING_IP']?? $_SERVER['HTTP_X_FORWARDED_FOR']?? $_SERVER['REMOTE_ADDR']?? '';
		$ip_address = substr( sanitize_text_field($ip), 0, 45 );

		foreach ($messages as $msg){
			$sender = isset($msg['sender']) ? sanitize_text_field($msg['sender']) : '';
			$text = isset($msg['text']) ? sanitize_textarea_field($msg['text']) : '';
			$created_at = isset($msg['created_at']) ? sanitize_textarea_field($msg['created_at']) : '';
			if (!$text) continue;
			$result = $wpdb->insert(
				$table,
				[
					'session_id'	=> $session_id,
					'sender'		=> $sender,
					'message'		=> $text,
					'user_agent'	=> $user_agent,
					'ip_address'	=> $ip_address,
					'created_at'	=> $created_at
				],
				['%s','%s','%s','%s','%s','%s']
			);
			if ($result === false) {
				wp_send_json_error();
				error_log('DB INSERT FAILED: ' . $wpdb->last_error);
				error_log(print_r($wpdb->last_query, true));
				return;
			}
		}
		wp_send_json_success();
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
	 * Get Embed Cache
	 *
	 * @since    1.0.0
	 */
	private function get_embedding_cache($text) {
		return get_transient('emb_' . md5($text));
	}

	/**
	 * Set Embed Cache
	 *
	 * @since    1.0.0
	 */
	private function set_embedding_cache($text, $embedding) {
		set_transient('emb_' . md5($text), $embedding, WEEK_IN_SECONDS);
	}

	/**
	 * AI Find Relevant Chunks
	 *
	 * @since    1.0.0
	 */
	private function find_relevant_chunks($question, $limit = 3) {

		$question_embedding = $this->create_embedding($question);

		if (!$question_embedding) {
			return [];
		}

		$results = [];

		$posts = get_posts([
			'post_type' => 'techno_chatbot_aidb',
			'numberposts' => -1,
			'post_status' => 'publish'
		]);

		foreach ($posts as $post) {

			$stored = get_post_meta($post->ID, '_ai_embeddings', true);
			$chunks = $stored;
			if (is_string($chunks)) {
				$chunks = maybe_unserialize($chunks);
			}

			if (!$chunks) continue;

			foreach ($chunks as $chunk) {

				if (empty($chunk['embedding']) || !is_array($chunk['embedding'])) continue;
				if (!isset($chunk['embedding']) || !isset($chunk['text'])) continue;

				$score = $this->cosine_similarity($question_embedding, $chunk['embedding']);
				$lengthPenalty = 1 / (1 + (strlen($chunk['text']) / 1000));
				$score = $score * $lengthPenalty;
				// if ($score < 0.45) continue;

				$results[] = [
					'text' => $chunk['text'],
					'score' => $score
				];
			}
		}

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
			return 'NO_ANSWER';
		}

		if (empty($context_chunks)) {
			return 'NO_ANSWER';
		}

		$context_text = '';
		foreach ($context_chunks as $chunk) {
			$text = $this->limit_context_tokens($chunk['text'], 800);
			$context_text .= "SOURCE:\n" . $text . "\n\n";
		}

		$prompt = "
		You are a helpful customer support assistant.

		Use the provided context to answer the user's question naturally and conversationally.

		Instructions:
		- Answer directly using the context.
		- Always produce a complete, self-contained answer.
		- Do NOT assume the user has seen previous messages or context.
		- Do NOT refer to 'previous answers', 'above', 'earlier', or 'context'.
		- Do NOT use phrases that depend on follow-up continuity (like 'as mentioned', 'that', 'it', unless clearly defined in the current question).
		- If multiple facts are relevant, merge them into one clear explanation.
		- Keep answers direct, informative, and independent.
		- Keep the tone friendly and concise.
		- Avoid conversational dependency or implied follow-up context
		- Do not mention 'the context says' or 'according to the context.'
		- If the information is not available, respond only with: 'NO_ANSWER'

		Context:
		$context_text

		Question:
		$question
		";
	
		$response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
			'headers' => [
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json',
			],
			'body' => json_encode([
				'model' => 'gpt-4o-mini',
				'messages' => [
					['role' => 'user', 'content' => $prompt]
				],
				'temperature' => 0.3
			]),
			'timeout' => 20
		]);

		if (is_wp_error($response)) {
			error_log('TechnoChatbot Error contacting AI.');
			return 'NO_ANSWER';
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);
		return $body['choices'][0]['message']['content'] ?? 'NO_ANSWER';
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

		if (!$api_key) {
			return false;
		}

		$cached = $this->get_embedding_cache($text);
		if ($cached) {
			return $cached;
		}

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
				'timeout' => 30
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

		$embedding = $body['data'][0]['embedding'];
		$this->set_embedding_cache($text, $embedding);
		return $embedding;
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

		// 1. Get relevant chunks
		$chunks = $this->find_relevant_chunks($question);

		// 2. Ask OpenAI
		$answer = $this->ask_openai($question, $chunks);

		wp_send_json_success([
			'answer' => $answer
		]);
	}
}
