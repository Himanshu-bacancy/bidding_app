<?php
require_once( APPPATH .'libraries/REST_Controller.php' );

/**
 * REST API for Shippingcarrier
 */
class Shippingcarrier extends API_Controller
{

	/**
	 * Constructs Parent Constructor
	 */
	function __construct()
	{
		parent::__construct( 'Shippingcarriers' );
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

		$conds['order_by'] = 1;
		$conds['order_by_field'] = 'id';
		$conds['order_by_type'] = 'asc';
		return $conds;
	}

	/**
	 * Convert Object
	 */
	function convert_object( &$obj )
	{

		// call parent convert object
		parent::convert_object( $obj );

		// convert customize item object
		$this->ps_adapter->convert_shippingcarrier( $obj );

	}

	/**
	 * Fetch shipping carriers of any packagesize
	 * 1) Shipping carrier list
	 * @param      <type>   $packagesize_id  The Packagesize id
	 */

	function getdata_post()
	{
		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
		
		$rules = array(
			array(
	        	'field' => 'packagesize_id',
	        	'rules' => 'required'
	        )
	    );   
	    
	    // exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

		$id = $this->post('packagesize_id');

        $condscstm['packagesize_id'] = $this->post('packagesize_id');

        // check packagesize id
		$packagecheck  = $this->Packagesizes->get_one($this->post('packagesize_id'));

        
        if(isset($packagecheck->is_empty_object))
		{
        	$this->error_response( get_msg( 'invalid_packagesize_id' ));

        } else {

			
			$data = $this->Shippingcarriers->get_all_by( $condscstm )->result();

        	$this->custom_response( $data );

        }
	}

}