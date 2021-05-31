<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for Shippingcarriers table
 */
class Shippingcarriers extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct() 
	{
		parent::__construct( 'bs_shippingcarriers', 'id', 'shcr_' );
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

		// id condition
		if ( isset( $conds['id'] )) {
			$this->db->where( 'id', $conds['id'] );
		}

		// name condition
		if ( isset( $conds['name'] )) {
			$this->db->where( 'name', $conds['name'] );
		}

		if ( isset( $conds['min_days'] )) {
			$this->db->where( 'min_days', $conds['min_days'] );
		}

		if ( isset( $conds['price'] )) {
			$this->db->where( 'price', $conds['price'] );
		}

		if ( isset( $conds['max_days'] )) {
			$this->db->where( 'max_days', $conds['max_days'] );
		}

		// packagesize id condition
		if ( isset( $conds['packagesize_id'] )) {
			
			if ($conds['packagesize_id'] != "" || $conds['packagesize_id'] != 0) {
				
				$this->db->where( 'packagesize_id', $conds['packagesize_id'] );	

			}			
		}

		

		// searchterm
		if ( isset( $conds['searchterm'] )) {
			if ($conds['searchterm'] != "") 
			{
				$this->db->group_start();
				$this->db->like( 'name', $conds['searchterm'] );
				$this->db->or_like( 'min_days', $conds['searchterm'] );
				$this->db->or_like( 'price', $conds['searchterm'] );
				$this->db->or_like( 'max_days', $conds['searchterm'] );
				$this->db->group_end();
			}
			
		}

		// order_by
		if ( isset( $conds['order_by'] )) {

			$order_by_field = $conds['order_by_field'];
			$order_by_type = $conds['order_by_type'];

			$this->db->order_by( 'bs_shippingcarriers.'.$order_by_field, $order_by_type );
		} else {

			$this->db->order_by( 'added_date' );
		}
		
	}
}