<?php
	$attributes = array('id' => 'search-form', 'enctype' => 'multipart/form-data');
	echo form_open( $module_site_url .'/search', $attributes);
?>

<div class="row my-3">	
	<!-- end form-inline -->
	<div class="form-inline">

		<div class="form-group">
			<?php echo form_input(array(
				'name' => 'searchterm',
				'value' => set_value( 'searchterm' ),
				'class' => 'form-control form-control-sm',
				'placeholder' => get_msg( 'btn_search' ),
				'style' => 'float: left; margin-right: 20px;'
			)); ?>
		</div>

		<div class="form-group">

			<?php

				echo get_msg( 'status_label' );
				$options = array( 0 => get_msg('select_status_label'), 1 => 'Progress', 2 => 'Finished', 3 => 'Not yet start');

				echo form_dropdown(
					'status[]',
					$options,
					set_value( 'status', show_data( $status ), false ),
					'class="form-control form-control-sm mr-3 ml-3" id="status"'
				);
				
			?>

  		</div>
		
		<div class="input-group" style="padding-top: 5px;">
		    <div class="input-group-prepend">
		      <span class="input-group-text">
		        <i class="fa fa-calendar"></i>
		      </span>
		    </div>
			   <?php echo form_input(array(
					'name' => 'date',
					'value' => set_value( 'date' ),
					'class' => 'form-control',
					'placeholder' => '',
					'id' => 'reservation',
					'size' => '20',
					'readonly' => 'readonly'
				)); ?>

 		</div>

		<div class="form-group" style="padding-left: 10px;padding-top: 5px;">
		  	<button type="submit" value="submit" name="submit" class="btn btn-sm btn-primary">
		  		<?php echo get_msg( 'btn_search' )?>
		  	</button>
	  	</div>

	  	<div class="row">
	  		<div class="form-group ml-3" style="padding-top: 5px;">
			  	<a href="<?php echo $module_site_url; ?>" class="btn btn-sm btn-primary">
					  		<?php echo get_msg( 'btn_reset' ); ?>
				</a>
			</div>
		</div>

	</div>

</div>

<?php echo form_close(); ?>