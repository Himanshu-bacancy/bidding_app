<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Payouts Controller
 */
class Payouts extends BE_Controller {

	/**
	 * Construct required variables
	 */
	function __construct() {

		parent::__construct( MODULE_CONTROL, 'Payouts' );
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
	 * List down the payouts
	 */
	function index() {
		
		// no publish filter
		$conds['no_publish_filter'] = 1;
		$conds['order_by'] = 1;
		$conds['order_by_field'] = "created_at";
		$conds['order_by_type'] = "desc";
		// get rows count
        
		$this->data['rows_count'] = $this->Payout->count_all_by( $conds );
		// get payouts
		$this->data['payouts'] = $this->Payout->get_all_by( $conds , $this->pag['per_page'], $this->uri->segment( 4 ) );
		// load index logic
		parent::index();
	}

	/**
	 * Searches for the first match.
	 */
	function search() {
		

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'payout_search' );
		// condition with search term
        if($this->searchterm_handler($this->input->post('user_filter')) ) {
            $conds['filter_user_id'] = $this->input->post('user_filter');
        }
		// no publish filter
		$conds['no_publish_filter'] = 1;
		$conds['order_by'] = 1;
		$conds['order_by_field'] = "created_at";
		$conds['order_by_type'] = "desc";

		// pagination
		$this->data['rows_count'] = $this->Payout->count_all_by( $conds );
		// search data
		$this->data['payouts'] = $this->Payout->get_all_by( $conds, $this->pag['per_page'], $this->uri->segment( 4 ) );
		// load add list
		parent::search();
	}
}