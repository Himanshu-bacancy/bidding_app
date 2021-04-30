<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for category table
 */
class Category extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct() 
	{
		parent::__construct( 'bs_categories', 'cat_id', 'cat' );
	}

	/**
	 * Implement the where clause
	 *
	 * @param      array  $conds  The conds
	 */
	function custom_conds( $conds = array())
	{
		// default where clause
		if ( !isset( $conds['no_publish_filter'] )) {
			$this->db->where( 'status', 1 );
		}


		// cat_id condition
		if ( isset( $conds['cat_id'] )) {
			$this->db->where( 'cat_id', $conds['cat_id'] );
		}

		// cat_name condition
		if ( isset( $conds['cat_name'] )) {
			$this->db->where( 'cat_name', $conds['cat_name'] );
		}

		// searchterm
		if ( isset( $conds['searchterm'] )) {
			$this->db->like( 'cat_name', $conds['searchterm'] );
		}

		// order_by
		if ( isset( $conds['order_by'] )) {

			$order_by_field = $conds['order_by_field'];
			$order_by_type = $conds['order_by_type'];

			$this->db->order_by( 'bs_categories.'.$order_by_field, $order_by_type );
		} else {

			$this->db->order_by( 'added_date' );
		}
	}
}