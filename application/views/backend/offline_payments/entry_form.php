<?php
  $attributes = array( 'id' => 'offlinepayment-form', 'enctype' => 'multipart/form-data');
  echo form_open( '', $attributes);
?>

<section class="content animated fadeInRight">
        
  <div class="card card-info">
    <div class="card-header">
      <h3 class="card-title"><?php echo get_msg('offline_info')?></h3>
    </div>

    <form role="form">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('offline_title_label')?>
              </label>

               <?php echo form_input( array(
                'name' => 'title',
                'value' => set_value( 'title', show_data( @$offline_payment->title), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('offline_title_label'),
                'id' => 'title'
                
              )); ?>

            </div>
           
            <div class="form-group">
              <label> <span style="font-size: 17px; color: red;">*</span>
                <?php echo get_msg('offline_description_label')?>
              </label>

              <?php echo form_textarea( array(
                'name' => 'description',
                'value' => set_value( 'description', show_data( @$offline_payment->description), false ),
                'class' => 'form-control form-control-sm',
                'placeholder' => get_msg('offline_description_label'),
                'id' => 'description',
                'rows' => "3"
              )); ?>

            </div>

             <div class="form-group" style="padding-top: 30px;">
              <div class="form-check">

                <label>
                
                  <?php echo form_checkbox( array(
                    'name' => 'status',
                    'id' => 'status',
                    'value' => 'accept',
                    'checked' => set_checkbox('status', 1, ( @$offline_payment->status == 1 )? true: false ),
                    'class' => 'form-check-input'
                  )); ?>

                  <?php echo get_msg( 'status' ); ?>
                </label>
              </div>
            </div>

            <?php if ( !isset( $offline_payment )): ?>

          <div class="form-group">
            <span style="font-size: 17px; color: red;">*</span>
            <label>
              <?php echo get_msg('offline_icon')?> 
            </label>

            <br/>

            <input class="btn btn-sm" type="file" name="icon" id="icon">
          </div>

       <?php else: ?>
          <span style="font-size: 17px; color: red;">*</span>
          <label><?php echo get_msg('offline_icon')?>
            <a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo get_msg('offline_photo_tooltips')?>">
              <span class='glyphicon glyphicon-info-sign menu-icon'>
            </a>
          </label> 
          
          <div class="btn btn-sm btn-primary btn-upload pull-right" data-toggle="modal" data-target="#uploadImage">
            <?php echo get_msg('btn_replace_photo')?>
          </div>
          
          <hr/>
          
          <?php

            $conds = array( 'img_type' => 'offline_icon', 'img_parent_id' => $offline_payment->id );
            
            //print_r($conds); die;
            $images = $this->Image->get_all_by( $conds )->result();
          ?>
            
          <?php if ( count($images) > 0 ): ?>
            
            <div class="row">

            <?php $i = 0; foreach ( $images as $img ) :?>

              <?php if ($i>0 && $i%3==0): ?>
                  
              </div><div class='row'>
              
              <?php endif; ?>
                
              <div class="col-md-4" style="height:100">

                <div class="thumbnail">

                  <img src="<?php echo $this->ps_image->upload_thumbnail_url . $img->img_path; ?>">

                  <br/>
                  
                  <p class="text-center">
                    
                    <a data-toggle="modal" data-target="#deletePhoto" class="delete-img" id="<?php echo $img->img_id; ?>"   
                      image="<?php echo $img->img_path; ?>">
                      <?php echo get_msg('remove_label'); ?>
                    </a>
                  </p>

                </div>

               </div>
   
            <?php endforeach; ?>
          </div>

          <?php endif; ?>
          
         <?php endif; ?>
         
        
      </div>
    </div>
    </div>
  </form>

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

