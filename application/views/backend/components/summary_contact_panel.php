
  <div class="card-header">
    <h3 class="card-title">
      <span class="badge badge-warning" style="height: 30px; padding: 10px; font-size: 14px;">

        <?php echo get_msg('total_label'); ?>
        <?php echo get_msg('divider_label'); ?>
        <?php echo $total_count; ?>
        <?php echo get_msg('messages_label'); ?>

      </span>
    </h3>

    <div class="card-tools">

      <button type="button" class="btn btn-tool" data-widget="collapse">
        <i class="fa fa-minus"></i>
      </button>
      <button type="button" class="btn btn-tool" data-widget="remove">
        <i class="fa fa-times"></i>
      </button>
    </div>
  </div>
  <!-- /.card-header -->
  <div class="card-body p-0" style="height: 188px;">
    <ul class="products-list product-list-in-card pl-2 pr-2">
      <?php if ( ! empty( $data )): ?>
        <?php foreach($data as $d): ?>
          <?php $wallpaper_count = $this->Contact->count_all_by(array("contact_id" => $d->contact_id)); ?>
          <li class="item">
            <div class="product-img">
              <img src="<?php echo base_url('assets/dist/img/email.png'); ?>" alt="Product Image" class="img-size-50">
            </div>
            <div class="product-info">
              <?php echo $d->contact_name; ?>
                <span class="float-right"> By : <?php echo $d->contact_email; ?></span>
              <span class="product-description">
                <?php echo $d->contact_message; ?>
              </span>
            </div>
          </li>
        <?php endforeach; ?>
      <?php endif; ?>
    </ul>
  </div>

  <!-- /.card-body -->
 <?php 
    //get login user id
    $login_user = $this->ps_auth->get_user_info();
    $conds['user_id'] = $login_user->user_id;

    // get count from permission table
    $permission_data_count = $this->Permission->count_all_by($conds);

    if ($permission_data_count > 0) {
      /* for super admin and shop admin */
      //get module id
      $conds_moudle['module_name'] = "contacts";
      $cond_permission['module_id'] = $this->Module->get_one_by($conds_moudle)->module_id;
      $cond_permission['user_id'] = $login_user->user_id;

      $allowed_module_data = $this->Permission->get_one_by($cond_permission);

      if ($allowed_module_data->is_empty_object == 0) { ?>
        <!-- allowed this module to login user -->
        <div class="card-footer text-center">
          <a href="<?php echo site_url('admin/contacts'); ?>"><?php echo get_msg('view_all_label'); ?></a>
        </div>
        <!-- /.card-footer -->
      <?php } ?>
    <?php } else { ?>  
        <div class="card-footer text-center">
          <a href="<?php echo site_url('admin/contacts'); ?>"><?php echo get_msg('view_all_label'); ?></a>
        </div>
        <!-- /.card-footer -->
<?php } ?>   
