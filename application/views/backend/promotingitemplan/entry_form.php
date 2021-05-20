
<?php
	$attributes = array( 'id' => 'promotingitemplan-form', 'enctype' => 'multipart/form-data');
	echo form_open( '', $attributes);
?>
	
<section class="content animated fadeInRight">
	<div class="">
		<div class="card card-info">
		    <div class="card-header">
		        <h3 class="card-title"><?php echo get_msg('item_plan_info')?></h3>
		    </div>
	        <!-- /.card-header -->
	        <div class="card-body">
	            <div class="row">
	             	<div class="col-md-6">
	            		<div class="form-group">
	                   		<label>
	                   			<span style="font-size: 17px; color: red;">*</span>
								<?php echo get_msg('plan_name')?>
								<a href="#" class="tooltip-ps" data-toggle="tooltip">
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>

							<?php echo form_input( array(
								'name' => 'name',
								'value' => set_value( 'name', show_data( @$promotingitemplan->name ), false ),
								'class' => 'form-control form-control-sm',
								'placeholder' => get_msg( 'plan_name' ),
								'id' => 'name'
							)); ?>
	              		</div>

	              		
	            	</div>

					<div class="col-md-6">
	            		<div class="form-group">
	                   		<label>
	                   			<span style="font-size: 17px; color: red;">*</span>
								<?php echo get_msg('plan_code')?>
								<a href="#" class="tooltip-ps" data-toggle="tooltip">
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>

							<?php echo form_input( array(
								'name' => 'code',
								'value' => set_value( 'code', show_data( @$promotingitemplan->code ), false ),
								'class' => 'form-control form-control-sm',
								'placeholder' => get_msg( 'plan_code' ),
								'id' => 'code'
							)); ?>
	              		</div>

	              		
	            	</div>

					<div class="col-md-6">
	            		<div class="form-group">
	                   		<label>
	                   			<span style="font-size: 17px; color: red;">*</span>
								<?php echo get_msg('plan_price')?> (<?php echo get_msg('in_dollors')?>)
								<a href="#" class="tooltip-ps" data-toggle="tooltip" >
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>

							<?php echo form_input( array(
								'name' => 'price',
								'value' => set_value( 'price', show_data( @$promotingitemplan->price ), false ),
								'class' => 'form-control form-control-sm',
								'placeholder' => get_msg( 'plan_price' ),
								'id' => 'price'
							)); ?>
	              		</div>

	              		
	            	</div>

					<div class="col-md-6">
	            		<div class="form-group">
	                   		<label>
	                   			<span style="font-size: 17px; color: red;">*</span>
								<?php echo get_msg('no_of_days')?>
								<a href="#" class="tooltip-ps" data-toggle="tooltip" >
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
							</label>

							<?php echo form_input( array(
								'name' => 'days',
								'value' => set_value( 'no_of_days', show_data( @$promotingitemplan->days ), false ),
								'class' => 'form-control form-control-sm',
								'placeholder' => get_msg( 'no_of_days' ),
								'id' => 'days'
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