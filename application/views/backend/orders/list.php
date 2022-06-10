<div class="table-responsive animated fadeInRight">
	<table class="table m-0 table-striped">
		<tr>
			<th><?php echo get_msg('no'); ?></th>
			<th><?php echo get_msg('Item'); ?></th>
			<th><?php echo get_msg('user_name'); ?></th>
            <th><?php echo get_msg('Order Status'); ?></th>
			<th><?php echo get_msg('Price'); ?></th>
			<?php if ( $this->ps_auth->has_access( EDIT )): ?>
				<th><span class="th-title"><?php echo get_msg('btn_view')?></span></th>
			<?php endif; ?>			
		</tr>
	<?php $count = $this->uri->segment(4) or $count = 0; ?>
	<?php if ( !empty( $orders ) && count( $orders->result()) > 0 ): ?>
		<?php foreach($orders->result() as $order): ?>
			<tr>
				<td><?php echo ++$count;?></td>
				<td><?php echo $this->Item->get_one( $order->items )->title; ?></td>
				
				<td><?php echo $this->User->get_one( $order->user_id )->user_name ? $this->User->get_one( $order->user_id )->user_name : 'N/A'; ?></td>
                <td><?php // echo $order->status ? $order->status : 'N/A'; 
                $print_status = $order->status ? $order->status : 'N/A';
                if($order->is_return) {
                    $return_details = $this->db->select('status')->from('bs_return_order')
                        ->where('order_id', $order->order_id)
                        ->get()->row();
                    if($return_details->status == 'initiate') {
                        $print_status = 'Return requested';
                    } else if($return_details->status == 'reject') {
                        $print_status = 'Return reject by seller';
                        if($order->is_dispute){
                            $dispute_details = $this->db->select('status')->from('bs_dispute')->where('order_id', $order->order_id)->get()->row();
                            if($dispute_details->status == 'initiate') {
                                $print_status = 'Dispute generated';
                            } else if($dispute_details->status == 'reject') { 
                                $print_status = 'Dispute rejected';
                            } else if($dispute_details->status == 'solve') { 
                                $print_status = 'Dispute solved';
                            }
                        }
                    } else if($return_details->status == 'accept') {
                        $print_status = 'Return accept by seller';
                    } else if ($return_details->status == 'cancel') {
                        $print_status = 'Return cancel by buyer';
                    }
                    echo '<a href="'.$module_site_url .'/returndetail/'.$order->id.'">'.$print_status.'</a>';
                } else {
                    echo $print_status;
                }
                ?>
                </td>
                <td><?php echo $order->total_amount; ?></td>

				<?php if ( $this->ps_auth->has_access( VIEW )): ?>
					<td>
						<a href='<?php echo $module_site_url .'/edit/'. $order->id; ?>'>
                            <i class="fa fa-eye" style='font-size: 18px;'></i>
						</a>
					</td>
				<?php endif; ?>
			</tr>
		<?php endforeach; ?>
	<?php else: ?>
		<?php $this->load->view( $template_path .'/partials/no_data' ); ?>
	<?php endif; ?>
</table>
</div>

