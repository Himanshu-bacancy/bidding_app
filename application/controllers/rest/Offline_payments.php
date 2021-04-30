<?php
require_once( APPPATH .'libraries/REST_Controller.php' );

/**
 * REST API for News
 */
class Offline_payments extends API_Controller
{

	/**
	 * Constructs Parent Constructor
	 */
	function __construct()
	{
		parent::__construct( 'Offline_payment' );
	}

	/**
	 * Convert Object
	 */
	function convert_object( &$obj )
	{
	
		// call parent convert object
		parent::convert_object( $obj );

	}

}