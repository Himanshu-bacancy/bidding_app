<script>

	<?php if ( $this->config->item( 'client_side_validation' ) == true ): ?>

	function jqvalidate() {

		$('#reportitemreason-form').validate({
			rules:{
				name:{
					blankCheck : "",
					minlength: 3,
					remote: "<?php echo $module_site_url .'/ajx_exists/'.@$reportitemreason->id; ?>"
				}
			},
			messages:{
				name:{
					blankCheck : "<?php echo get_msg( 'err_reportitemreason_name' ) ;?>",
					minlength: "<?php echo get_msg( 'err_reportitemreason_len' ) ;?>",
					remote: "<?php echo get_msg( 'err_reportitemreason_exist' ) ;?>."
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

</script>