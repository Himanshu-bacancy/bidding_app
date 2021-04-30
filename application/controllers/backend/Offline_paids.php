<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Offline_paids Controller
 */
class Offline_paids extends BE_Controller {

	/**
	 * Construt required variables
	 */
	function __construct() {

		parent::__construct( MODULE_CONTROL, 'OFFLINE_PAIDS' );
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
		$conds['payment_type'] = 1;
		// get rows count
		$this->data['rows_count'] = $this->Item->count_all_by( $conds );

		// get categories
		$this->data['items'] = $this->Item->get_all_by( $conds , $this->pag['per_page'], $this->uri->segment( 4 ) );


		// load index logic
		parent::index();
	}

	/**
	 * Searches for the first match.
	 */
	function search() {

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'prd_search' );

		// condition with search term
		if($this->input->post('submit') != NULL ){

			if($this->input->post('searchterm') != "") {
				$conds['searchterm'] = $this->input->post('searchterm');
				$this->data['searchterm'] = $this->input->post('searchterm');
				$this->session->set_userdata(array("searchterm" => $this->input->post('searchterm')));
			} else {
				
				$this->session->set_userdata(array("searchterm" => NULL));
			}
			
			if($this->input->post('cat_id') != ""  || $this->input->post('cat_id') != '0') {
				$conds['cat_id'] = $this->input->post('cat_id');
				$this->data['cat_id'] = $this->input->post('cat_id');
				$this->data['selected_cat_id'] = $this->input->post('cat_id');
				$this->session->set_userdata(array("cat_id" => $this->input->post('cat_id')));
				$this->session->set_userdata(array("selected_cat_id" => $this->input->post('cat_id')));
			} else {
				$this->session->set_userdata(array("cat_id" => NULL ));
			}

			if($this->input->post('sub_cat_id') != ""  || $this->input->post('sub_cat_id') != '0') {
				$conds['sub_cat_id'] = $this->input->post('sub_cat_id');
				$this->data['sub_cat_id'] = $this->input->post('sub_cat_id');
				$this->session->set_userdata(array("sub_cat_id" => $this->input->post('sub_cat_id')));
			} else {
				$this->session->set_userdata(array("sub_cat_id" => NULL ));
			}

			if($this->input->post('item_price_type_id') != ""  || $this->input->post('item_price_type_id') != '0') {
				$conds['item_price_type_id'] = $this->input->post('item_price_type_id');
				$this->data['item_price_type_id'] = $this->input->post('item_price_type_id');
				
				$this->session->set_userdata(array("item_price_type_id" => $this->input->post('item_price_type_id')));
				
			} else {
				$this->session->set_userdata(array("item_price_type_id" => NULL ));
			}

			if($this->input->post('item_type_id') != ""  || $this->input->post('item_type_id') != '0') {
				$conds['item_type_id'] = $this->input->post('item_type_id');
				$this->data['item_type_id'] = $this->input->post('item_type_id');
				
				$this->session->set_userdata(array("item_type_id" => $this->input->post('item_type_id')));
				
			} else {
				$this->session->set_userdata(array("item_type_id" => NULL ));
			}

			if($this->input->post('item_currency_id') != ""  || $this->input->post('item_currency_id') != '0') {
				$conds['item_currency_id'] = $this->input->post('item_currency_id');
				$this->data['item_currency_id'] = $this->input->post('item_currency_id');
				
				$this->session->set_userdata(array("item_currency_id" => $this->input->post('item_currency_id')));
				
			} else {
				$this->session->set_userdata(array("item_currency_id" => NULL ));
			}

			if($this->input->post('is_paid') != "") {
				$conds['is_paid'] = $this->input->post('is_paid');
				$this->data['is_paid'] = $this->input->post('is_paid');
				$this->session->set_userdata(array("is_paid" => $this->input->post('is_paid')));
			
			} else {
				$this->session->set_userdata(array("is_paid" => NULL ));
			}



		} else {
			//read from session value
			if($this->session->userdata('searchterm') != NULL){
				$conds['searchterm'] = $this->session->userdata('searchterm');
				$this->data['searchterm'] = $this->session->userdata('searchterm');
			}

			if($this->session->userdata('cat_id') != NULL){
				$conds['cat_id'] = $this->session->userdata('cat_id');
				$this->data['cat_id'] = $this->session->userdata('cat_id');
				$this->data['selected_cat_id'] = $this->session->userdata('cat_id');
			}

			if($this->session->userdata('sub_cat_id') != NULL){
				$conds['sub_cat_id'] = $this->session->userdata('sub_cat_id');
				$this->data['sub_cat_id'] = $this->session->userdata('sub_cat_id');
				$this->data['selected_cat_id'] = $this->session->userdata('cat_id');
			}

			if($this->session->userdata('item_price_type_id') != NULL){
				$conds['item_price_type_id'] = $this->session->userdata('item_price_type_id');
				$this->data['item_price_type_id'] = $this->session->userdata('item_price_type_id');
			}

			if($this->session->userdata('item_type_id') != NULL){
				$conds['item_type_id'] = $this->session->userdata('item_type_id');
				$this->data['item_type_id'] = $this->session->userdata('item_type_id');
			}

			if($this->session->userdata('item_currency_id') != NULL){
				$conds['item_currency_id'] = $this->session->userdata('item_currency_id');
				$this->data['item_currency_id'] = $this->session->userdata('item_currency_id');
			}
		
			if($this->session->userdata('is_paid') != NULL){
				$conds['is_paid'] = $this->session->userdata('is_paid');
				$this->data['is_paid'] = $this->session->userdata('is_paid');
			}
			

		}

