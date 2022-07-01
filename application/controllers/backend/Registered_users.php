<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Users controller for BE_USERS table
 */
class Registered_users extends BE_Controller {

	/**
	 * Constructs required variables
	 */
	function __construct() {
		parent::__construct( MODULE_CONTROL, 'REGISTERED_USERS' );
		///start allow module check 
		$conds_mod['module_name'] = $this->router->fetch_class();
		$module_id = $this->Module->get_one_by($conds_mod)->module_id;
		
		$logged_in_user = $this->ps_auth->get_user_info();

		$user_id = $logged_in_user->user_id;
		if(empty($this->User->has_permission( $module_id,$user_id )) && $logged_in_user->user_is_sys_admin!=1){
			return redirect( site_url('/admin') );
		}
		///end check
	}

	/**
	 * List down the registered users
	 */
	function index() {

		//registered users filter
        $conds['order_by'] = 1;
		$conds['order_by_field'] = "added_date";
		$conds['order_by_type'] = "desc";
		$conds = array( 'register_role_id' => 4 );
        
		// get rows count
//		$this->data['rows_count'] = $this->User->count_all_by($conds);

		// get users
//		$this->data['users'] = $this->User->get_all_by($conds, $this->pag['per_page'], $this->uri->segment( 4 ) );
		$this->data['users'] = $this->User->get_all_by($conds);

		// load index logic
		parent::index();
	}

	/**
	 * Searches for the first match in system users
	 */
	function search() {

		// breadcrumb urls
		$data['action_title'] = get_msg( 'user_search' );

		if($this->input->post('submit') != NULL ){
            
			if($this->input->post('searchterm') != "") {
				$conds['searchterm'] = $this->input->post('searchterm');
				$this->data['searchterm'] = $this->input->post('searchterm');
				$this->session->set_userdata(array("searchterm" => $this->input->post('searchterm')));
			} else {
				
				$this->session->set_userdata(array("searchterm" => NULL));
			}
            if($this->input->post('state_dd')) {
                $conds['state_dd'] = $this->input->post('state_dd');
				$this->data['search_state'] = $this->input->post('state_dd');
                $this->session->set_userdata(array("state_dd" => $this->input->post('state_dd')));
            } else {
				$this->session->set_userdata(array("state_dd" => NULL));
			}
            if($this->input->post('city_dd')) {
                $conds['city_dd'] = $this->input->post('city_dd');
				$this->data['search_city'] = $this->input->post('city_dd');
                $this->session->set_userdata(array("city_dd" => $this->input->post('city_dd')));
            } else {
				$this->session->set_userdata(array("city_dd" => NULL));
			}
            
		} else {
			//read from session value
			if($this->session->userdata('searchterm') != NULL){
				$conds['searchterm'] = $this->session->userdata('searchterm');
				$this->data['searchterm'] = $this->session->userdata('searchterm');
			}
            
            if($this->session->userdata('state_dd') != NULL){
                $conds['state_dd'] = $this->session->userdata('state_dd');
                $this->data['search_state'] = $this->session->userdata('state_dd');
            }
            if($this->session->userdata('city_dd') != NULL){
                $conds['city_dd'] = $this->session->userdata('city_dd');
                $this->data['search_city'] = $this->session->userdata('city_dd');
            }

		}
        
        $result = $this->db->select('DISTINCT(core_users.user_id), core_users.*')->from('core_users')
                ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id','left')
                ->where('role_id', 4);
        if(isset($conds['searchterm']) && !empty($conds['searchterm'])) {
            $result = $result->group_start()->like( 'user_name', $conds['searchterm'] )->or_like( 'user_email', $conds['searchterm'] )->group_end();
        }
        if($conds['state_dd']) {
            $where = 'LOWER(TRIM(bs_addresses.state)) = "'.$conds['state_dd'].'"';
            $result = $result->where($where);
        }
        if($conds['city_dd']) {
            $where = 'LOWER(TRIM(bs_addresses.city)) = "'.$conds['city_dd'].'"';
            $result = $result->where($where);
        }
        $result = $result->order_by('added_date', 'desc');
        $store_for_count = $result->get_compiled_select();
        $count = $this->db->query($store_for_count)->num_rows();
        if($this->pag['per_page']) {
            $store_for_count .= " LIMIT ".$this->pag['per_page'];
        }
        if($this->uri->segment( 4 )) {
            $store_for_count .= ", ".$this->uri->segment( 4 );
        }
        $query_result = $this->db->query($store_for_count);
//        dd($this->db->last_query());
//		$conds['register_role_id'] = 4;
//		$this->data['rows_count'] = $this->User->count_all_by( $conds );
		$this->data['rows_count'] = $count;
//		$this->data['users'] = $this->User->get_all_by( $conds, $this->pag['per_page'], $this->uri->segment( 4 ));
		$this->data['users'] = $query_result;
		
		parent::search();
	}

