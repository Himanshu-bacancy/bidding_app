<?php
require_once( APPPATH .'libraries/REST_Controller.php' );


/**
 * REST API for Notification
 */
class Chats extends API_Controller
{
	/**
	 * Constructs Parent Constructor
	 */
	function __construct()
	{
		// call the parent
		parent::__construct( 'Chat' );

	}

	/**
	 * Add Chat History
	 */
	function add_post()
	{

		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
		// validation rules for chat history
		$rules = array(
			array(
	        	'field' => 'item_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'buyer_user_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'seller_user_id',
	        	'rules' => 'required'
	        )

        );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;
        $type = $this->post('type');

        $chat_data = array(

        	"item_id" => $this->post('item_id'), 
        	"buyer_user_id" => $this->post('buyer_user_id'), 
        	"seller_user_id" => $this->post('seller_user_id')

        );

        $chat_data_count = $this->Chat->count_all_by($chat_data);

        if ($chat_data_count > 1) {
        	$this->Chat->delete_by($chat_data);
        }

        $chat_history_data = $this->Chat->get_one_by($chat_data);


        if($chat_history_data->id == "") {

        	if ( $type == "to_buyer" ) {

		    	$buyer_unread_count = $chat_history_data->buyer_unread_count;
		    	
		    	$chat_data = array(

		        	"item_id" => $this->post('item_id'), 
		        	"buyer_user_id" => $this->post('buyer_user_id'), 
		        	"seller_user_id" => $this->post('seller_user_id'),
		        	"buyer_unread_count" => $buyer_unread_count + 1,
		        	"added_date" => date("Y-m-d H:i:s")

		        );

		    	} elseif ( $type == "to_seller" ) {

		    	$seller_unread_count = $chat_history_data->seller_unread_count;
		    	
		    	$chat_data = array(

		        	"item_id" => $this->post('item_id'), 
		        	"buyer_user_id" => $this->post('buyer_user_id'), 
		        	"seller_user_id" => $this->post('seller_user_id'),
		        	"seller_unread_count" => $seller_unread_count + 1,
		        	"added_date" => date("Y-m-d H:i:s")

		        );

		    	}

	        if ( !$this->Chat->save($chat_data)) {
	        	
	        	$this->error_response( get_msg( 'err_chat_history_save' ));
	        
	        } else {

	        	$obj = $this->Chat->get_one_by($chat_data);
				$this->ps_adapter->convert_chathistory( $obj );
				$this->custom_response( $obj );

	        }

	    } else {

	    	if ( $type == "to_buyer" ) {

		    	$buyer_unread_count = $chat_history_data->buyer_unread_count;
		    	
		    	$chat_data = array(

		        	"item_id" => $this->post('item_id'), 
		        	"buyer_user_id" => $this->post('buyer_user_id'), 
		        	"seller_user_id" => $this->post('seller_user_id'),
		        	"buyer_unread_count" => $buyer_unread_count + 1,
		        	"added_date" => date("Y-m-d H:i:s")

		        );

		    	} elseif ( $type == "to_seller" ) {

		    	$seller_unread_count = $chat_history_data->seller_unread_count;
		    	
		    	$chat_data = array(

		        	"item_id" => $this->post('item_id'), 
		        	"buyer_user_id" => $this->post('buyer_user_id'), 
		        	"seller_user_id" => $this->post('seller_user_id'),
		        	"seller_unread_count" => $seller_unread_count + 1,
		        	"added_date" => date("Y-m-d H:i:s")

		        );

		    	}
	    	

	    	if ( $this->Chat->save($chat_data,$chat_history_data->id)) {
	        	
	        	$obj = $this->Chat->get_one_by($chat_data);
				$this->ps_adapter->convert_chathistory( $obj );
				$this->custom_response( $obj );
	        
	        }

	    }


	}


