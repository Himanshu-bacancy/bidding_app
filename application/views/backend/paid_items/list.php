<div class="table-responsive animated fadeInRight">
	<table class="table m-0 table-striped">
		<tr>
			<th><?php echo get_msg('no'); ?></th>
			<th><?php echo get_msg('item_name'); ?></th>
			<th><?php echo get_msg('cat_name'); ?></th>
			<th><?php echo get_msg('subcat_name'); ?></th>
			<th><?php echo get_msg('status_label'); ?></th>
			
			<?php if ( $this->ps_auth->has_access( EDIT )): ?>
				
				<th><span class="th-title"><?php echo get_msg('btn_edit')?></span></th>
			
			<?php endif; ?>
		</tr>
		
	
	<?php $count = $this->uri->segment(4) or $count = 0; ?>

	<?php if ( !empty( $paid_items ) && count( $paid_items->result()) > 0 ): ?>

		<?php foreach($paid_items->result() as $item): ?>
			
			<tr>
				<td><?php echo ++$count;?></td>
				<td><?php echo $item->title;?></td>
				<td><?php echo $this->Category->get_one( $item->cat_id )->cat_name; ?></td>
				<td><?php echo $this->Subcategory->get_one( $item->sub_cat_id )->name; ?></td>
				<td>
					<?php if ($item->status == 1) {
						echo "Published";
						} elseif ($item->status == 2) {
							echo "Reported";
						}
					?>
				</td>

				<?php if ( $this->ps_auth->has_access( EDIT )): ?>
			
					<td>
						<a href='<?php echo $module_site_url .'/edit_paid/'. $item->id; ?>'>
							<i class='fa fa-pencil-square-o'></i>
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