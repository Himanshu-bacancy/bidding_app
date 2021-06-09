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

	/**
	 * Add / Update Address of login users
	 * 1) Add address
	 * 2) Edit address if id already there
	 * @param      <type>   $address1  The  address1
	 * @param      <type>   $address2  The  address2 (optional)
	 * @param      <type>   $zipcode  The  zipcode
	 * @param      <type>   $state  The  state
	 * @param      <type>   $city  The  city
	 * @param      <type>   $country  The  country
	 * @param      <type>   $latitude  The  latitude
	 * @param      <type>   $longitude  The  longitude
	 * @param      <type>   $user_id  The  User id
	 * @param      <type>   $is_home_address  if its home address (optional)
	 * @param      <type>   $is_default_address  Make default address (optional)
	 */

	function add_post() {
		
		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
		if(!empty($user_data) && $user_data['token_data']){
			// return data
			// $this->api_return(
			//     [
			//         'status' => true,
			//         "result" => [
			//             'user_data' => $user_data['token_data']
			//         ],
			//     ],
			// 200);

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
					'field' => 'country',
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
				"country" => $this->post('country'),
				"latitude" => $this->post('latitude'), 
				"longitude" => $this->post('longitude'),
				"user_id" => $this->post('user_id'),
				"is_home_address" => (($this->post('is_home_address')=='1'))?'1':'0', 
				"is_default_address" => (($this->post('is_default_address')=='1'))?'1':'0', 
				"id" => $this->post('id')
			);

			$id = $address_data['id'];

			// Validation for only one home address and default address 

			$homedata = $this->db->get_where('bs_addresses', array('is_home_address' => 1 ,'user_id' => $this->post('user_id')));

			$defaultdata = $this->db->get_where('bs_addresses', array('is_default_address' => 1 ,'user_id' => $this->post('user_id')));
			
			$usercheck  = $this->User->get_one($this->post('user_id'));
			
			if(isset($usercheck->is_empty_object))
			{
				$this->error_response( get_msg( 'user_not_found' ));
			}
			else
			{
				if($id != ""){

					$addresscheck  = $this->Addresses->get_one($id);
					if(isset($addresscheck->is_empty_object))
					{
						$this->error_response( get_msg( 'address_not_found' ));
					}
					else
					{
						// Validation for only one home address and default address 
						$this->db->select('*');
						$this->db->from('bs_addresses');
						$this->db->where(array('is_home_address' => 1 ,'user_id' => $this->post('user_id')));
						$this->db->where_not_in('id',$id);
						$homedataedit = $this->db->get();
						
						$this->db->select('*');
						$this->db->from('bs_addresses');
						$this->db->where(array('is_default_address' => 1 ,'user_id' => $this->post('user_id')));
						$this->db->where_not_in('id',$id);
						$defaultdataedit = $this->db->get();
						
						// if(($homedataedit->num_rows() >= 1) && ($this->post('is_home_address') =='1'))
						// {
						// 	$this->error_response( get_msg( 'home_address_exist' ));
						// }
						// else if(($defaultdataedit->num_rows() >= 1) && ($this->post('is_default_address') =='1'))
						// {
						// 	$this->error_response( get_msg( 'default_address_exist' ));
						// }
						// else
						// {
							
						// 	// Edit address
						// 	$address_data['updated_date'] =  date("Y-m-d H:i:s");
						// 	$this->Addresses->save($address_data,$id);
						// }

						if($this->post('is_home_address')=='1')
						{
							$this->db->set('is_home_address', '0');
							$this->db->where('is_home_address', '1');
							$this->db->where('user_id', $this->post('user_id'));
							$this->db->where('id !=', $id);
							$this->db->update('bs_addresses'); 
						}

						if($this->post('is_default_address')=='1')
						{
							$this->db->set('is_default_address', '0');
							$this->db->where('is_default_address', '1');
							$this->db->where('user_id', $this->post('user_id'));
							$this->db->where('id !=', $id);
							$this->db->update('bs_addresses'); 
						}

						$address_data['updated_date'] =  date("Y-m-d H:i:s");
						$this->Addresses->save($address_data,$id);
						
					}

					
				} else{
		
					// if(($homedata->num_rows() >= 1) && ($this->post('is_home_address') =='1'))
					// {
					// 	$this->error_response( get_msg( 'home_address_exist' ));
					// }
					// else if(($defaultdata->num_rows() >= 1) && ($this->post('is_default_address') =='1'))
					// {
					// 	$this->error_response( get_msg( 'default_address_exist' ));
					// }
					// else
					// {
					// 	$address_data['added_date'] =  date("Y-m-d H:i:s");
					// 	$this->Addresses->save($address_data);
		
					// 	$id = $address_data['id'];
					// }

					if($this->post('is_home_address')=='1')
					{
						$this->db->set('is_home_address', '0');
						$this->db->where('is_home_address', '1');
						$this->db->where('user_id', $this->post('user_id'));
						$this->db->update('bs_addresses'); 
					}

					if($this->post('is_default_address')=='1')
					{
						$this->db->set('is_default_address', '0');
						$this->db->where('is_default_address', '1');
						$this->db->where('user_id', $this->post('user_id'));
						$this->db->update('bs_addresses'); 
					}

					$address_data['added_date'] =  date("Y-m-d H:i:s");
					$this->Addresses->save($address_data);

					$id = $address_data['id'];
				}
				
				$obj = $this->Addresses->get_one( $id );
				
				//$this->ps_adapter->convert_item( $obj );
				$this->custom_response( $obj );
			}
		} 
		// } else {
		// 	//echo $user_data->error; die;
		// 	$obj = [
		// 		'status'=>false,
		// 		'message'=>$user_data->error,

		// 	];
		// 	$this->custom_response();
		// }
	}

	/**
	 * Fetch Address of login users
	 * 1) Address list
	 * @param      <type>   $user_id  The User id
	 */

	function fetch_address_post( ) {

		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);

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
        	$this->error_response( get_msg( 'invalid_user_id' ));

        } else {

			
			$data = $this->Addresses->get_all_by( $condscstm )->result();

        	$this->custom_response( $data );

        }


	}


	/**
	 * Fetch default Address of login users
	 * 1) Default Address 
	 * @param      <type>   $user_id  The User id
	 */

	function fetch_defaultaddress_post( ) {

		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);

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
		$condscstm['is_default_address'] = '1';

        // check user id
		$usercheck  = $this->User->get_one($this->post('user_id'));

        
        if(isset($usercheck->is_empty_object))
		{
        	$this->error_response( get_msg( 'invalid_user_id' ));

        } else {

			$this->db->select('*');
			$this->db->from('bs_addresses');
			$this->db->where(array('is_default_address' => 1 ,'user_id' => $this->post('user_id')));
			$defaultdata = $this->db->get();
			//$data = $this->Addresses->get_all_by( $condscstm )->result();

        	$this->custom_response( $defaultdata->result() );

        }


	}

	/**
	 * set default address
	 * @param      <type>   $address_id  The Address id
	 */

	 function set_default_address_post()
	 {
		 // API Configuration [Return Array: User Token Data]
		 $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
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

        	$this->error_response( get_msg( 'invalid_address_id' ));

        } else {

			$this->db->set('is_default_address', '0');
			$this->db->where('is_default_address', '1');
			$this->db->where('user_id', $address_data->user_id);
			$this->db->update('bs_addresses'); 

			
			$address_data1['is_default_address'] =  '1';
			$address_data1['updated_date'] =  date("Y-m-d H:i:s");
			$this->Addresses->save($address_data1,$id);

        	$this->success_response( get_msg( 'success_default_address' ));

        }
	 }

	 /**
	 * set home address
	 * @param      <type>   $address_id  The Address id
	 */

	 function set_home_address_post()
	 {
		 // API Configuration [Return Array: User Token Data]
		 $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);

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

        	$this->error_response( get_msg( 'invalid_address_id' ));

        } else {

			$this->db->set('is_home_address', '0');
			$this->db->where('is_home_address', '1');
			$this->db->where('user_id', $address_data->user_id);
			$this->db->update('bs_addresses'); 

			
			$address_data1['is_home_address'] =  '1';
			$address_data1['updated_date'] =  date("Y-m-d H:i:s");
			$this->Addresses->save($address_data1,$id);

        	$this->success_response( get_msg( 'success_home_address' ));

        }
	 }


	/**
	 * delete Address data
	 * @param      <type>   $address_id  The Address id
	 */

	function address_delete_post( ) {

		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
		
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

        	$this->error_response( get_msg( 'invalid_address_id' ));

        } else {
			
			$this->Addresses->delete( $this->post('address_id') );
			
			$this->success_response( get_msg( 'success_delete' ));

        }


	}

	
}