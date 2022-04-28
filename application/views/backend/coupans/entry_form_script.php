<script>

	<?php if ( $this->config->item( 'client_side_validation' ) == true ): ?>

	function jqvalidate() {

		$('#coupan-form').validate({
			rules:{
				coupan_value:{
					blankCheck : "",
				},
                min_purchase_amount:{
					blankCheck : "",
				},
                end_at:{
					blankCheck : "",
				}
			},
			messages:{
				coupan_value:{
					blankCheck : "<?php echo get_msg( 'err_coupan_value' ) ;?>",
				},
                min_purchase_amount:{
					blankCheck : "<?php echo get_msg( 'err_min_purchase_amount' ) ;?>",
				},
                end_at:{
					blankCheck : "<?php echo get_msg( 'err_end_at' ) ;?>",
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