<script>

	<?php if ( $this->config->item( 'client_side_validation' ) == true ): ?>

	function jqvalidate() {

		$('#reason-form').validate({
			rules:{
				name:{
					blankCheck : "",
					minlength: 3,
					remote: "<?php echo $module_site_url .'/ajx_exists/'.@$reason->id; ?>"
				},
				type: {
		       		indexCheck : ""
		      	}
			},
			messages:{
				name:{
					blankCheck : "<?php echo get_msg( 'err_reason_name' ) ;?>",
					minlength: "<?php echo get_msg( 'err_reason_len' ) ;?>",
					remote: "<?php echo get_msg( 'err_reason_exist' ) ;?>."
				},
				type:{
			       indexCheck: "<?php echo get_msg( 'err_reason_select' ) ;?>"
			    }
			}
		});
        jQuery.validator.addMethod("indexCheck",function( value, element ) {
			   if(value == 0) {
			    	return false;
			   } else {
			    	return true;
			   };
		});
		// custom validation
		jQuery.validator.addMethod("blankCheck",function( value, element ) {
			
			   if(value == "") {
			    	return false;
			   } else {
			    	return true;
			   }
		});
	}

	<?php endif; ?>

</script>