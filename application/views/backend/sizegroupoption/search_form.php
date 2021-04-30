<div class='row my-3'>

	<div class='col-9'>
	<?php
		$attributes = array('class' => 'form-inline');
		echo form_open( $module_site_url .'/search/'.$sizegroup->id, $attributes);
	?>
		
		<div class="form-group mr-3">

			<?php echo form_input(array(
				'name' => 'searchterm',
				'value' => set_value( 'searchterm' ),
				'class' => 'form-control form-control-sm',
				'placeholder' => get_msg( 'btn_search' )
			)); ?>

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
    <?php 
        if(isset($sizegroup->id) && !empty($sizegroup->id)){
    ?>
        <div class='col-3'>
            <a href='<?php echo $module_site_url .'/addoption/'. $sizegroup->id;?>' class='btn btn-sm btn-primary pull-right'>
                <span class='fa fa-plus'></span> 
                Add SizeGroup Option
            </a>
        </div>
    <?php } ?>

</div>