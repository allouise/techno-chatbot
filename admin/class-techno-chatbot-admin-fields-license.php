<?php

/**
 * The admin settings field - chatbot
 *
 * @link       https://technodreamwebdesign.com/techno-chatbot/
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
class Techno_Chatbot_Admin_Fields_License {

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
        'license_section' => array(
            'title' => 'License',
        ),
		'api_section' => array(
            'title' => 'TechnoDream Live Chat API',
        ),
		'openapi_section' => array(
            'title' => 'Open API',
        ),
    );

    /**
	 * Define Fields
	 *
	 * @since    1.0.0
	 */
    private static $fields = array(

		// License
		'techno_chatbot_license' => array(
			'label'       => 'License Key',
			'type'        => 'text',
			'section'     => 'license_section',
			'default'     => '',
			'placeholder' => '',
			'description' => '',
		),

		// API
		'techno_chatbot_apiurl' => array(
			'label'       => 'API Socket URL',
			'type'        => 'url',
			'section'     => 'api_section',
			'default'     => '',
			'placeholder' => '',
			'description' => 'Designated Socket URL provided by our store.',
			'features'	  => array('live_chat'),
		),

		'techno_chatbot_secret' => array(
			'label'       => 'Secret Key',
			'type'        => 'password',
			'section'     => 'api_section',
			'default'     => '',
			'placeholder' => '',
			'description' => '',
			'features'	  => array('live_chat'),
		),

		// Opean AI
		'techno_chatbot_openai_app' => array(
			'label'       => 'App Name',
			'type'        => 'text',
			'section'     => 'openapi_section',
			'default'     => '',
			'placeholder' => '',
			'description' => '',
			'features'	  => array('ai_training'),
		),

		'techno_chatbot_openai_secret' => array(
			'label'       => 'Secret Key',
			'type'        => 'password',
			'section'     => 'openapi_section',
			'default'     => '',
			'placeholder' => '',
			'description' => '',
			'features'	  => array('ai_training'),
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
				'techno_chatbot_license_group',
				$option,
				array(
					'sanitize_callback' => 
					$option === 'techno_chatbot_license'
						? array($this, 'validate_license')
						: ($option === 'techno_chatbot_secret' ||
							$option === 'techno_chatbot_openai_secret'
								? function($value) use ($option) {
									return $this->sanitize_secret_key($value, $option);
								}
							: ($data['type'] === 'checkbox'
								? array($this, 'sanitize_checkbox')
								: ($data['type'] === 'url'
									? 'esc_url_raw'
									: 'sanitize_text_field'
								)
							)
						),
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
		$disabled    = ( isset($args['disabled']) && $args['disabled'] == 1 )? 'disabled' : '';
		$default     = $args['default'];
		$description = $args['description'];
		$placeholder = $args['placeholder'];
		$features	 = $args['features'];
		$value		 = get_option( $option, $default );
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
			if ($type === 'password') {
				$input_type = 'password';
			} elseif ($type === 'url') {
				$input_type = 'url';
			} else {
				$input_type = 'text';
			}
			if ( $type === 'password' && !empty($value) ) {
				$display_value = '';
			} else {
				$display_value = $value;
			}

			echo '<input 
					type="' . esc_attr($input_type) . '"
					name="' . esc_attr( $option ) . '"
					value="' . esc_attr( $display_value ) . '"
					class="regular-text '.$disabled.'"
					style="width:100%;"
					placeholder="' . ( $type === 'password' && !empty($value)? '********' : esc_attr( $placeholder ) ) . '"
					'.( $input_type == 'url'? 'pattern="https?://.*"' : '' ).'
				/>';

			if ( $type === 'password' && !empty($value) ) {
				echo '<p class="description">';
				echo esc_html__('Leave blank to keep the current value.','techno-chatbot');
				echo '</p>';
			}
		}

		if ($option === 'techno_chatbot_license') {

			$license_data = get_option('techno_chatbot_license_data', [
				'plan' => 'free',
				'status' => 'invalid',
				'expiry_date' => '',
				'ai_assistance_limit' => 0,
				'language_count' => 0,
				'last_check' => ''
			]);

			$status = ucfirst($license_data['status'] ?? 'Inactive');
			$plan = ucfirst($license_data['plan'] ?? 'Free');
			$expiry_date = !empty($license_data['expiry_date']) ? date('M d, Y', strtotime($license_data['expiry_date'])) : 'Lifetime';
			$last_check = !empty($license_data['last_check']) ? date('Y-m-d H:i:s', $license_data['last_check']) : '';
			$ai_assistance_limit = (int) ( $license_data['ai_assistance_limit'] ?? 0 );
			$language_count = (int) ( $license_data['language_count'] ?? 0 );

			if( $status == 'Active' ){
				echo "<div class='license-details' style='margin-top:10px;'> 
					Last Check: <strong>$last_check</strong> | 
					Plan: <strong style='color: #0066ff;'>$plan</strong> | 
					Status: <strong style='color: #03a756;'>$status</strong> | 
					Expires: <strong>$expiry_date</strong> | 
					AI Assitance Limit: <strong>$ai_assistance_limit</strong> |
					Language Limit: <strong>$language_count</strong>
				</div>";
			}else{
				echo '<div class="license-details" style="margin-top:10px;">License Status: <strong style="color: #f00">' . esc_html($status) . '</strong></div>';
			}
			
		}

		if ( ! empty( $description ) && $type !== 'checkbox' ) {
			echo '<p class="description">'. esc_html__( $description, 'techno-chatbot' ) . '</p>';
		}

		// Pro notice
		if ( $disabledmsg ) {
			techno_chatbot_msgformat($disabledmsg);
		}

	}

	/**
	 * Validate License
	 *
	 * @since    1.0.0
	 */
	public function validate_license( $license_key ) {
		return techno_chatbot_license()->validate_license( $license_key );
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
	 * Sanitize Secret Key
	 *
	 * Only update if a new value is entered.
	 *
	 * @since 1.0.0
	 */
	public function sanitize_secret_key( $value, $option_name ) {
		$current = get_option($option_name, '');
		// If empty, keep existing value
		if ( empty($value) ) {
			return $current;
		}
		return sanitize_text_field( $value );
	}

}