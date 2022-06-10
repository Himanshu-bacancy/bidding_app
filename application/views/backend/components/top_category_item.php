<div class="card-header border-transparent">
    <h3 class="card-title"><?php echo $panel_title ?></h3>
</div>
<!-- /.card-header -->
<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table m-0 table-hover">
            <thead>
                <tr>
                    <td><b>State</b></td>
                    <td><b>Total</b></td>
                    <td><b>Request</b></td>
                    <td><b>Selling</b></td>
                    <td><b>Exchange</b></td>
                </tr>
            </thead>
            <tbody>
                <?php 
                    foreach ($data as $key => $value) {
                        echo '<tr>
                            <td>'.$value->cat_name.'</td>
                            <td>'.$value->record_count.'</td>
                            <td>'.$this->db->from('bs_items')->where('item_type_id', REQUEST_ITEM)->where('cat_id',  $value->cat_id)->get()->num_rows().'</td>
                            <td>'.$this->db->from('bs_items')->where('item_type_id', SELLING)->where('cat_id',  $value->cat_id)->get()->num_rows().'</td>
                            <td>'.$this->db->from('bs_items')->where('item_type_id', EXCHANGE)->where('cat_id',  $value->cat_id)->get()->num_rows().'</td>
                        </tr>';
                    }
                ?>
            </tbody>
        </table>
    </div>
    <!-- /.table-responsive -->
</div>
