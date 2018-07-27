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
        add_shortcode('MFBJOBSAPI_xmljobs', array($this, 'get_jobs_from_xml'));
       
        
        /*
        * CONSTANTS
        */
        define('MFBJOBSAPI_SupplierID','V000311000');
        define('MFBJOBSAPI_SupplierIndustry',5); //5 = Arbeitgeber
        define('MFBJOBSAPI_HiringOrg','TVS Personalservice GmbH');
        define('MFBJOBSAPI_HiringOrgWeb','http://www.tvs-personalservice.de');
        define('MFBJOBSAPI_HiringOrgEmail','info@tvspersonalservice.de');
        define('MFBJOBSAPI_HiringOrgContactSalutation',1); // 1 = Sehr geehrter, 2 = Sehr geehrte, 3 = Sehr geehrte/r Herr / Frau
        define('MFBJOBSAPI_HiringOrgContactName','Schmidt');
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
        $xmlcontent = '<?xml version="1.0" encoding="UTF-8"?><!--XML-Beispieldatei von XMLSpy generiert v2008 rel. 2 sp1 (http://www.altova.com)-->         
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
    }
    
    
    
    /*
    * returns jobs as select list
    @returns: object connectToDB   
    */
    public function get_jobs_from_xml() {
        $xmlFileName = 'http://localhost:8888/mfbjobsapi/wp-content/plugins/mfbjobsapi/assets/arbeitsagentur/vam_beruf_kurz_P22_Testdatei.xml';
       // $xmlString = $xmlfile; 

        $doc = new DOMDocument();
        $doc->load($xmlFileName);
        $domElm = $doc->documentElement;
        $xmlNodes = $domElm->childNodes;
        foreach ($xmlNodes AS $item) {
            $singleNode = $item->childNodes;
              foreach ($singleNode AS $single) {
                 $singleNode = $single->childNodes;
              foreach ($singleNode AS $single) {
                print_r($single);
              }
              }
        }
        
        
    }
    
    public function get_node_content($nodes, $level){
        $jobs = array();
        $job = new stdClass();
        
        foreach ($nodes AS $item) {
        MFBJOBSAPI::printValues($item, $level);
          if ($item->nodeType == 1) { //DOMElement
              foreach ($item->attributes AS $itemAtt) {
                  MFBJOBSAPI::printValues($itemAtt, $level+3);
              }
              if($item->childNodes || $item->childNodes->lenth > 0) {
                  MFBJOBSAPI::get_node_content($item->childNodes, 1);
              }
          }
        }
}
   
public function printValues($item, $level){
    $job = new stdClass();
    if ($item->nodeType == 1) { //DOMElement
      /*  MFBJOBSAPI::printLevel($level);
        print $item->nodeName . " = " . $item->nodeValue;*/
    }
    if ($item->nodeType == 2) { //DOMAttr
        MFBJOBSAPI::printLevel($level);
        
        if ( $item->name == "id") {
            $job->jobid = $item->value;
        }
        
        if ( $item->name == "bkz") {
            $job->bkz = $item->value;
        }
         if ( $item->name == "kuenstler") {
            $job->kuenstler = $item->value;
        }
    }
    if ($item->nodeType == 3) { //DOMText
       // print_r($item);
        print $item->nodeName . " = " . $item->value ;
      if ($item->isWhitespaceInElementContent() == false){
        MFBJOBSAPI::printLevel($level);
        print $item->wholeText ;
      }
    }
    
    print_r($job);
}

    public function get_object_content($item, $level, $which){
        if ($item->nodeType == 2 && $item->nodeName == $which) { //DOMAttr
           // MFBJOBSAPI::printLevel($level);
           // print $item->name . " = " . $item->value ;
            return $item->value;
        }
       /* if ($item->nodeType == 3) { //DOMText
          if ($item->isWhitespaceInElementContent() == false){
            MFBJOBSAPI::printLevel($level);
            print $item->wholeText ;
          }
        } */
        return false;
    }

