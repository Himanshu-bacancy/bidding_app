 <!-- TABLE: LATEST ORDERS -->
<div class="card-header border-transparent">
  <h3 class="card-title">
    <span class="badge badge-warning" style="height: 30px; padding: 10px; font-size: 14px; ">

      <?php echo get_msg('total_label'); ?>
      <?php echo get_msg('divider_label'); ?>
      <?php echo $total_count; ?>
      <?php echo get_msg('items_label'); ?>

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
<div class="card-body table-responsive p-0">
  
  <table class="table m-0 table-striped">
    <tr>
      <th><?php echo get_msg('no'); ?></th>
      <th><?php echo get_msg('item_name'); ?></th>
      <th><?php echo get_msg('cat_name'); ?></th>
      <th><?php echo get_msg('owner_name'); ?></th>
      <th><?php echo get_msg('item_img'); ?></th>
      <th><?php echo get_msg('added_date'); ?></th>
    </tr>
    
    <?php $count = $this->uri->segment(4) or $count = 0; ?>
    <?php if ( ! empty( $data )): ?>
      <?php foreach($data as $d): ?>
          <?php $item = get_default_photo( $d->id, 'item' ); ?>
          <?php $item_count = $this->Item->count_all_by(array("cat_id" => $d->cat_id)); ?>
          <tr>
            <td><?php echo ++$count; ?></td>
            <td><?php echo $d->title; ?></td>
            <td><?php echo $this->Category->get_one($d->cat_id)->cat_name;?></td>
            <td><?php echo $this->User->get_one($d->added_user_id)->user_name;?></td>
              <?php 

                $default_photo = get_default_photo( $d->id, 'item' );

                if($default_photo->img_path != "") {
                 ?>   

                <td><img width="128" height="128" src="<?php echo img_url( 'thumbnail/'. $default_photo->img_path ); ?>"/></td>

              <?php } else { ?>
                <td><img width="128" height="128" src="<?php echo img_url( 'thumbnail/no_image.png'); ?>"/></td>
              <?php } ?>
            <td>
              <div class="sparkbar" data-color="#00a65a" data-height="20"><?php echo $d->added_date; ?></div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
   
  </table>
</div>
  <!-- /.table-responsive -->

<?php 
    //get login user id
    $login_user = $this->ps_auth->get_user_info();
    $conds['user_id'] = $login_user->user_id;

    // get count from permission table
    $permission_data_count = $this->Permission->count_all_by($conds);

    if ($permission_data_count > 0) {
      /* for super admin and shop admin */
      //get module id
      $conds_moudle['module_name'] = "items";
      $cond_permission['module_id'] = $this->Module->get_one_by($conds_moudle)->module_id;
      $cond_permission['user_id'] = $login_user->user_id;

      $allowed_module_data = $this->Permission->get_one_by($cond_permission);

      if ($allowed_module_data->is_empty_object == 0) { ?>
        <!-- allowed this module to login user -->
        <div class="card-footer text-center">
          <a href="<?php echo site_url('admin/items'); ?>"><?php echo get_msg('view_all_label'); ?></a>
        </div>
        <!-- /.card-footer -->
      <?php } ?>
    <?php } else { ?>  
        <div class="card-footer text-center">
          <a href="<?php echo site_url('admin/items'); ?>"><?php echo get_msg('view_all_label'); ?></a>
        </div>
        <!-- /.card-footer -->
<?php } ?>  
           