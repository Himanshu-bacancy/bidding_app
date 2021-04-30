<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for about table
 */
class Paid_item extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct() 
	{
		parent::__construct( 'bs_paid_items_history', 'id', 'his' );
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

		// item_id condition
		if ( isset( $conds['item_id'] )) {
			$this->db->where( 'item_id', $conds['item_id'] );
		}
		// amount condition
		if ( isset( $conds['amount'] )) {
			$this->db->where( 'amount', $conds['amount'] );
		}

		// payment_method condition
		if ( isset( $conds['payment_method'] )) {
			$this->db->where( 'payment_method', $conds['payment_method'] );
		}

		// added_user_id condition
		if ( isset( $conds['added_user_id'] )) {
			$this->db->where( 'added_user_id', $conds['added_user_id'] );
		}

		if ( isset( $conds['searchterm'] )) {
			$this->db->where_in( 'item_id', $conds['searchterm'] );
		}

		//for status
		if(isset($conds['status'])) {
			$today_date = date('Y-m-d');
			if ($conds['status'] == 1) {
				//for progress
				$this->db->where( 'date(start_date) <= ', $today_date );
   				$this->db->where( 'date(end_date) >= ', $today_date );
			} elseif ($conds['status'] == 2) {
				//for finished
				$this->db->where( 'date(start_date) < ', $today_date );
   				$this->db->where( 'date(end_date) < ', $today_date );
			} elseif ($conds['status'] == 3) {
				//for not yet start
				$this->db->where( 'date(start_date) > ', $today_date );
   				$this->db->where( 'date(end_date) > ', $today_date );
			}
		}

		//for date
		if (isset( $conds['date'] )) {

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
			
			if ($mindate != "" && $maxdate != "") {
				//got 2dates
				if ($mindate == $maxdate ) {

					$this->db->where("added_date BETWEEN DATE('".$mindate."' - INTERVAL 1 DAY) AND DATE('". $maxdate."' + INTERVAL 1 DAY)");

				} else {

					$today_date = date('Y-m-d');
					if($today_date == $maxdate) {
						$current_time = date('H:i:s');
						$maxdate = $maxdate . " ". $current_time;
					}

					$this->db->where( 'date(start_date) >=', $mindate );
   					$this->db->where( 'date(end_date) <=', $maxdate );

				}
				
			}
			 
	    }

	    $this->db->order_by( 'added_date', 'desc' );

	}
}