<?php
	$attributes = array('id' => 'user-form');
	echo form_open( '', $attributes );
?>
<div class="card card-info  animated fadeInRight">
	<div class="card-header">
        <h3 class="card-title"><?php echo get_msg('user_info')?></h3>
    </div>

	<div id="perm_err" class="alert alert-danger fade in" style="display: none">
		<label for="permissions[]" class="error"></label>
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
	</div>
	<!-- /.card-header -->
    <div class="card-body">	
		<div class="row">
			<div class="col-6">
					<div class="form-group">
						<label><?php echo get_msg('user_name')?></label>

						<?php echo form_input(array(
							'name' => 'user_name',
							'value' => set_value( 'user_name', show_data( @$user->user_name ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => 'Name',
							'id' => 'name'
						)); ?>

					</div>
					
					<div class="form-group">
						<label><?php echo get_msg('user_email')?></label>

						<?php echo form_input(array(
							'name' => 'user_email',
							'value' => set_value( 'user_email', show_data( @$user->user_email ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => 'Email',
							'id' => 'user_email'
						)); ?>

					</div>
					
					<?php if ( @$user->user_is_sys_admin == false ): ?>

					<div class="form-group">
						<label><?php echo get_msg('user_password')?></label>

						<?php echo form_input(array(
							'type' => 'password',
							'name' => 'user_password',
							'value' => set_value( 'user_password' ),
							'class' => 'form-control form-control-sm',
							'placeholder' => 'Password',
							'id' => 'user_password'
						)); ?>
					</div>
								
					<div class="form-group">
						<label><?php echo get_msg('conf_password')?></label>
						
						<?php echo form_input(array(
							'type' => 'password',
							'name' => 'conf_password',
							'value' => set_value( 'conf_password' ),
							'class' => 'form-control form-control-sm',
							'placeholder' => 'Confirm Password',
							'id' => 'conf_password'
						)); ?>
					</div>
					
					<div class="form-group">
						<label><?php echo get_msg('role')?></label>

						<?php 
							$conds['role_id'] = 1;
							$result = $this->User->get_one_by($conds);
							if (isset($result)) {
							
								$options = array();
								foreach($this->Role->get_all()->result() as $role) {
									if ($role->role_id != 1) {
										$options[$role->role_id] = $role->role_desc;
									}
								}

								echo form_dropdown(
									'role_id',
									$options,
									set_value( 'role_id', @$user->role_id ),
									'class="form-control form-control-sm" id="role_id"'
								);
							} else {
								$options = array();
								foreach($this->Role->get_all()->result() as $role) {
									$options[$role->role_id] = $role->role_desc;
								}

								echo form_dropdown(
									'role_id',
									$options,
									set_value( 'role_id', @$user->role_id ),
									'class="form-control form-control-sm" id="role_id"'
								);
							}
							
						?>
					</div>

					<?php endif; ?>

					<div class="form-group" style="padding-top: 30px;">
		              <div class="form-check">

		                <label>
		                
		                  <?php echo form_checkbox( array(
		                    'name' => 'is_show_email',
		                    'id' => 'is_show_email',
		                    'value' => 'accept',
		                    'checked' => set_checkbox('is_show_email', 1, ( @$user->is_show_email == 1 )? true: false ),
		                    'class' => 'form-check-input'
		                    
		                  )); ?>

		                  <?php echo get_msg( 'is_show_email' ); ?>
		                </label>
		              </div><br>

		               <div class="form-check">

		                <label>
		                
		                  <?php echo form_checkbox( array(
		                    'name' => 'is_show_phone',
		                    'id' => 'is_show_phone',
		                    'value' => 'accept',
		                    'checked' => set_checkbox('is_show_phone', 1, ( @$user->is_show_phone == 1 )? true: false ),
		                    'class' => 'form-check-input'
		                    
		                  )); ?>

		                  <?php echo get_msg( 'is_show_phone' ); ?>
		                </label>
		              </div>
		            </div>
			</div>
			
			<?php if ( @$user->user_is_sys_admin == false ): ?>

			<div class="col-6" style="padding-left: 50px;">
				<div class="form-group">
					<label> <span style="font-size: 17px; color: red;">*</span>
							<?php echo get_msg('allowed_modules')?></label>
						
						<?php 
							if (!@$user) {
							foreach($this->Module->get_all_module()->result() as $module): 
							if( $module->module_id != 4 ) {
						?>

							<div class="form-check">
								<label class="form-check-label">
								
								<?php echo form_checkbox('permissions[]', $module->module_id, set_checkbox('permissions', $module->module_id, $this->User->has_permission( $module->module_id, @$user->user_id )),'checked'); ?>

								<?php echo $module->module_desc; ?>

								</label>
							</div>
						<?php } ?>
						<?php endforeach; ?>
						<?php 
							} else { 
								foreach($this->Module->get_all_module()->result() as $module): 
								if( $module->module_id != 4 ) {
						?>
							<div class="form-check">
								<label class="form-check-label">
								
								<?php echo form_checkbox('permissions[]', $module->module_id, set_checkbox('permissions', $module->module_id, $this->User->has_permission( $module->module_id, @$user->user_id ))); ?>
								<?php echo $module->module_desc; ?>

								</label>
							</div>
							<?php } ?>
						<?php endforeach; ?>
					<?php } ?>
				</div>
			</div>

			<?php endif; ?>

		</div>
	</div>
	
	<div class="card-footer">
		<button type="submit" class="btn btn-sm btn-primary"><?php echo get_msg('btn_save')?></button>
		<a href="<?php echo $module_site_url; ?>" class="btn btn-sm btn-primary"><?php echo get_msg('btn_cancel')?></a>
	</div>
</div>
<?php echo form_close(); ?>