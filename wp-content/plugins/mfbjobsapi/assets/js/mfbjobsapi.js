jQuery.noConflict();
(function( $ ) {
  $(function() {
    
      
    $('.addrow').click ( function (ev) {
        ev.preventDefault();
        $( $(this).attr('data-load') ).fadeIn();
       
    })
      
      
    $('#jobtermbool').change ( function () {
       if ( $(this).val() != 2 ) {
           $('.tohide_jobtermbool').fadeIn();
       } else {
            $('.tohide_jobtermbool').fadeOut();
       } 
    })
      
    
    $('#jobpayscale').change ( function () {
       if ( $(this).val() != 0 ) {
           $('.tohide_jobpayscale').fadeIn();
       } else {
            $('.tohide_jobpayscale').fadeOut();
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
        var current_input_length = $("#"+license_field_id).val().length;
		var current_input = $("#"+license_field_id).val();
        var suggest_url = $("#"+license_field_id).attr('data-suggest'); 
            
        if (current_input_length > 3) {

					$.post( suggest_url, { searchquery:current_input,searchtype: "license",resultdiv:"#"+license_field_id })

					  .done(function( data ) {

						console.log( "Data Loaded: " + data );
						jQuery('.'+license_result).html(data);

					  });

					

				} else {
                    jQuery('.'+license_result).html('');
                }

			});

})(jQuery);
 