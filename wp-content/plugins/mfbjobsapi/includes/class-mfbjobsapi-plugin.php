<?php
/*
Main Class to handle functions in MFBJOBSAPI
*/

class MFBJOBSAPI {
    
    /*
    * contructor, started at plugin setup
    * contains e.g. the needed shortcodes
    */
    public function __construct()
    {
        
        /*
        * SHORTCODES
        */
         //session starten
       
        // SC to test class, output: Version of Plugin
        add_shortcode('MFBJOBSAPI_Version', array($this, 'get_mfjobsapi_version'));
        
        // SC to test jobs, output differs
        add_shortcode('MFBJOBSAPI_alljobs', array($this, 'get_all_jobs'));
  
        // SC to test jobs, read from xml
        add_shortcode('MFBJOBSAPI_xmljobs', array($this, 'get_jobs_from_db'));
        
         // SC to test jobs, read from xml
        add_shortcode('MFBJOBSAPI_jobmeta', array($this, 'get_job_meta'));
       
        
        // SC create the jobsearch field in backend, which searches through the jobdatabase
        add_shortcode('MFBJOBSAPI_createjobsearch', array($this, 'set_jobs_search_field'));
       
        
        /*
        * CONSTANTS
        */
        define('TABLEPREF','wp');
        define('MFBJOBSAPI_SupplierID','V000311000');
        define('MFBJOBSAPI_HiringOrgID','A046A18000');
        define('MFBJOBSAPI_AllianceID','13739');
        define('MFBJOBSAPI_BACustomer','08791942');
        define('MFBJOBSAPI_SupplierIndustry',5); //5 = Arbeitgeber
        define('MFBJOBSAPI_HiringOrg','TVS Personalservice GmbH');
        define('MFBJOBSAPI_HiringOrgWeb','http://www.tvs-personalservice.de');
        define('MFBJOBSAPI_HiringOrgEmail','info@tvspersonalservice.de');
        define('MFBJOBSAPI_HiringOrgContactSalutation',1); // 1 = Sehr geehrter, 2 = Sehr geehrte, 3 = Sehr geehrte/r Herr / Frau
        define('MFBJOBSAPI_HiringOrgContactGivenName','Stefan');
        define('MFBJOBSAPI_HiringOrgContactName','Schmidt');
        define('MFBJOBSAPI_HiringOrgGivenName','Stefan');
        define('MFBJOBSAPI_HiringOrgContactPositionTitle','Personaldisponent');
        define('MFBJOBSAPI_HiringOrgContactStreet','Friedrich-Ebert-Straße 16');
        define('MFBJOBSAPI_HiringOrgContactZip','06237');
        define('MFBJOBSAPI_HiringOrgContactCity','Leuna');
        define('MFBJOBSAPI_HiringOrgContactCountry','DE');
        define('MFBJOBSAPI_HiringOrgContactRegion','14'); //14=Sachsen Anhalt, Bundesland ATTRIBUTE: zu entnehmen aus der Datei Regionen-VAM-Code
        define('MFBJOBSAPI_NAICS','78.10.0'); //Code für Branche, nach WZ2008, für Personalvermittler -> 78.10.0 = Vermittlung von Arbeitskräften
        define('MFBJOBSAPI_HiringOrgContactPhoneCode','+049');
        define('MFBJOBSAPI_HiringOrgContactAreaCode','3461');
        define('MFBJOBSAPI_HiringOrgContactPhoneNr','826990');
        define('MFBJOBSAPI_HiringOrgContactMobileAreaCode','170');
        define('MFBJOBSAPI_HiringOrgContactMobileNr','9669624');
        define('MFBJOBSAPI_HiringOrgContactFaxNr','8269919');
       
        
         
    }
    
    
    
     public function get_id_by_title( $jobtitle ) {

         
        $query = "SELECT * FROM `jobs_vam` WHERE `bezeichnung` like '%".$jobtitle."%' and `typ`= 'a' ";
      
        $mysqli = MFBJOBSAPI::connectToDB();
		if ($result = $mysqli->query($query)) {
			
			/* create single article-object and push to array */
			 while($obj = $result->fetch_object()){ 
              return $obj->_id;
			 }
		}
    }
    
      public function get_komp_id_by_title( $jobtitle ) {

         
        $query = "SELECT * FROM `komp_vam` WHERE `bezeichnung` = '".$jobtitle."'";
      
        $mysqli = MFBJOBSAPI::connectToDB();
		if ($result = $mysqli->query($query)) {
			
			/* create single article-object and push to array */
			 while($obj = $result->fetch_object()){ 
              return $obj->_id;
			 }
		}
    }
    public function get_komp_title_by_id( $jobid ) {

         
        $query = "SELECT * FROM `komp_vam` WHERE `_id` = '".$jobid."'";
      
        $mysqli = MFBJOBSAPI::connectToDB();
		if ($result = $mysqli->query($query)) {
			
			/* create single article-object and push to array */
			 while($obj = $result->fetch_object()){ 
              return $obj->bezeichnung;
			 }
		}
    }
    /*
    * SC Function, corresponding shortcode: MFBJOBSAPI_createjobsearch
    */
    public function set_jobs_search_field() {
        $url = get_bloginfo('url').'/wp-content/plugins/mfbjobsapi/includes/class-mfbjobsapi-suggest.php';
        $field = "<div id='jobs_search_field'><input type='text' data-suggest='".$url."' id='searchinput_KEY' class='form-control input-lg'><br>
        <div class='searchformresult'></div></div>";
        echo $field;
    }
       
    
    /*
    * SC Function, corresponding shortcode: MFBJOBSAPI_Version
    */
    public function get_mfjobsapi_version() {
        echo '<h1>Version: '.PLUGIN_MFBJOBSAPI_VERSION.'</h1>';
        echo '<h2>Supplier: '.MFBJOBSAPI_SupplierID.'</h2>';
    }
    
    
    /*
    * just my own db handle
    */
    protected function connectToDB() {
		
		$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		//does not work on PHP smaller 5.2.9
		if ($mysqli->connect_error) {
    		die('Connect Error: ' . $mysqli->connect_error);
		}		
		return $mysqli;	
	}
    
    
    /*
    * returns head of xmlfile
    @returns: string    
    */
    
