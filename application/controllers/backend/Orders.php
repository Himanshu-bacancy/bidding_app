<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Item Itemreport Controller
 */
class Orders extends BE_Controller {

	/**
	 * Construt required variables
	 */
	function __construct() {

		parent::__construct( MODULE_CONTROL, 'Orders' );
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

		$conds['order_by'] = 1;
        $conds['order_by_field'] = "created_at";
		$conds['order_by_type'] = "desc";
		// get rows count
		$this->data['rows_count'] = $this->Order->count_all_by( $conds );
		
		// get Item reports
		$this->data['orders'] = $this->Order->get_all_by( $conds , $this->pag['per_page'], $this->uri->segment( 4 ) );
        // echo '<pre>'; print_r($this->data['orders']->result()); die(' hello testing');
		// load index logic
		parent::index();
	}

	/**
	 * Searches for the first match.
	 */
	function search() {
		// breadcrumb urls
		$this->data['item_reports'] = get_msg( 'report_search' );
		
		// condition with search term
		$conds = array( 'searchterm' => $this->searchterm_handler( $this->input->post( 'searchterm' )) );
		// no publish filter
		$conds['status'] = 1;
		$conds['type'] = 'report_item';
		$conds['order_by'] = 1;
		$conds['order_by_field'] = "added_date";
		$conds['order_by_type'] = "desc";


		// pagination
		$this->data['rows_count'] = $this->Reason_operation->count_all_by( $conds );

		// search data
		$this->data['reports'] = $this->Reason_operation->get_all_by( $conds, $this->pag['per_page'], $this->uri->segment( 4 ) );
		
		// load add list
		parent::search();
	}

	/**
	 * Update the existing one
	 */
	function edit( $id ) {
        // breadcrumb urls
		$this->data['action_title'] = get_msg( 'order_detail' );
        // load order
        //$order = $this->db->query("SELECT * FROM `bs_order` WHERE id = '".$id."'")->result();
        $order = $this->db->from('bs_order')
                ->where('bs_order.id', $id)
                ->join('core_users as order_user', 'bs_order.user_id = order_user.user_id')
                ->join('bs_items', 'bs_order.items = bs_items.id')
                ->join('core_users as seller', 'bs_items.added_user_id = seller.user_id')
		        ->join('bs_track_order', 'bs_order.order_id = bs_track_order.order_id', 'left')->get()->result();
        // print_r($this->db->last_query());die;
        //$this->data['orders'] = $order[0];
        $item_id = $order[0]->items;;

		$this->data['item'] = $this->Item->get_one( $item_id );
        //echo '<pre>'; print_r($order); die;
		// call the parent edit logic
		parent::edit( $id );
	}

	/**
	 * Saving Logic
	 * 1) upload image
	 * 2) save Itemreport
	 * 3) save image
	 * 4) check transaction status
	 *
	 * @param      boolean  $id  The user identifier
	 */
	 
	function save( $id = false ) {
		//echo "2";die;
		
			$logged_in_user = $this->ps_auth->get_user_info();

			$report = $this->Reason_operation->get_one( $id );

			$item_id = $report->operation_id;

			$user_id = $report->user_id;
			//print_r($user_id);die;

			$conds['item_id'] = $item_id;

			$conds['user_id'] = $user_id;


			if( isset($item_id) && isset($user_id) ){
				$this->Reason_operation->delete_by( $conds );
			}

			$data['status'] = 2;

			//print_r($data);die;
			//save item
			if ( ! $this->Item->save( $data, $item_id )) {
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
	function is_valid_input( $id = 0 ) {

		return true;
	}

	/**
	 * Determines if valid name.
	 *
	 * @param      <report>   $name  The  name
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

		// enable trigger to delete all products related data
	    $enable_trigger = true;

	    if ( ! $this->ps_delete->delete_report( $id, $enable_trigger )) {

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
}