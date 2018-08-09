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
        define('MFBJOBSAPI_AllianceID','13739');
        define('MFBJOBSAPI_BACustomer','08791942');
        define('MFBJOBSAPI_SupplierIndustry',5); //5 = Arbeitgeber
        define('MFBJOBSAPI_HiringOrg','TVS Personalservice GmbH');
        define('MFBJOBSAPI_HiringOrgWeb','http://www.tvs-personalservice.de');
        define('MFBJOBSAPI_HiringOrgEmail','info@tvspersonalservice.de');
        define('MFBJOBSAPI_HiringOrgContactSalutation',1); // 1 = Sehr geehrter, 2 = Sehr geehrte, 3 = Sehr geehrte/r Herr / Frau
        define('MFBJOBSAPI_HiringOrgContactGivenName','Stefan');
        define('MFBJOBSAPI_HiringOrgContactName','Schmidt');
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
		
		$mysqli = @new mysqli('localhost', 'root', 'root', 'mfbjobsapi');
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
        return '<BAInternalInformation>
				<InternalInformation/>
				<MaximumPlacementSuggestions>0</MaximumPlacementSuggestions>
				<PlacementSuggestionsIssued>0</PlacementSuggestionsIssued>
				<MatchingStrategy>1</MatchingStrategy>
			</BAInternalInformation>
		</JobPositionPosting>
		<DeleteEntry>
			<EntryId>a</EntryId>
		</DeleteEntry>
	</Data></HRBAXMLJobPositionPosting>';
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
				if ( $obj->meta_key == "job_id") {
                    $job->id = $obj->meta_value;
                } 
                if ( $obj->meta_key == "jobbezeichnung") {
                    $job->jobtitle = $obj->meta_value;
                } 
                if ( $obj->meta_key == "job_bkz") {
                    $job->bkz = $obj->meta_value;
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
                    $job->jobapplication = $obj->meta_value;
                } 
                if ( $obj->meta_key == "jobleadership") {
                    $job->jobleadership = $obj->meta_value;
                } 
                if ( $obj->meta_key == "jobhours") {
                    $job->jobhours = $obj->meta_value;
                }  
                if ( $obj->meta_key == "jobworkingplan") {
                    $job->jobworkingplan = $obj->meta_value;
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
                    $job->joblicense_1 = $obj->meta_value;
                } 
                if ( $obj->meta_key == "joblicense_2") {
                    $job->joblicense_2 = $obj->meta_value;
                } 
                if ( $obj->meta_key == "joblicense_3") {
                    $job->joblicense_3 = $obj->meta_value; 
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
                if ( $obj->meta_key == "jobemployers") {
                    $job->jobemployers = $obj->meta_value; 
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
                        $job->jobskilldrive[$i] = $obj->meta_value; 
                    }
                    if ( $obj->meta_key == "jobskilldrivevalue_".$i) {
                        $job->jobskilldrivevalue[$i] = $obj->meta_value; 
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
        // some vars
       
         
        //loop through jobs
        foreach ( $jobs as $job ) {
            $jobmeta = MFBJOBSAPI::get_job_meta($job->ID); 
            for ( $i=1; $i<6; $i++) {
                if ($jobmeta->jobskillsoft[$i] ) {
                    $skillssoft .=  '<SkillName>'.$jobmeta->jobskillsoft[$i].'</SkillName>';
                }
                if ($jobmeta->jobskill[$i] ) {
                    $skillshard .=  '<SkillName>'.$jobmeta->jobskill[$i].'</SkillName>
                                    <SkillLevel>'.$jobmeta->jobskillvalue[$i].'</SkillLevel>';
                }
                
            }
            for ( $i=1; $i<11; $i++) {
                if ($jobmeta->joblocationplz[$i] && $jobmeta->joblocationcity[$i] ) {
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
                if ($jobmeta->jobskilldrive[$i] ) {
                    $drive .=  '<DrivingLicenceName>'.$jobmeta->jobskilldrive[$i].'</DrivingLicenceName>
                                                                <DrivingLicenceRequired>'.$jobmeta->jobskilldrivevalue[$i].'</DrivingLicenceRequired>';
                }
                
            }
            if ( $job->joblicense_1 && $job->joblicense_1 != "") {
                $licenses .= "<Licence><LicenceName>".$job->joblicense_1."</LicenceName>                                                                <LicenceLevel>".$job->joblicenselevel_1."</LicenceLevel>
                </Licence>";
            }
            
            if ( $job->joblicense_2 && $job->joblicense_2 != "") {
                $licenses .= "<Licence><LicenceName>".$job->joblicense_2."</LicenceName>                                                                <LicenceLevel>".$job->joblicenselevel_2."</LicenceLevel>
                </Licence>";
            }
            
            if ( $job->joblicense_3 && $job->joblicense_3 != "") {
                $licenses .= "<Licence><LicenceName>".$job->joblicense_3."</LicenceName>                                                                <LicenceLevel>".$job->joblicenselevel_3."</LicenceLevel>
                </Licence>";
            }
             
            if ( $jobmeta->jobtitle && $jobmeta->jobtitle != "") {
       
            //<Allianzpartnernummer>-<beliebigeEinzigartigeZeichenkette>-S
            $jobPositioningPostingId = MFBJOBSAPI_AllianceID.'-'.$job->ID.'2018-S';
            $xmlcontent .= '<Data>
                                        <JobPositionPosting>
                                            <JobPositionPostingId>'.$jobPositioningPostingId.'</JobPositionPostingId>
                                            <HiringOrg>
                                                <HiringOrgName>'.MFBJOBSAPI_HiringOrg.'</HiringOrgName>
                                                <HiringOrgId>'.MFBJOBSAPI_BACustomer.'</HiringOrgId>
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
                                                        <Municipality/>
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
                                                <LastModificationDate/>
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
                                                            <Municipality/>
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
                                                        <JobContactWebSite>'.MFBJOBSAPI_HiringOrgWeb.'/'.$job->slug.'</JobContactWebSite>
                                                        <InterviewContact>
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
                                                            <Municipality/>
                                                            <District/>
                                                            <AddressLine/>
                                                            <StreetName>'.MFBJOBSAPI_HiringOrgContactStreet.'</StreetName>
                                                        </InterviewLocation>
                                                        <Interview>
                                                            <InterviewDate></InterviewDate>
                                                            <InterviewTime>0</InterviewTime>
                                                            <RoomNumber></RoomNumber>
                                                            <AdditionalInformation></AdditionalInformation>
                                                        </Interview>
                                                    </Contact>
                                                </PostedBy>
                                                <BASupervision>0</BASupervision>
                                                <SupervisionDesired>0</SupervisionDesired>                                                
                                                <BAContact>
                                                    <Department/>
                                                    <Salutation></Salutation>
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
                                                </BAContact> 
                                            </PostDetail>
                                            <JobPositionInformation>
                                                <JobPositionTitle>
                                                    <TitleCode>'.$jobmeta->id.'</TitleCode>
                                                    <Degree>1</Degree>
                                                </JobPositionTitle>
                                                <AlternativeJobPositionTitle/>
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
                                                        <KindOfApplication>'.$jobmeta->jobapplication.'</KindOfApplication>
                                                        <ApplicationStartDate>'.date('Y-m-d').'</ApplicationStartDate>
                                                        <ApplicationEndDate></ApplicationEndDate>
                                                        <EnclosuresRequired/>
                                                    </Application>
                                                    <Leadership>'.$jobmeta->jobleadership.'</Leadership>
                                                    <MiniJob>0</MiniJob>
                                                    <Classification>
                                                        <Schedule>
                                                            <HoursPerWeek>'.$jobmeta->jobhours.'</HoursPerWeek>
                                                            <WorkingPlan>'.$jobmeta->jobworkingplan.'</WorkingPlan>
                                                            <SummaryText/>
                                                        </Schedule>
                                                        <Duration>
                                                            <TermLength>'.$jobmeta->jobtermlength.'</TermLength>
                                                            <TermDate>'.$jobmeta->jobtermdate.'</TermDate>
                                                            <TemporaryOrRegular>'.$jobmeta->jobtermbool.'</TemporaryOrRegular>
                                                            <TakeOver>'.$jobmeta->jobtermtakeover.'</TakeOver>
                                                        </Duration>
                                                    </Classification>
                                                    <CompensationDescription>
                                                        <Salary>'.$jobmeta->jobsalary.'</Salary>
                                                        <DailyRate></DailyRate>
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
                                                            <EduDegreeRequired>'.$jobmeta->jobdegreemusthave.'</EduDegreeRequired>
                                                            <School>
                                                                <SubjectGerman>1</SubjectGerman>
                                                                <SubjectEnglish>1</SubjectEnglish>
                                                                <SubjectMaths>1</SubjectMaths>
                                                                <OtherSubjects/>
                                                            </School>
                                                        </EducationQualifs>
                                                        <ManagementQualifs>
                                                            <LeadershipType>'.$jobmeta->jobleadershiptype.'</LeadershipType>
                                                            <Authority>'.$jobmeta->jobauth.'</Authority>
                                                            <LeadershipEx>'.$jobmeta->jobleadershipex.'</LeadershipEx>
                                                            <BudgetResp>'.$jobmeta->jobbudget.'</BudgetResp>
                                                            <EmployeeResp>'.$jobmeta->jobemployers.'</EmployeeResp>
                                                        </ManagementQualifs>
                                                        <LanguageQualifs>
                                                            <Language>
                                                                <LanguageName>0</LanguageName>
                                                                <LanguageLevel>0</LanguageLevel>
                                                            </Language>
                                                        </LanguageQualifs>
                                                        <ProfessionalTrainingQualifs>
                                                            <AdditionalInformation/>
                                                            <ProfessionalTraining>
                                                                <Title>
                                                                    <TitleCode>000</TitleCode>
                                                                    <Degree>1</Degree>
                                                                </Title>
                                                            </ProfessionalTraining>
                                                        </ProfessionalTrainingQualifs>
                                                        <Licences>'.$licenses.'
                                                        </Licences>
                                                        <CertificationQualifs>
                                                            <Certification>
                                                                <CertificationName>a</CertificationName>
                                                                <Description/>
                                                            </Certification>
                                                        </CertificationQualifs>
                                                        <SkillQualifs>
                                                            <HardSkill>
                                                                '.$skillshard.'
                                                            </HardSkill>
                                                            <SoftSkill>
                                                                '.$skillssoft.'
                                                            </SoftSkill>
                                                        </SkillQualifs>
                                                        <Mobility>
                                                            <DrivingLicence>
                                                                '.$drive.'
                                                            </DrivingLicence>
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
                                                <NumberToFill>0</NumberToFill>
                                                <AssignmentStartDate>1967-08-13</AssignmentStartDate>
                                                <AssignmentEndDate>1967-08-13</AssignmentEndDate>
                                            </JobPositionInformation>'; 
            } // end foreach-loop through jobs
         }
        //get xml-footer
        $xmlcontent .= MFBJOBSAPI::get_xml_footer();
        $filename = "../../jobs-ba.xml";
        $xmlphyfile = fopen(dirname(__FILE__).'/'. $filename,'w' );
		fwrite($xmlphyfile, $xmlcontent); 
		fclose($xmlphyfile);
       // return "<form><textarea cols=100 rows=30>". $jobmeta->id.$xmlcontent . "</textarea></form>";
       // print_r($jobs);
        
   
    }
   

}


?>