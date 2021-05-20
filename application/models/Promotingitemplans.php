<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for Promotingitemplan table
 */
class Promotingitemplans extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct() 
	{
		parent::__construct( 'bs_promotingitemplans', 'id', '' );
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

		if ( isset( $conds['code'] )) {
			$this->db->where( 'code', $conds['code'] );
		}

		if ( isset( $conds['price'] )) {
			$this->db->where( 'price', $conds['price'] );
		}

		if ( isset( $conds['days'] )) {
			$this->db->where( 'days', $conds['days'] );
		}

		// searchterm
		if ( isset( $conds['searchterm'] )) {
			$this->db->like( 'name', $conds['searchterm'] );
			$this->db->or_like( 'code', $conds['searchterm'] );
			$this->db->or_like( 'price', $conds['searchterm'] );
			$this->db->or_like( 'days', $conds['searchterm'] );
		}

		// order_by
		if ( isset( $conds['order_by'] )) {

			$order_by_field = $conds['order_by_field'];
			$order_by_type = $conds['order_by_type'];

			$this->db->order_by( 'bs_promotingitemplans.'.$order_by_field, $order_by_type );
		} else {

			$this->db->order_by( 'added_date' );
		}
	}
}