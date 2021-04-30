<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Downloads Controller
 */
class Ratings extends BE_Controller {

	/**
	 * Construt required variables
	 */
	function __construct() {

		parent::__construct( MODULE_CONTROL, 'RATINGS' );
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
		$conds['order_by_field'] = "overall_rating";
		$conds['order_by_type'] = "desc";

		$this->data['ratings'] = $this->User->get_all_by( $conds , $this->pag['per_page'], $this->uri->segment( 4 ) );

		$this->data['rows_count'] = count($this->data['ratings']);

		// load index logic
		parent::index();
	}

}