<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Sizegroupoption Controller
 */
class Sizegroupoption extends BE_Controller
{

	/**
	 * Construt required variables
	 */
	function __construct()
	{

		parent::__construct(MODULE_CONTROL, 'SIZEGROUPOPTION');
		///start allow module check 
		$conds_mod['module_name'] = $this->router->fetch_class();
		//echo '<pre>'; print_r($conds_mod); die;
		$module_id = $this->Module->get_one_by($conds_mod)->module_id;

		$logged_in_user = $this->ps_auth->get_user_info();

		$user_id = $logged_in_user->user_id;
		if (empty($this->User->has_permission($module_id, $user_id)) && $logged_in_user->user_is_sys_admin != 1) {
			return redirect(site_url('/admin'));
		}
		///end check
	}

	/**
	 * List down all the sizegroup options
	 */
	function index($sizegroupId=NULL)
	{
		// no publish filter
		$conds['no_publish_filter'] = 1;
		$conds['order_by'] = 1;
		$conds['order_by_field'] = "added_date";
		$conds['order_by_type'] = "desc";
		//die('hello testing');
		$conds['sizegroup_id'] = $sizegroupId;
		$this->load->model('Sizegroup_option');
		// get rows count
		$this->data['rows_count'] = $this->Sizegroup_option->count_all_by( $conds );
		$this->data['sizegroup_id'] = $sizegroupId;
		$this->data['sizegroup'] = $this->Sizegroups->get_one( $sizegroupId );
		// get sizegroup options
		// echo '<pre>'; print_r($conds);
		// echo $this->pag['per_page'].'  '.$this->uri->segment(5); die;
		$this->data['sizegroupoptions'] = $this->Sizegroup_option->get_all_by( $conds , $this->pag['per_page'], $this->uri->segment(5));
		$sizeGroupOptionData = $this->data['sizegroupoptions']->result_array();
		if(empty($sizegroupId)){
			foreach($sizeGroupOptionData as $key=>$options){
				$sizeGroupOptionData[$key]['sizegroup'] = (array)$this->Sizegroups->get_one( $options['sizegroup_id'] );
			}
		}
		$this->data['sizeGroupOptionData'] = $sizeGroupOptionData;
		//echo '<pre>'; print_r($this->data); die;
		// load header
		parent::index();
	}

	/**
	 * Searches for the first match.
	 */
	function search($sizegroupId = NULL)
	{
		$this->load->model('Sizegroup_option');
		// breadcrumb urls
		$this->data['action_title'] = get_msg('sizegroup_option_search');
		// condition with search term
		$conds = array('searchterm' => $this->searchterm_handler($this->input->post('searchterm')));
		// no publish filter
		$conds['no_publish_filter'] = 1;
		$conds['order_by'] = 1;
		$conds['order_by_field'] = "added_date";
		$conds['order_by_type'] = "desc";
		$conds['sizegroup_id'] = $sizegroupId;
		// pagination
		$this->data['rows_count'] = $this->Sizegroup_option->count_all_by( $conds );
		$this->data['sizegroup_id'] = $sizegroupId;
		$this->data['sizegroup'] = $this->Sizegroups->get_one( $sizegroupId );
		// get sizegroup options
		
		$this->data['sizegroupoptions'] = $this->Sizegroup_option->get_all_by( $conds , $this->pag['per_page'], $this->uri->segment(5));
		$sizeGroupOptionData = $this->data['sizegroupoptions']->result_array();
		if(empty($sizegroupId)){
			foreach($sizeGroupOptionData as $key=>$options){
				$sizeGroupOptionData[$key]['sizegroup'] = (array)$this->Sizegroups->get_one( $options['sizegroup_id'] );
			}
		}
		$this->data['sizeGroupOptionData'] = $sizeGroupOptionData;
		
		// load add list
		parent::search();
	}

	/**
	 * Create new one
	 */
	function add($sizegroupId = NULL)
	{
		//echo $sizegroupId; die;
		// breadcrumb urls
		$this->data['action_title'] = get_msg('sizegroupoption_add');
		foreach($this->input->post('data') as $key=>$datail){
			$data[$key]['sizegroup_id'] = $this->input->post('sizegroup_id');
			$data[$key]['title'] = $datail['optionname'];
			$data[$key]['description'] = $datail['optiondesc'];
			$data[$key]['status'] = 1;
		}
		//echo '<pre>'; print_r($data); die;
		$this->db->insert_batch('bs_sizegroup_option', $data);
		$this->set_flash_msg('success', get_msg('success_sizegroup_option_add'));
		redirect($this->module_site_url('index/'.$sizegroupId));
	}

