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
    add_action( 'admin_menu', 'setupBackend' );
}


/**
 * Plugin_MFBJOBSAPI Class for Backendfunctions
 *
 * @package Plugin MFBJOBSAPI
 */
class MFBJOBSAPI_Backend {
    
    protected function connectToDB() {
		
		$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
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
        add_action( 'init',      array( $this, 'sessionstart'         ) );
        add_action( 'pre_post_update',  array( $this,'editpost') );
        add_filter('redirect_post_location', array( $this,'update_post_redirect'));
       // add_action( 'admin_print_scripts', array( $this, 'admin_footer' ), 1000 );
        
        if ( isset($_GET['errors']) ) {
            add_action('admin_notices', array( $this, 'sample_admin_notice__error') );
        } 
              /* menu */
       
        add_menu_page(
            __( 'Jobexport', 'plugin-MFBJOBSAPI' ),
            __( 'Jobexport','plugin-MFBJOBSAPI' ),
            'manage_options',
            'jobexport',
            array(
                $this,
                'setupJobexports'
            ),
            10
        );
        
        
    }

    
    
    
    public function sample_admin_notice__error() {
	   $class = 'notice notice-error';
	   $message = __( $_GET['errors'], 'plugin-MFBJOBSAPI' );

	   printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message  ); 
    }
     public function update_post_redirect($location){
        $syndicated = isset($_POST['syndicated']) ? $_POST['syndicated'] : 0;
        if($syndicated == true) {
            $location .= '&syndicated=1&errors='.$_POST['errors'];
         } 
        return $location;
     }
    
  
    /*
    * checks for dependencies on post save
    */
    public function editpost( $post_id ) {
            $post_type = get_post_type($post_id);
            $counter = 0;
            $error = ""; 
            $etype = ""; 
        
            //prüfe Stellen gegen Orte
            for ( $ival=1; $ival<11; $ival++) {
                if ( ! empty ($_POST['joblocationplz_'.$ival] ) ) {
                    $counter++;
                } 
            }
            // A function to perform actions any time any post changes status.
            if ( $post_type == "job_listing" ) {
                
               //prüfe Stellen gegen Orte
               if ( isset($_POST['jobfullfill']) && $_POST['jobfullfill'] > $counter ) {
                    $error .= "<p>Korrigieren Sie die Anzahl der Stellen / Arbeitsorte.</p>";
               }  
               
                //Neuanlage? dann nur Zustand = E jobaction / jobzustand (derzeit sind alle auf E)
                $jobbezeichnung_val[0] = sanitize_text_field( $_POST['jobbezeichnung'] );
        $jobbezeichnung_val[1] = sanitize_text_field( $_POST['jobbezeichnung_2'] );
        $jobbezeichnung_val[2] = sanitize_text_field( $_POST['jobbezeichnung_3'] );
                
                
                $bid1 = MFBJOBSAPI_Backend::get_job_id( $jobbezeichnung_val[0] );
                $bid2 = MFBJOBSAPI_Backend::get_job_id( $jobbezeichnung_val[1] );
                $bid3 = MFBJOBSAPI_Backend::get_job_id( $jobbezeichnung_val[2] );
                
                
                $hs1 = MFBJOBSAPI_Backend::get_jobstate_from_db( $jobbezeichnung_val[0], "hs" );
                $hs2 = MFBJOBSAPI_Backend::get_jobstate_from_db( $jobbezeichnung_val[1], "hs" );
                $hs3 = MFBJOBSAPI_Backend::get_jobstate_from_db( $jobbezeichnung_val[2], "hs" );
                
                $typ1 = MFBJOBSAPI_Backend::get_jobstate_from_db( $jobbezeichnung_val[0], "typ" );
                $typ2 = MFBJOBSAPI_Backend::get_jobstate_from_db( $jobbezeichnung_val[1], "typ" );
                $typ3 = MFBJOBSAPI_Backend::get_jobstate_from_db( $jobbezeichnung_val[2], "typ" );
                
                $quali1 = MFBJOBSAPI_Backend::get_job_quali( $bid1 );
                $quali2 = MFBJOBSAPI_Backend::get_job_quali( $bid2 );
                $quali2 = MFBJOBSAPI_Backend::get_job_quali( $bid3 );
                
                /*
                    Hochschulberuf?  MFBJOBSAPI_Backend::get_jobstate_from_db($jobbezeichnung_val[0],"hs"); 
                    if hs { jobedudegree > 1 }
                */
                
                if ( $jobbezeichnung_val[0] != "" && $hs1 = "ja" && $_POST['jobedudegree1'] == 1 ) {
                    $error .= "<p>Hochschulabschlussart des Hauptberufes fehlt.</p>";
                }
                if ( $jobbezeichnung_val[1] != "" && $hs2 == "ja" && $_POST['jobedudegree2'] == 1 ) {
                    $error .= "<p>Hochschulabschlussart des 2.Berufes fehlt.</p>";
                }
                if ( $jobbezeichnung_val[2] != "" && $hs3 = "ja" &&  $_POST['jobedudegree3'] == 1 ) {
                    $error .= "<p>Hochschulabschlussart des 3.Berufes fehlt.</p>";
                }
               
                /*
                    Helferstelle?  MFBJOBSAPI_Backend::get_jobstate_from_db($jobbezeichnung_val[0],"typ"); 
                    if typ == T && quali == 1 
                */
               /* if ( $_POST['joboffertype'] == 38 ) {
                    if ( $typ1 != "T" && $quali1 > 1 ) {
                         $error .= "<p>Hochschulabschlussart des Hauptberufes zu hochwertig</p>";
                    }
                }*/
            }
        
        if ( $error != "") {
           $_POST['syndicated'] = true;  
           $_POST['errors'] = urlencode($error);      
        } 
        
        
    }
    
    
    /*
    * returns jobstates, to check agains allowance of the chosen jobtitle 
    @returns: int
    */
    public function get_jobstate_from_db($job,$att) {
    
        $query = "SELECT * FROM `jobs_vam` WHERE `bezeichnung` ='".$job."'";
        $mysqli = MFBJOBSAPI_Backend::connectToDB();
		//echo $query;  
		if ($result = $mysqli->query($query)) {
			
			/* create single article-object and push to array */
			 while($obj = $result->fetch_object()){ 
                 if ( $att == "zustand") {
                     return $obj->zustand;
                 }
                if ( $att == "ebene") {
                     return $obj->ebene;
                 }
                 if ( $att == "hs") {
                     return $obj->hochschulberuf;
                 }
				 if ( $att == "typ") {
                     return $obj->typ;
                 }
			 }
		}
       //return false;
    }
  
        /*
    * returns job niveau
    @returns: int 
    */
    public function get_job_quali( $jobid ) {
        
        $query = "SELECT * FROM `niveau_vam` WHERE `jobid` = ".$jobid;
       
        $mysqli = MFBJOBSAPI_Backend::connectToDB();
		if ($result = $mysqli->query($query)) {
			
			/* create single article-object and push to array */
			 while($obj = $result->fetch_object()){ 
              return $obj->niveau;
			 }
		}
    }
    
    
    /*
    * returns job niveau
    @returns: int 
    */
    public function get_job_id( $jobtitle ) {
        
        $query = "SELECT * FROM `jobs_vam` WHERE `bezeichnung` = '".$jobtitle."'";
       
        $mysqli = MFBJOBSAPI_Backend::connectToDB();
		if ($result = $mysqli->query($query)) {
			
			/* create single article-object and push to array */
			 while($obj = $result->fetch_object()){ 
              return $obj->_id;
			 }
		}
    }
    
    
       public function get_komp_id_by_title( $jobtitle ) {

         
        $query = "SELECT * FROM `komp_vam` WHERE `bezeichnung` = '".$jobtitle."'";
      
        $mysqli = MFBJOBSAPI_Backend::connectToDB();
		if ($result = $mysqli->query($query)) {
			
			/* create single article-object and push to array */
			 while($obj = $result->fetch_object()){ 
              return $obj->_id;
			 }
		}
    }
    
     /*
    * returns job niveau
    @returns: int 
    */
    public function get_job_title( $jobid ) {
        
        $query = "SELECT * FROM `jobs_vam` WHERE `_id` = ".$jobid;
       
        $mysqli = MFBJOBSAPI_Backend::connectToDB();
		if ($result = $mysqli->query($query)) {
			
			/* create single article-object and push to array */
			 while($obj = $result->fetch_object()){ 
              return $obj->bezeichnung;
			 }
		}
    }
    
    public function get_komp_title_by_id( $jobid ) {

         
        $query = "SELECT * FROM `komp_vam` WHERE `_id` = '".$jobid."'";
      
        $mysqli = MFBJOBSAPI_Backend::connectToDB();
		if ($result = $mysqli->query($query)) {
			
			/* create single article-object and push to array */
			 while($obj = $result->fetch_object()){ 
              return $obj->bezeichnung;
			 }
		}
    }
    public function setupJobexports() {
        echo do_shortcode('[MFBJOBSAPI_xmljobs]');
        /* menu */
        $filename = urlencode( str_replace ('../../','',$_SESSION['filename'] ) );
        echo "<h1>Jobexport</h1>";
        echo "<a href='".get_bloginfo('url')."/jobs.php?filename=".$filename."' class='button btn-default btn-lg' target='_blank'>Jobdatei (XML) herunterladen</a>";
         
        session_destroy();
    }
     
    
    /*
    * returns lincences 
    @returns: object connectToDB   
    */
    public function get_komps_from_db() {
        $query = "SELECT * FROM `komp_vam`";
        $mysqli = MFBJOBSAPI_Backend::connectToDB();
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
                __( 'Genaue Jobbezeichnung und Statusdaten des Angebotes', 'plugin-MFBJOBSAPI' ),
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
        $jobbezeichnung_val[0] = sanitize_text_field( $_POST['jobbezeichnung'] );
        $jobbezeichnung_val[1] = sanitize_text_field( $_POST['jobbezeichnung_2'] );
        $jobbezeichnung_val[2] = sanitize_text_field( $_POST['jobbezeichnung_3'] );
      
        $jobstatus_val = $_POST['jobstatus'];
        $jobaction_val = $_POST['jobaction'];
        $jobsocialinsurance_val = $_POST['jobsocialinsurance'];
        $job_id_val = $_POST['job_id'];
        $job_bkz_val = $_POST['job_bkz'];
        $joboffertype_val = $_POST['joboffertype'];
        $jobapplication_val = $_POST['jobapplication'];
        $jobleadership_val = $_POST['jobleadership'];
        $jobhours_val = $_POST['jobhours'];
        $jobworkingplan_val = $_POST['jobworkingplan'];
        $jobapplicationstart = $_POST['jobapplicationstart'];
        $jobapplicationend = $_POST['jobapplicationend'];
        $jobstart = $_POST['jobstart'];
        $jobend = $_POST['jobend'];
        $jobtermlength_val = $_POST['jobtermlength'];
        $jobtermdate_val = $_POST['jobtermdate'];
        $jobtermtakeover_val = $_POST['jobtermtakeover'];
        $jobtermbool_val  = $_POST['jobtermbool'];            
        $jobagreement_val  = $_POST['jobagreement'];
        $jobsalary_val  = $_POST['jobsalary'];
        $jobpayscale_val  = $_POST['jobpayscale'];
        $jobagreement_val  = $_POST['jobagreement'];
        $jobhousing_val  = $_POST['jobhousing'];
        $jobedutitle_val  = $_POST['jobedutitle'];
        $joblicense_1  = MFBJOBSAPI_Backend::get_komp_id_by_title($_POST['joblicense_1']);
        $joblicenselevel_1_val = $_POST['joblicenselevel_1'];
        $joblicense_2  = MFBJOBSAPI_Backend::get_komp_id_by_title($_POST['joblicense_2']);
        $joblicenselevel_2_val = $_POST['joblicenselevel_2'];
        $joblicense_3  = MFBJOBSAPI_Backend::get_komp_id_by_title($_POST['joblicense_3']);
        $joblicenselevel_3_val = $_POST['joblicenselevel_3'];
        $jobdegreemusthave_val = $_POST['jobdegreemusthave'];
        $jobhighestdegree_val = $_POST['jobhighestdegree'];
        $jobemployers_val = $_POST['joobemployers'];
        $jobbudget_val  = $_POST['jobbudget'];
        $jobleadershipex_val = $_POST['jobleadershipex'];
        $jobleadershiptype_val = $_POST['jobleadershiptype'];
        $jobauth_val = $_POST['jobauth'];
        $jobeduname_val = $_POST['jobeduname'];
        
        $jobedudegree_val_1 = $_POST['jobedudegree1']; 
        $jobedudegree_val_2 = $_POST['jobedudegree2']; 
        $jobedudegree_val_3 = $_POST['jobedudegree3']; 
        
        $jobknow_val= $_POST['jobknow'];
        $jobcertfreetitle_val = $_POST['jobcertfreetitle'];
        $jobcertfreedesc_val = $_POST['jobcertfreedesc'];
        $jobpkw_val = $_POST['jobpkw'];
        $joblkw_val = $_POST['joblkw'];
        $jobfullfill_val = $_POST['jobfullfill'];
        $jobknowledge_val = MFBJOBSAPI_Backend::get_job_id($_POST['jobknowledge']);
        $jobknowledgehs_val = $_POST['jobknowledgehs'];
        $jobknowledgeother_val = $_POST['jobknowledgeother'];
        
        $joblang_1 = $_POST['joblang_1'];
        $joblang_2 = $_POST['joblang_2'];
        $joblang_3 = $_POST['joblang_3'];
        $joblangval_1 = $_POST['joblangval_1'];
        $joblangval_2 = $_POST['joblangval_2'];
        $joblangval_3 = $_POST['joblangval_3'];
        // Update the meta fields.
        update_post_meta( $post_id, 'jobknowledge', $jobknowledge_val );
        update_post_meta( $post_id, 'jobbezeichnung', $jobbezeichnung_val[0] );
        update_post_meta( $post_id, 'jobbezeichnung_2', $jobbezeichnung_val[1] );
        update_post_meta( $post_id, 'jobbezeichnung_3', $jobbezeichnung_val[2] );
        update_post_meta( $post_id, 'job_id', $job_id_val );
        update_post_meta( $post_id, 'job_bkz', $job_bkz_val );
        update_post_meta( $post_id, 'jobstatus', $jobstatus_val );
        update_post_meta( $post_id, 'jobaction', $jobaction_val );
        update_post_meta( $post_id, 'jobsocialinsurance', $jobsocialinsurance_val );
        update_post_meta( $post_id, 'joboffertype', $joboffertype_val );
        update_post_meta( $post_id, 'jobapplication', implode(',',$jobapplication_val) );
        update_post_meta( $post_id, 'jobleadership', $jobleadership_val );
        update_post_meta( $post_id, 'jobhours', $jobhours_val );
        update_post_meta( $post_id, 'jobworkingplan', implode(',',$jobworkingplan_val) );
        update_post_meta( $post_id, 'jobtermlength', $jobtermlength_val );
        update_post_meta( $post_id, 'jobtermdate', $jobtermdate_val );
        update_post_meta( $post_id, 'jobtermbool', $jobtermbool_val );
        update_post_meta( $post_id, 'jobtermtakeover', $jobtermtakeover_val );
        update_post_meta( $post_id, 'jobsalary', $jobsalary_val );
        update_post_meta( $post_id, 'jobpayscale', $jobpayscale_val );
        update_post_meta( $post_id, 'jobagreement', $jobagreement_val );
        update_post_meta( $post_id, 'jobhousing', $jobhousing_val );
        update_post_meta( $post_id, 'jobedutitle', $jobedutitle_val );
        update_post_meta( $post_id, 'jobhighestdegree', $jobhighestdegree_val );
        update_post_meta( $post_id, 'jobdegreemusthave', $jobdegreemusthave_val );
         update_post_meta( $post_id, 'jobfullfill', $jobfullfill_val );
        update_post_meta( $post_id, 'jobstart', $jobstart );
         update_post_meta( $post_id, 'jobend', $jobend );
        //Licenses
        update_post_meta( $post_id, 'joblicense_1', $joblicense_1 );
        update_post_meta( $post_id, 'joblicenselevel_1', $joblicenselevel_1_val );
        update_post_meta( $post_id, 'joblicense_2', $joblicense_2 );
        update_post_meta( $post_id, 'joblicenselevel_2', $joblicenselevel_2_val );
        update_post_meta( $post_id, 'joblicense_3', $joblicense_3 );
        update_post_meta( $post_id, 'joblicenselevel_3', $joblicenselevel_3_val );
        
        update_post_meta( $post_id, 'jobemployers', $jobemployers_val );
        update_post_meta( $post_id, 'jobbudget', $jobbudget_val );
        update_post_meta( $post_id, 'jobleadershipex', $jobleadershipex_val );
        update_post_meta( $post_id, 'jobleadershiptype', $jobleadershiptype_val );
        update_post_meta( $post_id, 'jobauth', $jobauth_val );
        update_post_meta( $post_id, 'jobeduname', $jobeduname_val );
        update_post_meta( $post_id, 'jobedutit', $_POST['jobedutit'] );
        update_post_meta( $post_id, 'jobedudegree1', $jobedudegree_val_1 );
        
        update_post_meta( $post_id, 'jobedudegree2', $jobedudegree_val_2 );
        
        update_post_meta( $post_id, 'jobedudegree3', $jobedudegree_val_3 );
        update_post_meta( $post_id, 'jobapplicationstart', $jobapplicationstart );
        update_post_meta( $post_id, 'jobapplicationend', $jobapplicationend );
        
        update_post_meta( $post_id, 'jobcertfreetitle', $jobcertfreetitle_val);
        update_post_meta( $post_id, 'jobcertfreedesc', $jobcertfreedesc_val );
        update_post_meta( $post_id, 'jobpkw', $jobpkw_val);
        update_post_meta( $post_id, 'joblkw', $joblkw_val );
        update_post_meta( $post_id, 'jobknow', $jobknow_val ); 
        update_post_meta( $post_id, 'jobknowledgehs', $jobknowledgehs_val ); 
        update_post_meta( $post_id, 'jobknowledgeother', $jobknowledgeother_val ); 
        update_post_meta( $post_id, 'joblang_1', $joblang_1 ); 
        update_post_meta( $post_id, 'joblang_2', $joblang_2 ); 
        update_post_meta( $post_id, 'joblang_3', $joblang_3 );
        update_post_meta( $post_id, 'joblangval_1', $joblangval_1 ); 
        update_post_meta( $post_id, 'joblangval_2', $joblangval_2 ); 
        update_post_meta( $post_id, 'joblangval_3', $joblangval_3 ); 
        for ( $ival=1; $ival<11; $ival++) {
            $joblocationplz[$ival] = $_POST['joblocationplz_'.$ival];
            $joblocationcity[$ival] = $_POST['joblocationcity_'.$ival];
            $joblocationstreet[$ival] = $_POST['joblocationstreet_'.$ival];
            $joblocationregion[$ival] = $_POST['joblocationregion_'.$ival];
            update_post_meta( $post_id,'joblocationplz_'.$ival,$joblocationplz[$ival]);
            update_post_meta( $post_id,'joblocationcity_'.$ival,$joblocationcity[$ival]);
            update_post_meta( $post_id,'joblocationstreet_'.$ival,$joblocationstreet[$ival]);
            update_post_meta( $post_id,'joblocationregion_'.$ival,$joblocationregion[$ival]);
            if ( empty ($_POST['joblocationplz_'.$ival] )) {
                delete_post_meta($post_id, 'joblocationplz_'.$ival, $_POST['joblocationplz_'.$ival]);
                delete_post_meta($post_id, 'joblocationcity_'.$ival, $_POST['joblocationcity_'.$ival]);
                delete_post_meta($post_id, 'joblocationregion_'.$ival, $_POST['joblocationregion_'.$ival]);
                delete_post_meta($post_id, 'joblocationstreet_'.$ival, $_POST['joblocationstreet_'.$ival]);
            }
        }
        for ( $ival=1; $ival<6; $ival++) {
            $jobskillsoft[$ival] =  $_POST['jobskillsoft_'.$ival];
            update_post_meta( $post_id,'jobskillsoft_'.$ival,$jobskillsoft[$ival]);
            $jobskill[$ival] =  $_POST['jobskill_'.$ival];
            update_post_meta( $post_id,'jobskill_'.$ival,$jobskill[$ival]);
            $jobskillvalue[$ival] =  $_POST['jobskillvalue_'.$ival];
            update_post_meta( $post_id,'jobskillvalue_'.$ival,$jobskillvalue[$ival]);
            
        }
        for ( $ival=1; $ival<4; $ival++) {
            $jobskilldrive[$ival] = $_POST['jobskilldrive_'.$ival];
            update_post_meta( $post_id,'jobskilldrive_'.$ival,$jobskilldrive[$ival]);
            $jobskilldrivevalue[$ival] = $_POST['jobskilldrivevalue_'.$ival];
            update_post_meta( $post_id,'jobskilldrivevalue_'.$ival,$jobskilldrivevalue[$ival]);
            
        }
        
             
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
        $jobbezeichnung_val[0] = get_post_meta( $post->ID, 'jobbezeichnung', true );
        $jobbezeichnung_val[1] = get_post_meta( $post->ID, 'jobbezeichnung_2', true );
        $jobbezeichnung_val[2] = get_post_meta( $post->ID, 'jobbezeichnung_3', true );
      
        $jobstatus_val = get_post_meta( $post->ID, 'jobstatus', true );
        $jobaction_val = get_post_meta( $post->ID, 'jobaction', true );
        $jobapplicationstart = get_post_meta( $post->ID, 'jobapplicationstart', true );
        $jobapplicationend = get_post_meta( $post->ID, 'jobapplicationend', true );
        $_SESSION['jobapplicationend'] = get_post_meta( $post->ID, 'jobapplicationend', true );
        $job_id_val = get_post_meta( $post->ID, 'job_id', true );
        $job_bkz_val = get_post_meta( $post->ID, 'job_bkz', true );
        $joboffertype_val = get_post_meta( $post->ID, 'joboffertype', true );
        $jobsocialinsurance_val  = get_post_meta( $post->ID, 'jobsocialinsurance', true );
        $jobapplication_val  = get_post_meta( $post->ID, 'jobapplication', true );
        $jobapplication_val = explode(',',$jobapplication_val);
        $jobleadership_val  = get_post_meta( $post->ID, 'jobleadership', true );
        $jobhours_val  = get_post_meta( $post->ID, 'jobhours', true );
        $jobworkingplan_val  = get_post_meta( $post->ID, 'jobworkingplan', true );
        $jobworkingplan_val = explode(',',$jobworkingplan_val);
        $jobtermlength_val = get_post_meta( $post->ID, 'jobtermlength', true );
        $jobtermdate_val = get_post_meta( $post->ID, 'jobtermdate', true );
        $jobtermbool_val = get_post_meta( $post->ID, 'jobtermbool', true );
        $jobtermtakeover_val  = get_post_meta( $post->ID, 'jobtermtakeover', true );
        $jobsalary_val  = get_post_meta( $post->ID, 'jobsalary', true );
        $jobpayscale_val = get_post_meta( $post->ID, 'jobpayscale', true ); 
        $jobagreement_val = get_post_meta( $post->ID, 'jobagreement', true ); 
        $jobhousing_val = get_post_meta( $post->ID, 'jobhousing', true ); 
        $jobedutitle_val = get_post_meta( $post->ID, 'jobedutitle', true );
        $jobhighestdegree_val  = get_post_meta( $post->ID, 'jobhighestdegree', true );
        $jobdegreemusthave_val = get_post_meta( $post->ID, 'jobdegreemusthave', true );
        $jobeduname_val = get_post_meta( $post->ID, 'jobeduname', true );
        $jobedutit_val = get_post_meta( $post->ID, 'jobedutit', true );
        $jobedudegree_val_1 = get_post_meta( $post->ID, 'jobedudegree1', true );
        $jobedudegree_val_2 = get_post_meta( $post->ID, 'jobedudegree2', true );
        $jobedudegree_val_3 = get_post_meta( $post->ID, 'jobedudegree3', true );
        $jobpkw_val = get_post_meta( $post->ID, 'jobpkw', true );
        $joblkw_val = get_post_meta( $post->ID, 'joblkw', true );
        $jobknow_val = get_post_meta( $post->ID, 'jobknow', true );
        
        
        $joblang_1 = get_post_meta( $post->ID, 'joblang_1', true );
        $joblang_2 = get_post_meta( $post->ID, 'joblang_2', true );
        $joblang_2 = get_post_meta( $post->ID, 'joblang_2', true );
        $joblangval_1 = get_post_meta( $post->ID, 'joblangval_1', true );
        $joblangval_2 = get_post_meta( $post->ID, 'joblangval_2', true );
        $joblangval_3 = get_post_meta( $post->ID, 'joblangval_3', true );
        
        //Licenses
        $joblicense_1 = MFBJOBSAPI_Backend::get_komp_title_by_id(get_post_meta( $post->ID, 'joblicense_1', true ));
        $joblicenselevel_1_val = get_post_meta( $post->ID, 'joblicenselevel_1', true );
        $joblicense_2 = MFBJOBSAPI_Backend::get_komp_title_by_id( get_post_meta( $post->ID, 'joblicense_2', true ));
        $joblicenselevel_2_val = get_post_meta( $post->ID, 'joblicenselevel_2', true );
        $joblicense_3 = MFBJOBSAPI_Backend::get_komp_title_by_id( get_post_meta( $post->ID, 'joblicense_3', true ));
        $joblicenselevel_3_val = get_post_meta( $post->ID, 'joblicenselevel_3', true );

        // Display the form, using the current value.
        $jobcertfreetitle_val = get_post_meta( $post->ID, 'jobcertfreetitle', true );
        $jobfullfill_val = get_post_meta( $post->ID, 'jobfullfill', true );
        $jobcertfreedesc_val = get_post_meta( $post->ID, 'jobcertfreedesc', true );
        $jobemployers_val = get_post_meta( $post->ID, 'jobemployers', true );
        $jobbudget_val = get_post_meta( $post->ID, 'jobbudget', true );
        $jobleadershipex_val = get_post_meta( $post->ID, 'jobleadershipex', true );
        $jobleadershiptype_val = get_post_meta( $post->ID, 'jobleadershiptype', true );
        $jobauth_val = get_post_meta( $post->ID, 'jobauth', true );
        ?>
        <?php for ( $ival=1; $ival<11; $ival++) {
            $joblocationplz[$ival] = get_post_meta( $post->ID, 'joblocationplz_'.$ival, true );
            $joblocationcity[$ival] = get_post_meta( $post->ID, 'joblocationcity_'.$ival, true );
            $joblocationstreet[$ival] = get_post_meta( $post->ID, 'joblocationstreet_'.$ival, true );
            $joblocationregion[$ival] = get_post_meta( $post->ID, 'joblocationregion_'.$ival, true );
        }
        ?>
        <?php for ( $ival=1; $ival<6; $ival++) {
            $jobskillsoft[$ival] = get_post_meta( $post->ID, 'jobskillsoft_'.$ival, true );
            $jobskill[$ival] = get_post_meta( $post->ID, 'jobskill_'.$ival, true );
            $jobskillvalue[$ival] = get_post_meta( $post->ID, 'jobskillvalue_'.$ival, true );
        }
        ?>
        <?php for ( $ival=1; $ival<4; $ival++) {
            $jobskilldrive[$ival] = get_post_meta( $post->ID, 'jobskilldrive_'.$ival, true );
            $jobskilldrivevalue[$ival] = get_post_meta( $post->ID, 'jobskilldrivevalue_'.$ival, true );
        }
        $jobknowledge_val= MFBJOBSAPI_Backend::get_job_title(get_post_meta( $post->ID, 'jobknowledge', true ));
        $jobknowledgehs_val = get_post_meta( $post->ID, 'jobknowledgehs', true );
        $jobknowledgeother_val = get_post_meta( $post->ID, 'jobknowledgeother', true );
        $bid1 = MFBJOBSAPI_Backend::get_job_id( $jobbezeichnung_val[0] );
        $bid2 = MFBJOBSAPI_Backend::get_job_id( $jobbezeichnung_val[1] );
        $bid3 = MFBJOBSAPI_Backend::get_job_id( $jobbezeichnung_val[2] );
                
                
        $hs1 = MFBJOBSAPI_Backend::get_jobstate_from_db( $jobbezeichnung_val[0], "hs" );
        $hs2 = MFBJOBSAPI_Backend::get_jobstate_from_db( $jobbezeichnung_val[1], "hs" );
        $hs3 = MFBJOBSAPI_Backend::get_jobstate_from_db( $jobbezeichnung_val[2], "hs" );
                
        $typ1 = MFBJOBSAPI_Backend::get_jobstate_from_db( $jobbezeichnung_val[0], "typ" );
        $typ2 = MFBJOBSAPI_Backend::get_jobstate_from_db( $jobbezeichnung_val[1], "typ" );
        $typ3 = MFBJOBSAPI_Backend::get_jobstate_from_db( $jobbezeichnung_val[2], "typ" );
                
        $quali1 = MFBJOBSAPI_Backend::get_job_quali( $bid1 );
        $quali2 = MFBJOBSAPI_Backend::get_job_quali( $bid2 );
        $quali2 = MFBJOBSAPI_Backend::get_job_quali( $bid3 );
        $jobstart = get_post_meta( $post->ID, 'jobstart', true );
        $jobend = get_post_meta( $post->ID, 'jobend', true );
        
        
        ?>
       <script>
           window.setTimeout( function() { 
               jQuery('#jobapplicationend').val('<?php echo $jobapplicationend; ?>'); 
               jQuery('#jobapplicationstart').val('<?php echo $jobapplicationstart; ?>'); 
               jQuery('#jobstart').val('<?php echo $jobstart; ?>'); 
               jQuery('#jobend').val('<?php echo $jobend; ?>'); 
           },2000);

    </script>
        <h2 class="subhead">Stelleninfo</h2>
        <div class="onoffbox">
            <div class="boxtitle"><h3>Art des Jobs und Jobauswahl (bis zu 3 Jobs) <span class="onoffbtn">Öffnen / Schließen</span></h3></div>
            <div class="boxcontent tohide">
                <label for="joboffertype" class="boxlabel">
                    <?php _e( 'Angebotstyp, Änderungen eines bestehenden Eintrages löscht die Berufsauswahl', 'plugin-MFBJOBSAPI' ); ?>
                </label> 
                <p>Je nach Auswahl des Angebotstyps ändern sich die zur Verfügung stehenden Berufe.</p>
                <!-- Action (1)neu    (2)update  (3)löschen -->
                <select name="joboffertype" class="mfbjobsapi-formelement" id="joboffertype">
                        <option value="1" <?php if ( $joboffertype_val == 1 ) { echo 'selected'; } ?>>Arbeitsplatz</option>
                        <option value="36" <?php if ( $joboffertype_val == 36 ) { echo 'selected'; } ?>>Fachkraft</option>
                        <option value="37" <?php if ( $joboffertype_val == 37 ) { echo 'selected'; } ?>>Führungskraft</option>
                        <option value="38" <?php if ( $joboffertype_val == 38 ) { echo 'selected'; } ?>>Helfer</option>
                </select><br>
                <label for="searchinput_KEY" class="boxlabel">
                    <?php _e( 'Job 1 - Mindest. 4 Zeichen (System sucht automatisch)', 'plugin-MFBJOBSAPI' ); ?>
                    <?php _e( '<br>Ändern Sie den Angebotstyp, um die Auswahl an Jobs zu verändern', 'plugin-MFBJOBSAPI' ); ?>
                </label>
                <input value="<?php echo $jobbezeichnung_val[0]; ?>" type='text' name="jobbezeichnung" data-num="1" data-suggest='<?php echo $url; ?>' id='searchinput_KEY' class='form-control input-lg mfbjobsapi-formelement box_1 elmchanger' data-result="searchformresult.box_1" data-typ="<?php echo $typ1; ?>" data-quali="<?php echo $quali1; ?>" id="jobbezeichnung" data-job-titlelm="box_1"><br><div class='searchformresult box_1'></div>
               
                <span class="tocheck zustand1"><?php echo MFBJOBSAPI_Backend::get_jobstate_from_db($jobbezeichnung_val[0],"zustand"); ?> </span>
                <span class="tocheck quali1">
                    <?php echo MFBJOBSAPI_Backend::get_job_quali( MFBJOBSAPI_Backend::get_job_id($jobbezeichnung_val[0]) ); ?></span>
                <span class="tocheck hs1"><?php echo MFBJOBSAPI_Backend::get_jobstate_from_db($jobbezeichnung_val[0],"hs"); ?></span>
                <span class="tocheck ebene1"><?php echo MFBJOBSAPI_Backend::get_jobstate_from_db($jobbezeichnung_val[0],"ebene"); ?></span>
                
                <label for="searchinput_KEY2" class="boxlabel">
                    <?php _e( 'Job 2', 'plugin-MFBJOBSAPI' ); ?>
                </label>
                <input value="<?php echo $jobbezeichnung_val[1]; ?>" type='text' id="jobbezeichnung_2" name="jobbezeichnung_2" data-num="2" data-suggest='<?php echo $url; ?>' id='searchinput_KEY2' class='form-control input-lg mfbjobsapi-formelement box_2 elmchanger' data-result="searchformresult.box_2"  data-typ="all" data-quali="all"  data-job-titlelm="box_2"><br><div class='searchformresult box_2'></div>
                
                
                <span class="tocheck zustand2"><?php echo MFBJOBSAPI_Backend::get_jobstate_from_db($jobbezeichnung_val[1],"zustand"); ?></span>
                <span class="tocheck quali2">
                    <?php echo MFBJOBSAPI_Backend::get_job_quali( MFBJOBSAPI_Backend::get_job_id($jobbezeichnung_val[1]) ); ?></span>
                <span class="tocheck hs2"><?php echo MFBJOBSAPI_Backend::get_jobstate_from_db($jobbezeichnung_val[1],"hs"); ?></span>
                <span class="tocheck ebene2"><?php echo MFBJOBSAPI_Backend::get_jobstate_from_db($jobbezeichnung_val[1],"ebene"); ?></span>

                <label for="searchinput_KEY3" class="boxlabel">
                   <?php _e( 'Job 3', 'plugin-MFBJOBSAPI' ); ?>
                </label>
                <input value="<?php echo $jobbezeichnung_val[2]; ?>" type='text' name="jobbezeichnung_3" id="jobbezeichnung_3" data-num="3" data-suggest='<?php echo $url; ?>' id='searchinput_KEY3' class='elmchanger form-control input-lg mfbjobsapi-formelement box_3' data-result="searchformresult.box_3" data-typ="all" data-quali="all"  data-job-titlelm="box_3"><br><div class='searchformresult box_3'></div>
                
               
                  
            </div>
        </div>

        <!--/ Jobs -->


        <!-- A-Orte -->
            <div class="onoffbox">
            <div class="boxtitle"><h3>Arbeitsorte und Anzahl der Stellen <span class="onoffbtn">Öffnen / Schließen</span></h3></div>
            <div class="boxcontent tohide">     
                
                
                <label for="jobfullfill" class="boxlabel">
                   <?php _e( 'Anzahl der Stellen', 'plugin-MFBJOBSAPI' ); ?>
                </label>
                <input value="<?php echo $jobfullfill_val; ?>" type='text' name="jobfullfill" id="jobfullfill" data-num="3" data-suggest='<?php echo $url; ?>' id='jobfullfill' class='form-control input-lg mfbjobsapi-formelement jobfullfill'><br>
                  <!-- Arbeitsorte (ID)  -->
        <div class="container-fluid">
            
          
            <?php for ( $i=1; $i<11; $i++) {
                ?>
            <div class="row">
                <div class="col1">
                   <label for="" class="boxlabel">
                        <?php _e( 'Arbeitsort '.$i.'', 'plugin-MFBJOBSAPI' ); ?>
                    </label>   
                </div>
            </div>
                <div class="row">
                    <div class="col2" >
                       <input placeholder="PLZ" value="<?php echo $joblocationplz[$i]; ?>" type='text' name="joblocationplz_<?php echo $i; ?>" data-suggest='<?php echo $url; ?>' data-license-result="licenseresult_1" id='joblocationplz_<?php echo $i; ?>' class='form-control input-lg mfbjobsapi-formelement joblocation'>
                    </div>
                    <div class="col2">
                       <input placeholder="Ort"  value="<?php echo $joblocationcity[$i]; ?>" type='text' name="joblocationcity_<?php echo $i; ?>" data-suggest='<?php echo $url; ?>' id='joblocationcity_<?php echo $i; ?>' class='form-control input-lg mfbjobsapi-formelement joblocation'>
                    </div>
                </div>
                <div class="row" class="padding10">
                    <div class="col1">
                       <input placeholder="Str, Nr"  value="<?php echo $joblocationstreet[$i]; ?>" type='text' name="joblocationstreet_<?php echo $i; ?>" data-suggest='<?php echo $url; ?>' id='joblocationstreet_<?php echo $i; ?>' class='form-control input-lg mfbjobsapi-formelement joblocation'>
                    </div>
                </div>
                <div class="row" class="padding10">
                    <div class="col1">
                     <select name="joblocationregion_<?php echo $i; ?>"  class="mfbjobsapi-formelement" id="joblocationregion_<?php echo $i; ?>">
                  
                        <option <?php if ($joblocationregion[$i] == 1 ) { echo "selected"; } ?> value="1">Baden-Württemberg</option>
                        <option <?php if ($joblocationregion[$i] == 2 ) { echo "selected"; } ?>  value="2">Bayern</option>
                        <option <?php if ($joblocationregion[$i] == 3 ) { echo "selected"; } ?>  value="3">Berlin</option>
                        <option  <?php if ($joblocationregion[$i] == 4 ) { echo "selected"; } ?> value="4">Brandenburg</option>
                        <option <?php if ($joblocationregion[$i] == 5 ) { echo "selected"; } ?>  value="5">Bremen</option>
                        <option <?php if ($joblocationregion[$i] == 6 ) { echo "selected"; } ?>  value="6">Hamburg</option>
                        <option <?php if ($joblocationregion[$i] == 7 ) { echo "selected"; } ?>  value="7">Hessen</option>
                        <option <?php if ($joblocationregion[$i] == 8 ) { echo "selected"; } ?>  value="8">Mecklenburg-Vorpommern</option>
                        <option <?php if ($joblocationregion[$i] == 9 ) { echo "selected"; } ?>  value="9">Niedersachsen</option>
                        <option <?php if ($joblocationregion[$i] == 10 ) { echo "selected"; } ?>  value="10">Nordrhein-Westfalen</option>
                        <option <?php if ($joblocationregion[$i] == 11 ) { echo "selected"; } ?>  value="11">Rheinland-Pfalz</option>
                        <option <?php if ($joblocationregion[$i] == 12 ) { echo "selected"; } ?>  value="12">Saarland</option>
                        <option <?php if ($joblocationregion[$i] == 13 ) { echo "selected"; } ?>  value="13">Sachsen</option>
                        <option <?php if ($joblocationregion[$i] == 14 ) { echo "selected"; } ?>  value="14">Sachsen-Anhalt</option>
                        <option <?php if ($joblocationregion[$i] == 15 ) { echo "selected"; } ?>  value="15">Schleswig-Holstein</option>
                        <option <?php if ($joblocationregion[$i] == 16 ) { echo "selected"; } ?>  value="16">Thüringen</option>
                    </select>
                    </div>
            </div>

                <?php
            } ?>
          
        </div>
            </div>
        </div>

 <!-- /A-Orte -->


        <div class="onoffbox">
            <div class="boxtitle"><h3>Statusdaten zum Job<span class="onoffbtn">Öffnen / Schließen</span></h3></div>
            <div class="boxcontent tohide">
                   <label for="jobstatus" class="boxlabel">
                    <?php _e( 'Status des Stellenangebotes', 'plugin-MFBJOBSAPI' ); ?>
                </label>         
                <!-- Angaben zum Veröffentlichungsstatus des Stellenangebots.  (1)veröffentlicht    (2)anonym veröffentlicht   (3)nicht veröffentlicht -->
                <select name="jobstatus" class="mfbjobsapi-formelement" id="jobstatus">
                        <option value="1" <?php if ( $jobstatus_val == 1 ) { echo 'selected'; } ?>>veröffentlicht</option>
                        <option value="2" <?php if ( $jobstatus_val == 2 ) { echo 'selected'; } ?>>anonym veröffentlicht</option>
                        <option value="3" <?php if ( $jobstatus_val == 3 ) { echo 'selected'; } ?>>nicht veröffentlicht</option>
                </select><br>

                <label for="jobaction" class="boxlabel">
                    <?php _e( 'Action des Stellenangebotes', 'plugin-MFBJOBSAPI' ); ?>
                </label> 
                <!-- Action (1)neu    (2)update  (3)löschen -->
                <select name="jobaction" class="mfbjobsapi-formelement" id="jobaction">
                        <option value="1" <?php if ( $jobaction_val == 1 ) { echo 'selected'; } ?>>Neuanlage</option>
                        <option value="2" <?php if ( $jobaction_val == 2 ) { echo 'selected'; } ?>>Aktualisierung</option>
                        <option value="3" <?php if ( $jobaction_val == 3 ) { echo 'selected'; } ?>>Löschung</option>
                </select><br>
                
                
                <label for="jobsocialinsurance" class="boxlabel">
                    <?php _e( 'Sozialversicherungspflichtig?', 'plugin-MFBJOBSAPI' ); ?>
                </label> 
                <!-- Action (1)neu    (2)update  (3)löschen -->
                <select name="jobsocialinsurance" class="mfbjobsapi-formelement" id="jobsocialinsurance">
                        <option value="1" <?php if ( $jobsocialinsurance_val == 1 ) { echo 'selected'; } ?>>Ja</option>
                        <option value="0" <?php if ( $jobsocialinsurance_val == 0 ) { echo 'selected'; } ?>>Nein</option>
                </select><br>
                
                <?php _e( '<h3 class=educations>Angaben zu Abschlüssen</h3><br>Wenn der Beruf einen Hochschulabschluss verlangt, dann ist das Feld Pflicht.', 'plugin-MFBJOBSAPI' ); ?>
          
                <label for="jobedudegree1" class="jobedudegree1 boxlabel">
                    <?php _e( 'Hochschulabschlussart des Hauptberufes', 'plugin-MFBJOBSAPI' ); ?>
                </label>
                <select name="jobedudegree1"  class="mfbjobsapi-formelement jobedudegree1" id="jobedudegree1">
                        <option value="0" <?php if ( $jobedudegree_val_1==0 ) { echo 'selected'; } ?>>keine</option>
                        <option value="1" <?php if ( $jobedudegree_val_1==1) { echo 'selected'; } ?>>Nicht relevant</option>
                        <option value="2" <?php if ( $jobedudegree_val_1==2 ) { echo 'selected'; } ?>>Bachelor (BA)</option>
                        <option value="3" <?php if ( $jobedudegree_val_1==3 ) { echo 'selected'; } ?>>Bachelor (FH)</option>
                        <option value="4" <?php if ( $jobedudegree_val_1==4 ) { echo 'selected'; } ?>>Bachelor (Uni)</option>
                        <option value="5" <?php if ( $jobedudegree_val_1==5 ) { echo 'selected'; } ?>>Diplom (BA)</option>
                        <option value="6" <?php if ( $jobedudegree_val_1==6 ) { echo 'selected'; } ?>>Diplom (FH)</option>
                        <option value="7" <?php if ( $jobedudegree_val_1==7 ) { echo 'selected'; } ?>>Diplom (Uni)</option>
                        <option value="8" <?php if ( $jobedudegree_val_1==8 ) { echo 'selected'; } ?>>Kirchliches Examen / Lizenziat</option>
                        <option value="9" <?php if ( $jobedudegree_val_1==9 ) { echo 'selected'; } ?>>Magister</option>
                        <option value="10" <?php if ( $jobedudegree_val_1==10 ) { echo 'selected'; } ?>>Master (FH)</option>
                        <option value="11" <?php if ( $jobedudegree_val_1==11 ) { echo 'selected'; } ?>>Master (Uni)</option>
                        <option value="12" <?php if ( $jobedudegree_val_1==12) { echo 'selected'; } ?>>Promotion</option>
                        <option value="13" <?php if ( $jobedudegree_val_1==13 ) { echo 'selected'; } ?>>Staatsexamen</option>
                </select>
                
                     <label for="jobedudegree2" class="jobedudegree2 boxlabel">
                    <?php _e( 'Hochschulabschlussarten der alternativen Berufe', 'plugin-MFBJOBSAPI' ); ?>
                    </label>
                    <select name="jobedudegree2"  class="mfbjobsapi-formelement jobedudegree2" id="jobedudegree2">
                        
                          <option value="0" <?php if ( $jobedudegree_val_2==0 ) { echo 'selected'; } ?>>keine</option>
                    <option value="1" <?php if ( $jobedudegree_val_2==1) { echo 'selected'; } ?>>Nicht relevant</option>
                    <option value="2" <?php if ( $jobedudegree_val_2==2 ) { echo 'selected'; } ?>>Bachelor (BA)</option>
                    <option value="3" <?php if ( $jobedudegree_val_2==3 ) { echo 'selected'; } ?>>Bachelor (FH)</option>
                    <option value="4" <?php if ( $jobedudegree_val_2==4 ) { echo 'selected'; } ?>>Bachelor (Uni)</option>
                    <option value="5" <?php if ( $jobedudegree_val_2==5 ) { echo 'selected'; } ?>>Diplom (BA)</option>
                    <option value="6" <?php if ( $jobedudegree_val_2==6 ) { echo 'selected'; } ?>>Diplom (FH)</option>
                    <option value="7" <?php if ( $jobedudegree_val_2==7 ) { echo 'selected'; } ?>>Diplom (Uni)</option>
                    <option value="8" <?php if ( $jobedudegree_val_2==8 ) { echo 'selected'; } ?>>Kirchliches Examen / Lizenziat</option>
                    <option value="9" <?php if ( $jobedudegree_val_2==9 ) { echo 'selected'; } ?>>Magister</option>
                    <option value="10" <?php if ( $jobedudegree_val_2==10 ) { echo 'selected'; } ?>>Master (FH)</option>
                    <option value="11" <?php if ( $jobedudegree_val_2==11 ) { echo 'selected'; } ?>>Master (Uni)</option>
                    <option value="12" <?php if ( $jobedudegree_val_2==12) { echo 'selected'; } ?>>Promotion</option>
                    <option value="13" <?php if ( $jobedudegree_val_2==13 ) { echo 'selected'; } ?>>Staatsexamen</option>
                    </select>
                    <select name="jobedudegree3"  class="mfbjobsapi-formelement jobedudegree3" id="jobedudegree3">
                         <option value="0" <?php if ( $jobedudegree_val_3==0 ) { echo 'selected'; } ?>>keine</option>
                    <option value="1" <?php if ( $jobedudegree_val_3==1) { echo 'selected'; } ?>>Nicht relevant</option>
                    <option value="2" <?php if ( $jobedudegree_val_3==2 ) { echo 'selected'; } ?>>Bachelor (BA)</option>
                    <option value="3" <?php if ( $jobedudegree_val_3==3 ) { echo 'selected'; } ?>>Bachelor (FH)</option>
                    <option value="4" <?php if ( $jobedudegree_val_3==4 ) { echo 'selected'; } ?>>Bachelor (Uni)</option>
                    <option value="5" <?php if ( $jobedudegree_val_3==5 ) { echo 'selected'; } ?>>Diplom (BA)</option>
                    <option value="6" <?php if ( $jobedudegree_val_3==6 ) { echo 'selected'; } ?>>Diplom (FH)</option>
                    <option value="7" <?php if ( $jobedudegree_val_3==7 ) { echo 'selected'; } ?>>Diplom (Uni)</option>
                    <option value="8" <?php if ( $jobedudegree_val_3==8 ) { echo 'selected'; } ?>>Kirchliches Examen / Lizenziat</option>
                    <option value="9" <?php if ( $jobedudegree_val_3==9 ) { echo 'selected'; } ?>>Magister</option>
                    <option value="10" <?php if ( $jobedudegree_val_3==10 ) { echo 'selected'; } ?>>Master (FH)</option>
                    <option value="11" <?php if ( $jobedudegree_val_3==11 ) { echo 'selected'; } ?>>Master (Uni)</option>
                    <option value="12" <?php if ( $jobedudegree_val_3==12) { echo 'selected'; } ?>>Promotion</option>
                    <option value="13" <?php if ( $jobedudegree_val_3==13 ) { echo 'selected'; } ?>>Staatsexamen</option>
                </select>
            </div>
        </div>
        

<!--Konditionen-->
        <div class="onoffbox">
            <div class="boxtitle"><h3>Konditionen<span class="onoffbtn">Öffnen / Schließen</span></h3></div>
            <div class="boxcontent tohide">
                  <label for="jobapplication" class="boxlabel">
                    <?php _e( 'Bewerbungszeitraum', 'plugin-MFBJOBSAPI' ); ?>
                </label> 
                <div class="container">
                    <div class="row">
                        <div class="col2" >
                            <?php _e( 'Start', 'plugin-MFBJOBSAPI' ); ?><span class="starting"></span>
                           <input type='text' placeholder="2020-10-10" name="jobapplicationstart" data-suggest='<?php echo $url; ?>' id='jobapplicationstart' class='datepicker form-control  input-lg mfbjobsapi-formelement jobapplicationstart' value='<?php echo $jobapplicationstart; ?>' data-val-date='<?php echo $jobapplicationstart; ?>'>
                        </div>
                        <div class="col2">
                              <?php _e( 'Ende', 'plugin-MFBJOBSAPI' ); ?>
                              <input placeholder="2020-10-10"  value="<?php echo $jobapplicationend; ?>" type='text' name="jobapplicationend" data-suggest='<?php echo $url; ?>' id='jobapplicationend' class='datepicker form-control input-lg mfbjobsapi-formelement jobapplicationend'>
                        </div>
                    </div>
                </div>
                 <div class="container">
                    <div class="row">
                        <div class="col2" >
                            <?php _e( 'Frühester Eintrittstermin', 'plugin-MFBJOBSAPI' ); ?>
                           <input type='text' placeholder="2020-10-10" name="jobstart" data-suggest='<?php echo $url; ?>' id='jobstart' class='datepicker form-control  input-lg mfbjobsapi-formelement jobapplicationstart' value='<?php echo $jobstart; ?>' data-val-date='<?php echo $jobstart; ?>'>
                        </div>
                        <div class="col2">
                              <?php _e( 'Spätester Eintrittstermin', 'plugin-MFBJOBSAPI' ); ?>
                              <input placeholder="2020-10-10" placeholder="2020-01-01" value="<?php echo $jobend; ?>" type='text' name="jobend" data-suggest='<?php echo $url; ?>' id='jobend' class='datepicker form-control input-lg mfbjobsapi-formelement jobapplicationend'>
                        </div>
                    </div>
                </div>
                <label for="jobapplication">
                    <?php _e( 'Gewünschte Bewerbungsart?', 'plugin-MFBJOBSAPI' ); ?>
                </label> 

                <select name="jobapplication[]" multiple class="mfbjobsapi-formelement" id="jobapplication">
                        <option value="1" <?php if ( in_array(1,$jobapplication_val)) { echo 'selected'; } ?>>telefonisch</option>
                        <option value="2" <?php if ( in_array(2,$jobapplication_val)) { echo 'selected'; } ?>>schriftlich</option>
                        <option value="3" <?php if ( in_array(3,$jobapplication_val)) { echo 'selected'; } ?>>per E-Mail</option>
                        <option value="4" <?php if ( in_array(4,$jobapplication_val)) { echo 'selected'; } ?>>persönlich</option>
                        <option value="5" <?php if ( in_array(5,$jobapplication_val)) { echo 'selected'; } ?>>mit firmeneigenen Unterlagen</option>
                        <option value="6" <?php if ( in_array(6,$jobapplication_val)) { echo 'selected'; } ?>>über Internet</option>
                        <option value="7" <?php if ( in_array(7,$jobapplication_val)) { echo 'selected'; } ?>>über www.arbeitsagentur.de</option>
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

                <select name="jobworkingplan[]" multiple class="mfbjobsapi-formelement" id="jobworkingplan">
                        <option value="1" <?php if ( in_array(1,$jobworkingplan_val)) { echo 'selected'; } ?>>Vollzeit</option>
                        <option value="3" <?php if ( in_array(3,$jobworkingplan_val)) { echo 'selected'; } ?>>Teilzeit - Schicht</option>
                        <option value="4" <?php if ( in_array(4,$jobworkingplan_val)) { echo 'selected'; } ?>>Wochenende</option>
                        <option value="5" <?php if ( in_array(5,$jobworkingplan_val)) { echo 'selected'; } ?>>Nachtarbeit</option>
                        <option value="7" <?php if ( in_array(7,$jobworkingplan_val)) { echo 'selected'; } ?>>Teilzeit - Vormittag</option>
                        <option value="8" <?php if ( in_array(8,$jobworkingplan_val)) { echo 'selected'; } ?>>Teilzeit - Nachmittag</option>
                        <option value="9" <?php if ( in_array(9,$jobworkingplan_val)) { echo 'selected'; } ?>>Teilzeit - Abend</option>
                        <option value="10" <?php if ( in_array(10,$jobworkingplan_val)) { echo 'selected'; } ?>>Teilzeit - flexibel</option>
                        <option value=11 <?php if ( in_array(11,$jobworkingplan_val)) { echo 'selected'; } ?>>Heimarbeit</option>
                        <option value="12" <?php if ( in_array(12,$jobworkingplan_val)) { echo 'selected'; } ?>>Schicht</option>
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
                <p><label for="jobtermlength" class="jobterm <?php if ( $jobtermbool_val == 2 ) { ?>tohide_jobtermbool<?php } ?>">
                    <?php _e( 'Befristung (1-120 Monate, nur Zahl eingeben)', 'plugin-MFBJOBSAPI' ); ?>
                </label> 
                    <input value="<?php echo $jobtermlength_val; ?>" type='text' name="jobtermlength" data-suggest='<?php echo $url; ?>' id='jobtermlength' class='form-control input-lg mfbjobsapi-formelement jobterm <?php if ( $jobtermbool_val == 2 ) { ?>tohide_jobtermbool<?php } ?>'></p>

                <!-- Befristungsende, Datum -->
                <p><label for="jobtermdate"  class="jobterm <?php if ( $jobtermbool_val == 2 ) { ?>tohide_jobtermbool<?php } ?>">
                    <?php _e( 'Wann endet die Befristung? (Bis-Datum, Format 2018-12-01 einhalten)', 'plugin-MFBJOBSAPI' ); ?>
                </label> 
                <input value="<?php echo $jobtermdate_val; ?>" type='text' name="jobtermdate" data-suggest='<?php echo $url; ?>' id='jobtermdate' class='form-control input-lg jobterm mfbjobsapi-formelement  <?php if ( $jobtermbool_val == 2 ) { ?>tohide_jobtermbool<?php } ?>'></p>

                <!-- Übernahme möglich? -->
                <p><label for="jobtermtakeover jobterm"  class="jobterm <?php if ( $jobtermbool_val == 2 ) { ?>tohide_jobtermbool<?php } ?>">
                    <?php _e( 'Übernahme möglich?', 'plugin-MFBJOBSAPI' ); ?>
                </label> 

                <select name="jobtermtakeover"  class="mfbjobsapi-formelement jobterm <?php if ( $jobtermbool_val == 2 ) { ?>tohide_jobtermbool<?php } ?>" id="jobtermtakeover">
                        <option value="0" <?php if ( $jobtermtakeover_val == 0 ) { echo 'selected'; } ?>>nein</option>
                        <option value="1" <?php if ( $jobtermtakeover_val == 1 ) { echo 'selected'; } ?>>ja</option>
                    </select></p>

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
                <label for="jobagreement" class="<?php if ( $jobpayscale_val == 0 ) { ?>tohide_jobagreement<?php } ?>">
                    <?php _e( 'Angaben zum Tarifvertrag (Pflichtfeld)', 'plugin-MFBJOBSAPI' ); ?>
                </label> 
                <textarea  type='text' name="jobagreement" data-suggest='<?php echo $url; ?>' id='jobagreement' class='form-control jobagreement input-lg mfbjobsapi-formelement <?php if ( $jobpayscale_val == 0 ) { ?>tohide_jobagreement<?php } ?>'><?php echo $jobagreement_val; ?></textarea><br>

                <!-- ************************************************* / -->


                <!-- Unterkunft? -->
                <label for="jobhousing">
                    <?php _e( 'Wird Unterkunft gestellt?', 'plugin-MFBJOBSAPI' ); ?>
                </label>    
                <select name="jobhousing"  class="mfbjobsapi-formelement" id="jobhousing">
                        <option value="0" <?php if ( $jobhousing_val == 0 ) { echo 'selected'; } ?>>nein</option>
                        <option value="1" <?php if ( $jobhousing_val == 1 ) { echo 'selected'; } ?>>ja</option>
                </select><br>
        
            </div>
        </div>
        
<!--Bewerber-->
        <div class="onoffbox">
            <div class="boxtitle"><h3>Bewerber (Ausbildung  )<span class="onoffbtn">Öffnen / Schließen</span></h3></div>
            <div class="boxcontent tohide">
                <label for="jobknowledge" class="boxlabel">
                    <?php _e( 'Ausbildung des Bewerbers (nur Ausbildungsberufe werden durchsucht)', 'plugin-MFBJOBSAPI' ); ?>
                </label>
                <input value="<?php echo $jobknowledge_val; ?>" type='text' name="jobknowledge" data-suggest='<?php echo $url; ?>' class='form-control input-lg mfbjobsapi-formelement jobknowledge' data-typ="A" data-quali="2,4" data-result="jobknowledgebox" data-job-titlelm="#jobknowledge" id="jobknowledge"><br>  <div class='searchformresult jobknowledgebox'></div>
                
                <label for="jobknowledgehs" class="jobknowledgehs boxlabel">
                    <?php _e( 'Hochschulabschlussart', 'plugin-MFBJOBSAPI' ); ?>
                </label>
                <select name="jobknowledgehs"  class="mfbjobsapi-formelement jobknowledgehs" id="jobknowledgehs">
                        <option value="0" <?php if ( $jobknowledgehs_val==0 ) { echo 'selected'; } ?>>keine</option>
                        <option value="1" <?php if ( $jobknowledgehs_val==1) { echo 'selected'; } ?>>Nicht relevant</option>
                        <option value="2" <?php if ( $jobknowledgehs_val==2 ) { echo 'selected'; } ?>>Bachelor (BA)</option>
                        <option value="3" <?php if ( $jobknowledgehs_val==3 ) { echo 'selected'; } ?>>Bachelor (FH)</option>
                        <option value="4" <?php if ( $jobknowledgehs_val==4 ) { echo 'selected'; } ?>>Bachelor (Uni)</option>
                        <option value="5" <?php if ( $jobknowledgehs_val==5 ) { echo 'selected'; } ?>>Diplom (BA)</option>
                        <option value="6" <?php if ( $jobknowledgehs_val==6 ) { echo 'selected'; } ?>>Diplom (FH)</option>
                        <option value="7" <?php if ( $jobknowledgehs_val==7 ) { echo 'selected'; } ?>>Diplom (Uni)</option>
                        <option value="8" <?php if ( $jobknowledgehs_val==8 ) { echo 'selected'; } ?>>Kirchliches Examen / Lizenziat</option>
                        <option value="9" <?php if ( $jobknowledgehs_val==9 ) { echo 'selected'; } ?>>Magister</option>
                        <option value="10" <?php if ( $jobknowledgehs_val==10 ) { echo 'selected'; } ?>>Master (FH)</option>
                        <option value="11" <?php if ( $jobknowledgehs_val==11 ) { echo 'selected'; } ?>>Master (Uni)</option>
                        <option value="12" <?php if ( $jobknowledgehs_val==12) { echo 'selected'; } ?>>Promotion</option>
                        <option value="13" <?php if ( $jobknowledgehs_val==13 ) { echo 'selected'; } ?>>Staatsexamen</option>
                </select>
                <label for="jobknowledgeother" class="boxlabel">
                    <?php _e( 'Sonstige Angaben zur Ausbildung', 'plugin-MFBJOBSAPI' ); ?>
                </label>
                <textarea  type='text' name="jobknowledgeother" data-suggest='<?php echo $url; ?>' class='form-control input-lg mfbjobsapi-formelement jobknowledgeother' id="jobknowledgeother"><?php echo $jobknowledgeother_val; ?></textarea><br>  
                
        <!-- Höchster Bildungsabschluss? -->
        <label for="jobhighestdegree" class="boxlabel">
            <?php _e( 'Höchster Bildungsabschluss?', 'plugin-MFBJOBSAPI' ); ?>
        </label>    
        <select name="jobhighestdegree"  class="mfbjobsapi-formelement" id="jobhighestdegree">
                <option value="1" <?php if ( $jobhighestdegree_val == 1 ) { echo 'selected'; } ?>>kein Schulabschluss</option>
                <option value="2" <?php if ( $jobhighestdegree_val == 2 ) { echo 'selected'; } ?>>Hauptschulabschluss</option>
                <option value="3" <?php if ( $jobhighestdegree_val == 3 ) { echo 'selected'; } ?>>Mittlere Reife / Mittlerer Bildungsabschluss</option>
                <option value="4" <?php if ( $jobhighestdegree_val == 4 ) { echo 'selected'; } ?>>Fachhochschulreife (einschl. nur theoretischer Teil)</option>
                <option value="5" <?php if ( $jobhighestdegree_val == 5 ) { echo 'selected'; } ?>>Fachabitur / Fachgebundene Hochschulreife</option>
                <option value="6" <?php if ( $jobhighestdegree_val == 6 ) { echo 'selected'; } ?>>Abitur / Allgemeine Hochschulreife </option>
                <option value="7" <?php if ( $jobhighestdegree_val == 7 ) { echo 'selected'; } ?>>Abschluss Fachhochschule (oder Vergl.)</option>
                <option value="9" <?php if ( $jobhighestdegree_val == 9 ) { echo 'selected'; } ?>>Wissenschaftliche Hochschule / Universität</option>
                <option value="11" <?php if ( $jobhighestdegree_val == 11 ) { echo 'selected'; } ?>>Schulabschluss der Förderschule</option>
                <option value="12" <?php if ( $jobhighestdegree_val == 12 ) { echo 'selected'; } ?>>Qualifizierender / erweiterter Hauptschulabschluss</option>
                <option value="13" <?php if ( $jobhighestdegree_val == 13 ) { echo 'selected'; } ?>>Abgänger Klasse 11 -13 ohne Abschluss</option>
                <option value="14" <?php if ( $jobhighestdegree_val == 14 ) { echo 'selected'; } ?>>Hochschule ohne Abschluss</option>
                <option value="15" <?php if ( $jobhighestdegree_val == 15 ) { echo 'selected'; } ?>>nicht relevant</option>
        </select><br>

     <!-- Höchster Bildungsabschluss? -->
        <label for="jobdegreemusthave" class="boxlabel">
            <?php _e( 'Bewerber muss genau diesen Bildungsabschluss besitzen?', 'plugin-MFBJOBSAPI' ); ?>
        </label>    
        <select name="jobdegreemusthave"  class="mfbjobsapi-formelement" id="jobdegreemusthave">
                <option value="1" <?php if ( $jobdegreemusthave_val == 1 ) { echo 'selected'; } ?>>Bewerber muss genau diesen
         Bildungsabschluss besitzen</option>
                <option value="2" <?php if ( $jobdegreemusthave_val == 2 ) { echo 'selected'; } ?>>Bewerber muss mindestens diesen
         Bildungsabschluss besitzen</option>
                   </select><br>
        <label for="jobleadership" class="boxlabel">
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
        <?php _e( '<h3 class=joblead>Führungskompetenzen</h3>', 'plugin-MFBJOBSAPI' ); ?>



        <!-- Leitungsarten  -->
        <label for="jobleadershiptype" class=joblead boxlabel>
            <?php _e( 'Leitungsarten', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
        <select name="jobleadershiptype"  class="mfbjobsapi-formelement joblead" id="jobleadershiptype">
            <option value="0" <?php if ( $jobleadershiptype_val == 0 ) { echo 'selected'; } ?>>keine</option>
                <option value="1" <?php if ( $jobleadershiptype_val == 1 ) { echo 'selected'; } ?>>Technische Leitung</option>
                <option value="2" <?php if ( $jobleadershiptype_val == 2 ) { echo 'selected'; } ?>>Kaufmännische Leitung</option>
                <option value="3" <?php if ( $jobleadershiptype_val == 3 ) { echo 'selected'; } ?>>Technische und kaufmännische Leitung</option>
        </select>
        
         <!-- Leitungsarten  -->
        <label for="jobauth" class=joblead boxlabel>
            <?php _e( 'Vollmachten', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
        <select name="jobauth"  class="mfbjobsapi-formelement joblead" id="jobauth">
            <option value="0" <?php if ( $jobauth_val == 0 ) { echo 'selected'; } ?>>keine</option>
              
                <option value="1" <?php if ( $jobauth_val == 1 ) { echo 'selected'; } ?>>Handlungsvollmacht</option>
                <option value="2" <?php if ( $jobauth_val == 2 ) { echo 'selected'; } ?>>Prokura</option>
                <option value="3" <?php if ( $jobauth_val == 3 ) { echo 'selected'; } ?>>Generalvollmacht</option>
        </select>

         <!-- Leitungsarten  -->
        <label for="jobleadershipex" class=joblead boxlabel>
            <?php _e( 'Führungserfahrung', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
        <select name="jobleadershipex"  class="mfbjobsapi-formelement joblead" id="jobleadershipex">
            <option value="0" <?php if ( $jobleadershipex_val == 0 ) { echo 'selected'; } ?>>keine</option>
                <option value="1" <?php if ( $jobleadershipex_val == 1 ) { echo 'selected'; } ?>>bis 2 Jahre</option>
                <option value="2" <?php if ( $jobleadershipex_val == 2 ) { echo 'selected'; } ?>>2 - 5 Jahre</option>
                <option value="3" <?php if ( $jobleadershipex_val == 3 ) { echo 'selected'; } ?>>> 5 Jahre</option>
        </select>

 <!-- Leitungsarten  -->
        <label for="jobbudget" class="joblead boxlabel">
            <?php _e( 'Budgetverantwortung', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
        <select name="jobbudget"  class="mfbjobsapi-formelement joblead" id="jobbudget">
             <option value="0" <?php if ( $jobbudget_val == 0 ) { echo 'selected'; } ?>>keine</option>
                <option value="1" <?php if ( $jobbudget_val == 1 ) { echo 'selected'; } ?>>bis 1 Mio. EUR p.a.</option>
                <option value="2" <?php if ( $jobbudget_val == 2 ) { echo 'selected'; } ?>>1 – 9 Mio. EUR p.a.</option>
                <option value="3" <?php if ( $jobbudget_val == 3 ) { echo 'selected'; } ?>>10 – 49 Mio. EUR p.a.</option>
            <option value="4" <?php if ( $jobbudget_val == 4 ) { echo 'selected'; } ?>>ab 50 Mio. EUR p.a.</option>

        </select>

        <label for="jobemployers" class=joblead boxlabel>
            <?php _e( 'Personalverantwortung', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
        <select name="jobemployers"  class="mfbjobsapi-formelement joblead" id="jobemployers">
              <option value="0" <?php if ( $jobemployers_val == 0 ) { echo 'selected'; } ?>>keine</option>
              
                <option value="1" <?php if ( $jobemployers_val == 1 ) { echo 'selected'; } ?>>bis 9 Mitarbeiter/innen</option>
                <option value="2" <?php if ( $jobemployers_val == 2 ) { echo 'selected'; } ?>>10 bis 49 Mitarbeiter/innen</option>
                <option value="3" <?php if ( $jobemployers_val == 3 ) { echo 'selected'; } ?>>50 – 499 Mitarbeiter/innen</option>
            <option value="4" <?php if ( $jobemployers_val == 4 ) { echo 'selected'; } ?>>500 und mehr Mitarbeiter/innen</option>

        </select>

            </div>
        </div>
        


<div class="onoffbox">
            <div class="boxtitle"><h3>Bewerber (Kenntnisse, Fähigkeiten, Lizenzen)<span class="onoffbtn">Öffnen / Schließen</span></h3></div>
            <div class="boxcontent tohide">

     
     
 
      
        <!-- Lizenzen (ID)  -->
        <div class="container-fluid">
            <div class="row">
                <div class="col1">
                    <label for="joblicense_1 joblicense" class="boxlabel">
                        <?php _e( 'Lizenzen - Lizenz #1', 'plugin-MFBJOBSAPI' ); ?>
                        <p>Wenn eine Reihe entfernt werden soll, einfach das Texteingabefeld leeren und das Joblisting erneut speichern.</p>
                    </label> 
                </div>
            </div>
            <div class="row">
                <div class="col2">
                    <input  value="<?php echo $joblicense_1; ?>" type='text' name="joblicense_1" data-suggest='<?php echo $url; ?>' data-license-result="licenseresult_1" id='joblicense_1' data-result="licenseresult_1"  class='form-control input-lg mfbjobsapi-formelement joblicense joblicense_1'><br> <div class='licenseresult_1 licenseresult '></div>
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
                   
                </div>
            </div>
           
        </div>
   
        
        <div class="container-fluid" id="lic2">
            <div class="row">
                <div class="col1">
                    <label for="joblicense_2 joblicense boxlabel"  class="boxlabel">
                        
                        <?php _e( 'Lizenzen - Lizenz #2', 'plugin-MFBJOBSAPI' ); ?>
                    </label> 
                </div>
            </div>
            <div class="row">
                <div class="col2">
                   <input value="<?php echo $joblicense_2; ?>" type='text' name="joblicense_2" data-suggest='<?php echo $url; ?>' data-license-result="licenseresult_2" 
                          data-result="licenseresult_2" id='joblicense_2' class='form-control input-lg mfbjobsapi-formelement joblicense joblicense_2'>
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
                    <div class='licenseresult licenseresult_2'></div>
                </div>
            </div>
        </div>
        


        <div class="container-fluid" id="lic3">
            <div class="row">
                <div class="col1">
                    <label for="joblicense_3 joblicense boxlabel" class="boxlabel">
                        <?php _e( 'Lizenzen - Lizenz #3', 'plugin-MFBJOBSAPI' ); ?>
                    </label> 
                </div>
            </div>
            <div class="row">
                <div class="col2">
                   <input value="<?php echo $joblicense_3; ?>" type='text' data-license-name="licensename3" name="joblicense_3" data-result="licenseresult_3"  data-suggest='<?php echo $url; ?>' data-license-result="licenseresult_3" id='joblicense_3' class='form-control input-lg mfbjobsapi-formelement joblicense'>
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
                    <div class='licenseresult licenseresult_3'></div>
                </div>
            </div>
        </div>

    <!-- Freie Beschreibung Zertifikate -->
        <label for="jobcertfreetitle"  class="jobcertfreetitle jobcertfree boxlabel">
            <?php _e( 'Name der benötigten Qualifikation in Form von z.B. Weiterbildung, Zertifikat, Berechtigung (Freitext)', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
        <input value="<?php echo $jobcertfreetitle_val; ?>" type='text' name="jobcertfreetitle" data-suggest='<?php echo $url; ?>' id='jobcertfreetitle' class='form-control input-lg jobcertfree jobcertfreetitle mfbjobsapi-formelement'><br>
        
        <label for="jobcertfreedesc"  class="jobcertfreedesc jobcertfree boxlabel">
            <?php _e( 'Beschreibung der benötigten Qualifikation (Freitext)', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
        <textarea  type='text' name="jobcertfreedesc" data-suggest='<?php echo $url; ?>' id='jobcertfreedesc' class='form-control jobcertfree input-lg mfbjobsapi-formelement jobcertfreedesc'><?php echo $jobcertfreedesc_val; ?></textarea><br>

        
        <!-- Skills (ID)  -->
        <div class="container-fluid">
            
            <div class="row">
                <div class="col1">
                    <label for="jobskills" class="boxlabel">
                        <?php _e( 'Skills (Hardskills)', 'plugin-MFBJOBSAPI' ); ?>
                        
                        <p>Wenn eine Reihe entfernt werden soll, einfach das Texteingabefeld leeren und das Joblisting erneut speichern.</p>
                    </label> 
                </div>
            </div>
            <?php for ( $i=1; $i<6; $i++) {
                ?>
            <div class="row">
                <div class="col1">
                  
                        <?php _e( 'Skill '.$i.'', 'plugin-MFBJOBSAPI' ); ?>
                        
                </div>
            </div>
                <div class="row">
                    <div class="col2">
                       <input placeholder="Skillname (ID)" value="<?php echo $jobskill[$i]; ?>" type='text' name="jobskill_<?php echo $i; ?>" data-suggest='<?php echo $url; ?>' data-skill-result="jobskillresult_<?php echo $i; ?>" id='jobskill_<?php echo $i; ?>' class='form-control input-lg mfbjobsapi-formelement jobskills'>
                    </div>
                    <div class="col2">
                        <select name="jobskillvalue_<?php echo $i; ?>"  class="mfbjobsapi-formelement" id="jobskillvalue_<?php echo $i; ?>">
                  
                        <option <?php if ($jobskillvalue[$i] == 1 ) { echo "selected"; } ?> value="1">Grundkenntnisse</option>
                        <option <?php if ($jobskillvalue[$i] == 2 ) { echo "selected"; } ?>  value="2">Erweiterte Kenntnisse</option>
                        <option <?php if ($jobskillvalue[$i] == 3 ) { echo "selected"; } ?>  value="3">Expertenkenntnisse</option>
                        <option  <?php if ($jobskillvalue[$i] == 4 ) { echo "selected"; } ?> value="4">Zwingend erforderlich</option>
                    </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col1">
                        <div class="jobskillresult jobskillresult_<?php echo $i; ?>"></div>
                    </div>
                </div>
                <?php
            } ?>
          
        </div>
        <div class="container-fluid">
            
            <div class="row">
                <div class="col1">
                    <label for="jobskills" class="boxlabel">
                        <?php _e( 'Skills (Softskills)', 'plugin-MFBJOBSAPI' ); ?>
                        
                        <p>Wenn eine Reihe entfernt werden soll, einfach das Texteingabefeld leeren und das Joblisting erneut speichern.</p>
                    </label> 
                </div>
            </div>
            <?php for ( $i=1; $i<6; $i++) {
                ?>
            <div class="row">
                <div class="col1">
                  
                        <?php _e( 'Softskill '.$i.'', 'plugin-MFBJOBSAPI' ); ?>
                        
                </div>
            </div>
            <div class="row">
                    <div class="col1">
                        <select name="jobskillsoft_<?php echo $i; ?>"  class="mfbjobsapi-formelement" id="jobskillsoft_<?php echo $i; ?>">
                        <option <?php if ($jobskillsoft[$i] == 0 ) { echo "selected"; } ?> value="0">keine Auswahl</option>
                        <option <?php if ($jobskillsoft[$i] == 1 ) { echo "selected"; } ?> value="1">Analyse- und Problemlösefähigkeit</option>
                        <option <?php if ($jobskillsoft[$i] == 2 ) { echo "selected"; } ?>  value="2">Auffassungsfähigkeit/-gabe</option>
                        <option <?php if ($jobskillsoft[$i] == 3 ) { echo "selected"; } ?>  value="3">Entscheidungsfähigkeit </option>
                        <option  <?php if ($jobskillsoft[$i] == 4 ) { echo "selected"; } ?> value="4">Ganzheitliches Denken</option>
                        <option  <?php if ($jobskillsoft[$i] == 5 ) { echo "selected"; } ?> value="5">Organisationsfähigkeit</option>
                        <option  <?php if ($jobskillsoft[$i] == 6 ) { echo "selected"; } ?> value="6">Belastbarkeit</option>
                        <option  <?php if ($jobskillsoft[$i] == 7 ) { echo "selected"; } ?> value="7">Eigeninitiative</option>
                        <option  <?php if ($jobskillsoft[$i] == 8 ) { echo "selected"; } ?> value="8">Motivation/ Leistungsbereitschaft</option>
                        <option  <?php if ($jobskillsoft[$i] == 9 ) { echo "selected"; } ?> value="9">Selbständiges Arbeiten</option>
                        <option  <?php if ($jobskillsoft[$i] == 10 ) { echo "selected"; } ?> value="10">Zielstrebigkeit/Ergebnisorientierung</option>
                        <option  <?php if ($jobskillsoft[$i] == 11 ) { echo "selected"; } ?> value="11">Einfühlungsvermögen</option>
                        <option  <?php if ($jobskillsoft[$i] == 12 ) { echo "selected"; } ?> value="12">Führungsfähigkeit</option>
                        <option  <?php if ($jobskillsoft[$i] == 13 ) { echo "selected"; } ?> value="13">Kommunikationsfähigkeit</option>
                        <option  <?php if ($jobskillsoft[$i] == 14 ) { echo "selected"; } ?> value="14">Kundenorientierung</option>
                        <option  <?php if ($jobskillsoft[$i] == 15 ) { echo "selected"; } ?> value="15">Teamfähigkeit</option>
                        <option  <?php if ($jobskillsoft[$i] == 16 ) { echo "selected"; } ?> value="16">Flexibilität</option>
                        <option  <?php if ($jobskillsoft[$i] == 17 ) { echo "selected"; } ?> value="17">Kreativität</option>
                        <option  <?php if ($jobskillsoft[$i] == 18 ) { echo "selected"; } ?> value="18">Lernbereitschaft</option>
                        <option  <?php if ($jobskillsoft[$i] == 19 ) { echo "selected"; } ?> value="19">Sorgfalt/Genauigkeit </option>
                        <option  <?php if ($jobskillsoft[$i] == 20 ) { echo "selected"; } ?> value="20">Zuverlässigkeit</option>
                    </select>
                </div>
                    
                </div>
                
                <?php
            } ?>
          
        </div>

                
        <div class="container-fluid">
            
            <div class="row">
                <div class="col1">
                    <label for="jobskillsdrive" class="boxlabel">
                        <?php _e( 'Skills (Sprachen)', 'plugin-MFBJOBSAPI' ); ?>
                        
                        <p>Wenn eine Reihe entfernt werden soll, einfach das Texteingabefeld leeren und das Joblisting erneut speichern.</p>
                    </label> 
                </div>
            </div>
           
            <div class="row">
                <div class="col1">
                  
                        <?php _e( 'Sprache #1', 'plugin-MFBJOBSAPI' ); ?>
                        
                </div>
            </div>
            <div class="row">
                    <div class="col2">
                       <select name="joblang_1"  class="mfbjobsapi-formelement" id="joblang_1">
                        <option <?php if ($joblang_1 == 65488 ) { echo "selected"; } ?> value="65488">Deutsch</option>
                        <option <?php if ($joblang_1 == 65494 ) { echo "selected"; } ?> value="65494">Englisch</option>
                        <option <?php if ($joblang_1 == 65500 ) { echo "selected"; } ?> value="65500">Französisch</option>
                        </select>
                    </div>
                    <div class="col2">
                        <select name="joblangval_1"  class="mfbjobsapi-formelement" id="joblangval_1">
                            <option <?php if ($joblangval_1 == 0 ) { echo "selected"; } ?> value="0">Nicht erforderlich</option>
                            <option <?php if ($joblangval_1 == 1 ) { echo "selected"; } ?> value="1">Grundkenntnisse</option>
                            <option <?php if ($joblangval_1 == 2 ) { echo "selected"; } ?> value="2">Erweiterte Kenntnisse</option>
                            <option <?php if ($joblangval_1 == 3 ) { echo "selected"; } ?> value="3">Verhandlungssicher</option>
                            <option <?php if ($joblangval_1 == 4 ) { echo "selected"; } ?> value="4">Zwingend erforderlich</option>
                        </select>
                
                    </div>
            </div>
            
            <div class="row">
                <div class="col1">
                  
                        <?php _e( 'Sprache #2', 'plugin-MFBJOBSAPI' ); ?>
                        
                </div>
            </div>
            <div class="row">
                    <div class="col2">
                       <select name="joblang_2"  class="mfbjobsapi-formelement" id="joblang_2">
                        <option <?php if ($joblang_2 == 65488 ) { echo "selected"; } ?> value="65488">Deutsch</option>
                        <option <?php if ($joblang_2 == 65494 ) { echo "selected"; } ?> value="65494">Englisch</option>
                        <option <?php if ($joblang_2 == 65500 ) { echo "selected"; } ?> value="65500">Französisch</option>
                        </select>
                    </div>
                    <div class="col2">
                        <select name="joblangval_2"  class="mfbjobsapi-formelement" id="joblangval_2">
                            <option <?php if ($joblangval_2 == 0 ) { echo "selected"; } ?> value="0">Nicht erforderlich</option>
                            <option <?php if ($joblangval_2 == 1 ) { echo "selected"; } ?> value="1">Grundkenntnisse</option>
                            <option <?php if ($joblangval_2 == 2 ) { echo "selected"; } ?> value="2">Erweiterte Kenntnisse</option>
                            <option <?php if ($joblangval_2 == 3 ) { echo "selected"; } ?> value="3">Verhandlungssicher</option>
                            <option <?php if ($joblangval_2 == 4 ) { echo "selected"; } ?> value="4">Zwingend erforderlich</option>
                        </select>
                
                    </div>
            </div>
            
            <div class="row">
                <div class="col1">
                  
                        <?php _e( 'Sprache #3', 'plugin-MFBJOBSAPI' ); ?>
                        
                </div>
            </div>
            <div class="row">
                    <div class="col2">
                       <select name="joblang_3"  class="mfbjobsapi-formelement" id="joblang_3">
                        <option <?php if ($joblang_3 == 65488 ) { echo "selected"; } ?> value="65488">Deutsch</option>
                        <option <?php if ($joblang_3 == 65494 ) { echo "selected"; } ?> value="65494">Englisch</option>
                        <option <?php if ($joblang_3 == 65500 ) { echo "selected"; } ?> value="65500">Französisch</option>
                        </select>
                    </div>
                    <div class="col2">
                        <select name="joblangval_3"  class="mfbjobsapi-formelement" id="joblangval_3">
                            <option <?php if ($joblangval_3 == 0 ) { echo "selected"; } ?> value="0">Nicht erforderlich</option>
                            <option <?php if ($joblangval_3 == 1 ) { echo "selected"; } ?> value="1">Grundkenntnisse</option>
                            <option <?php if ($joblangval_3 == 2 ) { echo "selected"; } ?> value="2">Erweiterte Kenntnisse</option>
                            <option <?php if ($joblangval_3 == 3 ) { echo "selected"; } ?> value="3">Verhandlungssicher</option>
                            <option <?php if ($joblangval_3 == 4 ) { echo "selected"; } ?> value="4">Zwingend erforderlich</option>
                        </select>
                
                    </div>
            </div>
         
        </div>        
                
                
        <div class="container-fluid">
            
            <div class="row">
                <div class="col1">
                    <label for="jobskillsdrive" class="boxlabel">
                        <?php _e( 'Skills (Führerscheine)', 'plugin-MFBJOBSAPI' ); ?>
                        
                        <p>Wenn eine Reihe entfernt werden soll, einfach das Texteingabefeld leeren und das Joblisting erneut speichern.</p>
                    </label> 
                </div>
            </div>
           <?php for ( $i=1; $i<4; $i++) {
                ?>
            <div class="row">
                <div class="col1">
                  
                        <?php _e( '<h3>Führerschein '.$i.'</h3>', 'plugin-MFBJOBSAPI' ); ?>
                        
                </div>
            </div>
                <div class="row">
                    <div class="col2">
                       <input placeholder="Führerschein (ID)" value="<?php echo $jobskilldrive[$i]; ?>" type='text' name="jobskilldrive_<?php echo $i; ?>" data-suggest='<?php echo $url; ?>' data-skill-result="jobskilldriveresult_<?php echo $i; ?>" data-key="fahrerlaubnis" id='jobskilldrive_<?php echo $i; ?>' class='form-control input-lg mfbjobsapi-formelement driveskills'><br><div class="jobskillresult jobskilldriveresult_<?php echo $i; ?>"></div>
                    </div>
                    <div class="col2">
                        <select name="jobskilldrivevalue_<?php echo $i; ?>"  class="mfbjobsapi-formelement" id="jobskilldrivevalue_<?php echo $i; ?>">
                        <option <?php if ($jobskilldrivevalue[$i] == 0 ) { echo "selected"; } ?> value="0">keine Angabe</option>
                        <option <?php if ($jobskilldrivevalue[$i] == 1 ) { echo "selected"; } ?> value="1">Wünschenswert</option>
                        <option <?php if ($jobskilldrivevalue[$i] == 2 ) { echo "selected"; } ?>  value="2">Zwingend erforderlich</option>
                        
                    </select>
                    </div>
                </div>
             
                <?php
            } ?>
          
        </div>

         <!-- Befristung ja / nein -->
        <label for="jobpkw" class="boxlabel">
            <?php _e( 'PKW?', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
   
        <select name="jobpkw"  class="mfbjobsapi-formelement" id="jobpkw">
                <option value="0" <?php if ( $jobpkw_val == 0 ) { echo 'selected'; } ?>>nicht gefordert</option>
                <option value="1" <?php if ( $jobpkw_val == 1 ) { echo 'selected'; } ?>>gefordert</option>
               
        </select><br>
 <!-- Befristung ja / nein -->
        <label for="joblkw" class="boxlabel">
            <?php _e( 'LKW?', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
   
        <select name="joblkw"  class="mfbjobsapi-formelement" id="joblkw">
                <option value="0" <?php if ( $joblkw_val == 0 ) { echo 'selected'; } ?>>nicht gefordert</option>
                <option value="1" <?php if ( $joblkw_val == 1 ) { echo 'selected'; } ?>>gefordert</option>
               
        </select><br>
  <label for="jobknow" class="boxlabel">
            <?php _e( 'Berufserfahrung?', 'plugin-MFBJOBSAPI' ); ?>
        </label> 
   
        <select name="jobknow"  class="mfbjobsapi-formelement" id="jobknow">
                <option value="2" <?php if ( $jobknow_val == 2 ) { echo 'selected'; } ?>>Mit Berufserfahrung</option>
                <option value="1" <?php if ( $jobknow_val == 1 ) { echo 'selected'; } ?>>Einsteiger</option>
               
        </select><br>
    </div></div>
        <?php
    }
}