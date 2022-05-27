<div class='row my-3'>
	<div class='col-9'>
		<?php
        if($this->router->fetch_class() == 'registered_users' && $this->router->fetch_method() != 'wallethistory' ) {
			$attributes = array('class' => 'form-inline');
				
			echo form_open( $module_site_url . '/search', $attributes );
            
		?>
			<div class="form-group mr-3">
				
				<?php echo form_input(array(
					'name' => 'searchterm',
					'value' => set_value( 'searchterm', $searchterm ),
					'class' => 'form-control form-control-sm mr-3',
					'placeholder' => get_msg( 'btn_search' )
				)); ?>

		  	</div>

			<div class="form-group mr-3">
			  	<button type="submit" value="submit" name="submit" class="btn btn-sm btn-primary">
			  		<?php echo get_msg( 'btn_search' ); ?>
			  	</button>
		  	</div>

		  	<div class="form-group">
			  	<a href="<?php echo $module_site_url ; ?>" class="btn btn-sm btn-primary">
			  		<?php echo get_msg( 'btn_reset' ); ?>
			  	</a>
		  	</div>
		
		<?php
        
        echo form_close(); } else {
            $attributes = array('class' => 'form-inline');
			echo form_open( $module_site_url . '/wallethistory/'.$this->uri->segment(4), $attributes );
        ?>
        
        <div class="form-group mr-3">
				
				<?php 
                $options=array();
				$options[]= get_msg('select_type');
				$options['bank_deposit']= 'bank_deposit';
				$options['instantpay']= 'instantpay';
				$options['refund']= 'refund';
				$options['rate_order']= 'rate_order';
                
                echo form_dropdown(
					'type_filter',
					$options,
					set_value( 'type_filter', show_data( @$type_filter), false ),
					'class="form-control form-control-sm mr-3" id="type_filter"'
				); ?>

		  	</div>

			<div class="form-group mr-3">
			  	<button type="submit" value="submit" name="submit" class="btn btn-sm btn-primary">
			  		<?php echo get_msg( 'btn_search' ); ?>
			  	</button>
		  	</div>

		  	<div class="form-group">
			  	<a href="<?php echo $module_site_url . '/wallethistory/'.$this->uri->segment(4) ; ?>" class="btn btn-sm btn-primary">
			  		<?php echo get_msg( 'btn_reset' ); ?>
			  	</a>
		  	</div>
        <?php
        
        echo form_close(); } 
        ?>

	</div>
</div>