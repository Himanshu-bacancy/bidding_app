<div class="table-responsive animated fadeInRight">
	<table class="table m-0 table-striped">
		<tr>
			<th><?php echo get_msg('no'); ?></th>
			<th><?php echo get_msg('user_name'); ?></th>
			<th><?php echo get_msg('amount'); ?></th>
            <th><?php echo get_msg('type'); ?></th>
            <th><?php echo get_msg('created_at'); ?></th>
		</tr>
	<?php $count = $this->uri->segment(4) or $count = 0; ?>
	<?php if ( !empty( $payouts ) && count( $payouts->result()) > 0 ): ?>
		<?php foreach($payouts->result() as $payout): ?>
			<tr>
				<td><?php echo ++$count;?></td>
				<td><?php echo $this->User->get_one( $payout->user_id )->user_name ? $this->User->get_one( $payout->user_id )->user_name : 'N/A'; ?></td>
                <td><?php echo $payout->amount;?></td>
                <td><?php echo (substr($payout->external_account_id, 0, 3) == 'ba_') ? 'Bank Transfer' : 'Instantpay';?></td>
                <td><?php echo $payout->created_at;?></td>

				<?php // if ( $this->ps_auth->has_access( VIEW )): ?>
<!--					<td>
						<a href='<?php echo $module_site_url .'/edit/'. $order->id; ?>'>
                            <i class="fa fa-eye" style='font-size: 18px;'></i>
						</a>
					</td>-->
				<?php // endif; ?>
			</tr>
		<?php endforeach; ?>
	<?php else: ?>
		<?php $this->load->view( $template_path .'/partials/no_data' ); ?>
	<?php endif; ?>
</table>
</div>

