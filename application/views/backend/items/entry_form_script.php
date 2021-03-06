<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
	<?php if ( $this->config->item( 'client_side_validation' ) == true ): ?>

	function jqvalidate() {

		$('#item-form').validate({
			rules:{
				title:{
					blankCheck : "",
					minlength: 3,
					remote: "<?php echo $module_site_url .'/ajx_exists/'.@$item->id; ?>"
				},
				cat_id: {
		       		indexCheck : ""
		      	},
		      	sub_cat_id: {
		       		indexCheck : ""
		      	},
		      	lat:{
                    blankCheck : "",
                    indexCheck : "",
                    validChecklat : ""
			    },
			    lng:{
			     	blankCheck : "",
			     	indexCheck : "",
			     	validChecklng : ""
			    },
				images1:{
					required: true
				}
			},
			messages:{
				title:{
					blankCheck : "<?php echo get_msg( 'err_item_name' ) ;?>",
					minlength: "<?php echo get_msg( 'err_item_len' ) ;?>",
					remote: "<?php echo get_msg( 'err_item_exist' ) ;?>."
				},
				cat_id:{
			       indexCheck: "<?php echo get_msg( 'err_cat_select' ) ;?>"
			    },
			    sub_cat_id:{
			       indexCheck: "<?php echo get_msg( 'err_subcat_select' ) ;?>"
			    },
				lat:{
			     	blankCheck : "<?php echo get_msg( 'err_lat' ) ;?>",
			     	indexCheck : "<?php echo get_msg( 'err_lat_lng' ) ;?>",
			     	validChecklat : "<?php echo get_msg( 'lat_invlaid' ) ;?>"
			    },
			    lng:{
			     	blankCheck : "<?php echo get_msg( 'err_lng' ) ;?>",
			     	indexCheck : "<?php echo get_msg( 'err_lat_lng' ) ;?>",
			     	validChecklng : "<?php echo get_msg( 'lng_invlaid' ) ;?>"
			    },
				images1:{
					required: "<?php echo get_msg( 'err_upload_photo' ) ;?>"
				},
			},

			submitHandler: function(form) {
		        if ($("#item-form").valid()) {
		            form.submit();
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

		jQuery.validator.addMethod("blankCheck",function( value, element ) {
			
			   if(value == "") {
			    	return false;
			   } else {
			   	 	return true;
			   }
		});

		jQuery.validator.addMethod("validChecklat",function( value, element ) {
			    if (value < -90 || value > 90) {
			    	return false;
			    } else {
			   	 	return true;
			    }
		});

		jQuery.validator.addMethod("validChecklng",function( value, element ) {
			    if (value < -180 || value > 180) {
			    	return false;
			   } else {
			   	 	return true;
			   }
		});
			

	}

	<?php endif; ?>

	jQuery(document).ready(function(){
		$('#sizegroupoption_ids').select2();
		$('#color_ids').select2();
	});

	function runAfterJQ() {

		$('#cat_id').on('change', function() {
			var value = $('option:selected', this).text().replace(/Value\s/, '');
			var catId = $(this).val();
			$.ajax({
				url: '<?php echo $module_site_url . '/get_all_sub_categories/';?>' + catId,
				method: 'GET',
				dataType: 'JSON',
				success:function(data){
					$('#sub_cat_id').html("");
					$.each(data, function(i, obj){
						$('#sub_cat_id').append('<option value="'+ obj.id +'">' + obj.name+ '</option>');
					});
					$('#name').val($('#name').val() + " ").blur();
					$('#sub_cat_id').trigger('change');
				}
			});
		});

		$('#sub_cat_id').on('change', function() {
			var value = $('option:selected', this).text().replace(/Value\s/, '');
			var catId = $('#cat_id').val();
			var subCatId = $(this).val();
			$.ajax({
				url: '<?php echo $module_site_url . '/get_all_childsub_categories/';?>' + subCatId + '/' + catId,
				method: 'GET',
				dataType: 'JSON',
				success:function(data){
					$('#childsubcat_id').html("");
					$.each(data, function(i, obj){
						$('#childsubcat_id').append('<option value="'+ obj.id +'">' + obj.name+ '</option>');
					});
					$('#name').val($('#name').val() + " ").blur();
					$('#childsubcat_id').trigger('change');
				}
			});
		});

		$('#sizegroup_id').on('change', function() {
			var value = $('option:selected', this).text().replace(/Value\s/, '');
			var sizegroupId = $(this).val();
			$.ajax({
				url: '<?php echo $module_site_url . '/get_all_sizegroup_option/';?>' + sizegroupId,
				method: 'GET',
				dataType: 'JSON',
				success:function(data){
					$('#sizegroupoption_ids').html("");
					$.each(data, function(i, obj){
						$('#sizegroupoption_ids').append('<option value="'+ obj.id +'">' + obj.title+ '</option>');
					});
					$('#name').val($('#name').val() + " ").blur();
					$('#sizegroupoption_ids').trigger('change');
				}
			});
		});


		$('#package_size_id').on('change', function() {
			var value = $('option:selected', this).text().replace(/Value\s/, '');
			var packageSizeId = $(this).val();
			$.ajax({
				url: '<?php echo $module_site_url . '/get_all_shipping_carrier/';?>' + packageSizeId,
				method: 'GET',
				dataType: 'JSON',
				success:function(data){
					$('#shippingcarrier_id').html("");
					$.each(data, function(i, obj){
						$('#shippingcarrier_id').append('<option value="'+ obj.id +'">' + obj.name+ '</option>');
					});
					$('#name').val($('#name').val() + " ").blur();
					$('#shippingcarrier_id').trigger('change');
				}
			});
		});
        
		$(function() {
			var selectedClass = "";
			$(".filter").click(function(){
			selectedClass = $(this).attr("data-rel");
			$("#gallery").fadeTo(100, 0.1);
			$("#gallery div").not("."+selectedClass).fadeOut().removeClass('animation');
			setTimeout(function() {
			$("."+selectedClass).fadeIn().addClass('animation');
			$("#gallery").fadeTo(300, 1);
			}, 300);
			});
		});

		$('.delete-img').click(function(e){
			e.preventDefault();

			// get id and image
			var id = $(this).attr('id');

			// do action
			var action = '<?php echo $module_site_url .'/delete_cover_photo/'; ?>' + id + '/<?php echo @$item->id; ?>';
			console.log( action );
			$('.btn-delete-image').attr('href', action);
		});
	}

</script>
<?php 
	// replace cover photo modal
	$data = array(
		'title' => get_msg('upload_photo'),
		'img_type' => 'item',
		'img_parent_id' => @$item->id
	);

	$this->load->view( $template_path .'/components/photo_upload_modal', $data );

	// delete cover photo modal
	$this->load->view( $template_path .'/components/delete_cover_photo_modal' ); 
?>