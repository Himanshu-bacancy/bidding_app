<script>
function runAfterJQ() {

	$(document).ready(function(){
        $('#orders_table').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": false,
            "info": true,
            "autoWidth": true,
            order: [[ 0, 'desc' ]],
            "columnDefs": [
                { orderable: true,  targets: [0,4,5,6,7,8,11] },
                { orderable: false, targets: '_all' }
            ],
            lengthMenu: [
                [20, 50, 100, -1],
                [20, 50, 100, 'All'],
            ],
            pagingType: 'numbers',
            dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-7'p><'col-sm-5'>>",
        });
    });
}
</script>
