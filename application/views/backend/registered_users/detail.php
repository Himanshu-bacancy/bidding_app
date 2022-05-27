<div class="table-responsive animated fadeInRight">
	<table class="table m-0 table-striped">

		<tr>
			<th><?php echo get_msg('no')?></th>
			<!--<th><?php echo get_msg('parent id')?></th>-->
			<th><?php echo get_msg('amount')?></th>
			<th><?php echo get_msg('type')?></th>
			<th><?php echo get_msg('Date')?></th>

		</tr>


		<?php if ( !empty( $wallet_history ) && count( $wallet_history) > 0 ): ?>
				
			<?php foreach($wallet_history as $history): ?>
				
				<tr>
					<td><?php echo $history->id;?></td>
					<!--<td><?php echo $history->parent_id;?></td>-->
					<td><?php echo $history->amount;?></td>
					<td><?php echo $history->type;?></td>
					<td><?php echo $history->created_at;?></td>
				</tr>
			
			<?php endforeach; ?>

		<?php else: ?>
				
			<?php $this->load->view( $template_path .'/partials/no_data' ); ?>

		<?php endif; ?>

	</table>
</div>