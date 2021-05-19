<?php
require_once( APPPATH .'libraries/REST_Controller.php' );

/**
 * REST API for Addresses
 */
class Address extends API_Controller
{

	/**
	 * Constructs Parent Constructor
	 */
	function __construct()
	{
		parent::__construct( 'Addresses' );
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

			// get default setting for GET_ALL_CATEGORIES
			//$setting = $this->Api->get_one_by( array( 'api_constant' => GET_ALL_CATEGORIES ));

			$conds['order_by'] = 1;
			$conds['order_by_field'] = $setting->order_by_field;
			$conds['order_by_type'] = $setting->order_by_type;
		}
		

		if ( $this->is_search ) {

			//$setting = $this->Api->get_one_by( array( 'api_constant' => SEARCH_WALLPAPERS ));

			if($this->post('searchterm') != "") {
				$conds['searchterm']   = $this->post('searchterm');
			}

			if($this->post('user_id') != "") {
				$conds['user_id']   = $this->post('user_id');
			}

			$conds['order_by'] = 1;
			$conds['order_by_field']    = $this->post('order_by');
			$conds['order_by_type']     = $this->post('order_type');
				
		}

		return $conds;
	}

	function add_post() {
		
		// validation rules for add address
		$rules = array(
			array(
	        	'field' => 'address1',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'zipcode',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'state',
	        	'rules' => 'required'
	        ),
	       
	        array(
	        	'field' => 'city',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'latitude',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'longitude',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'user_id',
	        	'rules' => 'required'
	        )

        );

        $lat = $this->post('latitude');
		$lng = $this->post('longitude');
        $location = location_check($lat,$lng);

        // exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;
        
	  	$address_data = array(

        	"address1" => $this->post('address1'),
			"address2" => (!empty($this->post('address2')))?$this->post('address2'):'', 
        	"zipcode" => $this->post('zipcode'),
        	"state" => $this->post('state'),
        	"city" => $this->post('city'),
        	"latitude" => $this->post('latitude'), 
        	"longitude" => $this->post('longitude'),
        	"user_id" => $this->post('user_id'),
			"is_home_address" => (!empty($this->post('is_home_address')))?$this->post('is_home_address'):'0', 
			"is_default_address" => (!empty($this->post('is_default_address')))?$this->post('is_default_address'):'0', 
        	"id" => $this->post('id'),
        	"added_date" =>  date("Y-m-d H:i:s")
        	
        );

		$id = $address_data['id'];

		$usercheck  = $this->User->get_one($this->post('user_id'));
		
		if(isset($usercheck->is_empty_object))
		{
			$this->error_response( get_msg( 'User not found' ));
		}
		else
		{
			if($id != ""){

				$addresscheck  = $this->Addresses->get_one($id);
				if(isset($addresscheck->is_empty_object))
				{
					$this->error_response( get_msg( 'Address not found' ));
				}
				else
				{
					// Edit address
					$this->Addresses->save($address_data,$id);
				}

				
			} else{
	
				 $this->Addresses->save($address_data);
	
				 $id = $address_data['id'];
				 
			}
			 
			$obj = $this->Addresses->get_one( $id );
			
			$this->ps_adapter->convert_item( $obj );
			$this->custom_response( $obj );
		}
		
	}

	function fetch_address_post( ) {

		$rules = array(
			array(
	        	'field' => 'user_id',
	        	'rules' => 'required'
	        )
	    );   
	    
	    // exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        $id = $this->post('user_id');

        $condscstm['user_id'] = $this->post('user_id');

        // check user id
		$usercheck  = $this->User->get_one($this->post('user_id'));

        
        if(isset($usercheck->is_empty_object))
		{
        	$this->error_response( get_msg( 'Invalid User id' ));

        } else {

			
			$data = $this->Addresses->get_all_by( $condscstm )->result();

        	$this->custom_response( $data );

        }


	}


	/**
	* delete Address data
	*/

	function address_delete_post( ) {

		$rules = array(
			array(
	        	'field' => 'address_id',
	        	'rules' => 'required'
	        )
	    );   
	    
	    // exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        $id = $this->post('address_id');

        $conds['id'] = $id;

        // check address id
		$address_data = $this->Addresses->get_one_by($conds);

        
        if ( $address_data->id == "") {

        	$this->error_response( get_msg( 'Invalid Address id' ));

        } else {
			
			$this->ps_delete->delete_address($this->post('address_id'));
        	
			$this->success_response( get_msg( 'success_delete' ));

        }


	}

	
}