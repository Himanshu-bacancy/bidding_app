<div class='row my-3'>

	<div class='col-9'>
	<?php
		$attributes = array('class' => 'form-inline');
		echo form_open( $module_site_url .'/search', $attributes);
	?>
		
<!--		<div class="form-group mr-3">

			<?php echo form_input(array(
				'name' => 'searchterm',
				'value' => set_value( 'searchterm' ),
				'class' => 'form-control form-control-sm',
				'placeholder' => get_msg( 'btn_search' )
			)); ?>

	  	</div>-->
        <div class="form-group" style="padding-right: 3px;">

			<?php
				$options=array();
				$options[]= get_msg('select_user');
                $get_records = $payouts->result_array();
                $usersids = array_unique(array_column($get_records,'user_id'));
                foreach($usersids as $value) {
                    $options[$value] = $this->User->get_one($value)->user_name;
                }
				echo form_dropdown(
					'user_filter',
					$options,
					set_value( 'is_return', show_data( @$payout->user_id), false ),
					'class="form-control form-control-sm mr-3" id="user_filter"'
				);
			?>

	  	</div>

		<div class="form-group">
		  	<button type="submit" class="btn btn-sm btn-primary">
		  		<?php echo get_msg( 'btn_search' )?>
		  	</button>
	  	</div>

	  	<div class="row">
	  		<div class="form-group ml-3">
			  	<a href="<?php echo $module_site_url; ?>" class="btn btn-sm btn-primary">
					  		<?php echo get_msg( 'btn_reset' ); ?>
				</a>
			</div>
		</div>
	
	<?php echo form_close(); ?>

	</div>	

</div>