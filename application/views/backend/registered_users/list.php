<div class="table-responsive animated fadeInRight">
	<table class="table m-0 table-striped">

		<tr>
			<th><?php echo get_msg('no')?></th>
			<th><?php echo get_msg('id')?></th>
			<th><?php echo get_msg('user_name')?></th>
			<th><?php echo get_msg('user status')?></th>
			<th><?php echo get_msg('user_email')?></th>
			<th><?php echo get_msg('state')?></th>
			<th><?php echo get_msg('city')?></th>
			<th><?php echo get_msg('user_phone')?></th>
			<!-- <th><?php echo get_msg('view'); ?></th> -->
			<th><?php echo get_msg('role')?></th>

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

		<?php $count = $this->uri->segment(4) or $count = 0; ?>

		<?php if ( !empty( $users ) && count( $users->result()) > 0 ): ?>
				
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
					<td><?php echo ++$count;?></td>
					<td><?php echo $user->user_id;?></td>
					<td><?php echo $user->user_name;?></td>
					<td><?php echo ($user->status) ? 'Active' : 'Inactive';?></td>
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
					<td><?php echo "Registered User";?></td>

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

		<?php else: ?>
				
			<?php $this->load->view( $template_path .'/partials/no_data' ); ?>

		<?php endif; ?>

	</table>
</div>