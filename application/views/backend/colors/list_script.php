<script>
function runAfterJQ() {

	$(document).ready(function(){
		
		// Delete Trigger
		$('.btn-delete').click(function(){

			// get id and links
			var id = $(this).attr('id');
			var btnYes = $('.btn-yes').attr('href');
			var btnNo = $('.btn-no').attr('href');

			// modify link with id
			$('.btn-yes').attr( 'href', btnYes + id );
			$('.btn-no').attr( 'href', btnNo + id );
		});

	});
}
</script>

<?php
	// Delete Confirm Message Modal
	$data = array(
		'title' => get_msg( 'delete_color_label' ),
		'message' => get_msg( 'color_delete_confirm_message' ) .'<br>',
		'yes_all_btn' => get_msg( 'cat_yes_all_label' ),
		'no_only_btn' => get_msg( 'cat_no_only_label' )
	);
	
	$this->load->view( $template_path .'/components/delete_confirm_modal', $data );
?>