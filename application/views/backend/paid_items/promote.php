<?php
  $attributes = array( 'id' => 'paid-form', 'enctype' => 'multipart/form-data');
  echo form_open( '', $attributes);
?>
<?php 
  // load breadcrumb
  show_breadcrumb( $action_title );

  // show flash message
  flash_msg();
?>

<div class="content animated fadeInRight">
  
  <div class="col-md-9">    
    <div class="card card-info">
      <div class="card-header">
        <h3 class="card-title"><?php echo get_msg('prd_paid_info')?></h3>
      </div>

      <div class="card-body">
        <div class="col-md-8">
          <div class="form-group">
            <label>
              <?php echo get_msg('date')?>
            </label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">
                  <i class="fa fa-calendar"></i>
                </span>
              </div>
               <?php echo form_input(array(
                'name' => 'date',
                'value' => set_value( 'date' , $paiditem->date ),
                'class' => 'form-control',
                'placeholder' => '',
                'id' => 'reservation',
                'size' => '20',
                'readonly' => 'readonly'
              )); ?>

            </div>
          </div>
          
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
</div>
  
<?php echo form_close(); ?>