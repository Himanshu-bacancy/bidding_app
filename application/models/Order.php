<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for api table
 */
class Order extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct() 
	{
		parent::__construct( 'bs_order', 'ord_id', 'ord' );
	}

	/**
	 * Implement the where clause
	 *
	 * @param      array  $conds  The conds
	 */
	function custom_conds( $conds = array())
	{
        // id condition
		if ( isset( $conds['is_return'] )) {
			$this->db->where( 'is_return', $conds['is_return'] );
		}
		
		// order by
		// post parameter "order_by" : "fieldname"
		// post parameter "order_type" : "asc/desc"
		// -- For Low to high : "order_by" : "price" & "order_type" : "asc"
		// -- For High to low : "order_by" : "price" & "order_type" : "desc"
		// -- For Latest : "order_by" : "added_date" & "order_type" : "desc"
		// -- For Popular : "order_by" : "touch_count" & "order_type" : "desc"
		if ( isset( $conds['order_by_field'] )) {
			$order_by_field = $conds['order_by_field'];
			$order_by_type = $conds['order_by_type'];
			
			$this->db->order_by( 'bs_order.'.$order_by_field, $order_by_type);
		} else {
			$this->db->order_by('created_at', 'desc' );
		}
	}
	
}