	/**
	 * Create the user
	 */
	function add() {

		// breadcrumb
		$this->data['action_title'] = get_msg( 'user_add' );

		// call add logic
		parent::add();
	}

	/**
	 * Update the user
	 */
	function edit( $user_id ) {

		// breadcrumb
		$this->data['action_title'] = get_msg( 'user_view' );

		// load user
		$this->data['user'] = $this->User->get_one( $user_id );

		// call update logic
		parent::edit( $user_id );
	}


	/**
	 * Saving User Info logic
	 *
	 * @param      boolean  $user_id  The user identifier
	 */
	function save( $user_id = false ) {
		// prepare user object and permission objects
		$user_data = array();

		// save username
		if ( $this->has_data( 'user_name' )) {
			$user_data['user_name'] = $this->get_data( 'user_name' );
		}

		
		if( $this->has_data( 'user_email' )) {
			$user_data['user_email'] = $this->get_data( 'user_email' );
		}
		
		if( $this->has_data( 'user_phone' )) {
			$user_data['user_phone'] = $this->get_data( 'user_phone' );
		}


		// user_address
		if ( $this->has_data( 'user_address' )) {
			$user_data['user_address'] = $this->get_data( 'user_address' );
		}

		// save city
		if( $this->has_data( 'city' )) {
			$user_data['city'] = $this->get_data( 'city' );
		}

		// save user_about_me
		if( $this->has_data( 'user_about_me' )) {
			$user_data['user_about_me'] = $this->get_data( 'user_about_me' );
		}

		// if 'show email' is checked,
		if ( $this->has_data( 'is_show_email' )) {
			$user_data['is_show_email'] = 1;
		} else {
			$user_data['is_show_email'] = 0;
		}

		// if 'show phone' is checked,
		if ( $this->has_data( 'is_show_phone' )) {
			$user_data['is_show_phone'] = 1;
		} else {
			$user_data['is_show_phone'] = 0;
		}

		// $permissions = ( $this->get_data( 'permissions' ) != false )? $this->get_data( 'permissions' ): array();

		// save data
		// print_r($user_data);die;
		if ( ! $this->User->save( $user_data, $user_id )) {
		// if there is an error in inserting user data,	

			$this->set_flash_msg( 'error', get_msg( 'err_model' ));
		} else {
		// if no eror in inserting

			if ( $user_id ) {
			// if user id is not false, show success_add message
				
				$this->set_flash_msg( 'success', get_msg( 'success_user_edit' ));
			} else {
			// if user id is false, show success_edit message

				$this->set_flash_msg( 'success', get_msg( 'success_user_add' ));
			}
		}

		redirect( $this->module_site_url());
	}

	/**
	 * Determines if valid input.
	 *
	 * @return     boolean  True if valid input, False otherwise.
	 */
	function is_valid_input( $user_id = 0 ) {

		$email_verify = $this->User->get_one( $user_id )->email_verify;
		if ($email_verify == 1) {
		
			$rule = 'required|callback_is_valid_email['. $user_id  .']';

			$this->form_validation->set_rules( 'user_email', get_msg( 'user_email' ), $rule);

			if ( $this->form_validation->run() == FALSE ) {
			// if there is an error in validating,

				return false;
			}
		}

		return true;
	}

	/**
	 * Determines if valid email.
	 *
	 * @param      <type>   $email  The user email
	 * @param      integer  $user_id     The user identifier
	 *
	 * @return     boolean  True if valid email, False otherwise.
	 */
	function is_valid_email( $email, $user_id = 0 )
	{		

		if ( strtolower( $this->User->get_one( $user_id )->user_email ) == strtolower( $email )) {
		// if the email is existing email for that user id,
			// echo "1";die;
			return true;
		} else if ( $this->User->exists( array( 'user_email' => $_REQUEST['user_email'] ))) {
		// if the email is existed in the system,
			// echo "2";die;
			$this->form_validation->set_message('is_valid_email', get_msg( 'err_dup_email' ));
			return false;
		}
		return true;
	}

