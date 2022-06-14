<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sizegroup Controller
 */
class Sizegroup extends BE_Controller {

	/**
	 * Construt required variables
	 */
	function __construct() {

		parent::__construct( MODULE_CONTROL, 'SIZEGROUP' );
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
		$this->data['rows_count'] = $this->Sizegroups->count_all_by( $conds );
		
		// get sizegroups
		$this->data['sizegroups'] = $this->Sizegroups->get_all_by( $conds , $this->pag['per_page'], $this->uri->segment( 4 ) );
		// load index logic
		parent::index();
	}

	/**
	 * Searches for the first match.
	 */
	function search() {
		

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'sizegroup_search' );
		
		// condition with search term
		$conds = array( 'searchterm' => $this->searchterm_handler( $this->input->post( 'searchterm' )) );
		// no publish filter
		$conds['no_publish_filter'] = 1;
		$conds['order_by'] = 1;
		$conds['order_by_field'] = "added_date";
		$conds['order_by_type'] = "desc";


		// pagination
		$this->data['rows_count'] = $this->Sizegroups->count_all_by( $conds );

		// search data
		$this->data['sizegroups'] = $this->Sizegroups->get_all_by( $conds, $this->pag['per_page'], $this->uri->segment( 4 ) );
		
		// load add list
		parent::search();
	}


	/**
	 * Create new one
	 */
	function add() {

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'sizegroup_add' );

		// call the core add logic
		parent::add();
	}

	/**
	 * Update the existing one
	 */
	function edit( $id ) {

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'sizegroup_edit' );

		// load user
		$this->data['sizegroup'] = $this->Sizegroups->get_one( $id );

		// call the parent edit logic
		parent::edit( $id );
	}

	/**
	 * Saving Logic
	 * 1) upload image
	 * 2) save Sizegroups
	 * 3) save image
	 * 4) check transaction status
	 *
	 * @param      boolean  $id  The user identifier
	 */
	function save( $id = false ) {
		// start the transaction
		$this->db->trans_start();
		
		/** 
		 * Insert Sizegroups Records 
		 */
		$data = array();

		// prepare cat name
		if ( $this->has_data( 'name' )) {
			$data['name'] = $this->get_data( 'name' );
		}


		// save Sizegroups
		if ( ! $this->Sizegroups->save( $data, $id )) {
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
				
				$this->set_flash_msg( 'success', get_msg( 'success_sizegroup_edit' ));
			} else {
			// if user id is false, show success_edit message

				$this->set_flash_msg( 'success', get_msg( 'success_sizegroup_add' ));
			}
		}

		redirect( $this->module_site_url());
	}


	

	/**
	 * Delete the record
	 * 1) delete Sizegroups
	 * 2) delete image from folder and table
	 * 3) check transactions
	 */
	function delete( $id ) {

		// start the transaction
		$this->db->trans_start();

		// check access
		$this->check_access( DEL );
		
		// delete sizegroups and images
		if ( !$this->ps_delete->delete_sizegroup( $id )) {

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
        	
			$this->set_flash_msg( 'success', get_msg( 'success_sizegroup_delete' ));
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

		$this->form_validation->set_rules( 'name', get_msg( 'sizegroup_name' ), $rule);

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

		 	if ( strtolower( $this->Sizegroups->get_one( $id )->name ) == strtolower( $name )) {
			// if the name is existing name for that user id,
				return true;
			} else if ( $this->Sizegroups->exists( ($conds ))) {
			// if the name is existed in the system,
				$this->form_validation->set_message('is_valid_name', get_msg( 'err_dup_name' ));
				return false;
			}
			return true;
	}
	/**
	 * Check Sizegroups name via ajax
	 *
	 * @param      boolean  $cat_id  The cat identifier
	 */
	function ajx_exists( $id = false )
	{
		// get Sizegroups name

		$name = $_REQUEST['name'];

		if ( $this->is_valid_name( $name, $id )) {

		// if the Sizegroups name is valid,
			
			echo "true";
		} else {
		// if invalid Sizegroups name,
			
			echo "false";
		}
	}

	/**
	 * Publish the record
	 *
	 * @param      integer  $Sizegroups_id  The Sizegroups identifier
	 */
	function ajx_publish( $id = 0 )
	{
		// check access
		$this->check_access( PUBLISH );
		
		// prepare data
		$sizegroup_data = array( 'status'=> 1 );
			
		// save data
		if ( $this->Sizegroups->save( $sizegroup_data, $id )) {
			echo 'true';
		} else {
			echo 'false';
		}
	}
	
	/**
	 * Unpublish the records
	 *
	 * @param      integer  $Sizegroups_id  The Sizegroups identifier
	 */
	function ajx_unpublish( $id = 0 )
	{
		// check access
		$this->check_access( PUBLISH );
		
		// prepare data
		$sizegroup_data = array( 'status'=> 0 );
			
		// save data
		if ( $this->Sizegroups->save( $sizegroup_data, $id )) {
			echo 'true';
		} else {
			echo 'false';
		}
	}

	function addoption( $groupsizeId, $groupsizeOptionId=NULL ) {
		// breadcrumb urls
		$this->data['action_title'] = 'Size Group Options';

		// load user
		$this->data['sizegroup'] = $this->Sizegroups->get_one( $groupsizeId );
		//$this->load->model('Sizegroup_option');
		$this->data['sizegroup_option'] = $this->Sizegroup_option->get_one( $groupsizeOptionId );
		// call the parent edit logic
		// load header
		$this->load_view( 'partials/header', $this->data );
		// load view
		$this->load_view( 'sizegroup/addoption' , $this->data);

		// load footer
		$this->load_view( 'partials/footer' );
	}
    public function uploadbyscv() {
        if($this->input->post('upload_submit')) {
            $this->form_validation->set_rules('file', 'CSV file', 'callback_file_check');
            if($this->form_validation->run() == true){
                if($_FILES['file']['error'] == 0){
                    $tmpName = $_FILES['file']['tmp_name'];
                    if(($handle = fopen($tmpName, 'r')) !== FALSE) {
                        $date = date('Y-m-d H:i:s');
                        set_time_limit(0);
                        $row = 0;
                        $row_sizegrpoption = 0;
                        while(($data = fgetcsv($handle)) !== FALSE) {
                            // number of fields in the csv
                            $col_count = count($data);

                            // get the values from the csv
                            $csv[$row]['name'] = $data[0];
                            $csv[$row]['status'] = 1;
                            $csv[$row]['added_date'] = $date;
                            $this->db->insert('bs_sizegroup',$csv[$row]);
                            $record_id = $this->db->insert_id();
                            if(!empty($data[1])) {
                                $sizegrpoptions = explode(',', $data[1]);
                                foreach ($sizegrpoptions as $key => $value) {
                                    $sizegrpoption[$row_sizegrpoption]['sizegroup_id'] = $record_id;
                                    $sizegrpoption[$row_sizegrpoption]['title'] = trim($value);
                                    $sizegrpoption[$row_sizegrpoption]['status'] = 1;
                                    $sizegrpoption[$row_sizegrpoption]['added_date'] = $date;
                                    $row_sizegrpoption++;
                                }
                            }

                            // inc the row
                            $row++;
                        }
                        fclose($handle);
                    }
                    if(!empty($sizegrpoption)) {
                        $this->db->insert_batch('bs_sizegroup_option', $sizegrpoption); 
                    }
                    $this->set_flash_msg( 'success', get_msg( 'File import successfully' ));
                }
            }else {
                $this->set_flash_msg( 'error', get_msg( 'csv_file_upload_failed' ));	
            }
        }
        redirect( $this->module_site_url());
    }
    
    public function file_check($str){
        $allowed_mime_types = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
        if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != ""){
            $mime = get_mime_by_extension($_FILES['file']['name']);
            $fileAr = explode('.', $_FILES['file']['name']);
            $ext = end($fileAr);
            if(($ext == 'csv') && in_array($mime, $allowed_mime_types)){
                return true;
            }else{
                $this->form_validation->set_message('file_check', 'Please select only CSV file to upload.');
                return false;
            }
        }else{
            $this->form_validation->set_message('file_check', 'Please select a CSV file to upload.');
            return false;
        }
    }
}