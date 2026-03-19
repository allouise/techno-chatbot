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
	 * Register FAQ CPT
	 *
	 * @since    1.0.0
	 */
    public function register_faq_post_type() {

        $labels = array(
            'name'          => __( 'FAQ', 'techno-chatbot' ),
            'singular_name' => __( 'FAQ', 'techno-chatbot' ),
            'menu_name'     => __( 'FAQ', 'techno-chatbot' ),
            'add_new_item'  => __( 'Add New FAQ', 'techno-chatbot' ),
            'edit_item'     => __( 'Edit FAQ', 'techno-chatbot' ),
            'not_found'     => __( 'No FAQ found', 'techno-chatbot' ),
        );

        $args = array(
            'labels'        => $labels,
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => false,
            'menu_icon'     => 'dashicons-editor-help',
            'supports'      => array( 'title' ),
            'rewrite'       => array( 'slug' => 'techno-chatbot-faq' ),
            'show_in_rest'  => true,
        );

        register_post_type( 'techno_chatbot_faq', $args );
    }

    /**
	 * Register FAQ Tax: Category
	 *
	 * @since    1.0.0
	 */
    public function register_faq_taxonomy() {

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
	 * Add CPT FAQ metabox
	 *
	 * @since    1.0.0
	 */
    public function add_faq_meta_boxes() {
        add_meta_box(
            'techno_chatbot_faq_meta',
            __( 'FAQ Details', 'techno-chatbot' ),
            array( $this, 'render_faq_meta_box' ),
            'techno_chatbot_faq',
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
}