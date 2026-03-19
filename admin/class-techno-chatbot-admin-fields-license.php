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
					'sanitize_callback' => $option === 'techno_chatbot_license'
						? array( $this, 'validate_license' )
						: ( $data['type'] === 'checkbox'
							? array( $this, 'sanitize_checkbox' )
							: 'sanitize_text_field' ),
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
		$value = get_option( $option, $default );

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

		if ($option === 'techno_chatbot_license') {
			$license_data = get_option('techno_chatbot_license_data', [
				'plan' => 'free',
				'status' => 'inactive',
				'expires' => ''
			]);
			$status = ucfirst($license_data['status'] ?? 'Inactive');
			$plan   = ucfirst($license_data['plan'] ?? 'Free');
			$expires = !empty($license_data['expires']) ? date('M d, Y', strtotime($license_data['expires'])) : '—';
			echo '<div class="license-details" style="margin-top:10px;"> Plan: <strong style="color: #0066ff;">' . esc_html($plan) . '</strong> Status: <strong style="color: '. ( $status == 'Active'? '#03a756' : '#f00' ) .';">' . esc_html($status) . '</strong> Expires: <strong>' . esc_html($expires) . '</strong>
			</div>';
		}

		if ( ! empty( $description ) && $type !== 'checkbox' ) {
			echo '<p class="description">'
				. esc_html__( $description, 'techno-chatbot' ) .
			'</p>';
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

}