	/**
	 * Update Price 
	 */
	function update_price_post(){
		// validation rules for chat history
		$rules = array(
			array(
	        	'field' => 'requested_item_id',
	        	'rules' => 'required'
	        ),
			array(
	        	'field' => 'offered_item_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'buyer_user_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'seller_user_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'nego_price',
	        	'rules' => 'required'
			),
	        array(
	        	'field' => 'type',
	        	'rules' => 'required'
	        )
        );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;
		$requestedItemId = $this->post('requested_item_id');
		$offeredItemId = $this->post('offered_item_id');

		$requestedItemDetails = $this->Item->get_one( $requestedItemId );
		$offeredItemDetails = $this->Item->get_one( $offeredItemId );
		
		if($requestedItemDetails->cat_id != $offeredItemDetails->cat_id){
			$this->error_response( get_msg( 'err_make_offer_category_validation_error' ));
		}
		if($requestedItemDetails->sub_cat_id != $offeredItemDetails->sub_cat_id){
			$this->error_response( get_msg( 'err_make_offer_category_validation_error' ));
		}
		if($requestedItemDetails->childsubcat_id != $offeredItemDetails->childsubcat_id){
			$this->error_response( get_msg( 'err_make_offer_category_validation_error' ));
		}
        $type = $this->post('type');
		$chat_data = array(
        	"requested_item_id" => $this->post('requested_item_id'),
			"offered_item_id" => $this->post('offered_item_id'), 
        	"buyer_user_id" => $this->post('buyer_user_id'), 
        	"seller_user_id" => $this->post('seller_user_id')
        );

        $chat_history_data = $this->Chat->get_one_by($chat_data);
        if($chat_history_data->id == "") {

        	if ( $type == "to_buyer" ) {

        		//prepare data for noti
		    	$user_ids[] = $this->post('buyer_user_id');
				
	        	$devices = $this->Noti->get_all_device_in($user_ids)->result();
	        	//echo '<pre>'; print_r($devices); die;	

				$device_ids = array();
				if ( count( $devices ) > 0 ) {
					foreach ( $devices as $device ) {
						$device_ids[] = $device->device_token;
					}
				}
	        	
				$user_id = $this->post('buyer_user_id');
	       		$user_name = $this->User->get_one($user_id)->user_name;
				
		    	$price = $this->post('nego_price');
	       		if ( $price == 0) {
		    		$data['message'] = "Offer Rejected!";
		    	} else {
		    		$data['message'] = "Make Offer!";
		    	}
		    	$data['buyer_user_id'] = $this->post('buyer_user_id');
		    	$data['seller_user_id'] = $this->post('seller_user_id');
		    	$data['sender_name'] = $user_name;
		    	$data['requested_item_id'] = $this->post('requested_item_id');
				$data['offered_item_id'] = $this->post('offered_item_id');
				$data["type"] = $type;

		    	$buyer_unread_count = $chat_history_data->buyer_unread_count;
		    	
		    	$chat_data = array(

		        	"requested_item_id" => $this->post('requested_item_id'),
					"offered_item_id" => $this->post('offered_item_id'), 
		        	"buyer_user_id" => $this->post('buyer_user_id'), 
		        	"seller_user_id" => $this->post('seller_user_id'),
		        	"buyer_unread_count" => $buyer_unread_count + 1,
		        	"added_date" => date("Y-m-d H:i:s"),
		        	"nego_price" => $this->post('nego_price'),
                    "size_id" => $this->post('size_id'),
                    "color_id" => $this->post('color_id'),
                    "quantity" => $this->post('quantity'),
					"type" => $type

		        );
	    	} elseif ( $type == "to_seller" ) {

	    		//prepare data for noti
		    	$user_ids[] = $this->post('seller_user_id');

	        	$devices = $this->Noti->get_all_device_in($user_ids)->result();
	        	//print_r($devices);die;	

				$device_ids = array();
				if ( count( $devices ) > 0 ) {
					foreach ( $devices as $device ) {
						$device_ids[] = $device->device_token;
					}
				}

				$user_id = $this->post('seller_user_id');
	       		$user_name = $this->User->get_one($user_id)->user_name;

		    	$price = $this->post('nego_price');
	       		if ( $price == 0) {
		    		$data['message'] = "Offer Rejected!";
		    	} else {
		    		$data['message'] = "Make Offer!";
		    	}
		    	$data['buyer_user_id'] = $this->post('buyer_user_id');
		    	$data['seller_user_id'] = $this->post('seller_user_id');
		    	$data['sender_name'] = $user_name;
		    	$data['requested_item_id'] = $this->post('requested_item_id');
				$data['offered_item_id'] = $this->post('offered_item_id');
				$data['type'] = $type;

		    	$seller_unread_count = $chat_history_data->seller_unread_count;
		    	
		    	$chat_data = array(
		        	"requested_item_id" => $this->post('requested_item_id'),
					"offered_item_id" => $this->post('offered_item_id'),
		        	"buyer_user_id" => $this->post('buyer_user_id'), 
		        	"seller_user_id" => $this->post('seller_user_id'),
		        	"seller_unread_count" => $seller_unread_count + 1,
		        	"added_date" => date("Y-m-d H:i:s"),
		        	"nego_price" => $this->post('nego_price'),
                    "size_id" => $this->post('size_id'),
                    "color_id" => $this->post('color_id'),
                    "quantity" => $this->post('quantity'),
					"type" => $type
		        );
	    	}
	       	//sending noti
	    	$status = send_android_fcm_chat( $device_ids, $data );

	        $this->Chat->save($chat_data);	
	        $obj = $this->Chat->get_one_by($chat_data);
			$this->ps_adapter->convert_chathistory( $obj );
			$this->custom_response( $obj );
	    } else {
			if ( $type == "to_buyer" ) {
		    	//prepare data for noti
		    	$user_ids[] = $this->post('buyer_user_id');
	        	$devices = $this->Noti->get_all_device_in($user_ids)->result();
	        	//print_r($devices);die;	
				$device_ids = array();
				if ( count( $devices ) > 0 ) {
					foreach ( $devices as $device ) {
						$device_ids[] = $device->device_token;
					}
				}
				$user_id = $this->post('buyer_user_id');
	       		$user_name = $this->User->get_one($user_id)->user_name;

		    	$price = $this->post('nego_price');
	       		if ( $price == 0) {
		    		$data['message'] = "Offer Rejected!";
		    	} else {
		    		$data['message'] = "Make Offer!";
		    	}
		    	$data['buyer_user_id'] = $this->post('buyer_user_id');
		    	$data['seller_user_id'] = $this->post('seller_user_id');
		    	$data['sender_name'] = $user_name;
		    	$data['requested_item_id'] = $this->post('requested_item_id');
				$data['offered_item_id'] = $this->post('offered_item_id');
				$data['type'] = $type;


		    	$buyer_unread_count = $chat_history_data->buyer_unread_count;
		    	$chat_data = array(
		        	"requested_item_id" => $this->post('requested_item_id'),
					"offered_item_id" => $this->post('offered_item_id'), 
		        	"buyer_user_id" => $this->post('buyer_user_id'), 
		        	"seller_user_id" => $this->post('seller_user_id'),
		        	"buyer_unread_count" => $buyer_unread_count + 1,
		        	"added_date" => date("Y-m-d H:i:s"),
		        	"nego_price" => $this->post('nego_price'),
                    "size_id" => $this->post('size_id'),
                    "color_id" => $this->post('color_id'),
                    "quantity" => $this->post('quantity'),
					"type" => $type
		        );
			} elseif ( $type == "to_seller" ) {
		    	$user_ids[] = $this->post('seller_user_id');
	        	$devices = $this->Noti->get_all_device_in($user_ids)->result();
				$device_ids = array();
				if ( count( $devices ) > 0 ) {
					foreach ( $devices as $device ) {
						$device_ids[] = $device->device_token;
					}
				}
				$user_id = $this->post('seller_user_id');
	       		$user_name = $this->User->get_one($user_id)->user_name;

		    	$price = $this->post('nego_price');
	       		if ( $price == 0) {
		    		$data['message'] = "Offer Rejected!";
		    	} else {
		    		$data['message'] = "Make Offer!";
		    	}
		    	$data['buyer_user_id'] = $this->post('buyer_user_id');
		    	$data['seller_user_id'] = $this->post('seller_user_id');
		    	$data['sender_name'] = $user_name;
		    	$data['requested_item_id'] = $this->post('requested_item_id');
				$data['offered_item_id'] = $this->post('offered_item_id');
				$data['type'] = $type;

		    	$seller_unread_count = $chat_history_data->seller_unread_count;
		    	
		    	$chat_data = array(
		        	"requested_item_id" => $this->post('requested_item_id'),
					"offered_item_id" => $this->post('offered_item_id'),
		        	"buyer_user_id" => $this->post('buyer_user_id'), 
		        	"seller_user_id" => $this->post('seller_user_id'),
		        	"seller_unread_count" => $seller_unread_count + 1,
		        	"added_date" => date("Y-m-d H:i:s"),
		        	"nego_price" => $this->post('nego_price'),
                    "size_id" => $this->post('size_id'),
                    "color_id" => $this->post('color_id'),
                    "quantity" => $this->post('quantity'),
					"type" => $type
		        );
			}
			if( !$this->Chat->Save( $chat_data,$chat_history_data->id )) {
				$this->error_response( get_msg( 'err_price_update' ));
			} else {
				//sending noti
				$status = send_android_fcm_chat( $device_ids, $data );
				$obj = $this->Chat->get_one_by($chat_data);
				$this->ps_adapter->convert_chathistory( $obj );
				$this->custom_response( $obj );
			}
	    }
	}

