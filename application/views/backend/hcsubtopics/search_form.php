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
        <div class="form-group" style="padding-right: 3px;">

			<?php
				$options=array();
				$options[0]=get_msg('select_topic_name');
				$topics = $this->Hctopic->get_all();
				foreach($topics->result() as $topic) {
                    $options[$topic->id] = $topic->name;
				}

				echo form_dropdown(
					'topic_id',
					$options,
					set_value( 'topic_id', show_data( @$hctopic->topic_id), false ),
					'class="form-control form-control-sm mr-3" id="topic_id"'
				);
			?>

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
			Add SubTopic
		</a>
	</div>

</div>