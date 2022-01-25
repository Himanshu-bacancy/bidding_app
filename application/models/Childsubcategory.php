<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for category table
 */
class Childsubcategory extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct() 
	{
		parent::__construct( 'bs_childsubcategories', 'id', 'childsubcat' );
	}

	/**
	 * Implement the where clause
	 *
	 * @param      array  $conds  The conds
	 */
	function custom_conds( $conds = array())
	{
		//echo '<pre>'; print_r($conds); die;
		// default where clause
		if ( !isset( $conds['no_publish_filter'] )) {
			$this->db->where( 'status', 1 );
		}

		// category id condition
		if ( isset( $conds['cat_id'] )) {
			
			if ($conds['cat_id'] != "" || $conds['cat_id'] != 0) {
				
				$this->db->where( 'cat_id', $conds['cat_id'] );	

			}			
		}

		//sub category id condition
		if ( isset( $conds['sub_cat_id'] )) {
			
			if ($conds['sub_cat_id'] != "" || $conds['sub_cat_id'] != 0) {
				
				$this->db->where( 'sub_cat_id', $conds['sub_cat_id'] );	

			}			
		}

		// sub category id condition
		if ( isset( $conds['id'] )) {
			$this->db->where( 'id', $conds['id'] );	
		}

		// sub cat_name condition
		if ( isset( $conds['name'] )) {
			$this->db->where( 'name', $conds['name'] );
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
        if ( isset( $conds['order_by'] )) {

			$order_by_field = $conds['order_by_field'];
			$order_by_type = $conds['order_by_type'];

			$this->db->order_by( 'bs_childsubcategories.'.$order_by_field, $order_by_type );
		} else {

			$this->db->order_by( 'added_date' );
        }
	}


	function getSelectedSizegroups($childSubCatId = NULL){
		if(empty($childSubCatId)) return false;
		$this->load->model('Childsubcategory_sizegroup');
		$conds['child_subcategory_id'] = $childSubCatId;
		$selectedSizeGroup = $this->Childsubcategory_sizegroup->get_all_by($conds);
		$selectedData = [];
		foreach($selectedSizeGroup->result() as $sizegroupIds){
			$selectedData[] = $sizegroupIds->sizegroup_id;
		}
		return $selectedData;
	}
}
	