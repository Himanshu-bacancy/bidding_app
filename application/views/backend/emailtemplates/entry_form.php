
<?php
	$attributes = array( 'id' => 'template-form', 'enctype' => 'multipart/form-data');
	echo form_open( '', $attributes);
?>
	
<section class="content animated fadeInRight">
	<div class="col-md-6">
		<div class="card card-info">
		    <div class="card-header">
		        <h3 class="card-title">Email Template</h3>
		    </div>
	        <!-- /.card-header -->
	        <div class="card-body">
	            <div class="row">
	             	<div class="col-md-12">
	            		<div class="form-group">
	                   		<label>
	                   			<span style="font-size: 17px; color: red;">*</span>
								Title
								<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo get_msg('title')?>">
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>

							<?php echo form_input( array(
								'name' => 'title',
								'value' => set_value( 'title', show_data( @$template->title ), false ),
								'class' => 'form-control form-control-sm',
								'placeholder' => 'Email Title',
								'id' => 'title'
							)); ?>
	              		</div>
                        
                        <div class="form-group">
                            <label>
                                <?php echo get_msg('content_label')?>
                                <a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo get_msg('key_label')?>">
                                    <span class='glyphicon glyphicon-info-sign menu-icon'>
                                </a>
                            </label>


                            <?php echo form_textarea( array(
                                'name' => 'content',
                                'value' => set_value( 'content', show_data( @$template->content ), false ),
                                'class' => 'form-control form-control-sm',
                                'placeholder' => get_msg( 'content' ),
                                'rows' => '10',
                                'id' => 'content',
                            )); ?>
                        </div>

	              		
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