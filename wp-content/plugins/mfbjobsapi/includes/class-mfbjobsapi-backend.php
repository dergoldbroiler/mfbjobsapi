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
    
    protected function connectToDB() {
		
		$mysqli = @new mysqli('localhost', 'root', 'root', 'mfbjobsapi');
		//does not work on PHP smaller 5.2.9
		if ($mysqli->connect_error) {
    		die('Connect Error: ' . $mysqli->connect_error);
		}		
		return $mysqli;	
	}
    
    /*
    * contructor, started at plugin setup
    * contains e.g. the needed shortcodes
    */
    public function __construct()
    {
        
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post',      array( $this, 'save'         ) );
        
    }
    
    /*
    * returns lincences 
    @returns: object connectToDB   
    */
    public function get_komps_from_db() {
        $query = "SELECT * FROM `komp_vam`";
        $mysqli = MFBJOBSAPI::connectToDB();
		$kompsArray = array();
		if ($result = $mysqli->query($query)) {
			
			/* create single article-object and push to array */
			 while($obj = $result->fetch_object()){ 
				
                 array_push($kompsArray, $obj);
			 }
		}
        return $kompsArray;
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
        $joboffertype_val = $_POST['joboffertype'];
        $jobapplication_val = $_POST['jobapplication'];
        $jobleadership_val = $_POST['jobleadership'];
        $jobhours_val = $_POST['jobhours'];
        $jobworkingplan_val = $_POST['jobworkingplan'];
        $jobtermlength_val = $_POST['jobtermlength'];
        $jobtermdate_val = $_POST['jobtermdate'];
        $jobtermtakeover_val = $_POST['jobtermtakeover'];
        $jobtermbool_val  = $_POST['jobtermbool'];
        $jobsalary_val  = $_POST['jobsalary'];
        $jobpayscale_val  = $_POST['jobpayscale'];
        $jobagreement_val  = $_POST['jobagreement'];
        $jobhousing_val  = $_POST['jobhousing'];
        $jobedutitle_val  = $_POST['jobedutitle'];
        $joblicense_1_val  = $_POST['joblicense_1'];
        $joblicenselevel_1_val = $_POST['joblicenselevel_1'];
        $joblicense_2_val  = $_POST['joblicense_2'];
        $joblicenselevel_2_val = $_POST['joblicenselevel_2'];
        $joblicense_3_val  = $_POST['joblicense_3'];
        $joblicenselevel_3_val = $_POST['joblicenselevel_3'];
        
        // Update the meta fields.
        update_post_meta( $post_id, 'jobbezeichnung', $jobbezeichnung_val );
        update_post_meta( $post_id, 'job_id', $job_id_val );
        update_post_meta( $post_id, 'job_bkz', $job_bkz_val );
        update_post_meta( $post_id, 'jobstatus', $jobstatus_val );
        update_post_meta( $post_id, 'jobaction', $jobaction_val );
        update_post_meta( $post_id, 'jobsocialinsurance', $jobsocialinsurance_val );
        update_post_meta( $post_id, 'joboffertype', $joboffertype_val );
        update_post_meta( $post_id, 'jobapplication', $jobapplication_val );
        update_post_meta( $post_id, 'jobleadership', $jobleadership_val );
        update_post_meta( $post_id, 'jobhours', $jobhours_val );
        update_post_meta( $post_id, 'jobworkingplan', $jobworkingplan_val );
        update_post_meta( $post_id, 'jobtermlength', $jobtermlength_val );
        update_post_meta( $post_id, 'jobtermdate', $jobtermdate_val );
        update_post_meta( $post_id, 'jobtermbool', $jobtermbool_val );
        update_post_meta( $post_id, 'jobtermtakeover', $jobtermtakeover_val );
        update_post_meta( $post_id, 'jobsalary', $jobsalary_val );
        update_post_meta( $post_id, 'jobpayscale', $jobpayscale_val );
        update_post_meta( $post_id, 'jobagreement', $jobagreement_val );
        update_post_meta( $post_id, 'jobhousing', $jobhousing_val );
        update_post_meta( $post_id, 'jobedutitle', $jobedutitle_val );
        
        //Licenses
        update_post_meta( $post_id, 'joblicense_1', $joblicense_1_val );
        update_post_meta( $post_id, 'joblicenselevel_1', $joblicenselevel_1_val );
        update_post_meta( $post_id, 'joblicense_2', $joblicense_2_val );
        update_post_meta( $post_id, 'joblicenselevel_2', $joblicenselevel_2_val );
        update_post_meta( $post_id, 'joblicense_3', $joblicense_3_val );
        update_post_meta( $post_id, 'joblicenselevel_3', $joblicenselevel_3_val );
             
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
        $joboffertype_val = get_post_meta( $post->ID, 'joboffertype', true );
        $jobsocialinsurance_val  = get_post_meta( $post->ID, 'jobsocialinsurance', true );
        $jobapplication_val  = get_post_meta( $post->ID, 'jobapplication', true );
        $jobleadership_val  = get_post_meta( $post->ID, 'jobleadership', true );
        $jobhours_val  = get_post_meta( $post->ID, 'jobhours', true );
        $jobworkingplan_val  = get_post_meta( $post->ID, 'jobworkingplan', true );
        $jobtermlength_val = get_post_meta( $post->ID, 'jobtermlength', true );
        $jobtermdate_val = get_post_meta( $post->ID, 'jobtermdate', true );
        $jobtermbool_val = get_post_meta( $post->ID, 'jobtermbool', true );
        $jobtermtakeover_val  = get_post_meta( $post->ID, 'jobtermtakeover', true );
        $jobsalary_val  = get_post_meta( $post->ID, 'jobsalary', true );
        $jobpayscale_val = get_post_meta( $post->ID, 'jobpayscale', true ); 
        $jobagreement_val = get_post_meta( $post->ID, 'jobagreement', true ); 
        $jobhousing_val = get_post_meta( $post->ID, 'jobhousing', true ); 
        $jobedutitle_val = get_post_meta( $post->ID, 'jobedutitle', true );
        
        //Licenses
        $joblicense_1_val = get_post_meta( $post->ID, 'joblicense_1', true );
        $joblicenselevel_1_val = get_post_meta( $post->ID, 'joblicenselevel_1', true );
        $joblicense_2_val = get_post_meta( $post->ID, 'joblicense_2', true );
        $joblicenselevel_2_val = get_post_meta( $post->ID, 'joblicenselevel_2', true );
        $joblicense_3_val = get_post_meta( $post->ID, 'joblicense_3', true );
        $joblicenselevel_3_val = get_post_meta( $post->ID, 'joblicenselevel_3', true );
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
        <label for="joboffertype">
            <?php _e( 'Angebotstyp', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
        <!-- Action (1)neu    (2)update  (3)löschen -->
        <select name="joboffertype" class="mfbjobsapi-formelement" id="joboffertype">
                <option value="1" <?php if ( $joboffertype_val == 1 ) { echo 'selected'; } ?>>Arbeitsplatz</option>
                <option value="36" <?php if ( $joboffertype_val == 36 ) { echo 'selected'; } ?>>Fachkraft</option>
                <option value="37" <?php if ( $joboffertype_val == 37 ) { echo 'selected'; } ?>>Führungskraft</option>
                <option value="38" <?php if ( $joboffertype_val == 38 ) { echo 'selected'; } ?>>Helfer</option>
        </select><br>
        <label for="jobsocialinsurance">
            <?php _e( 'Sozialversicherungspflichtig?', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
        <!-- Action (1)neu    (2)update  (3)löschen -->
        <select name="jobsocialinsurance" class="mfbjobsapi-formelement" id="jobsocialinsurance">
                <option value="1" <?php if ( $jobsocialinsurance_val == 1 ) { echo 'selected'; } ?>>Ja</option>
                <option value="0" <?php if ( $jobsocialinsurance_val == 0 ) { echo 'selected'; } ?>>Nein</option>
        </select><br>
        <label for="jobapplication">
            <?php _e( 'Gewünschte Bewerbungsart?', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
   
        <select name="jobapplication" multiple class="mfbjobsapi-formelement" id="jobapplication">
                <option value="1" <?php if ( $jobapplication_val == 1 ) { echo 'selected'; } ?>>telefonisch</option>
                <option value="2" <?php if ( $jobapplication_val == 2 ) { echo 'selected'; } ?>>schriftlich</option>
                <option value="3" <?php if ( $jobapplication_val == 3 ) { echo 'selected'; } ?>>per E-Mail</option>
                <option value="4" <?php if ( $jobapplication_val == 4 ) { echo 'selected'; } ?>>persönlich</option>
                <option value="5" <?php if ( $jobapplication_val == 5 ) { echo 'selected'; } ?>>mit firmeneigenen Unterlagen</option>
                <option value="6" <?php if ( $jobapplication_val == 6 ) { echo 'selected'; } ?>>über Internet</option>
                <option value="7" <?php if ( $jobapplication_val == 7 ) { echo 'selected'; } ?>>über www.arbeitsagentur.de</option>
        </select><br>
        <label for="jobleadership">
            <?php _e( 'Führungsverantwortung?', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
   
        <select name="jobleadership" class="mfbjobsapi-formelement" id="jobleadership">
                <option value="1" <?php if ( $jobleadership_val == 1 ) { echo 'selected'; } ?>>keine</option>
                <option value="2" <?php if ( $jobleadership_val == 2 ) { echo 'selected'; } ?>>alle Führungsebenen</option>
                <option value="3" <?php if ( $jobleadership_val == 3 ) { echo 'selected'; } ?>>Teamleitung, Projektleitung, Gruppenleitung</option>
                <option value="4" <?php if ( $jobleadership_val == 4 ) { echo 'selected'; } ?>>Abteilungsleitung, Bereichsleitung, Ressortleitung</option>
                <option value="5" <?php if ( $jobleadership_val == 5 ) { echo 'selected'; } ?>>Geschäftsleitung, Vorstand, Betriebsleitung</option>
                <option value="6" <?php if ( $jobleadership_val == 6 ) { echo 'selected'; } ?>>Stabsfunktion</option>
        </select><br>
        <label for="jobhours">
            <?php _e( 'Wochenstunden?', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
   
        <select name="jobhours" class="mfbjobsapi-formelement" id="jobhours">
            <?php
            for ( $mfbI = 1; $mfbI < 49; $mfbI++ ) {
                ?>
                <option value="<?php echo $mfbI; ?>" <?php if ( $jobhours_val == $mfbI ) { echo 'selected'; } ?>><?php echo $mfbI; ?></option>
                <?php
            }
            ?>
        </select><br>
        <label for="jobworkingplan">
            <?php _e( 'Arbeitszeit?', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
   
        <select name="jobworkingplan" multiple class="mfbjobsapi-formelement" id="jobworkingplan">
                <option value="1" <?php if ( $jobworkingplan_val == 1 ) { echo 'selected'; } ?>>Vollzeit</option>
                <option value="3" <?php if ( $jobworkingplan_val == 3 ) { echo 'selected'; } ?>>Teilzeit - Schicht</option>
                <option value="4" <?php if ( $jobworkingplan_val == 4 ) { echo 'selected'; } ?>>Wochenende</option>
                <option value="5" <?php if ( $jobworkingplan_val == 5 ) { echo 'selected'; } ?>>Nachtarbeit</option>
                <option value="7" <?php if ( $jobworkingplan_val == 7 ) { echo 'selected'; } ?>>Teilzeit - Vormittag</option>
                <option value="8" <?php if ( $jobworkingplan_val == 8 ) { echo 'selected'; } ?>>Teilzeit - Nachmittag</option>
                <option value="9" <?php if ( $jobworkingplan_val == 9 ) { echo 'selected'; } ?>>Teilzeit - Abend</option>
                <option value="10" <?php if ( $jobworkingplan_val == 10 ) { echo 'selected'; } ?>>Teilzeit - flexibel</option>
                <option value=11 <?php if ( $jobworkingplan_val == 11 ) { echo 'selected'; } ?>>Heimarbeit</option>
                <option value="12" <?php if ( $jobworkingplan_val == 12 ) { echo 'selected'; } ?>>Schicht</option>
        </select><br>
    
        <!-- Befristung ja / nein -->
        <label for="jobtermbool">
            <?php _e( 'Befristung?', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
   
        <select name="jobtermbool"  class="mfbjobsapi-formelement" id="jobtermbool">
                <option value="2" <?php if ( $jobtermbool_val == 2 ) { echo 'selected'; } ?>>unbefristet</option>
                <option value="1" <?php if ( $jobtermbool_val == 1 ) { echo 'selected'; } ?>>befristet</option>
                
                <option value="3" <?php if ( $jobtermbool_val == 3 ) { echo 'selected'; } ?>>beides</option>
        </select><br>

        <!-- *************************************************
             ** Wenn Befristung = "befristet" oder "beides" **
             ************************************************* -->

        <!-- Wie lange befristet? -->
        <label for="jobtermlength tohide_jobtermbool">
            <?php _e( 'Befristung (1-120 Monate, nur Zahl eingeben)', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
        <input value="<?php echo $jobtermlength_val; ?>" type='text' name="jobtermlength" data-suggest='<?php echo $url; ?>' id='jobtermlength' class='form-control input-lg mfbjobsapi-formelement tohide_jobtermbool'><br>
        
        <!-- Befristungsende, Datum -->
        <label for="jobtermdate tohide_jobtermbool">
            <?php _e( 'Wann endet die Befristung? (Bis-Datum, Format 2018-12-01 einhalten)', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
        <input value="<?php echo $jobtermdate_val; ?>" type='text' name="jobtermdate" data-suggest='<?php echo $url; ?>' id='jobtermdate' class='form-control input-lg mfbjobsapi-formelement tohide_jobtermbool'><br>

        <!-- Übernahme möglich? -->
        <label for="jobtermtakeover tohide_jobtermbool">
            <?php _e( 'Befristung?', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
   
        <select name="jobtermtakeover"  class="mfbjobsapi-formelement tohide_jobtermbool" id="jobtermtakeover">
                <option value="0" <?php if ( $jobtermtakeover_val == 0 ) { echo 'selected'; } ?>>nein</option>
                <option value="1" <?php if ( $jobtermtakeover_val == 1 ) { echo 'selected'; } ?>>ja</option>
        </select><br>

        <!-- ************************************************* / -->

        <!-- Bezahlung -->
        <label for="jobsalary">
            <?php _e( 'Bezahlung', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
        <input value="<?php echo $jobsalary_val; ?>" type='text' name="jobsalary" data-suggest='<?php echo $url; ?>' id='jobsalary' class='form-control input-lg mfbjobsapi-formelement'><br>
        
        <!-- Arbeitgeber tarifgebunden? -->
        <label for="jobpayscale">
            <?php _e( 'Arbeitgeber tarifgebunden?', 'plugin-MFBJOBSAPI' ); ?>
        </label>    
        <select name="jobpayscale"  class="mfbjobsapi-formelement" id="jobpayscale">
                <option value="0" <?php if ( $jobpayscale_val == 0 ) { echo 'selected'; } ?>>nein</option>
                <option value="1" <?php if ( $jobpayscale_val == 1 ) { echo 'selected'; } ?>>ja</option>
        </select><br>

        <!-- *************************************************
             ** Wenn Arbeitgeber tarifgebunden ***************
             ************************************************* -->
        <!-- Bezahlung -->
        <label for="jobagreement tohide_jobagreement">
            <?php _e( 'Angaben zum Tarifvertrag (Pflichtfeld)', 'plugin-MFBJOBSAPI' ); ?>
            <?php _e( 'Angaben zum Tarifvertrag (Pflichtfeld)', 'plugin-MFBJOBSAPI' ); ?>
            <?php _e( 'Angaben zum Tarifvertrag (Pflichtfeld)', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
        <textarea value="<?php echo $jobagreement_val; ?>" type='text' name="jobagreement" data-suggest='<?php echo $url; ?>' id='jobagreement' class='form-control input-lg mfbjobsapi-formelement tohide_jobagreement'></textarea><br>

        <!-- ************************************************* / -->


        <!-- Unterkunft? -->
        <label for="jobhousing">
            <?php _e( 'Wird Unterkunft gestellt?', 'plugin-MFBJOBSAPI' ); ?>
        </label>    
        <select name="jobhousing"  class="mfbjobsapi-formelement" id="jobhousing">
                <option value="0" <?php if ( $jobhousing_val == 0 ) { echo 'selected'; } ?>>nein</option>
                <option value="1" <?php if ( $jobhousing_val == 1 ) { echo 'selected'; } ?>>ja</option>
        </select><br>

        <!-- Höchster Bildungsabschluss? -->
        <label for="jobhighestdegree">
            <?php _e( 'Wird Unterkunft gestellt?', 'plugin-MFBJOBSAPI' ); ?>
        </label>    
        <select name="jobhighestdegree"  class="mfbjobsapi-formelement" id="jobhighestdegree">
                <option value="0" <?php if ( $jobhousing_val == 0 ) { echo 'selected'; } ?>>nein</option>
                <option value="1" <?php if ( $jobhousing_val == 1 ) { echo 'selected'; } ?>>ja</option>
        </select><br>

        <!-- Geforderte Ausbildung  -->
        <label for="jobedutitle">
            <?php _e( 'Angaben zur Ausbildung', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
        <input value="<?php echo $jobedutitle_val; ?>" type='text' name="jobedutitle" data-suggest='<?php echo $url; ?>' id='jobedutitle' class='form-control input-lg mfbjobsapi-formelement'><br>


         <!-- Name des Ausbildungsberufes  -->
        <label for="jobeduname">
            <?php _e( 'ID des Ausbildungsberufes (Suchen über Texteingabe)', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
        <input value="<?php echo $jobeduname_val; ?>" type='text' name="jobeduname" data-suggest='<?php echo $url; ?>' id='jobeduname' class='form-control input-lg mfbjobsapi-formelement'><br><div class='searchformresult2'></div><br>
        

        <!-- Lizenzen (ID)  -->
        <div class="container-fluid">
            <div class="row">
                <div class="col1">
                    <label for="joblicense_1 joblicense">
                        <?php _e( '<h2>Kenntnisse & Fähigkeiten</h2>', 'plugin-MFBJOBSAPI' ); ?>
                        <?php _e( 'Kenntnisse & Fähigkeiten - #1', 'plugin-MFBJOBSAPI' ); ?>
                        <p>Wenn eine Reige entfernt werden soll, einfach das Texteingabefeld leeren und das Joblisting erneut speichern.</p>
                    </label> 
                </div>
            </div>
            <div class="row">
                <div class="col2">
                   <input value="<?php echo $joblicense_1_val; ?>" type='text' name="joblicense_1" data-suggest='<?php echo $url; ?>' data-license-result="licenseresult_1" id='joblicense_1' class='form-control input-lg mfbjobsapi-formelement joblicense'>
                </div>
               <div class="col2">
                    <select name="joblicenselevel_1"  class="mfbjobsapi-formelement" id="joblicenselevel_1">
                        <option value="1" <?php if ( $joblicenselevel_1_val == 1 ) { echo 'selected'; } ?>>Wünschenswert</option>
                        <option value="2" <?php if ( $joblicenselevel_1_val == 2 ) { echo 'selected'; } ?>>Zwingend erforderlich</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col1">
                    <div class='licenseresult licenseresult_1'></div>
                </div>
            </div>
            <div class="row tohide">
                <div class="col1">
                    <a href="#" class="addrow btn btn-primary" data-load="#lic2">Reihe hinzufügen</a> 
                </div>
            </div>
        </div>
   
        
        <div class="container-fluid" id="lic2">
            <div class="row">
                <div class="col1">
                    <label for="joblicense_2 joblicense">
                        
                        <?php _e( 'Kenntnisse & Fähigkeiten - #2', 'plugin-MFBJOBSAPI' ); ?>
                    </label> 
                </div>
            </div>
            <div class="row">
                <div class="col2">
                   <input value="<?php echo $joblicense_2_val; ?>" type='text' name="joblicense_2" data-suggest='<?php echo $url; ?>' data-license-result="licenseresult_2" id='joblicense_2' class='form-control input-lg mfbjobsapi-formelement joblicense'>
                </div>
               <div class="col2">
                    <select name="joblicenselevel_2"  class="mfbjobsapi-formelement" id="joblicenselevel_2">
                        <option value="1" <?php if ( $joblicenselevel_2_val == 1 ) { echo 'selected'; } ?>>Wünschenswert</option>
                        <option value="2" <?php if ( $joblicenselevel_2_val == 2 ) { echo 'selected'; } ?>>Zwingend erforderlich</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col1">
                    <div class='licenseresult licenseresult_1'></div>
                    <div class='licenseresult licenseresult_2'></div>
                </div>
            </div>
        </div>
        


        <div class="container-fluid" id="lic3">
            <div class="row">
                <div class="col1">
                    <label for="joblicense_3 joblicense">
                        <?php _e( 'Kenntnisse & Fähigkeiten - #3', 'plugin-MFBJOBSAPI' ); ?>
                    </label> 
                </div>
            </div>
            <div class="row">
                <div class="col2">
                   <input value="<?php echo $joblicense_3_val; ?>" type='text' name="joblicense_3" data-suggest='<?php echo $url; ?>' data-license-result="licenseresult_3" id='joblicense_3' class='form-control input-lg mfbjobsapi-formelement joblicense'>
                </div>
               <div class="col2">
                    <select name="joblicenselevel_3"  class="mfbjobsapi-formelement" id="joblicenselevel_3">
                        <option value="1" <?php if ( $joblicenselevel_3_val == 1 ) { echo 'selected'; } ?>>Wünschenswert</option>
                        <option value="2" <?php if ( $joblicenselevel_3_val == 2 ) { echo 'selected'; } ?>>Zwingend erforderlich</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col1">
                    <div class='licenseresult licenseresult_1'></div>
                    <div class='licenseresult licenseresult_2'></div>
                    <div class='licenseresult licenseresult_3'></div>
                </div>
            </div>
        </div>
        <?php
    }
}