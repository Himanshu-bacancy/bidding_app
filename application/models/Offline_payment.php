<?php
	defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for Item table
 */
class Offline_payment extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct() 
	{
		parent::__construct( 'bs_offline_payment', 'id', 'offline_payment' );
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

        // title condition
		if ( isset( $conds['title'] )) {
			$this->db->like( 'title', $conds['title'] );
		}
 
        // description condition
		if ( isset( $conds['description'] )) {
			$this->db->where( 'description', $conds['description'] );
		}
		//added_date
		if(isset($conds['added_date'])){
			$this->db->where('added_date',$conds['added_date']);
		}
		if(isset($conds['status'])){
			$this->db->where('status',$conds['status']);
		}

		// id condition
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
			$this->db->like( 'title', $conds['searchterm'] );
			$this->db->or_like( 'description', $conds['searchterm'] );
			$this->db->group_end();
		}
         

		
	}

}
?>