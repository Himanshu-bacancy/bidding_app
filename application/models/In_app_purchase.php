<?php
	defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for Item table
 */
class In_app_purchase extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct() 
	{
		parent::__construct( 'bs_app_purchase', 'id', 'in_app_purchase_prd' );
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
          
		// id condition
		if ( isset( $conds['id'] )) {
			$this->db->where( 'id', $conds['id'] );
		}

		// in_app_purchase_prd_id
		if ( isset($conds['in_app_purchase_prd_id'])) {
			$this->db->where( 'in_app_purchase_prd_id' , $conds['in_app_purchase_prd_id']);
		}

        // day condition
		if ( isset( $conds['day'] )) {
			$this->db->like( 'day', $conds['day'] );
		}
        
        // description condition
		if ( isset( $conds['description'] )) {
			$this->db->where( 'description', $conds['description'] );
		}

		 // type condition
		if ( isset( $conds['type'] )) {
			$this->db->where( 'type', $conds['type'] );
		}
		//added_date
		if(isset($conds['added_date'])){
			$this->db->where('added_date',$conds['added_date']);
		}

		// added_user_id condition
		if ( isset( $conds['added_user_id'] )) {
			$this->db->where( 'added_user_id', $conds['added_user_id'] );
		}

		//updated_date
		if(isset($conds['updated_date'])){
			$this->db->where('updated_date',$conds['updated_date']);
		}		
	  
		// condition_of_item id condition
		if ( isset( $conds['updated_user_id'] )) {
			$this->db->where( 'updated_user_id', $conds['updated_user_id'] );
		}

		// searchterm
		if ( isset( $conds['searchterm'] )) {
			$this->db->group_start();
			$this->db->or_like( 'in_app_purchase_prd_id', $conds['searchterm'] );
			$this->db->group_end();
		}
		
	}

}
?>