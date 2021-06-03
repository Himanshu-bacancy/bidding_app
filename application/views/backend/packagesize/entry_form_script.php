<script>

	<?php if ( $this->config->item( 'client_side_validation' ) == true ): ?>

	function jqvalidate() {

		$('#packagesize-form').validate({
			rules:{
				name:{
					blankCheck : "",
					remote: "<?php echo $module_site_url .'/ajx_exists/'.@$packagesize->id; ?>"
				},
				length:{
					required: true,
					number : true
				},
				width:{
					required: true,
					number : true
				},
				height:{
					required: true,
					number : true
				},
				weight:{
					required: true,
					number : true
				},
				icon:{
					required: true
				}
			},
			messages:{
				name:{
					blankCheck : "<?php echo get_msg( 'err_packagesize_name' ) ;?>",
					remote: "<?php echo get_msg( 'err_packagesize_exist' ) ;?>."
				},
				length:{
					required: "<?php echo get_msg( 'err_packagesize_length' ) ;?>",
					number : "<?php echo get_msg( 'err_numbers' ) ;?>"
				},
				width:{
					required: "<?php echo get_msg( 'err_packagesize_width' ) ;?>",
					number : "<?php echo get_msg( 'err_numbers' ) ;?>"
				},
				height:{
					required: "<?php echo get_msg( 'err_packagesize_height' ) ;?>",
					number : "<?php echo get_msg( 'err_numbers' ) ;?>"
				},
				weight:{
					required: "<?php echo get_msg( 'err_packagesize_weight' ) ;?>",
					number : "<?php echo get_msg( 'err_numbers' ) ;?>"
				},
				icon:{
					required: "<?php echo get_msg( 'err_upload_icon' ) ;?>"
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

	$('.delete-img').click(function(e){
			e.preventDefault();

			// get id and image
			var id = $(this).attr('id');

			// do action
			var action = '<?php echo $module_site_url .'/delete_cover_photo/'; ?>' + id + '/<?php echo @$packagesize->id; ?>';
			console.log( action );
			$('.btn-delete-image').attr('href', action);
			
		});

</script>

<?php 
	// replace icon icon modal
	$data = array(
		'title' => get_msg('packagesize_icon'),
		'img_type' => 'packagesize_icon',
		'img_parent_id' => @$packagesize->id,
		'label' => get_msg('packagesize_icon'),	);
		$this->load->view( $template_path .'/components/icon_uploadcstm_modal', $data );
		// delete icon photo modal
		$this->load->view( $template_path .'/components/delete_icon_modal' ); 
?>