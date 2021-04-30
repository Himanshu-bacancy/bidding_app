<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Categories Controller
 */
class In_app_purchases extends BE_Controller {

	/**
	 * Construt required variables
	 */
	function __construct() {
		
		
		parent::__construct( MODULE_CONTROL, 'In_App_Purchased' );
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
		
		// no publish filter
	function index() {
		// no publish filter
		$conds['no_publish_filter'] = 1;
		// get rows count
		$this->data['rows_count'] = $this->In_app_purchase->count_all_by( $conds );

		// get categories
		$this->data['in_app_purchases'] = $this->In_app_purchase->get_all_by( $conds , $this->pag['per_page'], $this->uri->segment( 4 ) );


		// load index logic
		parent::index();
	
	}

	/**
	 * Searches for the first match.
	 */
	function search() {
		

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'purchase_search' );
		
		// condition with search term
		$conds = array( 'searchterm' => $this->searchterm_handler( $this->input->post( 'searchterm' )) );
      
        $conds['no_publish_filter'] = 1;
         //print_r($conds);die;

		// pagination
		$this->data['rows_count'] = $this->In_app_purchase->count_all_by( $conds );

		// search data
		$this->data['in_app_purchases'] = $this->In_app_purchase->get_all_by( $conds, $this->pag['per_page'], $this->uri->segment( 4 ) );
		
		// load add list
		parent::search();
	}

	/**
	 * Create new one
	 */
	function add() {

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'purchase_add' );

		// call the core add logic
		parent::add();
	}

	/**
	 * Update the existing one
	 */
	function edit( $id ) {

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'purchase_edit' );

		// load user
		$this->data['in_app_purchase'] = $this->In_app_purchase->get_one( $id );

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
		// start the transaction
		$this->db->trans_start();

		$logged_in_user = $this->ps_auth->get_user_info();
		
		/** 
		 * Insert Offline Records 
		 */
		$data = array();


			//purchase day
		   if ( $this->has_data( 'day' )) {
				$data['day'] = $this->get_data( 'day' );
			}
            
            //purchase type
		    if ( $this->has_data( 'type' )) {
				if($data['type'] == "IOS") {
					$data['type'] = $this->get_data( 'type' );
				}elseif ($data['type'] == "Android") {
					$data['type'] = $this->get_data( 'type' );
				}else{
					$data['type'] = $this->get_data( 'type' );
				}
			}

            //purchase description
           	if ( $this->has_data( 'description' )) {
				$data['description'] = $this->get_data( 'description' );
			}

			//purchase in_app_purchase_prd_id
           	if ( $this->has_data( 'in_app_purchase_prd_id' )) {
				$data['in_app_purchase_prd_id'] = $this->get_data( 'in_app_purchase_prd_id' );
			}
            
            if ($this->has_data('status')) {
                $data['status'] = 1;
            }else{
                $data['status'] = 0;
            }

            // added user id and timezone 

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
            // print_r($data);die;
			// save in_app_purched
			if ( ! $this->In_app_purchase->save( $data, $id )) {
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
				
				$this->set_flash_msg( 'success', get_msg( 'success_purchase_edit' ));
			} else {
			// if user id is false, show success_edit message

				$this->set_flash_msg( 'success', get_msg( 'success_purchase_add' ));
			}
		}

		redirect( $this->module_site_url());
	}


	

	/**
	 * Delete the record
	 * 1) delete category
	 * 2) delete image from folder and table
	 * 3) check transactions
	 */
	function delete( $id ) {

		// start the transaction
		$this->db->trans_start();

		// check access
		$this->check_access( DEL );
		
		// delete categories and images
		if ( !$this->ps_delete->delete_purchase( $id )) {

			// set error message
			$this->set_flash_msg( 'error', get_msg( 'err_model' ));

			// rollback
			$this->trans_rollback();

			// redirect to list view
			redirect( $this->module_site_url());
		}
			
		/**
		 * Check Transcation Status
		//  */
		if ( !$this->check_trans()) {

			$this->set_flash_msg( 'error', get_msg( 'err_model' ));	
		} else {
        	
			$this->set_flash_msg( 'success', get_msg( 'success_purchase_delete' ));
		}
		
		redirect( $this->module_site_url());
	}


	/**
	 * Delete all the news under category
	 *
	 * @param      integer  $category_id  The category identifier
	 */
	function delete_all( $id = 0 )
	{
		// start the transaction
		$this->db->trans_start();

		// check access
		$this->check_access( DEL );
		
		// delete categories and images
		$enable_trigger = true; 
		
		$type = "in_app_purchase";

		/** Note: enable trigger will delete news under category and all news related data */
		if ( !$this->ps_delete->delete_history( $id, $type, $enable_trigger )) {
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
        	
			$this->set_flash_msg( 'success', get_msg( 'success_purchase_delete' ));
		}
		
		redirect( $this->module_site_url());
	}

	
	/**
	 * Determines if valid input.
	 *
	 * @return     boolean  True if valid input, False otherwise.
	 */
	function is_valid_input( $id = 0 ) 
	{
		
		$rule = 'required|callback_is_valid_name['. $id  .']';

		$this->form_validation->set_rules( 'in_app_purchase_prd_id', get_msg( 'in_app_purchase_prd_id' ), $rule);
		
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
	function is_valid_name( $description, $id = 0 )
	{		
		 $conds['description'] = $description;
		
		if ( strtolower( $this->In_app_purchase->get_one( $id )->description ) == strtolower( $description )) {
		// if the name is existing name for that user id,
			return true;
		} else if ( $this->In_app_purchase->exists( ($conds ))) {
		// if the name is existed in the system,
			$this->form_validation->set_message('is_valid_name', get_msg( 'err_dup_name' ));
			return false;
		}
		return true;
	}
	
	/**
	 * Publish the record
	 *
	 * @param      integer  $category_id  The category identifier
	 */
	function ajx_publish( $id = 0 )
	{

		// check access
		$this->check_access( PUBLISH );
		
		// prepare data
		$data = array( 'status'=> 1 );
			
		// save data
		if ( $this->In_app_purchase->save( $data, $id )) {
			//Need to delete at history table because that wallpaper need to show again on app
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
	function ajx_unpublish( $id = 0 )
	{
		// check access
		$this->check_access( PUBLISH );
		
		// prepare data
		$data = array( 'status'=> 0 );
			
		// save data
		if ( $this->In_app_purchase->save( $data, $id )) {
			echo 'true';
		} else {
			echo 'false';
		}
	}

 }