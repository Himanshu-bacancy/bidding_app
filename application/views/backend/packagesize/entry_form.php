
<?php
	$attributes = array( 'id' => 'packagesize-form', 'enctype' => 'multipart/form-data');
	echo form_open( '', $attributes);
?>
	
<section class="content animated fadeInRight">
	<div class="">
		<div class="card card-info">
		    <div class="card-header">
		        <h3 class="card-title"><?php echo get_msg('package_size_info')?></h3>
		    </div>
	        <!-- /.card-header -->
	        <div class="card-body">
	            <div class="row">
	             	<div class="col-md-6">
	            		<div class="form-group">
	                   		<label>
	                   			<span style="font-size: 17px; color: red;">*</span>
								<?php echo get_msg('package_name')?>
								<a href="#" class="tooltip-ps" data-toggle="tooltip">
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>

							<?php echo form_input( array(
								'name' => 'name',
								'value' => set_value( 'name', show_data( @$packagesize->name ), false ),
								'class' => 'form-control form-control-sm',
								'placeholder' => get_msg( 'package_name' ),
								'id' => 'name'
							)); ?>
	              		</div>

	              		
	            	</div>

					<div class="col-md-6">
	            		<div class="form-group">
	                   		<label>
	                   			<span style="font-size: 17px; color: red;">*</span>
								<?php echo get_msg('length')?> (<?php echo get_msg('in_meters')?>)
								<a href="#" class="tooltip-ps" data-toggle="tooltip">
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>

							<?php echo form_input( array(
								'name' => 'length',
								'value' => set_value( 'length', show_data( @$packagesize->length ), false ),
								'class' => 'form-control form-control-sm',
								'placeholder' => get_msg( 'length' ),
								'id' => 'length'
							)); ?>
	              		</div>

	              		
	            	</div>

					<div class="col-md-6">
	            		<div class="form-group">
	                   		<label>
	                   			<span style="font-size: 17px; color: red;">*</span>
								<?php echo get_msg('width')?> (<?php echo get_msg('in_meters')?>)
								<a href="#" class="tooltip-ps" data-toggle="tooltip" >
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>

							<?php echo form_input( array(
								'name' => 'width',
								'value' => set_value( 'width', show_data( @$packagesize->width ), false ),
								'class' => 'form-control form-control-sm',
								'placeholder' => get_msg( 'width' ),
								'id' => 'width'
							)); ?>
	              		</div>

	              		
	            	</div>

					<div class="col-md-6">
	            		<div class="form-group">
	                   		<label>
	                   			<span style="font-size: 17px; color: red;">*</span>
								<?php echo get_msg('height')?> (<?php echo get_msg('in_meters')?>)
								<a href="#" class="tooltip-ps" data-toggle="tooltip" >
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>

							<?php echo form_input( array(
								'name' => 'height',
								'value' => set_value( 'height', show_data( @$packagesize->height ), false ),
								'class' => 'form-control form-control-sm',
								'placeholder' => get_msg( 'height' ),
								'id' => 'height'
							)); ?>
	              		</div>

	              		
	            	</div>

					<div class="col-md-6">
	            		<div class="form-group">
	                   		<label>
	                   			<span style="font-size: 17px; color: red;">*</span>
								<?php echo get_msg('weight')?> (<?php echo get_msg('in_lbs')?>)
								<a href="#" class="tooltip-ps" data-toggle="tooltip" >
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>

							<?php echo form_input( array(
								'name' => 'weight',
								'value' => set_value( 'weight', show_data( @$packagesize->weight ), false ),
								'class' => 'form-control form-control-sm',
								'placeholder' => get_msg( 'weight' ),
								'id' => 'weight'
							)); ?>
	              		</div>

	              		
	            	</div>

					<div class="col-md-6">
						<?php if ( !isset( $packagesize )): ?>
								<div class="form-group">
									<label> <span style="font-size: 17px; color: red;">*</span>
										<?php echo get_msg('packagesize_icon')?> 
									</label>
									<br/>
									<input class="btn btn-sm w-100" type="file" name="icon" id="icon">
								</div>
							<?php else: ?>
								<label> <span style="font-size: 17px; color: red;">*</span>
									<?php echo get_msg('packagesize_icon')?>
								</label> 																
								<div class="btn btn-sm btn-primary btn-upload pull-right" data-toggle="modal" data-target="#uploadIcon">
									<?php echo get_msg('btn_replace_icon')?>
								</div>								
								<hr/>								
								<?php
									$conds = array( 'img_type' => 'packagesize_icon', 'img_parent_id' => $packagesize->id );									
									$images = $this->Image->get_all_by( $conds )->result();
								?>									
								<?php if ( count($images) > 0 ): ?>									
									<div class="row">
										<?php $i = 0; foreach ( $images as $img ) :?>
											<?php if ($i>0 && $i%3==0): ?>												
											</div><div class='row'>										
											<?php endif; ?>											
											<div class="col-md-4" style="height:100">
												<div class="thumbnail">
													<img src="<?php echo $this->ps_image->upload_thumbnail_url . $img->img_path; ?>">
													<br/>												
													<p class="text-center">													
														<a data-toggle="modal" data-target="#deleteIcon" class="delete-img" id="<?php echo $img->img_id; ?>"   
															image="<?php echo $img->img_path; ?>">
															<?php echo get_msg('remove_label'); ?>
														</a>
													</p>
												</div>
											</div>
										<?php endforeach; ?>
									</div>								
								<?php endif; ?>
							<?php endif; ?>
							<br>
					</div>

					
	            <!-- /.row -->
	        	</div>
	        <!-- /.card-body -->
	   		</div>

			<div class="card-footer">
	            <button type="submit" class="btn btn-sm btn-primary">
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