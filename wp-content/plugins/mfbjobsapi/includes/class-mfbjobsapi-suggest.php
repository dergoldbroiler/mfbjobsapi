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
    public function get_jobs_from_db( $searchquery ) {
        $query = "SELECT * FROM `jobs_vam` WHERE `bezeichnung` LIKE '%".$searchquery."%'";
        $mysqli = Suggest::connectToDB();
		$jobs = "";
		if ($result = $mysqli->query($query)) {
			
			/* create single article-object and push to array */
			 while($obj = $result->fetch_object()){ 
				
                 $job = "<a href='#' class='mfbjobsapi_joblink mfbjobsapi_link' data-jobtyp='".$obj->typ."' data-job_id='".$obj->_id."' data-job_bkz='".$obj->_bkz."'>".$obj->bezeichnung."</a><br>"; 
                 $jobs .= $job;
			 }
		}
        
        $javascript = "<script>jQuery('a.mfbjobsapi_joblink').click( function(ev){
                ev.preventDefault();
                //set chosen job as field value
                var jobbezeichnung = jQuery(this).html(); 
                var job_id = jQuery(this).attr('data-job_id');
                var job_bkz = jQuery(this).attr('data-job_bkz');
                jQuery('#searchinput_KEY').val(jobbezeichnung);    
                jQuery('#job_id').val(job_id);    
                jQuery('#job_bkz').val(job_bkz);
                jQuery('.searchformresult').html('');
          });</script>";
        echo $jobs.$javascript;
    }
    
    
}





$suggest = new Suggest();

if ( $_POST['searchquery']) {

	$suggest->get_jobs_from_db($_POST['searchquery']);

}

