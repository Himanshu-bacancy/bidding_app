<?php
	$attributes = array( 'id' => 'item-form', 'enctype' => 'multipart/form-data');
	echo form_open( '', $attributes);
?>

<section class="content animated fadeInRight">
  			
  <div class="card card-info">
  	<div class="card-header">
    	<h3 class="card-title"><?php echo get_msg('prd_info')?></h3>
  	</div>

    <form role="form">
      <div class="card-body">
      	<div class="row">
      		<div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('itm_title_label')?>
              </label>

              <?php echo form_input( array(
                'name' => 'title',
                'value' => set_value( 'title', show_data( @$item->title), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('itm_title_label'),
                'id' => 'title'
                
              )); ?>

            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('Prd_search_cat')?>
              </label>

              <?php
                $options=array();
                $conds['status'] = 1;
                $options[0]=get_msg('Prd_search_cat');
                $categories = $this->Category->get_all_by($conds);
                foreach($categories->result() as $cat) {
                    $options[$cat->cat_id]=$cat->cat_name;
                }

                echo form_dropdown(
                  'cat_id',
                  $options,
                  set_value( 'cat_id', show_data( @$item->cat_id), false ),
                  'class="form-control form-control-sm mr-3" id="cat_id"'
                );
              ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('Prd_search_subcat')?>
              </label>
              <?php
                if(isset($item)) {
                  $options=array();
                  $options[0]=get_msg('Prd_search_subcat');
                  $conds['cat_id'] = $item->cat_id;
                  $sub_cat = $this->Subcategory->get_all_by($conds);
                  foreach($sub_cat->result() as $subcat) {
                    $options[$subcat->id]=$subcat->name;
                  }
                  echo form_dropdown(
                    'sub_cat_id',
                    $options,
                    set_value( 'sub_cat_id', show_data( @$item->sub_cat_id), false ),
                    'class="form-control form-control-sm mr-3" id="sub_cat_id"'
                  );
                } else {
                  $conds['cat_id'] = $selected_cat_id;
                  $options=array();
                  $options[0]=get_msg('Prd_search_subcat');
                  echo form_dropdown(
                    'sub_cat_id',
                    $options,
                    set_value( 'sub_cat_id', show_data( @$item->sub_cat_id), false ),
                    'class="form-control form-control-sm mr-3" id="sub_cat_id"'
                  );
                }                
              ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('Prd_search_child_subcat')?>
              </label>
              <?php
                if(isset($item)) {
                  $options=array();
                  $options[0]=get_msg('Prd_search_child_subcat');
                  $conds['sub_cat_id'] = $item->sub_cat_id;
                  $child_sub_cat = $this->Childsubcategory->get_all_by($conds);
                  foreach($child_sub_cat->result() as $childSubcat) {
                    $options[$childSubcat->id]=$childSubcat->name;
                  }
                  echo form_dropdown(
                    'childsubcat_id',
                    $options,
                    set_value( 'childsubcat_id', show_data( @$item->childsubcat_id), false ),
                    'class="form-control form-control-sm mr-3" id="childsubcat_id"'
                  );
                } else {
                  $conds['sub_cat_id'] = $selected_cat_id;
                  $options=array();
                  $options[0]=get_msg('Prd_search_child_subcat');
                  echo form_dropdown(
                    'childsubcat_id',
                    $options,
                    set_value( 'childsubcat_id', show_data( @$item->childsubcat_id), false ),
                    'class="form-control form-control-sm mr-3" id="childsubcat_id"'
                  );
                }
              ?>
            </div>
          </div>
          <div class="col-md-6"> 
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;"></span>
                <?php echo get_msg('itm_select_type')?>
              </label>

              <?php
              
                $options=array();
                $options[0]=get_msg('itm_select_type');
                $types = $this->Itemtype->get_all();
                foreach($types->result() as $typ) {
                    $options[$typ->id]=$typ->name;
                }

                echo form_dropdown(
                  'item_type_id',
                  $options,
                  set_value( 'item_type_id', show_data( @$item->item_type_id), false ),
                  'class="form-control form-control-sm mr-3" id="item_type_id"'
                );
              ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;"></span>
                <?php echo get_msg('itm_select_location')?>
              </label>

              <?php
              
                $options=array();
                $options[0]=get_msg('itm_select_location');
                $locations = $this->Itemlocation->get_all();
                foreach($locations->result() as $location) {
                    $options[$location->id]=$location->name;
                }

                echo form_dropdown(
                  'item_location_id',
                  $options,
                  set_value( 'item_location_id', show_data( @$item->item_location_id), false ),
                  'class="form-control form-control-sm mr-3" id="item_location_id"'
                );
              ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
                <label> <span style="font-size: 17px; color: red;"></span>
                  <?php echo get_msg('itm_select_deal_option')?>
                </label>

                <?php
                  $options=array();
                  $conds['status'] = 1;
                  $options[0]=get_msg('deal_option_id_label');
                  $deals = $this->Option->get_all_by($conds);
                  foreach($deals->result() as $deal) {
                      $options[$deal->id]=$deal->name;
                  }

                  echo form_dropdown(
                    'deal_option_id',
                    $options,
                    set_value( 'deal_option_id', show_data( @$item->deal_option_id), false ),
                    'class="form-control form-control-sm mr-3" id="deal_option_id"'
                  );
                ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;"></span>
                <?php echo get_msg('price')?>
              </label>

              <?php echo form_input( array(
                'name' => 'price',
                'value' => set_value( 'price', show_data( @$item->price), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('price'),
                'id' => 'price'                
              )); ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;"></span>
                <?php echo get_msg('brand_label')?>
              </label>

              <?php echo form_input( array(
                'name' => 'brand',
                'value' => set_value( 'brand', show_data( @$item->brand), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('brand_label'),
                'id' => 'brand'
                
              )); ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('Prd_search_color')?>
              </label>

              <?php
                $options=array();
                $options[0]=get_msg('Prd_search_color');
                $colors = $this->Color->get_all_by();
                foreach($colors->result() as $color) {
                    $options[$color->id] = $color->name;
                }
                echo form_dropdown(
                  'color_ids[]',
                  $options,
                  set_value( 'color_ids[]', show_data( @$item->color_id), false ),
                  'class="form-control form-control-sm mr-3" id="color_ids" multiple'
                );
              ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;"></span>
                <?php echo get_msg('Select_SizeGroup')?>
              </label>

              <?php
                $options=array();
                $conds['status'] = 1;
                $options[0]=get_msg('Select_SizeGroup');
                $sizegroups = $this->Sizegroups->get_all_by($conds);
                foreach($sizegroups->result() as $sizegroup) {
                    $options[$sizegroup->id] = $sizegroup->name;
                }

                echo form_dropdown(
                  'sizegroup_id',
                  $options,
                  set_value( 'sizegroup_id', show_data( @$item->sizegroup_id), false ),
                  'class="form-control form-control-sm mr-3" id="sizegroup_id"'
                );
              ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('Prd_search_sizegroup_option')?>
              </label>

              <?php
                if(isset($item)) {
                  $options=array();
                  $options[0]=get_msg('Prd_search_sizegroup_option');
                  $conds['sizegroup_id'] = $item->sizegroup_id;
                  $sizegroupOption = $this->Sizegroup_option->get_all_by($conds);
                  foreach($sizegroupOption->result() as $sizegroupoptions) {
                    $options[$sizegroupoptions->id]=$sizegroupoptions->title;
                  }
                  echo form_dropdown(
                    'sizegroupoption_ids[]',
                    $options,
                    set_value( 'sizegroupoption_ids[]', show_data( @$item->sizegroupoption_ids), false ),
                    'class="form-control form-control-sm mr-3" id="sizegroupoption_ids" multiple'
                  );

                } else {
                  $conds['sizegroup_id'] = $selected_sizegroup_id;
                  $options=array();
                  $options[0]=get_msg('Prd_search_sizegroup_option');

                  echo form_dropdown(
                    'sizegroupoption_ids[]',
                    $options,
                    set_value( 'sizegroupoption_ids[]', show_data( @$item->sizegroupoption_ids), false ),
                    'class="form-control form-control-sm mr-3" id="sizegroupoption_ids" multiple'
                  );
                }
              ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('Prd_delivery_method')?>
              </label>

              <?php
                $options=array();
                $options[0]=get_msg('Prd_delivery_method');
                $deliveryMethodData = $this->Deliverymethods->get_all_by();
                foreach($deliveryMethodData->result() as $deliveryMethod) {
                    $options[$deliveryMethod->id] = $deliveryMethod->name;
                }
                echo form_dropdown(
                  'delivery_method_id',
                  $options,
                  set_value('delivery_method_id', show_data( @$item->delivery_method_id), false ),
                  'class="form-control form-control-sm mr-3" id="delivery_method_id"'
                );
              ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('Prd_pay_shipping_by')?>
              </label>

              <?php
                $options=array();
                $options[0] = get_msg('Prd_pay_shipping_by');
                $options[1] = 'for Buyer';
                $options[2] = 'for Seller';
                echo form_dropdown(
                  'pay_shipping_by',
                  $options,
                  set_value('pay_shipping_by', show_data( @$item->pay_shipping_by), false ),
                  'class="form-control form-control-sm mr-3" id="pay_shipping_by"'
                );
              ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('Prd_shipping_type')?>
              </label>

              <?php
                $options=array();
                $options[0] = get_msg('Prd_shipping_type');
                $options[1] = 'for prepaid label';
                $options[2] = 'for manual delivery';
                echo form_dropdown(
                  'shipping_type',
                  $options,
                  set_value('shipping_type', show_data( @$item->shipping_type), false ),
                  'class="form-control form-control-sm mr-3" id="shipping_type"'
                );
              ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('Prd_package_size')?>
              </label>
              <?php
                $options=array();
                $options[0]=get_msg('Prd_package_size');
                $packageSizeOptions = $this->Packagesizes->get_all_by();
                foreach($packageSizeOptions->result() as $packageSize) {
                    $options[$packageSize->id] = $packageSize->name;
                }
                echo form_dropdown(
                  'package_size_id',
                  $options,
                  set_value('package_size_id', show_data( @$item->package_size_id), false ),
                  'class="form-control form-control-sm mr-3" id="package_size_id"'
                );
              ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('Prd_shippingcarrier')?>
              </label>
              <?php
                if(isset($item)) {
                  $options=array();
                  $options[0]=get_msg('Prd_shippingcarrier');
                  $conds['packagesize_id'] = $item->packagesize_id;
                  $shippingCarrierOptions = $this->Shippingcarriers->get_all_by($conds);
                  foreach($shippingCarrierOptions->result() as $shippingCarrier) {
                    $options[$shippingCarrier->id] = $shippingCarrier->name;
                  }
                  echo form_dropdown(
                    'shippingcarrier_id',
                    $options,
                    set_value( 'shippingcarrier_id', show_data( @$item->shippingcarrier_id), false ),
                    'class="form-control form-control-sm mr-3" id="shippingcarrier_id"'
                  );
                } else {
                  $options=array();
                  $options[0]=get_msg('Prd_search_subcat');
                  echo form_dropdown(
                    'shippingcarrier_id',
                    $options,
                    set_value( 'shippingcarrier_id', show_data( @$item->shippingcarrier_id), false ),
                    'class="form-control form-control-sm mr-3" id="shippingcarrier_id"'
                  );
                }                
              ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;"></span>
                <?php echo get_msg('shipping_cost_by_seller')?>
              </label>

              <?php echo form_input( array(
                'name' => 'shipping_cost_by_seller',
                'value' => set_value( 'shipping_cost_by_seller', show_data( @$item->shipping_cost_by_seller), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('shipping_cost_by_seller'),
                'id' => 'shipping_cost_by_seller'                
              )); ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;"></span>
                <?php echo get_msg('expiration_date')?>
              </label>

              <?php echo form_input( array(
                'name' => 'expiration_date',
                'type' => 'date', 
                'value' => set_value( 'expiration_date', show_data( @$item->expiration_date), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('expiration_date'),
                'id' => 'expiration_date'                
              )); ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;"></span>
                <?php echo get_msg('item_description_label')?>
              </label>

              <?php echo form_textarea( array(
                'name' => 'description',
                'value' => set_value( 'description', show_data( @$item->description), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('item_description_label'),
                'id' => 'description',
                'rows' => "3"
              )); ?>

            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;"></span>
                <?php echo get_msg('prd_high_info')?>
              </label>

              <?php echo form_textarea( array(
                'name' => 'highlight_info',
                'value' => set_value( 'info', show_data( @$item->highlight_info), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('ple_highlight_info'),
                'id' => 'info',
                'rows' => "3"
              )); ?>

            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;"></span>
                <?php echo get_msg('prd_dynamic_link_label') ." : ".$item->dynamic_link; ?>
              </label>
            </div>

            <div class="form-group">
              <label>
                <?php echo get_msg('owner_of_item') ." : ".$this->User->get_one( $item->added_user_id )->user_name; ?>
              </label>
            </div>
            <!-- form group -->
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;"></span>
                <?php echo get_msg('itm_select_price')?>
              </label>

              <?php
                $options=array();
                $conds['status'] = 1;
                $options[0]=get_msg('itm_select_price');
                $pricetypes = $this->Pricetype->get_all_by($conds);
                foreach($pricetypes->result() as $price) {
                    $options[$price->id]=$price->name;
                }

                echo form_dropdown(
                  'item_price_type_id',
                  $options,
                  set_value( 'item_price_type_id', show_data( @$item->item_price_type_id), false ),
                  'class="form-control form-control-sm mr-3" id="item_price_type_id"'
                );
              ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;"></span>
                <?php echo get_msg('itm_select_currency')?>
              </label>

              <?php
                $options=array();
                $conds['status'] = 1;
                $options[0]=get_msg('itm_select_currency');
                $currency = $this->Currency->get_all_by($conds);
                foreach($currency->result() as $curr) {
                    $options[$curr->id]=$curr->currency_short_form;
                }

                echo form_dropdown(
                  'item_currency_id',
                  $options,
                  set_value( 'item_currency_id', show_data( @$item->item_currency_id), false ),
                  'class="form-control form-control-sm mr-3" id="item_currency_id"'
                );
              ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;"></span>
                <?php echo get_msg('itm_select_condition_of_item')?>
              </label>

              <?php
                $options=array();
                $conds['status'] = 1;
                $options[0]=get_msg('condition_of_item');
                $conditions = $this->Condition->get_all_by($conds);
                foreach($conditions->result() as $cond) {
                    $options[$cond->id]=$cond->name;
                }

                echo form_dropdown(
                  'condition_of_item_id',
                  $options,
                  set_value( 'condition_of_item_id', show_data( @$item->condition_of_item_id), false ),
                  'class="form-control form-control-sm mr-3" id="condition_of_item_id"'
                );
              ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;"></span>
                <?php echo get_msg('pickup_distance')?>
              </label>

              <?php echo form_input( array(
                'name' => 'pickup_distance', 
                'value' => set_value( 'pickup_distance', show_data( @$item->pickup_distance), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('pickup_distance'),
                'id' => 'pickup_distance'                
              )); ?>
            </div>
          </div>
          <div class="col-md-6">
            <?php if ( !isset( $item )): ?>

              <div class="form-group">
                <span style="font-size: 17px; color: red;">*</span>
                <label><?php echo get_msg('item_img')?>
                  <a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo get_msg('item_img')?>">
                    <span class='glyphicon glyphicon-info-sign menu-icon'>
                  </a>
                </label>

                <br/>

                <input class="btn btn-sm w-100" type="file" name="images1">
              </div>

              <?php else: ?>
              <span style="font-size: 17px; color: red;">*</span>
              <label><?php echo get_msg('item_img')?>
                <a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo get_msg('cat_photo_tooltips')?>">
                  <span class='glyphicon glyphicon-info-sign menu-icon'>
                </a>
              </label> 
              
              <div class="btn btn-sm btn-primary btn-upload pull-right" data-toggle="modal" data-target="#uploadImage">
                <?php echo get_msg('btn_replace_photo')?>
              </div>
              
              <hr/>
            
              <?php
                $conds = array( 'img_type' => 'item', 'img_parent_id' => $item->id );
                $images = $this->Image->get_all_by( $conds )->result();
                if (empty($images[0]->img_path)) {
                  $img_path = 'no_image.png';
                } else {
                  $img_path = $images[0]->img_path;
                }
              ?>
                
              <div class="col-md-4" style="height:100">

                <div class="thumbnail">

                  <img src="<?php echo $this->ps_image->upload_thumbnail_url . $img_path; ?>">

                  <br/>
                  
                  <p class="text-center">
                    
                    <a data-toggle="modal" data-target="#deletePhoto" class="delete-img" id="<?php echo $images[0]->img_id; ?>"   
                      image="<?php echo $img->img_path; ?>">
                      <?php echo get_msg('remove_label'); ?>
                    </a>
                  </p>

                </div>

              </div>

            <?php endif; ?> 
            <!-- End Item default photo -->
          </div>
          <div class="col-md-6">
            <div class="form-group" style="padding-top: 30px;">
              <div class="form-check">

                <label>
                
                  <?php echo form_checkbox( array(
                    'name' => 'status',
                    'id' => 'status',
                    'value' => '1',
                    'checked' => set_checkbox('status', 1, ( @$item->status == 1 )? true: false ),
                    'class' => 'form-check-input'
                  )); ?>

                  <?php echo get_msg( 'status' ); ?>
                </label>
              </div>
            </div>
          </div>
          
          

          <div class="col-md-6">
            <div class="form-group">
              <div class="form-check">
                <label>
                
                <?php echo form_checkbox( array(
                  'name' => 'business_mode',
                  'id' => 'business_mode',
                  'value' => '1',
                  'checked' => set_checkbox('business_mode', 1, ( @$item->business_mode == 1 )? true: false ),
                  'class' => 'form-check-input'
                )); ?>

                <?php echo get_msg( 'itm_business_mode' ); ?><?php echo get_msg( 'itm_show_shop' ) ?>
                </label>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <div class="form-check">
                <label>
                
                <?php echo form_checkbox( array(
                  'name' => 'is_sold_out',
                  'id' => 'is_sold_out',
                  'value' => '1',
                  'checked' => set_checkbox('is_sold_out', 1, ( @$item->is_sold_out == 1 )? true: false ),
                  'class' => 'form-check-input'
                )); ?>

                <?php echo get_msg( 'itm_is_sold_out' ); ?>

                </label>
              </div>
            </div>
            <!-- form group -->
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <div class="form-check">
                <label>
                
                <?php echo form_checkbox( array(
                  'name' => 'is_confirm_with_seller',
                  'id' => 'is_confirm_with_seller',
                  'value' => '1',
                  'checked' => set_checkbox('is_confirm_with_seller', 1, ( @$item->is_confirm_with_seller == 1 )? true: false ),
                  'class' => 'form-check-input'
                )); ?>

                <?php echo get_msg( 'itm_is_confirm_with_seller' ); ?>

                </label>
              </div>
            </div>
            <!-- form group -->
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <div class="form-check">
                <label> 
                <?php echo form_checkbox( array(
                  'name' => 'is_exchange',
                  'id' => 'is_exchange',
                  'value' => 'accept',
                  'checked' => set_checkbox('is_exchange', 1, ( @$item->is_exchange == 1 )? true: false ),
                  'class' => 'form-check-input'
                )); ?>
                <?php echo get_msg( 'is_exchange' ); ?>
                </label>
              </div>
            </div>
            <!-- form group -->
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <div class="form-check">
                <label>
                <?php echo form_checkbox( array(
                  'name' => 'is_accept_similar',
                  'id' => 'is_accept_similar',
                  'value' => 'accept',
                  'checked' => set_checkbox('is_accept_similar', 1, ( @$item->is_accept_similar == 1 )? true: false ),
                  'class' => 'form-check-input'
                )); ?>
                <?php echo get_msg( 'is_accept_similar' ); ?>
                </label>
              </div>
            </div>
            <!-- form group -->
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <div class="form-check">
                <label>
                <?php echo form_checkbox( array(
                  'name' => 'is_confirm',
                  'id' => 'is_confirm',
                  'value' => 'accept',
                  'checked' => set_checkbox('is_confirm', 1, ( @$item->is_confirm == 1 )? true: false ),
                  'class' => 'form-check-input'
                )); ?>
                <?php echo get_msg( 'is_confirm' ); ?>
                </label>
              </div>
            </div>
            <!-- form group -->
          </div>
          <div class="col-md-6">
            <legend><?php echo get_msg('location_info_label'); ?></legend>
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;"></span>
                <?php echo get_msg('itm_address_label')?>
              </label>

              <?php echo form_textarea( array(
                'name' => 'address',
                'value' => set_value( 'address', show_data( @$item->address), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('itm_address_label'),
                'id' => 'address',
                'rows' => "5"
              )); ?>

            </div>
          <?php if (  @$item->lat !='0' && @$item->lng !='0' ):?>
            <div class="form-group">
              <label><span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('itm_lat_label') ?>
                <a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo get_msg('city_lat_label')?>">
                  <span class='glyphicon glyphicon-info-sign menu-icon'>
                </a>
              </label>

              <br>

              <?php 
                echo form_input( array(
                  'type' => 'text',
                  'name' => 'lat',
                  'id' => 'lat',
                  'class' => 'form-control',
                  'placeholder' => '',
                  'value' => set_value( 'lat', show_data( @$item->lat ), false ),
                ));
              ?>
            </div>
            <div class="form-group">
              <label><span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('itm_lng_label') ?>
                <a href="#" class="tooltip-ps" data-toggle="tooltip" 
                  title="<?php echo get_msg('city_lng_tooltips')?>">
                  <span class='glyphicon glyphicon-info-sign menu-icon'>
                </a>
              </label>

              <br>

              <?php 
                echo form_input( array(
                  'type' => 'text',
                  'name' => 'lng',
                  'id' => 'lng',
                  'class' => 'form-control',
                  'placeholder' => '',
                  'value' =>  set_value( 'lng', show_data( @$item->lng ), false ),
                ));
              ?>
            </div>
            <!-- form group -->
          </div>

          <?php endif ?>
          <div class="col-md-6">
            <div id="itm_location" style="width: 100%; height: 400px;"></div>
          </div>
        </div>
        <!-- row -->
      </div>

        <!-- Grid row --> 
        <?php if ( isset( $item )): ?>
        <div class="gallery" id="gallery" style="margin-left: 15px; margin-bottom: 15px;">
          <?php
              $conds = array( 'img_type' => 'item', 'img_parent_id' => $item->id );
              $images = $this->Image->get_all_by( $conds )->result();
          ?>
          <?php $i = 0; foreach ( $images as $img ) :?>
            <!-- Grid column -->
            <div class="mb-3 pics animation all 2">
              <a href="#<?php echo $i;?>"><img class="img-fluid" src="<?php echo img_url('/' . $img->img_path); ?>" alt="Card image cap"></a>
            </div>
            <!-- Grid column -->
          <?php $i++; endforeach; ?>

          <?php $i = 0; foreach ( $images as $img ) :?>
            <a href="#_1" class="lightbox trans" id="<?php echo $i?>"><img src="<?php echo img_url('/' . $img->img_path); ?>"></a>
          <?php $i++; endforeach; ?>
        </div>
        <!-- Grid row -->
        <?php endif; ?>

      <div class="card-footer">
        <button type="submit" class="btn btn-sm btn-primary" style="margin-top: 3px;">
          <?php echo get_msg('btn_save')?>
        </button>

        <button type="submit" name="gallery" id="gallery" class="btn btn-sm btn-primary" style="margin-top: 3px;">
          <?php echo get_msg('btn_save_gallery')?>
        </button>

        <button type="submit" name="promote" id="promote" class="btn btn-sm btn-primary" style="margin-top: 3px;">
          <?php echo get_msg('btn_promote')?>
        </button>

        <a href="<?php echo $module_site_url; ?>" class="btn btn-sm btn-primary" style="margin-top: 3px;">
          <?php echo get_msg('btn_cancel')?>
        </a>
      </div>
    </form>
  </div>
</section>