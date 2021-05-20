<?php
require_once( APPPATH .'libraries/REST_Controller.php' );

/**
 * REST API for Deliverymethods
 */
class Deliverymethod extends API_Controller
{

	/**
	 * Constructs Parent Constructor
	 */
	function __construct()
	{
		parent::__construct( 'Deliverymethods' );
	}

	/**
	 * Default Query for API
	 * @return [type] [description]
	 */
	function default_conds()
	{
		$conds = array();

		if ( $this->is_get ) {
		// if is get record using GET method

		}

		return $conds;
	}

	/**
	 * Convert Object
	 */
	function convert_object( &$obj )
	{

		// call parent convert object
		parent::convert_object( $obj );

	}

	/**
	 * Save and update delivery method id in Users table
	 * @param      <type>   $delivery_method_id  The  Delivery method id
	 * @param      <type>   $user_id  The  User id
	 */
	function user_accept_delivery_post()
	{
		// validation rules
		$rules = array(
			array(
	        	'field' => 'delivery_method_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'user_id',
	        	'rules' => 'required'
	        )
	    );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

		$deliverycheck  = $this->Deliverymethods->get_one($this->post('delivery_method_id'));
		$usercheck  = $this->User->get_one($this->post('user_id'));

		if(isset($deliverycheck->is_empty_object))
		{
			$this->error_response( get_msg( 'delivery_method_not_found' ));
		}

		else if(isset($usercheck->is_empty_object))
		{
			$this->error_response( get_msg( 'user_not_found' ));
		}

		else
		{
			$delivery_data['accept_delivery_id'] = $this->post('delivery_method_id');
			$this->User->save($delivery_data,$this->post('user_id'));

			$this->success_response( get_msg( 'accept_delivery_updated' ));
		}
	}

}