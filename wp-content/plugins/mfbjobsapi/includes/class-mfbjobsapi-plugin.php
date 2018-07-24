<?php
class MFBJOBSAPI {
    
    public function __construct()
    {
        add_shortcode('MFBJOBSAPI_Version', array($this, 'get_mfjobsapi_version'));
    }
    
    public function get_mfjobsapi_version() {
        echo '<h1>Version: '.PLUGIN_MFBJOBSAPI_VERSION.'</h1>';
    }
    
    

}


?>