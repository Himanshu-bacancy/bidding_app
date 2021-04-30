<?php
$attributes = array('id' => 'app-form','enctype' => 'multipart/form-data');
echo form_open( '', $attributes);
?>

<section class="content animated fadeInRight">
	<div class="card card-info">
		 <div class="card-header">
	        <h3 class="card-title"><?php echo get_msg('app_setting_lable')?></h3>
	    </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="row">
              	<div class="col-md-6">
              		<div id="app_location" style="width: 100%; height: 400px;"></div>
            			<div class="clearfix">&nbsp;</div>
							
							<div class="form-group">
								<label><?php echo get_msg('app_lat_label') ?>
					              	<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo get_msg('city_lat_label')?>">
					              		<span class='glyphicon glyphicon-info-sign menu-icon'>
					              	</a>
				              	</label>

								<br>

								<?php 
									echo form_input( array(
										'type' => 'text',
										'name' => 'lat',
										'id' => 'lat',
										'class' => 'form-control',
										'placeholder' => '',
										'value' => set_value( 'lat', show_data( @$app->lat ), false ),
									));
								?>
							</div>

							<div class="form-group">
								<label><?php echo get_msg('app_lng_label') ?>
									<a href="#" class="tooltip-ps" data-toggle="tooltip" 
										title="<?php echo get_msg('city_lng_tooltips')?>">
										<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>

								<br>

								<?php 
									echo form_input( array(
										'type' => 'text',
										'name' => 'lng',
										'id' => 'lng',
										'class' => 'form-control',
										'placeholder' => '',
										'value' =>  set_value( 'lng', show_data( @$app->lng ), false ),
									));
								?>
							</div>

							<div class="form-group">
								<div class="form-check">
									<label class="form-check-label">
									
									<?php echo form_checkbox( array(
										'name' => 'is_approval_enabled',
										'id' => 'is_approval_enabled',
										'value' => 'accept',
										'checked' => set_checkbox('is_approval_enabled', 1, ( @$app->is_approval_enabled == 1 )? true: false ),
										'class' => 'form-check-input'
									));	?>

									<?php echo get_msg( 'app_is_approval_enabled' ); ?>

									</label>
								</div>
							</div>
					</div>
              	</div>
            </div>

            <div class="card-footer">
				<button type="submit" name="save" class="btn btn-sm btn-primary">
					<?php echo get_msg('btn_save')?>
				</button>

				<a href="<?php echo $module_site_url; ?>" class="btn btn-sm btn-primary">
					<?php echo get_msg('btn_cancel')?>
				</a>
			</div>
        </div>
    </div>
    
</section>
<?php echo form_close(); ?>