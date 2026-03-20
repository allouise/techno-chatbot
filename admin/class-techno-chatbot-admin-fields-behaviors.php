<?php

/**
 * The admin settings field - styles
 *
 * @link       https://technodreamwebdesign.com/
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
			'title' => 'Behavior Settings',
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
			'default'     => 2,
			'min'         => 1,
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
			'default'     => '',
			'description' => 'Show & require \'Best time to call?\' question when getting client\'s phone number.',
		),

		'techno_chatbot_transfer_next_step' => array(
			'label'       => 'Transfer / Next Step',
			'type'        => 'select',
			'section'     => 'behavior_section',
			'default'     => 0,
			'description' => 'What should happen when no answer trigger is reached.',
			'options'     => array(
				array(
					'label' => 'Call/Email',
					'value' => 0,
				),
				array(
					'label' => 'Default Reply',
					'value' => 1,
				),
				array(
					'label' => 'Livechat',
					'value' => 2,
					'features' => array('live_chat')
				),
			),
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
				'techno_chatbot_behaviors_group',
				$option,
				array(
					'sanitize_callback' => $data['type'] === 'checkbox'
						? array( $this, 'sanitize_checkbox' )
						: 'sanitize_text_field',
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
					'unit'        => $data['unit'] ?? '',
					'disabled' 	  => $data['disabled'] ?? '',
					'options' 	  => $data['options'] ?? array(),
					'features'	  => $data['features'] ?? '',
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
		$value       = get_option( $option, $default );
        $value       = ($default && empty($value))? $default : $value;
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
			echo '<input '.$disabled.'
					type="number"
					name="' . esc_attr( $option ) . '"
					value="' . esc_attr( $value ) . '"
					min="' . esc_attr( $min ) . '"
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
        return ($default && empty($value))? $default : $value;
    }

}