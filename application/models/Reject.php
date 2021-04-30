<?php
	defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for Reject table
 */
class Reject extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct() 
	{
		parent::__construct( 'bs_items', 'id', 'itm_' );
	}

	/**
	 * Implement the where clause
	 *
	 * @param      array  $conds  The conds
	 */
	function custom_conds( $conds = array())
	{
		
		// default where clause
		if (isset( $conds['status'] )) {
			$this->db->where( 'status', $conds['status'] );
		}
		
		// is_paid condition
		if (isset( $conds['is_paid'] )) {
			$this->db->where( 'is_paid', $conds['is_paid'] );
		}

		// order by
		if ( isset( $conds['order_by_field'] )) {
			$order_by_field = $conds['order_by_field'];
			$order_by_type = $conds['order_by_type'];
			
			$this->db->order_by( 'bs_items.'.$order_by_field, $order_by_type);
		} else {
			$this->db->order_by('added_date', 'desc' );
		}

		// id condition
		if ( isset( $conds['id'] )) {
			$this->db->where( 'id', $conds['id'] );
		}

		// title condition
		if ( isset( $conds['title'] )) {
			$this->db->where( 'title', $conds['title'] );
		}

		// id condition
		if ( isset( $conds['added_user_id'] )) {
			$this->db->where( 'added_user_id', $conds['added_user_id'] );
		}

		// category id condition
		if ( isset( $conds['cat_id'] )) {
			
			if ($conds['cat_id'] != "") {
				if($conds['cat_id'] != '0'){
					$this->db->where( 'cat_id', $conds['cat_id'] );	
				}

			}			
		}

		//  sub category id condition 
		if ( isset( $conds['sub_cat_id'] )) {
			
			if ($conds['sub_cat_id'] != "") {
				if($conds['sub_cat_id'] != '0'){
				
					$this->db->where( 'sub_cat_id', $conds['sub_cat_id'] );	
				}

			}			
		}

		// Type id
		if ( isset( $conds['item_type_id'] )) {
			
			if ($conds['item_type_id'] != "") {
				if($conds['item_type_id'] != '0'){
				
					$this->db->where( 'item_type_id', $conds['item_type_id'] );	
				}

			}			
		}
	  
		// Price id
		if ( isset( $conds['item_price_type_id'] )) {
			
			if ($conds['item_price_type_id'] != "") {
				if($conds['item_price_type_id'] != '0'){
				
					$this->db->where( 'item_price_type_id', $conds['item_price_type_id'] );	
				}

			}			
		}
	   
		// Currency id
		if ( isset( $conds['item_currency_id'] )) {
			
			if ($conds['item_currency_id'] != "") {
				if($conds['item_currency_id'] != '0'){
				
					$this->db->where( 'item_currency_id', $conds['item_currency_id'] );	
				}

			}			
		}

		// location id
		if ( isset( $conds['item_location_id'] )) {
			
			if ($conds['item_location_id'] != "") {
				if($conds['item_location_id'] != '0'){
				
					$this->db->where( 'item_location_id', $conds['item_location_id'] );	
				}

			}			
		}

		// condition_of_item id condition
		if ( isset( $conds['condition_of_item_id'] )) {
			$this->db->where( 'condition_of_item_id', $conds['condition_of_item_id'] );
		}

		// description condition
		if ( isset( $conds['description'] )) {
			$this->db->where( 'description', $conds['description'] );
		}

		// highlight_info condition
		if ( isset( $conds['highlight_info'] )) {
			$this->db->where( 'highlight_info', $conds['highlight_info'] );
		}

		// deal_option_id condition
		if ( isset( $conds['deal_option_id'] )) {
			$this->db->where( 'deal_option_id', $conds['deal_option_id'] );
		}

		// brand condition
		if ( isset( $conds['brand'] )) {
			$this->db->where( 'brand', $conds['brand'] );
		}

		// business_mode condition
		if ( isset( $conds['business_mode'] )) {
			$this->db->where( 'business_mode', $conds['business_mode'] );
		}

		// title condition
		if ( isset( $conds['title'] )) {
			$this->db->like( 'title', $conds['title'] );
		}

		// searchterm
		if ( isset( $conds['searchterm'] )) {
			$this->db->group_start();
			$this->db->like( 'title', $conds['searchterm'] );
			$this->db->or_like( 'description', $conds['searchterm'] );
			$this->db->or_like( 'condition_of_item_id', $conds['searchterm'] );
			$this->db->or_like( 'highlight_info', $conds['searchterm'] );
			$this->db->group_end();
		}

		if( isset($conds['max_price']) ) {
			if( $conds['max_price'] != 0 ) {
				$this->db->where( 'price <=', $conds['max_price'] );
			}	

		}

		if( isset($conds['min_price']) ) {

			if( $conds['min_price'] != 0 ) {
				$this->db->where( 'price >=', $conds['min_price'] );
			}

		}

		
	}

}