<script>
function runAfterJQ() {

	$(document).ready(function(){
        
        var register_user_table = $('#register_user_table').DataTable( {
            "paging": false,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            order: [[ 2, 'asc' ]],
            columnDefs: [ {
                orderable: false,
                'searchable': false,
                className: 'dt-body-center',
                targets:   0,
                'render': function (data, type, full, meta){
                    return '<input type="checkbox" name="id[]" value="'+data+'">';
                }
            },
            { className: 'dt-body-center',  targets: [8,10] },
            { orderable: true,  targets: [2] },
            { orderable: false, targets: '_all' } ]
        } );
        // Handle click on "Select all" control
        $('#example-select-all').on('click', function(){
           // Get all rows with search applied
           var rows = register_user_table.rows({ 'search': 'applied' }).nodes();
           // Check/uncheck checkboxes for all rows in the table
           $('input[type="checkbox"]', rows).prop('checked', this.checked);
        });
        // Handle click on checkbox to set state of "Select all" control
        $('#register_user_table tbody').on('change', 'input[type="checkbox"]', function(){
           // If checkbox is not checked
           if(!this.checked){
              var el = $('#example-select-all').get(0);
              // If "Select all" control is checked and has 'indeterminate' property
              if(el && el.checked && ('indeterminate' in el)){
                 // Set visual state of "Select all" control
                 // as 'indeterminate'
                 el.indeterminate = true;
              }
           }
        });
        $( "#sendnotiform" ).submit(function( event ) {
            var val = [];
            $(':checkbox:not(#example-select-all):checked').each(function(i){
                val[i] = $(this).val();
            });
            if(val.length > 0) {
                $('#userids').val(JSON.stringify(val));
                return;
            }
            event.preventDefault(); 
        });
		$(document).delegate('.ban','click',function(){
			var btn = $(this);
			var id = $(this).attr('userid');

			$.ajax({
				url: "<?php echo $module_site_url .'/ban/';?>"+id,
				method:'GET',
				success:function(msg){
					if(msg == 'true')
						btn.addClass('unban btn-danger')
							.removeClass('btn-primary-green ban')
							.html('<?php echo get_msg( 'user_unban' ); ?>');
					else
						console.log( 'System error occured. Please contact your system administrator.' );
				}
			});
		});
		
		$(document).delegate('.unban','click',function(){
			var btn = $(this);
			var id = $(this).attr('userid');

			$.ajax({
				url: "<?php echo $module_site_url .'/unban/';?>"+id,
				method:'GET',
				success:function(msg){
					if(msg == 'true')
						btn.addClass('ban btn-primary-green')
							.removeClass('btn-danger unban')
							.html('<?php echo get_msg( 'user_ban' ); ?>');
					else
						console.log( 'System error occured. Please contact your system administrator.' );
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
		'title' => get_msg( 'delete_user_label' ),
		'message' => get_msg( 'user_delete_confirm_message' ) .'<br>',
		'yes_all_btn' => get_msg( 'user_yes_all_label' ),
		'no_only_btn' => get_msg( 'user_no_only_label' )
	);
	
	$this->load->view( $template_path .'/components/delete_confirm_modal', $data );
?>