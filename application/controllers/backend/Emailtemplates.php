<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Emailtemplates Controller
 */
class Emailtemplates extends BE_Controller {

	/**
	 * Construct required variables
	 */
	function __construct() {

		parent::__construct( MODULE_CONTROL, 'Emailtemplates' );
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
	 * List down the Emailtemplates
	 */
	function index() {
		
		// no publish filter
		$conds['no_publish_filter'] = 1;
		$conds['order_by'] = 1;
		$conds['order_by_field'] = "created_at";
		$conds['order_by_type'] = "desc";
		// get rows count
        
		$this->data['rows_count'] = $this->Emailtemplate->count_all_by( $conds );
		// get subtopics
		$this->data['templates'] = $this->Emailtemplate->get_all_by( $conds , $this->pag['per_page'], $this->uri->segment( 4 ) );
		// load index logic
		parent::index();
	}

	/**
	 * Searches for the first match.
	 */
	function search() {
		

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'title_search' );
		
		// condition with search term
		$conds = array( 'searchterm' => $this->searchterm_handler( $this->input->post( 'searchterm' )));
		// no publish filter
		$conds['no_publish_filter'] = 1;
		$conds['order_by'] = 1;
		$conds['order_by_field'] = "created_at";
		$conds['order_by_type'] = "desc";


		// pagination
		$this->data['rows_count'] = $this->Emailtemplate->count_all_by( $conds );
		// search data
		$this->data['templates'] = $this->Emailtemplate->get_all_by( $conds, $this->pag['per_page'], $this->uri->segment( 4 ) );
		// load add list
		parent::search();
	}

	/**
	 * Create new one
	 */
	function add() {

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'template_add' );

		// call the core add logic
		parent::add();
	}

	/**
	 * Update the existing one
	 */
	function edit( $id ) {

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'template_edit' );

		// load user
		$this->data['template'] = $this->Emailtemplate->get_one( $id );

		// call the parent edit logic
		parent::edit( $id );
	}

	/**
	 * Saving Logic
	 * 2) save template
	 * 4) check transaction status
	 *
	 * @param      boolean  $id  The user identifier
	 */
	function save( $id = false ) {
		// start the transaction
		$this->db->trans_start();
		
		/** 
		 * Insert SubTopics Records 
		 */
		$data = array();

		// prepare subtopic name
		if ( $this->has_data( 'title' )) {
			$data['title'] = $this->get_data( 'title' );
		}
        
        // content
		if ( $this->has_data( 'content' )) {
			$data['content'] = $this->get_data( 'content' );
		}

        $data['created_at'] = date('Y-m-d H:i:s');
		// save subtopics
		if ( ! $this->Emailtemplate->save( $data, $id )) {
		// if there is an error in inserting data,	

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
				
				$this->set_flash_msg( 'success', get_msg( 'success_subtopic_edit' ));
			} else {
			// if user id is false, show success_edit message

				$this->set_flash_msg( 'success', get_msg( 'success_subtopic_add' ));
			}
		}

		redirect( $this->module_site_url());
	}


	

	/**
	 * Delete the record
	 * 1) delete template
	 * 3) check transactions
	 */
	function delete( $id ) {

		// start the transaction
		$this->db->trans_start();

		// check access
		$this->check_access( DEL );
		
		// delete subtopics and images
		if ( !$this->ps_delete->delete_template( $id )) {

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
        	
			$this->set_flash_msg( 'success', get_msg( 'success_template_delete' ));
		}
		
		redirect( $this->module_site_url());
	}


	
	/**
	 * Determines if valid input.
	 *
	 * @return     boolean  True if valid input, False otherwise.
	 */
	function is_valid_input( $id = 0 ) {

		$rule = 'required|callback_is_valid_title['. $id  .']';

		$this->form_validation->set_rules( 'title', get_msg( 'title' ), $rule);

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
	function is_valid_title( $name, $id = 0 )
	{		
		 $conds['name'] = $name;

		 	if ( strtolower( $this->Emailtemplate->get_one( $id )->title ) == strtolower( $name )) {
			// if the name is existing name for that user id,
				return true;
			} else if ( $this->Emailtemplate->exists( ($conds ))) {
			// if the name is existed in the system,
				$this->form_validation->set_message('is_valid_title', get_msg( 'err_dup_name' ));
				return false;
			}
			return true;
	}
	/**
	 * Check Emailtemplates name via ajax
	 *
	 * @param      boolean  $id  The id identifier
	 */
	function ajx_exists( $id = false )
	{
		// get subtopics name

		$name = $_REQUEST['name'];

		if ( $this->is_valid_name( $name, $id )) {

		// if the subtopics name is valid,
			
			echo "true";
		} else {
		// if invalid subtopics name,
			
			echo "false";
		}
	}

	/**
	 * Publish the record
	 *
	 * @param      integer  $id  The Emailtemplates identifier
	 */
	function ajx_publish( $id = 0 )
	{
		// check access
		$this->check_access( PUBLISH );
		
		// prepare data
		$topic_data = array( 'status'=> 1 );
			
		// save data
		if ( $this->Emailtemplate->save( $topic_data, $id )) {
			echo 'true';
		} else {
			echo 'false';
		}
	}
	
	/**
	 * Unpublish the records
	 *
	 * @param      integer  $id  The subtopics identifier
	 */
	function ajx_unpublish( $id = 0 )
	{
		// check access
		$this->check_access( PUBLISH );
		
		// prepare data
		$topic_data = array( 'status'=> 0 );
			
		// save data
		if ( $this->Emailtemplate->save( $topic_data, $id )) {
			echo 'true';
		} else {
			echo 'false';
		}
	}
}