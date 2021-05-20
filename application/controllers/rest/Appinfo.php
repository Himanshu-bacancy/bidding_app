<?php
require_once( APPPATH .'libraries/REST_Controller.php' );

/**
 * REST API for About
 */
class Appinfo extends API_Controller
{
	/**
	 * Constructs Parent Constructor
	 */
	function __construct()
	{
		// call the parent
		parent::__construct( 'Appinfo' );		
	}

	/**
	 * Convert Object
	 */
	function convert_object( &$obj )
	{
		// call parent convert object
		parent::convert_object( $obj );

	}

	function similar_item_criteria_get()
	{
		$rows = '[{"id":1,"title":"Brand"},{"id":2,"title":"Size"},{"id":3,"title":"Condition"},{"id":4,"title":"Color"}]';

		$objArray = json_decode($rows);

		$this->custom_response( $objArray );		
	}
}