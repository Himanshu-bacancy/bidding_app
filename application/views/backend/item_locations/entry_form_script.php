<script>

	<?php if ( $this->config->item( 'client_side_validation' ) == true ): ?>

	function jqvalidate() {

		$('#location-form').validate({
			rules:{
				name:{
					blankCheck : "",
					minlength: 3,
					remote: "<?php echo $module_site_url .'/ajx_exists/'.@$location->id; ?>"
				},
		      	lat:{
                    blankCheck : ""
			    },
			    lng:{
			     blankCheck : ""
			    }
			},
			messages:{
				name:{
					blankCheck : "<?php echo get_msg( 'err_location_name' ) ;?>",
					minlength: "<?php echo get_msg( 'err_location_len' ) ;?>",
					remote: "<?php echo get_msg( 'err_location_exist' ) ;?>."
				},
				lat:{
			     blankCheck : "<?php echo get_msg( 'err_lat' ) ;?>"
			    },
			    lng:{
			     blankCheck : "<?php echo get_msg( 'err_lng' ) ;?>"
			    }
			}
		});
		// custom validation
		jQuery.validator.addMethod("blankCheck",function( value, element ) {
			
			   if(value == "") {
			    	return false;
			   } else {
			    	return true;
			   }
		})
	}

	<?php endif; ?>

	function runAfterJQ() {

		  $('input[name="ordering"]').keyup(function(e)
                                {
		  if (/[^0-9\.]/g.test(this.value))
		  {
		    // Filter non-digits from input value.
		    //this.value = this.value.replace(/\D/g, '');
		    this.value = this.value.replace(/[^0-9\.]/g,'');
		  }
		});
	}

</script>