	/**
	 * Update count
	 */
	function reset_count_post()
	{
		// validation rules for chat history
		$rules = array(
			array(
	        	'field' => 'item_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'buyer_user_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'seller_user_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'type',
	        	'rules' => 'required'
	        )
        );


		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        $chat_data = array(

        	"item_id" => $this->post('item_id'), 
        	"buyer_user_id" => $this->post('buyer_user_id'), 
        	"seller_user_id" => $this->post('seller_user_id')

        );

        $chat_history_data = $this->Chat->get_one_by($chat_data);


        if($chat_history_data->id == "") {
	        	
	        $this->error_response( get_msg( 'err_chat_history_not_exist' ));


	    } else {
	    	
	    	if($this->post('type') == "to_seller") {

		    	$chat_data_update = array(

		        	"item_id" => $this->post('item_id'), 
		        	"buyer_user_id" => $this->post('buyer_user_id'), 
		        	"seller_user_id" => $this->post('seller_user_id'),
		        	"seller_unread_count" => 0

		        );

		    } else if($this->post('type') == "to_buyer") {

		    	$chat_data_update = array(

		        	"item_id" => $this->post('item_id'), 
		        	"buyer_user_id" => $this->post('buyer_user_id'), 
		        	"seller_user_id" => $this->post('seller_user_id'),
		        	"buyer_unread_count" => 0

		        );
		    }

	    	if( !$this->Chat->Save( $chat_data_update,$chat_history_data->id )) {

	    		$this->error_response( get_msg( 'err_count_update' ));

	    	
	    	} else {

	    		$obj = $this->Chat->get_one_by($chat_data);
				$this->ps_adapter->convert_chathistory( $obj );
				$this->custom_response( $obj );

	    	}


	    }


	}

