
<?php
	$attributes = array( 'id' => 'coupan-form', 'enctype' => 'multipart/form-data');
	echo form_open( '', $attributes);
?>
	
<section class="content animated fadeInRight">
	<div class="col-md-6">
		<div class="card card-info">
		    <div class="card-header">
		        <h3 class="card-title">Coupan Information</h3>
		    </div>
	        <!-- /.card-header -->
	        <div class="card-body">
	            <div class="row">
	             	<div class="col-md-12">
	            		<div class="form-group">
	                   		<label>
	                   			<span style="font-size: 17px; color: red;">*</span>
								Coupan Type
								<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo get_msg('coupan_type')?>">
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>

							<?php $options=array();
							$options[] = 'Direct';
							$options[] = 'Percentage';
							
							echo form_dropdown(
								'type',
								$options,
								set_value( 'type', show_data( @$coupan->type), false ),
								'class="form-control form-control-sm mr-3" id="type"'
							); ?>
	              		</div>
                        
                        <div class="form-group">
							<label>
	                   			<span style="font-size: 17px; color: red;">*</span>
								<?php echo get_msg('coupan_value')?>
								<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo get_msg('coupan_value')?>">
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>
							<?php							
							echo form_input( array(
								'name' => 'coupan_value',
								'value' => set_value( 'coupan_value', show_data( @$coupan->value ), false ),
								'class' => 'form-control form-control-sm',
								'placeholder' => get_msg( 'coupan_value' ),
								'id' => 'coupan_value'
							)); ?>
	              		</div>
                        
                        
                        <div class="form-group">
							<label>
	                   			<span style="font-size: 17px; color: red;">*</span>
								<?php echo get_msg('min_purchase_amount')?>
								<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo get_msg('min_purchase_amount')?>">
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>
							<?php							
							echo form_input( array(
								'name' => 'min_purchase_amount',
								'value' => set_value( 'min_purchase_amount', show_data( @$coupan->min_purchase_amount ), false ),
								'class' => 'form-control form-control-sm',
								'placeholder' => get_msg( 'min_purchase_amount' ),
								'id' => 'min_purchase_amount'
							)); ?>
	              		</div>
                        
                        <div class="form-group">
							<label>
	                   			<span style="font-size: 17px; color: red;">*</span>
								<?php echo get_msg('end_at')?>
								<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo get_msg('end_at')?>">
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>
							<?php 
                            $format_date = date('m/d/Y', strtotime(@$coupan->end_at));
                            echo form_input( array(
                                'name' => 'end_at',
                                'type' => 'date', 
                                'value' => set_value( 'end_at', $format_date),
                                'class' => 'form-control form-control-sm',
                                'placeholder' => get_msg('end_at'),
                                'id' => 'end_at'                
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