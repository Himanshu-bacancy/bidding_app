<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Users crontroller for BE_USERS table
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
		$conds = array( 'register_role_id' => 4 );

		// get rows count
		$this->data['rows_count'] = $this->User->count_all_by($conds);

		// get users
		$this->data['users'] = $this->User->get_all_by($conds, $this->pag['per_page'], $this->uri->segment( 4 ) );

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
			

		} else {
			//read from session value
			if($this->session->userdata('searchterm') != NULL){
				$conds['searchterm'] = $this->session->userdata('searchterm');
				$this->data['searchterm'] = $this->session->userdata('searchterm');
			}

		}


		$conds['register_role_id'] = 4;
		$this->data['rows_count'] = $this->User->count_all_by( $conds );

		$this->data['users'] = $this->User->get_all_by( $conds, $this->pag['per_page'], $this->uri->segment( 4 ));
		
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
}