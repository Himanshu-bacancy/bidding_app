<?php
	defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for Item table
 */
class Item extends PS_Model {

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
			$this->db->where( 'bs_items.status', $conds['status'] );
		}

		// no_publish_filter where clause
		if (isset( $conds['no_publish_filter'] )) {
			$this->db->where( 'bs_items.no_publish_filter', $conds['no_publish_filter'] );
		}
		
		// is_paid condition
		if (!empty( $conds['is_paid'] )) {
			$this->db->where( 'bs_items.is_paid', $conds['is_paid'] );
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
			
			$this->db->order_by( 'bs_items.'.$order_by_field, $order_by_type);
		} else {
			$this->db->order_by('added_date', 'desc' );
		}

		// id condition
		if ( isset( $conds['id'] )) {
			$this->db->where( 'bs_items.id', $conds['id'] );
		}

		// title condition
		if ( isset( $conds['title'] )) {
			$this->db->where( 'bs_items.title', $conds['title'] );
		}

		// id condition
		if ( isset( $conds['added_user_id'] )) {
			$this->db->where( 'bs_items.added_user_id', $conds['added_user_id'] );
		}

		// category id condition
		if ( isset( $conds['cat_id'] )) {
			
			if ($conds['cat_id'] != "") {
				if($conds['cat_id'] != '0'){
					$this->db->where( 'bs_items.cat_id', $conds['cat_id'] );	
				}

			}			
		}

		//  sub category id condition 
		if ( isset( $conds['sub_cat_id'] )) {
			
			if ($conds['sub_cat_id'] != "") {
				if($conds['sub_cat_id'] != '0'){
				
					$this->db->where( 'bs_items.sub_cat_id', $conds['sub_cat_id'] );	
				}

			}			
		}

		//  delivery method id condition 
		if ( isset( $conds['delivery_method_id'] )) {
			
			if ($conds['delivery_method_id'] != "") {
				if($conds['delivery_method_id'] != '0'){
				
					$this->db->where( 'bs_items.delivery_method_id', $conds['delivery_method_id'] );	
				}

			}			
		}

		//  child sub category id condition 
		if ( isset( $conds['childsubcat_id'] )) {
			
			if ($conds['childsubcat_id'] != "") {
				if($conds['childsubcat_id'] != '0'){
				
					$this->db->where( 'bs_items.childsubcat_id', $conds['childsubcat_id'] );	
				}

			}			
		}

		//  sizegroup id condition 
		if ( isset( $conds['sizegroup_id'] )) {
			
			if ($conds['sizegroup_id'] != "") {
				if($conds['sizegroup_id'] != '0'){
				
					$this->db->where( 'bs_items.sizegroup_id', $conds['sizegroup_id'] );	
				}

			}			
		}

		// //  sizegroupoption id condition 
		if ( isset( $conds['sizegroupoption_id'] )) {
			
			if ($conds['sizegroupoption_id'] != "") {
				if($conds['sizegroupoption_id'] != '0'){
				
					$this->db->select('*');
					$this->db->from('bs_item_sizegroupoptions');
					$this->db->where( 'bs_items.sizegroup_option_id', $conds['sizegroupoption_id'] );

				}

			}			
		}

		

		// Type id
		if ( isset( $conds['item_type_id'] )) {
			if ($conds['item_type_id'] != "" && !is_array($conds['item_type_id'])) {
				if($conds['item_type_id'] != '0'){
				
					$this->db->where( 'bs_items.item_type_id', $conds['item_type_id'] );	
				}

			}			
		}
	  
		// Price id
		if ( isset( $conds['item_price_type_id'] )) {
			
			if ($conds['item_price_type_id'] != "") {
				if($conds['item_price_type_id'] != '0'){
				
					$this->db->where( 'bs_items.item_price_type_id', $conds['item_price_type_id'] );	
				}

			}			
		}
	   
		// Currency id
		if ( isset( $conds['item_currency_id'] )) {
			
			if ($conds['item_currency_id'] != "") {
				if($conds['item_currency_id'] != '0'){
				
					$this->db->where( 'bs_items.item_currency_id', $conds['item_currency_id'] );	
				}

			}			
		}

		// location id
		if ( isset( $conds['item_location_id'] )) {
			
			if ($conds['item_location_id'] != "") {
				if($conds['item_location_id'] != '0'){
				
					$this->db->where( 'bs_items.item_location_id', $conds['item_location_id'] );	
				}

			}			
		}

		// condition_of_item id condition
		if ( isset( $conds['condition_of_item_id'] )) {
			$this->db->where( 'bs_items.condition_of_item_id', $conds['condition_of_item_id'] );
		}

		// description condition
		if ( isset( $conds['description'] )) {
			$this->db->where( 'bs_items.description', $conds['description'] );
		}

		// highlight_info condition
		if ( isset( $conds['highlight_info'] )) {
			$this->db->where( 'bs_items.highlight_info', $conds['highlight_info'] );
		}

		// deal_option_id condition
		if ( isset( $conds['deal_option_id'] )) {
			$this->db->where( 'bs_items.deal_option_id', $conds['deal_option_id'] );
		}

		// brand condition
		if ( isset( $conds['brand'] )) {
			$this->db->where( 'bs_items.brand', $conds['brand'] );
		}

		// business_mode condition
		if ( isset( $conds['business_mode'] )) {
			$this->db->where( 'bs_items.business_mode', $conds['business_mode'] );
		}


		// business_mode condition
		if ( isset( $conds['is_confirm'] )) {
			$this->db->where( 'bs_items.is_confirm', $conds['is_confirm'] );
		}

		// business_mode condition
		if ( isset( $conds['is_confirm_with_seller'] )) {
			$this->db->where( 'bs_items.is_confirm_with_seller', $conds['is_confirm_with_seller'] );
		}

		// business_mode condition
		if ( isset( $conds['is_exchange'] )) {
			$this->db->where( 'bs_items.is_exchange', $conds['is_exchange'] );
		}


		// business_mode condition
		if ( isset( $conds['is_accept_similar'] )) {
			$this->db->where( 'bs_items.is_accept_similar', $conds['is_accept_similar'] );
		}

		// business_mode condition
		if ( isset( $conds['is_sold_out'] )) {
			$this->db->where( 'bs_items.is_sold_out', $conds['is_sold_out'] );
		}


		// title condition
		if ( isset( $conds['title'] )) {
			$this->db->where( 'bs_items.title', $conds['title'] );
		}

		// payment_type condition
		if ( isset( $conds['payment_type'] )) {
			$this->db->like( 'payment_type', $conds['payment_type'] );
		}

		// is_paid condition
		if ( !empty( $conds['is_paid'] )) {
			$this->db->like( 'is_paid', $conds['is_paid'] );
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
				$this->db->where( 'bs_items.price <=', $conds['max_price'] );
			}	

		}

		if( isset($conds['min_price']) ) {

			if( $conds['min_price'] != 0 ) {
				$this->db->where( 'bs_items.price >=', $conds['min_price'] );
			}

		}

		
	}

}