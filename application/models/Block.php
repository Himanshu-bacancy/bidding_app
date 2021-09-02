<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for Userfollow table
 */
class Block extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct() 
	{
		parent::__construct( 'bs_blocks', 'id', 'itm_b' );
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

		// from_block_user_id condition
		if ( isset( $conds['from_block_user_id'] )) {
			$this->db->where( 'from_block_user_id', $conds['from_block_user_id'] );
		}

		// to_block_user_id
		if ( isset( $conds['to_block_user_id'] )) {
			$this->db->like( 'to_block_user_id', $conds['to_block_user_id'] );
		}

		

		$this->db->order_by( 'added_date' );
		
	}
}