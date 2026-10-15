<?php

/**
 * The admin settings field - styles
 *
 * @link       https://technodreamwebdesign.com/techno-chatbot/
 * @since      1.0.0
 *
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/admin
 */

/**
 * The admin settings field - styles
 *
 * @since      1.0.0
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/includes
 * @author     Technodream <al.esilverconnect@gmail.com>
 */
class Techno_Chatbot_Admin_Fields_Behaviors {

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
	 */
	private $sections = array(

		'behavior_section' => array(
			'title' => 'Chatbot Behavior',
		),
		'aibehavior_section' => array(
			'title' => 'AI Behavior',
		),

	);

	/**
	 * Define Fields
	 */
	private static $fields = array(

		'techno_chatbot_no_answer_trigger' => array(
			'label'       => 'No Answer Transfer Trigger',
			'type'        => 'number',
			'section'     => 'behavior_section',
			'default'     => 0,
			'min'         => 0,
			'description' => 'Transfer to live chat / default message if no correct answer is provided for (x) times.',
		),

		'techno_chatbot_idle_support' => array(
			'label'       => 'Idle Support',
			'type'        => 'number',
			'section'     => 'behavior_section',
			'default'     => '',
			'min'         => 0,
			'description' => 'Trigger default message after (x) seconds if the support goes idle. Leave blank to disable.',
			'unit'        => 'seconds',
			'features'	  => array('live_chat')
		),

		'techno_chatbot_timetocall' => array(
			'label'       => 'Best time to call',
			'type'        => 'checkbox',
			'section'     => 'behavior_section',
			'default'     => 1,
			'description' => 'Show & require \'Best time to call?\' question when getting client\'s phone number.',
		),

		'techno_chatbot_livechatgetname' => array(
			'label'       => 'Live chat get name',
			'type'        => 'checkbox',
			'section'     => 'behavior_section',
			'default'     => 1,
			'description' => 'Require name before transferring client to live chat.',
		),

		'techno_chatbot_openonload' => array(
			'label'       => 'Opened Chatbox',
			'type'        => 'checkbox',
			'section'     => 'behavior_section',
			'default'     => '',
			'description' => 'Open Chatbox on page load',
		),

		'techno_chatbot_customprompt' => array(
            'label'       => 'Custom Prompt',
            'type'        => 'textarea',
            'section'     => 'aibehavior_section',
            'rows'        => 2,
			'maxlength'	  => 500,
            'default'     => '',
            'description' => 'FOR ADMINISTRATIVE USE ONLY'
        ),

		'techno_chatbot_aipagechunk' => array(
			'label'       => 'AI Page Chunk Limit',
			'type'        => 'number',
			'section'     => 'aibehavior_section',
			'default'     => 2,
			'min'         => 1,
			'max'         => 3,
			'description' => 'Returned Page chunk by AI (Max of 3)',
		),

		'techno_chatbot_airelchunk' => array(
			'label'       => 'AI Relevant Chunk Limit',
			'type'        => 'number',
			'section'     => 'aibehavior_section',
			'default'     => 5,
			'min'         => 1,
			'max'         => 10,
			'description' => 'Returned Relevant chunk by AI (Max of 10)',
		),

		'techno_chatbot_aithreshold' => array(
			'label'       => 'AI Similarity Threshold Multiplier',
			'type'        => 'number',
			'section'     => 'aibehavior_section',
			'default'     => 0.80,
			'min'         => 0.01,
			'max'         => 1.00,
			'step'        => '0.01',
			'description' => 'Multiplier applied to best match similarity score (Default: 0.80)',
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

			if ( $data['type'] === 'number' ) {
				$sanitize_callback = array( $this, 'sanitize_number' );
			} elseif ( $data['type'] === 'textarea' ) {
				$sanitize_callback = array( $this, 'sanitize_textarea' );
			} elseif ( $data['type'] === 'checkbox' ) {
				$sanitize_callback = array( $this, 'sanitize_checkbox' );
			} else {
				$sanitize_callback = 'sanitize_text_field';
			}

			register_setting(
				'techno_chatbot_behaviors_group',
				$option,
				array(
					'sanitize_callback' => $sanitize_callback,
					'default'           => $data['default'],
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
					'min'         => $data['min'] ?? '',
					'step'        => $data['step'] ?? '',
					'unit'        => $data['unit'] ?? '',
					'disabled' 	  => $data['disabled'] ?? '',
					'options' 	  => $data['options'] ?? array(),
					'features'	  => $data['features'] ?? '',
					'rows'        => $data['rows'] ?? 4,
					'maxlength'   => $data['maxlength'] ?? 500,
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
		$features	 = $args['features'];
		$placeholder = $args['placeholder'];
		$min         = $args['min'];
		$unit        = $args['unit'];
		$rows        = $args['rows'];
		$maxlength   = $args['maxlength'];
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
		} elseif ( $type === 'number' ) {
			$step = isset( $args['step'] ) ? 'step="' . esc_attr( $args['step'] ) . '"' : '';
			echo '<input '.$disabled.'
					type="number"
					name="' . esc_attr( $option ) . '"
					value="' . esc_attr( $value ) . '"
					min="' . esc_attr( $min ) . '"
					' . $step . '
					style="width:100px;"
				/>';
			if ( ! empty( $unit ) ) {
				echo ' <span style="color:#666;">' . esc_html( $unit ) . '</span>';
			}
		} elseif ( in_array($type, ['text','date','time','datetime-local']) ) {
			echo '<input '.$disabled.'
					type="' . esc_attr($type) . '"
					name="' . esc_attr( $option ) . '"
					value="' . esc_attr( $value ) . '"
					class="regular-text"
					style="width:100%;"
					placeholder="' . esc_attr( $placeholder ) . '"
				/>';
		} elseif ( $type === 'select' ) {
			echo '<select name="' . esc_attr($option) . '" '.$disabled.'>';
			foreach ($args['options'] as $opt) {

				if( !empty($opt['features']) ){
					$optplans = techno_chatbot_feature($opt['features']);
					$opt_disabled = $optplans['allowed'] == false ? 'disabled' : '';
					$disabledmsg = $optplans['message'];
				}else{
					$optplans = null;
					$opt_disabled = (!empty($opt['disabled'])) ? 'disabled' : '';
					$disabledmsg = '';
				}

				echo '<option value="' . esc_attr($opt['value']) . '" 
						' . selected($value, $opt['value'], false) . ' 
						' . $opt_disabled . '>
						' . esc_html($opt['label']) . '
					</option>';
			}
			echo '</select>';
		} elseif ( $type === 'textarea' ) {
            echo '<textarea name="' . esc_attr( $option ) . '" maxlength="'. esc_attr( $maxlength ) .'" rows="' . esc_attr( $rows ) . '" style="width:100%;" placeholder="' . esc_attr( $placeholder ) . '">' . esc_textarea( $value ) . '</textarea>';
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
     * Sanitize textarea input
	 * 
	 * @since    1.2.6
     */
    public function sanitize_textarea( $input ) {
        return wp_kses_post( $input );
    }

	/**
	 * Sanitize and enforce min/max bounds for float/number inputs
	 *
	 * @since    1.2.6
	 */
	public function sanitize_number( $value ) {
		// Allow blank values to pass through if submitted empty
		if ( '' === trim( $value ) ) {
			return '';
		}

		$val = floatval( $value );

		// Look up constraints from field definitions
		foreach ( self::$fields as $key => $data ) {
			if ( isset( $_POST[$key] ) ) {
				if ( isset( $data['min'] ) && '' !== $data['min'] && $val < floatval( $data['min'] ) ) {
					return floatval( $data['min'] );
				}
				if ( isset( $data['max'] ) && '' !== $data['max'] && $val > floatval( $data['max'] ) ) {
					return floatval( $data['max'] );
				}
				return $val;
			}
		}

		return $val;
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