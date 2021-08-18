<script>

	<?php if ( $this->config->item( 'client_side_validation' ) == true ): ?>

	function jqvalidate() {

		$('#shippingcarrier-form').validate({
			rules:{
				name:{
					blankCheck : "",
					minlength: 3
				},
				min_days:{
					required: true,
					digits : true
				},
				price:{
					required: true,
					number : true
				},
				max_days:{
					required: true,
					digits : true
				},
				icon:{
					required: true
				},
				packagesize_id: {
		       		indexCheck : ""
		      	},
				shippo_object_id:{
					required: true
				}
				shippo_servicelevel_token:{
					required: true
				}
			},
			messages:{
				name:{
					blankCheck : "<?php echo get_msg( 'err_shippingcarrier_name' ) ;?>",
					minlength: "<?php echo get_msg( 'err_shippingcarrier_len' ) ;?>"
				},
				min_days:{
					required: "<?php echo get_msg( 'err_shippingcarrier_min' ) ;?>",
					digits : "<?php echo get_msg( 'err_digits' ) ;?>"
				},
				price:{
					required: "<?php echo get_msg( 'err_shippingcarrier_price' ) ;?>",
					number : "<?php echo get_msg( 'err_numbers' ) ;?>"
				},
				max_days:{
					required: "<?php echo get_msg( 'err_shippingcarrier_max' ) ;?>",
					digits : "<?php echo get_msg( 'err_digits' ) ;?>"
				},
				icon:{
					required: "<?php echo get_msg( 'err_upload_icon' ) ;?>"
				},
				packagesize_id: {
					indexCheck: "<?php echo get_msg( 'err_packagesize_select' ) ;?>"
		      	},
				shippo_object_id:{
					required: "<?php echo get_msg( 'err_shippo_object_id' ) ;?>"
				},
				shippo_servicelevel_token:{
					required: "<?php echo get_msg( 'err_shippo_servicelevel_token' ) ;?>"
				},
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

		jQuery.validator.addMethod("indexCheck",function( value, element ) {
			
			if(value == 0) {
				 return false;
			} else {
				 return true;
			};
		});
	}

	<?php endif; ?>

	$('.delete-img').click(function(e){
			e.preventDefault();

			// get id and image
			var id = $(this).attr('id');

			// do action
			var action = '<?php echo $module_site_url .'/delete_cover_photo/'; ?>' + id + '/<?php echo @$shippingcarrier->id; ?>';
			console.log( action );
			$('.btn-delete-image').attr('href', action);
			
		});

</script>

<?php 
	// replace icon icon modal
	$data = array(
		'title' => get_msg('shippingcarrier_icon'),
		'img_type' => 'shippingcarrier_icon',
		'img_parent_id' => @$shippingcarrier->id,
		'label' => get_msg('shippingcarrier_icon'),	);
		$this->load->view( $template_path .'/components/icon_uploadcstm_modal', $data );
		// delete icon photo modal
		$this->load->view( $template_path .'/components/delete_icon_modal' ); 
?>