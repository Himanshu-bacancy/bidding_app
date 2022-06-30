<?php
	$attributes = array('id' => 'user-form');
	echo form_open( '', $attributes );
?>
<section class="content animated fadeInRight">

	<div class="card card-info">
	    <div class="card-header">
	      <h3 class="card-title"><?php echo get_msg('user_info')?></h3>
	    </div>

	   
  		<div class="card-body">
    		<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo get_msg('user_name'); ?></label>
						<?php echo form_input( array(
							'name' => 'user_name',
							'value' => set_value( 'user_name', show_data( @$user->user_name ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'user_name' ),
							'id' => 'user_name'
						)); ?>
					</div>

					<?php if($user->email_verify == 1): ?>
					<div class="form-group">
						<label><?php echo get_msg('user_email'); ?></label>
						<?php echo form_input( array(
							'name' => 'user_email',
							'value' => set_value( 'user_email', show_data( @$user->user_email ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'user_email' ),
							'id' => 'user_email'
						)); ?>
					</div>
					<?php endif; ?>

					<?php if($user->phone_verify == 1): ?>
					<div class="form-group">
						<label><?php echo get_msg('user_phone'); ?></label>
						<?php echo form_input( array(
							'name' => 'user_phone',
							'value' => set_value( 'user_phone', show_data( @$user->user_phone ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'user_phone' ),
							'id' => 'user_phone'
						)); ?>
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
                    
                    <div class="form-group">	
						<?php $conds_rating['to_user_id'] = $obj->to_user_id;

                                $total_rating_count = $this->Rate->count_all_by($conds_rating);
                                $sum_rating_value = $this->Rate->sum_all_by($conds_rating)->result()[0]->rating;

                                if($total_rating_count > 0) {
                                    $total_rating_value = number_format((float) ($sum_rating_value  / $total_rating_count), 1, '.', '');
                                } else {
                                    $total_rating_value = 0;
                                }

                                $overall_rating = $total_rating_value;?>
						<label><?php echo get_msg('Overall rating').' :- '.$total_rating_value; ?></label>
					</div>
					
				</div>

				<div class="col-md-6">
					<div class="form-group">	
						<label><?php echo get_msg('user_address'); ?></label>
						<?php echo form_input( array(
							'name' => 'user_address',
							'value' => set_value( 'user_address', show_data( @$user->user_address ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'user_address' ),
							'id' => 'user_address'
						)); ?>
					</div>
					<div class="form-group">	
						<label><?php echo get_msg('user_city'); ?></label>
						<?php echo form_input( array(
							'name' => 'city',
							'value' => set_value( 'city', show_data( @$user->city ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'city' ),
							'id' => 'city'
						)); ?>
					</div>
					<div class="form-group">	
						<label><?php echo get_msg('about_me'); ?></label>
						<?php echo form_input( array(
							'name' => 'user_about_me',
							'value' => set_value( 'user_about_me', show_data( @$user->user_about_me ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'about_me' ),
							'id' => 'user_about_me'
						)); ?>
					</div>
					<div class="form-group">	
						<label><?php echo get_msg('Wallet Amount'); ?></label>
						<?php echo form_input( array(
							'name' => 'wallet_amount',
							'value' => set_value( 'wallet_amount', show_data( @$user->wallet_amount ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'wallet_amount' ),
							'id' => 'wallet_amount',
                            'readonly' => 'true'
						)); 
                        $get_history = $this->db->from('bs_wallet')->where('user_id', $user->user_id)->get()->num_rows();
                        $label = '<br>No wallet history availible';
                        if($get_history) {
                            $label = '<br><a href="'.$module_site_url .'/wallethistory/'.$user->user_id.'">Show Wallet History</a>';
                        } 
                        echo $label;
                        ?>
					</div>
				</div>
			</div>
		</div>
		 <!-- /.card-body -->

		<div class="card-footer">
            <button type="submit" class="btn btn-sm btn-primary">
				<?php echo get_msg('btn_save')?>
			</button>

			<a href="<?php echo $module_site_url; ?>" class="btn btn-sm btn-primary">
				<?php echo get_msg('btn_cancel')?>
			</a>
        </div>
	</div>
</section>

<?php echo form_close(); ?>