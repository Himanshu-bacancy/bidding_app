<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for category table
 */
class Childsubcategory_sizegroup extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct()
    {
        parent::__construct( 'bs_childsubcategory_sizegroups', 'id' );
	}

	/**
	 * Implement the where clause
	 *
	 * @param      array  $conds  The conds
	 */
	function custom_conds( $conds = array())
	{
		
		// sub category id condition
		if ( isset( $conds['child_subcategory_id'] )) {
			
			if ($conds['child_subcategory_id'] != "" || $conds['child_subcategory_id'] != 0) {
				
				$this->db->where( 'child_subcategory_id', $conds['child_subcategory_id'] );	

			}			
		}

		// sub category id condition
		if ( isset( $conds['id'] )) {
			$this->db->where( 'id', $conds['id'] );	
		}

		// sub cat_name condition
		if ( isset( $conds['sizegroup_id'] )) {
			$this->db->where( 'sizegroup_id', $conds['sizegroup_id'] );
		}

		// search_term
		if ( isset( $conds['searchterm'] )) {
			
			if ($conds['searchterm'] != "") {
				$this->db->group_start();
				$this->db->like( 'name', $conds['searchterm'] );
				$this->db->or_like( 'name', $conds['searchterm'] );
				$this->db->group_end();
			}
			
			}

		$this->db->order_by( 'added_date', 'desc' );

	}
}
	