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
	 * 
	 * MAKE OFFER API 
	 * 
	 * AND CANCEL OFFER API 
	 * 
	 * ( WHEN PRICE = 0 AND IS_CANCEL = 1 THIS API WORK AS CANCEL OTHERWISE CREATE OFFER )
	 * 
	 */
	function update_price_post(){
		// validation rules for chat history
		$rules = array(
			array(
	        	'field' => 'requested_item_id',
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
			),
			array(
	        	'field' => 'operation_type',
	        	'rules' => 'required'
	        )
        );
		//echo '<pre>'; print_r($this->post()); die;
		if($this->post('operation_type') == REQUEST_ITEM){
			array_push($rules, array(
	        	'field' => 'offered_item_id',
	        	'rules' => 'required'
	        ));
		}elseif($this->post('operation_type') == EXCHANGE){
			array_push($rules, array(
	        	'field' => 'offered_item_id[]',
	        	'rules' => 'required'
	        ));
		}

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;
		$requestedItemId = $this->post('requested_item_id');
		if(is_array($this->post('offered_item_id'))){
			foreach($this->post('offered_item_id') as $offeredItemId){
				$this->validation_chat_item_categories($requestedItemId, $offeredItemId,'exchange');
			}	
		} else {
			if($this->post('operation_type') == REQUEST_ITEM){
				$this->validation_chat_item_categories($requestedItemId, $this->post('offered_item_id'));
			}
		}
		
		$obj = $this->save_chat($this->post('offered_item_id'));
		$this->ps_adapter->convert_chathistory( $obj );
        /*Notify seller :start*/
        $post = $this->post();
        $title = '';
        $token = '';
        $item_img = '';
        if($post['operation_type'] == DIRECT_BUY || $post['operation_type'] == EXCHANGE) {
            $get_user = $this->db->select('device_token')->from('core_users')->where('user_id', $post['seller_user_id'])->get()->row();
            $token = $get_user->device_token;
            
            $get_item = $this->db->select('is_confirm_with_seller,title')->from('bs_items')->where('id', $requestedItemId)->get()->row();
            $title = $get_item->title;
            
            $item_images = $this->db->select('img_path')->from('core_images')->where('img_type', 'item')->where('img_parent_id', $requestedItemId)->get()->row();
            $item_img = $item_images->img_path;
            
        } else if ($post['operation_type'] == REQUEST_ITEM) {
            $get_user = $this->db->select('device_token')->from('core_users')->where('user_id', $post['buyer_user_id'])->get()->row();
            $token = $get_user->device_token;
            
            $get_item = $this->db->select('is_confirm_with_seller,title')->from('bs_items')->where('id', $post['offered_item_id'])->get()->row();
            $title = $get_item->title;
            
            $item_images = $this->db->select('img_path')->from('core_images')->where('img_type', 'item')->where('img_parent_id', $post['offered_item_id'])->get()->row();
            $item_img = $item_images->img_path;
        }
        if(isset($post['quantity']) && $post['quantity'] > 1) {
            send_push( [$token], ["message" => "Offer received $".$post['nego_price'], "flag" => "chat",'title' => $title], ['image' => 'http://bacancy.com/biddingapp/uploads/'.$item_img]);
        }
        if($get_item->is_confirm_with_seller) {
             send_push( [$token], ["message" => "Offer received $".$post['nego_price'], "flag" => "chat",'title' => $title], ['image' => 'http://bacancy.com/biddingapp/uploads/'.$item_img]);
        }
        /*Notify seller :end*/
        
		$this->custom_response( $obj );
	}

	//  VALIDATE REQUEST ITEM AND OFFERED ITEM CATEGORY, SUB CATEGORY AND CHILD SUBCATEGORY ARE SAME OR NOT
	function validation_chat_item_categories($requestedItemId, $offeredItemId, $operation_type = null){
		$requestedItemDetails = $this->Item->get_one( $requestedItemId );
		$this->ps_adapter->convert_item($requestedItemDetails);
		$offeredItemDetails = $this->Item->get_one( $offeredItemId );
		if(isset($operation_type) && $operation_type=='exchange'){
			$exchangeCategoriesFlag = false;
			foreach($requestedItemDetails->exchange_category as $exchangeCat){
				$exchangeCategoriesArray[] = $exchangeCat->cat_id;
				if($exchangeCat->cat_id == $offeredItemDetails->cat_id){
					$exchangeCategoriesFlag = true;
				}
			}
			if(!$exchangeCategoriesFlag){
				$this->error_response( get_msg( 'err_make_offer_category_validation_error' ));
			}
		} else {
			if($requestedItemDetails->cat_id != $offeredItemDetails->cat_id){
				$this->error_response( get_msg( 'err_make_offer_category_validation_error' ));
			}
			if($requestedItemDetails->sub_cat_id != $offeredItemDetails->sub_cat_id){
				$this->error_response( get_msg( 'err_make_offer_category_validation_error' ));
			}
			if($requestedItemDetails->childsubcat_id != $offeredItemDetails->childsubcat_id){
				$this->error_response( get_msg( 'err_make_offer_category_validation_error' ));
			}
		}
	}

	// ADD CHAT FOR REQUEST SELLER AND EXCHANGE    -- Operation type = 3 means exchange, 1 = Request, 4 = Direct Buy
	function save_chat($offeredItemId){
		$requestedItemId = $this->post('requested_item_id');

		$buyerUserId = $this->post('buyer_user_id');
		$sellerUserId = $this->post('seller_user_id');
		
		if($this->post('operation_type') == EXCHANGE){
			$chat_data = array(
				"requested_item_id" => $requestedItemId,
				"buyer_user_id" => $buyerUserId, 
				"seller_user_id" => $sellerUserId,
				"operation_type" => EXCHANGE
			);

			// GET ALL DATA 
			$chat_history_data = $this->Chat->get_one_by($chat_data);
			if($chat_history_data->id != ""){
				$offer_details = array('chat_id' => $chat_history_data->id, 'offered_item_id' => $this->post('offered_item_id'));
				$exchange_offer_id_data = $this->ExchangeChatHistory->get_all_in_exchange_chat_item($offer_details);
				if(!empty($exchange_offer_id_data) && $exchange_offer_id_data[0]->id == ""){
					$chat_history_data->id = "";
				}
			}
		}else{
			$chat_data = array(
				"requested_item_id" => $requestedItemId,
				"offered_item_id" => $offeredItemId, 
				"buyer_user_id" => $buyerUserId, 
				"seller_user_id" => $sellerUserId,
			);
			$chat_history_data = $this->Chat->get_one_by($chat_data);
		}
		$type = $this->post('type');
		
		if($chat_history_data->id == "") {
			$operation_type = $this->post('operation_type');

			if ( $type == "to_buyer" ) {

				//prepare data for noti
				$user_ids[] = $buyerUserId;
				
				$devices = $this->Noti->get_all_device_in($user_ids)->result();
				//echo '<pre>'; print_r($devices); die;	

				$device_ids = array();
				if ( count( $devices ) > 0 ) {
					foreach ( $devices as $device ) {
						$device_ids[] = $device->device_token;
					}
				}
				
				$user_id = $buyerUserId;
				$user_name = $this->User->get_one($user_id)->user_name;
				
				$price = $this->post('nego_price');
				/*
				NOTIFICATION SEND FROM SEPRATE API
				if ( $price == 0) {
					$data['message'] = "Offer Rejected!";
				} else {
					$data['message'] = "Make Offer!";
				}
				$data['buyer_user_id'] = $buyerUserId;
				$data['seller_user_id'] = $sellerUserId;
				$data['sender_name'] = $user_name;
				$data['requested_item_id'] = $requestedItemId;
				
				if($this->post('operation_type') != EXCHANGE){
					$data['offered_item_id'] = $offeredItemId;
				}
				$data["type"] = $type;*/
				$buyer_unread_count = $chat_history_data->buyer_unread_count;
				
				$chat_data = array(
					"requested_item_id" => $requestedItemId,
					"offered_item_id" => ($this->post('operation_type') != EXCHANGE) ? $offeredItemId : NULL,
					"buyer_user_id" => $buyerUserId, 
					"seller_user_id" => $sellerUserId,
					"buyer_unread_count" => $buyer_unread_count + 1,
					"added_date" => date("Y-m-d H:i:s"),
					"nego_price" => $this->post('nego_price'),
					"size_id" => $this->post('size_id'),
					"color_id" => $this->post('color_id'),
					"quantity" => $this->post('quantity'),
					"type" => $type,
					"operation_type" => $this->post('operation_type'),
					"is_cancel" => $this->post('is_cancel'),
					"cancel_reason" => $this->post('cancel_reason'),
					"delivery_to" => $this->post('delivery_to'),
					"payment_method_id" => $this->post('payment_method_id'),
				);
			} elseif ( $type == "to_seller" ) {

				//prepare data for noti
				$user_ids[] = $sellerUserId;

				$devices = $this->Noti->get_all_device_in($user_ids)->result();
				$device_ids = array();
				if ( count( $devices ) > 0 ) {
					foreach ( $devices as $device ) {
						$device_ids[] = $device->device_token;
					}
				}

				$user_id = $sellerUserId;
				$user_name = $this->User->get_one($user_id)->user_name;

				$price = $this->post('nego_price');
				if ( $price == 0) {
					$data['message'] = "Offer Rejected!";
				} else {
					$data['message'] = "Make Offer!";
				}
				/*
				NOTIFICATION SEND FROM SEPRATE FUNCTION
				$data['buyer_user_id'] = $this->post('buyer_user_id');
				$data['seller_user_id'] = $sellerUserId;
				$data['sender_name'] = $user_name;
				$data['requested_item_id'] = $requestedItemId;
				
				$data['offered_item_id'] = $offeredItemId;
				$data['type'] = $type;
				$data['operation_type'] = $operation_type; */

				$seller_unread_count = $chat_history_data->seller_unread_count;
				$chat_data = array(
					"requested_item_id" => $requestedItemId,
					"offered_item_id" => ($this->post('operation_type') != EXCHANGE) ? $offeredItemId : NULL,
					"buyer_user_id" => $buyerUserId, 
					"seller_user_id" => $sellerUserId,
					"seller_unread_count" => $seller_unread_count + 1,
					"added_date" => date("Y-m-d H:i:s"),
					"nego_price" => $this->post('nego_price'),
					"size_id" => $this->post('size_id'),
					"color_id" => $this->post('color_id'),
					"quantity" => $this->post('quantity'),
					"type" => $type,
					"operation_type" => $this->post('operation_type'),
					"is_cancel" => $this->post('is_cancel'),
					"cancel_reason" => $this->post('cancel_reason'),
					"delivery_to" => $this->post('delivery_to'),
					"payment_method_id" => $this->post('payment_method_id'),
				);
			}
			// $status = send_android_fcm_chat( $device_ids, $data );
			$this->Chat->save($chat_data);	
			$obj = $this->Chat->get_one_by($chat_data);
			if($this->post('operation_type') == EXCHANGE){
				$this->ExchangeChatHistory->delete_by(array('chat_id' => $obj->id));
				if(is_array($this->post('offered_item_id'))){
					foreach($this->post('offered_item_id') as $offerId){
						$exchange_store_data = array(
							'chat_id' => $obj->id,
							'operation_type' => $this->post('operation_type'),
							'who_pay' => $this->post('who_pay'),
							'offered_item_id' => $offerId,
							"date_added" => date("Y-m-d H:i:s"),
						);
						$this->ExchangeChatHistory->Save($exchange_store_data);
					}	
				} else {
					$exchange_store_data = array(
						'chat_id' => $obj->id,
						'operation_type' => $this->post('operation_type'),
						'who_pay' => $this->post('who_pay'),
						'offered_item_id' => $this->post('offered_item_id'),
						"date_added" => date("Y-m-d H:i:s"),
					);
					$this->ExchangeChatHistory->Save($exchange_store_data);
				}
			}
			return $obj;
		} else {
			if ( $type == "to_buyer" ) {
				//prepare data for noti
				$user_ids[] = $buyerUserId;
				$devices = $this->Noti->get_all_device_in($user_ids)->result();
				$device_ids = array();
				if ( count( $devices ) > 0 ) {
					foreach ( $devices as $device ) {
						$device_ids[] = $device->device_token;
					}
				}
				$user_id = $buyerUserId;
				$user_name = $this->User->get_one($user_id)->user_name;

				$price = $this->post('nego_price');
				/*
				NOTIFICATION SENDS FROM SEPRATE FUNCTIONS
				
				if ( $price == 0) {
					$data['message'] = "Offer Rejected!";
				} else {
					$data['message'] = "Make Offer!";
				}
				$data['buyer_user_id'] = $buyerUserId;
				$data['seller_user_id'] = $sellerUserId;
				$data['sender_name'] = $user_name;
				$data['requested_item_id'] = $requestedItemId;

				$data['offered_item_id'] = $offeredItemId;

				$data['type'] = $type; */

				$buyer_unread_count = $chat_history_data->buyer_unread_count;
				$chat_data = array(
					"requested_item_id" => $requestedItemId,
					"offered_item_id" => ($this->post('operation_type') != EXCHANGE) ? $offeredItemId : NULL,
					"buyer_user_id" => $buyerUserId, 
					"seller_user_id" => $sellerUserId,
					"buyer_unread_count" => $buyer_unread_count + 1,
					"added_date" => date("Y-m-d H:i:s"),
					"nego_price" => $this->post('nego_price'),
					"size_id" => $this->post('size_id'),
					"color_id" => $this->post('color_id'),
					"quantity" => $this->post('quantity'),
					"type" => $type,
					"operation_type" => $this->post('operation_type'),
					"is_cancel" => $this->post('is_cancel'),
					"cancel_reason" => $this->post('cancel_reason'),
					"delivery_to" => $this->post('delivery_to'),
					"payment_method_id" => $this->post('payment_method_id'),
				);
			} elseif ( $type == "to_seller" ) {
				$user_ids[] = $sellerUserId;
				$devices = $this->Noti->get_all_device_in($user_ids)->result();
				$device_ids = array();
				if ( count( $devices ) > 0 ) {
					foreach ( $devices as $device ) {
						$device_ids[] = $device->device_token;
					}
				}
				$user_id = $sellerUserId;
				$user_name = $this->User->get_one($user_id)->user_name;

				$price = $this->post('nego_price');
				/*
				NOTIFICATION SENDS FROM SEPRATE FUNCTIONS
				
				if ( $price == 0) {
					$data['message'] = "Offer Rejected!";
				} else {
					$data['message'] = "Make Offer!";
				}
				$data['buyer_user_id'] = $buyerUserId;
				$data['seller_user_id'] = $sellerUserId;
				$data['sender_name'] = $user_name;
				$data['requested_item_id'] = $requestedItemId;

				$data['offered_item_id'] = $offeredItemId;
				$data['type'] = $type; */

				$seller_unread_count = $chat_history_data->seller_unread_count;
				
				$chat_data = array(
					"requested_item_id" => $requestedItemId,
					"offered_item_id" => ($this->post('operation_type') != EXCHANGE) ? $offeredItemId : NULL,
					"buyer_user_id" => $buyerUserId, 
					"seller_user_id" => $sellerUserId,
					"seller_unread_count" => $seller_unread_count + 1,
					"added_date" => date("Y-m-d H:i:s"),
					"nego_price" => $this->post('nego_price'),
					"size_id" => $this->post('size_id'),
					"color_id" => $this->post('color_id'),
					"quantity" => $this->post('quantity'),
					"type" => $type,
					"operation_type" => $this->post('operation_type'),
					"is_cancel" => $this->post('is_cancel'),
					"cancel_reason" => $this->post('cancel_reason'),
					"delivery_to" => $this->post('delivery_to'),
					"payment_method_id" => $this->post('payment_method_id'),
				);
			}
			if(!$this->Chat->Save( $chat_data, $chat_history_data->id )) {
				$this->error_response( get_msg( 'err_price_update' ));
			} else {
				//sending noti
				// $status = send_android_fcm_chat( $device_ids, $data );
				$obj = $this->Chat->get_one_by($chat_data);
				if($this->post('operation_type') == EXCHANGE){

					$this->ExchangeChatHistory->delete_by(array('chat_id' => $obj->id));
					if(is_array($this->post('offered_item_id'))){
						foreach($this->post('offered_item_id') as $offerId){
							$exchange_store_data = array(
								'chat_id' => $obj->id,
								'operation_type' => $this->post('operation_type'),
								'who_pay' => $this->post('who_pay'),
								'offered_item_id' => $offerId,
								"date_added" => date("Y-m-d H:i:s"),
							);
							$this->ExchangeChatHistory->Save($exchange_store_data);
						}	
					} else {
						$exchange_store_data = array(
							'chat_id' => $obj->id,
							'operation_type' => $this->post('operation_type'),
							'who_pay' => $this->post('who_pay'),
							'offered_item_id' => $this->post('offered_item_id'),
							"date_added" => date("Y-m-d H:i:s"),
						);
						$this->ExchangeChatHistory->Save($exchange_store_data);
					}
					
				}
				return $obj;
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
	        	'field' => 'chat_id',
	        	'rules' => 'required'
	        ),
//			array(
//	        	'field' => 'item_id',
//	        	'rules' => 'required'
//	        ),
//	        array(
//	        	'field' => 'buyer_user_id',
//	        	'rules' => 'required'
//	        ),
//	        array(
//	        	'field' => 'seller_user_id',
//	        	'rules' => 'required'
//	        ),
	        array(
	        	'field' => 'user_id',
	        	'rules' => 'required'
	        ),
//	        array(
//	        	'field' => 'type',
//	        	'rules' => 'required'
//	        )
        );


		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;
        
        $get_chat_detail = $this->db->from('bs_chat_history')->where('id', $this->post('chat_id'))->get()->row();
        if($get_chat_detail->seller_user_id == $this->post('user_id')) {
            
            $chat_data_update = array(
                "id" => $this->post('chat_id'), 
                "seller_unread_count" => 0
            );

        } else {
            $chat_data_update = array(
                "id" => $this->post('chat_id'), 
                "buyer_unread_count" => 0
            );

        }
        $chat_data = array(
        	"id" => $this->post('chat_id'), 
        );
        $chat_history_data = $this->Chat->get_one_by($chat_data);
        if( !$this->Chat->Save( $chat_data_update,$chat_history_data->id )) {
            $this->error_response( get_msg( 'err_count_update' ));
        } else {
            $obj = $this->Chat->get_one_by($chat_data);
            $this->ps_adapter->convert_chathistory( $obj );
            if(empty($obj->buyer_unread_count)) {
                $obj->buyer_unread_count = "0";
            }
            if(empty($obj->seller_unread_count)) {
                $obj->seller_unread_count = "0";
            }
            $this->custom_response( $obj );
        }
        
//        $chat_data = array(
//
//        	"item_id" => $this->post('item_id'), 
//        	"buyer_user_id" => $this->post('buyer_user_id'), 
//        	"seller_user_id" => $this->post('seller_user_id')
//
//        );

//        $chat_history_data = $this->Chat->get_one_by($chat_data);
//
//
//        if($chat_history_data->id == "") {
//	        	
//	        $this->error_response( get_msg( 'err_chat_history_not_exist' ));
//
//
//	    } else {
	    	
//	    	if($this->post('type') == "to_seller") {
//
//		    	$chat_data_update = array(
//
//		        	"item_id" => $this->post('item_id'), 
//		        	"buyer_user_id" => $this->post('buyer_user_id'), 
//		        	"seller_user_id" => $this->post('seller_user_id'),
//		        	"seller_unread_count" => 0
//
//		        );
//
//		    } else if($this->post('type') == "to_buyer") {
//
//		    	$chat_data_update = array(
//
//		        	"item_id" => $this->post('item_id'), 
//		        	"buyer_user_id" => $this->post('buyer_user_id'), 
//		        	"seller_user_id" => $this->post('seller_user_id'),
//		        	"buyer_unread_count" => 0
//
//		        );
//		    }
//
//	    	if( !$this->Chat->Save( $chat_data_update,$chat_history_data->id )) {
//
//	    		$this->error_response( get_msg( 'err_count_update' ));
//
//	    	
//	    	} else {
//
//	    		$obj = $this->Chat->get_one_by($chat_data);
//				$this->ps_adapter->convert_chathistory( $obj );
//				$this->custom_response( $obj );
//
//	    	}
//
//
//	    }


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
    
    public function offer_by_items_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        $rules = array(
        array(
	        	'field' => 'item_id',
	        	'rules' => 'required'
	        )
        );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;
        
        $item_id = $this->post('item_id');
        $condition = 'requested_item_id = "'.$item_id.'"';
        $obj = $this->db->query("SELECT * FROM `bs_chat_history` WHERE ".$condition)->result();
        
        $this->ps_adapter->convert_chathistory( $obj );
		$this->custom_response( $obj );
    }

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
		$return_type = $this->post('return_type');

		$users = global_user_check($user_id);

		$limit = $this->get( 'limit' );
		$offset = $this->get( 'offset' );
		
		//start new code
		$type 	 = $this->post('type');
		if($type != '' && $type != 2){
			$condition = "operation_type = '".$type."'";
		}
		// if($return_type == 'buyer'){
			
		// } else if($return_type == 'seller'){
		// 	$condition = "operation_type = '2'";	
		// }
		// $condition = "type = '".$type."'";

		// if($type == DIRECT_BUY || $type == REQUEST_ITEM) {
		// 	$condition .= " AND buyer_user_id = '".$user_id."'";
		// }else if($type == SELLING) {
		// 	$condition .= " AND seller_user_id = '".$user_id."'";
		// } else if($type == EXCHANGE) {
		// 	$condition .= " AND (buyer_user_id = '".$user_id."' OR seller_user_id = '".$user_id."') ";
		// }
		if($type == DIRECT_BUY || $type == REQUEST_ITEM) {
			$condition .=  $condition != '' ? 
			" AND buyer_user_id = '".$user_id."' AND requested_item_id != ''" : 
			"buyer_user_id = '".$user_id."' AND (operation_type = '1' OR operation_type = '4') AND requested_item_id != ''"; 
		} else if($type == EXCHANGE){
			$condition .= $condition != '' ? 
			" AND (buyer_user_id = '".$user_id."' OR seller_user_id = '".$user_id."') AND operation_type = '3' AND requested_item_id != ''" : 
			"(buyer_user_id = '".$user_id."' OR seller_user_id = '".$user_id."') AND operation_type = '3' AND requested_item_id != '' ";
		} else {
			$condition .= $condition != '' ? 
			" AND seller_user_id = '".$user_id."' AND requested_item_id != ''" : 
			"seller_user_id = '".$user_id."' AND operation_type != '3' AND requested_item_id != ''";		
		}
		//echo "SELECT DISTINCT requested_item_id FROM `bs_chat_history` WHERE ".$condition; die(' dieee');
		$records = $this->db->query("SELECT DISTINCT requested_item_id FROM `bs_chat_history` WHERE ".$condition)->result();
		$obj = [];
		//  SEND USER COUNT AND Lowest Price
		foreach($records as $key => $data){
			$details = $this->db->query("SELECT * FROM `bs_chat_history` WHERE ".$condition." AND requested_item_id = '".$records[$key]->requested_item_id."'")->row();
			$obj[] = isset($details) && !empty($details) ? $details : [];
		}
		foreach($obj as $key => $data){
			if(isset($obj[$key]->requested_item_id) && isset($obj[$key]->operation_type)){
				$total = $this->db->query('SELECT COUNT(*) AS total_user FROM `bs_chat_history` WHERE '.$condition.' AND requested_item_id = "'.$obj[$key]->requested_item_id.'"')->row();
				$obj[$key]->bid_count = $total->total_user;

				$result_price = $this->db->query('SELECT MIN(nego_price) AS lowest_price FROM `bs_chat_history` WHERE '.$condition.' AND requested_item_id = "'.$obj[$key]->requested_item_id.'"')->row();
				$obj[$key]->lowest_price = $result_price->lowest_price ? $result_price->lowest_price : $obj[$key]->nego_price;
				// if($obj[$key]->operation_type==3){
				// 	$exchangeOfferedItems = $this->db->query('SELECT * FROM `bs_exchange_chat_history` WHERE chat_id = "'.$obj[$key]->id.'"')->result();
				// 	$obj[$key]->exchange_offered_items_detail = $exchangeOfferedItems;
				// }
			} else {
				$obj[$key]->lowest_price = $obj[$key]->nego_price;
			}
			$obj[$key]->quantity = $obj[$key]->quantity != 0 ? $obj[$key]->quantity : 1;	 		
		}
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
		} else {
			$chat_data_update = array(
				"nego_price" => $this->post('price'), 
			);
			if(!$this->Chat->Save($chat_data_update, $chat_history_id)) {
				$this->error_response(get_msg( 'err_count_update'));
			} else {
				$this->success_response(get_msg('offer_change_success'));
			}
		}
	}

	// GET FEES API	
	function get_fees_detail_post(){
		$config_data = $this->Backend_config->get_one_by();
		$chat_data_update = array(
			"selling_fees" => $config_data->selling_fees,
			"processing_fees" => $config_data->processing_fees, 
		);
		// $this->ps_adapter->convert_chathistory( $chat_data_update );
		$this->custom_response( $chat_data_update );
	}
	
	public function get_offer_details_post(){
		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
		// validation rules for cancel offer
		$rules = array(
			array(
	        	'field' => 'offer_id',
	        	'rules' => 'required'
	        )
        );
		if ( !$this->is_valid( $rules )) exit;
		$offerId = $this->post('offer_id');
		$conds['id'] = $offerId;

        // check user id

        $offer_data = $this->Chat->get_one_by($conds);
        if(!$offer_data->shipping_amount) {
            $this->db->where('id', $offerId)->update('bs_chat_history', ['shipping_amount' => NULL]);
            $offer_data->shipping_amount = '';
        }
        if(!empty($offer_data->packagesize_id)) {
            $package_details = $this->Packagesizes->get_one( $offer_data->packagesize_id );
            $this->ps_adapter->convert_packagesize( $package_details );
            $offer_data->package_details = $package_details;
        } else {
            $offer_data->package_details = (object)[];
        }
        if(!empty($offer_data->shippingcarrier_id)) {
            $shipping_details = $this->Shippingcarriers->get_one( $offer_data->shippingcarrier_id );
            $this->ps_adapter->convert_shippingcarrier( $shipping_details );
            $offer_data->shipping_details = $shipping_details;
        } else {
            $offer_data->shipping_details = (object)[];
        }
		$this->ps_adapter->convert_chathistory( $offer_data );
		// echo '<pre>'; print_r($offer_data); die('rukooo');
		if($offer_data){
			$this->custom_response( $offer_data );
		} else {
			$this->error_response(get_msg( 'offer_not_found'));
		}
	}
    
    public function save_shipping_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
		$rules = array(
			array(
	        	'field' => 'chat_id',
	        	'rules' => 'required'
	        ),
            array(
	        	'field' => 'prepaidlabel',
	        	'rules' => 'required'
	        )
        );
		if ( !$this->is_valid( $rules )) exit;
		$posts_var = $this->post();
        
        if(!$posts_var['prepaidlabel']){
            if(!isset($posts_var['shipping_amount']) || empty($posts_var['shipping_amount']) || is_null($posts_var['shipping_amount'])) {
                $this->error_response("Please provide shipping amount");
            } else {
                $this->db->where('id', $posts_var['chat_id'])->update('bs_chat_history', ['shipping_amount' => $posts_var['shipping_amount']]);
            }
        } else {
            $this->db->where('id', $posts_var['chat_id'])->update('bs_chat_history', ['shipping_amount' => $posts_var['shipping_amount']]);
        }
        if($posts_var['prepaidlabel']) {
            if(!isset($posts_var['packagesize_id']) || empty($posts_var['packagesize_id']) || is_null($posts_var['packagesize_id'])){
                $this->error_response("Please provide packagesize id");
            }   
            if(!isset($posts_var['shippingcarrier_id']) || empty($posts_var['shippingcarrier_id']) || is_null($posts_var['shippingcarrier_id'])) {
                $this->error_response("Please provide shippingcarrier id");
            }   

            $this->db->where('id', $posts_var['chat_id'])->update('bs_chat_history', ['packagesize_id' => $posts_var['packagesize_id'],'shippingcarrier_id' => $posts_var['shippingcarrier_id']]);
        }
        
        $get_user = $this->db->select('buyer_user_id')->from('bs_chat_history')->where('id', $posts_var['chat_id'])->get()->row();
        $buyer = $this->db->select('device_token')->from('core_users')
                            ->where('user_id', $get_user->buyer_user_id)->get()->row();
        send_push( $buyer->device_token, ["message" => "Shipment confirmed by Seller ", "flag" => "chat",'chat_id' => $posts_var['chat_id']] );
        
        $this->response(['status' => 'success', 'message' => 'Shipping details saved']);
        
    }
    
    public function unread_count_bytype_post() {
         $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        $rules = array(
			array(
	        	'field' => 'user_id',
	        	'rules' => 'required'
	        ),
        );

        // exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;
        $count_object = new stdClass; 
        $count_object->request_unread_counts = $this->db->from('bs_chat_history')
            ->where('operation_type', REQUEST_ITEM)
            ->where('buyer_user_id', $this->post('user_id'))
            ->where('buyer_unread_count > 0')
            ->group_start()
                ->where('requested_item_id is NOT NULL', NULL, FALSE)
                ->or_where('offered_item_id is NOT NULL', NULL, FALSE)
            ->group_end()
            ->get()->num_rows();
        
        $count_object->directbuy_unread_counts = $this->db->from('bs_chat_history')
            ->where('operation_type', DIRECT_BUY)
            ->where('buyer_user_id', $this->post('user_id'))
            ->where('buyer_unread_count > 0')
            ->group_start()
                ->where('requested_item_id is NOT NULL', NULL, FALSE)
                ->or_where('offered_item_id is NOT NULL', NULL, FALSE)
            ->group_end()
            ->get()->num_rows();
        
        $count_object->selling_unread_counts = $this->db->from('bs_chat_history')
            ->where('operation_type', DIRECT_BUY)
            ->where('seller_user_id', $this->post('user_id'))
            ->where('seller_unread_count > 0')
            ->group_start()
                ->where('requested_item_id is NOT NULL', NULL, FALSE)
                ->or_where('offered_item_id is NOT NULL', NULL, FALSE)
            ->group_end()
            ->get()->num_rows();
        
        $exchange_selling_unread_counts = $this->db->from('bs_chat_history')
            ->where('operation_type', EXCHANGE)
            ->where('seller_user_id', $this->post('user_id'))
            ->where('seller_unread_count > 0')
            ->group_start()
                ->where('requested_item_id is NOT NULL', NULL, FALSE)
                ->or_where('offered_item_id is NOT NULL', NULL, FALSE)
            ->group_end()
            ->get()->num_rows();
        
        $exchange_buying_unread_counts = $this->db->from('bs_chat_history')
            ->where('operation_type', EXCHANGE)
            ->where('buyer_user_id', $this->post('user_id'))
            ->where('buyer_unread_count > 0')
            ->group_start()
                ->where('requested_item_id is NOT NULL', NULL, FALSE)
                ->or_where('offered_item_id is NOT NULL', NULL, FALSE)
            ->group_end()
            ->get()->num_rows();
        
        $count_object->exchange_unread_counts = $exchange_selling_unread_counts + $exchange_buying_unread_counts;
        $final_data = $this->ps_security->clean_output( $count_object );
		$this->response( $final_data );
    }

}
