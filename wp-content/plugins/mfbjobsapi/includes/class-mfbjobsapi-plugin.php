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
        // some vars
        
        //loop through jobs
        foreach ( $jobs as $job ) {
           
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
            $jobmeta = MFBJOBSAPI::get_job_meta($job->ID);  
            if ( $jobmeta->jobtitle && $jobmeta->jobtitle != "") {
            print_r($jobmeta);
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
                                                            <GivenName>a</GivenName>
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
                                                            <Location></Location>
                                                            <CountryCode>'.MFBJOBSAPI_HiringOrgContactCountry.'</CountryCode>
                                                            <PostalCode>a</PostalCode>
                                                            <Region>1</Region>
                                                            <Municipality>a</Municipality>
                                                            <District/>
                                                            <AddressLine/>
                                                            <StreetName>a</StreetName>
                                                        </InterviewLocation>
                                                        <Interview>
                                                            <InterviewDate>1967-08-13</InterviewDate>
                                                            <InterviewTime>00:00</InterviewTime>
                                                            <RoomNumber>a</RoomNumber>
                                                            <AdditionalInformation>a</AdditionalInformation>
                                                        </Interview>
                                                    </Contact>
                                                </PostedBy>
                                                <BASupervision>0</BASupervision>
                                                <SupervisionDesired>0</SupervisionDesired>                                                
                                                <BAContact>
                                                    <Department/>
                                                    <Salutation>1</Salutation>
                                                    <Title/>
                                                    <NamePrefix>String</NamePrefix>
                                                    <FamilyName/>
                                                    <PostalAddress>
                                                        <CountryCode>AA</CountryCode>
                                                        <PostalCode>a</PostalCode>
                                                        <Region>1</Region>
                                                        <Municipality>a</Municipality>
                                                        <District/>
                                                        <AddressLine/>
                                                        <StreetName>a</StreetName>
                                                    </PostalAddress>
                                                    <VoiceNumber>
                                                        <IntlCode>+0</IntlCode>
                                                        <AreaCode>99999999999999</AreaCode>
                                                        <TelNumber>9999999999999999999999999</TelNumber>
                                                    </VoiceNumber>
                                                    <FaxNumber>
                                                        <IntlCode>+0</IntlCode>
                                                        <AreaCode>99999999999999</AreaCode>
                                                        <TelNumber>9999999999999999999999999</TelNumber>
                                                    </FaxNumber>
                                                    <EMail>a</EMail>
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
                                                <SpecialEngagement>1</SpecialEngagement>
                                                <SocialInsurance>'.$jobmeta->jobsocialinsurance.'</SocialInsurance>
                                                <Objective>'.$job->post_content.'</Objective>
                                                <EducationAuthorisation>0</EducationAuthorisation>
                                                <JobPositionDescription>
                                                    <JobPositionLocation>
                                                        <Location>
                                                            <CountryCode>DE</CountryCode>
                                                            <PostalCode>'.$jobmeta->geolocation_postcode.'</PostalCode>
                                                            <Region>'.$jobmeta->state_long.'</Region>
                                                            <Municipality>'.$jobmeta->geolocation_city.'</Municipality>
                                                            <District/>
                                                            <AddressLine/>
                                                            <StreetName/>
                                                        </Location>
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
                                                            <TermLength>'.$jobmeta->jobworkingplan.'</TermLength>
                                                            <TermDate>'.$jobmeta->jobworkingplan.'</TermDate>
                                                            <TemporaryOrRegular>'.$jobmeta->jobworkingplan.'</TemporaryOrRegular>
                                                            <TakeOver>'.$jobmeta->jobworkingplan.'</TakeOver>
                                                        </Duration>
                                                    </Classification>
                                                    <CompensationDescription>
                                                        <Salary/>
                                                        <DailyRate>1</DailyRate>
                                                        <EmployerPayscaleBound>0</EmployerPayscaleBound>
                                                        <CollectiveAgreement/>
                                                        <InternalCompensation/>
                                                    </CompensationDescription>
                                                    <Housing>0</Housing>
                                                </JobPositionDescription>
                                                <JobPositionRequirements>
                                                    <QualificationsRequired>
                                                        <EducationQualifs>
                                                            <EduDegree>1</EduDegree>
                                                            <EduDegreeRequired>1</EduDegreeRequired>
                                                            <School>
                                                                <SubjectGerman>1</SubjectGerman>
                                                                <SubjectEnglish>1</SubjectEnglish>
                                                                <SubjectMaths>1</SubjectMaths>
                                                                <OtherSubjects/>
                                                            </School>
                                                        </EducationQualifs>
                                                        <ManagementQualifs>
                                                            <LeadershipType>1</LeadershipType>
                                                            <Authority>1</Authority>
                                                            <LeadershipEx>1</LeadershipEx>
                                                            <BudgetResp>1</BudgetResp>
                                                            <EmployeeResp>1</EmployeeResp>
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
                                                                <SkillName>0</SkillName>
                                                                <SkillLevel>1</SkillLevel>
                                                            </HardSkill>
                                                            <SoftSkill>
                                                                <SkillName>1</SkillName>
                                                            </SoftSkill>
                                                        </SkillQualifs>
                                                        <Mobility>
                                                            <DrivingLicence>
                                                                <DrivingLicenceName>0</DrivingLicenceName>
                                                                <DrivingLicenceRequired>1</DrivingLicenceRequired>
                                                            </DrivingLicence>
                                                            <Vehicle>
                                                                <Car>0</Car>
                                                                <Motorcycle>0</Motorcycle>
                                                                <Truck>0</Truck>
                                                                <Omnibus>0</Omnibus>
                                                            </Vehicle>
                                                        </Mobility>
                                                    </QualificationsRequired>
                                                    <ProfessionalExperience>1</ProfessionalExperience>
                                                    <TravelRequired>1</TravelRequired>
                                                    <Handicap>1</Handicap>
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