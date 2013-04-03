(function($){
	
		$(function(){
			
			
			function formShizzle () {
				// front-end validation
				// - checks fields as they're tabed / unfocused 	
				$('#contact-form [data-required="required"]').blur(function(){
					if ( $(this).val() === '' ) {
						var field = $(this).attr('data-field');
						$(this).addClass('error');
						$('#msg').html('<span style="color:#cc0000;">Please enter your '+field+'</span>');
					} else {
						$(this).removeClass('error');
						$('#msg').text();
					}
				});
							
				// ajax form submit 
			    $('#contact-form').submit(function(){  
			    	// check for required fields
			    	if ( $('#contact-form [data-required="required"]').val().length ) {
			    			// ok to send
			        	$.post("inc/contact_form.php",  
			         	   	$('#contact-form').serialize(),  
			       	     		function(json) {  
			       	         	$('#msg').html(json.msg);  
			           	 	},
			            		"json"  
			       		 ); 
			    	} else {
			    		// required feilds not completed
			    		
			    		// show general error
			    		$('#msg').html('<span style="color:#cc0000;">Please complete all required fields and check your email address is valid.</span>');
			    		// add error class to fields with errors
			    		$('#contact-form [data-required="required"]').each(function(){
			    			if ( $(this).val() === '' ) { 
								$(this).addClass('error');
			    			}
			    		});
			    	}
					        	return false;       	
			    });	//submit
			}	
		
			formShizzle ();
			
			
		});
		// doc ready
})(jQuery);
// jQ wrapper