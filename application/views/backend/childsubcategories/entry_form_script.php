<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
	<?php if ( $this->config->item( 'client_side_validation' ) == true ): ?>

	jQuery(document).ready(function(){
		$('#multiselect').select2();
	});
	function jqvalidate() {

		$('#subcategory-form').validate({
			rules:{
				name:{
					blankCheck : "",
					minlength: 3,
					remote: "<?php echo $module_site_url .'/ajx_exists/'.@$child_subcategory->id; ?>"
				},
				cat_id: {
		       		indexCheck : ""
		      	}
			},
			messages:{
				name:{
					blankCheck : "<?php echo get_msg( 'err_child_subcat_name' ) ;?>",
					minlength: "<?php echo get_msg( 'err_child_subcat_len' ) ;?>",
					remote: "<?php echo get_msg( 'err_child_subcat_exist' ) ;?>."
				},
				cat_id:{
			       indexCheck: "<?php echo $this->lang->line('f_item_cat_required'); ?>"
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
			   };
		})

	}

	<?php endif; ?>

		$('.delete-img').click(function(e){
			e.preventDefault();

			// get id and image
			var id = $(this).attr('id');

			// do action
			var action = '<?php echo $module_site_url .'/delete_cover_photo/'; ?>' + id + '/<?php echo @$child_subcategory->id; ?>';
			console.log( action );
			$('.btn-delete-image').attr('href', action);
			
		});

		jQuery('#cat_id').on('change', function() {

			var catId = $(this).val();
			$.ajax({
				url: '<?php echo $module_site_url . '/get_all_sub_categories/';?>' + catId,
				method: 'GET',
				dataType: 'JSON',
				success:function(data){
					jQuery('#sub_cat_id').html("");
					jQuery.each(data, function(i, obj){
						jQuery('#sub_cat_id').append('<option value="'+ obj.id +'">' + obj.name + '</option>');
					});
				}
			});
		});

</script>

<?php 
	// replace cover photo modal
	$data = array(
		'title' => get_msg('upload_photo'),
		'img_type' => 'childsubcategory_cover',
		'img_parent_id' => @$child_subcategory->id
	);

	$this->load->view( $template_path .'/components/photo_upload_modal', $data );
	// delete cover photo modal
	$this->load->view( $template_path .'/components/delete_cover_photo_modal' ); 

	// replace icon icon modal
	$data = array(
		'title' => get_msg('upload_icon'),
		'img_type' => 'childsubcategory_icon',
		'img_parent_id' => @$child_subcategory->id
	);
		$this->load->view( $template_path .'/components/icon_upload_modal', $data );
		// delete icon photo modal
		$this->load->view( $template_path .'/components/delete_icon_modal' ); 
?>