    /* Update accept or not
    */


    function update_accept_post()
	{
		// validation rules for chat history
		$rules = array(
			array(
	        	'field' => 'item_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'buyer_user_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'seller_user_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'nego_price',
	        	'rules' => 'required'
	        )
        );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;
        $type = $this->post('type');

        $chat_data = array(

        	"item_id" => $this->post('item_id'), 
        	"buyer_user_id" => $this->post('buyer_user_id'), 
        	"seller_user_id" => $this->post('seller_user_id')

        );

        $chat_history_data = $this->Chat->get_one_by($chat_data);
        //print_r($chat_history_data);die;

        // is_accept checking by seller_id and item_id
        $accept_checking_data = array(

        	"item_id" => $this->post('item_id'), 
        	"seller_user_id" => $this->post('seller_user_id'),

        );

        //print_r($accept_checking_data);die;	
        $accept_checking_result = $this->Chat->get_all_by($accept_checking_data)->result();
        //print_r($accept_checking_result);die;


        $accept_result_flag = 0;

        foreach ($accept_checking_result as $rst) {
	    		
    		if ($rst->is_accept == 1) {
    			$accept_result_flag = 1;
    			break;
    		}
    		

    	}

    	if( $accept_result_flag == 1 ) {
    		$this->error_response( get_msg( 'err_accept_offer' ));
    	} else {


	        if($chat_history_data->id == "") {

	        	if ( $type == "to_buyer" ) {

	        		//prepare data for noti
			    	$user_ids[] = $this->post('buyer_user_id');


		        	$devices = $this->Noti->get_all_device_in($user_ids)->result();
		        	//print_r($devices);die;	

					$device_ids = array();
					if ( count( $devices ) > 0 ) {
						foreach ( $devices as $device ) {
							$device_ids[] = $device->device_token;
						}
					}


					$user_id = $this->post('buyer_user_id');
		       		$user_name = $this->User->get_one($user_id)->user_name;
		       		$price = $this->post('nego_price');
		       		
			    	$data['message'] = "Offer Accepted!";
			    	$data['buyer_user_id'] = $this->post('buyer_user_id');
			    	$data['seller_user_id'] = $this->post('seller_user_id');
			    	$data['sender_name'] = $user_name;
			    	$data['item_id'] = $this->post('item_id');

			    	$buyer_unread_count = $chat_history_data->buyer_unread_count;
			    	
			    	$chat_data = array(

			        	"item_id" => $this->post('item_id'), 
			        	"buyer_user_id" => $this->post('buyer_user_id'), 
			        	"seller_user_id" => $this->post('seller_user_id'),
			        	"buyer_unread_count" => $buyer_unread_count + 1,
			        	"added_date" => date("Y-m-d H:i:s"),
			        	"nego_price" => $this->post('nego_price'),
			        	"is_accept" => 1

			        );

			    	} elseif ( $type == "to_seller" ) {

			    	//prepare data for noti
			    	$user_ids[] = $this->post('seller_user_id');


		        	$devices = $this->Noti->get_all_device_in($user_ids)->result();
		        	//print_r($devices);die;	

					$device_ids = array();
					if ( count( $devices ) > 0 ) {
						foreach ( $devices as $device ) {
							$device_ids[] = $device->device_token;
						}
					}


					$user_id = $this->post('seller_user_id');
		       		$user_name = $this->User->get_one($user_id)->user_name;

			    	$data['message'] = "Offer Accepted!";
			    	$data['buyer_user_id'] = $this->post('buyer_user_id');
			    	$data['seller_user_id'] = $this->post('seller_user_id');
			    	$data['sender_name'] = $user_name;
			    	$data['item_id'] = $this->post('item_id');	

			    	$seller_unread_count = $chat_history_data->seller_unread_count;
			    	
			    	$chat_data = array(

			        	"item_id" => $this->post('item_id'), 
			        	"buyer_user_id" => $this->post('buyer_user_id'), 
			        	"seller_user_id" => $this->post('seller_user_id'),
			        	"seller_unread_count" => $seller_unread_count + 1,
			        	"added_date" => date("Y-m-d H:i:s"),
			        	"nego_price" => $this->post('nego_price'),
			        	"is_accept" => 1


			        );

			    	}

			    //sending noti
		    	$status = send_android_fcm_chat( $device_ids, $data );	

		        $this->Chat->save($chat_data);	
		        $obj = $this->Chat->get_one_by($chat_data);
				$this->ps_adapter->convert_chathistory( $obj );
				$this->custom_response( $obj );


		    } else {


		    	//print_r($chat_history_data->is_accept);die;

		    	$conds_chat['seller_user_id'] = $chat_history_data->seller_user_id;
		    	$conds_chat['item_id'] = $chat_history_data->item_id;

		    	$chats = $this->Chat->get_all_by($conds_chat)->result();

		    	//print_r($chats);die;

		    	$accept_flag = 0;	

		    	foreach ($chats as $chat) {
		    		
		    		if ($chat->is_accept == 1) {
		    			$accept_flag = 1;
		    			break;
		    		}
		    		

		    	}
		    	
		    	if( $accept_flag == 1 ) {

		    		$this->error_response( get_msg( 'err_accept_offer' ));
		    	}

		    	else {

		    		if ( $type == "to_buyer" ) {

		    		//prepare data for noti
			    	$user_ids[] = $this->post('buyer_user_id');


		        	$devices = $this->Noti->get_all_device_in($user_ids)->result();
		        	//print_r($devices);die;	

					$device_ids = array();
					if ( count( $devices ) > 0 ) {
						foreach ( $devices as $device ) {
							$device_ids[] = $device->device_token;
						}
					}


					$user_id = $this->post('buyer_user_id');
		       		$user_name = $this->User->get_one($user_id)->user_name;
		       		
		       		$data['message'] = "Offer Accepted!";
			    	$data['buyer_user_id'] = $this->post('buyer_user_id');
			    	$data['seller_user_id'] = $this->post('seller_user_id');
			    	$data['sender_name'] = $user_name;
			    	$data['item_id'] = $this->post('item_id');

			    	$buyer_unread_count = $chat_history_data->buyer_unread_count;


			    	$chat_data = array(

			        	"item_id" => $this->post('item_id'), 
			        	"buyer_user_id" => $this->post('buyer_user_id'), 
			        	"seller_user_id" => $this->post('seller_user_id'),
			        	"buyer_unread_count" => $buyer_unread_count + 1,
			        	"added_date" => date("Y-m-d H:i:s"),
			        	"nego_price" => $this->post('nego_price'),
			        	"is_accept"	 => 1	

			        );


			    	} elseif ( $type == "to_seller" ) {

			    	//prepare data for noti
			    	$user_ids[] = $this->post('seller_user_id');


		        	$devices = $this->Noti->get_all_device_in($user_ids)->result();
		        	//print_r($devices);die;	

					$device_ids = array();
					if ( count( $devices ) > 0 ) {
						foreach ( $devices as $device ) {
							$device_ids[] = $device->device_token;
						}
					}


					$user_id = $this->post('seller_user_id');
		       		$user_name = $this->User->get_one($user_id)->user_name;

			    	$data['message'] = "Offer Accepted!";
			    	$data['buyer_user_id'] = $this->post('buyer_user_id');
			    	$data['seller_user_id'] = $this->post('seller_user_id');
			    	$data['sender_name'] = $user_name;
			    	$data['item_id'] = $this->post('item_id');	

			    	$seller_unread_count = $chat_history_data->seller_unread_count;
			    	
			    	$chat_data = array(

			        	"item_id" => $this->post('item_id'), 
			        	"buyer_user_id" => $this->post('buyer_user_id'), 
			        	"seller_user_id" => $this->post('seller_user_id'),
			        	"seller_unread_count" => $seller_unread_count + 1,
			        	"added_date" => date("Y-m-d H:i:s"),
			        	"nego_price" => $this->post('nego_price'),
			        	"is_accept"	 => 1

			        );

			    	}
		    	}

		    	if( !$this->Chat->Save( $chat_data,$chat_history_data->id )) {

		    		$this->error_response( get_msg( 'err_accept_update' ));

		    	
		    	} else {

		    		//sending noti
		    		$status = send_android_fcm_chat( $device_ids, $data );
		    		$obj = $this->Chat->get_one_by($chat_data);
					$this->ps_adapter->convert_chathistory( $obj );
					$this->custom_response( $obj );

		    	}
		    }

	    }

	}

