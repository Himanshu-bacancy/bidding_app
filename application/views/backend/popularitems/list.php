<div class="table-responsive animated fadeInRight">
	<table class="table m-0 table-striped">
	<tr>
		<th><?php echo get_msg('no'); ?></th>
		<th><?php echo get_msg('item_name'); ?></th>
		<th><?php echo get_msg('category'); ?></th>
		<th><?php echo get_msg('subcategory'); ?></th>
		<th><?php echo get_msg('item type'); ?></th>
		<th><span class="th-title"><?php echo get_msg('view'); ?></span></th>
		<th><?php echo get_msg('total views'); ?></th>
	</tr>

	<?php $count = $this->uri->segment(4) or $count = 0; ?>

	<?php if ( !empty( $popularitems ) && count( $popularitems->result()) > 0 ): ?>

		<?php foreach($popularitems->result() as $popularitem): ?>
			
			<tr>
				<td><?php echo ++$count;?></td>
				<td><?php echo $popularitem->title;?></td>
				<td><?php 
                $cat_name = $this->db->select('cat_name')->from('bs_categories')->where('cat_id',$popularitem->cat_id)->get()->row();
                echo $cat_name->cat_name;?></td>
				<td><?php 
                $subcat_name = $this->db->select('name')->from('bs_subcategories')
                        ->where('cat_id',$popularitem->cat_id)
                        ->where('id',$popularitem->sub_cat_id)
                        ->get()->row();
                echo $subcat_name->name;?></td>
				<td><?php 
                $type_name = $this->db->select('name')->from('bs_items_types')
                        ->where('id',$popularitem->item_type_id)
                        ->get()->row();
                echo $type_name->name;?></td>

				<?php if ( $this->ps_auth->has_access( EDIT )): ?>
			
					<td>
						<a href='<?php echo $module_site_url .'/edit/'. $popularitem->id; ?>'>
							<i class='fa fa-eye'></i>
						</a>
					</td>
				
				<?php endif; ?>
                <td><?php echo $popularitem->touch_count;?></td>
			</tr>

		<?php endforeach; ?>

	<?php else: ?>
			
		<?php $this->load->view( $template_path .'/partials/no_data' ); ?>

	<?php endif; ?>

</table>
</div>