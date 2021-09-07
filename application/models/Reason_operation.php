<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for Reason table
 */
class Reason_operation extends PS_Model {

	/**Reason_operations
	 * Constructs the required data
	 */
	function __construct() 
	{	
		parent::__construct( 'bs_reason_operation', 'id');
	}

	/**
	 * Implement the where clause
	 *
	 * @param      array  $conds  The conds
	 */
	function custom_conds( $conds = array())
	{
		// id condition
		if ( isset( $conds['id'] )) {
			$this->db->where( 'id', $conds['id'] );
		}

		// reason_id condition
		if ( isset( $conds['reason_id'] )) {
			$this->db->where( 'reason_id', $conds['reason_id'] );
		}


		// type condition
		if ( isset( $conds['type'] )) {
			$this->db->where( 'type', $conds['type'] );
		}
		
		// operation_id
		if ( isset( $conds['operation_id'] )) {
			$this->db->like( 'operation_id', $conds['operation_id'] );
		}

		// other_reason
		if ( isset( $conds['other_reason'] )) {
			$this->db->like( 'other_reason', $conds['other_reason'] );
		}

		// user_id
		if ( isset( $conds['user_id'] )) {
			$this->db->like( 'user_id', $conds['user_id'] );
		}
		// order_by
		if ( isset( $conds['order_by'] )) {

			$order_by_field = $conds['order_by_field'];
			$order_by_type = $conds['order_by_type'];

			$this->db->order_by( 'bs_reason_operation.'.$order_by_field, $order_by_type );
		} else {

			$this->db->order_by( 'added_date' );
		}
	}


	function count_all_reported_items_for_today(){
		
		$this->db->from('bs_reason_operation');
		$date = date('Y-m-d');
		
		$this->db->where('DATE(added_date)', $date);
		$this->db->where('type', 'report_item');
		// return the count all results
		//echo '<pre>'; print_r($this->db->result()); die('hello testing');
		return $this->db->count_all_results();
		//print_r($this->db->last_query());die;
	}
}