<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Packagesize Controller
 */
class Packagesize extends BE_Controller {

	/**
	 * Construt required variables
	 */
	function __construct() {

		parent::__construct( MODULE_CONTROL, 'Packagesize' );
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
		$this->data['rows_count'] = $this->Packagesizes->count_all_by( $conds );
		
		// get packagesizes
		$this->data['packagesizes'] = $this->Packagesizes->get_all_by( $conds , $this->pag['per_page'], $this->uri->segment( 4 ) );
		// load index logic
		parent::index();
	}

	/**
	 * Searches for the first match.
	 */
	function search() {
		

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'packagesize_search' );
		
		// condition with search term
		$conds = array( 'searchterm' => $this->searchterm_handler( $this->input->post( 'searchterm' )) );
		// no publish filter
		$conds['no_publish_filter'] = 1;
		$conds['order_by'] = 1;
		$conds['order_by_field'] = "added_date";
		$conds['order_by_type'] = "desc";


		// pagination
		$this->data['rows_count'] = $this->Packagesizes->count_all_by( $conds );

		// search data
		$this->data['packagesizes'] = $this->Packagesizes->get_all_by( $conds, $this->pag['per_page'], $this->uri->segment( 4 ) );
		
		// load add list
		parent::search();
	}

	/**
	 * Create new one
	 */
	function add() {

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'packagesize_add' );

		// call the core add logic
		parent::add();
	}

	/**
	 * Update the existing one
	 */
	function edit( $id ) {

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'packagesize_edit' );

		// load user
		$this->data['packagesize'] = $this->Packagesizes->get_one( $id );

		// call the parent edit logic
		parent::edit( $id );
	}

	/**
	 * Saving Logic
	 * 1) upload image
	 * 2) save Packagesizes
	 * 3) save image
	 * 4) check transaction status
	 *
	 * @param      boolean  $id  The user identifier
	 */
	function save( $id = false ) {
		// start the transaction
		$this->db->trans_start();
		
		/** 
		 * Insert Packagesizes Records 
		 */
		$data = array();

		if ( $this->has_data( 'name' )) {
			$data['name'] = $this->get_data( 'name' );
		}
		if ( $this->has_data( 'length' )) {
			$data['length'] = $this->get_data( 'length' );
		}
		if ( $this->has_data( 'width' )) {
			$data['width'] = $this->get_data( 'width' );
		}
		if ( $this->has_data( 'height' )) {
			$data['height'] = $this->get_data( 'height' );
		}
		if ( $this->has_data( 'weight' )) {
			$data['weight'] = $this->get_data( 'weight' );
		}


		// save Packagesizes
		if ( ! $this->Packagesizes->save( $data, $id )) {
		// if there is an error in inserting user data,	

			// rollback the transaction
			$this->db->trans_rollback();

			// set error message
			$this->data['error'] = get_msg( 'err_model' );
			
			return;
		}

		if ( !$id ) {
			if ( ! $this->insert_images_icon_and_cover( $_FILES, 'packagesize_icon', $data['id'], "icon" )) {
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
				
				$this->set_flash_msg( 'success', get_msg( 'success_packagesize_edit' ));
			} else {
			// if user id is false, show success_edit message

				$this->set_flash_msg( 'success', get_msg( 'success_packagesize_add' ));
			}
		}

		redirect( $this->module_site_url());
	}


	

	/**
	 * Delete the record
	 * 1) delete Packagesizes
	 * 2) delete image from folder and table
	 * 3) check transactions
	 */
	function delete( $id ) {

		// start the transaction
		$this->db->trans_start();

		$carrierdata = $this->db->get_where('bs_shippingcarriers', array('packagesize_id' => $id));

		if($carrierdata->num_rows() >= 1)
		{
			$this->db->trans_rollback();

			// set error message
			$this->data['error'] = get_msg( 'carrier_delete_alert' );

			$this->set_flash_msg( 'error', get_msg( 'carrier_delete_alert' ));	
			
			// redirect to list view
			redirect( $this->module_site_url());
		}

		

		// check access
		$this->check_access( DEL );
		
		// delete packagesizes and images
		if ( !$this->ps_delete->delete_packagesize( $id )) {

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
        	
			$this->set_flash_msg( 'success', get_msg( 'success_packagesize_delete' ));
		}
		
		redirect( $this->module_site_url());
	}


	
	/**
	 * Determines if valid input.
	 *
	 * @return     boolean  True if valid input, False otherwise.
	 */
	function is_valid_input( $id = 0 ) {

		$rule = 'required|callback_is_valid_name['. $id  .']';

		$this->form_validation->set_rules( 'name', get_msg( 'packagesize_name' ), $rule);

		if ( $this->form_validation->run() == FALSE ) {
		// if there is an error in validating,

			return false;
		}

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

		 	if ( strtolower( $this->Packagesizes->get_one( $id )->name ) == strtolower( $name )) {
			// if the name is existing name for that user id,
				return true;
			} else if ( $this->Packagesizes->exists( ($conds ))) {
			// if the name is existed in the system,
				$this->form_validation->set_message('is_valid_name', get_msg( 'err_dup_name' ));
				return false;
			}
			return true;
	}

	
	/**
	 * Check Packagesizes name via ajax
	 *
	 * @param      boolean  $cat_id  The cat identifier
	 */
	function ajx_exists( $id = false )
	{
		// get Packagesizes name

		$name = $_REQUEST['name'];
		
		if ( $this->is_valid_name( $name, $id )) {

		// if the Packagesizes name is valid,
			
			echo "true";
		} 
		else {
		// if invalid Packagesizes name,
			
			echo "false";
		}
	}

	

	/**
	 * Publish the record
	 *
	 * @param      integer  $Packagesizes_id  The Packagesizes identifier
	 */
	function ajx_publish( $id = 0 )
	{
		// check access
		$this->check_access( PUBLISH );
		
		// prepare data
		$packagesize_data = array( 'status'=> 1 );
			
		// save data
		if ( $this->Packagesizes->save( $packagesize_data, $id )) {
			echo 'true';
		} else {
			echo 'false';
		}
	}
	
	/**
	 * Unpublish the records
	 *
	 * @param      integer  $Packagesizes_id  The Packagesizes identifier
	 */
	function ajx_unpublish( $id = 0 )
	{
		// check access
		$this->check_access( PUBLISH );
		
		// prepare data
		$packagesize_data = array( 'status'=> 0 );
			
		// save data
		if ( $this->Packagesizes->save( $packagesize_data, $id )) {
			echo 'true';
		} else {
			echo 'false';
		}
	}
}