	function is_valid_phone( $phone, $user_id = 0 )
	{	
		if ( $this->User->get_one( $user_id )->user_phone  ==  $phone ) {
		// if the email is existing email for that user id,
			// echo "1";die;
			
			return true;
		} elseif ( $this->User->exists( array( 'user_phone' => $_REQUEST['user_phone'] ))) {
		// if the email is existed in the system,
			// echo "2";die;
			$this->form_validation->set_message('is_valid_phone', get_msg( 'err_dup_phone' ));
			return false;
		}
			
			return true;
	}

	/**
	 * Ajax Exists
	 *
	 * @param      <type>  $user_id  The user identifier
	 */
	function ajx_exists( $user_id = null )
	{
		$user_email = $_REQUEST['user_email'];
		
		if ( $this->is_valid_email( $user_email, $user_id )) {
		// if the user email is valid,
			
			echo "true";
		} else {
		// if the user email is invalid,

			echo "false";
		}
	}

	/**
	 * Ajax Exists
	 *
	 * @param      <type>  $user_id  The user identifier
	 */
	function ajx_exists_phone( $user_id = null )
	{
		$user_phone = $_REQUEST['user_phone'];
		
		if ( $this->is_valid_phone( $user_phone, $user_id )) {
		// if the user email is valid,
			
			echo "true";
		} else {
		// if the user email is invalid,

			echo "false";
		}
	}

	/**
	 * Ban the user
	 *
	 * @param      integer  $user_id  The user identifier
	 */
	function ban( $user_id = 0 )
	{
		$this->check_access( BAN );
		
		$data = array( 'is_banned' => 1 );
			
		if ( $this->User->save( $data, $user_id )) {
			$conds['added_user_id'] = $user_id;
			$items = $this->Item->get_all_by($conds);
			foreach ($items->result() as $itm) {
				$item_id = $itm->id;
				$item_data['status'] = 0;
				$this->Item->save($item_data,$item_id);
			}
			echo 'true';
		} else {
			echo 'false';
		}
	}
	
	/**
	 * Unban the user
	 *
	 * @param      integer  $user_id  The user identifier
	 */
	function unban( $user_id = 0 )
	{
		$this->check_access( BAN );
		
		$data = array( 'is_banned' => 0 );
			
		if ( $this->User->save( $data, $user_id )) {
			$conds['added_user_id'] = $user_id;
			$items = $this->Item->get_all_by($conds);
			foreach ($items->result() as $itm) {
				$item_id = $itm->id;
				$item_data['status'] = 1;
				$this->Item->save($item_data,$item_id);
			}
			echo 'true';
		} else {
			echo 'false';
		}
	}

	/**
	 * Delete the record
	 * 1) delete category
	 * 2) delete image from folder and table
	 * 3) check transactions
	 */
	function delete( $user_id ) {

		// start the transaction
		$this->db->trans_start();

		// check access
		$this->check_access( DEL );
		
		// delete categories and images
		if ( !$this->ps_delete->delete_user( $user_id )) {

			// set error message
			$this->set_flash_msg( 'error', get_msg( 'err_model' ));

			// rollback
			$this->trans_rollback();

			// redirect to list view
			redirect( $this->module_site_url());
		}
			
		/**
		 * Check Transcation Status
		 */
		if ( !$this->check_trans()) {

			$this->set_flash_msg( 'error', get_msg( 'err_model' ));	
		} else {
        	
			$this->set_flash_msg( 'success', get_msg( 'success_user_delete' ));
		}
		
		redirect( $this->module_site_url());
	}

	/**
	 * Delete all the news under category
	 *
	 * @param      integer  $category_id  The category identifier
	 */
	function delete_all( $user_id = 0 )
	{
		// start the transaction
		$this->db->trans_start();

		// check access
		$this->check_access( DEL );
		
		// delete categories and images

		/** Note: enable trigger will delete news under category and all news related data */
		if ( !$this->ps_delete->delete_user( $user_id )) {
		// if error in deleting category,

			// set error message
			$this->set_flash_msg( 'error', get_msg( 'err_model' ));

			// rollback
			$this->trans_rollback();

			// redirect to list view
			redirect( $this->module_site_url());
		}
			
		/**
		 * Check Transcation Status
		 */
		if ( !$this->check_trans()) {

			$this->set_flash_msg( 'error', get_msg( 'err_model' ));	
		} else {
        	
			$this->set_flash_msg( 'success', get_msg( 'success_user_delete' ));
		}
		
		redirect( $this->module_site_url());
	}
    
