<script>
function runAfterJQ() {

	$(document).ready(function(){
		
		// Publish Trigger
		$(document).delegate('.accept','click',function(){
			
			// get button and id
			var btn = $(this);
			var id = $(this).attr('id');
			var order_id = $(this).attr('order_id');

			// Ajax Call to publish
			$.ajax({
				url: "<?php echo $module_site_url .'/ajx_publish/'; ?>" + id+'/'+order_id,
				method: 'GET',
				success: function( msg ) {
					if ( msg == 'true' ) {
						location.reload();
					}
					else {
						alert( "<?php echo get_msg( 'err_sys' ); ?>" );
					}
				}
			});
		});
		
		// Unpublish Trigger
		$(document).delegate('.reject','click',function(){

			// get button and id
			var btn = $(this);
			var id = $(this).attr('id');
            var order_id = $(this).attr('order_id');

			// Ajax call to unpublish
			$.ajax({
				url: "<?php echo $module_site_url .'/ajx_unpublish/'; ?>" + id+'/'+order_id,
				method: 'GET',
				success: function( msg ){
					if ( msg == 'true' ) {
						location.reload();
					}
					else {
						alert( "<?php echo get_msg( 'err_sys' ); ?>" );
					}
				}
			});
		});
		
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
		'title' => get_msg( 'delete_topic_label' ),
		'message' => get_msg( 'topic_delete_confirm_message' ) .'<br>',
		'yes_all_btn' => get_msg( 'topic_yes_all_label' ),
		'no_only_btn' => get_msg( 'topic_no_only_label' )
	);
	
	$this->load->view( $template_path .'/components/delete_confirm_modal', $data );
?>