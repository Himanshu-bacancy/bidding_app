<script>
function runAfterJQ() {

	$(document).ready(function(){
		$('#reported_item_table').DataTable({
            "paging": false,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            order: [[ 1, 'desc' ]],
            "columnDefs": [
                { orderable: true,  targets: [1,3,4,5] },
                { orderable: false, targets: '_all' }
            ]
        });
		// Publish Trigger
		$(document).delegate('.publish','click',function(){
			
			// get button and id
			var btn = $(this);
			var id = $(this).attr('id');

			// Ajax Call to publish
			$.ajax({
				url: "<?php echo $module_site_url .'/ajx_publish/'; ?>" + id,
				method: 'GET',
				success: function( msg ) {
					if ( msg == 'true' ) {
						btn.addClass('unpublish').addClass('btn-success')
							.removeClass('publish').removeClass('btn-danger')
							.html('<?php echo get_msg( 'btn_yes' ); ?>');
					}
					else {
						alert( "<?php echo get_msg( 'err_sys' ); ?>" );
					}
				}
			});
		});
		
		// Unpublish Trigger
		$(document).delegate('.unpublish','click',function(){

			// get button and id
			var btn = $(this);
			var id = $(this).attr('id');

			// Ajax call to unpublish
			$.ajax({
				url: "<?php echo $module_site_url .'/ajx_unpublish/'; ?>" + id,
				method: 'GET',
				success: function( msg ){
					if ( msg == 'true' ) {
						btn.addClass('publish').addClass('btn-danger')
							.removeClass('unpublish').removeClass('btn-success')
							.html('<?php echo get_msg( 'btn_no' ); ?>');
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