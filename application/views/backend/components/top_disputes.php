<style>
    #dispute_view_all_link {float: right}
</style>
<div class="card-header border-transparent">
    <h3 class="card-title"><?php echo $panel_title ?>
        <a id="dispute_view_all_link" href="<?php echo site_url( 'admin' ) .'/disputes'; ?>">View All</a>
    </h3>
</div>
<!-- /.card-header -->
<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table m-0 table-hover">
            <thead>
                <tr>
                    <td><b>No</b></td>
                    <td><b>User Name</b></td>
                    <td><b>User Email</b></td>
                    <td><b>User Phone</b></td>
                    <td><b>Dispute Status</b></td>
                    <td><b>Is Seller Dispute ?</b></td>
                    <td><b>Message</b></td>
                    <td><b>View</b></td>
                </tr>
            </thead>
            <tbody>
                <?php 
                    foreach ($data as $key => $value) {
                        $record_no = $this->db->select('id')->from('bs_order')->where('order_id', $value->order_id)->get()->row()->id;
                        
                        echo '<tr>
                            <td>'.++$key.'</td>
                            <td>'.$value->name.'</td>
                            <td>'.$value->email.'</td>
                            <td>'.$value->phone.'</td>
                            <td>'.$value->status.'</td>
                            <td>'.(($value->is_seller_generate) ? 'yes1' :'no').'</td>
                            <td>'.$value->message.'</td>
                            <td><a href="'.site_url( 'admin' ) .'/orders/returndetail/'. $record_no.'"><i class="fa fa-eye" style="font-size: 18px;"></i></a></td>
                        </tr>';
                    }
                ?>
            </tbody>
        </table>
    </div>
    <!-- /.table-responsive -->
</div>
