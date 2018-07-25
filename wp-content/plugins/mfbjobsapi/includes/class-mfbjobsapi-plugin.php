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
        ++ define all shortcodes here   
        */
    
        // SC to test class, output: Version of Plugin
        add_shortcode('MFBJOBSAPI_Version', array($this, 'get_mfjobsapi_version'));
    }
    
    /*
    * SC Function, corresponding shortcode: MFBJOBSAPI_Version
    */
    public function get_mfjobsapi_version() {
        echo '<h1>Version: '.PLUGIN_MFBJOBSAPI_VERSION.'</h1>';
    }
    
    
    
    /*
    * returns head of xmlfile
    @returns: string    
    */
    
    public function get_xml_head() {
        
    }
    
    /*
    * returns footer of xmlfile
    @returns: string    
    */
    
    public function get_xml_footer() {
        
    }
    
    
    /*
    * get all existing jobs in an array
    * with stdClass Objects, individually defined to fit the needs
    * of arbeitsagentur
    @returns: array   
    */
    
    public function get_all_jobs() {
        
    }

}


?>