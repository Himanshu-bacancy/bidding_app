<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Empty Class
 */
class My_Model {}

/**
 * PanaceaSoft Base Model
 */
class PS_Model extends CI_Model {
	
	// name of the database table
	protected $table_name;

	// name of the ID field
	public $primary_key;

	// name of the key prefix
	protected $key_prefix;

	/**
	 * constructs required data
	 */
	function __construct( $table_name, $primary_key = false, $key_prefix = false )
	{
		parent::__construct();

		// set the table name
		$this->table_name = $table_name;
		$this->primary_key = $primary_key;
		$this->key_prefix = $key_prefix;
	}

	/**
	 * Empty class to be extended
	 *
	 * @param      array  $conds  The conds
	 */
	function custom_conds( $conds = array()) {

	}

	/**
	 * Generate the TeamPS Unique Key
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	function generate_key()
	{
		return $this->key_prefix . md5( $this->key_prefix . microtime() . uniqid() . 'teamps' );
	}

    /**
     * Determines if exist.
     *
     * @param      <type>   $id     The identifier
     *
     * @return     boolean  True if exist, False otherwise.
     */
    function is_exist( $id ) {
    	
    	// from table
    	$this->db->from( $this->table_name );

    	// where clause
		$this->db->where( $this->primary_key, $id );
		
		// get query
		$query = $this->db->get();

		// return the result
		return ($query->num_rows()==1);
    }

    /**
     * Save the data if id is not existed
     *
     * @param      <type>   $data   The data
     * @param      boolean  $id     The identifier
     */
	function save( &$data, $id = false ) {
		//print_r($data);die;
		if ( !$id ) {
		// if id is not false and id is not yet existed,
			if ( !empty( $this->primary_key ) && !empty( $this->key_prefix )) {
			// if the primary key and key prefix is existed,
			
				// generate the unique key
				$data[ $this->primary_key ] = $this->generate_key();
			}

			// insert the data as new record
			return $this->db->insert( $this->table_name, $data );

			  // print_r($this->db->last_query());die;
		} else {
		// else
			// where clause
			$this->db->where( $this->primary_key, $id);

			// update the data
			return $this->db->update($this->table_name,$data);
			//print_r($this->db->last_query());die;
		}
	}

	/**
	 * Returns all the records
	 *
	 * @param      boolean  $limit   The limit
	 * @param      boolean  $offset  The offset
	 */
	function get_all( $limit = false, $offset = false ) {
        
		// where clause
		$this->custom_conds();

		// from table
		$this->db->from($this->table_name);


		if ( $limit ) {
		// if there is limit, set the limit
			
			$this->db->limit($limit);
		}
		
		if ( $offset ) {
		// if there is offset, set the offset,
			
			$this->db->offset($offset);
		}
		return $this->db->get();
		//print_r($this->db->last_query());die;
	}

	/**
	 * Returns the total count
	 */
	function count_all() {
		// from table
		$this->db->from( $this->table_name );

		// where clause
		$this->custom_conds();

		// return the count all results
		return $this->db->count_all_results();
		// print_r($this->db->last_query());die;
	}

	/**
	 * Return the info by Id
	 *
	 * @param      <type>  $id     The identifier
	 */
	function get_one( $id ) {
		// query the record
		$query = $this->db->get_where( $this->table_name, array( $this->primary_key => $id ));
		if ( $query->num_rows() == 1 ) {
		// if there is one row, return the record
			
			return $query->row();
		} else {
		// if there is no row or more than one, return the empty object
			
			return $this->get_empty_object( $this->table_name );
		}
	}

	/**
	 * Returns the multiple Info by Id
	 *
	 * @param      array  $ids    The identifiers
	 */
	function get_multi_info( $ids = array()) {
		
		// from table
		$this->db->from( $this->table_name );

		// where clause
		$this->db->where_in( $this->primary_key, $ids );

		// returns
		return $this->db->get();
	}

	/**
	 * Delete the records by Id
	 *
	 * @param      <type>  $id     The identifier
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	function delete( $id )
	{
		
		// where clause
		$this->db->where( $this->primary_key, $id );

		// delete the record
		return $this->db->delete( $this->table_name );
 	}

 	/**
 	 * Delete the records by ids
 	 *
 	 * @param      array   $ids    The identifiers
 	 *
 	 * @return     <type>  ( description_of_the_return_value )
 	 */
 	function delete_list( $ids = array()) {
 		
 		// where clause
		$this->db->where_in( $this->primary_key, $id );

		// delete the record
		return $this->db->delete( $this->table_name );
 	}

	/**
	 * returns the object with the properties of the table
	 *
	 * @return     stdClass  The empty object.
	 */
    function get_empty_object()
    {   
        $obj = new stdClass();
        
        $fields = $this->db->list_fields( $this->table_name );
        foreach ( $fields as $field ) {
            $obj->$field = '';
        }
        $obj->is_empty_object = true;
        return $obj;
    }

   	/**
   	 * Execute The query
   	 *
   	 * @param      <type>   $sql     The sql
   	 * @param      <type>   $params  The parameters
   	 *
   	 * @return     boolean  ( description_of_the_return_value )
   	 */
	function exec_sql( $sql, $params = false )
	{
		if ( $params ) {
		// if the parameter is not false

			// bind the parameter and run the query
			return $this->db->query( $sql, $params );	
		}

		// if there is no parameter,
		return $this->db->query( $sql );
	}

	/**
	 * Implement the where clause
	 *
	 * @param      array  $conds  The conds
	 */
	function conditions( $conds = array())
	{
		// if condition is empty, return true
		if ( empty( $conds )) return true;
	}

	/**
	 * Check if the key is existed,
	 *
	 * @param      array   $conds  The conds
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	function exists( $conds = array()) {

		// where clause
		$this->custom_conds( $conds );
		
		// from table
		$this->db->from( $this->table_name );

		// get query
		$query = $this->db->get();

		// return the result
		return ($query->num_rows() == 1);
		// print_r($this->db->last_query());die;
	}

	/**
	 * Gets all by the conditions
	 *
	 * @param      array    $conds   The conds
	 * @param      boolean  $limit   The limit
	 * @param      boolean  $offset  The offset
	 *
	 * @return     <type>   All by.
	 */
	function get_all_by( $conds = array(), $limit = false, $offset = false ) {

		if($conds['lat'] != "" && $conds['lng'] != "") {
			$this->db->select('*,( 3959
		      * acos( cos( radians('. $conds['lat'] .') )
		              * cos(  radians( lat )   )
		              * cos(  radians( lng ) - radians('. $conds['lng'] .') )
		            + sin( radians('. $conds['lat'] .') )
		              * sin( radians( lat ) )
		            )
		    ) as distance');

		    if ($conds['miles'] == "") {
		    	$conds['miles'] = 0;
		    	$this->db->having('distance < ' .  $conds['miles'] );
		    } else {
		    	$this->db->having('distance < ' .  $conds['miles'] );

		    }

		   
		}

		// where clause
		$this->custom_conds( $conds );

		// from table
		$this->db->from( $this->table_name );

		if ( $limit ) {
		// if there is limit, set the limit
			
			$this->db->limit($limit);
		}
		
		if ( $offset ) {
		// if there is offset, set the offset,
			
			$this->db->offset($offset);
		}
		
	 	return $this->db->get();
		// print_r($this->db->last_query());die;
	
	}

	function get_all_by1( $conds = array(), $limit = false, $offset = false ) {

		if($conds['lat'] != "" && $conds['lng'] != "") {
			$this->db->select('*,( 3959
		      * acos( cos( radians('. $conds['lat'] .') )
		              * cos(  radians( lat )   )
		              * cos(  radians( lng ) - radians('. $conds['lng'] .') )
		            + sin( radians('. $conds['lat'] .') )
		              * sin( radians( lat ) )
		            )
		    ) as distance');

		    if ($conds['miles'] == "") {
		    	$conds['miles'] = 0;
		    	$this->db->having('distance < ' .  $conds['miles'] );
		    } else {
		    	$this->db->having('distance < ' .  $conds['miles'] );

		    }

		   
		}

		// where clause
		$this->custom_conds( $conds );

		// from table
		$this->db->from( $this->table_name );

		if ( $limit ) {
		// if there is limit, set the limit
			
			$this->db->limit($limit);
		}
		
		if ( $offset ) {
		// if there is offset, set the offset,
			
			$this->db->offset($offset);
		}
		
	 	 $this->db->get();
		 print_r($this->db->last_query());die;
	
	}






