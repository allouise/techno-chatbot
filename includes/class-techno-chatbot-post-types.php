<?php
/**
 * Register Custom Post Types
 *
 * @package Techno_Chatbot
 */

class Techno_Chatbot_Post_Types {
    
    /**
	 * CPT constructors
	 *
	 * @since    1.0.0
	 */
    public function __construct() {
        add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_gutenberg' ), 10, 2 );
        add_filter( 'enter_title_here', array( $this, 'change_title_placeholder' ), 10, 2 );
    }

    /**
	 * Disable Gutenburg for Chatbot CPTs
	 *
	 * @since    1.0.0
	 */
    public function disable_gutenberg( $use_block_editor, $post_type ) {
        if ( 'techno_chatbot_faq' === $post_type ) {
            return false;
        }
        return $use_block_editor;
    }

    /**
     * Change title placeholder for CPT
     */
    public function change_title_placeholder( $title, $post ) {

        if ( 'techno_chatbot_aidb' === $post->post_type ) {
            return __( 'Page Title', 'techno-chatbot' );
        }

        return $title;
    }

    /**
	 * Register CPTs
	 *
	 * @since    1.0.0
	 */
    public function register_post_types() {

        // FAQ
        register_post_type( 'techno_chatbot_faq', array(
            'labels'        => array(
                'name'          => __( 'Techno Chatbot FAQ', 'techno-chatbot' ),
                'singular_name' => __( 'FAQ', 'techno-chatbot' ),
                'menu_name'     => __( 'FAQ', 'techno-chatbot' ),
                'add_new_item'  => __( 'Add New FAQ', 'techno-chatbot' ),
                'edit_item'     => __( 'Edit FAQ', 'techno-chatbot' ),
                'not_found'     => __( 'No FAQ found', 'techno-chatbot' ),
            ),
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => false,
            'menu_icon'     => 'dashicons-editor-help',
            'supports'      => array( 'title' ),
            'rewrite'       => array( 'slug' => 'techno-chatbot-faq' ),
            'show_in_rest'  => true,
        ) );

        // AI Knowledge Source
        register_post_type( 'techno_chatbot_aidb', array(
            'labels'        => array(
                'name'          => __( 'Techno Chatbot AI Knowldegebase', 'techno-chatbot' ),
                'singular_name' => __( 'AI Knowldegebase', 'techno-chatbot' ),
                'menu_name'     => __( 'AI Knowldegebase', 'techno-chatbot' ),
                'add_new_item'  => __( 'Add AI Knowldegebase', 'techno-chatbot' ),
                'edit_item'     => __( 'Edit AI Knowldegebase', 'techno-chatbot' ),
                'not_found'     => __( 'No AI Knowldegebase found', 'techno-chatbot' ),
            ),
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => false,
            'menu_icon'     => 'dashicons-editor-help',
            'supports'      => array( 'title' ),
            'rewrite'       => array( 'slug' => 'techno-chatbot-aiknowledge' ),
            'show_in_rest'  => true,
        ) );
    }

    /**
	 * Register FAQ Tax: Category
	 *
	 * @since    1.0.0
	 */
    public function register_taxonomies() {

        $labels = array(
            'name'              => __( 'FAQ Categories', 'techno-chatbot' ),
            'singular_name'     => __( 'FAQ Category', 'techno-chatbot' ),
            'search_items'      => __( 'Search Categories', 'techno-chatbot' ),
            'all_items'         => __( 'All Categories', 'techno-chatbot' ),
            'edit_item'         => __( 'Edit Category', 'techno-chatbot' ),
            'update_item'       => __( 'Update Category', 'techno-chatbot' ),
            'add_new_item'      => __( 'Add New Category', 'techno-chatbot' ),
            'new_item_name'     => __( 'New Category Name', 'techno-chatbot' ),
            'menu_name'         => __( 'Categories', 'techno-chatbot' ),
        );

        register_taxonomy( 'techno_chatbot_faq_category', 'techno_chatbot_faq', array(
            'hierarchical' => true,
            'labels'       => $labels,
            'show_ui'      => true,
            'show_admin_column' => true,
            'public'       => false,
            'rewrite'      => false,
        ) );
    }

    /**
	 * Add CPT metabox
	 *
	 * @since    1.0.0
	 */
    public function add_meta_boxes() {
        
        //FAQ
        add_meta_box(
            'techno_chatbot_faq_meta',
            __( 'FAQ Details', 'techno-chatbot' ),
            array( $this, 'render_faq_meta_box' ),
            'techno_chatbot_faq',
            'normal',
            'default'
        );

        //AI Knowledgebase
        add_meta_box(
            'techno_chatbot_aidb_meta',
            __( 'AI Knowledge', 'techno-chatbot' ),
            array( $this, 'render_aidb_meta_box' ),
            'techno_chatbot_aidb',
            'normal',
            'default'
        );
    }

