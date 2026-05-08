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
                'name'          => __( 'FAQ', 'techno-chatbot' ),
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
                'name'          => __( 'AI Knowldegebase', 'techno-chatbot' ),
                'singular_name' => __( 'AI Knowldegebase', 'techno-chatbot' ),
                'menu_name'     => __( 'AI Knowldegebase', 'techno-chatbot' ),
                'add_new_item'  => __( 'Add New AI Knowldegebase', 'techno-chatbot' ),
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
        $answer = get_post_meta( $post->ID, '_faq_answer', true );
        $priority = get_post_meta( $post->ID, '_faq_priority', true );
        ?>

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
            <textarea name="faq_answer" rows="5" style="width:100%;"><?php echo esc_textarea( $answer ); ?></textarea>
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

        if ( isset( $_POST['possible_questions'] ) ) {
            update_post_meta( $post_id, '_possible_questions', sanitize_text_field( $_POST['possible_questions'] ) );
        }

        if ( isset( $_POST['faq_answer'] ) ) {
            update_post_meta( $post_id, '_faq_answer', sanitize_textarea_field( $_POST['faq_answer'] )
            );
        }

        if ( isset( $_POST['faq_priority'] ) ) {
            update_post_meta( $post_id, '_faq_priority', intval( $_POST['faq_priority'] ) );
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
        $crawled_content = get_post_meta( $post->ID, '_crawled_content', true );
        $ai_clean_text = get_post_meta( $post->ID, '_ai_clean_text', true );
        $ai_embeddings = get_post_meta( $post->ID, '_ai_embeddings', true );
        $ai_last_crawled = get_post_meta( $post->ID, '_ai_last_crawled', true );
        $ai_status = get_post_meta( $post->ID, '_ai_status', true );
        $ai_enabled = get_post_meta( $post->ID, '_ai_enabled', true );

        $ai_check = techno_chatbot_feature('ai_training');
    	$ai_allowed = $ai_check['allowed'] === true;
        $disabledmsg = $ai_check['message'];
        $post_status = get_post_status( $post->ID );
        if( $ai_allowed && $post_status === 'publish' ){ ?>
            <p>
                <button type="button" class="button button-primary" id="techno-crawl-page">
                    Crawl This Page
                </button>
            </p>
        <?php }else{ ?>
            <div style="display:flex;align-items: center;gap: 20px;">
                <p>
                    <button type="button" class="button button-primary" disabled>
                        Crawl This Page
                    </button>
                </p>
                <?php techno_chatbot_msgformat($disabledmsg); ?>
            </div>
        <?php } ?>
        
        <p>
            <label><strong><?php _e( 'Page URL', 'techno-chatbot' ); ?></strong></label><br>
            <input type="url" name="page_url" value="<?php echo esc_attr( $page_url ); ?>" <?php echo isset($page_url) && !empty($page_url)? 'readonly disabled':''; ?> style="width:100%;" />
        </p>

        <p>
            <label><strong><?php _e( 'Crawled Content', 'techno-chatbot' ); ?></strong></label><br>
            <div class="crawled_content"><?php echo esc_textarea( $crawled_content ); ?></div>
        </p>

        <p>
            <label><strong><?php _e( 'AI Clean Text', 'techno-chatbot' ); ?></strong></label><br>
            <div class="crawled_content"><?php echo esc_textarea( $ai_clean_text ); ?></div>
        </p>

        <p>
            <label><strong><?php _e( 'AI Embeddings', 'techno-chatbot' ); ?></strong></label><br>
            <div class="crawled_content"><?php echo esc_textarea( is_array($ai_embeddings)? print_r($ai_embeddings, true) : $ai_embeddings ); ?></div>
        </p>

        <p>
            <label><strong><?php _e( 'AI Last Crawled', 'techno-chatbot' ); ?></strong></label><br>
            <div class="crawled_content"><?php echo esc_textarea( $ai_last_crawled ); ?></div>
        </p>

        <p>
            <label><strong><?php _e( 'AI Status', 'techno-chatbot' ); ?></strong></label><br>
            <div class="crawled_content"><?php echo esc_textarea( $ai_status ); ?></div>
        </p>

        <p>
            <label><strong><?php _e( 'AI Enabled', 'techno-chatbot' ); ?></strong></label><br>
            <div class="crawled_content"><?php echo esc_textarea( $ai_enabled ); ?></div>
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
            update_post_meta( $post_id, '_page_url', sanitize_text_field( $_POST['page_url'] ) );
        }

    }
}