<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Coupans Controller
 */
class Coupans extends BE_Controller {

	/**
	 * Construct required variables
	 */
	function __construct() {

		parent::__construct( MODULE_CONTROL, 'Coupons' );
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
	 * List down the registered coupans
	 */
	function index() {
		
		// no publish filter
		$conds['no_publish_filter'] = 1;
		$conds['order_by'] = 1;
		$conds['order_by_field'] = "created_at";
		$conds['order_by_type'] = "desc";
		// get rows count
        
		$this->data['rows_count'] = $this->Coupan->count_all_by( $conds );
        
		// get Coupans
		$this->data['hcCoupan'] = $this->Coupan->get_all_by( $conds , $this->pag['per_page'], $this->uri->segment( 4 ) );
		// load index logic
		parent::index();
	}

	/**
	 * Searches for the first match.
	 */
	function search() {
		

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'Coupans_search' );
		
		// condition with search term
		$conds = array( 'searchterm' => $this->searchterm_handler( $this->input->post( 'searchterm' )) );
		// no publish filter
		$conds['no_publish_filter'] = 1;
		$conds['order_by'] = 1;
		$conds['order_by_field'] = "created_at";
		$conds['order_by_type'] = "desc";


		// pagination
		$this->data['rows_count'] = $this->Coupan->count_all_by( $conds );
		// search data
		$this->data['hcCoupan'] = $this->Coupan->get_all_by( $conds, $this->pag['per_page'], $this->uri->segment( 4 ) );
		// load add list
		parent::search();
	}

	/**
	 * Create new one
	 */
	function add() {

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'coupan_add' );

		// call the core add logic
		parent::add();
	}

	/**
	 * Update the existing one
	 */
	function edit( $id ) {

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'Coupan_edit' );

		// load user
		$this->data['coupan'] = $this->Coupan->get_one( $id );

		// call the parent edit logic
		parent::edit( $id );
	}

	/**
	 * Saving Logic
	 * 1) upload image
	 * 2) save Coupans
	 * 3) save image
	 * 4) check transaction status
	 *
	 * @param      boolean  $id  The user identifier
	 */
	function save( $id = false ) {
		// start the transaction
		$this->db->trans_start();
		
		/** 
		 * Insert Coupans Records 
		 */
		$data = array();

		// prepare Coupan name
		if ( $this->has_data( 'type' )) {
			$data['type'] = $this->get_data( 'type' );
		}
		if ( $this->has_data( 'coupan_name' )) {
			$data['name'] = $this->get_data( 'coupan_name' );
            if(!$id) {
                $data['slug'] = str_replace(' ', '_', strtolower($data['name']));
            }
		}
		if ( $this->has_data( 'user_earn' )) {
			$data['user_earn'] = $this->get_data( 'user_earn' );
		}
		if ( $this->has_data( 'coupan_value' )) {
			$data['value'] = $this->get_data( 'coupan_value' );
		}
		if ( $this->has_data( 'min_purchase_amount' )) {
			$data['min_purchase_amount'] = $this->get_data( 'min_purchase_amount' );
		}
		if ( $this->has_data( 'end_at' ) && !empty($this->get_data( 'end_at' )) ) {
			$data['end_at'] = $this->get_data( 'end_at' );
		}
        
        $data['created_at'] = date('Y-m-d H:i:s');
		// save Coupans
		if ( ! $this->Coupan->save( $data, $id )) {
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
				
				$this->set_flash_msg( 'success', get_msg( 'success_Coupan_edit' ));
			} else {
			// if user id is false, show success_edit message

				$this->set_flash_msg( 'success', get_msg( 'success_Coupan_add' ));
			}
		}

		redirect( $this->module_site_url());
	}


	

	/**
	 * Delete the record
	 * 1) delete Coupans
	 * 2) delete image from folder and table
	 * 3) check transactions
	 */
	function delete( $id ) {

		// start the transaction
		$this->db->trans_start();

		// check access
		$this->check_access( DEL );
		
		// delete Coupans and images
		if ( !$this->ps_delete->delete_coupan( $id )) {

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
        	
			$this->set_flash_msg( 'success', get_msg( 'success_Coupan_delete' ));
		}
		
		redirect( $this->module_site_url());
	}


	
	/**
	 * Determines if valid input.
	 *
	 * @return     boolean  True if valid input, False otherwise.
	 */
	function is_valid_input( $id = 0 ) {

//		$rule = 'required|callback_is_valid_name['. $id  .']';
//
//		$this->form_validation->set_rules( 'type', get_msg( 'type' ), $rule);
//
//		if ( $this->form_validation->run() == FALSE ) {
//		// if there is an error in validating,
//
//			return false;
//		}

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
		 $conds['name'] = $name;

		 	if ( strtolower( $this->Coupan->get_one( $id )->name ) == strtolower( $name )) {
			// if the name is existing name for that user id,
				return true;
			} else if ( $this->Coupan->exists( ($conds ))) {
			// if the name is existed in the system,
				$this->form_validation->set_message('is_valid_name', get_msg( 'err_dup_name' ));
				return false;
			}
			return true;
	}
	/**
	 * Check Coupans name via ajax
	 *
	 * @param      boolean  $id  The id identifier
	 */
	function ajx_exists( $id = false )
	{
		// get Coupans name

		$name = $_REQUEST['name'];

		if ( $this->is_valid_name( $name, $id )) {

		// if the Coupans name is valid,
			
			echo "true";
		} else {
		// if invalid Coupans name,
			
			echo "false";
		}
	}

	/**
	 * Publish the record
	 *
	 * @param      integer  $id  The Coupans identifier
	 */
	function ajx_publish( $id = 0 )
	{
		// check access
		$this->check_access( PUBLISH );
		
		// prepare data
		$Coupan_data = array( 'status'=> 1 );
			
		// save data
		if ( $this->Coupan->save( $Coupan_data, $id )) {
			echo 'true';
		} else {
			echo 'false';
		}
	}
	
	/**
	 * Unpublish the records
	 *
	 * @param      integer  $id  The Coupans identifier
	 */
	function ajx_unpublish( $id = 0 )
	{
		// check access
		$this->check_access( PUBLISH );
		
		// prepare data
		$Coupan_data = array( 'status'=> 0 );
			
		// save data
		if ( $this->Coupan->save( $Coupan_data, $id )) {
			echo 'true';
		} else {
			echo 'false';
		}
	}
}