    /**
	 * Render CPT FAQ metabox
	 *
	 * @since    1.0.0
	 */
    public function render_faq_meta_box( $post ) {

        wp_nonce_field( 'techno_chatbot_faq_nonce', 'techno_chatbot_faq_nonce_field' );

        $possible_questions = get_post_meta( $post->ID, '_possible_questions', true );
        $answer             = get_post_meta( $post->ID, '_faq_answer', true );
        $priority           = get_post_meta( $post->ID, '_faq_priority', true );
        $secondary_langs    = get_option( 'techno_chatbot_active_languages', array() );

        // Fetch existing translations from custom table
        global $wpdb;
        $table_name   = $wpdb->prefix . 'techno_cb_translations';
        $translations = array();

        if ( !empty( $secondary_langs ) ) {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT lang_code, option_key, option_value FROM {$table_name} WHERE option_key LIKE 'faq_%d_%%'",
                    $post->ID
                )
            );

            if ( !empty( $results ) ) {
                foreach ( $results as $row ) {
                    $translations[ $row->lang_code ][ $row->option_key ] = $row->option_value;
                }
            }
        }
        ?>

        <?php if ( ! empty( $secondary_langs ) ) : ?>
            <h2 class="nav-tab-wrapper techno-faq-tabs" style="padding-bottom:0; margin-bottom:15px;">
                <a href="#techno-tab-default" class="nav-tab nav-tab-active"><?php _e( 'Default (EN)', 'techno-chatbot' ); ?></a>
                <?php foreach ( $secondary_langs as $code => $lang ) : ?>
                    <a href="#techno-tab-<?php echo esc_attr( $code ); ?>" class="nav-tab">
                        <?php echo esc_html( strtoupper( $lang ) ); ?>
                    </a>
                <?php endforeach; ?>
            </h2>
        <?php endif; ?>

        <!-- DEFAULT / PRIMARY LANGUAGE TAB -->
        <div id="techno-tab-default" class="techno-faq-tab-content">
            <p>
                <label><strong><?php _e( 'Possible Questions', 'techno-chatbot' ); ?></strong></label><br>
                <input type="text" 
                    name="possible_questions" 
                    value="<?php echo esc_attr( $possible_questions ); ?>" 
                    style="width:100%;" />
                <small style="color:#666;">
                    <?php _e( 'Separate questions by comma.', 'techno-chatbot' ); ?>
                </small>
            </p>

            <p>
                <label><strong><?php _e( 'Answer', 'techno-chatbot' ); ?></strong></label><br>
                <?php wp_editor(
                    $answer,
                    'faq_answer_editor',
                    array(
                        'textarea_name' => 'faq_answer', 
                        'textarea_rows' => 6,
                        'media_buttons' => false,
                        'teeny'         => true,
                        'quicktags'     => false,
                        'tinymce'       => array(
                            'toolbar1' => 'bold,italic,bullist,numlist,link,unlink,undo,redo',
                            'toolbar2' => '',
                            'menubar'  => false,
                            'branding' => false,
                            'statusbar'=> false,
                        ),
                    )
                ); ?>
            </p>

            <p>
                <label><strong><?php _e( 'Priority', 'techno-chatbot' ); ?></strong></label><br>
                <input type="number"
                    name="faq_priority"
                    value="<?php echo esc_attr( $priority ); ?>"
                    min="0"
                    step="1"
                    style="width:120px;" />
                <small style="color:#666;">
                    <?php _e( 'Higher priority FAQs appear first.', 'techno-chatbot' ); ?>
                </small>
            </p>
        </div>

        <!-- SECONDARY LANGUAGE TABS -->
        <?php foreach ( $secondary_langs as $code => $lang ) : 
            $lang_q_key = "faq_{$post->ID}_questions";
            $lang_a_key = "faq_{$post->ID}_answer";
            $saved_q = isset( $translations[ $code ][ $lang_q_key ] ) ? $translations[ $code ][ $lang_q_key ] : '';
            $saved_a = isset( $translations[ $code ][ $lang_a_key ] ) ? $translations[ $code ][ $lang_a_key ] : '';

            if ( ! empty( $saved_q ) ) {
                $decoded = json_decode( $saved_q, true );
                if ( is_array( $decoded ) ) {
                    $saved_q = implode( ', ', $decoded );
                }
            }

            // Editor IDs must be lowercase, alphanumeric, with no hyphens
            $editor_id = 'faq_answer_editor_' . preg_replace( '/[^a-z0-9_]/', '', strtolower( $code ) );
        ?>
            <div id="techno-tab-<?php echo esc_attr( $code ); ?>" class="techno-faq-tab-content" style="display:none;">
                <p>
                    <label><strong><?php printf( esc_html__( 'Possible Questions (%s)', 'techno-chatbot' ), strtoupper( $code ) ); ?></strong></label><br>
                    <input type="text" 
                        name="trans_questions[<?php echo esc_attr( $code ); ?>]" 
                        value="<?php echo esc_attr( $saved_q ); ?>" 
                        style="width:100%;" 
                        placeholder="<?php esc_attr_e( 'Leave empty to fallback to primary language', 'techno-chatbot' ); ?>" />
                    <small style="color:#666;">
                        <?php _e( 'Separate questions by comma.', 'techno-chatbot' ); ?>
                    </small>
                </p>

                <p>
                    <label><strong><?php printf( esc_html__( 'Answer (%s)', 'techno-chatbot' ), strtoupper( $code ) ); ?></strong></label><br>
                    <?php wp_editor(
                        $saved_a,
                        $editor_id,
                        array(
                            'textarea_name' => 'trans_answers[' . esc_attr( $code ) . ']',
                            'textarea_rows' => 6,
                            'media_buttons' => false,
                            'teeny'         => true,
                            'quicktags'     => false,
                            'tinymce'       => array(
                                'toolbar1' => 'bold,italic,bullist,numlist,link,unlink,undo,redo',
                                'toolbar2' => '',
                                'menubar'  => false,
                                'branding' => false,
                                'statusbar'=> false,
                            ),
                        )
                    ); ?>
                </p>
            </div>
        <?php endforeach; ?>

        <!-- Tab Switcher with TinyMCE Refresh Script -->
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.techno-faq-tabs a').on('click', function(e) {
                    e.preventDefault();
                    $('.techno-faq-tabs a').removeClass('nav-tab-active');
                    $(this).addClass('nav-tab-active');
                    
                    $('.techno-faq-tab-content').hide();
                    var targetTab = $($(this).attr('href'));
                    targetTab.show();

                    // Refresh TinyMCE dimensions so toolbars render properly in newly visible tabs
                    if (typeof tinymce !== 'undefined') {
                        targetTab.find('.mce-tinymce').each(function() {
                            var editorId = $(this).attr('id').replace('_parent', '');
                            var editor = tinymce.get(editorId);
                            if (editor) {
                                editor.execCommand('mceAutoResize');
                            }
                        });
                    }
                });
            });
        </script>
        <?php
    }

    /**
	 * Save CPT FAQ metabox
	 *
	 * @since    1.0.0
	 */
   public function save_faq_meta( $post_id ) {

        if ( ! isset( $_POST['techno_chatbot_faq_nonce_field'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['techno_chatbot_faq_nonce_field'], 'techno_chatbot_faq_nonce' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // 1. Save Primary Meta Fields
        if ( isset( $_POST['possible_questions'] ) ) {
            update_post_meta( $post_id, '_possible_questions', sanitize_text_field( $_POST['possible_questions'] ) );
        }

        if ( isset( $_POST['faq_answer'] ) ) {
            update_post_meta( $post_id, '_faq_answer', wp_kses_post( wp_unslash( $_POST['faq_answer'] ) ) );
        }

        if ( isset( $_POST['faq_priority'] ) ) {
            update_post_meta( $post_id, '_faq_priority', intval( $_POST['faq_priority'] ) );
        }

        // 2. Save Secondary Language Translations to Custom Table
        global $wpdb;
        $table_name = $wpdb->prefix . 'techno_cb_translations';

        $secondary_langs = get_option( 'techno_chatbot_active_languages', array() );

        foreach ( $secondary_langs as $code => $lang ) {
            $questions_raw = isset( $_POST['trans_questions'][ $code ] ) ? sanitize_text_field( $_POST['trans_questions'][ $code ] ) : '';
            $answer_raw    = isset( $_POST['trans_answers'][ $code ] ) ? wp_kses_post( wp_unslash( $_POST['trans_answers'][ $code ] ) ) : '';

            $q_key = "faq_{$post_id}_questions";
            $a_key = "faq_{$post_id}_answer";

            // Save Questions
            if ( ! empty( $questions_raw ) ) {
                $wpdb->replace(
                    $table_name,
                    array(
                        'lang_code'    => $code,
                        'option_key'   => $q_key,
                        'option_value' => $questions_raw,
                    ),
                    array( '%s', '%s', '%s' )
                );
            } else {
                $wpdb->delete( $table_name, array( 'lang_code' => $code, 'option_key' => $q_key ), array( '%s', '%s' ) );
            }

            // Save Answer
            if ( ! empty( $answer_raw ) ) {
                $wpdb->replace(
                    $table_name,
                    array(
                        'lang_code'    => $code,
                        'option_key'   => $a_key,
                        'option_value' => $answer_raw,
                    ),
                    array( '%s', '%s', '%s' )
                );
            } else {
                $wpdb->delete( $table_name, array( 'lang_code' => $code, 'option_key' => $a_key ), array( '%s', '%s' ) );
            }
        }
    }

    /**
	 * Render CPT AI Knowledgebase metabox
	 *
	 * @since    1.0.0
	 */
    public function render_aidb_meta_box( $post ) {

        wp_nonce_field( 'techno_chatbot_aidb_nonce', 'techno_chatbot_aidb_nonce_field' );

        $page_url = get_post_meta( $post->ID, '_page_url', true );
        $ai_clean_text = get_post_meta( $post->ID, '_ai_clean_text', true );

        $ai_embeddings = get_post_meta( $post->ID, '_ai_embeddings', true );
        $ai_embeddings = ! empty( $ai_embeddings ) ? $ai_embeddings : 'N/A';

        $ai_last_crawled = get_post_meta( $post->ID, '_ai_last_crawled', true );
        $ai_last_crawled = ! empty( $ai_last_crawled ) ? $ai_last_crawled : 'N/A';

        $ai_status = get_post_meta( $post->ID, '_ai_status', true );
        $ai_status = ! empty( $ai_status ) ? $ai_status : 'N/A';

        $ai_db_type = get_post_meta( $post->ID, '_ai_db_type', true );
        $ai_db_type = ! empty( $ai_db_type ) ? $ai_db_type : 'N/A';

        $ai_db_postID = get_post_meta( $post->ID, '_ai_db_postID', true );
        $ai_db_postID = ! empty( $ai_db_postID ) ? $ai_db_postID : 'N/A';

        $ai_check = techno_chatbot_feature('ai_training');
    	$ai_allowed = $ai_check['allowed'] === true;
        $disabledmsg = $ai_check['message'];
        $post_status = get_post_status( $post->ID );
        $button_label = !empty( $ai_clean_text ) ? __( 'Re-crawl This Source', 'techno-chatbot' ) : __( 'Crawl This Source', 'techno-chatbot' );

        $ai_clean_text = ! empty( $ai_clean_text ) ? $ai_clean_text : 'N/A';

        if( $ai_allowed && $post_status === 'publish' ){ ?>
            <p>
                <button type="button" class="button button-primary" id="techno-crawl-page" data-default-text="<?php echo esc_attr( $button_label ); ?>">
                    <?php echo esc_html( $button_label ); ?>
                </button>
            </p>
        <?php }else{ ?>
            <div style="display:flex;align-items: center;gap: 20px;">
                <p>
                    <button type="button" class="button button-primary" disabled data-default-text="<?php echo esc_attr( $button_label ); ?>">
                        <?php echo esc_html( $button_label ); ?>
                    </button>
                </p>
                <?php techno_chatbot_msgformat($disabledmsg); ?>
            </div>
        <?php } ?>
        
        <p>
            <label><strong><?php _e( 'Resource URL', 'techno-chatbot' ); ?></strong></label><br>
            <input type="url" name="page_url" value="<?php echo esc_attr( $page_url ); ?>" <?php echo isset($page_url) && !empty($page_url)? 'readonly disabled':''; ?> style="width:100%;" /><br/>
            <small>If using PDF as a source make sure it's not a flatten image.</small>
        </p>

        <p>
            <label><strong><?php _e( 'Type', 'techno-chatbot' ); ?></strong></label><br>
            <div><?php echo esc_textarea( $ai_db_type ); ?> | ID: <?php echo esc_textarea( $ai_db_postID ); ?></div>
        </p>

        <p>
            <label><strong><?php _e( 'Clean Text', 'techno-chatbot' ); ?></strong></label><br>
            <div><?php echo esc_textarea( $ai_clean_text ); ?></div>
        </p>

        <p>
            <label><strong><?php _e( 'Embeddings', 'techno-chatbot' ); ?></strong></label><br>
            <div style=" width: 100%; height: 20px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; "><?php echo esc_textarea( is_array($ai_embeddings)? print_r($ai_embeddings, true) : $ai_embeddings ); ?></div>
        </p>

        <p>
            <label><strong><?php _e( 'Last Crawled', 'techno-chatbot' ); ?></strong></label><br>
            <div><?php echo esc_textarea( $ai_last_crawled ); ?></div>
        </p>

        <p>
            <label><strong><?php _e( 'Status', 'techno-chatbot' ); ?></strong></label><br>
            <div><?php echo esc_textarea( $ai_status ); ?></div>
        </p>

        <?php
    }

    /**
	 * Save CPT FAQ metabox
	 *
	 * @since    1.0.0
	 */
    public function save_aidb_meta( $post_id ) {

        if ( ! isset( $_POST['techno_chatbot_aidb_nonce_field'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['techno_chatbot_aidb_nonce_field'], 'techno_chatbot_aidb_nonce' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( isset( $_POST['page_url'] ) ) {
            $url = sanitize_text_field( $_POST['page_url'] );

            if ( ! empty( $url ) ) {
                // If it's a full URL, validate that it belongs to the current site host
                if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
                    $target_host       = parse_url( $url, PHP_URL_HOST );
                    $site_host         = parse_url( home_url(), PHP_URL_HOST );
                    $target_host_clean = preg_replace( '/^www\./', '', strtolower( $target_host ) );
                    $site_host_clean   = preg_replace( '/^www\./', '', strtolower( $site_host ) );

                    if ( $target_host_clean === $site_host_clean ) {
                        $path = parse_url( $url, PHP_URL_PATH );
                        update_post_meta( $post_id, '_page_url', sanitize_text_field( $path ) );
                    }
                } else {
                    // If the user pasted a relative path directly (e.g. /22-woodbrook-drive-ridge/)
                    $path = '/' . ltrim( $url, '/' );
                    update_post_meta( $post_id, '_page_url', sanitize_text_field( $path ) );
                }
            } else {
                update_post_meta( $post_id, '_page_url', '' );
            }
        }

    }

    /**
	 * Add AI DB Columns
	 *
	 * @since    1.2.6
	 */
    public function add_aidb_column( $columns ) {
        $new_columns = array();
        foreach ( $columns as $key => $title ) {
            $new_columns[ $key ] = $title;
            if ( 'title' === $key ) {
                $new_columns['techno_post_type'] = __( 'Post Type', 'techno-chatbot' );
            }
        }
        return $new_columns;
    }

    /**
	 * Render AI DB Columns
	 *
	 * @since    1.2.6
	 */
    public function render_aidb_column( $column_name, $post_id = 0 ) {
        if ( 'techno_post_type' === $column_name && $post_id ) {
            $target_post_id = get_post_meta( $post_id, '_ai_db_postID', true );
            
            if ( ! empty( $target_post_id ) && intval( $target_post_id ) > 0 ) {
                $post_type_obj = get_post_type_object( get_post_type( $target_post_id ) );
                echo esc_html( $post_type_obj ? $post_type_obj->labels->singular_name : get_post_type( $target_post_id ) );
            } else {
                $ai_db_type = get_post_meta( $post_id, '_ai_db_type', true );
                echo esc_html( ! empty( $ai_db_type ) ? ucfirst( $ai_db_type ) : __( 'N/A', 'techno-chatbot' ) );
            }
        }
    }

    /**
	 * Add AI DB list view
	 *
	 * @since    1.2.6
	 */
    public function add_aidb_post_type_views( $views ) {
        global $wpdb;
        $current = isset( $_GET['techno_ai_type_filter'] ) ? sanitize_text_field( $_GET['techno_ai_type_filter'] ) : '';

        // Fetch distinct types and their counts from postmeta and posts tables
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT pm.meta_value AS type, COUNT(p.ID) AS total 
                FROM {$wpdb->postmeta} pm
                INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                WHERE pm.meta_key = %s 
                AND pm.meta_value != '' 
                AND p.post_type = %s 
                AND p.post_status != 'trash'
                GROUP BY pm.meta_value",
                '_ai_db_type',
                'techno_chatbot_aidb'
            ),
            ARRAY_A
        );

        if ( ! empty( $results ) ) {
            foreach ( $results as $row ) {
                $type  = $row['type'];
                $count = $row['total'];
                $class = ( $current === $type ) ? 'current' : '';
                $url   = admin_url( 'edit.php?post_type=techno_chatbot_aidb&techno_ai_type_filter=' . urlencode( $type ) );
                
                $label = $type;
                if ( 'file' === $type ) {
                    $label = __( 'Files', 'techno-chatbot' );
                } else {
                    $post_type_obj = get_post_type_object( $type );
                    if ( $post_type_obj ) {
                        $label = $post_type_obj->labels->name;
                    } else {
                        $label = ucfirst( $type );
                    }
                }

                $views[ 'techno_type_' . $type ] = sprintf(
                    '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>',
                    esc_url( $url ),
                    esc_attr( $class ),
                    esc_html( $label ),
                    esc_html( $count )
                );
            }
        }

        return $views;
    }

    /**
	 * Query AI DB list view
	 *
	 * @since    1.2.6
	 */
    public function filter_aidb_requests( $query ) {
        global $pagenow;
        if ( is_admin() && 'edit.php' === $pagenow && $query->get( 'post_type' ) === 'techno_chatbot_aidb' ) {
            if ( isset( $_GET['techno_ai_type_filter'] ) && ! empty( $_GET['techno_ai_type_filter'] ) ) {
                $filter_type = sanitize_text_field( $_GET['techno_ai_type_filter'] );
                $meta_query  = $query->get( 'meta_query' );
                if ( ! is_array( $meta_query ) ) {
                    $meta_query = array();
                }
                $meta_query[] = array(
                    'key'     => '_ai_db_type',
                    'value'   => $filter_type,
                    'compare' => '=',
                );
                $query->set( 'meta_query', $meta_query );
            }
        }
    }

    /**
     * Render the Manage Multiple modal popup on the AI Knowledgebase list page.
     *
     * @since 1.2.6
     */
    public function render_manage_multiple_modal() {
        global $pagenow;
        if ( 'edit.php' !== $pagenow || ! isset( $_GET['post_type'] ) || 'techno_chatbot_aidb' !== $_GET['post_type'] ) {
            return;
        }

        $post_types = get_post_types( array( 'public' => true ), 'objects' );
        unset( $post_types['attachment'] );
        unset( $post_types['techno_chatbot_aidb'] );
        unset( $post_types['techno_chatbot_faq'] );
        ?>
        <div id="techno-manage-multiple-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:100000; align-items:center; justify-content:center;">
            <div style="background:#fff; padding:30px; width:600px; max-width:90%; border-radius:4px; box-shadow:0 4px 10px rgba(0,0,0,0.3); position:relative; max-height:90vh; overflow-y:auto;">
                <h2><?php _e( 'Crawl Multiple Posts', 'techno-chatbot' ); ?></h2>
                <button type="button" id="techno-close-modal" style="position:absolute; top:15px; right:15px; background:none; border:none; font-size:18px; cursor:pointer;">&times;</button>
                
                <form id="techno-manage-multiple-form">
                    <!-- Post Types Checkboxes -->
                    <p>
                        <label><strong><?php _e( 'Select Post Types:', 'techno-chatbot' ); ?></strong></label><br>
                        <div style="margin-top:8px; display:flex; flex-wrap:wrap; gap:15px;">
                            <?php foreach ( $post_types as $pt_slug => $pt_obj ) : 
                                $checked = ( 'page' === $pt_slug ) ? 'checked' : '';
                            ?>
                                <label>
                                    <input type="checkbox" name="post_types[]" value="<?php echo esc_attr( $pt_slug ); ?>" <?php echo $checked; ?>>
                                    <?php echo esc_html( $pt_obj->labels->name ); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </p>

                    <!-- Exclude Search Field with Button -->
                    <p style="position:relative;">
                        <label><strong><?php _e( 'Exclude Specific Posts:', 'techno-chatbot' ); ?></strong></label><br>
                        <div style="display:flex; gap:5px; margin-top:5px;">
                            <input type="text" id="techno-exclude-search" placeholder="<?php esc_attr_e( 'Search by post title to exclude...', 'techno-chatbot' ); ?>" style="flex:1;" autocomplete="off">
                            <button type="button" class="button" id="techno-search-posts-btn" title="<?php esc_attr_e( 'Search Posts', 'techno-chatbot' ); ?>">
                                <span class="dashicons dashicons-search" style="margin-top:3px;"></span>
                            </button>
                        </div>
                        <div id="techno-search-results" style="position:absolute; background:#fff; border:1px solid #ccc; width:100%; max-height:150px; overflow-y:auto; display:none; z-index:10; box-shadow:0 2px 5px rgba(0,0,0,0.1);"></div>
                    </p>

                    <!-- Excluded List Container -->
                    <div id="techno-excluded-items" style="margin-bottom:15px; display:flex; flex-wrap:wrap; gap:5px;"></div>

                    <!-- Progress Bar -->
                    <div id="techno-progress-container" style="display:none; margin-bottom:15px;">
                        <div style="background:#f1f1f1; border-radius:4px; overflow:hidden; height:20px; border:1px solid #ccc;">
                            <div id="techno-progress-bar" style="background:#007cba; width:0%; height:100%; text-align:center; color:#fff; font-size:12px; line-height:20px; transition:width 0.3s;">0%</div>
                        </div>
                        <p id="techno-progress-text" style="margin:5px 0 0; font-size:13px; color:#666;"></p>
                    </div>

                    <!-- Action Button -->
                    <p style="text-align:right; margin-top:20px;">
                        <button type="button" class="button button-primary" id="techno-multiple-crawl-btn"><?php _e( 'Batch Crawl', 'techno-chatbot' ); ?></button>
                    </p>
                </form>
                
                <hr/>

                <form id="techno-manage-delete-form">
                    <h2><?php _e( 'Bulk Permanent Delete All', 'techno-chatbot' ); ?></h2>
                    <p style="color:#d63638; font-weight:500;">
                        <?php _e( 'Notice: This will permanently delete all AI knowledgebase data and associated posts.', 'techno-chatbot' ); ?>
                    </p>
                    <p>
                        <label>
                            <input type="checkbox" id="techno-delete-confirm" required>
                            <?php _e( 'I understand and want to permanently delete all AI Knowledgebase data.', 'techno-chatbot' ); ?>
                        </label>
                    </p>

                    <!-- Delete Progress Bar -->
                    <div id="techno-delete-progress-container" style="display:none; margin-bottom:15px;">
                        <div style="background:#f1f1f1; border-radius:4px; overflow:hidden; height:20px; border:1px solid #ccc;">
                            <div id="techno-delete-progress-bar" style="background:#d63638; width:0%; height:100%; text-align:center; color:#fff; font-size:12px; line-height:20px; transition:width 0.3s;">0%</div>
                        </div>
                        <p id="techno-delete-progress-text" style="margin:5px 0 0; font-size:13px; color:#666;"></p>
                    </div>

                    <p style="text-align:right; margin-top:20px;">
                        <button type="button" class="button button-link-delete" id="techno-multiple-delete-btn" disabled><?php _e( 'Delete All AI Knowledgebase', 'techno-chatbot' ); ?></button>
                    </p>
                </form>
            </div>
        </div>

        <link rel="stylesheet" href="<?php echo esc_url( includes_url( 'css/dashicons.min.css' ) ); ?>">
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            if ($('.page-title-action').length) {
                $('.page-title-action').after('<button type="button" class="button page-title-action" id="techno-open-manage-modal" style="margin-left: 5px;"><?php esc_attr_e( 'Manage', 'techno-chatbot' ); ?></button>');
            }

            $('#techno-open-manage-modal').on('click', function(e) {
                e.preventDefault();
                $('#techno-manage-multiple-modal').css('display', 'flex');
            });
            $('#techno-close-modal, #techno-manage-multiple-modal').on('click', function(e) {
                if (e.target.id === 'techno-manage-multiple-modal' || e.target.id === 'techno-close-modal') {
                    $('#techno-manage-multiple-modal').hide();
                }
            });

            // Trigger search only via Search Icon Button and use currently checked post types
            $('#techno-search-posts-btn').on('click', function() {
                let keyword = $('#techno-exclude-search').val();
                let selectedPostTypes = [];
                $('#techno-manage-multiple-form input[name="post_types[]"]:checked').each(function() {
                    selectedPostTypes.push($(this).val());
                });

                if (selectedPostTypes.length === 0) {
                    alert('Please select at least one post type to search within.');
                    return;
                }

                if (keyword.length < 1) {
                    $('#techno-search-results').hide().html('');
                    return;
                }

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'techno_chatbot_search_posts',
                        keyword: keyword,
                        post_types: selectedPostTypes,
                        nonce: '<?php echo wp_create_nonce( "techno_exclude_nonce" ); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            let html = '';
                            response.data.forEach(function(post) {
                                html += '<div class="techno-search-result-item" data-id="' + post.ID + '" data-title="' + post.title + '" style="padding:8px; cursor:pointer; border-bottom:1px solid #eee;">' + post.display_title + '</div>';
                            });
                            $('#techno-search-results').html(html).show();
                        } else {
                            $('#techno-search-results').html('<div style="padding:8px; color:#666;">No posts found</div>').show();
                        }
                    }
                });
            });

            // Allow pressing Enter in search field to trigger search button
            $('#techno-exclude-search').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#techno-search-posts-btn').click();
                }
            });

            // Add to Exclude List
            $(document).on('click', '.techno-search-result-item', function() {
                let postId = $(this).data('id');
                let postTitle = $(this).data('title');
                
                if ($('#techno-exclude-input-' + postId).length === 0) {
                    $('#techno-excluded-items').append(
                        '<span class="tag" style="background:#fa1d1d; color:#fff; padding:4px 8px; border-radius:3px; font-size:12px; display:inline-flex; align-items:center; gap:5px;">' +
                        postTitle +
                        '<span class="remove-exclude" style="cursor:pointer; font-weight:bold; color:#fff;" data-id="' + postId + '">&times;</span>' +
                        '<input type="hidden" name="exclude_ids[]" id="techno-exclude-input-' + postId + '" value="' + postId + '">' +
                        '</span>'
                    );
                }
                $('#techno-exclude-search').val('');
                $('#techno-search-results').hide().html('');
            });

            $(document).on('click', '.remove-exclude', function() {
                $(this).parent('.tag').remove();
            });

            // Multiple Crawl Batch Processing
            $('#techno-multiple-crawl-btn').on('click', function() {
                let form_data = $('#techno-manage-multiple-form').serializeArray();
                form_data.push({ name: 'action', value: 'techno_chatbot_batch_crawl' });
                form_data.push({ name: 'nonce', value: '<?php echo wp_create_nonce( "techno_batch_crawl_nonce" ); ?>' });
                form_data.push({ name: 'duplicates', value: 0 });

                $('#techno-progress-container').show();
                $('#techno-progress-bar').css('width', '0%').text('0%');
                $('#techno-progress-text').text('Initializing batch crawl...');
                $('#techno-manage-multiple-form input, #techno-multiple-crawl-btn').prop('disabled', true);

                runBatchCrawl(form_data, 1, 0);
            });

            function runBatchCrawl(formData, page, totalDuplicates) {
                let data = formData.filter(item => item.name !== 'duplicates' && item.name !== 'paged');
                data.push({ name: 'paged', value: page });
                data.push({ name: 'duplicates', value: totalDuplicates });

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            let percent = response.data.percent;
                            let currentDuplicates = response.data.duplicates || 0;
                            
                            $('#techno-progress-bar').css('width', percent + '%').text(percent + '%');
                            $('#techno-progress-text').text(response.data.message);

                            if (response.data.has_more) {
                                runBatchCrawl(formData, page + 1, currentDuplicates);
                            } else {
                                $('#techno-progress-text').html(
                                    response.data.message + '<br><br>' +
                                    'Crawling completed successfully! ' +
                                    '<button type="button" class="button button-primary" id="techno-reload-btn" style="margin-left: 10px;">Success Reload Now</button>'
                                );
                                
                                $(document).on('click', '#techno-reload-btn', function() {
                                    location.reload();
                                });
                            }
                        } else {
                            alert(response.data.message || 'An error occurred.');
                            $('#techno-manage-multiple-form input, #techno-multiple-crawl-btn').prop('disabled', false);
                        }
                    },
                    error: function() {
                        alert('Server error during batch processing.');
                        $('#techno-manage-multiple-form input, #techno-multiple-crawl-btn').prop('disabled', false);
                    }
                });
            }

            // Enable/Disable delete button based on checkbox confirmation
            $('#techno-delete-confirm').on('change', function() {
                $('#techno-multiple-delete-btn').prop('disabled', !this.checked);
            });

            // Bulk Permanent Delete Processing
            $('#techno-multiple-delete-btn').on('click', function() {
                if (!$('#techno-delete-confirm').is(':checked')) {
                    return;
                }

                $('#techno-delete-progress-container').show();
                $('#techno-delete-progress-bar').css('width', '0%').text('0%');
                $('#techno-delete-progress-text').text('Initializing permanent deletion...');
                
                // Disable all buttons and inputs in the entire popup modal
                $('#techno-manage-multiple-modal button, #techno-manage-multiple-modal input').prop('disabled', true);

                runBatchDelete(1);
            });

            function runBatchDelete(page) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'techno_chatbot_batch_delete_aidb',
                        paged: page,
                        nonce: '<?php echo wp_create_nonce( "techno_batch_delete_nonce" ); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            let percent = response.data.percent;
                            $('#techno-delete-progress-bar').css('width', percent + '%').text(percent + '%');
                            $('#techno-delete-progress-text').text(response.data.message);

                            if (response.data.has_more) {
                                runBatchDelete(page + 1);
                            } else {
                                $('#techno-delete-progress-text').html(
                                    response.data.message + '<br><br>' +
                                    'Deletion completed successfully! ' +
                                    '<button type="button" class="button button-primary" id="techno-delete-reload-btn" style="margin-left: 10px;">Success Reload Now</button>'
                                );
                                
                                $(document).on('click', '#techno-delete-reload-btn', function() {
                                    location.reload();
                                });
                            }
                        } else {
                            alert(response.data.message || 'An error occurred during deletion.');
                            $('#techno-manage-multiple-modal input, #techno-manage-multiple-modal button').prop('disabled', false);
                        }
                    },
                    error: function() {
                        alert('Server error during batch deletion.');
                        $('#techno-manage-multiple-modal input, #techno-manage-multiple-modal button').prop('disabled', false);
                    }
                });
            }
        });
        </script>
        <?php
    }

    /**
     * AJAX Handler: Search posts for exclusion popup.
     * 
     * @since 1.2.6
     */
    public function search_posts_for_exclude() {
        check_ajax_referer( 'techno_exclude_nonce', 'nonce' );

        $keyword = sanitize_text_field( $_POST['keyword'] );
        $post_types = isset( $_POST['post_types'] ) ? array_map( 'sanitize_text_field', $_POST['post_types'] ) : array();

        if ( empty( $post_types ) ) {
            wp_send_json_success( array() );
        }

        $args = array(
            'post_type'      => $post_types,
            'post_status'    => 'publish',
            's'              => $keyword,
            'posts_per_page' => 10,
        );

        $query = new WP_Query( $args );
        $results = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post(); // Fixed fatal memory leak bug from previous typo ($query->the_post;)
                $pt = get_post_type();
                $pt_obj = get_post_type_object( $pt );
                $pt_label = $pt_obj ? $pt_obj->labels->singular_name : ucfirst( $pt );
                $title = get_the_title();

                $results[] = array(
                    'ID'            => get_the_ID(),
                    'title'         => $title,
                    'display_title' => $pt_label . ': ' . $title,
                );
            }
            wp_reset_postdata();
        }

        wp_send_json_success( $results );
    }
}