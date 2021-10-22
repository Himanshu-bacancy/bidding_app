<div class="table-responsive animated fadeInRight">
	<table class="table m-0 table-striped">
		<tr>
			<th><?php echo get_msg('no'); ?></th>
			<th><?php echo get_msg('Item'); ?></th>
			<th><?php echo get_msg('user_name'); ?></th>
            <th><?php echo get_msg('status'); ?></th>
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
                <td><?php echo $order->status ? $order->status : 'N/A'; ?></td>

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