	function get_all_by_item( $conds = array(), $limit = false, $offset = false ) {
		//print_r($conds);die;

		if($conds['lat'] != "" && $conds['lng'] != "") {
			$this->db->select('*,( 3959
		      * acos( cos( radians('. $conds['lat'] .') )
		              * cos(  radians( lat )   )
		              * cos(  radians( lng ) - radians('. $conds['lng'] .') )
		            + sin( radians('. $conds['lat'] .') )
		              * sin( radians( lat ) )
		            )
		    ) as distance');

		    if ($conds['miles'] == "") {
		    	$conds['miles'] = 0;
		    	$this->db->having('distance < ' .  $conds['miles'] );
		    } else {
		    	$this->db->having('distance < ' .  $conds['miles'] );

		    }

		   
		}

		// item id (id) check for user block condition
		if ( isset( $conds['item_id'] )) {
			
			if ($conds['item_id'] != "") {
				if($conds['item_id'] != '0'){
				
					$this->db->where_not_in( 'bs_items.id', $conds['item_id'] );	
				}

			}			
		}

		// item id (id) check for item reported condition
		if ( isset( $conds['reported_item_id'] )) {
			
			if ($conds['reported_item_id'] != "") {
				if($conds['reported_item_id'] != '0'){
				
					$this->db->where_not_in( 'bs_items.id', $conds['reported_item_id'] );	
				}

			}			
		}

		// where clause
		$this->custom_conds( $conds );

		// from table
		$this->db->from( $this->table_name );

		if ( $limit ) {
		// if there is limit, set the limit
			
			$this->db->limit($limit);
		}
		
		if ( $offset ) {
		// if there is offset, set the offset,
			
			$this->db->offset($offset);
		}
		
	 	return $this->db->get();
		// print_r($this->db->last_query());die;
	
	}

