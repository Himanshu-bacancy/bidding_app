<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Transactions Controller
 */
class Transactions extends BE_Controller {

	/**
	 * Construt required variables
	 */
	function __construct() {

		parent::__construct( MODULE_CONTROL, 'TRANSACTIONS' );
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
		
		// no publish filter
		$conds['no_publish_filter'] = 1;

		// get rows count
		$this->data['rows_count'] = $this->Paid_item->count_all_by( $conds );

		// get transactions
		$this->data['transactions'] = $this->Paid_item->get_all_by( $conds , $this->pag['per_page'], $this->uri->segment( 4 ) );

		// load index logic
		parent::index();
	}

	/**
	 * Searches for the first match.
	 */
	function search() {

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'history_search' );

		// condition with search term
		if($this->input->post('submit') != NULL ){
			
			if($this->input->post('searchterm') != "") {
				$conds_name['title'] = $this->input->post('searchterm');
				$items = $this->Item->get_all_by($conds_name)->result();
			
				foreach ($items as $itm) {
					$result .= $itm->id .",";
					
				}
				
				$item_id = rtrim($result,",");
				$tmp_item_id = explode(",", $item_id);
			  	$conds['searchterm'] = $tmp_item_id;
				$this->data['searchterm'] = $conds['searchterm'];

				$this->session->set_userdata(array("searchterm" => $tmp_item_id));
			} else {
				
				$this->session->set_userdata(array("searchterm" => NULL));
			}

			if($this->input->post('status') != "") {
				$status = $this->input->post('status');
				$conds['status'] = $status[0];
				$this->session->set_userdata(array("status" => $this->input->post('status')));
			} else {
				
				$this->session->set_userdata(array("status" => NULL));
			}
			
			if($this->input->post('date') != "") {
				$conds['date'] = $this->input->post('date');
				$this->data['date'] = $this->input->post('date');
				$this->session->set_userdata(array("date" => $this->input->post('date')));
			} else {
				
				$this->session->set_userdata(array("date" => NULL));
			}


		} else {
			//read from session value
			if($this->session->userdata('searchterm') != NULL){
				$conds['searchterm'] = $this->session->userdata('searchterm');
				$this->data['searchterm'] = $this->session->userdata('searchterm');
			}

			if($this->session->userdata('status') != NULL){
				$conds['status'] = $this->session->userdata('status');
				$this->data['status'] = $this->session->userdata('status');
			}

			if($this->session->userdata('date') != NULL){
				$conds['date'] = $this->session->userdata('date');
				$this->data['date'] = $this->session->userdata('date');
			}

		}

		if ($conds['status'] == 0) {
			$conds['status'] = " ";
		}
		// print_r($conds);die;
		// pagination
		$this->data['rows_count'] = $this->Paid_item->count_all_by( $conds );

		// search data
		$this->data['transactions'] = $this->Paid_item->get_all_by( $conds, $this->pag['per_page'], $this->uri->segment( 4 ) );

		// load add list
		parent::search();
	}

	/**
	 * Update the existing one
	 */
	function edit( $id ) {

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'paid_history_edit' );

		// load user
		$this->data['trans'] = $this->Paid_item->get_one( $id );
		// load item
		$item_id = $this->Paid_item->get_one( $id )->item_id;
		$this->data['item'] = $this->Item->get_one( $item_id );

		// call the parent edit logic
		parent::edit( $id );
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

		// Item id
	   	if ( $this->has_data( 'id' )) {
			$data['id'] = $this->get_data( 'id' );

		}

	   	// item id
	   	if ( $this->has_data( 'item_id' )) {
			$data['item_id'] = $this->get_data( 'item_id' );
		}

		// start_date
	   	if ( $this->has_data( 'start_date' )) {
			$data['start_date'] = $this->get_data( 'start_date' );
		}

		// end_date
	   	if ( $this->has_data( 'end_date' )) {
			$data['end_date'] = $this->get_data( 'end_date' );
		}

		// amount
	   	if ( $this->has_data( 'amount' )) {
			$data['amount'] = $this->get_data( 'amount' );
		}

		// payment_method
	   	if ( $this->has_data( 'payment_method' )) {
			$data['payment_method'] = $this->get_data( 'payment_method' );
		}

		// set timezone

		if($id == "") {
			//save
			$data['added_date'] = date("Y-m-d H:i:s");
			$data['added_user_id'] = $logged_in_user->user_id;

		} else {
			//edit
			unset($data['added_date']);
			$data['updated_date'] = date("Y-m-d H:i:s");
			$data['updated_user_id'] = $logged_in_user->user_id;
		}
		//save item
		if ( ! $this->Paid_item->save( $data, $id )) {
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


		// Item Id Checking 
		if ( $this->has_data( 'gallery' )) {
		// if there is gallery, redirecti to gallery
			redirect( $this->module_site_url( 'gallery/' .$id ));
		}
		else {
		// redirect to list view
			redirect( $this->module_site_url() );
		}

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

}

?>