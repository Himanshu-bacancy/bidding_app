<?php
	$attributes = array( 'id' => 'history-form', 'enctype' => 'multipart/form-data');
	echo form_open( '', $attributes);
?>
	
<section class="content animated fadeInRight">
	<div class="card card-info">
	    <div class="card-header">
	        <h3 class="card-title"><?php echo get_msg('Order Info')?></h3>
	    </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="row">
            	<div class="col-md-6">
            		<div class="form-group">
                   		<label>
							<?php echo get_msg('Buyer')?>
                        </label> :- 
                        <?php 
                            $buyer = $this->db->select('user_name')->from('core_users')->where('user_id', $order['user_id'])->get()->row();
                            $buyer_url = site_url( 'admin' ).'/registered_users/edit/'.$order['user_id'];
                        ?>
                        <a href="<?php echo $buyer_url; ?>"><?php echo $buyer->user_name; ?></a>
						
              		</div>
            	</div>
            </div>
            <div class="row">
            	<div class="col-md-6">
            		<div class="form-group">
                   		<label>
                   			<span style="font-size: 17px; color: red;">*</span>
							<?php echo get_msg('Id')?>
						</label>

						<?php echo form_input( array(
							'name' => 'order_id',
							'value' => set_value( 'order_id', show_data( $order['order_id'] ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'order_id' ),
							'id' => 'order_id',
							'readonly' => 'true'
						)); ?>
              		</div>
            	</div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>
                            <span style="font-size: 17px; color: red;">*</span>
                            <?php echo get_msg('Reasons')?>
                        </label>

                        <?php echo form_input( array(
                            'name' => 'reasons',
                            'value' => set_value( 'reasons', show_data( $order['reason_name'] ), false ),
                            'class' => 'form-control form-control-sm',
                            'placeholder' => get_msg( 'reasons' ),
                            'id' => 'reasons',
                            'readonly' => 'true'
                        )); ?>
                    </div>
                </div>
            </div>
            
            <div class="row">
            	<div class="col-md-6">
            		<div class="form-group">
                   		<label>
                   			<span style="font-size: 17px; color: red;">*</span>
							<?php echo get_msg('Return Status')?>
						</label>

						<?php echo form_input( array(
							'name' => 'status',
							'value' => set_value( 'status', show_data( $order['status'] ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'status' ),
							'id' => 'status',
							'readonly' => 'true'
						)); ?>
              		</div>
            	</div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>
                            <span style="font-size: 17px; color: red;">*</span>
                            <?php echo get_msg('Created At')?>
                        </label>

                        <?php echo form_input( array(
                            'name' => 'created_at',
                            'value' => set_value( 'created_at', show_data( $order['created_at'] ), false ),
                            'class' => 'form-control form-control-sm',
                            'placeholder' => get_msg( 'created_at' ),
                            'id' => 'created_at',
                            'readonly' => 'true'
                        )); ?>
                    </div>
                </div>
            </div>
            
            <div class="row">
            	<div class="col-md-6">
                <div class="form-group">
                  <label> <span style="font-size: 17px; color: red;"></span>
                    <?php echo get_msg('item_description_label')?>
                  </label>

                  <?php echo form_textarea( array(
                    'name' => 'description',
                    'value' => set_value( 'description', show_data( $order['description']), false ),
                    'class' => 'form-control form-control-sm',
                    'placeholder' => get_msg('item_description_label'),
                    'id' => 'description',
                    'rows' => "3",
                    'readonly' => 'true'
                  )); ?>

                </div>
            </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>
                            <span style="font-size: 17px; color: red;">*</span>
                            <?php echo get_msg('Images')?>
                        </label>
                        <div class="thumbnail">
                            <div class="row">
                                <?php 
                                $return_details->images = $this->Image->get_all_by( array( 'img_parent_id' => $order['order_id'], 'img_type' => 'return_order' ))->result();
                                if(!empty($return_details->images)) {
                                    foreach ($return_details->images as $key => $value) {
                                        echo '<div class "col-3"><img src="'.$this->ps_image->upload_url.$value->img_path .'" height="75" width="75" style="margin-right:15px;margin-bottom:15px;"></div>';
                                    }
                                } else {
                                    echo '<img src="'.$this->ps_image->upload_thumbnail_url .'no_image.png" height="75" width="75">';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
            	<div class="col-md-6">
            		<div class="form-group">
                   		<label>
							<?php echo get_msg('Seller')?>
                        </label> :- 
                        <?php 
                            $seller = $this->db->select('core_users.user_id, user_name')->from('bs_order')
                                    ->join('bs_items', 'bs_order.items = bs_items.id')
                                    ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                                    ->where('order_id', $order['order_id'])->get()->row();
                            $seller_url = site_url( 'admin' ).'/registered_users/edit/'.$seller->user_id;
                        ?>
                        <a href="<?php echo $seller_url; ?>"><?php echo $seller->user_name; ?></a>
						
              		</div>
            	</div>
                <div class="col-md-6">
            		<div class="form-group">
                   		<label>
							<?php echo get_msg('Item')?>
                        </label> :- 
                        <?php 
                            $item = $this->db->select('title')->from('bs_items')
                                    ->where('id', $order['items'])->get()->row();
                            $item_url = site_url( 'admin' ).'/items/edit/'.$order['items'];
                        ?>
                        <a href="<?php echo $item_url; ?>"><?php echo $item->title; ?></a>
						
              		</div>
            	</div>
            </div>
            
            <div class="row">
            	<div class="col-md-6">
                    <div class="form-group">
                      <label> <span style="font-size: 17px; color: red;"></span>
                        <?php echo get_msg('seller_response')?>
                      </label>

                      <?php echo form_textarea( array(
                        'name' => 'description',
                        'value' => set_value( 'description', show_data( $order['seller_response']), false ),
                        'class' => 'form-control form-control-sm',
                        'placeholder' => get_msg('seller_response'),
                        'id' => 'seller_response',
                        'rows' => "3",
                        'readonly' => 'true'
                      )); ?>

                    </div>
                </div>
                
                <div class="col-md-6">
            		<div class="form-group">
                   		<label>
							<?php echo get_msg('Seller Costs')?>
                        </label>
                        <?php echo form_input( array(
                            'name' => 'amount',
                            'value' => set_value( 'amount', show_data( ($order['amount']) ?? 0 ), false ),
                            'class' => 'form-control form-control-sm',
                            'placeholder' => get_msg( 'amount' ),
                            'id' => 'amount',
                            'readonly' => 'true'
                        )); ?>
                        
              		</div>
            	</div>
            </div>
            
        </div>
    </div>
    <!-- card info -->
</section>
				
<?php echo form_close(); ?>