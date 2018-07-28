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
				
                 $job = "<a href='#' data-jobtyp='".$obj->_typ."'>".$obj->bezeichnung."</a><br>"; 
                 $jobs .= $job;
			 }
		}
        echo $jobs;
    }
    
    
}





$suggest = new Suggest();

if ( $_POST['searchquery']) {

	$suggest->get_jobs_from_db($_POST['searchquery']);

}