	function get_all_by_itemnew( $conds = array(), $limit = false, $offset = false ) {
//		echo '<pre>'; print_r($conds);die;
		
		if ( empty( $conds['is_draft'] )) {
			$this->db->where( 'bs_items.is_draft', 0);
		}
        
        if ( isset( $conds['cat_id'] )) {
			if ($conds['cat_id'] != "") {
				if($conds['cat_id'] != '0'){
					$this->db->where( 'cat_id', $conds['cat_id'] );	
				}
			}			
		}
        
        if(isset($conds['sub_cat_id'])) {
			if ($conds['sub_cat_id'] != "" || $conds['sub_cat_id'] != 0) {
				$this->db->where( 'sub_cat_id', $conds['sub_cat_id'] );	
			}
		}

		// item id (id) check for user block condition
		if ( isset( $conds['item_id'] )) {
			if ($conds['item_id'] != "") {
				if($conds['item_id'] != '0'){
					$this->db->where_not_in( 'bs_items.id', $conds['item_id'] );	
				}
			}			
		}

		// item id (id) check for item reported condition
		if ( isset( $conds['reported_item_id'] )) {
			if ($conds['reported_item_id'] != "") {
				if($conds['reported_item_id'] != '0'){
					$this->db->where_not_in( 'bs_items.id', $conds['reported_item_id'] );	
				}
			}			
		}
		

		// item id (id) check for item color condition
		if ( isset( $conds['coloritem_id'] )) {
			if ($conds['coloritem_id'] != "") {
				if($conds['coloritem_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['coloritem_id'] );	
				}
			}			
		}
		

		// item id (id) check for sizegroupoption condition
		if ( isset( $conds['sizeoption_item_id'] )) {
			if ($conds['sizeoption_item_id'] != "") {
				if($conds['sizeoption_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['sizeoption_item_id'] );	
				}
			}			
		}
		
		// added_user_id
		if ( isset( $conds['added_user_id'] )) {
			if ($conds['added_user_id'] != "") {
				if($conds['added_user_id'] != '0'){
					$this->db->where( 'bs_items.added_user_id', $conds['added_user_id'] );	
                    $this->db->where('bs_items.status > ',-1);
				}
			}			
        } else {
            $this->db->where( 'bs_items.status', 1);	
        }
        
        // remove added_user_id
		if ( isset( $conds['excpet_login_user'] ) && $conds['excpet_login_user']) {
			if ($conds['login_user_id'] != "") {
				if($conds['login_user_id'] != '0'){
					$this->db->where( 'bs_items.added_user_id <> "'.$conds['login_user_id'].'"' );	
				}
			}			
		}
        
        // remove sold_item
		if ( isset( $conds['excpet_sold_item'] ) && $conds['excpet_sold_item']) {
			if ($conds['excpet_sold_item'] != "") {
				if($conds['excpet_sold_item'] != '0'){
					$this->db->where( 'bs_items.is_sold_out <> 1');	
				}
			}			
		}

		if ( isset( $conds['is_draft'] )) {
			$this->db->where( 'bs_items.is_draft', (int)$conds['is_draft'] );
		}

		// item check for brand condition
		/*if ( isset( $conds['brand_items_id'] )) {
			
			
			if ($conds['brand_items_id'] != "") {
				if($conds['brand_items_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['brand_items_id'] );	
				}

			}			
		}*/

		// SKV: CHECK CONDITION FOR BRAND
		if ( isset( $conds['brand_search'] )) {
			if ($conds['brand_search'] != "") {
				if($conds['brand_search'] != '0'){
					$this->db->where_in( 'bs_items.brand', $conds['brand_search'] );	
				}
			}			
		}

		// item check for itemtype condition
		if ( isset( $conds['itemtype_item_id'] )) {
			if ($conds['itemtype_item_id'] != "") {
				if($conds['itemtype_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['itemtype_item_id'] );	
				}
			}			
		}
		
		// SKV: CHECK CONDITION FOR ITEM TYPE
		if ( isset( $conds['item_type_id'] )) {
			if ($conds['item_type_id'] != "") {
				if($conds['item_type_id'] != '0'){
					$this->db->where_in( 'bs_items.item_type_id', $conds['item_type_id'] );	
				}
			}			
		}

		// item check for child subcat condition
		if ( isset( $conds['childsubcat_item_id'] )) {
			if ($conds['childsubcat_item_id'] != "") {
				if($conds['childsubcat_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['childsubcat_item_id'] );	
				}
			}			
		}
		
		// item check for delivery method condition
		if ( isset( $conds['deliverymethod_item_id'] )) {
			if ($conds['deliverymethod_item_id'] != "") {
				if($conds['deliverymethod_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['deliverymethod_item_id'] );	
				}
			}			
		}

		
		// item check for item condition
		if ( isset( $conds['itemcondition_item_id'] )) {
			if ($conds['itemcondition_item_id'] != "") {
				if($conds['itemcondition_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['itemcondition_item_id'] );	
				}
			}			
		}
		
		
		// address id (id) check for lat long 
		if ( isset( $conds['address_item_id'] )) {
			if ($conds['address_item_id'] != "") {
				if($conds['address_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['address_item_id'] );	
				}
			}			
		}
        
        if(isset($conds['min_price'])) {
			if ($conds['min_price'] != "" || $conds['min_price'] != 0) {	
				$this->db->where( 'price>=', $conds['min_price'] );	
			}
		}
        
        if(isset($conds['max_price'])) {
			if ($conds['max_price'] != "" || $conds['max_price'] != 0) {	
				$this->db->where( 'price<=', $conds['max_price'] );
			}
		}

        if ( isset( $conds['searchterm'] )) {
			if ($conds['searchterm'] != "") {
                $this->db->like( 'bs_items.title', $conds['searchterm'] );	
			}			
		}

		if ( isset( $conds['order_by_field'] )) {
			$order_by_field = $conds['order_by_field'];
			$order_by_type = $conds['order_by_type'];
//			$this->db->order_by( 'bs_items.is_paid', 'desc');
			$this->db->order_by( 'bs_items.'.$order_by_field, $order_by_type);
		} else {
			$this->db->order_by( 'bs_items.is_paid', 'desc');
			$this->db->order_by('added_date', 'desc' );
		}

		if($conds['lat'] != "" && $conds['lng'] != "") {
			$this->db->select('*,( 3959
		      * acos( cos( radians('. $conds['lat'] .') )
		              * cos(  radians( lat )   )
		              * cos(  radians( lng ) - radians('. $conds['lng'] .') )
		            + sin( radians('. $conds['lat'] .') )
		              * sin( radians( lat ) )
		            )
		    ) as distance');
		    if ($conds['miles'] == "") {
		    	$conds['miles'] = 0;
		    	$this->db->having('distance < ' .  $conds['miles'] );
		    } else {
		    	$this->db->having('distance < ' .  $conds['miles'] );
		    }
		}

		// where clause
		//$this->custom_conds( $conds );
		
		// from table
		$this->db->from( $this->table_name );

		if ( $limit ) {
		// if there is limit, set the limit
			$this->db->limit($limit);
		}
		
		if ( $offset ) {
		// if there is offset, set the offset,			
			$this->db->offset($offset);
		}
	 	return $this->db->get();
		// print_r($this->db->last_query());die;
	}


	/**
	 * Counts the number of all by the conditions
	 *
	 * @param      array   $conds  The conds
	 *
	 * @return     <type>  Number of all by.
	 */
	function count_all_by( $conds = array()) {
		// where clause
		$this->custom_conds( $conds );
		// from table
		$this->db->from( $this->table_name );

		// return the count all results
		return $this->db->count_all_results();
		 //print_r($this->db->last_query());die;
	}

	/**
	 * Counts the number of all by the conditions
	 *
	 * @param      array   $conds  The conds
	 *
	 * @return     <type>  Number of all by.
	 */
	function count_all_for_today( $conds = array()) {
		// where clause
		$this->custom_conds( $conds );
		// from table
		$this->db->from( $this->table_name );
		$date = date('Y-m-d');
		
		$this->db->where('DATE(added_date)', $date);
		// return the count all results
		return $this->db->count_all_results();
		//print_r($this->db->last_query());die;
	}


	/**
	 * Counts the number of all by the conditions
	 *
	 * @param      array   $conds  The conds
	 *
	 * @return     <type>  Number of all by.
	 */
	function count_all_reported_items_for_today( $conds = array()) {
		$this->db->from('bs_reason_operations');
		$date = date('Y-m-d');
		
		$this->db->where('DATE(added_date)', $date);
		$this->db->where('type', 'report_item');
		// return the count all results
		//echo '<pre>'; print_r($this->db->result()); die('hello testing');
		return $this->db->count_all_results();
		//print_r($this->db->last_query());die;
	}

	
	/**
	 * Sum the number of all by the conditions
	 *
	 * @param      array   $conds  The conds
	 *
	 * @return     <type>  Number of all by.
	 */
	function sum_all_by( $conds = array()) {
		
		// where clause
		$this->custom_conds( $conds );
		
		$this->db->select_sum('rating');
		// from table
		$this->db->from( $this->table_name );

		// return the count all results
		//return $this->db->count_all_results();
		return $this->db->get();
	}

	/**
	 * Gets the information by.
	 *
	 * @param      array   $conds  The conds
	 *
	 * @return     <type>  The information by.
	 */
	function get_one_by( $conds = array()) {
		
		// where clause
		$this->custom_conds( $conds );
		// query the record
		$query = $this->db->get( $this->table_name );
		
		if ( $query->num_rows() == 1 ) {
		// if there is one row, return the record
			return $query->row();
		} else {
		// if there is no row or more than one, return the empty object
			 return $this->get_empty_object( $this->table_name );
			
		}
	}


	/**
	 * Delete the records by condition
	 *
	 * @param      array   $conds  The conds
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	function delete_by( $conds = array() )
	{
		// where clause
		$this->custom_conds( $conds );

		// delete the record
		return $this->db->delete( $this->table_name );
		 //print_r($this->db->last_query());die;
 	}

 	/**
	 * Delete the records
	 */
	function delete_all()
	{
		// where clause
		//$this->custom_conds( $conds );
		// delete the records
		//$this->db->delete( $this->table_name );
		//print_r($this->db->last_query());die;

		return $this->db->query('Delete from bs_chat_history');
 	}



 	/**
	 * Delete the records by condition
	 *
	 * @param      array   $conds  The conds
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	function get_rating_count( $conds = array() )
	{

		$this->db->select(' sum(rating) as t_count'); 
		$this->db->from('bs_rating');

		if(isset($conds['wallpaper_id'])) {

			if ($conds['wallpaper_id'] != "" || $conds['wallpaper_id'] != 0) {
					
					$this->db->where( 'wallpaper_id', $conds['wallpaper_id'] );	

			}

		}

	
	 	return $this->db->get();
		// print_r($this->db->last_query());die;
 	}

 

 	function get_total_earning_point($conds = array()) {

 		$this->db->select(' sum(earn_point) as total'); 
		$this->db->from('bs_earning_points');

		if(isset($conds['user_id'])) {

			if ($conds['user_id'] != "" || $conds['user_id'] != 0) {
					
					$this->db->where( 'user_id', $conds['user_id'] );	

			}

		}

	 	return $this->db->get();
		// print_r($this->db->last_query());die;
 	}


 	/** get popular item */

 	function get_item_count( $conds = array() )
	{
		$this->db->select('bs_items.*, count(bs_touches.touch_id) as t_count,bs_items.title'); 
		$this->db->from('bs_touches');
		$this->db->join('bs_items', 'bs_touches.item_id = bs_items.id');
	
		$this->db->limit(5);

		if(isset($conds['item_id'])) {

			if ($conds['item_id'] != "" || $conds['item_id'] != 0) {
					
					$this->db->where( 'item_id', $conds['item_id'] );	

			}

		}
		$this->db->group_by("bs_touches.item_id");
		$this->db->order_by("count(DISTINCT bs_touches.touch_id)", "DESC");
		return $this->db->get();
	
 	}

 	/** get reported item */

 	function get_item_report($conds = array())
	{
		//print_r("asdfasd" .$conds);die;
		$this->db->select('bs_items_report.*');    
  		$this->db->from('bs_items_report');
  		$this->db->where('month(added_date)',$conds['added_date']);

		return $this->db->get();
		 // print_r($this->db->last_query());die;
	}

 /**
  * Gets all by the conditions
  *
  * @param      array    $conds   The conds
  * @param      boolean  $limit   The limit
  * @param      boolean  $offset  The offset
  *
  * @return     <type>   All by.
  */
	function get_all_device_in( $conds = array(), $limit = false, $offset = false ) {

	  // where clause
	  $this->db->where_in('user_id', $conds);

	  // from table
	  $this->db->from( $this->table_name );

	  if ( $limit ) {
	  // if there is limit, set the limit
	   
	   $this->db->limit($limit);
	  }
	  
	  if ( $offset ) {
	  // if there is offset, set the offset,
	   
	   $this->db->offset($offset);
	  }
	  
	  return $this->db->get();

	 }

	 
 	/**
	* Returns Item list by followuser
	*/
	function get_all_item_by_followuser( $conds = array() , $limit = false, $offset = false)
	{


		$this->db->select('bs_items.*'); 
		$this->db->from('bs_items');

		//follow user

		if ( isset( $conds['followuser'] )) {
			if ($conds['followuser'] != "" ) {
				if ($conds['followuser'] != '0') {
					$this->db->where_in( 'bs_items.added_user_id', $conds['followuser'] );	
				} 
				
			}
		}

		// item id (id) check for user block condition
		if ( isset( $conds['item_id'] )) {
			
			if ($conds['item_id'] != "") {
				if($conds['item_id'] != '0'){
				
					$this->db->where_not_in( 'bs_items.id', $conds['item_id'] );	
				}

			}			
		}

		// item id (id) check for item reported condition
		if ( isset( $conds['reported_item_id'] )) {
			
			if ($conds['reported_item_id'] != "") {
				if($conds['reported_item_id'] != '0'){
				
					$this->db->where_not_in( 'bs_items.id', $conds['reported_item_id'] );	
				}

			}			
		}

		$this->custom_conds($conds);

		if ( $limit ) {
		// if there is limit, set the limit
			
			$this->db->limit($limit);
		}
		
		if ( $offset ) {
		// if there is offset, set the offset,
			
			$this->db->offset($offset);
		}

  		return $this->db->get();
  		 //print_r($this->db->last_query());die;


	}

	/**
	Returns popular Items count
	*/
	function count_item_by($conds = array()){
		$this->custom_conds();
		//where clause
		$this->db->select('bs_items.*, count(bs_touches.item_id) as t_count');    
  		$this->db->from('bs_items');
  		$this->db->join('bs_touches', 'bs_items.id = bs_touches.item_id');

		if ( isset( $conds['cat_id'] )) {
			if ($conds['cat_id'] != "" ) {
				if ($conds['cat_id'] != '0') {
					$this->db->where( 'bs_items.cat_id', $conds['cat_id'] );	
				} 
				
			}
		}

		//  sub category id condition 
		if ( isset( $conds['sub_cat_id'] )) {
			if ($conds['sub_cat_id'] != "" ) {
				if ($conds['sub_cat_id'] != '0') {
					$this->db->where( 'bs_items.sub_cat_id', $conds['sub_cat_id'] );
				}
				
			}
			
		}

		//  item type id condition 
		if ( isset( $conds['item_type_id'] )) {
			if ($conds['item_type_id'] != "" ) {
				if ($conds['item_type_id'] != '0') {
					$this->db->where( 'bs_items.item_type_id', $conds['item_type_id'] );
				}
				
			}
			
		}

		//  item price type id condition 
		if ( isset( $conds['item_price_type_id'] )) {
			if ($conds['item_price_type_id'] != "" ) {
				if ($conds['item_price_type_id'] != '0') {
					$this->db->where( 'bs_items.item_price_type_id', $conds['item_price_type_id'] );
				}
				
			}
			
		}

		//  item currency id condition 
		if ( isset( $conds['item_currency_id'] )) {
			if ($conds['item_currency_id'] != "" ) {
				if ($conds['item_currency_id'] != '0') {
					$this->db->where( 'bs_items.item_currency_id', $conds['item_currency_id'] );
				}
				
			}
			
		}

		//  status condition 
		if ( isset( $conds['status'] )) {
			if ($conds['status'] != "" ) {
				if ($conds['status'] != '0') {
					$this->db->where( 'bs_items.status', $conds['status'] );
				}
				
			}
			
		}

		if ( isset( $conds['searchterm'] ) || isset( $conds['date'] )) {
			$dates = $conds['date'];

			if ($dates != "") {
				$vardate = explode('-',$dates,2);

				$temp_mindate = $vardate[0];
				$temp_maxdate = $vardate[1];		

				$temp_startdate = new DateTime($temp_mindate);
				$mindate = $temp_startdate->format('Y-m-d');

				$temp_enddate = new DateTime($temp_maxdate);
				$maxdate = $temp_enddate->format('Y-m-d');
			} else {
				$mindate = "";
			 	$maxdate = "";
			}
			
			if ($conds['searchterm'] == "" && $mindate != "" && $maxdate != "") {
				//got 2dates
				if ($mindate == $maxdate ) {

					$this->db->where("bs_touches.added_date BETWEEN DATE('".$mindate."') AND DATE('". $maxdate."' + INTERVAL 1 DAY)");

				} else {

					$today_date = date('Y-m-d');
					if($today_date == $maxdate) {
						$current_time = date('H:i:s');
						$maxdate = $maxdate . " ". $current_time;
					}

					$this->db->where( 'date(bs_touches.added_date) >=', $mindate );
   					$this->db->where( 'date(bs_touches.added_date) <=', $maxdate );

				}
				$this->db->like( '(title', $conds['searchterm'] );
				$this->db->or_like( 'title)', $conds['searchterm'] );
			} else if ($conds['searchterm'] != "" && $mindate != "" && $maxdate != "") {
				//got name and 2dates
				if ($mindate == $maxdate ) {

					$this->db->where("bs_touches.added_date BETWEEN DATE('".$mindate."') AND DATE('". $maxdate."' + INTERVAL 1 DAY)");

				} else {

					$today_date = date('Y-m-d');
					if($today_date == $maxdate) {
						$current_time = date('H:i:s');
						$maxdate = $maxdate . " ". $current_time;
					}

					$this->db->where( 'date(bs_touches.added_date) >=', $mindate );
   					$this->db->where( 'date(bs_touches.added_date) <=', $maxdate );

				}
				$this->db->group_start();
				$this->db->like( 'title', $conds['searchterm'] );
				$this->db->or_like( 'title', $conds['searchterm'] );
				$this->db->group_end();
			} else {
				//only name 
				$this->db->group_start();
				$this->db->like( 'title', $conds['searchterm'] );
				$this->db->or_like( 'title', $conds['searchterm'] );
				$this->db->group_end();
				
			}
			 
	    }

  		$this->db->group_by('bs_touches.item_id');
  		$this->db->order_by('t_count', "DESC");
  		$this->db->order_by( 'bs_touches.added_date', "desc" );

  		return $this->db->count_all_results();
	}

	/**
	* Gets popular Items record
	*/
	function get_item_by ( $conds = array(), $limit = false, $offset = false ){


		//where clause
		$this->db->select('bs_items.*, count(bs_touches.item_id) as t_count');    
  		$this->db->from('bs_items');
  		$this->db->join('bs_touches', 'bs_items.id = bs_touches.item_id');

  		if ( isset( $conds['cat_id'] )) {
			if ($conds['cat_id'] != "" ) {
				if ($conds['cat_id'] != '0') {
					$this->db->where( 'bs_items.cat_id', $conds['cat_id'] );	
				} 
				
			}
		}

		//  sub category id condition 
		if ( isset( $conds['sub_cat_id'] )) {
			if ($conds['sub_cat_id'] != "" ) {
				if ($conds['sub_cat_id'] != '0') {
					$this->db->where( 'bs_items.sub_cat_id', $conds['sub_cat_id'] );
				}
				
			}
			
		}

		//  item type id condition 
		if ( isset( $conds['item_type_id'] )) {
			if ($conds['item_type_id'] != "" ) {
				if ($conds['item_type_id'] != '0') {
					$this->db->where( 'bs_items.item_type_id', $conds['item_type_id'] );
				}
				
			}
			
		}

		//  item price type id condition 
		if ( isset( $conds['item_price_type_id'] )) {
			if ($conds['item_price_type_id'] != "" ) {
				if ($conds['item_price_type_id'] != '0') {
					$this->db->where( 'bs_items.item_price_type_id', $conds['item_price_type_id'] );
				}
				
			}
			
		}

		//  item currency id condition 
		if ( isset( $conds['item_currency_id'] )) {
			if ($conds['item_currency_id'] != "" ) {
				if ($conds['item_currency_id'] != '0') {
					$this->db->where( 'bs_items.item_currency_id', $conds['item_currency_id'] );
				}
				
			}
			
		}

		//  status condition 
		if ( isset( $conds['status'] )) {
			if ($conds['status'] != "" ) {
				if ($conds['status'] != '0') {
					$this->db->where( 'bs_items.status', $conds['status'] );
				}
				
			}
			
		}

		if ( isset( $conds['searchterm'] ) || isset( $conds['date'] )) {
			$dates = $conds['date'];

			if ($dates != "") {
				$vardate = explode('-',$dates,2);

				$temp_mindate = $vardate[0];
				$temp_maxdate = $vardate[1];		

				$temp_startdate = new DateTime($temp_mindate);
				$mindate = $temp_startdate->format('Y-m-d');

				$temp_enddate = new DateTime($temp_maxdate);
				$maxdate = $temp_enddate->format('Y-m-d');
			} else {
				$mindate = "";
			 	$maxdate = "";
			}
			
			if ($conds['searchterm'] == "" && $mindate != "" && $maxdate != "") {
				//got 2dates
				if ($mindate == $maxdate ) {

					$this->db->where("bs_touches.added_date BETWEEN DATE('".$mindate."') AND DATE('". $maxdate."' + INTERVAL 1 DAY)");

				} else {

					$today_date = date('Y-m-d');
					if($today_date == $maxdate) {
						$current_time = date('H:i:s');
						$maxdate = $maxdate . " ". $current_time;
					}

					$this->db->where( 'date(bs_touches.added_date) >=', $mindate );
   					$this->db->where( 'date(bs_touches.added_date) <=', $maxdate );

				}
				$this->db->like( '(title', $conds['searchterm'] );
				$this->db->or_like( 'title)', $conds['searchterm'] );
			} else if ($conds['searchterm'] != "" && $mindate != "" && $maxdate != "") {
				//got name and 2dates
				if ($mindate == $maxdate ) {

					$this->db->where("bs_touches.added_date BETWEEN DATE('".$mindate."') AND DATE('". $maxdate."' + INTERVAL 1 DAY)");

				} else {

					$today_date = date('Y-m-d');
					if($today_date == $maxdate) {
						$current_time = date('H:i:s');
						$maxdate = $maxdate . " ". $current_time;
					}

					$this->db->where( 'date(bs_touches.added_date) >=', $mindate );
   					$this->db->where( 'date(bs_touches.added_date) <=', $maxdate );

				}
				$this->db->group_start();
				$this->db->like( 'title', $conds['searchterm'] );
				$this->db->or_like( 'title', $conds['searchterm'] );
				$this->db->group_end();
			} else {
				//only name 
				$this->db->group_start();
				$this->db->like( 'title', $conds['searchterm'] );
				$this->db->or_like( 'title', $conds['searchterm'] );
				$this->db->group_end();
				
			}
			 
	    }

  		$this->db->group_by('bs_touches.item_id');
  		$this->db->order_by('t_count', "DESC");
  		$this->db->order_by( 'bs_touches.added_date', "desc" );
  		
  		


  		if ( $limit ) {
		// if there is limit, set the limit
			
			$this->db->limit($limit);
		}
		
		if ( $offset ) {
		// if there is offset, set the offset,
			
			$this->db->offset($offset);
		}

  		return $this->db->get();
  		// print_r($this->db->last_query());die;

	}

	//get where in user

	function get_all_in_user( $conds = array() ) {

	  $sql = "SELECT * FROM `core_users` WHERE `user_id` IN(". $conds['chat_user_id'] .")";

		$query = $this->db->query($sql);

		return $query;

	}

	//get where in rating

	function get_all_in_rating( $conds1 = array() , $limit = false, $offset = false ) {

	  	$sql = "SELECT * FROM `bs_ratings` WHERE `id` IN(". $conds1['id'] .") ORDER BY added_date DESC LIMIT $offset,$limit ";

		$query = $this->db->query($sql);

		

		return $query;

	}	

	function get_all_in_chat( $conds = array() ) {

	  	$sql = "SELECT * FROM `bs_chat_history` WHERE `id` IN(". $conds['$id'] .") order by added_date desc";

		$query = $this->db->query($sql);

		return $query;

	}

	function get_all_not_in_noti( $conds = array() ) {

	  	$sql = "SELECT * FROM `bs_push_notification_messages` WHERE `id` NOT IN(". $conds['$id'] .") order by added_date desc";
		$query = $this->db->query($sql);

		return $query;

	}

	/**
	Returns user follow list
	*/
	function get_all_follower_by_user( $conds = array() , $limit = false, $offset = false)
	{
		//print_r($limit . "$offset");die;
		$sql = "SELECT *  FROM core_users WHERE  user_id in (". $conds['follower_and_following_user'] ." ) ORDER BY added_date DESC LIMIT $offset,$limit";
		$query = $this->db->query($sql);

		return $query;
		  //print_r($this->db->last_query());die;
		
	}

	/* Delete the records by condition
	 *
	 * @param      array   $conds  The conds
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 to get item favourite list
	 */
	function get_item_favourite( $conds = array(), $limit = false, $offset = false  )
	{
		$this->db->select('bs_items.*'); 
		$this->db->from('bs_items');
		$this->db->join('bs_favourite', 'bs_favourite.item_id = bs_items.id');

		if(isset($conds['user_id'])) {
			if ($conds['user_id'] != "" || $conds['user_id'] != 0) {
					$this->db->where( 'user_id', $conds['user_id'] );	
			}
		}

		// item id (id) check for user block condition
		if (isset($conds['item_id'] )) {
			if ($conds['item_id'] != "") {
				if($conds['item_id'] != '0'){
					$this->db->where_not_in( 'bs_items.id', $conds['item_id'] );	
				}
			}			
		}

		// item type (type) check this condition
		if (isset($conds['item_type_id'] )) {
			if ($conds['item_type_id'] != "") {
				if($conds['item_type_id'] != '0'){
					$this->db->where( 'bs_items.item_type_id', $conds['item_type_id'] );	
				}
			}			
		}

		// item id (id) check for item reported condition
		if (isset($conds['reported_item_id'] )) {			
			if ($conds['reported_item_id'] != "") {
				if($conds['reported_item_id'] != '0'){
					$this->db->where_not_in( 'bs_items.id', $conds['reported_item_id'] );	
				}
			}			
		}

		// status
		if (isset( $conds['status'] )) {
			$this->db->where( 'bs_items.status', $conds['status'] );
		}
		

		if ( $limit ) {
		// if there is limit, set the limit
			
			$this->db->limit($limit);
		}
		
		if ( $offset ) {
		// if there is offset, set the offset,
			$this->db->offset($offset);
		}
		
		return $this->db->get();
		print_r($this->db->last_query());die;
 	}

 	// get user with status 2 for request code

	function user_exists( $conds = array()) {

		$sql = "SELECT * FROM core_users WHERE `user_email` = '" . $conds['user_email'] . "' AND `status` = '" . $conds['status'] . "' ";

		$query = $this->db->query($sql);

		return $query;
	}

	// get user with email conds

	function get_one_user_email( $conds = array()) {

		$sql = "SELECT * FROM core_users WHERE `user_email` = '" . $conds['user_email'] . "' ";

		$query = $this->db->query($sql);

		return $query;
	}

	function get_all_not_in_lang( $id, $limit = false, $offset = false ) {
	  // where clause
	  $this->db->where_not_in('id', $id);

	  // from table
	  $this->db->from( $this->table_name );

	  if ( $limit ) {
	  // if there is limit, set the limit
	   
	   $this->db->limit($limit);
	  }
	  
	  if ( $offset ) {
	  // if there is offset, set the offset,
	   
	   $this->db->offset($offset);
	  }
	  
	  return $this->db->get();
	  // print_r($this->db->last_query());die;
	}

	function get_language_string( $conds = array() ){

		// from table
	  	$this->db->from( $this->table_name );

	  	if(isset($conds['language_id'])) {

			if ($conds['language_id'] != "" || $conds['language_id'] != 0) {
					
					$this->db->where( 'language_id', $conds['language_id'] );	

			}

		}

		if(isset($conds['key'])) {

			if ($conds['key'] != "" || $conds['key'] != 0) {
					
					$this->db->where( 'key', $conds['key'] );	

			}

		}

		if(isset($conds['value'])) {

			if ($conds['value'] != "" || $conds['value'] != 0) {
					
					$this->db->where( 'value', $conds['value'] );	

			}

		}

		return $this->db->get();
	    // print_r($this->db->last_query());die;

	}

	// function get_all_paid_item_finished () {
	// 	$this->db->select('bs_items.*'); 
	// 	$this->db->from('bs_items');
	// 	$this->db->join('bs_paid_items_history', 'bs_paid_items_history.item_id = bs_items.id');
	// 	$today_date = date('Y-m-d H:i:s');
		
 //   		$this->db->where( 'bs_paid_items_history.end_date <', $today_date );
 //   		return $this->db->get();
 //   		//print_r($this->db->last_query());die;
	// }

	function get_all_item_by_paid ( $conds ) {
		//echo '<pre>'; print_R($conds); die;
		$this->db->select('bs_items.*'); 
		
		$this->db->from('bs_items');
		if($conds['status']){
			$this->db->where('bs_items.status',(int)$conds['status']);
		} else {
			$this->db->where('bs_items.status > ',-1);
        }
		if($conds['is_draft']){
			$this->db->where('bs_items.is_draft',(int)$conds['is_draft']);
		}
        $this->db->join('bs_paid_items_history', 'bs_items.id = bs_paid_items_history.item_id', 'left');
		// $today_date = date('Y-m-d H:i:s');
		// $this->db->where( 'bs_paid_items_history.start_date <= ', $today_date );
   		// $this->db->where( 'bs_paid_items_history.end_date >= ', $today_date );
        
        if ( isset( $conds['cat_id'] )) {
			if ($conds['cat_id'] != "") {
				if($conds['cat_id'] != '0'){
					$this->db->where( 'bs_items.cat_id', $conds['cat_id'] );	
				}
			}			
		}
        if(isset($conds['sub_cat_id'])) {
			if ($conds['sub_cat_id'] != "" || $conds['sub_cat_id'] != 0) {
				$this->db->where( 'bs_items.sub_cat_id', $conds['sub_cat_id'] );	
			}
		}
   		// item id (id) check for user block condition
		if ( isset( $conds['item_id'] )) {
			if ($conds['item_id'] != "") {
				if($conds['item_id'] != '0'){
					$this->db->where_not_in( 'bs_items.id', $conds['item_id'] );	
				}
			}			
		}

		// added_user_id
		if ( isset( $conds['added_user_id'] )) {
			if ($conds['added_user_id'] != "") {
				if($conds['added_user_id'] != '0'){
					$this->db->where( 'bs_items.added_user_id', $conds['added_user_id'] );	
				}
			}			
		}
        
		// remove added_user_id
		if ( isset( $conds['excpet_login_user'] ) && $conds['excpet_login_user']) {
			if ($conds['login_user_id'] != "") {
				if($conds['login_user_id'] != '0'){
					$this->db->where( 'bs_items.added_user_id <> "'.$conds['login_user_id'].'"' );	
				}
			}			
		}
        
        // remove sold_item
		if ( isset( $conds['excpet_sold_item'] ) && $conds['excpet_sold_item']) {
			if ($conds['excpet_sold_item'] != "") {
				if($conds['excpet_sold_item'] != '0'){
					$this->db->where( 'bs_items.is_sold_out <> 1');	
				}
			}			
		}

		// item id (id) check for item reported condition
		if ( isset( $conds['reported_item_id'] )) {
			if ($conds['reported_item_id'] != "") {
				if($conds['reported_item_id'] != '0'){
					$this->db->where_not_in( 'bs_items.id', $conds['reported_item_id'] );	
				}
			}			
		}

		//is_paid
		if ( isset( $conds['is_paid'] )) {
			if ($conds['is_paid'] != "") {
				if($conds['is_paid'] != '0'){
					$this->db->where( 'bs_items.is_paid', $conds['is_paid'] );	
				}
			}			
		}

		// item id (id) check for item color condition
		if ( isset( $conds['coloritem_id'] )) {
			if ($conds['coloritem_id'] != "") {
				if($conds['coloritem_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['coloritem_id'] );	
				}
			}			
		}
		

		// item id (id) check for sizegroupoption condition
		if ( isset( $conds['sizeoption_item_id'] )) {
			if ($conds['sizeoption_item_id'] != "") {
				if($conds['sizeoption_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['sizeoption_item_id'] );	
				}
			}			
		}
		

		// item check for brand condition
		/*if ( isset( $conds['brand_items_id'] )) {
			
			
			if ($conds['brand_items_id'] != "") {
				if($conds['brand_items_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['brand_items_id'] );	
				}

			}			
		} */

		// SKV: CHECK CONDITION FOR BRAND
		if ( isset( $conds['brand_search'] )) {		
			if ($conds['brand_search'] != "") {
				if($conds['brand_search'] != '0'){
					$this->db->where_in( 'bs_items.brand', $conds['brand_search'] );	
				}
			}			
		}

		// item check for itemtype condition
		if ( isset( $conds['itemtype_item_id'] )) {
			if ($conds['itemtype_item_id'] != "") {
				if($conds['itemtype_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['itemtype_item_id'] );	
				}
			}			
		}

        // SKV: CHECK CONDITION FOR ITEM TYPE
		if ( isset( $conds['item_type_id'] )) {
			if ($conds['item_type_id'] != "") {
				if($conds['item_type_id'] != '0'){
					$this->db->where_in( 'bs_items.item_type_id', $conds['item_type_id'] );	
				}
			}			
		}

		// item check for child subcat condition
		if ( isset( $conds['childsubcat_item_id'] )) {
			if ($conds['childsubcat_item_id'] != "") {
				if($conds['childsubcat_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['childsubcat_item_id'] );	
				}
			}			
		}

		// item check for delivery method condition
		if ( isset( $conds['deliverymethod_item_id'] )) {
			if ($conds['deliverymethod_item_id'] != "") {
				if($conds['deliverymethod_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['deliverymethod_item_id'] );	
				}
			}			
		}

		
		// item check for item condition
		if ( isset( $conds['itemcondition_item_id'] )) {
			if ($conds['itemcondition_item_id'] != "") {
				if($conds['itemcondition_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['itemcondition_item_id'] );	
				}
			}			
		}
		
		
		// address id (id) check for lat long 
		if ( isset( $conds['address_item_id'] )) {
			if ($conds['address_item_id'] != "") {
				if($conds['address_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['address_item_id'] );	
				}
			}			
		}
        
        if(isset($conds['min_price'])) {
			if ($conds['min_price'] != "" || $conds['min_price'] != 0) {	
				$this->db->where( 'price>=', $conds['min_price'] );	
			}
		}
        
        if(isset($conds['max_price'])) {
			if ($conds['max_price'] != "" || $conds['max_price'] != 0) {	
				$this->db->where( 'price<=', $conds['max_price'] );
			}
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
		if ( isset( $conds['order_by_field'] )) {
			$order_by_field = $conds['order_by_field'];
			$order_by_type = $conds['order_by_type'];
			$this->db->order_by( 'bs_items.is_paid', 'desc');
			$this->db->order_by( 'bs_items.'.$order_by_field, $order_by_type);
		} else {
            if($conds['is_paid']) {
                $this->db->order_by('CASE  
                    WHEN `bs_paid_items_history.end_timestamp` > '.time().' THEN 1 
                    ELSE 0 END 
                DESC');    
            } else {
			$this->db->order_by( 'bs_items.is_paid', 'desc');
            }
			$this->db->order_by('added_date', 'desc' );
		}
		// if($conds['lat'] != "" && $conds['lng'] != "") {
			
		// 	//$this->db->select("'distance' as distance"); 
		// 	$this->db->select('( 3959
		//       * acos( cos( radians('. $conds['lat'] .') )
		//               * cos(  radians( lat )   )
		//               * cos(  radians( lng ) - radians('. $conds['lng'] .') )
		//             + sin( radians('. $conds['lat'] .') )
		//               * sin( radians( lat ) )
		//             )
		//     ) as distance');

		//     if ($conds['miles'] == "") {
		//     	$conds['miles'] = 0;
		//     	$this->db->having('distance < ' .  $conds['miles'] );
		//     } else {
		//     	$this->db->having('distance < ' .  $conds['miles'] );

		//     }
		// }
   		return $this->db->get();
   		//print_r($this->db->last_query());die;
	}

	function get_item_by_paid_progress ( $item_id= array() ) {
		$this->db->select('bs_paid_items_history.*'); 
		$this->db->from('bs_paid_items_history');
		$this->db->where_in('item_id', $item_id);
		$today_date = date('Y-m-d H:i:s');
		$this->db->group_start();
		$this->db->where( 'bs_paid_items_history.start_date <= ', $today_date );
   		$this->db->where( 'bs_paid_items_history.end_date >= ', $today_date );
   		$this->db->or_where( 'bs_paid_items_history.end_date >= ', $today_date );
   		$this->db->group_end();
   		return $this->db->get();
   		//print_r($this->db->last_query());die;
	}

	// function get_not_today_date ( $ignore ) {
	// 	// where clause
	//   	$this->db->where_not_in('item_id', $ignore);
	//   	// from table
	//   	$this->db->from( $this->table_name );
		
 //   		return $this->db->get();
 //   		// print_r($this->db->last_query());die;
	// }

	function get_all_item_by_paid_date ( $conds = array(), $limit = false, $offset = false ) {
		/* query 1 start */
		$this->db->select('bs_items.*'); 
		
		$this->db->from('bs_items');
        $this->db->join('bs_paid_items_history', 'bs_items.id = bs_paid_items_history.item_id', 'left');
		// $today_date = date('Y-m-d H:i:s');
		if($conds['status']){
			$this->db->where('bs_items.status',(int)$conds['status']);
		} else {
			$this->db->where('bs_items.status > ',-1);
        }
		if($conds['is_draft']){
			$this->db->where('bs_items.is_draft',(int)$conds['is_draft']);
		}
		//$this->db->where( 'bs_items.is_paid', (int)$conds['is_paid']);
		// $this->db->where( 'bs_paid_items_history.start_date <= ', $today_date );
   		// $this->db->where( 'bs_paid_items_history.end_date >= ', $today_date );
		// location id
		if ( isset( $conds['item_location_id'] )) {		
			if ($conds['item_location_id'] != "") {
				if($conds['item_location_id'] != '0'){
					$this->db->where( 'item_location_id', $conds['item_location_id'] );	
				}
			}			
		}


		//Start - Modify By PPH @ 12 May 2020
		if ( isset( $conds['cat_id'] )) {
			if ($conds['cat_id'] != "") {
				if($conds['cat_id'] != '0'){
					$this->db->where( 'cat_id', $conds['cat_id'] );	
				}
			}			
		}

        if(isset($conds['sub_cat_id'])) {
			if ($conds['sub_cat_id'] != "" || $conds['sub_cat_id'] != 0) {
				$this->db->where( 'sub_cat_id', $conds['sub_cat_id'] );	
			}
		}

		// item id (id) check for user block condition
		if ( isset( $conds['item_id'] )) {
			if ($conds['item_id'] != "") {
				if($conds['item_id'] != '0'){
					$this->db->where_not_in( 'bs_items.id', $conds['item_id'] );	
				}
			}			
		}

		// item id (id) check for item reported condition
		if ( isset( $conds['reported_item_id'] )) {
			if ($conds['reported_item_id'] != "") {
				if($conds['reported_item_id'] != '0'){
					$this->db->where_not_in( 'bs_items.id', $conds['reported_item_id'] );	
				}
			}			
		}

		// added_user_id
		if ( isset( $conds['added_user_id'] )) {
			if ($conds['added_user_id'] != "") {
				if($conds['added_user_id'] != '0'){
					$this->db->where( 'bs_items.added_user_id', $conds['added_user_id'] );	
				}
			}			
		}
        
        // remove added_user_id
		if ( isset( $conds['excpet_login_user'] ) && $conds['excpet_login_user']) {
			if ($conds['login_user_id'] != "") {
				if($conds['login_user_id'] != '0'){
					$this->db->where( 'bs_items.added_user_id <> "'.$conds['login_user_id'].'"' );	
				}
			}			
		}
        
        // remove sold_item
		if ( isset( $conds['excpet_sold_item'] ) && $conds['excpet_sold_item']) {
			if ($conds['excpet_sold_item'] != "") {
				if($conds['excpet_sold_item'] != '0'){
					$this->db->where( 'bs_items.is_sold_out <> 1');	
				}
			}			
		}
		
		// item id (id) check for item color condition
		if ( isset( $conds['coloritem_id'] )) {
			if ($conds['coloritem_id'] != "") {
				if($conds['coloritem_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['coloritem_id'] );	
				}
			}			
		}
		

		// item id (id) check for sizegroupoption condition
		if ( isset( $conds['sizeoption_item_id'] )) {
			if ($conds['sizeoption_item_id'] != "") {
				if($conds['sizeoption_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['sizeoption_item_id'] );	
				}
			}			
		}
		

		// item check for brand condition
		if ( isset( $conds['brand_items_id'] )) {
			if ($conds['brand_items_id'] != "") {
				if($conds['brand_items_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['brand_items_id'] );	
				}
			}			
		}

		// SKV: CHECK CONDITION FOR BRAND
		if ( isset( $conds['brand_search'] )) {
			if ($conds['brand_search'] != "") {
				if($conds['brand_search'] != '0'){
					$this->db->where_in( 'bs_items.brand', $conds['brand_search'] );	
				}
			}			
		}

		// item check for itemtype condition
		if ( isset( $conds['itemtype_item_id'] )) {
			if ($conds['itemtype_item_id'] != "") {
				if($conds['itemtype_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['itemtype_item_id'] );	
				}
			}			
		}


        // SKV: CHECK CONDITION FOR ITEM TYPE
		if ( isset( $conds['item_type_id'] )) {
			if ($conds['item_type_id'] != "") {
				if($conds['item_type_id'] != '0'){
					$this->db->where_in( 'bs_items.item_type_id', $conds['item_type_id'] );	
				}
			}			
		}

		// item check for child subcat condition
		if ( isset( $conds['childsubcat_item_id'] )) {
			if ($conds['childsubcat_item_id'] != "") {
				if($conds['childsubcat_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['childsubcat_item_id'] );	
				}
			}			
		}

		// item check for delivery method condition
		if ( isset( $conds['deliverymethod_item_id'] )) {
			if ($conds['deliverymethod_item_id'] != "") {
				if($conds['deliverymethod_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['deliverymethod_item_id'] );	
				}
			}			
		}

		
		// item check for item condition
		if ( isset( $conds['itemcondition_item_id'] )) {
			if ($conds['itemcondition_item_id'] != "") {
				if($conds['itemcondition_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['itemcondition_item_id'] );	
				}
			}			
		}
		
		// address id (id) check for lat long 
		if ( isset( $conds['address_item_id'] )) {
			if ($conds['address_item_id'] != "") {
				if($conds['address_item_id'] != '0'){
				
					$this->db->where_in( 'bs_items.id', $conds['address_item_id'] );	
				}
			}			
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


		//End - Modify By PPH @ 12 May 2020

		
   		if ( $limit ) {
		  	// if there is limit, set the limit
		   	$this->db->limit($limit);
	  	}
	  
	  	if ( $offset ) {
		  	// if there is offset, set the offset,
		   	$this->db->offset($offset);
	  	}

		if ( isset( $conds['order_by_field'] )) {
			$order_by_field = $conds['order_by_field'];
			$order_by_type = $conds['order_by_type'];
			$this->db->order_by( 'bs_items.is_paid', 'desc');
			$this->db->order_by( 'bs_items.'.$order_by_field, $order_by_type);
		} else {
            if ($conds['is_paid']) { 
                $this->db->order_by('CASE  
                    WHEN `bs_paid_items_history.end_timestamp` > '.time().' THEN 1 
                    ELSE 0 END 
                DESC');
            } else {
			$this->db->order_by( 'bs_items.is_paid', 'desc');
            }
			$this->db->order_by('added_date', 'desc' );
		}
   		$query1 = $this->db->get_compiled_select();
   		/*print_r($query1);die;*/

		
   		
	  	// from table
	  	$this->db->from( $this->table_name );

	  	/* query 1 end */

	  	/* query 2 start */
	  	//Start - Modify By PPH @ 12 May 2020

	  	if($conds['lat'] != "" && $conds['lng'] != "") {
			$this->db->select('*,( 3959
		      * acos( cos( radians('. $conds['lat'] .') )
		              * cos(  radians( lat )   )
		              * cos(  radians( lng ) - radians('. $conds['lng'] .') )
		            + sin( radians('. $conds['lat'] .') )
		              * sin( radians( lat ) )
		            )
		    ) as distance');
		    if ($conds['miles'] == "") {
		    	$conds['miles'] = 0;
		    	$this->db->having('distance < ' .  $conds['miles'] );
		    } else {
		    	$this->db->having('distance < ' .  $conds['miles'] );
		    }
		}

		// default where clause
		if (isset( $conds['status'] )) {
			$this->db->where( 'status', $conds['status'] );
		}
		
		if(isset($conds['min_price'])) {
			if ($conds['min_price'] != "" || $conds['min_price'] != 0) {	
				$this->db->where( 'price>=', $conds['min_price'] );	
			}
		}

		if(isset($conds['max_price'])) {
			if ($conds['max_price'] != "" || $conds['max_price'] != 0) {	
				$this->db->where( 'price<=', $conds['max_price'] );
			}
		}

		if(isset($conds['deal_option_id'])) {
			if ($conds['deal_option_id'] != "" || $conds['deal_option_id'] != 0) {
				$this->db->where( 'deal_option_id', $conds['deal_option_id'] );	
			}
		}

		//End - Modify By PPH @ 12 May 2020
   		if(isset($conds['cat_id'])) {
			if ($conds['cat_id'] != "" || $conds['cat_id'] != 0) {
				$this->db->where( 'cat_id', $conds['cat_id'] );	
			}
		}

		if(isset($conds['sub_cat_id'])) {
			if ($conds['sub_cat_id'] != "" || $conds['sub_cat_id'] != 0) {
				$this->db->where( 'sub_cat_id', $conds['sub_cat_id'] );	
			}
		}

		// id condition
		// if ( isset( $conds['added_user_id'] )) {
		// 	$this->db->where( 'added_user_id', $conds['added_user_id'] );
		// }

		// Type id
		// if ( isset( $conds['item_type_id'] )) {
		// 	if ($conds['item_type_id'] != "") {
		// 		if($conds['item_type_id'] != '0'){
		// 			$this->db->where( 'item_type_id', $conds['item_type_id'] );	
		// 		}
		// 	}			
		// }

        // SKV: CHECK CONDITION FOR ITEM TYPE
		if ( isset( $conds['item_type_id'] )) {
			if ($conds['item_type_id'] != "") {
				if($conds['item_type_id'] != '0'){
					$this->db->where_in( 'item_type_id', $conds['item_type_id'] );	
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

		// location id
		if ( isset( $conds['item_location_id'] )) {
			if ($conds['item_location_id'] != "") {
				if($conds['item_location_id'] != '0'){
					$this->db->where( 'item_location_id', $conds['item_location_id'] );	
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

		// condition_of_item id condition
		// if ( isset( $conds['condition_of_item_id'] )) {
			// $this->db->where( 'condition_of_item_id', $conds['condition_of_item_id'] );
		// }

		// item id (id) check for user block condition
		if ( isset( $conds['item_id'] )) {
			if ($conds['item_id'] != "") {
				if($conds['item_id'] != '0'){
					$this->db->where_not_in( 'bs_items.id', $conds['item_id'] );	
				}
			}			
		}

		// item id (id) check for item reported condition
		if ( isset( $conds['reported_item_id'] )) {
			if ($conds['reported_item_id'] != "") {
				if($conds['reported_item_id'] != '0'){
					$this->db->where_not_in( 'bs_items.id', $conds['reported_item_id'] );	
				}
			}			
		}

		// added_user_id id
		if ( isset( $conds['added_user_id'] )) {
			if ($conds['added_user_id'] != "") {
				if($conds['added_user_id'] != '0'){
					$this->db->where( 'bs_items.added_user_id', $conds['added_user_id'] );	
				}
			}			
		}
		if ( isset( $conds['is_paid'] )) {
			$this->db->where( 'bs_items.is_paid', (int)$conds['is_paid'] );	
		}

		if ( isset( $conds['is_draft'] )) {
			$this->db->where( 'bs_items.is_draft', (int)$conds['is_draft'] );	
		}

		// item id (id) check for item color condition
		if ( isset( $conds['coloritem_id'] )) {
			if ($conds['coloritem_id'] != "") {
				if($conds['coloritem_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['coloritem_id'] );	
				}
			}			
		}

		// item id (id) check for sizegroupoption condition
		if ( isset( $conds['sizeoption_item_id'] )) {
			if ($conds['sizeoption_item_id'] != "") {
				if($conds['sizeoption_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['sizeoption_item_id'] );	
				}
			}			
		}
		

		// item check for brand condition
		/*if ( isset( $conds['brand_items_id'] )) {
			if ($conds['brand_items_id'] != "") {
				if($conds['brand_items_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['brand_items_id'] );	
				}
			}			
		}*/

		// SKV: CHECK CONDITION FOR BRAND
		if ( isset( $conds['brand_search'] )) {
			if ($conds['brand_search'] != "") {
				if($conds['brand_search'] != '0'){
					$this->db->where_in( 'bs_items.brand', $conds['brand_search'] );	
				}
			}			
		}

		// item check for itemtype condition
		if ( isset( $conds['itemtype_item_id'] )) {
			if ($conds['itemtype_item_id'] != "") {
				if($conds['itemtype_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['itemtype_item_id'] );	
				}
			}			
		}

		// item check for child subcat condition
		if ( isset( $conds['childsubcat_item_id'] )) {
			if ($conds['childsubcat_item_id'] != "") {
				if($conds['childsubcat_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['childsubcat_item_id'] );	
				}
			}			
		}

		// item check for delivery method condition
		if ( isset( $conds['delivery_method_id'] )) {
			if ($conds['delivery_method_id'] != "") {
				if($conds['delivery_method_id'] != '0'){
					$this->db->where_in( 'bs_items.delivery_method_id', $conds['delivery_method_id'] );	
				}
			}			
		}

		// item check for item condition
		if ( isset( $conds['itemcondition_item_id'] )) {
			if ($conds['itemcondition_item_id'] != "") {
				if($conds['itemcondition_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['itemcondition_item_id'] );	
				}
			}			
		}
		
		// address id (id) check for lat long 
		if ( isset( $conds['address_item_id'] )) {
			if ($conds['address_item_id'] != "") {
				if($conds['address_item_id'] != '0'){
					$this->db->where_in( 'bs_items.id', $conds['address_item_id'] );	
				}
			}			
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


	  	if ( $limit ) {
		  	// if there is limit, set the limit
		   	$this->db->limit($limit);
	  	}
	  
	  	if ( $offset ) {
		  	// if there is offset, set the offset,
		   	$this->db->offset($offset);
	  	}


	  
	  	// $this->db->get();
		// print_r($this->db->last_query());die;
	  	// order by
		if ( isset( $conds['order_by_field'] )) {
			$order_by_field = $conds['order_by_field'];
			$order_by_type = $conds['order_by_type'];
			$this->db->order_by( 'bs_items.is_paid', 'desc');
			$this->db->order_by( 'bs_items.'.$order_by_field, $order_by_type);
		} else {
			$this->db->order_by( 'bs_items.is_paid', 'desc');
			$this->db->order_by('added_date', 'desc' );
		}
	    $query2 = $this->db->get_compiled_select();

	    /* query 2 end */

	    //print_r($query1);die;

	    $query = $this->db->query('( '. $query1 . ' ) UNION DISTINCT (' . $query2 .') ');
//	    print_r('( '. $query1 . ' ) UNION DISTINCT (' . $query2 .') ');die;
		return $query;
	  	
	}

	// get name with status 0 or 1

	function lang_exists( $conds = array()) {

		$sql = "SELECT * FROM bs_language WHERE `name` = '" . $conds['name'] . "' ";


		$query = $this->db->query($sql);

		return $query;
	}
	
	// get symbol with status 0 or 1

	function symbol_exists( $conds = array()) {

		$sql = "SELECT * FROM bs_language WHERE `symbol` = '" . $conds['symbol'] . "' ";
		

		$query = $this->db->query($sql);

		return $query;
	}

	// get user with phone conds

	function get_one_user_phone( $conds = array()) {

		$sql = "SELECT * FROM core_users WHERE `user_phone` = '" . $conds['user_phone'] . "' ";

		$query = $this->db->query($sql);

		return $query;
	}


	//get all chat not nego price 0

	function get_all_chat( $conds = array()) {
		//print_r($conds['id']);die;

		$this->db->select('bs_chat_history.*');    
  		$this->db->from('bs_chat_history');

  		if(isset($conds['buyer_user_id'])) {

			if ($conds['buyer_user_id'] != "" || $conds['buyer_user_id'] != 0) {
					
					$this->db->where( 'buyer_user_id', $conds['buyer_user_id'] );	

			}

		}

		if(isset($conds['seller_user_id'])) {

			if ($conds['seller_user_id'] != "" || $conds['seller_user_id'] != 0) {
					
					$this->db->where( 'seller_user_id', $conds['seller_user_id'] );	

			}

		}

		// item id (id) check for user block condition
		if ( isset( $conds['item_id'] )) {
			
			if ($conds['item_id'] != "") {
				if($conds['item_id'] != '0'){
				
					$this->db->where_not_in( 'id', $conds['item_id'] );	
				}

			}			
		}

		// item id (id) check for item reported condition
		if ( isset( $conds['chat_id'] )) {
			
			if ($conds['chat_id'] != "") {
				if($conds['chat_id'] != '0'){
				
					$this->db->where_not_in( 'id', $conds['chat_id'] );	
				}

			}			
		}


		$this->db->where( 'nego_price!=', $conds['nego_price'] );
		return $this->db->get();
		// print_r($this->db->last_query());die;
	}	

	/**
	Returns city Admin 
	*/
	function get_all_module( )
	{
	
		$this->db->select('core_modules.*');    
  		$this->db->from('core_modules');
  		$this->db->where('is_show_on_menu',1);
  		$this->db->order_by('group_id','AESC');
		return $this->db->get();

	}
	
	//get user with email and phone

	function get_email_phone( $conds = array()) {

		$sql = "SELECT * FROM core_users WHERE `user_email` = '" . $conds['user_email'] . "' AND `phone_id` = '" . $conds['phone_id'] . "' ";

		$query = $this->db->query($sql);

		return $query;
	}

	//get where in added_user_id at item table

	function get_all_in_item( $conds = array() ) {


		$sql = "SELECT * FROM `bs_items` WHERE `added_user_id` IN(". $conds['added_user_id'] .")";

		$query = $this->db->query($sql);
		///print_r($this->db->last_query());die;
		return $query;



	}

	//get where in buyer user id at chat history

	function get_all_in_chat_buyer( $conds = array() ) {


		$sql = "SELECT * FROM `bs_chat_history` WHERE `buyer_user_id` IN(". $conds['buyer_user_id'] .")";

		$query = $this->db->query($sql);
		//print_r($this->db->last_query());die;
		return $query;



	}

	//get where in seller user id at chat history

	function get_all_in_chat_seller( $conds = array() ) {


		$sql = "SELECT * FROM `bs_chat_history` WHERE `seller_user_id` IN(". $conds['seller_user_id'] .")";

		$query = $this->db->query($sql);
		//print_r($this->db->last_query());die;
		return $query;



	}

	//get where in item id at chat history

	function get_all_in_chat_item( $conds = array() ) {


		$sql = "SELECT * FROM `bs_chat_history` WHERE `item_id` IN(". $conds['item_id'] .")";

		$query = $this->db->query($sql);
		//print_r($this->db->last_query());die;
		return $query;



	}

	//get where in item id at chat history

	function get_all_in_exchange_chat_item( $conds = array() ) {

		// $sql = "SELECT * FROM `bs_chat_history` WHERE 1=1 ";

		// // item id (id) check for item reported condition
		// if ( isset( $conds['chat_id'] )) {
					
		// 	if ($conds['chat_id'] != "") {
		// 		if($conds['chat_id'] != '0'){
		// 			$sql .= " AND 'chat_id' INf" 
		// 			$this->db->where_in( 'chat_id', $conds['chat_id'] );	
		// 		}

		// 	}			
		// }

		// if ( isset( $conds['offered_item_id'] )) {
			
		// 	if ($conds['offered_item_id'] != "") {
		// 		if($conds['offered_item_id'] != '0'){
				
		// 			$this->db->where_in( 'offered_item_id', $conds['offered_item_id'] );	
		// 		}

		// 	}			
		// }

		// $query = $this->db->query($sql);
		// //print_r($this->db->last_query());die;
		// return $query;


		$this->db->select('bs_exchange_chat_history.*');    
  		$this->db->from('bs_exchange_chat_history');

		// item id (id) check for item reported condition
		if ( isset( $conds['chat_id'] )) {
			
			if ($conds['chat_id'] != "") {
				if($conds['chat_id'] != '0'){
				
					$this->db->where_in( 'chat_id', $conds['chat_id'] );	
				}

			}			
		}

		if ( isset( $conds['offered_item_id'] )) {
			
			if ($conds['offered_item_id'] != "") {
				if($conds['offered_item_id'] != '0'){
				
					$this->db->where_in( 'offered_item_id', $conds['offered_item_id'] );	
				}

			}			
		}

		return $this->db->get()->result();
	
	}

	//get all chat when there is user blocked

	function get_all_chat_history( $conds = array()) {
		//print_r($conds);die;

		$this->db->select('bs_chat_history.*');    
  		$this->db->from('bs_chat_history');

  		if(isset($conds['buyer_user_id'])) {

			if ($conds['buyer_user_id'] != "" || $conds['buyer_user_id'] != 0) {
					
					$this->db->where( 'buyer_user_id', $conds['buyer_user_id'] );	

			}

		}

		if(isset($conds['seller_user_id'])) {

			if ($conds['seller_user_id'] != "" || $conds['seller_user_id'] != 0) {
					
					$this->db->where( 'seller_user_id', $conds['seller_user_id'] );	

			}

		}

		// item id (id) check for user block condition
		if ( isset( $conds['item_id'] )) {
			
			if ($conds['item_id'] != "") {
				if($conds['item_id'] != '0'){
				
					$this->db->where_not_in( 'id', $conds['item_id'] );	
				}

			}			
		}

		// item id (id) check for item reported condition
		if ( isset( $conds['chat_id'] )) {
			
			if ($conds['chat_id'] != "") {
				if($conds['chat_id'] != '0'){
				
					$this->db->where_not_in( 'id', $conds['chat_id'] );	
				}

			}			
		}

		return $this->db->get();
		// print_r($this->db->last_query());die;
	}

	//get where in id at item table

	function get_all_in_report( $conds = array() ) {


		$sql = "SELECT * FROM `bs_items` WHERE `id` IN(". $conds['id'] .")";

		$query = $this->db->query($sql);
		///print_r($this->db->last_query());die;
		return $query;



	}	


	//get where in blocked user

	function get_all_in_blocked_user( $conds = array(), $limit = false, $offset = false ) {

	  	$sql = "SELECT * FROM `core_users` WHERE `user_id` IN(". $conds['user_id'] .") LIMIT $limit OFFSET $offset ";

		$query = $this->db->query($sql);

		return $query;

	} 


	//get where in reported item

	function get_all_in_reported_item( $conds = array(), $limit = false, $offset = false ) {

	  	$sql = "SELECT * FROM `bs_items` WHERE `id` IN(". $conds['id'] .") AND `status` = '" . $conds['status'] . "'  LIMIT $limit OFFSET $offset ";

		$query = $this->db->query($sql);

		return $query;

	} 

	
}