    public function get_xml_head() {        
        $xmlcontent = '<?xml version="1.0" encoding="UTF-8"?>         
                        <HRBAXMLJobPositionPosting>
                            <Header>
		                      <SupplierId>'.MFBJOBSAPI_SupplierID.'</SupplierId>
		                      <Timestamp>2001-12-17T09:30:47.0Z</Timestamp>
		                      <Amount>100</Amount>
		                      <TypeOfLoad>F</TypeOfLoad>
	                       </Header>'; 
        return $xmlcontent;
    }
    
    /*
    * returns footer of xmlfile
    @returns: string    
    */
    
    public function get_xml_footer() {
        return '</HRBAXMLJobPositionPosting>';
    }
    
    
    /*
    * returns meta data of the current job offer (e.g. Status, Action, Dates, etc)
    @params: ID of job offer
    @returns: object    
    */
    
    public function get_job_meta($jobid) {
        /*
        needed: start- and enddate, status, action
        */
        $query = "SELECT * FROM `".TABLEPREF."_postmeta` WHERE `post_id` = " . $jobid;
        $mysqli = MFBJOBSAPI::connectToDB();
		$job = new stdClass();
		if ($result = $mysqli->query($query)) {
			
			/* create single article-object and push to array */
			 while($obj = $result->fetch_object()){ 
				
                if ( $obj->meta_key == "jobbezeichnung") {
                    $job->jobtitle = $obj->meta_value;
                   
                      $job->id = MFBJOBSAPI::get_id_by_title($obj->meta_value);
                   // $job->id[1] = MFBJOBSAPI::get_id_by_title($job->jobbezeichnung_2);
                } 
                if ( $obj->meta_key == "jobbezeichnung_2") {
                     if ( $obj->meta_value != "" ) {
                        $job->jobbezeichnung_2 = $obj->meta_value;
                      
                     }
                } 
                if ( $obj->meta_key == "jobbezeichnung_3") {
                    if ( $obj->meta_value != "" ) {
                        $job->jobbezeichnung_3 = $obj->meta_value;
                    }
                }
                if ( $obj->meta_key == "job_bkz") {
                    $job->bkz = $obj->meta_value;
                } 
                 
                if ( $obj->meta_key == "jobapplicationstart") {
                     
                        $job->jobapplicationstart = $obj->meta_value;
                    
                } 
                 
                if ( $obj->meta_key == "jobapplicationend") {
                   
                        $job->jobapplicationend = $obj->meta_value;
                   
                } 
                if ( $obj->meta_key == "_job_expires") {
                    $job->jobend = $obj->meta_value;
                } 
                if ( $obj->meta_key == "_job_expires") {
                    $job->jobend = $obj->meta_value;
                } 
                if ( $obj->meta_key == "jobstatus") {
                    $job->jobstatus = $obj->meta_value;
                } 
                if ( $obj->meta_key == "jobaction") {
                    $job->jobaction = $obj->meta_value;
                } 
                if ( $obj->meta_key == "joboffertype") {
                    $job->joboffertype = $obj->meta_value;
                }  
                if ( $obj->meta_key == "jobsocialinsurance") {
                    $job->jobsocialinsurance = $obj->meta_value;
                } 
                if ( $obj->meta_key == "geolocation_postcode") {
                    $job->geolocation_postcode = $obj->meta_value;
                } else {
                     $job->geolocation_postcode = "00000";
                }
                if ( $obj->meta_key == "geolocation_city") {
                    $job->geolocation_city = $obj->meta_value;
                } 
                if ( $obj->meta_key == "geolocation_state_long") {
                    $job->geolocation_state_long = $obj->meta_value;
                } 
                if ( $obj->meta_key == "jobapplication") {
                    $job->jobapplication = explode(',',$obj->meta_value);
                    $applications = "";
                    foreach ($job->jobapplication as $application) {
                        $applications .= '<KindOfApplication>'.$application.'</KindOfApplication>';
                    }
                    
                } 
                if ( $obj->meta_key == "jobleadership") {
                    $job->jobleadership = $obj->meta_value;
                } 
                if ( $obj->meta_key == "jobhours") {
                    $job->jobhours = $obj->meta_value;
                }  
                if ( $obj->meta_key == "jobworkingplan") {
                    $job->jobworkingplan = explode(',',$obj->meta_value);
                    $jobworkingplans = "";
                    foreach ($job->jobworkingplan as $plan) {
                        $jobworkingplans .= '<WorkingPlan>'.$plan.'</WorkingPlan>';
                    }
                }  
                if ( $obj->meta_key == "jobedudegree1"){
                    if ( !empty($obj->meta_value)) {
                        $job->jobedudegree1 = $obj->meta_value;
                    }
                }
                if ( $obj->meta_key == "jobedudegree2"){
                    if ( !empty($obj->meta_value)) {
                        $job->jobedudegree2 = $obj->meta_value;
                    }
                }
                  if ( $obj->meta_key == "jobedudegree3"){
                    if ( !empty($obj->meta_value)) {
                        $job->jobedudegree3 = $obj->meta_value;
                    }
                }
                 
                 
                if ( $obj->meta_key == "jobtermlength") {
                    $job->jobtermlength = $obj->meta_value;
                }  
                if ( $obj->meta_key == "jobtermdate") {
                    $job->jobtermdate = $obj->meta_value;
                }  
                if ( $obj->meta_key == "jobtermbool") {
                    $job->jobtermbool = $obj->meta_value;
                }  
                if ( $obj->meta_key == "jobtermtakeover") {
                    $job->jobtermtakeover = $obj->meta_value;
                }  
                if ( $obj->meta_key == "jobsalary") {
                    $job->jobsalary = $obj->meta_value;
                }
                if ( $obj->meta_key == "jobpayscale") {
                    $job->jobpayscale = $obj->meta_value;
                }
                if ( $obj->meta_key == "jobagreement") {
                    $job->jobagreement = $obj->meta_value;
                }
                if ( $obj->meta_key == "jobhousing") {
                    $job->jobhousing = $obj->meta_value;
                }
                if ( $obj->meta_key == "jobedutitle") {
                    $job->jobedutitle = $obj->meta_value;
                }
                 if ( $obj->meta_key == "jobeduname") {
                    $job->jobeduname = $obj->meta_value;
                }
                if ( $obj->meta_key == "jobpkw") {
                    $job->jobpkw = $obj->meta_value;
                }
                 if ( $obj->meta_key == "joblkw") {
                    $job->joblkw = $obj->meta_value;
                }
                //Lizenzen
                if ( $obj->meta_key == "joblicense_1") {
                    if ( !empty($obj->meta_value)) {
                        $job->joblicense_1 = $obj->meta_value ;
                    }
                } 
                if ( $obj->meta_key == "joblicense_2") {
                    if ( !empty($obj->meta_value)) {
                        $job->joblicense_2 = $obj->meta_value;
                    }
                } 
                if ( $obj->meta_key == "joblicense_3") {
                    if ( !empty($obj->meta_value)) {
                        $job->joblicense_3 = $obj->meta_value;
                    }
                } 
                 
                if ( $obj->meta_key == "joblicenselevel_1") {
                    $job->joblicenselevel_1 = $obj->meta_value;
                } 
                if ( $obj->meta_key == "joblicenselevel_2") {
                    $job->joblicenselevel_2 = $obj->meta_value;
                } 
                if ( $obj->meta_key == "joblicenselevel_3") {
                    $job->joblicenselevel_3 = $obj->meta_value; 
                } 
                if ( $obj->meta_key == "jobhighestdegree") {
                    $job->jobhighestdegree = $obj->meta_value; 
                }
                if ( $obj->meta_key == "jobdegreemusthave") {
                    $job->jobdegreemusthave = $obj->meta_value; 
                }
                if ( $obj->meta_key == "jobleadershiptype") {
                    $job->jobleadershiptype = $obj->meta_value; 
                } 
                 if ( $obj->meta_key == "jobknow") {
                    $job->jobknow = $obj->meta_value; 
                } 
                if ( $obj->meta_key == "jobauth") {
                    $job->jobauth = $obj->meta_value; 
                }
                if ( $obj->meta_key == "jobleadershipex") {
                    $job->jobleadershipex = $obj->meta_value; 
                }
                if ( $obj->meta_key == "jobbudget") {
                    $job->jobbudget = $obj->meta_value; 
                }
                  if ( $obj->meta_key == "jobstart") {
                     if ( !empty($obj->meta_value)) {
                        $job->jobstart = $obj->meta_value;
                     }
                }
                 if ( $obj->meta_key == "jobend") {
                     if ( !empty($obj->meta_value)) {
                        $job->jobend = $obj->meta_value;
                     }
                }
                if ( $obj->meta_key == "jobemployers") {
                    $job->jobemployers = $obj->meta_value; 
                }
                if ( $obj->meta_key == "jobknowledge") {
                     if ( !empty($obj->meta_value)) {
                        $job->jobknowledge = $obj->meta_value;
                     }
                }  
                if ( $obj->meta_key == "jobknowledgehs") {
                     if ( !empty($obj->meta_value)) {
                        $job->jobknowledgehs = $obj->meta_value;
                     }
                }  
                if ( $obj->meta_key == "jobknowledgeother") {
                     if ( !empty($obj->meta_value)) {
                        $job->jobknowledgeother = $obj->meta_value;
                     }
                }
                  if ( $obj->meta_key == "jobfullfill") {
                     if ( !empty($obj->meta_value)) {
                        $job->jobfullfill = $obj->meta_value;
                     }
                }
                  if ( $obj->meta_key == "jobcertfreetitle") {
                      if ( !empty($obj->meta_value)) {
                        $job->jobcertfreetitle = $obj->meta_value; 
                      }
                    }
                 if ( $obj->meta_key == "jobcertfreedesc") {
                      if ( !empty($obj->meta_value)) {
                        $job->jobcertfreedesc = $obj->meta_value; 
                      }
                    } 
                  if ( $obj->meta_key == "joblang_1") {
                      if ( !empty($obj->meta_value)) {
                        $job->joblang_1 = $obj->meta_value; 
                      }
                    }
                 if ( $obj->meta_key == "joblang_2") {
                      if ( !empty($obj->meta_value)) {
                        $job->joblang_2 = $obj->meta_value; 
                      }
                    }
                 if ( $obj->meta_key == "joblang_3") {
                      if ( !empty($obj->meta_value)) {
                        $job->joblang_3 = $obj->meta_value; 
                      }
                    }
                 if ( $obj->meta_key == "joblangval_1") {
                      if ( !empty($obj->meta_value)) {
                        $job->joblangval_1 = $obj->meta_value; 
                      }
                    }
                  if ( $obj->meta_key == "joblangval_2") {
                      if ( !empty($obj->meta_value)) {
                        $job->joblangval_2 = $obj->meta_value; 
                      }
                    }
                  if ( $obj->meta_key == "joblangval_3") {
                      if ( !empty($obj->meta_value)) {
                        $job->joblangval_3 = $obj->meta_value; 
                      }
                    }
             
                
               for ( $ival=1; $ival<6; $ival++) {
                   if ( $obj->meta_key == "jobskillsoft_".$ival) {
                    $job->jobskillsoft[$ival] = $obj->meta_value; 
                   }
                   if ( $obj->meta_key == "jobskill_".$ival) {
                    $job->jobskill[$ival] = $obj->meta_value; 
                   }
                   if ( $obj->meta_key == "jobskillvalue_".$ival) {
                    $job->jobskillvalue[$ival] = $obj->meta_value; 
                   }
                    
              }
        
                 
                for ( $i=1; $i<11; $i++) {
                    if ( $obj->meta_key == "joblocationplz_".$i) {
                        $job->joblocationplz[$i] = $obj->meta_value; 
                    }
                    if ( $obj->meta_key == "joblocationregion_".$i) {
                        $job->joblocationregion[$i] = $obj->meta_value; 
                    } 
                    if ( $obj->meta_key == "joblocationcity_".$i) {
                        $job->joblocationcity[$i] = $obj->meta_value; 
                    } 
                    if ( $obj->meta_key == "joblocationstreet_".$i) {
                        $job->joblocationstreet[$i] = $obj->meta_value; 
                    } 
                    
                }
                for ( $i=1; $i<4; $i++) {
                    if ( $obj->meta_key == "jobskilldrive_".$i) {
                        if ( !empty($obj->meta_value)) {
                            $job->jobskilldrive[$i] = MFBJOBSAPI::get_komp_id_by_title( $obj->meta_value ); 
                        }
                    }
                    if ( $obj->meta_key == "jobskilldrivevalue_".$i) {
                         if ( !empty($obj->meta_value)) {
                            $job->jobskilldrivevalue[$i] = $obj->meta_value; 
                         }
                    }
                }
			 }
		}
        //print_r($job);
        return $job;
    }
    
    
    
