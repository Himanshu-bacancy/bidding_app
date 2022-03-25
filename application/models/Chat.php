<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for about table
 */
class Chat extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct() 
	{
		parent::__construct( 'bs_chat_history', 'id', 'chat_' );
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

		// requested_item_id condition
		if ( isset( $conds['requested_item_id'] )) {
			$this->db->where( 'requested_item_id', $conds['requested_item_id'] );
		}

		// offered_item_id condition
		if ( isset( $conds['offered_item_id'] )) {
			$this->db->where( 'offered_item_id', $conds['offered_item_id'] );
		}

		// buyer_user_id condition
		if ( isset( $conds['buyer_user_id'] )) {
			$this->db->where( 'buyer_user_id', $conds['buyer_user_id'] );
		}

		// seller_user_id condition
		if ( isset( $conds['seller_user_id'] )) {
			$this->db->where( 'seller_user_id', $conds['seller_user_id'] );
		}

		// buyer_unread_count condition
		if ( isset( $conds['buyer_unread_count'] )) {
			$this->db->where( 'buyer_unread_count', $conds['buyer_unread_count'] );
		}

		// seller_unread_count condition
		if ( isset( $conds['seller_unread_count'] )) {
			$this->db->where( 'seller_unread_count', $conds['seller_unread_count'] );
		}

		// nego_price condition
		if ( isset( $conds['nego_price'] )) {
			$this->db->where( 'nego_price', $conds['nego_price'] );
		}

		// size_id condition
		if ( isset( $conds['size_id'] )) {
			$this->db->where( 'size_id', $conds['size_id'] );
		}

		// color_id condition
		if ( isset( $conds['color_id'] )) {
			$this->db->where( 'color_id', $conds['color_id'] );
		}

		// quantity condition
		if ( isset( $conds['quantity'] )) {
			$this->db->where( 'quantity', $conds['quantity'] );
		}

		// operation type condition
		if ( isset( $conds['operation_type'] )) {
			$this->db->where( 'operation_type', $conds['operation_type'] );
		}
		// operation type condition
		if ( isset( $conds['is_offer'] )) {
			$this->db->where( 'is_offer', $conds['is_offer'] );
		}
		// operation type condition
		if ( isset( $conds['is_expired'] )) {
			$this->db->where( 'is_expired', $conds['is_expired'] );
		}
		
	}
}