    public function wallethistory($id) {
        // breadcrumb urls
		$this->data['action_title'] = get_msg( 'user_wallet_history' );
        // load order
        //$order = $this->db->query("SELECT * FROM `bs_order` WHERE id = '".$id."'")->result();
        $wallet_details = $this->db->select('*')
                        ->from('bs_wallet')
                        ->where('user_id', $id);
        
        if($this->has_data( 'type_filter' ) ) {
            $wallet_details = $wallet_details->where('type', $this->get_data( 'type_filter' ));
            $this->data['type_filter'] = $this->get_data( 'type_filter' );
        }
        $wallet_details = $wallet_details->get()->result();
//        echo $this->db->last_query();die();
//		$this->data['item'] = $this->Item->get_one( $item_id );
		$this->data['wallet_history'] = $wallet_details;
        
        $this->load_detail( $this->data );
    }
    
    public function sendnoti() {
        $uid = implode(',',json_decode($this->input->post('userids')));  
        $this->load_template( 'registered_users/send_noti', ['action_title' => 'send notifcation','ids' => $uid]);
    }
    public function notisubmit() {
        
        $tokens = $this->db->select('device_token')->from('core_users')->where_in('user_id',[$this->input->post('userids')])->get()->result_array();
        $tokens = array_filter(array_column($tokens, 'device_token'));
        send_push( $tokens, ["message" => $this->input->post('description'), "flag" => "common",'title' =>$this->input->post('title')] );
        
        $this->set_flash_msg( 'success', get_msg( 'Notification send successfully' ));
        
        redirect( $this->module_site_url());
    }
    
