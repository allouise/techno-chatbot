<?php

/**
 * The admin settings field - messages/texts
 *
 * @link       https://technodreamwebdesign.com/
 * @since      1.0.0
 *
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/admin
 */

/**
 * The admin settings field - messages/texts
 *
 * @since      1.0.0
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/admin
 * @author     Technodream <al.esilverconnect@gmail.com>
 */
class Techno_Chatbot_Admin_Fields_Texts {

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
        'texts_section' => array(
            'title' => 'Texts',
        ),
        'messages_section' => array(
            'title' => 'Messages',
        ),
    );

    /**
	 * Define Fields
	 *
	 * @since    1.0.0
	 */
    private static $fields = array(
        // Texts
        'techno_chatbot_header' => array(
            'label'       => 'Chatbox Header',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'Chat Support',
            'placeholder' => 'Chat Support',
            'description' => 'Title displayed at the top of the chatbox.'
        ),

        'techno_chatbot_icontext' => array(
            'label'       => 'Floating Text',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'Chat with us',
            'placeholder' => 'Chat with us',
            'description' => 'Text shown next to icon. Leave blank to disable.'
        ),

        'techno_chatbot_inputtext' => array(
            'label'       => 'Input text',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'Type your message...',
            'placeholder' => 'Type your message...',
            'description' => 'Chat Input placeholder.'
        ),

        'techno_chatbot_sendbtn' => array(
            'label'       => 'Send button text',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'Send',
            'placeholder' => 'Send',
            'description' => 'Send button text.'
        ),

        'techno_chatbot_menulivechat' => array(
            'label'       => 'Menu livechat',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'Transfer to live chat',
            'placeholder' => 'Transfer to live chat',
            'description' => 'Menu livechat button text.'
        ),

        'techno_chatbot_menucall' => array(
            'label'       => 'Menu call',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'Request for a call',
            'placeholder' => 'Request for a call',
            'description' => 'Menu call button text.'
        ),

        'techno_chatbot_menuemail' => array(
            'label'       => 'Menu email',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'Send me an email',
            'placeholder' => 'Send me an email',
            'description' => 'Menu email button text.'
        ),

        'techno_chatbot_menureset' => array(
            'label'       => 'Menu reset',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'Restart',
            'placeholder' => 'Restart',
            'description' => 'Menu reset button text.'
        ),

        // Messages
        'techno_chatbot_disclaimermsg' => array(
            'label'       => 'Disclaimer message',
            'type'        => 'text',
            'section'     => 'messages_section',
            'default'     => 'Do not provide sensitive information.',
            'placeholder' => 'Do not provide sensitive information.',
            'description' => 'Disclaimer message that will display in chatbox before welcome message.'
        ),

        'techno_chatbot_disclaimerfullmsg' => array(
            'label'       => 'Disclaimer popup message',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 4,
            'default'     => '<p><strong>Disclaimer:</strong></p>
<p>This plugin is provided for general use only. Do not share or submit any sensitive or confidential information, including but not limited to credit card details, passwords, personal identification numbers (PINs), or private personal data. You are responsible for ensuring that any information you provide is safe and appropriate.</p>',
            'description' => 'Disclaimer popup message'
        ),

        'techno_chatbot_welcomemsg' => array(
            'label'       => 'Welcome message',
            'type'        => 'text',
            'section'     => 'messages_section',
            'default'     => 'Hi! how can I help you?',
            'placeholder' => 'Hi! how can I help you?',
            'description' => ''
        ),

        'techno_chatbot_timetocall_txt' => array(
            'label'       => 'Best time to call',
            'type'        => 'text',
            'section'     => 'messages_section',
            'default'     => 'What is the best time to call you?',
            'placeholder' => 'What is the best time to call you?',
            'description' => ''
        ),

        'techno_chatbot_no_answer_message' => array(
            'label'       => 'No Answer Default',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 4,
            'default'     => 'I do not have the specific information for your question, please try asking different question.',
            'description' => 'Default message sent when the chatbot cannot find a proper answer.'
        ),

        'techno_chatbot_no_answer_message_final_default' => array(
            'label'       => 'Final No Answer Default',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 4,
            'default'     => 'I do not have the specific information for your question, the best thing I can do is have one of our representative reach you, please provide the best way to contact you.',
            'description' => 'Final message reply when the chatbot cannot find a proper answer then transfer to next option.'
        ),

        'techno_chatbot_offline_agents_message' => array(
            'label'       => 'Offline Agents',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 4,
            'default'     => 'Sorry all agents are busy right now — please provide the best way to contact you.',
            'description' => 'Message shown when all agents are offline.'
        ),

        'techno_chatbot_idle_agents_message' => array(
            'label'       => 'Agents Gets Idle',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 4,
            'default'     => 'It appears that our agent got disconnected — please provide the best way to contact you.',
            'description' => 'Message shown when agents gets disconnected while on live chat'
        ),

        'techno_chatbot_cphoneLabel' => array(
            'label'       => 'Contact Option Phone',
            'type'        => 'text',
            'section'     => 'messages_section',
            'default'     => 'What is your Phone Number?',
            'placeholder' => 'What is your Phone Number?',
            'description' => ''
        ),

        'techno_chatbot_cemailLabel' => array(
            'label'       => 'Contact Option Email',
            'type'        => 'text',
            'section'     => 'messages_section',
            'default'     => 'What is your Email Address?',
            'placeholder' => 'What is your Email Address?',
            'description' => ''
        ),

        'techno_chatbot_getname' => array(
            'label'       => 'Live Chat Get Name',
            'type'        => 'text',
            'section'     => 'messages_section',
            'default'     => 'Can I get your name?',
            'placeholder' => 'Can I get your name?',
            'description' => ''
        ),

        'techno_chatbot_transferred_live_message' => array(
            'label'       => 'Transferred to Live',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 2,
            'default'     => 'You are now transferred to a live agent.',
            'description' => 'Message shown when a user is transferred to a live agent.'
        ),

        'techno_chatbot_getcontact_finish' => array(
            'label'       => 'Contact Received',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 2,
            'default'     => 'Thank you for that information. We will contact you soon!',
            'description' => 'Message shown after successful getting of contact information.'
        ),

        'techno_chatbot_submissionspam_limit' => array(
            'label'       => 'Spam limitation warning',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 2,
            'default'     => 'Please wait a moment before submitting.',
            'description' => ''
        ),

        'techno_chatbot_error' => array(
            'label'       => 'Error message',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 2,
            'default'     => 'Something went wrong. Please try again later.',
            'description' => ''
        ),

        'techno_chatbot_criticalerror' => array(
            'label'       => 'Critical Error Message',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 2,
            'default'     => 'Something went wrong. Please try again. If problem persists contact Administrator.',
            'description' => ''
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
                'techno_chatbot_texts_group',
                $option,
                array(
                    'sanitize_callback' => $data['type'] === 'textarea'
                        ? array( $this, 'sanitize_textarea' )
                        : 'sanitize_text_field',
                    'default' => __( $data['default'], 'techno-chatbot' )
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
                    'default'     => __( $data['default'], 'techno-chatbot' ),
                    'description' => $data['description'] ?? '',
                    'placeholder' => $data['placeholder'] ?? '',
                    'rows'        => $data['rows'] ?? 4,
                )
            );
        }
    }

    /**
     * Render Fields
     */
    public function render_field( $args ) {

        $option      = $args['option_name'];
        $type        = $args['type'];
        $default     = $args['default'];
        $description = $args['description'];
        $placeholder = $args['placeholder'];
        $rows        = $args['rows'];
        $value       = get_option( $option, $default );
        $value		 = ( $default !== '' && $value === '' ) ? $default : $value;

        if ( $type === 'textarea' ) {

            echo '<textarea 
                    name="' . esc_attr( $option ) . '" 
                    rows="' . esc_attr( $rows ) . '" 
                    style="width:100%;"
                    placeholder="' . esc_attr( $placeholder ) . '"
                >' . esc_textarea( $value ) . '</textarea>';

        } else {

            echo '<input 
                    type="text"
                    name="' . esc_attr( $option ) . '"
                    value="' . esc_attr( $value ) . '"
                    class="regular-text"
                    placeholder="' . esc_attr( $placeholder ) . '"
                />';
        }

        if ( ! empty( $description ) ) {

            echo '<p class="description">'
                . esc_html__( $description, 'techno-chatbot' ) .
            '</p>';
        }
    }

    /**
     * Sanitize textarea input
     */
    public function sanitize_textarea( $input ) {
        return wp_kses_post( $input ); // allows safe HTML
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