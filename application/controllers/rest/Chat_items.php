<?php
require_once( APPPATH .'libraries/REST_Controller.php' );

/**
 * REST API for News
 */
class Chat_items extends API_Controller
{

	/**
	 * Constructs Parent Constructor
	 */
	function __construct()
	{
		parent::__construct( 'Item' );
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

		return $conds;
	}

	/**
	 * Get buyer and seller list
	 */
	function get_buyer_seller_list_post()
	{
		// add flag for default query
		$this->is_get = true;

		// get the post data
		$user_id = $this->post('user_id');
		$return_type 	 = $this->post('return_type');

		$limit = $this->get( 'limit' );
		$offset = $this->get( 'offset' );

			// get limit & offset

			if ( $return_type == "buyer") {
				//$conds['buyer_user_id'] = $user_id;

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


				
				
				//pph modified @ 22 June 2019
				$conds['seller_user_id'] = $user_id;

				//print_r($conds['id']);die;

				
				if ( !empty( $limit ) && !empty( $offset )) {
				// if limit & offset is not empty
					
					$chats = $this->Chat->get_all_chat_history($conds,$limit, $offset)->result();
				} else if ( !empty( $limit )) {
				// if limit is not empty

					
					$chats = $this->Chat->get_all_chat_history($conds, $limit )->result();
				} else {
				// if both are empty
					
					$chats = $this->Chat->get_all_chat_history($conds)->result();
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

			

		} else if ( $return_type == "seller") {

			//$conds['seller_user_id'] = $user_id;

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

			//pph modified @ 22 June 2019
			$conds['buyer_user_id'] = $user_id;

			if ( !empty( $limit ) && !empty( $offset )) {
			// if limit & offset is not empty
				
				$chats = $this->Chat->get_all_chat_history($conds,$limit, $offset)->result();
			} else if ( !empty( $limit )) {
			// if limit is not empty

				
				$chats = $this->Chat->get_all_chat_history($conds, $limit )->result();
			} else {
			// if both are empty
				
				$chats = $this->Chat->get_all_chat_history($conds)->result();
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


	/**
	 * Convert Object
	 */
	function convert_object( &$obj )
	{

		// call parent convert object
		parent::convert_object( $obj );

		// convert customize item object
		$this->ps_adapter->convert_chatitem( $obj );
	}

}