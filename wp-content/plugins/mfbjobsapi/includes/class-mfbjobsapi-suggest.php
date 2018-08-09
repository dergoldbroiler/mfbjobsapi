<?php

class Suggest {
    
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
    * returns jobs as links
    @returns: object connectToDB   
    */
    public function get_jobs_from_db( $searchquery, $licensename="" ) {
        if ( $_POST['searchtype'] == "job") { 
            $query = "SELECT * FROM `jobs_vam` WHERE `bezeichnung` LIKE '%".$searchquery."%'";
        }
        if ( $_POST['searchtype'] == "license") { 
            $query = "SELECT * FROM `komp_vam` WHERE `bezeichnung` LIKE '%".$searchquery."%'";
        }
        if ( $_POST['searchtype'] == "skill") { 
            $query = "SELECT * FROM `komp_vam` WHERE `bezeichnung` LIKE '%".$searchquery."%'";
        }
        $mysqli = Suggest::connectToDB();
		$jobs = "";
		if ($result = $mysqli->query($query)) {
			
			/* create single article-object and push to array */
			 while($obj = $result->fetch_object()){ 
			   $job = "<a href='#' class='mfbjobsapi_joblink mfbjobsapi_link' data-result='".$_POST['resultdiv']."' data-jobtyp='".$obj->typ."' data-license-name='".$_POST['license_name']."' data-job_id='".$obj->_id."' data-job_bkz='".$obj->_bkz."'>".$obj->bezeichnung."</a><br>"; 
                 $jobs .= $job;
			 }
		}
        
        $javascript = "<script>jQuery('.searchformresult a.mfbjobsapi_joblink').click( function(ev){
                ev.preventDefault(); 
                //set chosen job as field value
                var jobbezeichnung = jQuery(this).html(); 
                var job_id = jQuery(this).attr('data-job_id');
                var job_bkz = jQuery(this).attr('data-job_bkz');
                jQuery('#searchinput_KEY').val(jobbezeichnung);    
                jQuery('#job_id').val(job_id);    
                jQuery('#job_bkz').val(job_bkz);
                jQuery('.searchformresult').html('');
          });
          jQuery('.searchformresult2 a.mfbjobsapi_joblink').click( function(ev){
                ev.preventDefault();
                //set chosen job as field value
                var jobbezeichnung = jQuery(this).html(); 
                var job_id = jQuery(this).attr('data-job_id');
                var job_bkz = jQuery(this).attr('data-job_bkz');
                jQuery('#jobeduname').val(job_id); 
                jQuery('.edutit').html(jobbezeichnung);
                jQuery('#job_id').val(job_id);    
                jQuery('#job_bkz').val(job_bkz);
                jQuery('.searchformresult2').html('');
          });jQuery( '.licenseresult a.mfbjobsapi_joblink').click( function(ev){
                ev.preventDefault();
               // alert(jQuery(this).attr('data-result'));
                
                //set chosen job as field value
                var jobbezeichnung = jQuery(this).html(); 
                var job_id = jQuery(this).attr('data-job_id');
                var lic_name = jQuery(this).attr('data-license-name');
                var job_bkz = jQuery(this).attr('data-job_bkz');
                var resultdiv = jQuery(this).attr('data-result');
                jQuery(resultdiv).val(job_id); 
                jQuery(lic_name).html(jobbezeichnung);
                jQuery('.licenseresult').html('');
          }); jQuery( '.jobskillresult a.mfbjobsapi_joblink').click( function(ev){
                ev.preventDefault();
               // alert(jQuery(this).attr('data-result'));
                
                //set chosen job as field value
                var jobbezeichnung = jQuery(this).html(); 
                var job_id = jQuery(this).attr('data-job_id');
                var lic_name = jQuery(this).attr('data-license-name');
                var job_bkz = jQuery(this).attr('data-job_bkz');
                var resultdiv = jQuery(this).attr('data-result');
                jQuery(resultdiv).val(job_id); 
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
    
    //suggestion for joblicenses
    if ( $_POST['searchtype'] == "license") { 
        if ( isset ($_POST['license_name'])) {
            $suggest->get_jobs_from_db($_POST['searchquery'],$_POST['license_name']);
        }else {
            $suggest->get_jobs_from_db($_POST['searchquery']);
        }
	   
    }

}