    /**
	 * Update Price 
	 */
	function item_sold_out_post()
	{
		// validation rules for chat history
		$rules = array(
			array(
	        	'field' => 'item_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'buyer_user_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'seller_user_id',
	        	'rules' => 'required'
	        )
        );


		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;
        $item_id = $this->post('item_id');
        $buyer_user_id = $this->post('buyer_user_id');
        $seller_user_id = $this->post('seller_user_id');
        $item_sold_out = array(

        	"is_sold_out" => 1, 

        );

        $this->Item->save($item_sold_out,$item_id);
        $conds['item_id'] = $item_id;
        $conds['buyer_user_id'] = $buyer_user_id;
        $conds['seller_user_id'] = $seller_user_id;
        
        $obj = $this->Chat->get_one_by($conds);

        $this->ps_adapter->convert_chathistory( $obj );
        $this->custom_response($obj);
    }


    /**
	 * Reset is_accept 
	 */
	function reset_accept_post()
	{
		// validation rules for chat history
		$rules = array(
			array(
	        	'field' => 'item_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'buyer_user_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'seller_user_id',
	        	'rules' => 'required'
	        )
        );


		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        $chat_data = array(

        	"item_id" => $this->post('item_id'), 
        	"buyer_user_id" => $this->post('buyer_user_id'), 
        	"seller_user_id" => $this->post('seller_user_id')

        );

        $chat_history_data = $this->Chat->get_one_by($chat_data);


        if($chat_history_data->id == "") {
	        	
	        $this->error_response( get_msg( 'err_chat_history_not_exist' ));


	    } else {
	    	
	    	$chat_data = array(

	        	"item_id" => $this->post('item_id'), 
	        	"buyer_user_id" => $this->post('buyer_user_id'), 
	        	"seller_user_id" => $this->post('seller_user_id'),
	        	"is_accept" => 0

	        );

	    	if( !$this->Chat->Save( $chat_data,$chat_history_data->id )) {

	    		$this->error_response( get_msg( 'err_accept_update' ));

	    	
	    	} else {

	    		$this->success_response( get_msg( 'accept_reset_success' ));


	    	}


	    }


	}

