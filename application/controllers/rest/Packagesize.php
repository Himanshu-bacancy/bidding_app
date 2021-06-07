<?php
require_once( APPPATH .'libraries/REST_Controller.php' );

/**
 * REST API for Packagesize
 */
class Packagesize extends API_Controller
{

	/**
	 * Constructs Parent Constructor
	 */
	function __construct()
	{
		parent::__construct( 'Packagesizes' );
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
		$this->ps_adapter->convert_packagesize( $obj );

	}

	function getdata_get()
	{
		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['GET'],
            'requireAuthorization' => true,
        ]);

		// add flag for default query
		$this->is_get = true;

		// get id
		$id = $this->get( 'id' );

		if ( $id ) {
			
			// if 'id' is existed, get one record only
			$data = $this->model->get_one( $id );

			if ( isset( $data->is_empty_object )) {
			// if the id is not existed in the return object, the object is empty
				
				$data = array();
			}

			$this->custom_response( $data );
		}

		// get limit & offset
		$limit = $this->get( 'limit' );
		$offset = $this->get( 'offset' );


		// get search criteria
		$default_conds = $this->default_conds();
		$user_conds = $this->get();
		$conds = array_merge( $default_conds, $user_conds );

		if ( $limit ) {
			unset( $conds['limit']);
		}

		if ( $offset ) {
			unset( $conds['offset']);
		}


		if ( count( $conds ) == 0 ) {
		// if 'id' is not existed, get all	
		
			if ( !empty( $limit ) && !empty( $offset )) {
			// if limit & offset is not empty
				
				$data = $this->model->get_all( $limit, $offset )->result();
			} else if ( !empty( $limit )) {
			// if limit is not empty
				
				$data = $this->model->get_all( $limit )->result();
			} else {
			// if both are empty

				$data = $this->model->get_all()->result();
			}

			$this->custom_response( $data , $offset );
		} else {

			if ( !empty( $limit ) && !empty( $offset )) {
			// if limit & offset is not empty

				$data = $this->model->get_all_by( $conds, $limit, $offset )->result();
			} else if ( !empty( $limit )) {
			// if limit is not empty

				$data = $this->model->get_all_by( $conds, $limit )->result();
			} else {
			// if both are empty

				$data = $this->model->get_all_by( $conds )->result();
			}

			// foreach($data as $childkey => $sizedata)
			// {
			// 	$condscstm = array();
			// 	$this->ps_adapter->convert_shippingcarrier( $sizedata );
			// 	$condscstm['packagesize_id'] = $sizedata->id;

			// 	$carrierarray = $this->Shippingcarriers->get_all_by( $condscstm);

			// 	$carrierdata = $carrierarray->result();

			// 	foreach($carrierdata as $carrierkey => $carrier)
			// 	{
			// 		$carrierdata[$carrierkey]->default_icon = $this->get_default_photo( $carrier->id, 'shippingcarrier_icon' );
			// 	}
				
			// 	$data[$childkey]->shippingcarriers = $carrierdata;
			// }

			$this->custom_response( $data , $offset );
		}
	}

}