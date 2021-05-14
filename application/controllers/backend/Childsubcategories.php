<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Childsubcategories Controller
 */
class Childsubcategories extends BE_Controller {

	/**
	 * Construt required variables
	 */
	function __construct() {

		parent::__construct( MODULE_CONTROL, 'CHILDSUBCATEGORIES' );
		///start allow module check 
		$conds_mod['module_name'] = $this->router->fetch_class();
		//echo '<pre>'; print_r($conds_mod); die(' Hiii Himanshu');
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
	function index() 
	{
		// no publish filter
		$conds['no_publish_filter'] = 1;
		// get rows count
		$this->data['rows_count'] = $this->Childsubcategory->count_all_by( $conds );
		// get categories
		$this->data['child_subcategories'] = $this->Childsubcategory->get_all_by( $conds , $this->pag['per_page'], $this->uri->segment( 4 ) );
		// load index logic
		parent::index();
	}

	/**
	 * Searches for the first match.
	 */
	function search() 
	{
		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'child_subcat_search' );
		
		// condition with search term
		$conds = array( 
			'searchterm' => $this->searchterm_handler( $this->input->post( 'searchterm' )),
			'cat_id' => $this->searchterm_handler( $this->input->post('cat_id')) 
		);
		
		// pagination
		$this->data['rows_count'] = $this->Childsubcategory->count_all_by( $conds );

		// search data
		$this->data['child_subcategories'] = $this->Childsubcategory->get_all_by( $conds, $this->pag['per_page'], $this->uri->segment( 4 ) );

		// load add list
		parent::search();
	}

	/**
	 * Create new one
	 */
	function add() 
	{
		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'subcat_add' );
		//echo '<pre>'; print_r($_POST); die(' hiiii');
		// call the core add logic
		parent::add();
		
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
	function save( $id = false ) 
	{
		// start the transaction
		$this->db->trans_start();
		$logged_in_user = $this->ps_auth->get_user_info();
		
		/** 
		 * Insert Category Records 
		 */
		$data = array();
		$associatedData = [];
		//echo '<pre>'; print_r($this->input->post()); die;
	    // Category id
	    if ( $this->has_data( 'cat_id' )) {
			$data['cat_id'] = $this->get_data( 'cat_id' );
		}

	    // SubCategory id
	    if ( $this->has_data( 'sub_cat_id' )) {
			$data['sub_cat_id'] = $this->get_data( 'sub_cat_id' );
		}
        
		// prepare cat name
		if ( $this->has_data( 'name' )) {
			$data['name'] = $this->get_data( 'name' );
		}

		if ( $this->has_data( 'is_color_filter' )) {
			$data['is_color_filter'] = $this->get_data( 'is_color_filter' );
		}
		if ( $this->has_data( 'is_brand_filter' )) {
			$data['is_brand_filter'] = $this->get_data( 'is_brand_filter' );
		}

		// Category id
	    if ( $this->has_data( 'sizegroup_id' )) {
			$sizeGroupData = $this->get_data( 'sizegroup_id' );
			foreach($sizeGroupData as $sizeGroups){
				$associatedData[]['sizegroup_id'] = $sizeGroups;
			}
			$data['is_size_filter'] = 1; 
			//////$data['cat_id'] = 
		}

		//Default Status is Publish 
		$data['status'] = 1;

		// set timezone
		$data['added_user_id'] = $logged_in_user->user_id;

		if($id == "") {
			//save
			$data['added_date'] = date("Y-m-d H:i:s");
		} else {
			//edit
			unset($data['added_date']);
			$data['updated_date'] = date("Y-m-d H:i:s");
			//$data['updated_user_id'] = $logged_in_user->user_id;
		}

		//echo '<pre>'; print_r($data); die;
		// save category
		if ( ! $this->Childsubcategory->save( $data, $id )) {

			//echo '<pre>'; print_r($this->db->error()); die;
			// if there is an error in inserting user data,	
			// rollback the transaction
			$this->db->trans_rollback();
			// set error message
			$this->data['error'] = get_msg( 'err_model' );
			return;
		}
		foreach($associatedData as $key=>$associations){
			$associatedData[$key]['child_subcategory_id'] = $data['id'];
			$associatedData[$key]['added_date'] = date("Y-m-d H:i:s");
		}
		//echo '<pre>'; print_r($associatedData); die;
		if(!empty($associatedData)){
			$this->db->where('child_subcategory_id', $data['id']);
        	$this->db->delete('bs_childsubcategory_sizegroups');
			//echo '<pre>'; print_r($associatedData); die;
			$this->db->insert_batch('bs_childsubcategory_sizegroups', $associatedData);
		}

		/** 
		 * Upload Image Records 
		 */
		//echo $id; die('  hello testing');
		if ( !$id ) {
			if ( ! $this->insert_images_icon_and_cover( $_FILES, 'childsubcategory_cover', $data['id'], "cover" )) {
				// if error in saving image
				// commit the transaction
				$this->db->trans_rollback();
				
				return;
			}
			if ( ! $this->insert_images_icon_and_cover( $_FILES, 'childsubcategory_icon', $data['id'], "icon" )) {
				// if error in saving image
				// commit the transaction
				$this->db->trans_rollback();
				return;
			}	
		}
		// commit the transaction
		if ( ! $this->check_trans()) {
			// set flash error message
			$this->set_flash_msg( 'error', get_msg( 'err_model' ));
		} else {
			if ( $id ) {
			// if user id is not false, show success_add message
				$this->set_flash_msg( 'success', get_msg( 'success_child_subcat_edit' ));
			} else {
			// if user id is false, show success_edit message
				$this->set_flash_msg( 'success', get_msg( 'success_child_subcat_add' ));
			}
		}
		redirect( $this->module_site_url());
	}
	