    public function export() {
        $file_name = 'users_'.date('Ymd').'.csv'; 
        header("Content-Description: File Transfer"); 
        header("Content-Disposition: attachment; filename=$file_name"); 
        header("Content-Type: application/csv;");
        
        $file = fopen('php://output', 'w');
        
        $user_data = $this->db->select('core_users.user_id,user_name,user_email,user_phone,bs_addresses.city,bs_addresses.state,core_users.status')->from('core_users')
                ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id','left')
                ->get();
        
        $header = array("No","Signup date","User Name", "User email", "User phone", "State", "City", "Status","Rating", "Followers", "Following", "Active requests", "Active selling", "Active trade", "Total sales", "Total purchases", "Total trades", "Sales amount", "Purchases amount", "Trades amount", "Puchased canceled", "Sales canceled", "Trades canceled", "Returns Received", "Returns sent"); 
        fputcsv($file, $header);
        foreach ($user_data->result_array() as $key => $value)
        { 
            $row['no'] = $key+1;
            $row['date'] = $value['added_date'];
            $row['name'] = $value['user_name'];
            $row['email'] = $value['user_email'];
            $row['phone'] = $value['user_phone'];
            $row['state'] = $value['state'];
            $row['city'] = $value['city'];
            $row['status'] = ($value['status'] == 1) ? 'Active' : 'Inactive';
            $total_rating_count = $this->Rate->count_all_by(['to_user_id' => $value['user_id']]);
            $sum_rating_value = $this->Rate->sum_all_by(['to_user_id' => $value['user_id']])->result()[0]->rating;
            if($total_rating_count > 0) {
                $row['rating'] = number_format((float) ($sum_rating_value  / $total_rating_count), 1, '.', '');
            } else {
                $row['rating'] = 0;
            }
            $row['followers'] = $this->db->from('bs_follows')->where('user_id', $value['user_id'])->get()->num_rows();
            $row['following'] = $this->db->from('bs_follows')->where('followed_user_id', $value['user_id'])->get()->num_rows();
            $row['a_request'] = $this->db->from('bs_order')
                    ->where('operation_type', REQUEST_ITEM)
                    ->where('bs_order.status', 'succeeded')
                    ->where('bs_order.user_id', $value['user_id'])
                    ->where('completed_date is NULL')
                    ->get()->num_rows();
            
            $row['a_selling'] = $this->db->select('bs_order.id')->from('bs_order')
                    ->join('bs_items', 'bs_order.items = bs_items.id')
                    ->where('bs_order.operation_type != '.EXCHANGE)
                    ->where('bs_items.added_user_id', $value['user_id'])
                    ->where('bs_order.completed_date is NULL')->get()->num_rows();
            
            $row['a_trade'] = $this->db->select('bs_order.id')->from('bs_order')
                    ->join('bs_items', 'bs_order.items = bs_items.id')
                    ->where('bs_order.operation_type',EXCHANGE)
                    ->group_start()
                        ->where('bs_order.user_id', $value['user_id'])
                        ->or_where('bs_items.added_user_id', $value['user_id'])
                    ->group_end()
                    ->where('bs_order.completed_date is NULL')->get()->num_rows();
            $row['t_request'] = $this->db->from('bs_order')
                    ->where('operation_type', REQUEST_ITEM)
                    ->where('bs_order.status', 'succeeded')
                    ->where('bs_order.user_id', $value['user_id'])
                    ->get()->num_rows();
            
            $row['t_selling'] = $this->db->select('bs_order.id')->from('bs_order')
                    ->join('bs_items', 'bs_order.items = bs_items.id')
                    ->where('bs_order.operation_type != '.EXCHANGE)
                    ->where('bs_items.added_user_id', $value['user_id'])
                    ->get()->num_rows();
            
            $row['t_trade'] = $this->db->select('bs_order.id')->from('bs_order')
                    ->join('bs_items', 'bs_order.items = bs_items.id')
                    ->where('bs_order.operation_type',EXCHANGE)
                    ->group_start()
                        ->where('bs_order.user_id', $value['user_id'])
                        ->or_where('bs_items.added_user_id', $value['user_id'])
                    ->group_end()
                    ->get()->num_rows();
            
            $row['amt_selling'] = round($this->db->select_sum('total_amount')->from('bs_order')
                    ->join('bs_items', 'bs_order.items = bs_items.id')
                    ->where('bs_order.operation_type != '.EXCHANGE)
                    ->where('bs_items.added_user_id', $value['user_id'])
                    ->where('bs_order.completed_date is NOT NULL')->get()->row()->total_amount,2);
            
            $row['amt_request'] = round($this->db->select_sum('total_amount')->from('bs_order')
                    ->join('bs_items', 'bs_order.items = bs_items.id')
                    ->where('bs_order.operation_type', REQUEST_ITEM)
                    ->where('bs_order.user_id', $value['user_id'])
                    ->where('completed_date is NOT NULL')
                    ->get()->row()->total_amount,2);
            
            $row['amt_trade'] = round($this->db->select_sum('bs_order.total_amount')->from('bs_order')
                    ->join('bs_items', 'bs_order.items = bs_items.id')
                    ->where('bs_order.operation_type',EXCHANGE)
                    ->group_start()
                        ->where('bs_order.user_id', $value['user_id'])
                        ->or_where('bs_items.added_user_id', $value['user_id'])
                    ->group_end()
                    ->where('bs_order.completed_date is NOT NULL')->get()->row()->total_amount,2);
            
            $row['p_cancel'] = $this->db->select('id')->from('bs_chat_history')
                    ->where('operation_type', SELLING)
                    ->group_start()
                        ->where('is_cancel = 1')
                        ->or_where('is_expired = 1')
                    ->group_end()
                    ->where('buyer_user_id', $value['user_id'])
                    ->get()->num_rows();
            $row['s_cancel'] = $this->db->select('id')->from('bs_chat_history')
                    ->where('operation_type', SELLING)
                    ->group_start()
                        ->where('is_cancel = 1')
                        ->or_where('is_expired = 1')
                    ->group_end()
                    ->where('seller_user_id', $value['user_id'])
                    ->get()->num_rows();
            $row['t_cancel'] = $this->db->select('id')->from('bs_chat_history')
                    ->where('operation_type', EXCHANGE)
                    ->group_start()
                        ->where('is_cancel = 1')
                        ->or_where('is_expired = 1')
                    ->group_end()
                    ->group_start()
                        ->where('buyer_user_id', $value['user_id'])
                        ->or_where('seller_user_id', $value['user_id'])
                    ->group_end()
                    ->get()->num_rows();
            $row['s_return'] = $this->db->select('bs_return_order.id')->from('bs_return_order')
                    ->join('bs_order', 'bs_return_order.order_id = bs_order.order_id')
                    ->join('bs_items', 'bs_order.items = bs_items.id')
                    ->where('bs_items.added_user_id', $value['user_id'])
                    ->where('bs_return_order.status = "accept"')
                    ->get()->num_rows();
            $row['b_return'] = $this->db->select('bs_return_order.id')->from('bs_return_order')
                    ->join('bs_order', 'bs_return_order.order_id = bs_order.order_id')
                    ->where('user_id', $value['user_id'])
                    ->where('bs_return_order.status = "accept"')
                    ->get()->num_rows();
            
            fputcsv($file, $row); 
        }
        fclose($file); 
        exit; 
    }
}