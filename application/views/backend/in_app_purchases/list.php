<div class="table-responsive animated fadeInRight">
	<table class="table m-0 table-striped">
		<tr>
			<th><?php echo get_msg('no'); ?></th>
			<th><?php echo get_msg('In app purchased Product Id'); ?> </th>
			<th><?php echo get_msg('Description'); ?></th>
			<th><?php echo get_msg('Day'); ?></th>
			<th><?php echo get_msg('Type'); ?></th>

			
			<?php if ( $this->ps_auth->has_access( EDIT )): ?>
				
				<th><span class="th-title"><?php echo get_msg('btn_edit')?></span></th>
			
			<?php endif; ?>
			
			<?php if ( $this->ps_auth->has_access( DEL )): ?>
				
				<th><span class="th-title"><?php echo get_msg('btn_delete')?></span></th>
			
			<?php endif; ?>

			<?php if ( $this->ps_auth->has_access( PUBLISH )): ?>
			
			<th><?php echo get_msg('btn_publish')?></th>
		
		    <?php endif; ?>
			
		</tr>
		
	
	<?php $count = $this->uri->segment(4) or $count = 0; ?>

	<?php if ( !empty( $in_app_purchases ) && count( $in_app_purchases->result()) > 0 ): ?>

		<?php foreach($in_app_purchases->result() as $in_app_purchase): ?>
			
			<tr>
				<td><?php echo ++$count;?></td>
				<td><?php echo $in_app_purchase->in_app_purchase_prd_id;?></td>
				<td><?php echo $in_app_purchase->description;?></td>
				<td><?php echo $in_app_purchase->day;?></td>
				<td><?php echo  $this->In_app_purchase->get_one( $in_app_purchase->id )->type;?></td>
				
				<?php if ( $this->ps_auth->has_access( EDIT )): ?>
			
					<td>
						<a href='<?php echo $module_site_url .'/edit/'. $in_app_purchase->id; ?>'>
							<i class='fa fa-pencil-square-o'></i>
						</a>
					</td>
				
				<?php endif; ?>
				
				<?php if ( $this->ps_auth->has_access( DEL )): ?>
					
					<td>
						<a herf='#' class='btn-delete' data-toggle="modal" data-target="#myModal" id="<?php echo "$in_app_purchase->id";?>">
							<i class='fa fa-trash-o'></i>
						</a>
					</td>
				
				<?php endif; ?>

				<?php if ( $this->ps_auth->has_access( PUBLISH )): ?>
					
					<td>
						<?php if ( @$in_app_purchase->status == 1): ?>
							<button class="btn btn-sm btn-success unpublish" id='<?php echo $in_app_purchase->id;?>'>
							<?php echo get_msg('btn_yes'); ?></button>
						<?php else:?>
							<button class="btn btn-sm btn-danger publish" id='<?php echo $in_app_purchase->id;?>'>
							<?php echo get_msg('btn_no'); ?></button><?php endif;?>
					</td>
				
				<?php endif; ?>

			</tr>

		<?php endforeach; ?>

	<?php else: ?>
			
		<?php $this->load->view( $template_path .'/partials/no_data' ); ?>

	<?php endif; ?>

</table>
</div>