public function printLevel($level)
{
    print "<br>";
    if ($level == 0) {
        print "<br>";
    }
    for($i=0; $i < $level; $i++) {
        print "-";
    }
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
        
        // some vars
        
        //loop through jobs
        foreach ( $jobs as $job ) {
            $jobmeta = MFBJOBSAPI::get_job_meta($job->ID);
            $xmlcontent .= '<Data>
                                        <JobPositionPosting>
                                            <JobPositionPostingId>'.$job->ID.'</JobPositionPostingId>
                                            <HiringOrg>
                                                <HiringOrgName>'.MFBJOBSAPI_HiringOrg.'</HiringOrgName>
                                                <HiringOrgId>'.MFBJOBSAPI_SupplierID.'</HiringOrgId>
                                                <ProfileWebSite>'.MFBJOBSAPI_HiringOrgWeb.'</ProfileWebSite>
                                                <HiringOrgSize>2</HiringOrgSize>
                                                <Industry>
                                                    <NAICS>'.MFBJOBSAPI_NAICS.'</NAICS>
                                                </Industry>
                                                <Contact>
                                                    <Salutation>'.MFBJOBSAPI_HiringOrgContactSalutation.'</Salutation>
                                                    <Title/>
                                                    <GivenName/>
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
                                                <StartDate>1967-08-13</StartDate>
                                                <EndDate>1967-08-13</EndDate>
                                                <LastModificationDate>2001-12-17T09:30:47.0Z</LastModificationDate>
                                                <Status>1</Status>
                                                <Action>1</Action>
                                                <SupplierId>'.MFBJOBSAPI_SupplierID.'</SupplierId>
                                                <SupplierName>'.MFBJOBSAPI_HiringOrg.'</SupplierName>
                                                <SupplierIndustrie>'.MFBJOBSAPI_SupplierIndustry.'</SupplierIndustrie>
                                                <InternetReference>'.MFBJOBSAPI_HiringOrgWeb.'/'.$job->slug.'</InternetReference>
                                                <PostedBy>
                                                    <Contact>
                                                    <Salutation>'.MFBJOBSAPI_HiringOrgContactSalutation.'</Salutation>
                                                    <Title/>
                                                    <GivenName/>
                                                    <NamePrefix/>
                                                    <FamilyName>'.MFBJOBSAPI_HiringOrgContactName.'</FamilyName>
                                                        <PositionTitle/>
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
                                                        <JobContactWebSite/>
                                                        <InterviewContact>
                                                            <Salutation>1</Salutation>
                                                            <Title/>
                                                            <GivenName>a</GivenName>
                                                            <NamePrefix/>
                                                            <FamilyName>a</FamilyName>
                                                            <PositionTitle>a</PositionTitle>
                                                            <VoiceNumber>
                                                                <IntlCode>+0</IntlCode>
                                                                <AreaCode>99999999999999</AreaCode>
                                                                <TelNumber>9999999999999999999999999</TelNumber>
                                                            </VoiceNumber>
                                                            <MobilNumber>
                                                                <IntlCode>+0</IntlCode>
                                                                <AreaCode>99999999999999</AreaCode>
                                                                <TelNumber>9999999999999999999999999</TelNumber>
                                                            </MobilNumber>
                                                            <FaxNumber>
                                                                <IntlCode>+0</IntlCode>
                                                                <AreaCode>99999999999999</AreaCode>
                                                                <TelNumber>9999999999999999999999999</TelNumber>
                                                            </FaxNumber>
                                                            <EMail>a</EMail>
                                                        </InterviewContact>
                                                        <InterviewLocation>
                                                            <Location>a</Location>
                                                            <CountryCode>AA</CountryCode>
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
                                            </PostDetail>'; 
        } // end foreach-loop through jobs
        
        //get xml-footer
        $xmlcontent .= MFBJOBSAPI::get_xml_footer();
        echo "<form><textarea cols=100 rows=30>". $xmlcontent . "</textarea></form>";
       // print_r($jobs);
        
    }

}


?>