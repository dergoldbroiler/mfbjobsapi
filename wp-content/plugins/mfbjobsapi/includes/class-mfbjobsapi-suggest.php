<?php

class Suggest {
    
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
    * returns license-ids 
    @returns: array
    */
    public function get_licenses() {
        
        $query = "SELECT * FROM `lizenzen_vam`";
        $licenses_arr = array();
        $mysqli = Suggest::connectToDB();
		if ($result = $mysqli->query($query)) {
			
			/* create single article-object and push to array */
			 while($obj = $result->fetch_object()){ 
                array_push($licenses_arr, $obj->refid);
			 }
		}
        return $licenses_arr;
    }
    
     
    /*
    * returns jobs as links
    @returns: object connectToDB   
    */
    public function get_job_quali( $jobid ) {
        
        $query = "SELECT * FROM `niveau_vam` WHERE `jobid` = ".$jobid;
       
        $mysqli = Suggest::connectToDB();
		if ($result = $mysqli->query($query)) {
			
			/* create single article-object and push to array */
			 while($obj = $result->fetch_object()){ 
              return $obj->niveau;
			 }
		}
    }
    
    /*
    * returns jobs as links
    @returns: object connectToDB   
    */
    public function get_jobs_from_db( $searchquery, $licensename="" ) {
        if ( $_POST['searchtype'] == "job" && $_POST['allowed_type'] == "all" && $_POST['allowed_quali'] == "all") { 
            $query = "SELECT * FROM `jobs_vam` WHERE `bezeichnung` LIKE '%".$searchquery."%' AND kuenstler = 'nein'";
        }
         if ( $_POST['searchtype'] == "job" && $_POST['allowed_type'] != "all" && $_POST['allowed_quali'] != "all") { 
            $query = "SELECT * FROM `jobs_vam` WHERE `bezeichnung` LIKE '%".$searchquery."%' AND `typ` = '".$_POST['allowed_type']."' AND `niveau` = '".$_POST['allowed_quali']."' AND kuenstler = 'nein'";
        }
         if ( $_POST['searchtype'] == "job" && $_POST['allowed_type'] != "all" && $_POST['allowed_quali'] == "all") { 
            $query = "SELECT * FROM `jobs_vam` WHERE `bezeichnung` LIKE '%".$searchquery."%' AND `typ` = '".$_POST['allowed_type']."' AND kuenstler = 'nein'";
        }
        if ( $_POST['searchtype'] == "learning") { 
            $query = "SELECT * FROM `jobs_vam` WHERE `bezeichnung` LIKE '%".$searchquery."%' AND `typ` = 'A' AND kuenstler = 'nein'";
        }
        if ( $_POST['searchtype'] == "licenses") { 
          $query = "SELECT * FROM `komp_vam` WHERE `bezeichnung` LIKE '%".$searchquery."%'";
           
        }
        if ( $_POST['searchtype'] == "skill") { 
            $query = "SELECT * FROM `komp_vam` WHERE `bezeichnung` LIKE '%".$searchquery."%'";
        }
        
        
        $mysqli = Suggest::connectToDB();
      // $licenses = Suggest::get_licenses();
      
		$jobs = "";
		if ($result = $mysqli->query($query)) {
            
			/* create single article-object and push to array */
			 while($obj = $result->fetch_object()){ 
                
                $job = "<a href='#' class='mfbjobsapi_joblink mfbjobsapi_link' data-job-titlelm='".$_POST['titlelm']."' data-result='".$_POST['resultdiv']."' data-job_id='".$obj->_id."'>".$obj->bezeichnung."</a><br>"; 
                /*
                * job normal ausgeben 
                */
                /*$job = "<a href='#' class='mfbjobsapi_joblink mfbjobsapi_link' data-result='".$_POST['resultdiv']."' data-jobtyp='".$obj->typ."' data-license-name='".$_POST['license_name']."' data-job_id='".$obj->_id."' data-zustand='".$obj->zustand."' data-hs='".$obj->hochschulberuf."' data-ebene='".$obj->hochschulberuf."' data-quali='".$quali."' data-job_bkz='".$obj->_bkz."'>".$obj->bezeichnung."</a><br>";  
              */  
                // quali des jeweil. jobs ermitteln um mit mindestwert zu vergleichen 
                $quali = Suggest::get_job_quali($obj->_id);
                 if ( isset($_POST['allowed_quali'])) {
                    $allowed_quali = $_POST['allowed_quali'];
                    if ( strstr ( $allowed_quali,',') != false ) {
                        $allowed_quali_arr = explode(',',$allowed_quali);
                    } else {
                        $allowed_quali_arr[0] = $allowed_quali;
                    }
                 }
                 
                /*
                * geforderte Ausbildung umfasst nur Berufe mit quali 2 und 4 
                */
                if ( $_POST['searchtype'] == "learning") { 
                   if ( $quali == 2 || $quali == 4) {
                         $job = "<a href='#' class='mfbjobsapi_joblink mfbjobsapi_link' data-job-titlelm='".$_POST['titlelm']."' data-result='".$_POST['resultdiv']."' data-jobtyp='".$obj->typ."' data-license-name='".$_POST['license_name']."' data-job_id='".$obj->_id."' data-zustand='".$obj->zustand."' data-hs='".$obj->hochschulberuf."' data-ebene='".$obj->hochschulberuf."' data-quali='".$quali."' data-job_bkz='".$obj->_bkz."'>".$obj->bezeichnung."</a><br>"; 
                    }
                } 
               
                /*
                * Lizenzen
                */ 
                if ( $_POST['searchtype'] == "licenses") { 
                
                  // if ( in_array($obj->_id, $licenses) ) {
                     $job = "<a href='#' class='mfbjobsapi_joblink mfbjobsapi_link'  data-result='".$_POST['resultdiv']."' data-id='".$_POST['titlelm']."' data-job_id='".$obj->_id."'>".$obj->bezeichnung."</a><br>";
                    
                //  }
                   
                }
                 $jobs .= $job; 
               
			 }
		}
        $javascript = "<script>jQuery('.searchformresult a.mfbjobsapi_joblink').click( function(ev){
                ev.preventDefault(); 
           
                //set chosen job as field value
                var jobbezeichnung = jQuery(this).html(); 
                var job_id = jQuery(this).attr('data-job_id');
                var job_bkz = jQuery(this).attr('data-job_bkz');
                var titlelm = jQuery(this).attr('data-job-titlelm');
                var resdiv = jQuery(this).attr('data-result');
                jQuery(titlelm).val(jobbezeichnung);    
                jQuery('#job_id').val(job_id);     
                jQuery('#job_bkz').val(job_bkz);
                jQuery('.'+resdiv).html('');";
        
        if ( isset( $_POST['numberof']) ) {
        $javascript .= "jQuery('.zustand'+".$_POST['numberof'].").html( jQuery(this).attr('data-zustand'));
                    jQuery('.quali'+".$_POST['numberof'].").html(jQuery(this).attr('data-quali'));
                    jQuery('.hs'+".$_POST['numberof'].").html(jQuery(this).attr('data-hs'));
                    jQuery('.ebene'+".$_POST['numberof'].").html(jQuery(this).attr('data-ebene'));
                    //console.log( jQuery('.ebene'+i).html(jQuery(this).attr('data-ebene'))) });";
        } else {
              $javascript .= "});";
        }
             
    $javascript .= "jQuery('.searchformresult2 a.mfbjobsapi_joblink').click( function(ev){
                ev.preventDefault();
                //set chosen job as field value
                var jobbezeichnung = jQuery(this).html(); 
                var job_id = jQuery(this).attr('data-job_id');
                var job_bkz = jQuery(this).attr('data-job_bkz');
                jQuery('#jobeduname').val(job_id); 
                jQuery('.edutit').html(jobbezeichnung);
                jQuery('#job_id').val(job_id);    
                jQuery('#job_bkz').val(job_bkz);
                jQuery('.searchformresult2').html('');});"; 
    $javascript .= "jQuery( '.licenseresult a.mfbjobsapi_joblink').click( function(ev){
                ev.preventDefault();
               // alert(jQuery(this).attr('data-result'));
                
                //set chosen job as field value
                var jobbezeichnung = jQuery(this).html(); 
                var job_id = jQuery(this).attr('data-job_id');
                var resultdiv = jQuery(this).attr('data-result');
                var titlelm = jQuery(this).attr('data-id');
                jQuery(titlelm).val(jobbezeichnung); 
                jQuery('.licenseresult').html(''); 
              
          }); jQuery( '.jobskillresult a.mfbjobsapi_joblink').click( function(ev){
                ev.preventDefault();
              
                
                //set chosen job as field value
                var jobbezeichnung = jQuery(this).html(); 
                var job_id = jQuery(this).attr('data-job_id');
                var lic_name = jQuery(this).attr('data-license-name');
                var job_bkz = jQuery(this).attr('data-job_bkz');
                var resultdiv = jQuery(this).attr('data-result');
                jQuery(resultdiv).val(jobbezeichnung); 
                jQuery(lic_name).html(jobbezeichnung);
                jQuery('.jobskillresult').html('');
          });</script>"; 
        echo $jobs.$javascript;
    }
    
    
}





$suggest = new Suggest();

if ( $_POST['searchquery']) {
    
    //suggestion for jobtitles
    if ( $_POST['searchtype'] == "job") { 
	   $suggest->get_jobs_from_db($_POST['searchquery']);
    }
    //suggestion for jobtitles
    if ( $_POST['searchtype'] == "skill") { 
    
	   $suggest->get_jobs_from_db($_POST['searchquery']);
    }
    
    //suggestion for jobtitles
    if ( $_POST['searchtype'] == "learning") { 
	   $suggest->get_jobs_from_db($_POST['searchquery']);
    }
    
    //suggestion for joblicenses
    if ( $_POST['searchtype'] == "licenses") { 
       
        $suggest->get_jobs_from_db($_POST['searchquery']);
        
    }

}

