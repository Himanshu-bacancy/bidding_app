<?php
require_once( APPPATH .'libraries/REST_Controller.php' );
require_once( APPPATH .'libraries/braintree_lib/autoload.php' );
require_once( APPPATH .'libraries/stripe_lib/autoload.php' );

/**
 * REST API for News
 */
class Paid_items extends API_Controller
{

	/**
	 * Constructs Parent Constructor
	 */
	function __construct()
	{
		parent::__construct( 'Paid_item' );
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
			
			$conds['added_user_id'] = $this->get( 'login_user_id' );
		}

		return $conds;
	}

	function save_history($item_id) {
		$added_user_id = $this->Item->get_one($item_id)->added_user_id;
		/* for transaction code */
 		$transaction_row_count = $this->Paid_item->count_all();
		$current_date_month = date("Ym");
		$conds['code'] = $current_date_month;
		$trans_code_checking =  $this->Code->get_one_by($conds)->code;
		if($trans_code_checking == "") {
			//New record for this year--mm, need to insert as inside the core_code_generator table
			$data['type']  =  "transaction";
	 		$data['code']  =  $today = date("Ym");
	 		$data['count'] = $transaction_row_count + 1;
	 		$data['added_user_id'] = $added_user_id;
	 		$data['added_date'] = date("Y-m-d H:i:s"); 
	 		$data['updated_date'] = date("Y-m-d H:i:s"); 
	 		$data['updated_user_id'] = 0;
	 		$data['updated_flag'] = 0;

 			if( !$this->Code->save($data, $id) ) {
				// rollback the transaction
				$this->db->trans_rollback();
				$this->error_response( get_msg( 'err_model' ));
 			}

 			// get inserted id
			if ( !$id ) $id = $data['id']; 

			if($id) {
				$trans_code = $this->Code->get_one($id)->code;
			}
		} else {
			//record is already exist so just need to update for count field only
 			$data['count'] = $transaction_row_count + 1;

 			$core_code_generator_id =  $this->Code->get_one_by($conds)->id;

 			if( !$this->Code->save($data, $core_code_generator_id) ) {
				// rollback the transaction
				$this->db->trans_rollback();
				$this->error_response( get_msg( 'err_model' ));
 			}

 			$conds['id'] = $core_code_generator_id;
 			$trans_code =  $this->Code->get_one_by($conds)->code . ($transaction_row_count + 1);
		}
		/* save paid item history data*/
		$start_date = $this->post('start_date');
		$day = $this->post('how_many_day');
		$start_timestamp = $this->post('start_timestamp');
		$date = date("Y-m-d H:i:s");
        $temp_start_date = date("Y-m-d H:i:s", substr($start_timestamp, 0, 10));

        $vardate = explode(' ',$temp_start_date,2);

        $convert_start_date = $vardate[0];
        
	  	$temp_end_date = date('Y-m-d H:i:s', strtotime($temp_start_date. ' + '.$day.' day'));
	  	$varenddate = explode(' ',$temp_end_date,2);
	  	$end_date = $varenddate[0];
	  	//print_r($end_date);die;

	  	$d = DateTime::createFromFormat('Y-m-d H:i:s', $temp_end_date);
		$end_timestamp = $d->getTimestamp();
		//print_r($end_timestamp);die;

	  	$paid_data = array(
	  		"item_id" => $item_id,
	  		"start_date" => $convert_start_date,
	  		"start_timestamp" => $start_timestamp,
	  		"end_date" => $end_date,
	  		"end_timestamp" => $end_timestamp,
	  		"amount" => $this->post('amount'),
	  		"payment_method" => $this->post('payment_method'),
	  		"razor_id" => $this->post('razor_id'),
	  		"purchased_id" => $this->post('purchased_id'),
	  		"added_user_id" => $added_user_id
	  	);
	  	
	  	$this->Paid_item->save($paid_data);
	  	$id = $paid_data['id'];
	  	// print_r($id);die;
	  	return $id;

	}

	function add_post() {

		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
		// print_r($this->post( 'payment_method' ));die;
		if($this->post( 'payment_method' ) == "paypal") {

			//User using Paypal to submit the transaction
			$payment_info = $this->Paid_config->get_one('pconfig1');

			$gateway = new Braintree_Gateway([
			  'environment' => trim($payment_info->paypal_environment),
			  'merchantId' => trim($payment_info->paypal_merchant_id),
			  'publicKey' => trim($payment_info->paypal_public_key),
			  'privateKey' => trim($payment_info->paypal_private_key)
			]);

			$result = $gateway->transaction()->sale([
			  'amount' 			   => $this->post( 'amount' ),
			  'paymentMethodNonce' => $this->post( 'payment_method_nonce' ),
			  'options' => [
			    'submitForSettlement' => True
			  ]
			]);

			if($result->success == 1) {
			
				$paypal_result = $result->success;
			
			} else {

				$this->error_response( get_msg( 'paypal_transaction_failed' ) );
			
			}

		} else if($this->post( 'payment_method' ) == "stripe") {

			//User using Stripe to submit the transaction
			$paid_config = $this->Paid_config->get_one('pconfig1');

			try {
			
				# set stripe test key
				\Stripe\Stripe::setApiKey( trim($paid_config->stripe_secret_key) );
				
				$charge = \Stripe\Charge::create(array(
			    	"amount" 	  => $this->post( 'amount' ) * 100, // amount in cents, so need to multiply with 100 .. $amount * 100
			    	"currency"    => trim($paid_config->currency_short_form),
			    	"source"      => $this->post( 'payment_method_nonce' ),
			    	"description" => get_msg('order_desc')
			    ));
			    
			    if( $charge->status == "succeeded" )
			    {
			    	$stripe_result = 1;
			    } else {
			    	$this->error_response( get_msg( 'stripe_transaction_failed' ) );
			    }
				
			} 

			catch(exception $e) {
			  	
			 	$this->error_response( get_msg( 'stripe_transaction_failed' ) );
			    
			 }


		} else if($this->post( 'payment_method' ) == "razor") {
          
			//User Using COD 
			$payment_method = "Razor";


			$razor_result = 1;

		} else if($this->post( 'payment_method' ) == "offline") {

			//User Using COD 
			$payment_method = "Offline";


			$offline_result = 1;

		} else if($this->post( 'payment_method' ) == "paystack") {

			//User Using COD 
			$payment_method = "Paystack";


			$paystack_result = 1;

		} else if($this->post( 'payment_method' ) == "In_App_Purchase") {

			//User Using COD 
			$payment_method = "In_App_Purchase";


			$in_app_purchase_result = 1;

		}
		
		if( $paypal_result == 1 || $stripe_result == 1  || $razor_result == 1 || $paystack_result == 1 || $in_app_purchase_result == 1 ) {
			$item_id = $this->post('item_id');
			$item_data = array(
				"is_paid" => "1"
			);
			$this->Item->save($item_data,$item_id);
			$conds['item_id'] = $item_id;
			$paid_items = $this->Paid_item->get_all_by($conds)->result();
			if (empty($paid_items)) {
				$id = $this->save_history($item_id);	
			} else {
				foreach($paid_items as $paid) {
					
					$today = date('Y-m-d H:i:s');
					if( $paid->start_date < $today && $paid->end_date < $today ) {
					
					 	$finished_status = 1 ;
					} 
					
				}
				if ($finished_status == 1) {
					$id = $this->save_history($item_id);
				} else {
					$this->error_response( get_msg( 'err_paid_item_history' ));
				}
			}

		  	$obj = $this->Paid_item->get_one( $id );
			
			$this->ps_adapter->convert_paid_history( $obj );
			$this->custom_response( $obj );
		}

		if ( $offline_result == 1 ) {

			$item_id = $this->post('item_id');
			$item_data = array(
				"status" => 1,
				"payment_type" => 1,
				"is_paid" => 0
			);

			$this->Item->save($item_data,$item_id);
			$conds['item_id'] = $item_id;
			$paid_items = $this->Paid_item->get_all_by($conds)->result();
			if (empty($paid_items)) {
				$id = $this->save_history($item_id);	
			} else {
				foreach($paid_items as $paid) {
					
					$today = date('Y-m-d H:i:s');
					if( $paid->start_date < $today && $paid->end_date < $today ) {
					
					 	$finished_status = 1 ;
					} 
					
				}
				if ($finished_status == 1) {
					$id = $this->save_history($item_id);
				} else {
					$this->error_response( get_msg( 'err_paid_item_history' ));
				}
			}

		  	$obj = $this->Paid_item->get_one( $id );
			
			$this->ps_adapter->convert_paid_history( $obj );
			$this->custom_response( $obj );
		}
		
	}

	function add_promoteditems_post() {

		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);

		// validation rules for add promoted item
		$rules = array(
			array(
	        	'field' => 'item_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'plan_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'card_id',
	        	'rules' => 'required'
	        ),
	       
	        array(
	        	'field' => 'cvc',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'start_date',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'user_id',
	        	'rules' => 'required'
	        )
        );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

		// check item id

		$item_id = $this->post('item_id');
		$conds['id'] = $item_id;
		$item_data = $this->Item->get_one_by($conds);
		if ( $item_data->id == "") {
			$this->error_response( get_msg( 'invalid_item_id' ));
		}

		// check plan id
		$plancheck  = $this->Promotingitemplans->get_one($this->post('plan_id'));
		if(isset($plancheck->is_empty_object))
		{
			$this->error_response( get_msg( 'invalid_promotingplan_id' ));
		}

		// check user id
		$usercheck  = $this->User->get_one($this->post('user_id'));
		if(isset($usercheck->is_empty_object))
		{
			$this->error_response( get_msg( 'invalid_user_id' ));
		}

		// check card id
		$cardcheck  = $this->db->from('bs_card')->where('id', $this->post('card_id'))->get()->row();
		if(isset($cardcheck->is_empty_object))
		{
			$this->error_response( get_msg( 'invalid_card_id' ));
		}

		$card_id = $this->post('card_id');
		$cvc     = $this->post('cvc');
		$card_details = $this->db->from('bs_card')->where('id', $card_id)->get()->row();
		$expiry_date = explode('/',$card_details->expiry_date);
		$paid_config = $this->Paid_config->get_one('pconfig1');
		# set stripe test key
		\Stripe\Stripe::setApiKey(trim($paid_config->stripe_secret_key));
		$record_id = 0;
		try {
			$response = \Stripe\PaymentMethod::create([
				'type' => 'card',
				'card' => [
					'number' => $card_details->card_number,
					'exp_month' => $expiry_date[0],
					'exp_year' => $expiry_date[1],
					'cvc' => $cvc
				]
			]);
			$response = \Stripe\PaymentIntent::create([
				'amount' => $plancheck->price * 100,
				"currency" => trim($paid_config->currency_short_form),
				'payment_method' => $response->id,
				'payment_method_types' => ['card']
			]);
			
			$item_id = $this->post('item_id');
			$item_data = array(
				"is_paid" => "1"
			);
			$this->Item->save($item_data,$item_id);

			$start_date = $this->post('start_date');
			$day = $plancheck->days;
			$start_timestamp = strtotime($this->post('start_date'));
			$date = date("Y-m-d H:i:s");

			$temp_start_date = date("Y-m-d H:i:s", substr($start_timestamp, 0, 10));

			$vardate = explode(' ',$temp_start_date,2);

			$convert_start_date = $vardate[0];
			
			$temp_end_date = date('Y-m-d H:i:s', strtotime($temp_start_date. ' + '.$day.' day'));
			$varenddate = explode(' ',$temp_end_date,2);
			$end_date = $varenddate[0];
			$d = DateTime::createFromFormat('Y-m-d H:i:s', $temp_end_date);
			$end_timestamp = $d->getTimestamp();

			$paid_data = array(
				"item_id" => $item_id,
				"start_date" => $this->post('start_date'),
				"start_timestamp" => $start_timestamp,
				"end_date" => $end_date,
				"end_timestamp" => $end_timestamp,
				"amount" => $plancheck->price,
				"payment_method" => 'stripe',
				"transaction" => $response,
				"payment_id" => $response->id,
				"added_user_id" => $this->post('user_id')
			);
			
			$this->Paid_item->save($paid_data);
			$id = $paid_data['id'];
			
			if (isset($response->id)) {
				$paydata['payment_status'] = 'initiate';
				$this->db->where('id', $id);
        		$this->db->update('bs_paid_items_history', $paydata);
				
				$this->response(['status' => "success", 'payment_status' => 'success', 'intent_id' => $response->id, 'record_id' => $id, 'client_secret' => $response->client_secret, 'response' => ['payment_method' => $response->payment_method]]);
			} else {
				$paydata['payment_status'] = 'fail';
				$this->db->where('id', $id);
        		$this->db->update('bs_paid_items_history', $paydata);
				$this->error_response(get_msg('stripe_transaction_failed'));
			}
		} catch (exception $e) {
			$paydata['payment_status'] = 'fail';
			$this->db->where('id', $id);
			$this->db->update('bs_paid_items_history', $paydata);
			$this->error_response(get_msg('stripe_transaction_failed'));
		}
	}
    
    function update_promoteditems_post() {

		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);

		// validation rules for add promoted item
		$rules = array(
			array(
	        	'field' => 'item_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'intent_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'status',
	        	'rules' => 'required'
	        ),
        );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;
        $item_id = $this->post('item_id');
        $intent_id = $this->post('intent_id');
        $status = $this->post('status');
        if($status == 'succeeded') {
            $this->response(['status' => 'success', 'message' => 'Item promoted successfully']);
        } else {
            $item_data = array(
                "is_paid" => "0"
            );
            $this->Item->save($item_data,$item_id);
            $paydata['payment_status'] = 'fail';
            $this->db->where('payment_id', $intent_id);
            $this->db->where('item_id', $item_id);
            $this->db->update('bs_paid_items_history', $paydata);
                
            $this->response(['status' => 'success', 'message' => 'Item promoted failed']);
        }
    }

	/**
	 * Convert Object
	 */
	function convert_object( &$obj )
	{
		// call parent convert object
		parent::convert_object( $obj );

		// convert customize feed object
		$this->ps_adapter->convert_paid_history( $obj );

	}
}