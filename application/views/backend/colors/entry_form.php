
<?php
	$attributes = array( 'id' => 'color-form', 'enctype' => 'multipart/form-data');
	echo form_open( '', $attributes);
?>
	
<section class="content animated fadeInRight">
	<div class="card card-info">
	    <div class="card-header">
	        <h3 class="card-title"><?php echo get_msg('color_info')?></h3>
	    </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="row">
             	<div class="col-md-6">
                		<div class="form-group">
	                   		<label>
								<?php echo get_msg('color_name')?>
								<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo get_msg('color_name')?>">
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>

							<?php echo form_input( array(
								'name' => 'name',
								'value' => set_value( 'name', show_data( @$col->name ), false ),
								'class' => 'form-control form-control-sm',
								'placeholder' => get_msg( 'name' ),
								'id' => 'name'
							)); ?>
                  		</div>
                  	</div>

                  	<div class="col-md-5">
                  		<div class="form-group">
		                    <label><?php echo get_msg('color_code')?>
								<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo get_msg('color_code')?>">
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>
							<div class="input-group my-colorpicker2">
								<?php echo form_input(array(
									'name' => 'code',
									'value' => set_value( 'code', show_data( @$col->code ), false ),
									'class' => 'form-control form-control-sm',
									'placeholder' => "",
									'id' => 'code'
								)); ?>
								 <div class="input-group-addon"><i></i></div>
							</div>
                  		</div>
                  	</div>
                  		
            </div>
            <!-- /.row -->
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
    <!-- card info -->
</section>
				

	
	

<?php echo form_close(); ?>