	/**
	 * Delete All Chat History
	 */
	function delete_chat_history_post()
	{
		
		// delete categories and images
		if ( !$this->Chat->delete_all()) {

			// set error message
			$this->error_response( get_msg( 'error_delete_chat_history' ));
			// rollback

			
		}
		
		$this->success_response( get_msg( 'success_delete_chat_history' ));
		
	}

	/**
	 * Reset Soldout
	 */

	function reset_sold_out_post()
	{
		// validation rules for chat history
		$rules = array(
			array(
	        	'field' => 'item_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'buyer_user_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'seller_user_id',
	        	'rules' => 'required'
	        )
        );


		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        $chat_data = array(

        	"item_id" => $this->post('item_id'), 
        	"buyer_user_id" => $this->post('buyer_user_id'), 
        	"seller_user_id" => $this->post('seller_user_id')

        );

        $chat_history_data = $this->Chat->get_one_by($chat_data);


        if($chat_history_data->id == "") {
	        	
	        $this->error_response( get_msg( 'err_chat_history_not_exist' ));


	    } else {
	    	
	    	$chat_data = array(

	        	"item_id" => $this->post('item_id'), 
	        	"buyer_user_id" => $this->post('buyer_user_id'), 
	        	"seller_user_id" => $this->post('seller_user_id'),
	        	"is_accept" => 0

	        );

	    	if( !$this->Chat->Save( $chat_data,$chat_history_data->id )) {

	    		$this->error_response( get_msg( 'err_accept_update' ));

	    	
	    	} else {

	    		$item_data = array(
	    			"is_sold_out" => 0
	    		);

	    		if( !$this->Item->Save( $item_data, $this->post('item_id') )) {

		    		$this->error_response( get_msg( 'err_soldout_reset' ));

		    	} else {

		    		$this->success_response( get_msg( 'soldout_reset_success' ));

		    	}


	    	}


	    }

	}


	/**
	 * get chat history
	 */

