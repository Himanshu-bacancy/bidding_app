<?php
	$attributes = array( 'id' => 'subcategory-form', 'enctype' => 'multipart/form-data');
	echo form_open( '', $attributes);
?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<section class="content animated fadeInRight">
		<div class="card card-info">
          <div class="card-header">
            <h3 class="card-title"><?php echo get_msg('subcat_info')?></h3>
          </div>
			<form role="form">
        		<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label> <span style="font-size: 17px; color: red;">*</span>
									<?php echo get_msg('Prd_search_cat')?>
								</label>
								<?php
									$options=array();
									$options[0]=get_msg('Prd_search_cat');
									$categories = $this->Category->get_all();
										foreach($categories->result() as $cat) {
											$options[$cat->cat_id]=$cat->cat_name;
									}
									echo form_dropdown(
										'cat_id',
										$options,
										set_value( 'cat_id', show_data( @$child_subcategory->cat_id), false ),
										'class="form-control form-control-sm mr-3" id="cat_id"'
									);
								?>
							</div>
							<div class="form-group" id="subCategoryDropDown">
								<label> <span style="font-size: 17px; color: red;">*</span>
									<?php echo get_msg('Prd_search_subcat')?>
								</label>
								<?php
									$options=array();
									$options[0]=get_msg('Prd_search_subcat');
									
									if($child_subcategory->id)
									{
										$condscat['cat_id'] = $child_subcategory->cat_id;
										$sub_categories = $this->Subcategory->get_all_by($condscat);
									}
									else
									{
										$sub_categories = $this->Subcategory->get_all();
									}
									
									
										foreach($sub_categories->result() as $subcat) {
											$options[$subcat->id]=$subcat->name;
									}
									echo form_dropdown(
										'sub_cat_id',
										$options,
										set_value( 'sub_cat_id', show_data( @$child_subcategory->sub_cat_id), false ),
										'class="form-control form-control-sm mr-3" id="sub_cat_id"'
									);
								?>
							</div>
							<div class="form-group">
								<label> <span style="font-size: 17px; color: red;">*</span>
									<?php echo get_msg('child_subcat_name')?>
								</label>
								<?php echo form_input( array(
									'name' => 'name',
									'value' => set_value( 'name', show_data( @$child_subcategory->name), false ),
									'class' => 'form-control form-control-sm',
									'placeholder' => "Child SubCategory Name",
									'id' => 'name'
								)); ?>
							</div>
						</div>
						<div class="col-md-6" style="padding-left: 50px;">
							<?php if ( !isset( $child_subcategory )): ?>
							<div class="form-group">
								<label> <span style="font-size: 17px; color: red;">*</span>
									<?php echo get_msg('subcat_img')?> 
								</label>
								<br/>
								<input class="btn btn-sm w-100" type="file" name="cover" id="cover">
							</div>
							<?php else: ?>
								<label> <span style="font-size: 17px; color: red;">*</span>
									<?php echo get_msg('subcat_img')?>
								</label>								
								<div class="btn btn-sm btn-primary btn-upload pull-right" data-toggle="modal" data-target="#uploadImage">
									<?php echo get_msg('btn_replace_photo')?>
								</div>								
								<hr/>								
								<?php
									$conds = array( 'img_type' => 'childsubcategory_cover', 'img_parent_id' => $child_subcategory->id );									
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
														<a data-toggle="modal" data-target="#deletePhoto" class="delete-img" id="<?php echo $img->img_id; ?>"   
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
							<?php if ( !isset( $child_subcategory )): ?>
								<div class="form-group">
									<label> <span style="font-size: 17px; color: red;">*</span>
										<?php echo get_msg('subcat_icon')?> 
									</label>
									<br/>
									<input class="btn btn-sm w-100" type="file" name="icon" id="icon">
								</div>
							<?php else: ?>
								<label> <span style="font-size: 17px; color: red;">*</span>
									<?php echo get_msg('subcat_icon')?>
								</label> 																
								<div class="btn btn-sm btn-primary btn-upload pull-right" data-toggle="modal" data-target="#uploadIcon">
									<?php echo get_msg('btn_replace_icon')?>
								</div>								
								<hr/>								
								<?php
									$conds = array( 'img_type' => 'childsubcategory_icon', 'img_parent_id' => $child_subcategory->id );									
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
							<div class="form-group">
								<input type="checkbox" id="is_brand_filter" name="is_brand_filter" value="1" <?php echo $child_subcategory->is_brand_filter == 1 ? 'checked' : ''; ?>>
								<label for="is_brand_filter"> Brand Filter</label>
								&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="checkbox" id="is_color_filter" name="is_color_filter" value="1" <?php echo $child_subcategory->is_color_filter == 1 ? 'checked' : ''; ?>>
								<label for="is_color_filter"> Color Filter</label>
								<?php if($child_subcategory){ ?>
								<input type="hidden" name="is_size_filter" value="<?php echo $child_subcategory->is_color_filter; ?>">
								<?php } ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label><?php echo get_msg('Select_SizeGroup')?></label>
								<?php
									$options=array();
									$options[0]=get_msg('Select_SizeGroup');
									$sizegroups = $this->Sizegroups->get_all();
									$selectedSizeGroups = $this->Childsubcategory->getSelectedSizegroups($child_subcategory->id);
									foreach($sizegroups->result() as $sgroups) {
										$options[$sgroups->id]=$sgroups->name;
									}
									echo form_dropdown(
										'sizegroup_id[]',
										$options,
										set_value('sizegroup_id[]', show_data( @$selectedSizeGroups), false ),
										'class="form-control form-control-sm mr-3"  id="multiselect" multiple="multiple"'
									);
								?>
							</div>
						</div>
					</div>	
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
<?php echo form_close(); ?>
</section>