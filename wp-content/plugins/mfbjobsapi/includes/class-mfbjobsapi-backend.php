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
        if ( ! isset( $_POST['mfbjobsapi_inner_custom_box_nonce'] ) ) {
            return $post_id;
        }
 
        $nonce = $_POST['mfbjobsapi_inner_custom_box_nonce'];
 
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'mfbjobsapi_inner_custom_box' ) ) {
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
        $jobbezeichnung_val = sanitize_text_field( $_POST['jobbezeichnung'] );
        $jobstatus_val = $_POST['jobstatus'];
        $jobaction_val = $_POST['jobaction'];
        $job_id_val = $_POST['job_id'];
        $job_bkz_val = $_POST['job_bkz'];
    
        // Update the meta fields.
        update_post_meta( $post_id, 'jobbezeichnung', $jobbezeichnung_val );
        update_post_meta( $post_id, 'job_id', $job_id_val );
        update_post_meta( $post_id, 'job_bkz', $job_bkz_val );
        update_post_meta( $post_id, 'jobstatus', $jobstatus_val );
        update_post_meta( $post_id, 'jobaction', $jobaction_val );
    }
 
 
    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content( $post ) {
     
        //url of suggest script - needed for input field data-attribute -> data-suggest='<?php echo $url; 
        $url = get_bloginfo('url').'/wp-content/plugins/mfbjobsapi/includes/class-mfbjobsapi-suggest.php';
      
        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'mfbjobsapi_inner_custom_box', 'mfbjobsapi_inner_custom_box_nonce' );
 
        // Use get_post_meta to retrieve an existing value from the database.
        $jobbezeichnung_val = get_post_meta( $post->ID, 'jobbezeichnung', true );
        $jobstatus_val = get_post_meta( $post->ID, 'jobstatus', true );
        $jobaction_val = get_post_meta( $post->ID, 'jobaction', true );
        $job_id_val = get_post_meta( $post->ID, 'job_id', true );
        $job_bkz_val = get_post_meta( $post->ID, 'job_bkz', true );
      
        // Display the form, using the current value.
        ?>


        <label for="searchinput_KEY">
            <?php _e( 'Mindest. 3 Zeichen (System sucht automatisch)', 'plugin-MFBJOBSAPI' ); ?>
        </label>
        <input value="<?php echo $jobbezeichnung_val; ?>" type='text' name="jobbezeichnung" data-suggest='<?php echo $url; ?>' id='searchinput_KEY' class='form-control input-lg mfbjobsapi-formelement'><br>
        <div class='searchformresult'></div>
        <label for="jobstatus">
            <?php _e( 'Status des Stellenangebotes', 'plugin-MFBJOBSAPI' ); ?>
        </label>         
        <!-- Angaben zum Veröffentlichungsstatus des Stellenangebots.  (1)veröffentlicht    (2)anonym veröffentlicht   (3)nicht veröffentlicht -->
        <select name="jobstatus" class="mfbjobsapi-formelement" id="jobstatus">
                <option value="1" <?php if ( $jobstatus_val == 1 ) { echo 'selected'; } ?>></option></optio>veröffentlicht</option>
                <option value="2" <?php if ( $jobstatus_val == 2 ) { echo 'selected'; } ?>>anonym veröffentlicht</option>
                <option value="3" <?php if ( $jobstatus_val == 3 ) { echo 'selected'; } ?>>nicht veröffentlicht</option>
        </select><br>
        
        <label for="jobaction">
            <?php _e( 'Action des Stellenangebotes', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
        <!-- Action (1)neu    (2)update  (3)löschen -->
        <select name="jobaction" class="mfbjobsapi-formelement" id="jobaction">
                <option value="1" <?php if ( $jobaction_val == 1 ) { echo 'selected'; } ?>>Neuanlage</option>
                <option value="2" <?php if ( $jobaction_val == 2 ) { echo 'selected'; } ?>>Aktualisierung</option>
                <option value="3" <?php if ( $jobaction_val == 3 ) { echo 'selected'; } ?>>Löschung</option>
        </select><br>
        <label for="job_id">
            <?php _e( 'ID des Berufes', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
        <input value=<?php echo $job_id_val; ?> type='text' name="job_id" data-suggest='<?php echo $url; ?>' id='job_id' class='form-control input-lg mfbjobsapi-formelement'><br>
        <label for="job_bkz">
            <?php _e( 'Berufskennziffer', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
        <input value="<?php echo $job_bkz_val; ?>" type='text' name="job_bkz" data-suggest='<?php echo $url; ?>' id='job_bkz' class='form-control input-lg mfbjobsapi-formelement'><br>
        <?php
    }
}