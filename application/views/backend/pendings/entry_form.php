<?php
	$attributes = array( 'id' => 'pending-form', 'enctype' => 'multipart/form-data');
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
                'value' => set_value( 'title', show_data( @$pending->title), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('itm_title_label'),
                'id' => 'title',
                'readonly' => "true"
              )); ?>

            </div>

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
                  set_value( 'cat_id', show_data( @$pending->cat_id), false ),
                  'class="form-control form-control-sm mr-3" disabled="disabled" id="cat_id"'
                );
              ?>
            </div>
           
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
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
                  set_value( 'item_type_id', show_data( @$pending->item_type_id), false ),
                  'class="form-control form-control-sm mr-3" disabled="disabled" id="item_type_id"'
                );
              ?>
            </div>

            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
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
                  set_value( 'item_location_id', show_data( @$pending->item_location_id), false ),
                  'class="form-control form-control-sm mr-3" disabled="disabled" id="item_location_id"'
                );
              ?>
            </div>

            <div class="form-group">
                <label> <span style="font-size: 17px; color: red;">*</span>
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
                    set_value( 'deal_option_id', show_data( @$pending->deal_option_id), false ),
                    'class="form-control form-control-sm mr-3" disabled="disabled" id="deal_option_id"'
                  );
                ?>
            </div>

            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('item_description_label')?>
              </label>

              <?php echo form_textarea( array(
                'name' => 'description',
                'value' => set_value( 'description', show_data( @$pending->description), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('item_description_label'),
                'id' => 'description',
                'rows' => "3",
                'readonly' => "true"
              )); ?>

            </div>

            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('prd_high_info')?>
              </label>

              <?php echo form_textarea( array(
                'name' => 'highlight_info',
                'value' => set_value( 'info', show_data( @$pending->highlight_info), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('ple_highlight_info'),
                'id' => 'info',
                'rows' => "3",
                'readonly' => "true"
              )); ?>

            </div>
            <!-- form group -->
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('price')?>
              </label>

              <?php echo form_input( array(
                'name' => 'price',
                'value' => set_value( 'price', show_data( @$pending->price), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('price'),
                'id' => 'price',
                'readonly' => "true"
              )); ?>

            </div>

            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('Prd_search_subcat')?>
              </label>

              <?php
                if(isset($pending)) {
                  $options=array();
                  $options[0]=get_msg('Prd_search_subcat');
                  $conds['cat_id'] = $pending->cat_id;
                  $sub_cat = $this->Subcategory->get_all_by($conds);
                  foreach($sub_cat->result() as $subcat) {
                    $options[$subcat->id]=$subcat->name;
                  }
                  echo form_dropdown(
                    'sub_cat_id',
                    $options,
                    set_value( 'sub_cat_id', show_data( @$pending->sub_cat_id), false ),
                    'class="form-control form-control-sm mr-3" disabled="disabled" id="sub_cat_id"'
                  );

                } else {
                  $conds['cat_id'] = $selected_cat_id;
                  $options=array();
                  $options[0]=get_msg('Prd_search_subcat');

                  echo form_dropdown(
                    'sub_cat_id',
                    $options,
                    set_value( 'sub_cat_id', show_data( @$pending->sub_cat_id), false ),
                    'class="form-control form-control-sm mr-3" disabled="disabled" id="sub_cat_id"'
                  );
                }
                
              ?>

            </div>

            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
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
                  set_value( 'item_price_type_id', show_data( @$pending->item_price_type_id), false ),
                  'class="form-control form-control-sm mr-3" disabled="disabled" id="item_price_type_id"'
                );
              ?>
            </div>

            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
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
                  set_value( 'item_currency_id', show_data( @$pending->item_currency_id), false ),
                  'class="form-control form-control-sm mr-3" disabled="disabled" id="item_currency_id"'
                );
              ?>
            </div>

            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
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
                  set_value( 'condition_of_item_id', show_data( @$pending->condition_of_item_id), false ),
                  'class="form-control form-control-sm mr-3" disabled="disabled" id="condition_of_item_id"'
                );
              ?>
            </div>

          </div>
          
          <div class="col-md-6">
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('brand_label')?>
              </label>

              <?php echo form_input( array(
                'name' => 'brand',
                'value' => set_value( 'brand', show_data( @$pending->brand), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('brand_label'),
                'id' => 'brand',
                'readonly' => 'true'
              )); ?>

            </div>

          </div>

          <div class="col-md-6">
            <div class="form-group">
              <div class="form-check">
                <label>
                
                <?php echo form_checkbox( array(
                  'name' => 'business_mode',
                  'id' => 'business_mode',
                  'value' => 'accept',
                  'checked' => set_checkbox('business_mode', 1, ( @$pending->business_mode == 1 )? true: false ),
                  'class' => 'form-check-input',
                  'onclick' => 'return false'
                )); ?>

                <?php echo get_msg( 'itm_business_mode' ); ?>
                <br><?php echo get_msg( 'itm_show_shop' ) ?>
                </label>
              </div>
            </div>

            <div class="form-group">
              <div class="form-check">
                <label>
                
                <?php echo form_checkbox( array(
                  'name' => 'is_sold_out',
                  'id' => 'is_sold_out',
                  'value' => 'accept',
                  'checked' => set_checkbox('is_sold_out', 1, ( @$pending->is_sold_out == 1 )? true: false ),
                  'class' => 'form-check-input',
                  'onclick' => 'return false'
                )); ?>

                <?php echo get_msg( 'itm_is_sold_out' ); ?>

                </label>
              </div>
            </div>
            <!-- form group -->
          </div>
          <div class="col-md-6">
             <br><br>
            <legend><?php echo get_msg('location_info_label'); ?></legend>
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('itm_address_label')?>
              </label>

              <?php echo form_textarea( array(
                'name' => 'address',
                'value' => set_value( 'address', show_data( @$pending->address), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('itm_address_label'),
                'id' => 'address',
                'rows' => "5",
                'readonly' => 'true'
              )); ?>

            </div>
          </div>
          <?php if (  @$pending->lat !='0' && @$pending->lng !='0' ):?>
          <div class="col-md-6">
            <div id="pending_map" style="width: 100%; height: 400px;"></div>
            <div class="clearfix">&nbsp;</div>
            <div class="form-group">
              <label><?php echo get_msg('itm_lat_label') ?>
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
                  'value' => set_value( 'lat', show_data( @$pending->lat ), false ),
                  'readonly' => 'true'
                ));
              ?>
            </div>
            <div class="form-group">
              <label><?php echo get_msg('itm_lng_label') ?>
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
                  'value' =>  set_value( 'lat', show_data( @$pending->lng ), false ),
                  'readonly' => 'true'
                ));
              ?>
            </div>
            <!-- form group -->
          </div>

          <?php endif ?>
            
        </div>
        <!-- row -->
        <hr>
        <div class="form-group" style="background-color: #edbbbb; padding: 20px;">
          <label>
            <strong><?php echo get_msg('select_status')?></strong>
          </label>

          <select id="item_is_published" name="item_is_published" class="form-control">
             <option value="1">Approved</option>
             <option value="2">Disable</option>
             <option value="3">Reject</option>
          </select>
        </div>
      </div>

        <!-- Grid row --> 
        <?php if ( isset( $pending )): ?>
        <div class="gallery" id="gallery" style="margin-left: 15px; margin-bottom: 15px;">
          <?php
              $conds = array( 'img_type' => 'item', 'img_parent_id' => $pending->id );
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

        <a href="<?php echo $module_site_url; ?>" class="btn btn-sm btn-primary" style="margin-top: 3px;">
          <?php echo get_msg('btn_cancel')?>
        </a>
      </div>
    </form>
  </div>
</section>