		// if ($conds['status'] == "Select Status") {
		// 	$conds['status'] = "1";
		// }

		$conds['payment_type'] = 1;

		if ($conds['is_paid'] == 3) {
			$conds['is_paid'] = 0 ;
		}

		// pagination
		$this->data['rows_count'] = $this->Item->count_all_by( $conds );

		// search data
		$this->data['items'] = $this->Item->get_all_by( $conds, $this->pag['per_page'], $this->uri->segment( 4 ) );

		// load add list
		parent::search();
	}

	/**
	 * Saving Logic
	 * 1) upload image
	 * 2) save category
	 * 3) save image
	 * 4) check transaction status
	 *
	 * @param      boolean  $id  The user identifier
	 */
	function save( $id = false ) {
		
		$logged_in_user = $this->ps_auth->get_user_info();
	
		// if 'is_paid' is checked,
		if ( $this->has_data( 'is_paid' )) {
			$data['is_paid'] = $this->get_data( 'is_paid' );
		}

		
		//save item
		if ( ! $this->Item->save( $data, $id )) {
		// if there is an error in inserting user data,	

			// rollback the transaction
			$this->db->trans_rollback();

			// set error message
			$this->data['error'] = get_msg( 'err_model' );
			
			return;
		}
		$item_id = $id;
		$conds['item_id'] = $item_id;
		$conds['payment_method'] = "offline";
		$paid_items = $this->Paid_item->get_all_by($conds)->result();
		// foreach($paid_items as $paid) {
					
		// 	$today = date('Y-m-d H:i:s');
		// 	if ($today >= $paid->start_date && $today <= $paid->end_date) {
			
		// 	 	$paid_id = $paid->id;
		// 	 	$paid_start_date = $paid->start_date;
		// 	 	$paid_end_date = $paid->end_date;
		// 	} elseif ($today < $paid->start_date && $today < $paid->end_date) {
		// 		$paid_id = $paid->id;
		// 		$paid_start_date =$paid->start_date;
		// 	 	$paid_end_date = $paid->end_date;

			 
		// 	}
			
		// }
		
		// $start_date=date_create($paid_start_date);
		// $end_date=date_create($paid_end_date);
		// $diff=date_diff($start_date,$end_date);
		// $day = $diff->d;

		// $dates = $this->get_data( 'date' );
		// $vardate = explode(' ',$dates,2);

		// $temp_mindate = $vardate[0];

		// $temp_startdate = new DateTime($temp_mindate);
		// $result_start_date = $temp_startdate->format('Y-m-d H:i:s');

		// $d = DateTime::createFromFormat('Y-m-d H:i:s', $result_start_date);
		// $start_timestamp = $d->getTimestamp();
		// $convert_start_date = date("Y-m-d H:i:s", substr($start_timestamp, 0, 10));
		// $result_end_date = date('Y-m-d H:i:s', strtotime($convert_start_date. ' + '.$day.' day'));
		// $d = DateTime::createFromFormat('Y-m-d H:i:s', $result_end_date);
		// $end_timestamp = $d->getTimestamp();

		$start_timestamp = $paid_items[0]->start_timestamp;
		$end_timestamp = $paid_items[0]->end_timestamp;
		$start_date = $paid_items[0]->start_date;
		$end_date = $paid_items[0]->end_date;
		$paid_id = $paid_items[0]->id;


	  	$paid_data = array(
	  		"start_date" => $start_date,
	  		"start_timestamp" => $start_timestamp,
	  		"end_date" => $end_date,
	  		"end_timestamp" => $end_timestamp
	  	);

		//save item
		if ( ! $this->Paid_item->save( $paid_data, $paid_id )) {
		// if there is an error in inserting user data,	

			// rollback the transaction
			$this->db->trans_rollback();

			// set error message
			$this->data['error'] = get_msg( 'err_model' );
			
			return;
		}

			
		/** 
		 * Check Transactions 
		 */

		// commit the transaction
		if ( ! $this->check_trans()) {
        	
			// set flash error message
			$this->set_flash_msg( 'error', get_msg( 'err_model' ));
		} else {

			if ( $id ) {
			// if user id is not false, show success_add message
				
				$this->set_flash_msg( 'success', get_msg( 'success_prd_edit' ));
			} else {
			// if user id is false, show success_edit message

				$this->set_flash_msg( 'success', get_msg( 'success_prd_add' ));
			}
		}

		
		// redirect to list view
		redirect( $this->module_site_url() );
		
	}

	/**
 	* Update the existing one
	*/
	function edit( $id ) 
	{
		
		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'offline_paid_edit' );
		// load history
		$conds['item_id'] = $id;
		$conds['payment_method'] = "offline";
		$paid_items = $this->Paid_item->get_all_by($conds)->result();
		foreach($paid_items as $paid) {
					
			$today = date('Y-m-d H:i:s');
			//print_r($today);die;
			// if ($today >= $paid->start_date && $today <= $paid->end_date) {
			 	
			//  	$start_date = $paid->start_date;
			//  	$end_date = $paid->end_date;
			 
			// } elseif ($today < $paid->end_date && $today < $paid->end_date) {
				
				$start_date = $paid->start_date;
				$end_date = $paid->end_date;
			 
			//}
			
		}
		
		$this->data['start_date'] = $start_date;
		$this->data['end_date'] = $end_date;
		$this->data['item_id'] = $id;

		//print_r($start_date);

		// load user
		$this->data['item'] = $this->Item->get_one( $id );
		// call the parent edit logic
		parent::edit( $id );

	}

	/**
	 * Determines if valid input.
	 *
	 * @return     boolean  True if valid input, False otherwise.
	 */
	function is_valid_input( $id = 0 ) 
	{
		
		return true;
	}

	/**
	 * Determines if valid name.
	 *
	 * @param      <type>   $name  The  name
	 * @param      integer  $id     The  identifier
	 *
	 * @return     boolean  True if valid name, False otherwise.
	 */
	function is_valid_name( $name, $id = 0 )
	{	
		return true;
	}


	/**
	 * Delete the record
	 * 1) delete Item
	 * 2) delete image from folder and table
	 * 3) check transactions
	 */
	function delete( $id ) 
	{
		// start the transaction
		$this->db->trans_start();

		// check access
		$this->check_access( DEL );

		// delete categories and images
		$enable_trigger = true; 
		
		// delete categories and images
		//if ( !$this->ps_delete->delete_product( $id, $enable_trigger )) {
		$type = "item";

		if ( !$this->ps_delete->delete_history( $id, $type, $enable_trigger )) {

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
        	
			$this->set_flash_msg( 'success', get_msg( 'success_prd_delete' ));
		}
		
		redirect( $this->module_site_url());
	}


	/**
	 * Check Item name via ajax
	 *
	 * @param      boolean  $Item_id  The cat identifier
	 */
	function ajx_exists( $id = false )
	{
		
		// get Item name
		$name = $_REQUEST['title'];
		
		if ( $this->is_valid_name( $name, $id )) {
		// if the Item name is valid,
			
			echo "true";
		} else {
		// if invalid Item name,
			
			echo "false";
		}
	}
	/**
	 * Publish the record
	 *
	 * @param      integer  $prd_id  The Item identifier
	 */
	function ajx_publish( $item_id = 0 )
	{
		// check access
		$this->check_access( PUBLISH );
		
		// prepare data
		$prd_data = array( 'status'=> 1 );
			
		// save data
		if ( $this->Item->save( $prd_data, $item_id )) {
			//Need to delete at history table because that wallpaper need to show again on app
			$data_delete['item_id'] = $item_id;
			$this->Item_delete->delete_by($data_delete);
			echo 'true';
		} else {
			echo 'false';
		}
	}
	
	/**
	 * Unpublish the records
	 *
	 * @param      integer  $prd_id  The category identifier
	 */
	function ajx_unpublish( $item_id = 0 )
	{
		// check access
		$this->check_access( PUBLISH );
		
		// prepare data
		$prd_data = array( 'status'=> 0 );
			
		// save data
		if ( $this->Item->save( $prd_data, $item_id )) {

			//Need to save at history table because that wallpaper no need to show on app
			$data_delete['item_id'] = $item_id;
			$this->Item_delete->save($data_delete);
			echo 'true';
		} else {
			echo 'false';
		}
	}

 }