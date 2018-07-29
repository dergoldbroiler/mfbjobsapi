<?php

/**
 * Calls the class on the post edit screen.
 */
function setupBackend() {
    new MFBJOBSAPI_Backend();
}
 
if ( is_admin() ) {
    add_action( 'load-post.php',     'setupBackend' );
    add_action( 'load-post-new.php', 'setupBackend' );
}
 


/**
 * Plugin_MFBJOBSAPI Class for Backendfunctions
 *
 * @package Plugin MFBJOBSAPI
 */
class MFBJOBSAPI_Backend {

    /*
    * contructor, started at plugin setup
    * contains e.g. the needed shortcodes
    */
    public function __construct()
    {
        
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post',      array( $this, 'save'         ) );
        
    }
    
    
    /**
     * Adds the meta box container.
     */
    public function add_meta_box( $post_type ) {
        // Limit meta box to certain post types.
        $post_types = array( 'job_listing' );
 
        if ( in_array( $post_type, $post_types ) ) {
            add_meta_box(
                'metabox_bezeichnung',
                __( 'Genaue Jobbezeichnung', 'plugin-MFBJOBSAPI' ),
                array( $this, 'render_meta_box_content' ),
                $post_type,
                'advanced',
                'high'
            );
        }
    }
 
    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save( $post_id ) {
 
        /*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */
 
        // Check if our nonce is set.
        if ( ! isset( $_POST['myplugin_inner_custom_box_nonce'] ) ) {
            return $post_id;
        }
 
        $nonce = $_POST['myplugin_inner_custom_box_nonce'];
 
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'myplugin_inner_custom_box' ) ) {
            return $post_id;
        }
 
        /*
         * If this is an autosave, our form has not been submitted,
         * so we don't want to do anything.
         */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }
 
        // Check the user's permissions.
        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }
 
        /* OK, it's safe for us to save the data now. */
 
        // Sanitize the user input.
        $mydata = sanitize_text_field( $_POST['searchinput_KEY'] );
 
        // Update the meta field.
        update_post_meta( $post_id, '_my_meta_value_key', $mydata );
    }
 
 
    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content( $post ) {
        ?>
 
        <?php
      $url = get_bloginfo('url').'/wp-content/plugins/mfbjobsapi/includes/class-mfbjobsapi-suggest.php';
      
        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'myplugin_inner_custom_box', 'myplugin_inner_custom_box_nonce' );
 
        // Use get_post_meta to retrieve an existing value from the database.
        $value = get_post_meta( $post->ID, '_my_meta_value_key', true );
 
        // Display the form, using the current value.
        ?>
        <label for="searchinput_KEY">
            <?php _e( 'Mindest. 3 Zeichen (System sucht automatisch)', 'plugin-MFBJOBSAPI' ); ?>
        </label>
        <input type='text' data-suggest='<?php echo $url; ?>' id='searchinput_KEY' class='form-control input-lg'><br>
        <div class='searchformresult'></div>
        <?php
    }
}