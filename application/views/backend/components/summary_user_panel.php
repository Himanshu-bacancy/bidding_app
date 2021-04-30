<!-- USERS LIST -->

<div class="card-header">
  <h3 class="card-title">
    <span class="badge badge-warning" style="height: 30px; padding: 10px; font-size: 14px;">

          <?php echo get_msg('total_label'); ?>
            <?php echo get_msg('divider_label'); ?>
            <?php echo $total_count; ?>
            <?php echo get_msg('users_label'); ?>
            
      </span>
  </h3>

  <div class="card-tools">

    <button type="button" class="btn btn-tool" data-widget="collapse"><i class="fa fa-minus"></i>
    </button>
    <button type="button" class="btn btn-tool" data-widget="remove"><i class="fa fa-times"></i>
    </button>
  </div>

</div>

<!-- /.card-header -->
<div class="card-body p-0" style="height: 150px;">
  <br><br>
  <ul class="users-list clearfix">
    <?php if ( ! empty( $data )): ?>
      <?php foreach($data as $d): ?>

        <li>
          <?php if($d->user_profile_photo != "") { ?>
            <img src="<?php echo img_url($d->user_profile_photo); ?>" alt="User Image" style="border-radius: 50%; width:60px; height:60px;">
            <a class="users-list-name" href="#"><?php echo $d->user_name; ?></a>
            <span class="users-list-date"><?php echo $d->user_email; ?></span>
          <?php } else{ ?>
            <img src="<?php echo img_url('thumbnail/no_image.png'); ?>" alt="User Image" style="border-radius: 50%; width:60px; height:60px;">
            <a class="users-list-name" href="#"><?php echo $d->user_name; ?></a>
            <span class="users-list-date"><?php echo $d->user_email; ?></span>
          <?php } ?>
        </li>
      <?php endforeach; ?>
    <?php endif; ?>

  </ul>

  <!-- /.users-list -->
</div>

 <br><br>
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
      $conds_moudle['module_name'] = "registered_users";
      $cond_permission['module_id'] = $this->Module->get_one_by($conds_moudle)->module_id;
      $cond_permission['user_id'] = $login_user->user_id;

      $allowed_module_data = $this->Permission->get_one_by($cond_permission);

      if ($allowed_module_data->is_empty_object == 0) { ?>
        <!-- allowed this module to login user -->
        <div class="card-footer text-center">
          <a href="<?php echo site_url('admin/registered_users'); ?>"><?php echo get_msg('view_all_label'); ?></a>
        </div>
        <!-- /.card-footer -->
      <?php } ?>
    <?php } else { ?>  
        <div class="card-footer text-center">
          <a href="<?php echo site_url('admin/registered_users'); ?>"><?php echo get_msg('view_all_label'); ?></a>
        </div>
        <!-- /.card-footer -->
<?php } ?>   
             
            