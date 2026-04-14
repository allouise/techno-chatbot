<?php

/**
 * The admin settings field - chatbot
 *
 * @link       https://technodreamwebdesign.com/
 * @since      1.0.0
 *
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/admin
 */

/**
 * The admin settings field - chatbot
 *
 * @since      1.0.0
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/admin
 * @author     Technodream <al.esilverconnect@gmail.com>
 */
class Techno_Chatbot_Admin_Fields_General {

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
    }

    /**
	 * Define Sections
	 *
	 * @since    1.0.0
	 */
    private $sections = array(
        'general_section' => array(
            'title' => 'Settings',
        ),
        'livechat_section' => array(
            'title' => 'Livechat',
        ),
		'nextstep_section' => array(
            'title' => 'Next Step',
        ),
    );

    /**
	 * Define Fields
	 *
	 * @since    1.0.0
	 */
    private static $fields = array(

		'techno_chatbot_enabled' => array(
			'label'       => 'Enable / Show Chatbot',
			'type'        => 'checkbox',
			'section'     => 'general_section',
			'default'     => 1,
			'description' => 'Display the chatbot on the website.',
		),

		'techno_chatbot_disclaimer' => array(
			'label'       => 'Show Disclaimer',
			'type'        => 'checkbox',
			'section'     => 'general_section',
			'default'     => 1,
			'description' => 'Display the disclaimer in chatbot and menu',
		),

		'techno_chatbot_emails' => array(
			'label'       => 'Notification Emails',
			'type'        => 'text',
			'section'     => 'general_section',
			'default'     => '',
			'placeholder' => '',
			'description' => 'Admin emails to receive notifications. Separate emails with comma. e.g. (email1@email.com, email2@email.com). If blank default email to receive the notifications will be Wordpress administration email.',
		),

        // Livechat
		'techno_chatbot_live_chat_trigger' => array(
			'label'       => 'Live Chat Trigger',
			'type'        => 'text',
			'section'     => 'livechat_section',
			'default'     => 'talk to an agent, live chat',
			'placeholder' => 'help, support, live chat',
			'description' => 'Separate trigger text with comma. e.g. (Help, Support, Live chat)',
			'features'	  => array('live_chat')
		),

        // Nextstep
		'techno_chatbot_transfer_trigger_keyword' => array(
			'label'       => 'Next Step Trigger Keyword',
			'type'        => 'text',
			'section'     => 'nextstep_section',
			'default'     => '',
			'placeholder' => '',
			'description' => 'Separate trigger keyword with comma. WARNING: Once the system detected this keyword even in a sentence asked it will automatically transfer the chat to the next step (affects live chat/default no answer).',
		),

	);


    /**
	 * Register fields
	 *
	 * @since    1.0.0
	 */
    public function register( $page_slug ) {

        foreach ( $this->sections as $section_id => $section ) {

			add_settings_section(
				$section_id,
				__( $section['title'], 'techno-chatbot' ),
				null,
				$page_slug
			);

		}

		foreach ( self::$fields as $option => $data ) {

			register_setting(
				'techno_chatbot_general_group',
				$option,
				array(
					'sanitize_callback' => $data['type'] === 'checkbox'
						? array( $this, 'sanitize_checkbox' )
						: 'sanitize_text_field',
					'default' => $data['default'],
				)
			);

			add_settings_field(
				$option,
				__( $data['label'], 'techno-chatbot' ),
				array( $this, 'render_field' ),
				$page_slug,
				$data['section'],
				array(
					'option_name' => $option,
					'type'        => $data['type'],
					'default'     => $data['default'],
					'description' => $data['description'] ?? '',
					'placeholder' => $data['placeholder'] ?? '',
					'features'	  => $data['features'] ?? '',
					'disabled' 	  => $data['disabled'] ?? '',
				)
			);

		}

    }

    /**
	 * Render Fields
	 *
	 * @since    1.0.0
	 */
	public function render_field( $args ) {

		$option      = $args['option_name'];
		$type        = $args['type'];
		$default     = $args['default'];
		$description = $args['description'];
		$features	 = $args['features'];
		$placeholder = $args['placeholder'];
		$value       = get_option( $option, $default );
        $value		 = ( $default !== '' && $value === '' ) ? $default : $value;
		$disabled    = ( isset($args['disabled']) && $args['disabled'] == 1 )? 'disabled' : '';
		$disabledmsg = '';
		
		if( $features ){
			$plans = techno_chatbot_feature($features);
			$disabled = $plans['allowed'] == false ? 'disabled' : $disabled;
			$disabledmsg = $plans['message'];
		}

		// Checkbox
		if ( $type === 'checkbox' ) {
			echo '<label>';
			echo '<input '.$disabled.'
					type="checkbox" 
					name="' . esc_attr( $option ) . '" 
					value="1" ' . checked( 1, $value, false ) . ' 
				/>';
			if ( ! empty( $description ) ) {
				echo ' ' . esc_html__( $description, 'techno-chatbot' );
			}
			echo '</label>';
		} else {
			echo '<input '.$disabled.'
					type="text"
					name="' . esc_attr( $option ) . '"
					value="' . esc_attr( $value ) . '"
					class="regular-text"
					style="width:100%;"
					placeholder="' . esc_attr( $placeholder ) . '"
				/>';
		}

		if ( ! empty( $description ) && $type !== 'checkbox' ) {
			echo '<p class="description">'
				. esc_html__( $description, 'techno-chatbot' ) .
			'</p>';
		}

		// Pro notice
		if ( $disabledmsg ) {
			techno_chatbot_msgformat($disabledmsg);
		}

	}

    /**
	 * Sanitize checkbox input
	 *
	 * @since    1.0.0
	 */
	public function sanitize_checkbox( $value ) {
		return ( isset( $value ) && 1 == $value ) ? 1 : 0;
	}

    /**
	 * Get field values for other classes
	 *
	 * @since    1.0.0
	 */
	public static function get_value( $key ) {
		$default = self::$fields[$key]['default'] ?? '';
        $value = get_option( $key, $default );
		return ( $default !== '' && $value === '' ) ? $default : $value;
    }

}