	/**
	 * Delete the record
	 * 1) delete subcategory
	 * 2) delete image from folder and table
	 * 3) check transactions
	 */
	function delete( $id ) {

		// start the transaction
		$this->db->trans_start();

		// check access
		$this->check_access( DEL );

		// delete categories and images
		$enable_trigger = true; 
		
		// delete categories and images
		$type = "child_subcategory";
		//if ( !$this->ps_delete->delete_subcategory( $id, $enable_trigger )) {
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
        	
			$this->set_flash_msg( 'success', get_msg( 'success_child_subcat_delete' ));
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

		$this->form_validation->set_rules( 'name', get_msg( 'child_subcat_name' ), $rule);

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

			if ( html_entity_decode(strtolower( $this->Childsubcategory->get_one( $id )->name )) == htmlentities(strtolower( $name ))) {
			// if the name is existing name for that user id,
				return true;
			} else if ( $this->Childsubcategory->exists( ($conds ))) {
			// if the name is existed in the system,
				$this->form_validation->set_message('is_valid_name', get_msg( 'err_dup_name' ));
				return false;
			}

			return true;
	}

	/**childsubcategory
	 * Check child subcategory name via ajax
	 *
	 * @param      boolean  $child_subcategory_id  The subcategory identifier
	 */
	function ajx_exists( $child_subcategory_id = false )
	{
		

		// get subcategory name
		$name = $_REQUEST['name'];

		if ( $this->is_valid_name( $name, $child_subcategory_id )) {
		// if the child subcategory name is valid,
			
			echo "true";
		} else {
		// if invalid child subcategory name,
			
			echo "false";
		}

		
	}


	/**
	 * Publish the record
	 *
	 * @param      integer  $child_subcategory_id  The child subcategory identifier
	 */
	function ajx_publish( $child_subcategory_id = 0 )
	{
		// check access
		$this->check_access( PUBLISH );	
		// prepare data
		$child_subcategory_data = array( 'status'=> 1 );
		// save data
		if ( $this->Childsubcategory->save( $child_subcategory_data, $child_subcategory_id )) {
			echo true;
		} else {
			echo false;
		}
	}
	
	/**
	 * Unpublish the records
	 *
	 * @param      integer  $subcategory_id  The subcategory identifier
	 */
	function ajx_unpublish( $child_subcategory_id = 0 )
	{
		// check access
		$this->check_access( PUBLISH );
		
		// prepare data
		$child_subcategory_data = array( 'status'=> 0 );
			
		// save data
		if ( $this->Childsubcategory->save( $child_subcategory_data, $child_subcategory_id )) {
			echo true;
		} else {
			echo false;
		}
	}


	/**
 	* Update the existing one
	*/
	function edit( $id ) 
	{
		
		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'child_subcat_edit' );

		// load user
		$this->data['child_subcategory'] = $this->Childsubcategory->get_one( $id );
		//echo '<pre>'; print_r($this->data['child_subcategory']); die;
		// call the parent edit logic
		parent::edit( $id );
		
	}

	//get all subcategories when select category

	function get_all_sub_categories( $cat_id )
    {
    	$conds['cat_id'] = $cat_id;
    	
    	$sub_categories = $this->Subcategory->get_all_by($conds);
		echo json_encode($sub_categories->result());
    }

}