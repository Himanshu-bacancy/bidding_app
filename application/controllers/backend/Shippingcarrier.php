<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Shippingcarrier Controller
 */
class Shippingcarrier extends BE_Controller {

	/**
	 * Construt required variables
	 */
	function __construct() {

		parent::__construct( MODULE_CONTROL, 'Shippingcarrier' );
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
		$conds['order_by'] = 1;
		$conds['order_by_field'] = "added_date";
		$conds['order_by_type'] = "desc";
		// get rows count
		$this->data['rows_count'] = $this->Shippingcarriers->count_all_by( $conds );
		
		// get shippingcarriers
		$this->data['shippingcarriers'] = $this->Shippingcarriers->get_all_by( $conds , $this->pag['per_page'], $this->uri->segment( 4 ) );
		// load index logic
		parent::index();
	}

	/**
	 * Searches for the first match.
	 */
	function search() {
		

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'shippingcarrier_search' );
		
		// condition with search term
		$conds = array( 'searchterm' => $this->searchterm_handler( $this->input->post( 'searchterm' )) ,
		'packagesize_id' => $this->searchterm_handler( $this->input->post('packagesize_id')));
		// no publish filter
		$conds['no_publish_filter'] = 1;
		$conds['order_by'] = 1;
		$conds['order_by_field'] = "added_date";
		$conds['order_by_type'] = "desc";


		// pagination
		$this->data['rows_count'] = $this->Shippingcarriers->count_all_by( $conds );

		// search data
		$this->data['shippingcarriers'] = $this->Shippingcarriers->get_all_by( $conds, $this->pag['per_page'], $this->uri->segment( 4 ) );
		
		// load add list
		parent::search();
	}

	/**
	 * Create new one
	 */
	function add() {

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'shippingcarrier_add' );

		// call the core add logic
		parent::add();
	}

	/**
	 * Update the existing one
	 */
	function edit( $id ) {

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'shippingcarrier_edit' );

		// load user
		$this->data['shippingcarrier'] = $this->Shippingcarriers->get_one( $id );

		// call the parent edit logic
		parent::edit( $id );
	}

	/**
	 * Saving Logic
	 * 1) upload image
	 * 2) save Shippingcarriers
	 * 3) save image
	 * 4) check transaction status
	 *
	 * @param      boolean  $id  The user identifier
	 */
	function save( $id = false ) {
		// start the transaction
		$this->db->trans_start();
		
		/** 
		 * Insert Shippingcarriers Records 
		 */
		$data = array();

		if ( $this->has_data( 'name' )) {
			$data['name'] = $this->get_data( 'name' );
		}
		if ( $this->has_data( 'price' )) {
			$data['price'] = $this->get_data( 'price' );
		}
		if ( $this->has_data( 'min_days' )) {
			$data['min_days'] = $this->get_data( 'min_days' );
		}
		if ( $this->has_data( 'max_days' )) {
			$data['max_days'] = $this->get_data( 'max_days' );
		}
		if ( $this->has_data( 'packagesize_id' )) {
			$data['packagesize_id'] = $this->get_data( 'packagesize_id' );
		}
		if ( $this->has_data( 'shippo_object_id' )) {
			$data['shippo_object_id'] = $this->get_data( 'shippo_object_id' );
		}

		if ( $this->has_data( 'shippo_servicelevel_token' )) {
			$data['shippo_servicelevel_token'] = $this->get_data( 'shippo_servicelevel_token' );
		}


		// save Shippingcarriers
		if ( ! $this->Shippingcarriers->save( $data, $id )) {
		// if there is an error in inserting user data,	

			// rollback the transaction
			$this->db->trans_rollback();

			// set error message
			$this->data['error'] = get_msg( 'err_model' );
			
			return;
		}

		if ( !$id ) {
			if ( ! $this->insert_images_icon_and_cover( $_FILES, 'shippingcarrier_icon', $data['id'], "icon" )) {
				// if error in saving image
				// commit the transaction
				$this->db->trans_rollback();
				return;
			}	
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
				
				$this->set_flash_msg( 'success', get_msg( 'success_shippingcarrier_edit' ));
			} else {
			// if user id is false, show success_edit message

				$this->set_flash_msg( 'success', get_msg( 'success_shippingcarrier_add' ));
			}
		}

		redirect( $this->module_site_url());
	}


	

	/**
	 * Delete the record
	 * 1) delete Shippingcarriers
	 * 2) delete image from folder and table
	 * 3) check transactions
	 */
	function delete( $id ) {

		// start the transaction
		$this->db->trans_start();

		// check access
		$this->check_access( DEL );
		
		// delete shippingcarriers and images
		if ( !$this->ps_delete->delete_shippingcarrier( $id )) {

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
        	
			$this->set_flash_msg( 'success', get_msg( 'success_shippingcarrier_delete' ));
		}
		
		redirect( $this->module_site_url());
	}


	
	/**
	 * Determines if valid input.
	 *
	 * @return     boolean  True if valid input, False otherwise.
	 */
	function is_valid_input( $id = 0 ) {

		$rule = 'required';

		$this->form_validation->set_rules( 'name', get_msg( 'shippingcarrier_name' ), $rule);

		if ( $this->form_validation->run() == FALSE ) {
		// if there is an error in validating,

			return false;
		}

		return true;
	}

	/**
	 * Publish the record
	 *
	 * @param      integer  $Shippingcarriers_id  The Shippingcarriers identifier
	 */
	function ajx_publish( $id = 0 )
	{
		// check access
		$this->check_access( PUBLISH );
		
		// prepare data
		$shippingcarrier_data = array( 'status'=> 1 );
			
		// save data
		if ( $this->Shippingcarriers->save( $shippingcarrier_data, $id )) {
			echo 'true';
		} else {
			echo 'false';
		}
	}
	
	/**
	 * Unpublish the records
	 *
	 * @param      integer  $Shippingcarriers_id  The Shippingcarriers identifier
	 */
	function ajx_unpublish( $id = 0 )
	{
		// check access
		$this->check_access( PUBLISH );
		
		// prepare data
		$shippingcarrier_data = array( 'status'=> 0 );
			
		// save data
		if ( $this->Shippingcarriers->save( $shippingcarrier_data, $id )) {
			echo 'true';
		} else {
			echo 'false';
		}
	}
}