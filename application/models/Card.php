<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for about table
 */
class Card extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct() 
	{
		parent::__construct( 'bs_card', 'id' );
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

		// user_id condition
		if ( isset( $conds['user_id'] )) {
			$this->db->where( 'user_id', $conds['user_id'] );
		}

		// card_holder_name condition
		if ( isset( $conds['card_holder_name'] )) {
			$this->db->where( 'card_holder_name', $conds['card_holder_name'] );
		}

		// card_number condition
		if ( isset( $conds['card_number'] )) {
			$this->db->where( 'card_number', $conds['card_number'] );
		}

		// expiry_date condition
		if ( isset( $conds['expiry_date'] )) {
			$this->db->where( 'expiry_date', $conds['expiry_date'] );
		}

		// card_type condition
		if ( isset( $conds['card_type'] )) {
			$this->db->where( 'card_type', $conds['card_type'] );
		}

		// address_id condition
		if ( isset( $conds['address_id'] )) {
			$this->db->where( 'address_id', $conds['address_id'] );
		}

		// status condition
		if ( isset( $conds['status'] )) {
			$this->db->where( 'status', $conds['status'] );
		}
		
	}
}