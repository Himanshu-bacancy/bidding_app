<div class="table-responsive animated fadeInRight">
	<table class="table m-0 table-striped">
		<tr>
			<th><?php echo get_msg('no'); ?></th>
			<th><?php echo get_msg('carrier_name'); ?></th>
			<th><?php echo get_msg('package_size'); ?></th>
			<th><?php echo get_msg('price'); ?></th>
			<th><?php echo get_msg('days_range'); ?></th>
			
			<?php if ( $this->ps_auth->has_access( EDIT )): ?>
				
				<th><span class="th-title"><?php echo get_msg('btn_edit')?></span></th>
			
			<?php endif; ?>
			
			<?php if ( $this->ps_auth->has_access( DEL )): ?>
				
				<th><span class="th-title"><?php echo get_msg('btn_delete')?></span></th>
			
			<?php endif; ?>
			
			<?php if ( $this->ps_auth->has_access( PUBLISH )): ?>
				
				<th><span class="th-title"><?php echo get_msg('btn_status')?></span></th>
			
			<?php endif; ?>

		</tr>
		
	
	<?php $count = $this->uri->segment(4) or $count = 0; ?>

	<?php if ( !empty( $shippingcarriers ) && count( $shippingcarriers->result()) > 0 ): ?>

		<?php foreach($shippingcarriers->result() as $shippingcarrier): ?>
			
			<tr>
				<td><?php echo ++$count;?></td>
				<td ><?php echo $shippingcarrier->name;?></td>
				<td ><?php echo $this->Packagesizes->get_one( $shippingcarrier->packagesize_id )->name; ?></td>
				<td ><?php echo $shippingcarrier->price;?></td>
				<td ><?php echo $shippingcarrier->min_days;?> - <?php echo $shippingcarrier->max_days;?> <?php echo get_msg('days')?></td>

				<?php if ( $this->ps_auth->has_access( EDIT )): ?>
			
					<td>
						<a href='<?php echo $module_site_url .'/edit/'. $shippingcarrier->id; ?>'>
							<i style='font-size: 18px;' class='fa fa-pencil-square-o'></i>
						</a>
					</td>
				
				<?php endif; ?>
				
				<?php if ( $this->ps_auth->has_access( DEL )): ?>
					
					<td>
						<a herf='#' class='btn-delete' data-toggle="modal" data-target="#myModal" id="<?php echo $shippingcarrier->id;?>">
							<i style='font-size: 18px;' class='fa fa-trash-o'></i>
						</a>
					</td>
				
				<?php endif; ?>
				
				<?php if ( $this->ps_auth->has_access( PUBLISH )): ?>
					
					<td>
						<?php if ( @$shippingcarrier->status == 1): ?>
							<button class="btn btn-sm btn-success unpublish" id='<?php echo $shippingcarrier->id;?>'>
							<?php echo get_msg( 'btn_active' ); ?></button>
						<?php else:?>
							<button class="btn btn-sm btn-danger publish" id='<?php echo $shippingcarrier->id;?>'>
							<?php echo get_msg( 'btn_inactive' ); ?></button><?php endif;?>
					</td>
				
				<?php endif; ?>

			</tr>

		<?php endforeach; ?>

	<?php else: ?>
			
		<?php $this->load->view( $template_path .'/partials/no_data' ); ?>

	<?php endif; ?>

</table>
</div>

