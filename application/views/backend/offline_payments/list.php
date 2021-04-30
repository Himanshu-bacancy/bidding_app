<div class="table-responsive animated fadeInRight">
	<table class="table m-0 table-striped">
		<tr>
			<th><?php echo get_msg('no'); ?></th>
			<th><?php echo get_msg('Title'); ?></th>
			<th><?php echo get_msg('Description'); ?></th>

			
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

	<?php if ( !empty( $offline_payments ) && count( $offline_payments->result()) > 0 ): ?>

		<?php foreach($offline_payments->result() as $offline_payment): ?>
			
			<tr>
				<td><?php echo ++$count;?></td>
				<td><?php echo $offline_payment->title;?></td>
				<td><?php echo $offline_payment->description;?></td>
				
				<?php if ( $this->ps_auth->has_access( EDIT )): ?>
			
					<td>
						<a href='<?php echo $module_site_url .'/edit/'. $offline_payment->id; ?>'>
							<i class='fa fa-pencil-square-o'></i>
						</a>
					</td>
				
				<?php endif; ?>
				
				<?php if ( $this->ps_auth->has_access( DEL )): ?>
					
					<td>
						<a herf='#' class='btn-delete' data-toggle="modal" data-target="#myModal" id="<?php echo "$offline_payment->id";?>">
							<i class='fa fa-trash-o'></i>
						</a>
					</td>
				
				<?php endif; ?>
				<?php if ( $this->ps_auth->has_access( PUBLISH )): ?>
					
					<td>
						<?php if ( @$offline_payment->status == 1): ?>
							<button class="btn btn-sm btn-success unpublish" id='<?php echo $offline_payment->id;?>'>
							<?php echo get_msg('btn_yes'); ?></button>
						<?php else:?>
							<button class="btn btn-sm btn-danger publish" id='<?php echo $offline_payment->id;?>'>
							<?php echo get_msg('btn_no'); ?></button></button><?php endif;?>
					</td>
				
				<?php endif; ?>

			</tr>
				
			</tr>

		<?php endforeach; ?>

	<?php else: ?>
			
		<?php $this->load->view( $template_path .'/partials/no_data' ); ?>

	<?php endif; ?>

</table>
</div>

