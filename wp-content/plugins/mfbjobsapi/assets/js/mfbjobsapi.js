jQuery.noConflict();
(function( $ ) {
  $(function() {
    if ( $('#jobleadership').val() != 1 ) {
           $('.joblead').fadeIn();
       } else {
            $('.joblead').fadeOut();
       } 
      
    $('.addrow').click ( function (ev) {
        ev.preventDefault();
        $( $(this).attr('data-load') ).fadeIn();
       
    })
      
      
    $('#jobtermbool').change ( function () {
       if ( $(this).val() != 2 ) {
           $('.jobterm').fadeIn();
       } else {
            $('.jobterm').fadeOut();
       } 
    })
      
    $('#jobleadership').change ( function () {
       if ( $(this).val() != 1 ) {
           $('.joblead').fadeIn();
       } else {
            $('.joblead').fadeOut();
       } 
    })
    $('#jobpayscale').change ( function () {
        
       if ( $(this).val() == 1 ) {
           $('.jobagreement').fadeIn();
       } else {
          
            $('.jobagreement').fadeOut();
       } 
    })
      
    $('#searchinput_KEY').keyup( function () {

				var current_input_length = $(this).val().length;
				var current_input = $(this).val();
                var suggest_url = $(this).attr('data-suggest'); 
				if (current_input_length > 3) {

					 	

					$.post( suggest_url, { searchquery:current_input,searchtype: "job" })

					  .done(function( data ) {

						console.log( "Data Loaded: " + data );

						jQuery('.searchformresult').html(data);

					  });

					

				} else {
                    jQuery('.searchformresult').html('');
                }

			});
      
     
  });
    
  $('#jobeduname').keyup( function () {
           				var current_input_length = $(this).val().length;
				var current_input = $(this).val();
                var suggest_url = $(this).attr('data-suggest'); 
				if (current_input_length > 3) {

					 	

					$.post( suggest_url, { searchquery:current_input,searchtype: "job" })

					  .done(function( data ) {

						console.log( "Data Loaded: " + data );

						jQuery('.searchformresult2').html(data);

					  });

					

				} else {
                    jQuery('.searchformresult2').html('');
                }

			});
      
    
    $('.joblicense').keyup( function () {
           	    
        var license_result = $(this).attr('data-license-result');
        var license_field_id = $(this).attr('id');
        var license_name = $(this).attr('data-license-name');
        var current_input_length = $("#"+license_field_id).val().length;
		var current_input = $("#"+license_field_id).val();
        var suggest_url = $("#"+license_field_id).attr('data-suggest'); 
            
        if (current_input_length > 3) {

					$.post( suggest_url, { searchquery:current_input,searchtype: "license",resultdiv:"#"+license_field_id,license_name:"."+license_name })

					  .done(function( data ) {

						console.log( "Data Loaded: " + data );
						jQuery('.'+license_result).html(data);

					  });

					

				} else {
                    jQuery('.'+license_result).html('');
                }

			});
    
    $('.jobskills').keyup( function () {
           	    
        var skill_result = $(this).attr('data-skill-result');
        var skill_field_id = $(this).attr('id');
        //var skill_name = $(this).attr('data-skill-name');
        var current_input_length = $("#"+skill_field_id).val().length;
		var current_input = $("#"+skill_field_id).val();
        var suggest_url = $("#"+skill_field_id).attr('data-suggest'); 
            
        if (current_input_length > 3) {

					$.post( suggest_url, { searchquery:current_input,searchtype: "skill",resultdiv:"#"+skill_field_id })

					  .done(function( data ) {

						console.log( "Data Loaded: " + data );
						jQuery('.'+skill_result).html(data);

					  });

					

				} else {
                    jQuery('.'+skill_result).html('');
                }

			});

})(jQuery);
 