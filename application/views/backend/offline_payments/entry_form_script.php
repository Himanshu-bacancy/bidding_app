<script>
	<?php if ( $this->config->item( 'client_side_validation' ) == true ): ?>

	function jqvalidate() {

		$('#offlinepayment-form').validate({
			rules:{
				title:{
					blankCheck : "",
					minlength: 3,
					remote: "<?php echo $module_site_url .'/ajx_exists/'.@$offline_payment->id; ?>"
				},
				
				icon:{
					required: true
				}
			},
			messages:{
				title:{
					blankCheck : "<?php echo get_msg( 'err_offline_name' ) ;?>",
					minlength: "<?php echo get_msg( 'err_offline_len' ) ;?>",
					remote: "<?php echo get_msg( 'err_offline_exist' ) ;?>."
				},
				
				icon:{
					required: "Please File Upload Icon."
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

		$('.delete-img').click(function(e){
			e.preventDefault();

			// get id and image
			var id = $(this).attr('id');

			// do action
			var action = '<?php echo $module_site_url .'/delete_cover_photo/'; ?>' + id + '/<?php echo @$offline_payment->id; ?>';
			console.log( action );
			$('.btn-delete-image').attr('href', action);
		});
	}
</script>

<?php 

	// replace cover icon modal
	$data = array(
		'title' => get_msg('upload_icon'),
		'img_type' => 'offline_icon',
		'img_parent_id' => @$offline_payment->id
	);
		$this->load->view( $template_path .'/components/photo_upload_modal', $data );
			// delete cover photo modal
	$this->load->view( $template_path .'/components/delete_cover_photo_modal' ); 

?>