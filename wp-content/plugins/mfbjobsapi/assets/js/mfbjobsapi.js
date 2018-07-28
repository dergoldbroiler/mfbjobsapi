jQuery.noConflict();
(function( $ ) {
  $(function() {
      
    $('#searchinput_KEY').keyup( function () {

				var current_input_length = $(this).val().length;
				var current_input = $(this).val();
                var suggest_url = $(this).attr('data-suggest'); 
				if (current_input_length > 1) {

					 	

					$.post( suggest_url, { searchquery:current_input })

					  .done(function( data ) {

						console.log( "Data Loaded: " + data );

						jQuery('.searchformresult').html(data);

					  });

					

				}

			})
  });
})(jQuery);
 