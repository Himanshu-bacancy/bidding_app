<script>

	<?php if ( $this->config->item( 'client_side_validation' ) == true ): ?>

	function jqvalidate() {

		$('#promotingitemplan-form').validate({
			rules:{
				name:{
					blankCheck : "",
					minlength: 3,
					remote: "<?php echo $module_site_url .'/ajx_exists/'.@$promotingitemplan->id; ?>"
				},
				code:{
					required: true,
					remote: "<?php echo $module_site_url .'/ajxcode_exists/'.@$promotingitemplan->id; ?>"
				},
				price:{
					required: true
				},
				days:{
					required: true
				}
			},
			messages:{
				name:{
					blankCheck : "<?php echo get_msg( 'err_promotingitemplan_name' ) ;?>",
					minlength: "<?php echo get_msg( 'err_promotingitemplan_len' ) ;?>",
					remote: "<?php echo get_msg( 'err_promotingitemplan_exist' ) ;?>."
				},
				code:{
					required: "<?php echo get_msg( 'err_plan_code' ) ;?>",
					remote: "<?php echo get_msg( 'err_plancode_exist' ) ;?>."
				},
				price:{
					required: "<?php echo get_msg( 'err_plan_price' ) ;?>"
				},
				days:{
					required: "<?php echo get_msg( 'err_plan_days' ) ;?>"
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