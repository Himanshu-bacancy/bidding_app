<?php
  $attributes = array( 'id' => 'in-app-purchases-form', 'enctype' => 'multipart/form-data');
  echo form_open( '', $attributes);
?>

<section class="content animated fadeInRight">
  <div class="col-md-6">
  <div class="card card-info">
    <div class="card-header">
      <h3 class="card-title"><?php echo get_msg('in_app_purchase_info')?></h3>
    </div>

      <div class="card-body">
        <div class="row">
          <div class="col-md-12">
            
             <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('app_purchase_product_id_label')?>
              </label>

               <?php echo form_input( array(
                'name' => 'in_app_purchase_prd_id',
                'value' => set_value( 'in_app_purchase_prd_id', show_data( @$in_app_purchase->in_app_purchase_prd_id), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('app_purchase_product_id_label'),
                'id' => 'in_app_purchase_prd_id'
                
              )); ?>

            </div>

            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('day_label')?>
              </label>

               <?php echo form_input( array(
                'name' => 'day',
                'value' => set_value( 'day', show_data( @$in_app_purchase->day), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('day_label'),
                'id' => 'day'
                
              )); ?>

            </div>
           
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('purchase_description_label')?>
              </label>

              <?php echo form_textarea( array(
                'name' => 'description',
                'value' => set_value( 'description', show_data( @$in_app_purchase->description), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('purchase_description_label'),
                'id' => 'description',
                'rows' => "4"
              
              )); ?>

            </div>

            <div class="form-group">
                <label> <span style="font-size: 17px; color: red;">*</span>
                  <?php echo get_msg('purchase_type_label')?>
                </label><br>

              <select class="form-control" name="type" id="type">
              <option value="0"><?php echo get_msg('purchase_type_label');?></option>

              <?php
              $array = array('IOS' => 'IOS', 'Android' => 'Android');
                  foreach ($array as $key=>$value) {
                    
                    if($value == $in_app_purchase->type) {
                      echo '<option value="'.$value.'" selected>'.$key.'</option>';
                    } else {
                      echo '<option value="'.$value.'">'.$key.'</option>';
                    }
                  }
              ?>
            </select>

            </div>

            <div class="form-group" style="padding-top: 30px;">
              <div class="form-check">

                <label>
                
                  <?php echo form_checkbox( array(
                    'name' => 'status',
                    'id' => 'status',
                    'value' => 'accept',
                    'checked' => set_checkbox('status', 1, ( @$in_app_purchase->status == 1 )? true: false ),
                    'class' => 'form-check-input'
                  )); ?>

                  <?php echo get_msg( 'status' ); ?>
                </label>
                
              </div>
            </div>

          </div>
          </div>
        </div>
      </form>
      
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
    <!-- card info -->
</section>
        
<?php echo form_close(); ?>