	/**
	 * Update the existing one
	 */
	function edit($sizegroupId = Null)
	{
		//echo '<pre>'; print_r($this->input->post()); die;
		// breadcrumb urls
		$this->data['action_title'] = get_msg('sizegroupoption_edit');
		
		$postData = $this->input->post('data');
		$data['sizegroup_id'] = $this->input->post('sizegroup_id');
		$data['title'] = $postData[0]['optionname'];
		$data['description'] = $postData[0]['optiondesc'];
		$data['status'] = 1;
		//echo '<pre>'; print_r($data); die;
		// load user
		$this->db->where('id', $this->input->post('sizegroupoption_id'));
        $this->db->update('bs_sizegroup_option', $data);

		$this->set_flash_msg('success', get_msg('success_sizegroup_option_edit'));
		redirect($this->module_site_url('index/'.$sizegroupId));
	}


	/**
	 * Delete the record
	 * 1) delete Sizegroups
	 * 2) delete image from folder and table
	 * 3) check transactions
	 */
	function delete($sizeGroupOptionId=NULL, $sizeGroupId=NULL)
	{

		// start the transaction
		$this->db->trans_start();
		// check access
		$this->check_access(DEL);
		$this->load->model('Sizegroup_option');
		// delete sizegroups and images
		if (!$this->Sizegroup_option->delete($sizeGroupOptionId)) {

			// set error message
			$this->set_flash_msg('error', get_msg('err_model'));

			// rollback
			$this->trans_rollback();

			// redirect to list view
			redirect($this->module_site_url('index/'.$sizeGroupId));
		}

		/**
		 * Check Transcation Status
		 */
		if (!$this->check_trans()) {
			$this->set_flash_msg('error', get_msg('err_model'));
		} else {
			$this->set_flash_msg('success', get_msg('success_sizegroupoption_delete'));
		}
		redirect($this->module_site_url('index/'.$sizeGroupId));
	}



	/**
	 * Determines if valid input.
	 *
	 * @return     boolean  True if valid input, False otherwise.
	 */
	function is_valid_input($id = 0)
	{

		$rule = 'required|callback_is_valid_name[' . $id  . ']';

		$this->form_validation->set_rules('title', get_msg('sizegroupoption_title'), $rule);

		if ($this->form_validation->run() == FALSE) {
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
	function is_valid_name($name, $id = 0)
	{
		$conds['name'] = $name;
		$this->load->model('Sizegroup_option');
		if (strtolower($this->Sizegroup_option->get_one($id)->title) == strtolower($name)) {
			// if the name is existing name for that user id,
			return true;
		} else if ($this->Sizegroup_option->exists(($conds))) {
			// if the name is existed in the system,
			$this->form_validation->set_message('is_valid_name', get_msg('err_dup_name'));
			return false;
		}
		return true;
	}
	/**
	 * Check Sizegroups name via ajax
	 *
	 * @param      boolean  $cat_id  The cat identifier
	 */
	function ajx_exists($id = false)
	{
		// get Sizegroups name

		$name = $_REQUEST['name'];

		if ($this->is_valid_name($name, $id)) {

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
	function ajx_publish($id = 0)
	{
		// check access
		$this->check_access(PUBLISH);

		// prepare data
		$sizegroupoption_data = array('status' => 1);
		$this->load->model('Sizegroup_option');
		// save data
		if ($this->Sizegroup_option->save($sizegroupoption_data, $id)) {
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
	function ajx_unpublish($id = 0)
	{
		// check access
		$this->check_access(PUBLISH);

		// prepare data
		$sizegroupoption_data = array('status' => 0);
		$this->load->model('Sizegroup_option');

		// save data
		if ($this->Sizegroup_option->save($sizegroupoption_data, $id)) {
			echo 'true';
		} else {
			echo 'false';
		}
	}

	function addoption($id)
	{

		// breadcrumb urls
		$this->data['action_title'] = 'Size Group Options';

		// load user
		$this->data['sizegroup'] = $this->Sizegroups->get_one($id);

		// call the parent edit logic
		// load header
		$this->load_view('partials/header', $this->data);

		// load view
		$this->load_view('sizegroup/addoption', $this->data);

		// load footer
		$this->load_view('partials/footer');
	}
}
