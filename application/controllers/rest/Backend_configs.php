<?php
require_once( APPPATH .'libraries/REST_Controller.php' );

/**
 * REST API for Notification
 */
class Backend_configs extends API_Controller
{
	/**
	 * Constructs Parent Constructor
	 */
	function __construct()
	{
		// call the parent
		parent::__construct( 'Backend_configs' );

	}

	// GET FEES API	
	function get_fees_detail_get(){
		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);

		$config_data = $this->Backend_config->get_one_by();
		$chat_data_update = array(
			"selling_fees" => $config_data->selling_fees,
			"processing_fees" => $config_data->processing_fees, 
		);
		
		$this->custom_response( $chat_data_update );
	}

}