	function get_chat_history_post()
	{
		// validation rules for chat history
		$rules = array(
			array(
	        	'field' => 'item_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'buyer_user_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'seller_user_id',
	        	'rules' => 'required'
	        )
        );


		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        $chat_data = array(

        	"item_id" => $this->post('item_id'), 
        	"buyer_user_id" => $this->post('buyer_user_id'), 
        	"seller_user_id" => $this->post('seller_user_id')

        );

        $obj = $this->Chat->get_one_by($chat_data);

        $this->ps_adapter->convert_chathistory( $obj );
		$this->custom_response( $obj );

    }

    /**
	 * Offer list Api
	 */

	function offer_list_post()
	{

		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
		
		// validation rules for chat history
		// $rules = array(
	    //     array(
	    //     	'field' => 'seller_user_id',
	    //     	'rules' => 'required'
	    //     )
        // );


		// // exit if there is an error in validation,
        // if ( !$this->is_valid( $rules )) exit;
        // $chat_data = array(

        	// "seller_user_id" => $this->post('seller_user_id')

        // );
        // $chats = $this->Chat->get_all_by($chat_data);
        // foreach ($chats->result() as $ch) {
        // 	$nego_price = $ch->nego_price;
        // 	$is_accept = $ch->is_accept;
        // 	if ($nego_price != 0 && $is_accept != 0) {
        // 		$result .= $ch->id .",";
	    //     }
        // }
        // $id_from_his = rtrim($result,",");
		// $result_id = explode(",", $id_from_his);
		// $obj = $this->Chat->get_multi_info($result_id)->result();

		// $this->ps_adapter->convert_chathistory( $obj );
		// $this->custom_response( $obj );

		// add flag for default query
		$this->is_get = true;

		// get the post data
		$user_id = $this->post('user_id');
		$return_type 	 = $this->post('return_type');

		$users = global_user_check($user_id);

		$limit = $this->get( 'limit' );
		$offset = $this->get( 'offset' );
                //start new code
		$type 	 = $this->post('type');
                $condition = "type = '".$type."'";
                if($type == 'direct_buy' || $type == 'request_item') {
                    $condition .= " AND buyer_user_id = '".$user_id."'";
                } else if($type == 'selling ') {
                    $condition .= " AND seller_user_id = '".$user_id."'";
                } else if($type == 'exchange ') {
                    $condition .= " AND (buyer_user_id = '".$user_id."' OR seller_user_id = '".$user_id."') ";
                }
                $obj = $this->db->query("SELECT * FROM `bs_chat_history` WHERE ".$condition)->result();
		$this->ps_adapter->convert_chathistory( $obj );
                $this->custom_response( $obj );
                // end of code
			// get limit & offset

			if ( $return_type == "buyer") {
				
			//pph modified @ 22 June 2019

			/* For User Block */

			//user block check with user_id
			$conds_login_block['from_block_user_id'] = $user_id;
			$login_block_count = $this->Block->count_all_by($conds_login_block);

			// user blocked existed by user id
			if ($login_block_count > 0) {
				// get the blocked user by user id
				$to_block_user_datas = $this->Block->get_all_by($conds_login_block)->result();

				foreach ( $to_block_user_datas as $to_block_user_data ) {

					$to_block_user_id .= "'" .$to_block_user_data->to_block_user_id . "',";
			
				}

				// get block user's chat list

				$result_users = rtrim($to_block_user_id,',');
				$conds_user['buyer_user_id'] = $result_users;

				$chat_users = $this->Chat->get_all_in_chat_buyer( $conds_user )->result();


				foreach ( $chat_users as $chat_user ) {

					$chat_ids .= $chat_user->id .",";
				
				}

				// get all chat id without block user's list

				$results = rtrim($chat_ids,',');
				$chat_id = explode(",", $results);
				$conds['chat_id'] = $chat_id;


			}	

				$conds['seller_user_id'] = $user_id;
				$conds['nego_price'] = '0' ;



			if ( !empty( $limit ) && !empty( $offset )) {
			// if limit & offset is not empty
				
				$chats = $this->Chat->get_all_chat($conds,$limit, $offset)->result();
			} else if ( !empty( $limit )) {
			// if limit is not empty

				
				$chats = $this->Chat->get_all_chat($conds, $limit )->result();
			} else {
			// if both are empty
				
				$chats = $this->Chat->get_all_chat($conds)->result();
			}
			//print_r($chats);die;
			if (!empty($chats)) {
				foreach ( $chats as $chat ) {

				$id .= "'" .$chat->id . "',";
			
				}
			}	
			
			if ($id == "") {
				$this->error_response($this->config->item( 'record_not_found'));
			} else {

				$result = rtrim($id,',');
				$conds['$id'] = $result;

				$obj = $this->Chat->get_all_in_chat($conds)->result();
				$this->ps_adapter->convert_chathistory( $obj );
				$this->custom_response( $obj );

			}

			

		} else if ( $return_type == "seller") {

			//$conds['seller_user_id'] = $user_id;
			//pph modified @ 22 June 2019

			/* For User Block */

			//user block check with user_id
			$conds_login_block['from_block_user_id'] = $user_id;
			$login_block_count = $this->Block->count_all_by($conds_login_block);

			// user blocked existed by user id
			if ($login_block_count > 0) {
				// get the blocked user by user id
				$to_block_user_datas = $this->Block->get_all_by($conds_login_block)->result();

				foreach ( $to_block_user_datas as $to_block_user_data ) {

					$to_block_user_id .= "'" .$to_block_user_data->to_block_user_id . "',";
			
				}

				// get block user's chat list

				$result_users = rtrim($to_block_user_id,',');
				$conds_user['seller_user_id'] = $result_users;

				$chat_users = $this->Chat->get_all_in_chat_seller( $conds_user )->result();


				foreach ( $chat_users as $chat_user ) {

					$chat_ids .= $chat_user->id .",";
				
				}

				// get all chat id without block user's list

				$results = rtrim($chat_ids,',');
				$chat_id = explode(",", $results);
				$conds['chat_id'] = $chat_id;

			}

			/* For Item Report */

				//item report check with login_user_id
				$conds_report['reported_user_id'] = $user_id;
				$reported_data_count = $this->Itemreport->count_all_by($conds_report);

				// item reported existed by login user
				if ($reported_data_count > 0) {
					// get the reported item data
					$item_reported_datas = $this->Itemreport->get_all_by($conds_report)->result();

					foreach ( $item_reported_datas as $item_reported_data ) {

						$item_ids .= "'" .$item_reported_data->item_id . "',";
				
					}

					// get block user's item

					$result_reports = rtrim($item_ids,',');
					$conds_item['item_id'] = $result_reports;

					$item_reports = $this->Chat->get_all_in_chat_item( $conds_item )->result();

					foreach ( $item_reports as $item_report ) {

						$ids .= $item_report->id .",";
					
					}

					// get all item without block user's item

					$result_items = rtrim($ids,',');
					$reported_item_id = explode(",", $result_items);
					$conds['item_id'] = $reported_item_id;
				}

				$conds['buyer_user_id'] = $user_id;
				$conds['nego_price'] = '0' ;

			//print_r($conds);die;
				
			if ( !empty( $limit ) && !empty( $offset )) {
			// if limit & offset is not empty
				
				$chats = $this->Chat->get_all_chat($conds,$limit, $offset)->result();
			} else if ( !empty( $limit )) {
			// if limit is not empty

				
				$chats = $this->Chat->get_all_chat($conds, $limit )->result();
			} else {
			// if both are empty
				
				$chats = $this->Chat->get_all_chat($conds)->result();
			}
			
			if (!empty($chats)) {
				foreach ( $chats as $chat ) {

				$id .= "'" .$chat->id . "',";
			
				}
			}

			if ($id == "") {
				$this->error_response($this->config->item( 'record_not_found'));
			} else {

				$result = rtrim($id,',');
				$conds['$id'] = $result;

				$obj = $this->Chat->get_all_in_chat($conds)->result();
				$this->ps_adapter->convert_chathistory( $obj );
				$this->custom_response( $obj );

			}

		}

    }

	// COUNTER API
	function make_offer_counter_post(){
		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);

		// validation rules for chat history
		$rules = array(
			array(
	        	'field' => 'id',
	        	'rules' => 'required'
	        ),
			array(
	        	'field' => 'price',
	        	'rules' => 'required'
	        ),
        );
		if ( !$this->is_valid( $rules )) exit;

		$chat_history_id = $this->post('id');
		$chat_history_data = $this->Chat->get_one_by(array('id' => $chat_history_id));

		if($chat_history_data->id == ""){
			$this->error_response(get_msg('err_chat_history_not_available'));
		}else{
			$chat_data_update = array(
				"item_id" => $chat_history_data->item_id,
				"buyer_user_id" => $chat_history_data->buyer_user_id, 
				"seller_user_id" => $chat_history_data->seller_user_id, 
				"nego_price" => $this->post('price'), 
				"parent_id" => $chat_history_id, 
				"is_accept" => 0,
			);

			if(!$this->Chat->Save( $chat_data_update)) {
				$this->error_response(get_msg( 'err_count_update'));
			}else{
				$this->success_response(get_msg('offer_change_success'));
			}
		}
	}

}