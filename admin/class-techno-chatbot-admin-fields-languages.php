<?php

/**
 * The admin settings field - languages
 *
 * @link       https://technodreamwebdesign.com/techno-chatbot/
 * @since      1.1.6
 *
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/admin
 */

/**
 * The admin settings field - languages
 *
 * @since      1.1.6
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/admin
 * @author     Technodream <al.esilverconnect@gmail.com>
 */
class Techno_Chatbot_Admin_Fields_Languages {

    /**
	 * The ID of this plugin.
	 *
	 * @since    1.1.6
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.1.6
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.1.6
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
	 * @since    1.1.6
	 */
    private $sections = array(
        'languages_section' => array(
            'title' => 'Languages',
        )
    );

     /**
	 * Register fields
	 *
	 * @since    1.1.6
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

        register_setting(
            'techno_chatbot_languages_group',
            'techno_chatbot_active_languages',
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize_languages' ),
                'default'           => array(),
            )
        );

        add_settings_field(
            'techno_chatbot_active_languages',
            __( 'Secondary Languages', 'techno-chatbot' ),
            array( $this, 'render_field' ),
            $page_slug,
            'languages_section',
            array(
                'features' => array( 'multi_lang' ),
            )
        );
    }

    /**
     * Render Fields
     *
     * @since    1.1.6
     */
    public function render_field( $args ) {
        $features = $args['features'];
		$disabled = '';
        if( $features ){
			$plans = techno_chatbot_feature($features);
			$disabled = $plans['allowed'] == false ? 'disabled' : $disabled;
			$disabledmsg = $plans['message'];
		}

        $languages = get_option( 'techno_chatbot_active_languages', array() );
        $supported_languages = defined( 'TECHNO_CHATBOT_SUPPORTED_LANGUAGE' ) ? TECHNO_CHATBOT_SUPPORTED_LANGUAGE : array();
        ?>
        <p class="description" style="margin-bottom: 12px;">
            <?php esc_html_e( 'Select secondary languages enabled for your chatbot from the supported list.', 'techno-chatbot' ); ?>
        </p>

        <table class="widefat striped" id="td-chatbot-languages-table" style="max-width: 600px; margin-bottom: 12px;">
            <thead>
                <tr>
                    <th style="text-align:center; "><?php esc_html_e( 'Language', 'techno-chatbot' ); ?></th>
                    <th style="width: 25%; text-align: center;"><?php esc_html_e( 'Language Code', 'techno-chatbot' ); ?></th>
                    <th style="width: 15%; text-align: center;"><?php esc_html_e( 'Action', 'techno-chatbot' ); ?></th>
                </tr>
            </thead>
            <tbody id="td-chatbot-languages-body">
                <?php
                if ( ! empty( $languages ) && is_array( $languages ) ) :
                    foreach ( $languages as $code => $name ) :
                        ?>
                        <tr>
                            <td>
                                <select <?php echo $disabled; ?> name="techno_chatbot_active_languages[name][]" class="techno-lang-select regular-text" style="width: 100%;" required>
                                    <option value=""><?php esc_html_e( '-- Select Language --', 'techno-chatbot' ); ?></option>
                                    <?php foreach ( $supported_languages as $lang_name => $lang_code ) : ?>
                                        <option value="<?php echo esc_attr( $lang_name ); ?>" data-code="<?php echo esc_attr( $lang_code ); ?>" <?php selected( $name, $lang_name ); ?>>
                                            <?php echo esc_html( $lang_name ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input <?php echo $disabled; ?> type="text" name="techno_chatbot_active_languages[code][]" value="<?php echo esc_attr( $code ); ?>" class="techno-lang-code regular-text" style="width: 100%; text-align: center; background-color: #f0f0f1; text-transform: uppercase;" readonly required />
                            </td>
                            <td style="text-align: center;">
                                <button <?php echo $disabled; ?> type="button" class="button remove-lang-row" style="color: #b32d2e; border-color: #b32d2e;">&times;</button>
                            </td>
                        </tr>
                        <?php
                    endforeach;
                endif;
                ?>
            </tbody>
        </table>

        <?php 
        if ( $disabledmsg ) {
			techno_chatbot_msgformat($disabledmsg);
		}

        if ( $disabled == '' ) {
        ?>
            <button type="button" class="button button-secondary" id="add-lang-row">
                + <?php esc_html_e( 'Add Language', 'techno-chatbot' ); ?>
            </button>

            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    var languageOptionsHtml = '<option value=""><?php echo esc_js( __( '-- Select Language --', 'techno-chatbot' ) ); ?></option>';
                    <?php foreach ( $supported_languages as $lang_name => $lang_code ) : ?>
                        languageOptionsHtml += '<option value="<?php echo esc_js( $lang_name ); ?>" data-code="<?php echo esc_js( $lang_code ); ?>"><?php echo esc_html( $lang_name ); ?></option>';
                    <?php endforeach; ?>

                    $('#add-lang-row').on('click', function(e) {
                        e.preventDefault();
                        var newRow = `
                            <tr>
                                <td>
                                    <select name="techno_chatbot_active_languages[name][]" class="techno-lang-select regular-text" style="width: 100%;" required>
                                        ${languageOptionsHtml}
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="techno_chatbot_active_languages[code][]" value="" class="techno-lang-code regular-text" style="width: 100%; text-align: center; background-color: #f0f0f1; text-transform: uppercase;" readonly required />
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="button remove-lang-row" style="color: #b32d2e; border-color: #b32d2e;">&times;</button>
                                </td>
                            </tr>`;
                        $('#td-chatbot-languages-body').append(newRow);
                    });

                    $(document).on('change', '.techno-lang-select', function() {
                        var selectedOption = $(this).find('option:selected');
                        var code = selectedOption.data('code') || '';
                        $(this).closest('tr').find('.techno-lang-code').val(code);
                    });

                    $(document).on('click', '.remove-lang-row', function(e) {
                        e.preventDefault();
                        $(this).closest('tr').remove();
                    });
                });
            </script>
        <?php
        }
    }
    
    /**
	 * Sanitize submitted dynamic input array into key-value map [code => name]
     * 
     * @since    1.1.6
	 */
	public function sanitize_languages( $input ) {
        $sanitized = array();

        if ( is_array( $input ) ) {
            if ( isset( $input['code'] ) && isset( $input['name'] ) && is_array( $input['code'] ) && is_array( $input['name'] ) ) {
                $codes = array_values( $input['code'] );
                $names = array_values( $input['name'] );

                foreach ( $codes as $index => $code ) {
                    $clean_code = sanitize_text_field( $code );
                    $clean_name = isset( $names[ $index ] ) ? sanitize_text_field( $names[ $index ] ) : '';

                    if ( ! empty( $clean_code ) && ! empty( $clean_name ) ) {
                        $sanitized[ $clean_code ] = $clean_name;
                    }
                }
            } else {
                foreach ( $input as $code => $name ) {
                    if ( is_string( $code ) && is_string( $name ) ) {
                        $clean_code = sanitize_text_field( $code );
                        $clean_name = sanitize_text_field( $name );
                        if ( ! empty( $clean_code ) && ! empty( $clean_name ) ) {
                            $sanitized[ $clean_code ] = $clean_name;
                        }
                    }
                }
            }
        }

        return $sanitized;
    }
}