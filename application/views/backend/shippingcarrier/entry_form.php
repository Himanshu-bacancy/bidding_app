
<?php
	$attributes = array( 'id' => 'shippingcarrier-form', 'enctype' => 'multipart/form-data');
	echo form_open( '', $attributes);
?>
	
<section class="content animated fadeInRight">
	<div class="">
		<div class="card card-info">
		    <div class="card-header">
		        <h3 class="card-title"><?php echo get_msg('shipping_carrier_info')?></h3>
		    </div>
	        <!-- /.card-header -->
	        <div class="card-body">
	            <div class="row">
	             	<div class="col-md-6">
	            		<div class="form-group">
	                   		<label>
	                   			<span style="font-size: 17px; color: red;">*</span>
								<?php echo get_msg('name')?>
								<a href="#" class="tooltip-ps" data-toggle="tooltip">
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>

							<?php echo form_input( array(
								'name' => 'name',
								'value' => set_value( 'name', show_data( @$shippingcarrier->name ), false ),
								'class' => 'form-control form-control-sm',
								'placeholder' => get_msg( 'name' ),
								'id' => 'name'
							)); ?>
	              		</div>

	              		
	            	</div>

					<div class="col-md-6">
						<div class="form-group">
							<label> <span style="font-size: 17px; color: red;">*</span>
								<?php echo get_msg('select_packagesize')?>
							</label>
							<?php
								$options=array();
								$options[0]=get_msg('select_packagesize');
								$packagesizes = $this->Packagesizes->get_all();
									foreach($packagesizes->result() as $size) {
										$options[$size->id]=$size->name;
								}
								echo form_dropdown(
									'packagesize_id',
									$options,
									set_value( 'packagesize_id', show_data( @$shippingcarrier->packagesize_id), false ),
									'class="form-control form-control-sm mr-3" id="cat_id"'
								);
							?>
						</div>
					</div>

					<div class="col-md-6">
	            		<div class="form-group">
	                   		<label>
	                   			<span style="font-size: 17px; color: red;">*</span>
								<?php echo get_msg('price')?> (<?php echo get_msg('in_dollors')?>)
								<a href="#" class="tooltip-ps" data-toggle="tooltip">
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>

							<?php echo form_input( array(
								'name' => 'price',
								'value' => set_value( 'price', show_data( @$shippingcarrier->price ), false ),
								'class' => 'form-control form-control-sm',
								'placeholder' => get_msg( 'price' ),
								'id' => 'price'
							)); ?>
	              		</div>

	              		
	            	</div>

					<div class="col-md-6">
	            		<div class="form-group">
	                   		<label>
	                   			<span style="font-size: 17px; color: red;">*</span>
								<?php echo get_msg('min_days')?>
								<a href="#" class="tooltip-ps" data-toggle="tooltip" >
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>

							<?php echo form_input( array(
								'name' => 'min_days',
								'value' => set_value( 'min_days', show_data( @$shippingcarrier->min_days ), false ),
								'class' => 'form-control form-control-sm',
								'placeholder' => get_msg( 'min_days' ),
								'id' => 'min_days'
							)); ?>
	              		</div>

	              		
	            	</div>

					<div class="col-md-6">
	            		<div class="form-group">
	                   		<label>
	                   			<span style="font-size: 17px; color: red;">*</span>
								<?php echo get_msg('max_days')?>
								<a href="#" class="tooltip-ps" data-toggle="tooltip" >
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>

							<?php echo form_input( array(
								'name' => 'max_days',
								'value' => set_value( 'max_days', show_data( @$shippingcarrier->max_days ), false ),
								'class' => 'form-control form-control-sm',
								'placeholder' => get_msg( 'max_days' ),
								'id' => 'max_days'
							)); ?>
	              		</div>

	              		
	            	</div>

					<div class="col-md-6">
						<?php if ( !isset( $shippingcarrier )): ?>
								<div class="form-group">
									<label> <span style="font-size: 17px; color: red;">*</span>
										<?php echo get_msg('shippingcarrier_icon')?> 
									</label>
									<br/>
									<input class="btn btn-sm w-100" type="file" name="icon" id="icon">
								</div>
							<?php else: ?>
								<label> <span style="font-size: 17px; color: red;">*</span>
									<?php echo get_msg('shippingcarrier_icon')?>
								</label> 																
								<div class="btn btn-sm btn-primary btn-upload pull-right" data-toggle="modal" data-target="#uploadIcon">
									<?php echo get_msg('btn_replace_icon')?>
								</div>								
								<hr/>								
								<?php
									$conds = array( 'img_type' => 'shippingcarrier_icon', 'img_parent_id' => $shippingcarrier->id );									
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