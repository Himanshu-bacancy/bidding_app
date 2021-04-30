<script>
	function jqvalidate() {

		$(document).ready(function(){
			$('#about-form').validate({
				rules:{
					title:{
						required: true,
						minlength: 4
					}
				},
				messages:{
					title:{
						required: "Please fill title.",
						minlength: "The length of title must be greater than 4"
					}
				}
			});
		});
	}

</script>
<?php
// replace cover photo modal
	$data = array(
		'title' => get_msg('upload_photo'),
		'img_type' => 'about',
		'img_parent_id' => @$about->about_id
	);

	$this->load->view( $template_path .'/components/photo_upload_modal', $data );

	// delete cover photo modal
	$this->load->view( $template_path .'/components/delete_cover_photo_modal' ); 
?>