<section class="content animated fadeInRight">
  <!-- Content Header (Page header) -->
  <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark"> Welcome, <?php echo $this->ps_auth->get_user_info()->user_name;?>!</h1>
            <?php flash_msg(); ?>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->
</section>  

  <!-- Main content -->
 <div class="container-fluid">
    <div class="card-body">
      <div class="row"> 
        <div class="col-lg-3 col-6">
          <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url() .'/admin/registered_users',
                'total_count' => $this->db->from('core_users')->where('DATE(added_date)',  date('Y-m-d'))->get()->num_rows(),
                'label' => get_msg( 'New users of the day'),
                'icon' => "fa fa-th-list",
                'color' => "bg-primary",
                'hide_url' => 0
              );

              $this->load->view( $template_path .'/components/badge_count', $data );
            ?>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url().'/admin/orders' ,
                'total_count' => round($this->db->select_sum('total_amount')->from('bs_order')->where('status', 'succeeded')->where('DATE(created_at)',  date('Y-m-d'))->get()->row()->total_amount,2) ?? 0,
                'label' => get_msg( 'Value of transaction of the day'),
                'icon' => "fa fa-list",
                'color' => "bg-success",
                'hide_url' => 0
              );

              $this->load->view( $template_path .'/components/badge_count', $data ); 
            ?>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url() .'/admin/items' ,
                'total_count' => $this->db->from('bs_items')->where('item_type_id', REQUEST_ITEM)->where('DATE(added_date)',  date('Y-m-d'))->get()->num_rows(),
                'label' => get_msg( 'New request listings'),
                'icon' => "fa fa-wpforms",
                'color' => "bg-warning",
                'hide_url' => 0
              );

              $this->load->view( $template_path .'/components/badge_count', $data ); 
            ?>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url() .'/admin/contacts' ,
                'total_count' => $this->db->from('bs_contact')->where('status', 'unread')->where('DATE(added_date)',  date('Y-m-d'))->get()->num_rows(),
                'label' => get_msg( 'Unread messages'),
                'icon' => "fa fa-comment",
                'color' => "bg-danger",
                'hide_url' => 0
              );

              $this->load->view( $template_path .'/components/badge_count', $data ); 
            ?>
        </div>
          
        <div class="col-lg-3 col-6">
          <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url(),
                'total_count' => $this->db->from('bs_login_logs')->where('DATE(created_at)',  date('Y-m-d'))->get()->num_rows(),
                'label' => get_msg( 'Total accesses of the day'),
                'icon' => "fa fa-th-list",
                'color' => "bg-primary",
                'hide_url' => 1
              );

              $this->load->view( $template_path .'/components/badge_count', $data );
            ?>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url() .'/admin/orders' ,
                'total_count' => $this->db->from('bs_order')->where('status', 'succeeded')->where('DATE(created_at)',  date('Y-m-d'))->get()->num_rows(),
                'label' => get_msg( 'Number of transactions'),
                'icon' => "fa fa-list",
                'color' => "bg-success",
                'hide_url' => 0
              );

              $this->load->view( $template_path .'/components/badge_count', $data ); 
            ?>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url().'/admin/items'  ,
                'total_count' => $this->db->from('bs_items')->where('item_type_id', SELLING)->where('DATE(added_date)',  date('Y-m-d'))->get()->num_rows(),
                'label' => get_msg( 'New selling listings'),
                'icon' => "fa fa-wpforms",
                'color' => "bg-warning",
                'hide_url' => 0
              );

              $this->load->view( $template_path .'/components/badge_count', $data ); 
            ?>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url() ,
                'total_count' => $this->Block->count_all_for_today(),
                'label' => get_msg( 'New blocked users'),
                'icon' => "fa fa-comment",
                'color' => "bg-danger",
                'hide_url' => 1
              );

              $this->load->view( $template_path .'/components/badge_count', $data ); 
            ?>
        </div>
          
        <div class="col-lg-3 col-6">
          <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url() .'/admin/registered_users',
                'total_count' => $this->db->from('core_users')->where('status', 1)->get()->num_rows(),
                'label' => get_msg( 'Total active users'),
                'icon' => "fa fa-th-list",
                'color' => "bg-primary",
                'hide_url' => 0
              );

              $this->load->view( $template_path .'/components/badge_count', $data );
            ?>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url().'/admin/items' ,
                'total_count' => $this->Item->count_all(),
                'label' => get_msg( 'Total items listed'),
                'icon' => "fa fa-list",
                'color' => "bg-success",
                'hide_url' => 0
              );

              $this->load->view( $template_path .'/components/badge_count', $data ); 
            ?>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url().'/admin/items' ,
                'total_count' => $this->db->from('bs_items')->where('item_type_id', EXCHANGE)->where('DATE(added_date)',  date('Y-m-d'))->get()->num_rows(),
                'label' => get_msg( 'New selling/trade listings'),
                'icon' => "fa fa-wpforms",
                'color' => "bg-warning",
                'hide_url' => 0
              );

              $this->load->view( $template_path .'/components/badge_count', $data ); 
            ?>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url() ,
                'total_count' => $this->Itemreport->count_all_for_today(),
                'label' => get_msg( 'New reported items'),
                'icon' => "fa fa-comment",
                'color' => "bg-danger",
                'hide_url' => 0
              );

              $this->load->view( $template_path .'/components/badge_count', $data ); 
            ?>
        </div>

