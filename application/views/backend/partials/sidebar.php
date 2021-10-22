 <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <?php $be_url = $this->config->item('be_url'); ?>
    <?php $logged_in_user = $this->ps_auth->get_user_info(); ?>
    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <?php
            $conds = array( 'img_type' => 'backend-logo', 'img_parent_id' => 'be1' );
            $images = $this->Image->get_all_by( $conds )->result();
          ?>
          <img src="<?php echo img_url( $images[0]->img_path ); ?>" class="img-circle elevation-2" alt="User Image">

        </div>
        <div class="info" style="font-size: 14px; color: #fff; font-style: bold;">
            <?php echo get_msg( 'site_name' );?>
        </div>
      </div>

    
      <?php 

        $selected_menu_child_name = $this->uri->segment(2); 
        // print_r($selected_menu_child_name); die;
        
        if($selected_menu_child_name == "multipleupload") {
          $selected_menu_child_name = "multipleupload/upload";
        }

        $conds['module_name'] = $selected_menu_child_name;

        $selected_menu_group_id = $this->Module->get_one_by($conds)->group_id;
        $selected_menu_child_id = $this->Module->get_one_by($conds)->module_id;

      ?>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

          <li class="nav-item has-treeview">
            <a href="<?php echo site_url('/admin') ?>" class="nav-link">
              <i class="nav-icon fa fa-fw <?php echo "fa-tachometer"; ?>"></i>
              <p>
                <?php echo get_msg("dashboard_label"); ?>
              </p>
            </a>
          </li>


          <?php if ( !empty( $module_groups )): ?>
            <?php 
                
                $menu_open_state = "";

                foreach ( $module_groups as $group ): 

                    if($group->group_id == $selected_menu_group_id) {

                        $menu_open_state = "menu-open";

                    } else {
                        $menu_open_state = "";
                    }

                    if($selected_menu_child_name=="language_strings"){
                      if($group->group_id == '6') {

                        $menu_open_state = "menu-open";

                      } 
                    }

            ?>

          <li class="nav-item has-treeview <?php echo $menu_open_state; ?>">
            <a href="#" class="nav-link ">
              <i class="nav-icon fa fa-fw <?php echo $group->group_icon; ?>"></i>
              <p>
                <?php echo get_msg($group->group_lang_key); ?>
                <i class="right fa fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              
              <?php if (!empty( $allowed_modules )): ?>
                <?php 

                  $active_state = "";
                  foreach ( $allowed_modules as $module ): 
                    
                    if($selected_menu_child_id == $module->module_id) {
                        
                        $active_state = "active";
                    
                    } else {

                        $active_state = "";

                    }

                    if( $selected_menu_child_name=="language_strings"){
                      if($module->module_id == '33') {
                     
                        $active_state = "active";
                  
                      } 
                  
                    }

                ?>
                  <?php if ( $module->is_show_on_menu == 1 &&
                    $module->group_id == $group->group_id ): ?>
              <li class="nav-item">
                <a href="<?php echo site_url($be_url) . '/'. strtolower( $module->module_name ); ?>" class="nav-link <?php echo $active_state; ?>">
                  <i class="fa fa-caret-right"></i>
                  <p>
                    <?php 
                      $conds['status'] = 1;
                      $language = $this->Language->get_one_by($conds);
                      $language_id = $language->id;
                      // load the language
                      $conds_str['key'] = $module->module_lang_key;
                      $conds_str['language_id'] = $language_id;
                      $lang_string = $this->Language_string->get_one_by( $conds_str );
                      echo $lang_string->value;
                    ?>
                      
                  </p>
                </a>
              </li>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
              
            </ul>
          </li>
        <?php endforeach; ?>
      <?php endif; ?> 
         
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

 