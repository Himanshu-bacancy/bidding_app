<script>

	<?php if ( $this->config->item( 'client_side_validation' ) == true ): ?>

	function jqvalidate() {

		$('#module-form').validate({
			rules:{
				module_name:{
					blankCheck : "",
					minlength: 3,
					remote: "<?php echo $module_site_url .'/ajx_exists/'.@$mod->module_id; ?>"
					
				},
				group_id:{
					indexCheck : ""
				},
				module_lang_key:{
					required: true
				},
				ordering:{
					required: true
				},
				
			},
			messages:{
				module_name:{
					blankCheck : "<?php echo get_msg( 'err_module_name' ) ;?>",
					minlength: "<?php echo get_msg( 'err_module_len' ) ;?>",
					remote: "<?php echo get_msg( 'err_module_exist' ) ;?>."
				},
				group_id:{
					indexCheck: "<?php echo get_msg( 'err_module_groupselect' ) ;?>"
				},
				module_lang_key:{
					required: "<?php echo get_msg( 'err_module_lenkey' ) ;?>",
				},
				ordering:{
					required: "<?php echo get_msg( 'err_ordering' ) ;?>",
				},
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