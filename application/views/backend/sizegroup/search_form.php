<div class='row my-3'>

	<div class='col-9'>
	<?php
		$attributes = array('class' => 'form-inline');
		echo form_open( $module_site_url .'/search', $attributes);
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

	<div class='col-3'>
		<a href='<?php echo $module_site_url .'/add';?>' class='btn btn-sm btn-primary pull-right'>
			<span class='fa fa-plus'></span> 
			Add Size Group
		</a>
	</div>

</div>

<div class='row my-3'>
    <div class='col-6'>
        <?php
            $attributes = array('class' => 'form-inline', 'enctype' => 'multipart/form-data');
            echo form_open( $module_site_url .'/uploadbyscv', $attributes);
        ?>
            <div class="form-group mr-2">
                <input class="form-control form-control-sm" type="file" name="file" id="file">
            </div>

            <div class="form-group">
                <button type="submit" name="upload_submit" value="upload_submit" class="btn btn-sm btn-primary">
                    <?php echo get_msg( 'Import' )?>
                </button>
            </div>
        
        <?php echo form_close(); ?>
    </div>
    <div class='col-6'>
        <b>Please make sure that :-</b> <br>
        <label>1. Upload valid csv format </label> <br>
        <label>2. Start the content from first row itself without heading </label> <br>
        <label>3. First column contains sizegroup name in each column individually </label> <br>
        <label>4. Second column contains sizegroup options of that specific size as comma separated </label> <br>
    </div>
</div>