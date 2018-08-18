jQuery('.searchformresult a.mfbjobsapi_joblink').click( function(ev){
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
                jQuery('.'+resdiv).html('');
                
              
                    jQuery('.zustand'+".$_POST['numberof'].").html( jQuery(this).attr('data-zustand'));
                    jQuery('.quali'+".$_POST['numberof'].").html(jQuery(this).attr('data-quali'));
                    jQuery('.hs'+".$_POST['numberof'].").html(jQuery(this).attr('data-hs'));
                    jQuery('.ebene'+".$_POST['numberof'].").html(jQuery(this).attr('data-ebene'));
                    console.log( jQuery('.ebene'+".$_POST['numberof'].").html(jQuery(this).attr('data-ebene')))
                
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
          });