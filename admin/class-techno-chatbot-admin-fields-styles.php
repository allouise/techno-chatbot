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
class Techno_Chatbot_Admin_Fields_Styles {

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
     * Define sections
     */
    private $sections = array(
		'layout_section' => array(
			'title' => 'Styles & Layout',
		),
		'font_size_section' => array(
			'title' => 'Font Size',
		),
		'colors_section' => array(
			'title' => 'Colors',
		),
	);

    /**
     * Define fields
     */
    private static $fields = array(

		// Layout
		'techno_chatbot_icon' => array(
			'label'=>'Chatbot Icon','type'=>'icon','section'=>'layout_section','default'=>''
		),
		'techno_chatbot_iconsize' => array(
			'label'=>'Icon Size','type'=>'select','section'=>'layout_section','default'=>'contain',
			'options'=>array(
				array('label'=>'Contain','value'=>'contain'),
				array('label'=>'Cover','value'=>'cover'),
			)
		),
		'techno_chatbot_position' => array(
			'label'=>'Position','type'=>'select','section'=>'layout_section','default'=>'bottom right',
			'options'=>array(
				array('label'=>'Upper Left','value'=>'upper left'),
				array('label'=>'Top Center','value'=>'top center'),
				array('label'=>'Upper Right','value'=>'upper right'),
				array('label'=>'Left','value'=>'left'),
				array('label'=>'Right','value'=>'right'),
				array('label'=>'Bottom Left','value'=>'bottom left'),
				array('label'=>'Bottom Center','value'=>'bottom center'),
				array('label'=>'Bottom Right','value'=>'bottom right'),
			)
		),
		'techno_chatbot_offset_y' => array(
			'label'=>'Offset Y','type'=>'number','section'=>'layout_section','default'=>20,'min'=>0,'step'=>0.1,'unit'=>'px'
		),
		'techno_chatbot_offset_x' => array(
			'label'=>'Offset X','type'=>'number','section'=>'layout_section','default'=>20,'min'=>0,'step'=>0.1,'unit'=>'px'
		),
		'techno_chatbot_distance' => array(
			'label'=>'Chatbox Distance','type'=>'number','section'=>'layout_section','default'=>0,'min'=>0,'step'=>0.1,'unit'=>'px'
		),
		'techno_chatbot_height' => array(
			'label'=>'Chatbox Height','type'=>'number','section'=>'layout_section','default'=>420,'min'=>100,'unit'=>'px'
		),
		'techno_chatbot_icon_height' => array(
			'label'=>'Icon Height','type'=>'number','section'=>'layout_section','default'=>60,'min'=>10,'unit'=>'px'
		),
		'techno_chatbot_icon_width' => array(
			'label'=>'Icon Width','type'=>'number','section'=>'layout_section','default'=>60,'min'=>10,'unit'=>'px'
		),
		'techno_chatbot_zindex' => array(
			'label'=>'Z-Index','type'=>'number','section'=>'layout_section','default'=>5,'min'=>0
		),

		// Font sizes
		'techno_chatbot_heading_size' => array(
			'label'=>'Heading Size','type'=>'number','section'=>'font_size_section','default'=>20,'unit'=>'px'
		),
		'techno_chatbot_chatmsg_size' => array(
			'label'=>'Chat Message Size','type'=>'number','section'=>'font_size_section','default'=>15,'unit'=>'px'
		),
		'techno_chatbot_inputtxt_size' => array(
			'label'=>'Input Text Size','type'=>'number','section'=>'font_size_section','default'=>15,'unit'=>'px'
		),
		'techno_chatbot_sendbtn_size' => array(
			'label'=>'Send Button Size','type'=>'number','section'=>'font_size_section','default'=>15,'unit'=>'px'
		),
		'techno_chatbot_floatingtxt_size' => array(
			'label'=>'Floating Text Size','type'=>'number','section'=>'font_size_section','default'=>14,'unit'=>'px'
		),

		// Colors
		'techno_chaticon_bg_color'=>array('label'=>'Chat Icon BG','type'=>'color','section'=>'colors_section','default'=>'#0073aa'),
		'techno_chaticon_text_color'=>array('label'=>'Chat Icon Text','type'=>'color','section'=>'colors_section','default'=>'#ffffff'),
		'techno_floatingtxt_bg_color'=>array('label'=>'Floating BG','type'=>'color','section'=>'colors_section','default'=>'#0073aa'),
		'techno_floatingtxt_text_color'=>array('label'=>'Floating Text','type'=>'color','section'=>'colors_section','default'=>'#ffffff'),
		'techno_header_bg_color'=>array('label'=>'Header BG','type'=>'color','section'=>'colors_section','default'=>'#0073aa'),
		'techno_header_text_color'=>array('label'=>'Header Text','type'=>'color','section'=>'colors_section','default'=>'#ffffff'),
		'techno_chatbox_bg_color'=>array('label'=>'Chatbox BG','type'=>'color','section'=>'colors_section','default'=>'#ffffff'),
		'techno_admin_bubble_bg_color'=>array('label'=>'Admin Bubble BG','type'=>'color','section'=>'colors_section','default'=>'#f1f1f1'),
		'techno_admin_bubble_text_color'=>array('label'=>'Admin Bubble Text','type'=>'color','section'=>'colors_section','default'=>'#333'),
		'techno_visitor_bubble_bg_color'=>array('label'=>'Visitor Bubble BG','type'=>'color','section'=>'colors_section','default'=>'#0073aa'),
		'techno_visitor_bubble_text_color'=>array('label'=>'Visitor Bubble Text','type'=>'color','section'=>'colors_section','default'=>'#fff'),
		'techno_input_bg'=>array('label'=>'Input BG','type'=>'color','section'=>'colors_section','default'=>'#fff'),
		'techno_input_txt'=>array('label'=>'Input Text','type'=>'color','section'=>'colors_section','default'=>'#333'),
		'techno_optionbtn_bg'=>array('label'=>'Option Button BG','type'=>'color','section'=>'colors_section','default'=>'#bababa'),
		'techno_optionbtn_txt'=>array('label'=>'Option Button Text','type'=>'color','section'=>'colors_section','default'=>'#000'),
		'techno_sendbtn_bg'=>array('label'=>'Send Button BG','type'=>'color','section'=>'colors_section','default'=>'#0073aa'),
		'techno_sendbtn_txt'=>array('label'=>'Send Button Text','type'=>'color','section'=>'colors_section','default'=>'#fff'),
		'techno_disclaimerbg'=>array('label'=>'Disclaimer Popup BG','type'=>'color','section'=>'colors_section','default'=>'#fff'),
		'techno_disclaimertxt'=>array('label'=>'Disclaimer Popup Text','type'=>'color','section'=>'colors_section','default'=>'#000'),
		'techno_disclaimeroverlay'=>array('label'=>'Disclaimer Popup Overlay','type'=>'color','section'=>'colors_section','default'=>'rgba(0,0,0,0.7)'),

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

			// Sanitize
			$sanitize = 'sanitize_text_field';

			if ( $data['type'] === 'number' ) {
				$sanitize = array( $this, 'sanitize_number' );
			} elseif ( $data['type'] === 'color' ) {
				$sanitize = array( $this, 'sanitize_rgba_color' );
			} elseif ( $data['type'] === 'icon' ) {
				$sanitize = 'esc_url_raw';
			}

			register_setting(
				'techno_chatbot_styles_group',
				$option,
				array(
					'sanitize_callback' => $sanitize,
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
					'options'     => $data['options'] ?? array(),
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
		$value       = get_option( $option, $default );
        $value		 = ( $default !== '' && $value === '' ) ? $default : $value;
		$description = $args['description'];
		$placeholder = $args['placeholder'];
		$min         = $args['min'];
		$unit        = $args['unit'];

		// NUMBER
		if ( $type === 'number' ) {

			echo '<input type="number"
					name="' . esc_attr( $option ) . '"
					value="' . esc_attr( $value ) . '"
					min="' . esc_attr( $min ) . '"
					style="width:100px;"
				/>';
			if ( $unit ) {
				echo ' <span>' . esc_html( $unit ) . '</span>';
			}

		// SELECT
		} elseif ( $type === 'select' ) {

			echo '<select name="' . esc_attr($option) . '">';
			foreach ( $args['options'] as $opt ) {
				echo '<option value="' . esc_attr($opt['value']) . '" ' .
					selected($value, $opt['value'], false) . '>' .
					esc_html($opt['label']) .
				'</option>';
			}
			echo '</select>';

		// COLOR
		} elseif ( $type === 'color' ) {

			echo '<input type="text"
					name="' . esc_attr($option) . '"
					value="' . esc_attr($value) . '"
					class="techno-color-field"
					data-alpha-enabled="true"
				/>';

		// ICON
		} elseif ( $type === 'icon' ) {

			echo '<input type="text" name="' . esc_attr($option) . '" value="' . esc_attr($value) . '" style="width:60%;" />';
			echo '<button type="button" class="button techno-upload-button">Select Icon</button>';

			if ( $value ) {
				echo '<div><img src="' . esc_url($value) . '" style="max-width:80px;"></div>';
			}

		// TEXT DEFAULT
		} else {

			echo '<input type="text"
					name="' . esc_attr($option) . '"
					value="' . esc_attr($value) . '"
					class="regular-text"
					placeholder="' . esc_attr($placeholder) . '"
				/>';

		}

		if ( ! empty( $description ) ) {
			echo '<p class="description">' . esc_html($description) . '</p>';
		}
	}

    /**
	 * Sanitize number
	 */
	public function sanitize_number( $value ) {
		return is_numeric( $value ) ? $value : 0;
	}

	/**
	 * Sanitize RGBA field
	 *
	 * @since    1.0.0
	 */
	public function sanitize_rgba_color( $color ) {

		if ( empty( $color ) ) {
			return '';
		}

		// HEX fallback
		if ( false === strpos( $color, 'rgba' ) && false === strpos( $color, 'rgb' ) ) {
			return sanitize_hex_color( $color );
		}

		// Match rgba or rgb
		if ( preg_match( '/rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})(?:\s*,\s*(0|1|0?\.\d+))?\s*\)/', $color, $matches ) ) {
			return $matches[0];
		}

		return '';
	}

	/**
	 * Get field values for other class
	 *
	 * @since    1.0.0
	 */
    public static function get_value( $key ) {
		$default = self::$fields[$key]['default'] ?? '';
        $value = get_option( $key, $default );
		return ( $default !== '' && $value === '' ) ? $default : $value;
    }
}