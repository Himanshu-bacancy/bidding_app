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
                'url' => site_url() ,
                'total_count' => $this->db->from('core_users')->where('DATE(added_date)',  date('Y-m-d'))->get()->num_rows(),
                'label' => get_msg( 'New users of the day'),
                'icon' => "fa fa-th-list",
                'color' => "bg-primary"
              );

              $this->load->view( $template_path .'/components/badge_count', $data );
            ?>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url() ,
                'total_count' => ($this->db->select_sum('total_amount')->from('bs_order')->where('status', 'succeeded')->where('DATE(created_at)',  date('Y-m-d'))->get()->row()->total_amount) ?? 0,
                'label' => get_msg( 'Value of transaction of the day'),
                'icon' => "fa fa-list",
                'color' => "bg-success"
              );

              $this->load->view( $template_path .'/components/badge_count', $data ); 
            ?>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url()  ,
                'total_count' => $this->db->from('bs_items')->where('item_type_id', REQUEST_ITEM)->where('DATE(added_date)',  date('Y-m-d'))->count_all_results(),
                'label' => get_msg( 'New request listings'),
                'icon' => "fa fa-wpforms",
                'color' => "bg-warning"
              );

              $this->load->view( $template_path .'/components/badge_count', $data ); 
            ?>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url()  ,
                'total_count' => $this->Contact->count_all_for_today(['status' => 'unread']),
                'label' => get_msg( 'Unread messages'),
                'icon' => "fa fa-comment",
                'color' => "bg-danger"
              );

              $this->load->view( $template_path .'/components/badge_count', $data ); 
            ?>
        </div>
          
        <div class="col-lg-3 col-6">
          <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url() ,
                'total_count' => $this->db->from('bs_login_logs')->where('DATE(created_at)',  date('Y-m-d'))->count_all_results(),
                'label' => get_msg( 'Total accesses of the day'),
                'icon' => "fa fa-th-list",
                'color' => "bg-primary"
              );

              $this->load->view( $template_path .'/components/badge_count', $data );
            ?>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url() ,
                'total_count' => $this->db->from('bs_order')->where('status', 'succeeded')->where('DATE(created_at)',  date('Y-m-d'))->count_all_results(),
                'label' => get_msg( 'Number of transactions'),
                'icon' => "fa fa-list",
                'color' => "bg-success"
              );

              $this->load->view( $template_path .'/components/badge_count', $data ); 
            ?>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url()  ,
                'total_count' => $this->db->from('bs_items')->where('item_type_id', SELLING)->where('DATE(added_date)',  date('Y-m-d'))->count_all_results(),
                'label' => get_msg( 'New selling listings'),
                'icon' => "fa fa-wpforms",
                'color' => "bg-warning"
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
                'color' => "bg-danger"
              );

              $this->load->view( $template_path .'/components/badge_count', $data ); 
            ?>
        </div>
          
        <div class="col-lg-3 col-6">
          <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url() ,
                'total_count' => $this->db->from('core_users')->where('status', 1)->count_all_results(),
                'label' => get_msg( 'Total active users'),
                'icon' => "fa fa-th-list",
                'color' => "bg-primary"
              );

              $this->load->view( $template_path .'/components/badge_count', $data );
            ?>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url() ,
                'total_count' => $this->Item->count_all(),
                'label' => get_msg( 'Total items listed'),
                'icon' => "fa fa-list",
                'color' => "bg-success"
              );

              $this->load->view( $template_path .'/components/badge_count', $data ); 
            ?>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url() ,
                'total_count' => $this->Reason_operation->count_all_reported_items_for_today(),
                'label' => get_msg( 'New selling/trade listings'),
                'icon' => "fa fa-wpforms",
                'color' => "bg-warning"
              );

              $this->load->view( $template_path .'/components/badge_count', $data ); 
            ?>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <?php 
              $data = array(
                'url' => site_url() ,
                'total_count' => $this->Itemreport->count_all(),
                'label' => get_msg( 'New reported items'),
                'icon' => "fa fa-comment",
                'color' => "bg-danger"
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

        <div class="col-md-6">
          <div class="card">
            <?php
              $data = array(
                'panel_title' => get_msg('Total users evolution'),
                'module_name' => 'purchasedproduct' ,
                'total_count' => $this->Itemreport->count_all(),
                'data' => $this->Itemreport->get_item_report(4)->result()
              );

              $this->load->view( $template_path .'/components/user_chart', $data ); 
            ?>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card">
            <?php
              $data = array(
                'panel_title' => get_msg('Number of transactions'),
                'module_name' => 'purchasedproduct' ,
                'total_count' => $this->Itemreport->count_all(),
                'data' => $this->Itemreport->get_item_report(4)->result()
              );

              $this->load->view( $template_path .'/components/transaction_chart', $data ); 
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