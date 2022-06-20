<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Item Itemreport Controller
 */
class Reporteditems extends BE_Controller {

	/**
	 * Construt required variables
	 */
	function __construct() {

		parent::__construct( MODULE_CONTROL, 'Reporteditems' );
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
        $conds['order_by_field'] = "added_date";
		$conds['order_by_type'] = "desc";
		$conds['type'] = "report_item";
		// get rows count
		$this->data['rows_count'] = $this->Reason_operation->count_all_by( $conds );
		
		// get Item reports
		$this->data['itemreport'] = $this->Reason_operation->get_all_by( $conds , $this->pag['per_page'], $this->uri->segment( 4 ) );
		// load index logic
		parent::index();
	}

	/**
	 * Searches for the first match.
	 */
	function search() {
		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'Item filter' );
        
		// no publish filter
		$conds['order_by_field'] = "added_date";
		$conds['order_by_type'] = "desc";
        
        $conds['searchterm'] = $this->input->post('searchterm');
        
        $result = $this->db->select('bs_reason_operations.*')->from('bs_reason_operations')
                ->join('bs_reasons', 'bs_reason_operations.reason_id = bs_reasons.id', 'left')
                ->where('bs_reason_operations.type', 'report_item');
        
        if(isset($conds['searchterm']) && !empty($conds['searchterm'])) {
            $result = $result->group_start()->like( 'bs_reasons.name', $conds['searchterm'] )->or_like( 'bs_reason_operations.other_reason', $conds['searchterm'] )->group_end();
            
            $this->data['searchterm'] = $this->input->post('searchterm');
        }
        if($this->input->post('status_dd')) {
            $where = 'LOWER(TRIM(bs_reason_operations.status)) = "'.$this->input->post('status_dd').'"';
            $result = $result->where($where);
            
            $this->data['status_dd'] = $this->input->post('status_dd');
        }
        if(!empty($this->input->post('reservation'))) {
            $daterange = explode(' - ', $this->input->post('reservation'));
            $where = 'DATE(bs_reason_operations.added_date) BETWEEN "'.date('Y-m-d', strtotime($daterange[0])).'" AND "'.date('Y-m-d', strtotime($daterange[1])).'"';
            $result = $result->where($where);
            $this->data['reservation'] = $this->input->post('reservation');
        }
        $store_for_count = $result->get_compiled_select();
//        dd($store_for_count);
        $count = $this->db->query($store_for_count)->num_rows();

		if($this->pag['per_page']) {
            $store_for_count .= " LIMIT ".$this->pag['per_page'];
        }
        if($this->uri->segment( 4 )) {
            $store_for_count .= ", ".$this->uri->segment( 4 );
        }
        $query_result = $this->db->query($store_for_count);
        
        $this->data['rows_count'] = $count;
        
        $this->data['itemreport'] = $query_result;
		
		// load add list
		parent::search();
	}
    
    function detail($con_id)
	{
        $this->data['action_title'] = get_msg( 'update reporting item status' );
		$detail = $this->Reason_operation->get_one( $con_id );
		$this->data['detail'] = $detail;
		$this->load_detail( $this->data );
	}
    
    function changestatus()
	{
        $record_id = $this->input->post('record_id');
        $changestatus = $this->input->post('changestatus');
        $update_data['status'] = $changestatus;
        $update_data['updated_at'] =  date('Y-m-d H:i:s');
        $this->db->where('id', $record_id)->update('bs_reason_operations',$update_data);
        $this->set_flash_msg( 'success', get_msg( 'status updated' ));
		redirect( $this->module_site_url());
	}
}