<!--        <div class="col-md-6">
          <div class="card">
            <?php 

//              $data = array(
//                'url' => site_url() . "/admin/popularitems" ,
//                'panel_title' => get_msg('popular_item'),
//                'module_name' => 'popularitems' ,
//                'total_count' => $this->Touch->count_all(),
//                'data' => $this->Touch->get_item_count(5)
//              );
//
//              $this->load->view( $template_path .'/components/item_popular_panel', $data ); 
            ?>
          </div>
        </div>-->

        <div class="col-md-12">
          <div class="card">
            <?php
                $current_year = date('Y');
                $data = array(
                    'panel_title' => get_msg('Total users evolution'),
                    'data' => $this->db->query('SELECT count(id) as record_count, MONTH(created_at) as record_month FROM `bs_login_logs` where YEAR(created_at) = '.$current_year.' group by MONTH(created_at)')->result()
                );

              $this->load->view( $template_path .'/components/user_chart', $data ); 
            ?>
          </div>
        </div>

        <div class="col-md-12">
          <div class="card">
            <?php
                $current_year = date('Y');
                $data = array(
                  'panel_title' => get_msg('Number of transactions'),
                  'data' => $this->db->query('SELECT count(id) as record_count, MONTH(created_at) as record_month FROM `bs_order` where status = "succeeded" and YEAR(created_at) = '.$current_year.' group by MONTH(created_at)')->result()
                );

              $this->load->view( $template_path .'/components/transaction_chart', $data ); 
            ?>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card">
            <?php
                $data = array(
                  'panel_title' => get_msg('Top 5 states with listing'),
                  'data' => $this->db->query('SELECT count(bs_items.id) as record_count,bs_addresses.state FROM `bs_items` join bs_addresses on bs_items.Address_id = bs_addresses.id group by bs_addresses.state order by count(bs_items.id) desc limit 5')->result()
                );

              $this->load->view( $template_path .'/components/top_state_item', $data ); 
            ?>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card">
            <?php
                $data = array(
                  'panel_title' => get_msg('Top 5 city with listing'),
                  'data' => $this->db->query('SELECT count(bs_items.id) as record_count,bs_addresses.city FROM `bs_items` join bs_addresses on bs_items.Address_id = bs_addresses.id group by bs_addresses.city order by count(bs_items.id) desc limit 5;
')->result()
                );

              $this->load->view( $template_path .'/components/top_city_item', $data ); 
            ?>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card">
            <?php
                $current_year = date('Y');
                $data = array(
                  'panel_title' => get_msg('Top 5 category with listing'),
                  'data' => $this->db->query('SELECT count(bs_items.id) as record_count,bs_items.cat_id,bs_categories.cat_name FROM `bs_items` join bs_categories on bs_items.cat_id = bs_categories.cat_id group by bs_items.cat_id order by count(bs_items.id) desc limit 5')->result()
                );

              $this->load->view( $template_path .'/components/top_category_item', $data ); 
            ?>
          </div>
        </div>

<!--        <div class="col-12">
          <div class="card">
            <?php
//              $conds['count'] = 4;
//              $conds['status'] = 1;
//
//              $data = array(
//                'panel_title' => get_msg('item_panel_title'),
//                'module_name' => 'items' ,
//                'total_count' => $this->Item->count_all_by($conds),
//                'data' => $this->Item->get_all_by($conds,4)->result()
//              );
//
//              $this->load->view( $template_path .'/components/summary_item_panel', $data ); 
            ?>
          </div>
        </div>-->

<!--        <div class="col-md-6">
          <div class="card">
           <?php

//              $conds['role_id'] = 4;
//              $data = array(
//                'panel_title' => get_msg('user_latest_members'),
//                'module_name' => 'users' ,
//                'total_count' => $this->User->count_all_by($conds),
//                'data' => $this->User->get_all_by($conds,4)->result()
//              );
//
//              $this->load->view( $template_path .'/components/summary_user_panel', $data ); 
            ?>
          </div>
        </div>-->

<!--        <div class="col-md-6">
          <div class="card">
            <?php
//              $data = array(
//                'panel_title' => get_msg('contact_message'),
//                'module_name' => 'contacts' ,
//                'total_count' => $this->Contact->count_all(),
//                'data' => $this->Contact->get_all(2)->result()
//              );
//
//              $this->load->view( $template_path .'/components/summary_contact_panel', $data ); 
            ?>
          </div>
        </div>-->
        
      </div>
    </div>
  </div>  
       
</div>