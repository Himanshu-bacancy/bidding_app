<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for Packagesizes table
 */
class Packagesizes extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct() 
	{
		parent::__construct( 'bs_packagesizes', 'id', 'pkg_' );
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

		if ( isset( $conds['length'] )) {
			$this->db->where( 'length', $conds['length'] );
		}

		if ( isset( $conds['width'] )) {
			$this->db->where( 'width', $conds['width'] );
		}

		if ( isset( $conds['height'] )) {
			$this->db->where( 'height', $conds['height'] );
		}

		if ( isset( $conds['weight'] )) {
			$this->db->where( 'weight', $conds['weight'] );
		}

		// searchterm
		if ( isset( $conds['searchterm'] )) {
			$this->db->like( 'name', $conds['searchterm'] );
			$this->db->or_like( 'length', $conds['searchterm'] );
			$this->db->or_like( 'width', $conds['searchterm'] );
			$this->db->or_like( 'height', $conds['searchterm'] );
			$this->db->or_like( 'weight', $conds['searchterm'] );
		}

		// order_by
		if ( isset( $conds['order_by'] )) {

			$order_by_field = $conds['order_by_field'];
			$order_by_type = $conds['order_by_type'];

			$this->db->order_by( 'bs_packagesizes.'.$order_by_field, $order_by_type );
		} else {

			$this->db->order_by( 'added_date' );
		}
	}
}