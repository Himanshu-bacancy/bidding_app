<div class="table-responsive animated fadeInRight">
	<table class="table m-0 table-striped">
		<tr>
			<th><?php echo get_msg('no'); ?></th>
			<th><?php echo get_msg('plan_name'); ?></th>
			<th><?php echo get_msg('plan_code'); ?></th>
			<th><?php echo get_msg('plan_price'); ?></th>
			<th><?php echo get_msg('no_of_days'); ?></th>
			
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

	<?php if ( !empty( $promotingitemplans ) && count( $promotingitemplans->result()) > 0 ): ?>

		<?php foreach($promotingitemplans->result() as $promotingitemplan): ?>
			
			<tr>
				<td><?php echo ++$count;?></td>
				<td ><?php echo $promotingitemplan->name;?></td>
				<td ><?php echo $promotingitemplan->code;?></td>
				<td ><?php echo $promotingitemplan->price;?></td>
				<td ><?php echo $promotingitemplan->days;?></td>

				<?php if ( $this->ps_auth->has_access( EDIT )): ?>
			
					<td>
						<a href='<?php echo $module_site_url .'/edit/'. $promotingitemplan->id; ?>'>
							<i style='font-size: 18px;' class='fa fa-pencil-square-o'></i>
						</a>
					</td>
				
				<?php endif; ?>
				
				<?php if ( $this->ps_auth->has_access( DEL )): ?>
					
					<td>
						<a herf='#' class='btn-delete' data-toggle="modal" data-target="#myModal" id="<?php echo $promotingitemplan->id;?>">
							<i style='font-size: 18px;' class='fa fa-trash-o'></i>
						</a>
					</td>
				
				<?php endif; ?>
				
				<?php if ( $this->ps_auth->has_access( PUBLISH )): ?>
					
					<td>
						<?php if ( @$promotingitemplan->status == 1): ?>
							<button class="btn btn-sm btn-success unpublish" id='<?php echo $promotingitemplan->id;?>'>
							<?php echo get_msg( 'btn_active' ); ?></button>
						<?php else:?>
							<button class="btn btn-sm btn-danger publish" id='<?php echo $promotingitemplan->id;?>'>
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

