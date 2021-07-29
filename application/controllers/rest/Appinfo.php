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

	// To fetch similar items while adding product
	function similar_item_criteria_get()
	{
		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['GET'],
            'requireAuthorization' => true,
        ]);
		$data = $this->db->get('bs_similar_criterias')->result();
		foreach($data as $key=>$similarcriteria){
			$data[$key]->default_icon = $this->get_default_photo( $similarcriteria->id, 'similarcriteria_icon' );
		}
		//$rows = '[{"id":1,"title":"Brand"},{"id":2,"title":"Size"},{"id":3,"title":"Condition"},{"id":4,"title":"Color"}]';

		//$objArray = json_decode($data);

		$this->custom_response( $data );		
	}
}