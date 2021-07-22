<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for about table
 */
class ExchangeChatHistory extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct() 
	{
		parent::__construct( 'bs_exchange_chat_history', 'id', 'exchange_' );
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

		// Chat history id condition
		if ( isset( $conds['chat_id'] )) {
			$this->db->where( 'chat_id', $conds['chat_id'] );
		}

		// offered_item_id condition
		if ( isset( $conds['offered_item_id'] ) || (!empty($conds['offered_item_id'])) ) {
			if(is_array($conds['offered_item_id'])){
				$this->db->where_in( 'offered_item_id', $conds['offered_item_id'] );
			}else{
				$this->db->where( 'offered_item_id', $conds['offered_item_id'] );
			}
		}
	}
}