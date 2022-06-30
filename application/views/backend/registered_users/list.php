<style>
    .dataTables_wrapper .dataTables_paginate {
        float: none;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0px;
        margin-left: 0px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        border: none;
        background: none;
    }
    .dataTables_length {
        margin-top: 15px;
    }
    .dataTables_length label {
        display: flex;
        position: inherit;
    }
    .register_user_table_length{
        width: auto;
    }
</style>
<div class=" animated fadeInRight">
    <table class="table-responsive" id="register_user_table" >
        <thead>
		<tr>
			<th><input type="checkbox" name="select_all"  id="example-select-all"></th>
			<th><?php echo get_msg('user_name')?></th>
			<th><?php echo get_msg('user status')?></th>
			<th><?php echo get_msg('user_email')?></th>
			<th><?php echo get_msg('state')?></th>
			<th><?php echo get_msg('city')?></th>
			<th><?php echo get_msg('user_phone')?></th>
			<!-- <th><?php echo get_msg('view'); ?></th> -->
			<!--<th><?php echo get_msg('role')?></th>-->
			<th><?php echo get_msg('added date')?></th>

			<?php if ( $this->ps_auth->has_access( EDIT )): ?>
				
				<th><?php echo get_msg('btn_edit')?></th>

			<?php endif;?>

			<?php if ( $this->ps_auth->has_access( BAN )): ?>
				
				<th><?php echo get_msg('user_ban')?></th>

			<?php endif;?>

			<?php if ( $this->ps_auth->has_access( DEL )): ?>
				
				<th><span class="th-title"><?php echo get_msg('btn_delete')?></span></th>
			
			<?php endif; ?>

		</tr>
        </thead>
        <tbody>
		<?php $count = $this->uri->segment(4) or $count = 0; ?>
				
			<?php foreach($users->result() as $user): 
                
                $this->db->select('*');
                $this->db->from('bs_addresses');
                $this->db->where(array('user_id' => $user->user_id));
                if(isset($search_state) && !empty($search_state) && $search_state) {
                    $this->db->where(array('state' => $search_state));
                }
                if(isset($search_city) && !empty($search_city) && $search_city) {
                    $this->db->where(array('city' => $search_city));
                }
                if(!isset($search_city) && !isset($search_state)) {
                    $this->db->where(array('is_default_address' => 1));
                }
                $defaultdata = $this->db->get()->row();
                ?>
				
				<tr>
					<td><?php echo $user->user_id;?></td>
					<td><?php echo $user->user_name;?></td>
					<td><?php echo ($user->status == 1) ? 'Active' : 'Inactive';?></td>
					<td><?php echo $user->user_email;?></td>
					<td><?php echo $defaultdata->state;?></td>
					<td><?php echo $defaultdata->city;?></td>
					<td><?php echo $user->user_phone;?></td>
					<!-- <?php if ( $this->ps_auth->has_access( EDIT )): ?>
					
					<td>
						<a href='<?php echo $module_site_url .'/edit/'. $user->user_id; ?>'>
							<i class='fa fa-eye'></i>
						</a>
					</td>
				
				
					<?php endif; ?> -->
					<td><?php echo $user->added_date;?></td>

					<?php if ( $this->ps_auth->has_access( EDIT )): ?>
					
					<td>
						<a href='<?php echo $module_site_url .'/edit/'. $user->user_id; ?>'>
							<i class='fa fa-pencil-square-o'></i>
						</a>
					</td>
				
				
					<?php endif; ?>

					<?php if ( $this->ps_auth->has_access( BAN )):?>
					
						<td>
							<?php if ( @$user->is_banned == 0 ): ?>
								
								<button class="btn btn-sm btn-primary-green ban" userid='<?php echo @$user->user_id;?>'>
									<?php echo get_msg( 'user_ban' ); ?>
								</button>
							
							<?php else: ?>
								
								<button class="btn btn-sm btn-danger unban" userid='<?php echo @$user->user_id;?>'>
									<?php echo get_msg( 'user_unban' ); ?>
								</button>
							
							<?php endif; ?>

						</td>

					<?php endif;?>

					<?php if ( $this->ps_auth->has_access( DEL )): ?>
					
					<td>
						<a herf='#' class='btn-delete' data-toggle="modal" data-target="#myModal" id="<?php echo $user->user_id;?>">
							<i style='font-size: 18px;' class='fa fa-trash-o'></i>
						</a>
					</td>
				
				<?php endif; ?>

				</tr>
			
			<?php endforeach; ?>

        </tbody>
	</table>
</div>