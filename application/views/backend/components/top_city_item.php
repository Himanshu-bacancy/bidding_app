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
                        $request_item = $this->db->query('SELECT count(bs_items.id) as item_count FROM `bs_items` join bs_addresses on bs_items.Address_id = bs_addresses.id where bs_items.item_type_id = '.REQUEST_ITEM.' and bs_addresses.city = "'.$value->city.'" group by bs_addresses.city')->row()->item_count;
                        
                        $selling_item = $this->db->query('SELECT count(bs_items.id) as item_count FROM `bs_items` join bs_addresses on bs_items.Address_id = bs_addresses.id where bs_items.item_type_id = '.SELLING.' and bs_addresses.city = "'.$value->city.'" group by bs_addresses.city')->row()->item_count;
                        
                        $exchange_item = $this->db->query('SELECT count(bs_items.id) as item_count FROM `bs_items` join bs_addresses on bs_items.Address_id = bs_addresses.id where bs_items.item_type_id = '.EXCHANGE.' and bs_addresses.city = "'.$value->city.'" group by bs_addresses.city')->row()->item_count;
                        
                        echo '<tr>
                            <td>'.$value->city.'</td>
                            <td>'.$value->record_count.'</td>
                            <td>'.(($request_item) ?? 0).'</td>
                            <td>'.(($selling_item) ?? 0).'</td>
                            <td>'.(($exchange_item) ?? 0).'</td>
                        </tr>';
                    }
                ?>
            </tbody>
        </table>
    </div>
    <!-- /.table-responsive -->
</div>