    /*
    * returns jobs as select list
    @returns: object connectToDB   
    */
    public function get_jobs_from_db() {
        $query = "SELECT * FROM `jobs_vam`";
        $mysqli = MFBJOBSAPI::connectToDB();
		$jobsArray = array();
		if ($result = $mysqli->query($query)) {
			
			/* create single article-object and push to array */
			 while($obj = $result->fetch_object()){ 
				
                 array_push($jobsArray, $obj);
			 }
		}
        //print_r($jobsArray);
    }
    
   
    
    /*
    * get all existing jobs in an array
    * with stdClass Objects, individually defined to fit the needs
    * of arbeitsagentur
    @returns: array   
    */
    
    public function get_all_jobs() {
        //get all jobs
        $jobs = get_posts(array('post_type' => 'job_listing','posts_per_page' => 100));
        
        //var to fill xml data in, starting with header
        $xmlcontent = MFBJOBSAPI::get_xml_head();
        $licenses = "";
        $locations = "";
        $skillssoft = "";
        $skillshard = "";
        $drive = "";
        $altjobs = "";
        $langs = "";        // some vars
        $certs = "";
         
        //loop through jobs
        foreach ( $jobs as $job ) {
            $jobmeta = MFBJOBSAPI::get_job_meta($job->ID); 
            
            if ( isset($jobmeta->joblangval_1) && $jobmeta->joblangval_1 > 0) {
                $langs.=' <Language><LanguageName>'.$jobmeta->joblang_1.'</LanguageName>
                            <LanguageLevel>'.$jobmeta->joblangval_1.'</LanguageLevel>
                             </Language>';
            }
             if ( isset($jobmeta->joblangval_2) && $jobmeta->joblangval_2 > 0){
                $langs.=' <Language><LanguageName>'.$jobmeta->joblang_2.'</LanguageName>
                            <LanguageLevel>'.$jobmeta->joblangval_2.'</LanguageLevel>
                             </Language>';
            }
             if ( isset($jobmeta->joblangval_3) && $jobmeta->joblangval_3 > 0){
                $langs.=' <Language><LanguageName>'.$jobmeta->joblang_3.'</LanguageName>
                            <LanguageLevel>'.$jobmeta->joblangval_3.'</LanguageLevel>
                             </Language>';
            }
            for ( $i=1; $i<6; $i++) {
                if ( isset($jobmeta->jobskillsoft[$i])  && !empty($jobmeta->jobskillsoft[$i])) {
                    $skillssoft .=  '<SoftSkill><SkillName>'.$jobmeta->jobskillsoft[$i].'</SkillName></SoftSkill>';
                }
                if (isset($jobmeta->jobskill[$i]) && !empty($jobmeta->jobskill[$i])) {
                    $skillshard .=  '<HardSkill><SkillName>'.MFBJOBSAPI::get_komp_id_by_title($jobmeta->jobskill[$i]).'</SkillName>
                                    <SkillLevel>'.$jobmeta->jobskillvalue[$i].'</SkillLevel></HardSkill>';
                    
                }
                
            }
            
            
            for ( $i=1; $i<11; $i++) {
                if ( isset($jobmeta->joblocationplz[$i]) && isset($jobmeta->joblocationcity[$i]) ) {
                    $locations .=  '<Location><CountryCode>DE</CountryCode>
                                                            <PostalCode>'.$jobmeta->joblocationplz[$i].'</PostalCode>
                                                            <Region>'.$jobmeta->joblocationregion[$i].'</Region>
                                                            <Municipality>'.$jobmeta->joblocationcity[$i].'</Municipality>
                                                            <District/>
                                                            <AddressLine/>
                                                            <StreetName>'.$jobmeta->joblocationstreet[$i].'</StreetName>
                                                        </Location>';
                }
                
            }
            
            
            for ( $i=1; $i<4; $i++) {
                
               
                if ( isset($jobmeta->jobskilldrive[$i]) && !empty($jobmeta->jobskilldrive[$i])) {
                    $drive .=  '<DrivingLicence><DrivingLicenceName>'.$jobmeta->jobskilldrive[$i].'</DrivingLicenceName>
                                                                <DrivingLicenceRequired>'.$jobmeta->jobskilldrivevalue[$i].'</DrivingLicenceRequired></DrivingLicence>';
                }
                
            }
          
            if ( isset($job->joblicense_1) && $job->joblicense_1 != "") {
                $licenses .= "<Licence><LicenceName>".$job->joblicense_1."</LicenceName>                                                                <LicenceLevel>".$job->joblicenselevel_1."</LicenceLevel>
                </Licence>";
            }
            
            if ( isset($job->joblicense_2) && $job->joblicense_2 != "") {
                $licenses .= "<Licence><LicenceName>".$job->joblicense_2."</LicenceName>                                                                <LicenceLevel>".$job->joblicenselevel_2."</LicenceLevel>
                </Licence>";
            }
            
            if ( isset($job->joblicense_3)&& $job->joblicense_3 != "") {
                $licenses .= "<Licence><LicenceName>".$job->joblicense_3."</LicenceName>                                                                <LicenceLevel>".$job->joblicenselevel_3."</LicenceLevel>
                </Licence>";
            }
             
            /*
            * alternative Berufe
            */
                
                if ( isset($job->jobedudegree2) && $job->jobedudegree2 != 15 && $job->jobbezeichnung_2 != "") {
                      $altjobs .=  '<AlternativeJobPositionTitle><TitleCode>'.MFBJOBSAPI::get_id_by_title($job->jobbezeichnung_2).'</TitleCode>
                                                        <Degree>'.$job->jobedudegree2.'</Degree></AlternativeJobPositionTitle>';
                }
                if ( isset($job->jobedudegree3) && $job->jobedudegree3  != 15 && $job->jobbezeichnung_3 != "") {
                      $altjobs .=  '<AlternativeJobPositionTitle><TitleCode>'.MFBJOBSAPI::get_id_by_title($job->jobbezeichnung_3).'</TitleCode>
                                                        <Degree>'.$job->jobedudegree3.'</Degree></AlternativeJobPositionTitle>';
                }
                if ( isset($job->jobedudegree1)) {
                    $jobdegree = $job->jobedudegree1; 
                } else {
                    $jobdegree = 1;
                }
            /** / alternative Berufe  ***/
            
            if (  isset ($jobmeta->jobcertfreetitle) && $jobmeta->jobcertfreetitle != "") {
                $certs .= ' <CertificationName>'.$jobmeta->jobcertfreetitle.'</CertificationName>
                                                                <Description>'.$jobmeta->jobcertfreedesc.'</Description>
                                                            </Certification>';
            }
                                                              
            
            if ( isset($jobmeta->jobtitle) && $jobmeta->jobtitle != "") {
       
            //<Allianzpartnernummer>-<beliebigeEinzigartigeZeichenkette>-S
            $jobPositioningPostingId = MFBJOBSAPI_AllianceID.'-'.$job->ID.'2018-S';
            $xmlcontent .= '<Data>
                                        <JobPositionPosting>
                                            <JobPositionPostingId>'.$jobPositioningPostingId.'</JobPositionPostingId>
                                            <HiringOrg>
                                                <HiringOrgName>'.MFBJOBSAPI_HiringOrg.'</HiringOrgName>
                                                <HiringOrgId>'.MFBJOBSAPI_HiringOrgID.'</HiringOrgId>
                                                <ProfileWebSite>'.MFBJOBSAPI_HiringOrgWeb.'</ProfileWebSite>
                                                <HiringOrgSize>2</HiringOrgSize>
                                                <Industry>
                                                    <NAICS>'.MFBJOBSAPI_NAICS.'</NAICS>
                                                </Industry>
                                                <Contact>
                                                    <Salutation>'.MFBJOBSAPI_HiringOrgContactSalutation.'</Salutation>
                                                    <Title/>
                                                    <GivenName>'.MFBJOBSAPI_HiringOrgContactGivenName.'</GivenName>
                                                    <NamePrefix/>
                                                    <FamilyName>'.MFBJOBSAPI_HiringOrgContactName.'</FamilyName>
                                                    <PositionTitle/>
                                                    <!--
                                                        PostalAddress: Es darf entweder das Feld "StreetName" oder das Feld "PostOfficeBox" befüllt sein. Sind beide befüllt, wird das SteA abgewiesen.
                                                    -->
                                                    <PostalAddress>
                                                        <CountryCode>'.MFBJOBSAPI_HiringOrgContactCountry.'</CountryCode>
                                                        <PostalCode>'.MFBJOBSAPI_HiringOrgContactZip.'</PostalCode>
                                                        <Region>'.MFBJOBSAPI_HiringOrgContactRegion.'</Region>
                                                        <Municipality>'.MFBJOBSAPI_HiringOrgContactCity.'</Municipality>
                                                        <District/>
                                                        <AddressLine/>
                                                        <StreetName>'.MFBJOBSAPI_HiringOrgContactStreet.'</StreetName>
                                                        <PostOfficeBox/>
                                                    </PostalAddress>
                                                    <VoiceNumber>
                                                        <IntlCode>'.MFBJOBSAPI_HiringOrgContactPhoneCode.'</IntlCode>
                                                        <AreaCode>'.MFBJOBSAPI_HiringOrgContactAreaCode.'</AreaCode>
                                                        <TelNumber>'.MFBJOBSAPI_HiringOrgContactPhoneNr.'</TelNumber>
                                                    </VoiceNumber>
                                                    <MobilNumber>
                                                        <IntlCode>'.MFBJOBSAPI_HiringOrgContactPhoneCode.'</IntlCode>
                                                        <AreaCode>'.MFBJOBSAPI_HiringOrgContactMobileAreaCode.'</AreaCode>
                                                        <TelNumber>'.MFBJOBSAPI_HiringOrgContactMobileNr.'</TelNumber>
                                                    </MobilNumber>
                                                    <FaxNumber>
                                                        <IntlCode>'.MFBJOBSAPI_HiringOrgContactPhoneCode.'</IntlCode>
                                                        <AreaCode>'.MFBJOBSAPI_HiringOrgContactAreaCode.'</AreaCode>
                                                        <TelNumber>'.MFBJOBSAPI_HiringOrgContactPhoneNr.'</TelNumber>
                                                    </FaxNumber>
                                                    <EMail/>
                                                    <GeneralWebSite>'.MFBJOBSAPI_HiringOrgWeb.'</GeneralWebSite>
                                                </Contact>
                                            </HiringOrg>			
                                            <PostDetail>      
                                                <StartDate>'.date('Y-m-d').'</StartDate>
                                                <EndDate>'.$jobmeta->jobend.'</EndDate>
                                                <LastModificationDate>'.date('Y-m-d').'T'.date('H:i:s').'+02:00'.'</LastModificationDate>
                                                <Status>'.$jobmeta->jobstatus.'</Status>
                                                <Action>'.$jobmeta->jobaction.'</Action>
                                                <SupplierId>'.MFBJOBSAPI_SupplierID.'</SupplierId>
                                                <SupplierName>'.MFBJOBSAPI_HiringOrg.'</SupplierName>
                                                <SupplierIndustrie>'.MFBJOBSAPI_SupplierIndustry.'</SupplierIndustrie>
                                                <InternetReference>'.MFBJOBSAPI_HiringOrgWeb.'/'.$job->slug.'</InternetReference>
                                                <PostedBy>
                                                    <Contact>
                                                    <Company>'.MFBJOBSAPI_HiringOrg.'</Company>
                                                    <Salutation>'.MFBJOBSAPI_HiringOrgContactSalutation.'</Salutation>
                                                    <Title/>
                                                    <GivenName>'.MFBJOBSAPI_HiringOrgContactGivenName.'</GivenName>
                                                    <NamePrefix/>
                                                    <FamilyName>'.MFBJOBSAPI_HiringOrgContactName.'</FamilyName>
                                                        <PositionTitle>'.MFBJOBSAPI_HiringOrgContactPositionTitle.'</PositionTitle>
                                                        <PostalAddress>
                                                            <CountryCode>'.MFBJOBSAPI_HiringOrgContactCountry.'</CountryCode>
                                                            <PostalCode>'.MFBJOBSAPI_HiringOrgContactZip.'</PostalCode>
                                                            <Region>'.MFBJOBSAPI_HiringOrgContactRegion.'</Region>
                                                            <Municipality>'.MFBJOBSAPI_HiringOrgContactCity.'</Municipality>
                                                            <District/>
                                                            <AddressLine/>
                                                            <StreetName>'.MFBJOBSAPI_HiringOrgContactStreet.'</StreetName>
                                                            <PostOfficeBox/>
                                                        </PostalAddress>
                                                        <VoiceNumber>
                                                            <IntlCode>'.MFBJOBSAPI_HiringOrgContactPhoneCode.'</IntlCode>
                                                            <AreaCode>'.MFBJOBSAPI_HiringOrgContactAreaCode.'</AreaCode>
                                                            <TelNumber>'.MFBJOBSAPI_HiringOrgContactPhoneNr.'</TelNumber>
                                                        </VoiceNumber>
                                                        <MobilNumber>
                                                            <IntlCode>'.MFBJOBSAPI_HiringOrgContactPhoneCode.'</IntlCode>
                                                            <AreaCode>'.MFBJOBSAPI_HiringOrgContactMobileAreaCode.'</AreaCode>
                                                            <TelNumber>'.MFBJOBSAPI_HiringOrgContactMobileNr.'</TelNumber>
                                                        </MobilNumber>
                                                        <FaxNumber>
                                                            <IntlCode>'.MFBJOBSAPI_HiringOrgContactPhoneCode.'</IntlCode>
                                                            <AreaCode>'.MFBJOBSAPI_HiringOrgContactAreaCode.'</AreaCode>
                                                            <TelNumber>'.MFBJOBSAPI_HiringOrgContactPhoneNr.'</TelNumber>
                                                        </FaxNumber>
                                                        <EMail>'.MFBJOBSAPI_HiringOrgEmail.'</EMail>
                                                        <JobContactWebSite>'.MFBJOBSAPI_HiringOrgWeb.'/'.$job->slug.'</JobContactWebSite>';
                                        if ( $jobmeta->jobapplication == 4 ) {                 
                                                       $xmlcontent .= '<InterviewContact>
                                                            <Salutation>'.MFBJOBSAPI_HiringOrgContactSalutation.'</Salutation>
                                                            <Title/>
                                                            <GivenName>'.MFBJOBSAPI_HiringOrgGivenName.'</GivenName>
                                                            <NamePrefix/>
                                                            <FamilyName>'.MFBJOBSAPI_HiringOrgContactName.'</FamilyName>
                                                            <PositionTitle>'.MFBJOBSAPI_HiringOrgContactPositionTitle.'</PositionTitle>
                                                            <VoiceNumber>
                                                                <IntlCode>'.MFBJOBSAPI_HiringOrgContactPhoneCode.'</IntlCode>
                                                                <AreaCode>'.MFBJOBSAPI_HiringOrgContactAreaCode.'</AreaCode>
                                                                <TelNumber>'.MFBJOBSAPI_HiringOrgContactPhoneNr.'</TelNumber>
                                                            </VoiceNumber>
                                                            <MobilNumber>
                                                                <IntlCode>'.MFBJOBSAPI_HiringOrgContactPhoneCode.'</IntlCode>
                                                                <AreaCode>'.MFBJOBSAPI_HiringOrgContactMobileAreaCode.'</AreaCode>
                                                                <TelNumber>'.MFBJOBSAPI_HiringOrgContactMobileNr.'</TelNumber>
                                                            </MobilNumber>
                                                            <FaxNumber>
                                                                <IntlCode>'.MFBJOBSAPI_HiringOrgContactPhoneCode.'</IntlCode>
                                                                <AreaCode>'.MFBJOBSAPI_HiringOrgContactAreaCode.'</AreaCode>
                                                                <TelNumber>'.MFBJOBSAPI_HiringOrgContactPhoneNr.'</TelNumber>
                                                            </FaxNumber>
                                                            <EMail>'.MFBJOBSAPI_HiringOrgEmail.'</EMail>
                                                        </InterviewContact>
                                                        <InterviewLocation>
                                                            <Location>'.MFBJOBSAPI_HiringOrgWeb.', '.MFBJOBSAPI_HiringOrgContactCity.'</Location>
                                                            <CountryCode>'.MFBJOBSAPI_HiringOrgContactCountry.'</CountryCode>
                                                            <PostalCode>'.MFBJOBSAPI_HiringOrgContactZip.'</PostalCode>
                                                            <Region>'.MFBJOBSAPI_HiringOrgContactRegion.'</Region>
                                                            <Municipality>'.MFBJOBSAPI_HiringOrgContactCity.'</Municipality>
                                                            <District/>
                                                            <AddressLine/>
                                                            <StreetName>'.MFBJOBSAPI_HiringOrgContactStreet.'</StreetName>
                                                        </InterviewLocation>
                                                        <Interview>
                                                            <InterviewDate></InterviewDate>
                                                            <InterviewTime>0</InterviewTime>
                                                            <RoomNumber></RoomNumber>
                                                            <AdditionalInformation></AdditionalInformation>
                                                        </Interview>';
                                        }
                
                                        $xmlcontent .= '</Contact>
                                                </PostedBy>
                                                <BASupervision>0</BASupervision>
                                                <SupervisionDesired>0</SupervisionDesired>';
            if ( $jobmeta->supervision == 1 ) {          
                                                $xmlcontent .= '<BAContact>
                                                    <Department/>
                                                    <Salutation>2</Salutation>
                                                    <Title/>
                                                    <NamePrefix></NamePrefix>
                                                    <FamilyName/>
                                                    <PostalAddress>
                                                        <CountryCode></CountryCode>
                                                        <PostalCode></PostalCode>
                                                        <Region></Region>
                                                        <Municipality></Municipality>
                                                        <District/>
                                                        <AddressLine/>
                                                        <StreetName></StreetName>
                                                    </PostalAddress>
                                                    <VoiceNumber>
                                                        <IntlCode></IntlCode>
                                                        <AreaCode></AreaCode>
                                                        <TelNumber></TelNumber>
                                                    </VoiceNumber>
                                                    <FaxNumber>
                                                        <IntlCode></IntlCode>
                                                        <AreaCode></AreaCode>
                                                        <TelNumber></TelNumber>
                                                    </FaxNumber>
                                                    <EMail></EMail>
                                                </BAContact>'; }
                
                                            $xmlcontent .= '</PostDetail>
                                            <JobPositionInformation>
                                                <JobPositionTitle>
                                                    <TitleCode>'.$jobmeta->id.'</TitleCode>
                                                    <Degree>'.$jobdegree.'</Degree>
                                                </JobPositionTitle>
                                               
                                                    '.$altjobs.'
                                               
                                                <JobPositionTitleDescription>'.$job->post_title.'</JobPositionTitleDescription>
                                                <JobOfferType>'.$jobmeta->joboffertype.'</JobOfferType>
                                                <SocialInsurance>'.$jobmeta->jobsocialinsurance.'</SocialInsurance>
                                                <Objective>'.$job->post_content.'</Objective>
                                                <EducationAuthorisation>0</EducationAuthorisation>
                                                <JobPositionDescription>
                                                    <JobPositionLocation>
                                                        '.$locations.'
                                                    </JobPositionLocation>
                                                    <Application>
                                                       '.$applications.'
                                                       '.$applications.'
                                                        <ApplicationStartDate>'.$jobmeta->jobapplicationstart.'</ApplicationStartDate>
                                                        <ApplicationEndDate>'.$jobmeta->jobapplicationend.'</ApplicationEndDate>
                                                        <EnclosuresRequired/>
                                                    </Application>
                                                    <Leadership>'.$jobmeta->jobleadership.'</Leadership>
                                                    <MiniJob>0</MiniJob>
                                                    <Classification>
                                                        <Schedule>
                                                            <HoursPerWeek>'.$jobmeta->jobhours.'</HoursPerWeek>
                                                            '.$jobworkingplans.'
                                                            <SummaryText/>
                                                        </Schedule>';
if ( $jobmeta->jobtermbool!=2) { $xmlcontent .= '<Duration><TermLength>'.$jobmeta->jobtermlength.'</TermLength>
                                                            <TermDate>'.$jobmeta->jobtermdate.'</TermDate>
                                                            <TemporaryOrRegular>'.$jobmeta->jobtermbool.'</TemporaryOrRegular>
                                                            <TakeOver>'.$jobmeta->jobtermtakeover.'</TakeOver></Duration>';
    
}
                                                           
                                                        
                                                   $xmlcontent .= '</Classification>
                                                    <CompensationDescription>
                                                        <Salary>'.$jobmeta->jobsalary.'</Salary>
                                                        <EmployerPayscaleBound>'.$jobmeta->jobpayscale.'</EmployerPayscaleBound>
                                                        <CollectiveAgreement>'.$jobmeta->jobagreement.'</CollectiveAgreement>
                                                        <InternalCompensation/>
                                                    </CompensationDescription>
                                                    <Housing>'.$jobmeta->jobhousing.'</Housing>
                                                </JobPositionDescription>
                                                <JobPositionRequirements>
                                                    <QualificationsRequired>
                                                        <EducationQualifs>
                                                            <EduDegree>'.$jobmeta->jobhighestdegree.'</EduDegree>
                                                            <EduDegreeRequired>'.$jobmeta->jobdegreemusthave.'</EduDegreeRequired>';
if ( $jobmeta->joboffertype == 4 ){ $xmlcontent .= '<School><SubjectGerman>1</SubjectGerman>
                                                                <SubjectEnglish>1</SubjectEnglish>
                                                                <SubjectMaths>1</SubjectMaths>
                                                                <OtherSubjects/>
                                                            </School>'; }
                                   
                                                        $xmlcontent .= ' </EducationQualifs>';
    if ( $jobmeta->jobleadershiptype > 0 ){ $xmlcontent .= '<ManagementQualifs>
                                                            <LeadershipType>'.$jobmeta->jobleadershiptype.'</LeadershipType>
                                                            <Authority>'.$jobmeta->jobauth.'</Authority>
                                                            <LeadershipEx>'.$jobmeta->jobleadershipex.'</LeadershipEx>
                                                            <BudgetResp>'.$jobmeta->jobbudget.'</BudgetResp>
                                                            <EmployeeResp>'.$jobmeta->jobemployers.'</EmployeeResp>
                                                        </ManagementQualifs>'; }
                                                        
                                        $xmlcontent .= '<LanguageQualifs>
                                                           '.$langs.'
                                                        </LanguageQualifs>
                                                        <ProfessionalTrainingQualifs>
                                                            <AdditionalInformation>'.$jobmeta->jobknowledgeother_val.'</AdditionalInformation>
                                                            <ProfessionalTraining>
                                                                <Title>
                                                                    <TitleCode>'.$jobmeta->jobknowledge.'</TitleCode>
                                                                    <Degree>'.$jobmeta->jobknowledgehs.'</Degree>
                                                                </Title>
                                                            </ProfessionalTraining>
                                                        </ProfessionalTrainingQualifs>
                                                        <Licences>'.$licenses.'
                                                        </Licences>';
                                                        
                                  if ( $certs != "") {    $xmlcontent.='<CertificationQualifs>
                                                           '.$certs.'
                                                        </CertificationQualifs>'; } 
                                  
                                                         $xmlcontent.='<SkillQualifs>
                                                          
                                                                '.$skillshard.' 
                                                           
                                                          
                                                                '.$skillssoft.'
                                                          
                                                        </SkillQualifs>
                                                        <Mobility>
                                                            
                                                                '.$drive.'
                                                         
                                                            <Vehicle>
                                                                <Car>'.$jobmeta->jobpkw.'</Car>
                                                                <Motorcycle>0</Motorcycle>
                                                                <Truck>'.$jobmeta->joblkw.'</Truck>
                                                                <Omnibus>0</Omnibus>
                                                            </Vehicle>
                                                        </Mobility>
                                                    </QualificationsRequired>
                                                    <ProfessionalExperience>'.$jobmeta->jobknow.'</ProfessionalExperience>
                                                    <TravelRequired>1</TravelRequired>
                                                    <Handicap>2</Handicap>
                                                </JobPositionRequirements>
                                                <NumberToFill>'.$jobmeta->jobfullfill .'</NumberToFill>
                                                <AssignmentStartDate>'.$jobmeta->jobstart.'</AssignmentStartDate>
                                                <AssignmentEndDate>'.$jobmeta->jobend.'</AssignmentEndDate>
                                            </JobPositionInformation>
                                            <BAInternalInformation>
                                                <InternalInformation/>
                                                <MaximumPlacementSuggestions>0</MaximumPlacementSuggestions>
                                                <PlacementSuggestionsIssued>0</PlacementSuggestionsIssued>
                                                <MatchingStrategy>1</MatchingStrategy>
                                            </BAInternalInformation>
                                        </JobPositionPosting>';
                if ( $jobmeta->jobaction == 3) {
                                       $xmlcontent.='<DeleteEntry>
                                            <EntryId>'.$jobPositioningPostingId.'</EntryId>
                                        </DeleteEntry>';
                }
                                   $xmlcontent.= '</Data>'; 
            } // end foreach-loop through jobs
         }
        
        $xmlcontent .= MFBJOBSAPI::get_xml_footer();
        
        /*
        * filename per session, better to handle for the customer
        *
        */
        /*
        if ( empty($_SESSION['fulltimestr']) ) {
            
            $datestr = date('Y-m-d');
            $timestr = date('H-i-s');
            $_SESSION['xmlcontent'] = $xmlcontent;
            $_SESSION['fulltimestr'] = $datestr."_".$timestr;
            $_SESSION['filename'] = "../../DSV000311000_".$_SESSION['fulltimestr'].".xml";
          
        }
        else {
            
            $_SESSION['fulltimestr'] = $_SESSION['fulltimestr']; 
            $_SESSION['xmlcontent'] = $xmlcontent;
            $_SESSION['filename'] = $_SESSION['filename'];
            $_SESSION['filename'] = "../../DSV000311000_".$_SESSION['fulltimestr'].".xml";
        }*/
             $datestr = date('Y-m-d');
            $timestr = date('H-i-s');
            $fulltimestr = $datestr."_".$timestr;
            $filename = "../../DSV000311000_".$fulltimestr.".xml";
            $_SESSION['filename'] = "../../DSV000311000_".$fulltimestr.".xml";
            $xmlphyfile = fopen(dirname(__FILE__).'/'. $_SESSION['filename'],'w' );
		    fwrite($xmlphyfile, $xmlcontent); 
            fclose($xmlphyfile);
        
      //  echo session_id();
        
   
    }
   

}


?>