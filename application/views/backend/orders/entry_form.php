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
                   			<span style="font-size: 17px; color: red;">*</span>
							<?php echo get_msg('Id')?>
						</label>

						<?php echo form_input( array(
							'name' => 'order_id',
							'value' => set_value( 'order_id', show_data( @$order->order_id ), false ),
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
                            <?php echo get_msg('Item')?>
                        </label>

                        <?php echo form_input( array(
                            'name' => 'items',
                            'value' => set_value( 'items', show_data( @$order->item_name ), false ),
                            'class' => 'form-control form-control-sm',
                            'placeholder' => get_msg( 'item' ),
                            'id' => 'items',
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
							<?php echo get_msg('Delivery Method')?>
						</label>

						<?php
                        $print_str = 'Pickup and Delivery';
                        if($order->delivery_method == DELIVERY_ONLY) {
                            $print_str = 'Delivery Only';
                        } else if($order->delivery_method == PICKUP_ONLY) {
                            $print_str = 'Pickup Only';
                        }
                        echo form_input( array(
							'name' => 'delivery_method',
							'value' => set_value( 'delivery_method', show_data( $print_str ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'Delivery Method' ),
							'id' => 'delivery_method',
							'readonly' => 'true'
						)); ?>
              		</div>
            	</div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>
                            <span style="font-size: 17px; color: red;">*</span>
                            <?php echo get_msg('Payment Method')?>
                        </label>

                        <?php echo form_input( array(
                            'name' => 'payment_method',
                            'value' => set_value( 'payment_method', show_data( @$order->payment_method ), false ),
                            'class' => 'form-control form-control-sm',
                            'placeholder' => get_msg( 'payment_method' ),
                            'id' => 'payment_method',
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
							<?php echo get_msg('Amount')?>
						</label>

						<?php echo form_input( array(
							'name' => 'total_amount',
							'value' => set_value( 'total_amount', show_data( @$order->total_amount ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'Amount' ),
							'id' => 'total_amount',
							'readonly' => 'true'
						)); ?>
              		</div>
            	</div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>
                            <span style="font-size: 17px; color: red;">*</span>
                            <?php echo get_msg('Order Status')?>
                        </label>

                        <?php
                        echo form_input( array(
                            'name' => 'status',
                            'value' => set_value( 'status', show_data( @$order->status ), false ),
                            'class' => 'form-control form-control-sm',
                            'placeholder' => get_msg( 'status' ),
                            'id' => 'status',
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
							<?php echo get_msg('Operation Type')?>
						</label>

						<?php
                        $print_str = 'Direct Buy';
                        if($order->operation_type == REQUEST_ITEM) {
                            $print_str = 'Request Item';
                        } else if($order->operation_type == SELLING) {
                            $print_str = 'Selling';
                        } else if($order->operation_type == EXCHANGE) {
                            $print_str = 'Exchange';
                        }
                        echo form_input( array(
							'name' => 'operation_type',
							'value' => set_value( 'operation_type', show_data( $print_str ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'operation_type' ),
							'id' => 'operation_type',
							'readonly' => 'true'
						)); ?>
              		</div>
            	</div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>
                            <span style="font-size: 17px; color: red;">*</span>
                            <?php echo get_msg('Delivery Status')?>
                        </label>

                        <?php echo form_input( array(
                            'name' => 'delivery_status',
                            'value' => set_value( 'delivery_status', show_data( @$order->delivery_status ), false ),
                            'class' => 'form-control form-control-sm',
                            'placeholder' => get_msg( 'delivery_status' ),
                            'id' => 'delivery_status',
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
							<?php echo get_msg('QRcode')?>
						</label>

						<?php echo form_input( array(
							'name' => 'qrcode',
							'value' => set_value( 'qrcode', show_data( @$order->qrcode ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'qrcode' ),
							'id' => 'qrcode',
							'readonly' => 'true'
						)); ?>
              		</div>
            	</div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>
                            <span style="font-size: 17px; color: red;">*</span>
                            <?php echo get_msg('Delivery Address')?>
                        </label>

                        <?php 
                        $addresses = $this->db->from('bs_addresses')->where('id', $order->address_id)->get()->row();
                        $print_Str = $addresses->address1.','.$addresses->address2.','.$addresses->city.','.$addresses->state.','.$addresses->zipcode.','.$addresses->country;
                        echo form_input( array(
                            'name' => 'address_id',
                            'value' => set_value( 'address_id', show_data( $print_Str ), false ),
                            'class' => 'form-control form-control-sm',
                            'placeholder' => get_msg( 'address_id' ),
                            'id' => 'address_id',
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
							<?php echo get_msg('Place Date')?>
						</label>

						<?php echo form_input( array(
							'name' => 'created_at',
							'value' => set_value( 'created_at', show_data( @$order->created_at ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'created_at' ),
							'id' => 'created_at',
							'readonly' => 'true'
						)); ?>
              		</div>
            	</div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>
                            <span style="font-size: 17px; color: red;">*</span>
                            <?php echo get_msg('Processed Date')?>
                        </label>

                        <?php echo form_input( array(
                            'name' => 'processed_date',
                            'value' => set_value( 'processed_date', show_data( @$order->processed_date ), false ),
                            'class' => 'form-control form-control-sm',
                            'placeholder' => get_msg( 'processed_date' ),
                            'id' => 'processed_date',
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
							<?php echo get_msg('Pickup Date')?>
						</label>

						<?php echo form_input( array(
							'name' => 'pickup_date',
							'value' => set_value( 'pickup_date', show_data( @$order->pickup_date ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'pickup_date' ),
							'id' => 'pickup_date',
							'readonly' => 'true'
						)); ?>
              		</div>
            	</div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>
                            <span style="font-size: 17px; color: red;">*</span>
                            <?php echo get_msg('QRscan Date')?>
                        </label>

                        <?php echo form_input( array(
                            'name' => 'scanqr_date',
                            'value' => set_value( 'scanqr_date', show_data( @$order->scanqr_date ), false ),
                            'class' => 'form-control form-control-sm',
                            'placeholder' => get_msg( 'scanqr_date' ),
                            'id' => 'scanqr_date',
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
							<?php echo get_msg('Rate Date')?>
						</label>

						<?php echo form_input( array(
							'name' => 'rate_date',
							'value' => set_value( 'rate_date', show_data( @$order->rate_date ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'rate_date' ),
							'id' => 'rate_date',
							'readonly' => 'true'
						)); ?>
              		</div>
            	</div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>
                            <span style="font-size: 17px; color: red;">*</span>
                            <?php echo get_msg('Complete Date')?>
                        </label>

                        <?php echo form_input( array(
                            'name' => 'completed_date',
                            'value' => set_value( 'completed_date', show_data( @$order->completed_date ), false ),
                            'class' => 'form-control form-control-sm',
                            'placeholder' => get_msg( 'completed_date' ),
                            'id' => 'completed_date',
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
							<?php echo get_msg('QR Generate Date')?>
						</label>

						<?php echo form_input( array(
							'name' => 'generate_qr_date',
							'value' => set_value( 'generate_qr_date', show_data( @$order->generate_qr_date ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'generate_qr_date' ),
							'id' => 'generate_qr_date',
							'readonly' => 'true'
						)); ?>
              		</div>
            	</div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>
                            <span style="font-size: 17px; color: red;">*</span>
                            <?php echo get_msg('Seller Charge')?>
                        </label>

                        <?php echo form_input( array(
                            'name' => 'seller_charge',
                            'value' => set_value( 'seller_charge', show_data( @$order->seller_charge ), false ),
                            'class' => 'form-control form-control-sm',
                            'placeholder' => get_msg( 'seller_charge' ),
                            'id' => 'seller_charge',
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
							<?php echo get_msg('Buyer Detail')?>
						</label>

						<?php echo form_input( array(
							'name' => 'user_id',
							'value' => set_value( 'user_id', show_data( @$order->buyer_name ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'user_id' ),
							'id' => 'user_id',
							'readonly' => 'true'
						)); ?>
                        <br>
                        <?php echo form_input( array(
                            'name' => 'user_email',
                            'value' => set_value( 'user_email', show_data( @$order->buyer_email ), false ),
                            'class' => 'form-control form-control-sm',
                            'placeholder' => get_msg( 'user_email' ),
                            'id' => 'user_email',
                            'readonly' => 'true'
                        )); ?>
                        <br>
                        <?php echo form_input( array(
                            'name' => 'user_phone',
                            'value' => set_value( 'user_phone', show_data( @$order->buyer_phone ), false ),
                            'class' => 'form-control form-control-sm',
                            'placeholder' => get_msg( 'user_phone' ),
                            'id' => 'user_phone',
                            'readonly' => 'true'
                        )); ?>
              		</div>
            	</div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>
                            <span style="font-size: 17px; color: red;">*</span>
                            <?php echo get_msg('Seller Detail')?>
                        </label>

                        <?php echo form_input( array(
                            'name' => 'selleruser_id',
                            'value' => set_value( 'selleruser_id', show_data( @$order->seller_name ), false ),
                            'class' => 'form-control form-control-sm',
                            'placeholder' => get_msg( 'selleruser_id' ),
                            'id' => 'selleruser_id',
                            'readonly' => 'true'
                        )); ?>
                        <br>
                        <?php echo form_input( array(
                            'name' => 'selleruser_email',
                            'value' => set_value( 'selleruser_email', show_data( @$order->seller_email ), false ),
                            'class' => 'form-control form-control-sm',
                            'placeholder' => get_msg( 'selleruser_email' ),
                            'id' => 'selleruser_email',
                            'readonly' => 'true'
                        )); ?>
                        <br>
                        <?php echo form_input( array(
                            'name' => 'selleruser_phone',
                            'value' => set_value( 'selleruser_phone', show_data( @$order->seller_phone ), false ),
                            'class' => 'form-control form-control-sm',
                            'placeholder' => get_msg( 'selleruser_phone' ),
                            'id' => 'selleruser_phone',
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
							<?php echo get_msg('Tracking Status')?>
						</label>

						<?php echo form_input( array(
							'name' => 'tracking_status',
							'value' => set_value( 'tracking_status', show_data( @$order->tracking_status ), false ),
							'class' => 'form-control form-control-sm',
							'placeholder' => get_msg( 'tracking_status' ),
							'id' => 'tracking_status',
							'readonly' => 'true'
						)); ?>
              		</div>
            	</div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>
                            <span style="font-size: 17px; color: red;">*</span>
                            <?php echo get_msg('Tracking URL')?>
                        </label>

                        <?php echo form_input( array(
                            'name' => 'tracking_url',
                            'value' => set_value( 'tracking_url', show_data( @$order->tracking_url ), false ),
                            'class' => 'form-control form-control-sm',
                            'placeholder' => get_msg( 'tracking_url' ),
                            'id' => 'tracking_url',
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