<div class='row my-3'>
    <?php 
        if($this->router->fetch_class() == 'orders' && in_array($this->router->fetch_method(),['index','search'])) {
    ?>
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
				$options[]= get_msg('select_filter');
				$options[]= get_msg('All Order');
				$options[]= get_msg('Return Order');
//				$topics = $this->Hctopic->get_all();
//				foreach($topics->result() as $topic) {
//                    $options[$topic->id] = $topic->name;
//				}

				echo form_dropdown(
					'is_return',
					$options,
					set_value( 'is_return', show_data( @$orders->is_return), false ),
					'class="form-control form-control-sm mr-3" id="is_return"'
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
        <?php }?>
</div>