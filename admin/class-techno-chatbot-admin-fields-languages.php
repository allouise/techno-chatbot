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
        add_action( 'admin_init', array( $this, 'handle_clear_language_translations' ) );
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

        add_settings_field(
            'techno_chatbot_translation_stats',
            __( 'Translation Statistics', 'techno-chatbot' ),
            array( $this, 'render_translation_stats_field' ),
            $page_slug,
            'languages_section'
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
                                <button <?php echo $disabled; ?> type="button" title="delete" class="button remove-lang-row" style="color: #fff; background:#b32d2e; border-color: #b32d2e;">&times;</button>
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
                                    <button type="button" class="button remove-lang-row" style="color: #fff; background:#b32d2e; border-color: #b32d2e;">&times;</button>
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
     * Render Display-Only Translation Statistics Field
     *
     * @since    1.1.6
     */
    public function render_translation_stats_field() {
        global $wpdb;

        $table_name       = $wpdb->prefix . 'techno_cb_translations';
        $active_languages = get_option( 'techno_chatbot_active_languages', array() );
        if ( ! is_array( $active_languages ) ) {
            $active_languages = array();
        }

        // Fetch counts grouped by lang_code from the DB
        $counts = array();
        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
            $db_results = $wpdb->get_results(
                "SELECT LOWER(lang_code) as lang_code, COUNT(id) as total_translations 
                 FROM {$table_name} 
                 GROUP BY LOWER(lang_code)",
                ARRAY_A
            );

            if ( ! empty( $db_results ) ) {
                foreach ( $db_results as $row ) {
                    $counts[ $row['lang_code'] ] = intval( $row['total_translations'] );
                }
            }
        }

        // Combine active languages with any non-active languages that still have DB records
        $all_languages = array();

        // 1. Add active languages
        foreach ( $active_languages as $code => $name ) {
            $clean_code = strtolower( trim( $code ) );
            $all_languages[ $clean_code ] = $name;
        }

        // 2. Add inactive languages present in $counts
        foreach ( $counts as $code => $count ) {
            if ( $count > 0 && ! isset( $all_languages[ $code ] ) ) {
                // Label orphaned/removed languages clearly
                $all_languages[ $code ] = sprintf(
                    /* translators: %s: Language code */
                    __( 'Inactive (%s)', 'techno-chatbot' ),
                    strtoupper( $code )
                );
            }
        }

        if ( empty( $all_languages ) ) {
            echo '<p class="description">' . esc_html__( 'No active languages configured or recorded translations found.', 'techno-chatbot' ) . '</p>';
            return;
        }

        $nonce = wp_create_nonce( 'techno_cb_clear_translations' );
        ?>

        <p class="description" style="margin-bottom: 12px;">
            <?php esc_html_e( 'Overview of recorded translation strings stored per language.', 'techno-chatbot' ); ?>
        </p>

        <table class="widefat striped" style="max-width: 600px;">
            <thead>
                <tr>
                    <th style="width: 20%; text-align: center;"><?php esc_html_e( 'Language Code', 'techno-chatbot' ); ?></th>
                    <th style="text-align: left;"><?php esc_html_e( 'Language Name', 'techno-chatbot' ); ?></th>
                    <th style="width: 25%; text-align: center;"><?php esc_html_e( 'Translations', 'techno-chatbot' ); ?></th>
                    <th style="width: 25%; text-align: center;"><?php esc_html_e( 'Action', 'techno-chatbot' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $all_languages as $code => $name ) : 
                    $clean_code   = strtolower( trim( $code ) );
                    $count        = isset( $counts[ $clean_code ] ) ? $counts[ $clean_code ] : 0;
                    $current_page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : 'techno-chatbot-settings';
                    $current_tab  = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'languages';
                    $delete_url   = add_query_arg(
                        array(
                            'page'      => $current_page,
                            'tab'       => $current_tab,
                            'action'    => 'clear_cb_translations',
                            'lang_code' => $clean_code,
                            '_wpnonce'  => $nonce,
                        ),
                        admin_url( 'admin.php' )
                    );
                ?>
                    <tr>
                        <td style="text-align: center;"><code><?php echo esc_html( strtoupper( $clean_code ) ); ?></code></td>
                        <td><?php echo esc_html( $name ); ?></td>
                        <td style="text-align: center;">
                            <strong><?php echo number_format_i18n( $count ); ?></strong>
                        </td>
                        <td style="text-align: center;">
                            <?php if ( $count > 0 ) : ?>
                                <a href="<?php echo esc_url( $delete_url ); ?>" 
                                   class="button button-link-delete techno-clear-lang-btn"
                                   data-lang="<?php echo esc_attr( $name ); ?>"
                                   data-code="<?php echo esc_attr( strtoupper( $clean_code ) ); ?>"
                                   style="box-shadow: none;background: #b32d2e;color: #fff;text-decoration: none;border-color: #b32d2e;outline: none;"> ⚠️
                                    <?php esc_html_e( 'Clear Data', 'techno-chatbot' ); ?>
                                </a>
                            <?php else : ?>
                                <span style="color: #a7aaad;"><?php esc_html_e( 'Empty', 'techno-chatbot' ); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.techno-clear-lang-btn').on('click', function(e) {
                    var langName = $(this).data('lang');
                    var langCode = $(this).data('code');

                    var message = "<?php echo esc_js( __( 'Are you sure you want to delete all translations for', 'techno-chatbot' ) ); ?> " + langName + " (" + langCode + ")?\n\n<?php echo esc_js( __( 'This action is IRREVERSIBLE and will permanently delete all stored strings.', 'techno-chatbot' ) ); ?>";

                    if ( ! confirm( message ) ) {
                        e.preventDefault();
                    }
                });
            });
        </script>
        <?php
    }
    
    /**
     * Process deletion request for specific language translations
     *
     * @since    1.1.6
     */
    public function handle_clear_language_translations() {
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'clear_cb_translations' ) {

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( esc_html__( 'Unauthorized user.', 'techno-chatbot' ) );
            }

            if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'techno_cb_clear_translations' ) ) {
                wp_die( esc_html__( 'Security check failed.', 'techno-chatbot' ) );
            }

            $lang_code = isset( $_GET['lang_code'] ) ? sanitize_text_field( $_GET['lang_code'] ) : '';

            if ( ! empty( $lang_code ) ) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'techno_cb_translations';

                $deleted = $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM {$table_name} WHERE LOWER(lang_code) = %s",
                        strtolower( $lang_code )
                    )
                );

                // 1. Build a clean redirect URL back to the tab without action parameters
                $redirect_url = remove_query_arg( array( 'action', 'lang_code', '_wpnonce' ) );

                // 2. Add a status query arg so we can display a success notice after redirect
                if ( false !== $deleted ) {
                    $redirect_url = add_query_arg(
                        array(
                            'trans_cleared' => strtoupper( $lang_code ),
                        ),
                        $redirect_url
                    );
                }

                // 3. Perform redirect to clear URL parameters from browser history
                wp_safe_redirect( $redirect_url );
                exit;
            }
        }

        // Display admin notice after the clean redirect
        if ( isset( $_GET['trans_cleared'] ) ) {
            $cleared_code = sanitize_text_field( $_GET['trans_cleared'] );
            add_settings_error(
                'techno_chatbot_messages',
                'techno_chatbot_trans_cleared',
                sprintf( __( 'Successfully deleted translations for language code "%s".', 'techno-chatbot' ), $cleared_code ),
                'updated'
            );
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