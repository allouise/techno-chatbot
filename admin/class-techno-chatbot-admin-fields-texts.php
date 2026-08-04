<?php

/**
 * The admin settings field - messages/texts
 *
 * @link       https://technodreamwebdesign.com/techno-chatbot/
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
        'intent_answer_section' => array(
            'title' => 'Intent Answers',
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
            'default'     => 'Chat Assistant',
            'placeholder' => 'Chat Assistant',
            'description' => 'Title displayed at the top of the chatbox.'
        ),

        'techno_chatbot_chaticontext' => array(
            'label'       => 'Chat Icon Text',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => "Let's Chat!",
            'placeholder' => "Let's Chat!",
            'description' => 'Text shown next to icon If Icon type is = "Icon & Text"'
        ),

        'techno_chatbot_icontext' => array(
            'label'       => 'Floating Text',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => '',
            'placeholder' => "Got questions? Let's Chat!",
            'description' => 'Text shown next to icon. Leave blank to disable.'
        ),

        'techno_chatbot_inputtext' => array(
            'label'       => 'Input text Default',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'Type your message...',
            'placeholder' => 'Type your message...',
            'description' => 'Chat Input placeholder.'
        ),

        'techno_chatbot_inputchoose' => array(
            'label'       => 'Input text Choosing',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'Choose an option...',
            'placeholder' => 'Choose an option...',
            'description' => 'Chat Input placeholder when choosing an option.'
        ),

        'techno_chatbot_inputwait' => array(
            'label'       => 'Input text Waiting',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'Please wait...',
            'placeholder' => 'Please wait...',
            'description' => 'Chat Input placeholder when processing data.'
        ),

        'techno_chatbot_sendbtn' => array(
            'label'       => 'Send button text',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'Send',
            'placeholder' => 'Send',
            'description' => 'Send button text.'
        ),

        'techno_chatbot_menudisclaimer' => array(
            'label'       => 'Menu disclaimer',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'Disclaimer',
            'placeholder' => 'Disclaimer',
            'description' => 'Menu disclaimer button text.'
        ),

        'techno_chatbot_menulivechat' => array(
            'label'       => 'Menu livechat',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'Transfer me to a live agent.',
            'placeholder' => 'Transfer me to a live agent.',
            'description' => 'Menu livechat button text.'
        ),

        'techno_chatbot_menucall' => array(
            'label'       => 'Menu call',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'Call Me Back',
            'placeholder' => 'Call Me Back',
            'description' => 'Menu call button text.'
        ),

        'techno_chatbot_menuemail' => array(
            'label'       => 'Menu email',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'Email me',
            'placeholder' => 'Email me',
            'description' => 'Menu email button text.'
        ),

        'techno_chatbot_menureset' => array(
            'label'       => 'Menu reset',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'Start a new session',
            'placeholder' => 'Start a new session',
            'description' => 'Menu reset button text.'
        ),

        'techno_chatbot_menuhistorysend' => array(
            'label'       => 'Menu send history',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'Email Me the Transcript',
            'placeholder' => 'Email Me the Transcript',
            'description' => 'Menu send history button text.'
        ),

        'techno_chatbot_menuleave' => array(
            'label'       => 'Menu end',
            'type'        => 'text',
            'section'     => 'texts_section',
            'default'     => 'End Conversation',
            'placeholder' => 'End Conversation',
            'description' => 'Menu end button text.'
        ),

        // Intent Answers
        'techno_chatbot_greetings_intent_answer' => array(
            'label'       => 'Greetings Intent',
            'type'        => 'textarea',
            'section'     => 'intent_answer_section',
            'rows'        => 2,
            'default'     => 'Hi there! How can I help you today?',
            'description' => ''
        ),

        'techno_chatbot_generichelp_intent_answer' => array(
            'label'       => 'Generic Help Intent',
            'type'        => 'textarea',
            'section'     => 'intent_answer_section',
            'rows'        => 2,
            'default'     => "I'd be happy to help! What would you like to ask about?",
            'description' => ''
        ),

        // Messages
        'techno_chatbot_disclaimermsg' => array(
            'label'       => 'Disclaimer message',
            'type'        => 'text',
            'section'     => 'messages_section',
            'default'     => 'Please do not share sensitive or confidential information.',
            'placeholder' => 'Please do not share sensitive or confidential information.',
            'description' => 'Disclaimer message that will display in chatbox before welcome message.'
        ),

        'techno_chatbot_disclaimerfullmsg' => array(
            'label'       => 'Disclaimer popup message',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 4,
            'default'     => '<p><strong>Disclaimer:</strong></p>
<p>This chat tool is provided for general informational purposes only. For your security, please do not share sensitive or confidential information in this chat, including passwords, credit card details, PINs, or other personal data.</p>',
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
            'default'     => 'Best time to call?',
            'placeholder' => 'Best time to call?',
            'description' => ''
        ),

        'techno_chatbot_next_step' => array(
            'label'       => 'Next Step Message',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 4,
            'default'     => 'Please choose an option:',
            'description' => 'Default message sent when guest is sent next step'
        ),

        'techno_chatbot_no_answer_message' => array(
            'label'       => 'No Answer Default',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 4,
            'default'     => "I don't have the information you're looking for. Try rephrasing your question or asking something else.",
            'description' => 'Default message sent when the chatbot cannot find a proper answer.'
        ),

        'techno_chatbot_no_answer_message_final_default' => array(
            'label'       => 'Final No Answer Default',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 4,
            'default'     => "I'm sorry, I don't have the specifics on that at the moment. However, I can connect you with our team for better assistance. Please choose one of the options below:",
            'description' => 'Final message reply when the chatbot cannot find a proper answer then transfer to next option.'
        ),

        'techno_chatbot_offline_agents_message' => array(
            'label'       => 'Offline Agents',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 4,
            'default'     => "Sorry, all agents are currently busy. Please leave your contact details, and we'll get back to you as soon as possible.",
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

        'techno_chatbot_idle_guests_message' => array(
            'label'       => 'Guest Gets Idle',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 4,
            'default'     => 'Thank you for reaching out! Previous chat has been closed due to inactivity. If you still need assistance, simply start a new chat anytime. Our team will be happy to help you.',
            'description' => 'Message shown when guests gets disconnected while on live chat due to inactivity'
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
            'default'     => 'May I have your name, please?',
            'placeholder' => 'May I have your name, please?',
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

        'techno_chatbot_endchat' => array(
            'label'       => 'End Chat Message',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 4,
            'default'     => 'Thank you for chatting with us today! 😊, Would you like a copy of this conversation sent to your email?',
            'description' => 'Message sent to guests when you end the chat'
        ),

        'techno_chatbot_askemail' => array(
            'label'       => 'Ask Email for History',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 2,
            'default'     => 'Please enter your email to receive a copy of this conversation.',
            'description' => 'Message sent to guests asking for email when you ended the chat'
        ),

        'techno_chatbot_historysent' => array(
            'label'       => 'History Sent Prompt',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 2,
            'default'     => 'A copy of the conversation has been sent successfully. Thank you for chatting with us!',
            'description' => 'Message shown after successful sending history to guest.'
        ),

        'techno_chatbot_endchatmsg' => array(
            'label'       => 'End Chat Message',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 2,
            'default'     => 'Thank you for chatting with us. Have a great day!',
            'description' => 'Message shown after ending live chat'
        ),

        'techno_chatbot_submissionspam_limit' => array(
            'label'       => 'Spam limitation warning',
            'type'        => 'textarea',
            'section'     => 'messages_section',
            'rows'        => 2,
            'default'     => 'Please wait a moment before submitting.',
            'description' => ''
        ),

        'techno_chatbot_invalid_email' => array(
            'label'       => 'Invalid Email',
            'type'        => 'text',
            'section'     => 'messages_section',
            'default'     => 'Please enter a valid email address.',
            'description' => ''
        ),

        'techno_chatbot_invalid_phone' => array(
            'label'       => 'Invalid Phone',
            'type'        => 'text',
            'section'     => 'messages_section',
            'default'     => 'Please enter a valid phone number.',
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
	 * Helper to get available target languages (e.g. Spanish, French, etc.)
     * 
	 * @since    1.1.6
	 */
	public static function get_active_languages() {
        $saved_languages = get_option( 'techno_chatbot_active_languages', array() );
        return apply_filters( 'techno_chatbot_active_languages', $saved_languages );
    }

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

        $this->save_translations();
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
            echo '<textarea name="' . esc_attr( $option ) . '" rows="' . esc_attr( $rows ) . '" style="width:100%;" placeholder="' . esc_attr( $placeholder ) . '">' . esc_textarea( $value ) . '</textarea>';
        } else {
            echo '<input type="text" name="' . esc_attr( $option ) . '" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="' . esc_attr( $placeholder ) . '"/>';
        }

        if ( ! empty( $description ) ) {
            echo '<p class="description">' . esc_html__( $description, 'techno-chatbot' ) . '</p>';
        }

        $active_langs = self::get_active_languages();
		if ( ! empty( $active_langs ) ) {
			$translations = self::get_translations_for_key( $option );

			echo '<div class="techno-chatbot-translations-block" style="margin-top: 10px; padding-left: 15px; border-left: 3px solid #2271b1;">';
			echo '<p style="font-weight:600; margin-bottom:5px;">' . esc_html__( 'Translations:', 'techno-chatbot' ) . '</p>';

			foreach ( $active_langs as $code => $lang_label ) {
				$trans_val = $translations[ $code ] ?? '';
				$input_name = 'techno_chatbot_trans[' . esc_attr( $option ) . '][' . esc_attr( $code ) . ']';

				echo '<div style="margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">';
				echo '<span style="min-width: 70px; font-size: 12px; color: #50575e;">' . esc_html( $lang_label ) . ' (' . esc_html( $code ) . '):</span>';

				if ( $type === 'textarea' ) {
					echo '<textarea name="' . $input_name . '" rows="2" style="width:100%; font-size: 13px;" placeholder="' . esc_attr( $placeholder ) . '">' . esc_textarea( $trans_val ) . '</textarea>';
				} else {
					echo '<input type="text" name="' . $input_name . '" value="' . esc_attr( $trans_val ) . '" class="regular-text" style="font-size: 13px;" placeholder="' . esc_attr( $placeholder ) . '" />';
				}
				echo '</div>';
			}
			echo '</div>';
		}
    }

    /**
	 * Save dynamic translations to custom table
	 */
	private function save_translations() {
		if ( ! isset( $_POST['techno_chatbot_trans'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'techno_cb_translations';
		$translations_data = $_POST['techno_chatbot_trans'];

		if ( is_array( $translations_data ) ) {
			foreach ( $translations_data as $option_key => $lang_values ) {

				// Ensure option key is valid
				if ( ! isset( self::$fields[ $option_key ] ) ) {
					continue;
				}

				$type = self::$fields[ $option_key ]['type'];

				foreach ( $lang_values as $lang_code => $raw_value ) {
					$clean_value = ( $type === 'textarea' ) ? wp_kses_post( $raw_value ) : sanitize_text_field( $raw_value );

					if ( $clean_value !== '' ) {
						$wpdb->replace(
							$table,
							array(
								'lang_code'    => sanitize_text_field( $lang_code ),
								'option_key'   => sanitize_text_field( $option_key ),
								'option_value' => $clean_value,
							),
							array( '%s', '%s', '%s' )
						);
					} else {
						// Delete entry if left blank
						$wpdb->delete(
							$table,
							array(
								'lang_code'  => sanitize_text_field( $lang_code ),
								'option_key' => sanitize_text_field( $option_key ),
							),
							array( '%s', '%s' )
						);
					}
				}
			}
		}
	}

    /**
	 * Retrieve saved translations for a specific option key
	 */
	public static function get_translations_for_key( $key ) {
		global $wpdb;
		$table = $wpdb->prefix . 'techno_cb_translations';

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT lang_code, option_value FROM {$table} WHERE option_key = %s",
				$key
			),
			ARRAY_A
		);

		$translations = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $row ) {
				$translations[ $row['lang_code'] ] = $row['option_value'];
			}
		}

		return $translations;
	}

    /**
     * Sanitize textarea input
     */
    public function sanitize_textarea( $input ) {
        return wp_kses_post( $input );
    }

    /**
	 * Get field values for other class
	 *
	 * @since    1.0.0
	 */
    public static function get_value( $key, $lang_code = 'en' ) {
        if ( empty( $lang_code ) || 'en' === $lang_code ) {
            $default = self::$fields[ $key ]['default'] ?? '';
            $value   = get_option( $key, $default );
            return ( '' !== $default && '' === $value ) ? $default : $value;
        }

        static $translations = array();
        if ( ! isset( $translations[ $lang_code ] ) ) {
            global $wpdb;
            $table = $wpdb->prefix . 'techno_cb_translations';
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT option_key, option_value FROM %i WHERE lang_code = %s",
                    $table,
                    $lang_code
                ),
                OBJECT_K
            );

            $translations[ $lang_code ] = array();
            if ( ! empty( $results ) ) {
                foreach ( $results as $row_key => $row ) {
                    $translations[ $lang_code ][ $row_key ] = $row->option_value;
                }
            }
        }

        if ( ! empty( $translations[ $lang_code ][ $key ] ) ) {
            return $translations[ $lang_code ][ $key ];
        }

        return self::get_value( $key, 'en' );
    }
    
}