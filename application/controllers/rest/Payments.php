<?php

require_once( APPPATH . 'libraries/REST_Controller.php' );
require_once( APPPATH .'libraries/stripe_lib/autoload.php' );

/**
 * REST API for Notification
 */
class Payments extends API_Controller {

    /**
     * Constructs Parent Constructor
     */
    function __construct() {
        // call the parent
        parent::__construct('Payments');
    }

    public function checkout_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        $rules = array(
            array(
                'field' => 'user_id',
                'rules' => 'required'
            ),
//            array(
//                'field' => 'item_ids',
//                'rules' => 'required'
//            ),
            array(
                'field' => 'delivery_method',
                'rules' => 'required'
            ),
            array(
                'field' => 'payment_method',
                'rules' => 'required'
            ),
            array(
                'field' => 'address_id',
                'rules' => 'required'
            ),
            array(
                'field' => 'card_id',
                'rules' => 'required'
            ),
            array(
                'field' => 'total_amount',
                'rules' => 'required'
            ),
            array(
                'field' => 'cvc',
                'rules' => 'required'
            ),
//            array(
//                'field' => 'transaction_detail',
//                'rules' => 'required'
//            )
        );
        if (!$this->is_valid($rules)) exit;
        
        $user_id            = $this->post('user_id');
//        $item_ids           = implode(',', $this->post('item_ids'));
        $delivery_method    = $this->post('delivery_method');
        $payment_method     = strtolower($this->post('payment_method'));
        $address_id         = $this->post('address_id');
        $total_amount       = $this->post('total_amount');
        $posts_var = $this->post();

        $item_ids = [];
        if(!isset($posts_var['item_ids']) || empty($posts_var['item_ids']) || is_null($posts_var['item_ids'])) { 
            $this->error_response("Please pass item ids");
        } else {
            if(is_array($posts_var['item_ids'])) {
                $item_ids = implode(',', $posts_var['item_ids']);
            } else {
                $item_ids = $posts_var['item_ids'];
            }
        }
        if($payment_method == 'card') {
            if(!isset($posts_var['card_id']) || empty($posts_var['card_id']) || is_null($posts_var['card_id'])) {
                $this->error_response("Please pass card id");
            }
            if(!isset($posts_var['cvc']) || empty($posts_var['cvc']) || is_null($posts_var['cvc'])) {
                $this->error_response("Please pass cvc");
            }
            $card_id = $this->post('card_id');
            $cvc     = $this->post('cvc');
            $card_details = $this->db->from('bs_card')->where('id', $card_id)->get()->row();
            $expiry_date = explode('/',$card_details->expiry_date);
            $paid_config = $this->Paid_config->get_one('pconfig1');
            $item_price = $this->post('total_amount');
            
            $get_item = $this->db->select('pay_shipping_by,shipping_type,shippingcarrier_id,shipping_cost_by_seller')->from('bs_items')->where('id', $item_ids)->get()->row();
            if($get_item->pay_shipping_by == '1') {
                if($get_item->shipping_type == '1') {
                    $get_shiping_detail = $this->db->from('bs_shippingcarriers')->where('id', $get_item->shippingcarrier_id)->get()->row();
                    
                    $item_price = $item_price + (float)$get_shiping_detail->price;
                } else if($get_item->shipping_type == '2'){
                    $item_price = $item_price + $get_item->shipping_cost_by_seller;   
                }
            }
            
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
                    'amount' => $item_price * 100,
                    "currency" => trim($paid_config->currency_short_form),
                    'payment_method' => $response->id,
                    'payment_method_types' => ['card']
                ]);
                $new_odr_id = 'odr_'.time().$user_id;
                $this->db->insert('bs_order', ['order_id' => $new_odr_id,'user_id' => $user_id, 'items' => $item_ids, 'delivery_method' => $delivery_method,'payment_method' => 'card', 'card_id' => $card_id, 'address_id' => $address_id, 'total_amount' => $total_amount, 'status' => 'pending', 'delivery_status' => 'pending', 'transaction' => $response,'created_at' => date('Y-m-d H:i:s')]);
                $record_id = $this->db->insert_id();
                if (isset($response->id)) { 
                    $this->db->where('id', $record_id)->update(['status' => 'initiate', 'transaction_id' => $response->id]);
                    $this->response(['status' => "success", 'order_status' => 'success', 'intent_id' => $response->id, 'record_id' => $new_odr_id, 'client_secret' => $response->client_secret, 'response' => $response]);
                    
                    $items = $this->db->from('bs_items')->where_in('id', $item_ids)->get()->result_array();
                    foreach ($items as $key => $value) {
                        $seller_device_token = $this->db->select('device_token')->from('core_users')->where('user_id', $value['added_user_id'])->get()->row();
                        send_push( $seller_device_token->device_token, ["message" => "New order arrived", "flag" => "new_order"] );
                    }
                } else {
                    $this->db->where('id', $record_id)->update(['status' => 'fail']);
                    $this->error_response(get_msg('stripe_transaction_failed'));
                }
            } catch (exception $e) {
                $this->db->where('id', $record_id)->update(['status' => 'fail']);
                $this->error_response(get_msg('stripe_transaction_failed'));
            }
        } else if($payment_method == 'cash') {
            $this->db->insert('bs_order', ['user_id' => $user_id, 'items' => $item_ids, 'delivery_method' => $delivery_method, 'payment_method' => 'cash', 'card_id' => 0, 'address_id' => $address_id, 'total_amount' => $total_amount, 'status' => 'success', 'delivery_status' => 'pending', 'transaction' => '','created_at' => date('Y-m-d H:i:s')]);
            
            $this->response(['status' => "success", 'order_status' => 'success']);
        }
//        $transaction_detail = $this->post('transaction_detail');
        
//        $this->db->insert('bs_order', ['user_id' => $user_id, 'items' => $item_ids, 'delivery_method' => $delivery_method, 'card_id' => $card_id, 'address_id' => $address_id, 'total_amount' => $total_amount, 'status' => 'success', 'transaction' => $transaction_detail,'created_at' => date('Y-m-d H:i:s')]);
//        
//        $this->response(['status' => "success", 'order_status' => 'success']);
//        if($delivery_method == 'card') {
//            $card_details = $this->db->from('bs_card')->where('id', $card_id)->get()->row();
//            $expiry_date = explode('/',$card_details->expiry_date);
//            $paid_config = $this->Paid_config->get_one('pconfig1');
//            # set stripe test key
//            \Stripe\Stripe::setApiKey(trim($paid_config->stripe_secret_key));
//            $record_id = 0;
//            try {
//                $response = \Stripe\PaymentMethod::create([
//                    'type' => 'card',
//                    'card' => [
//                        'number' => $card_details->card_number,
//                        'exp_month' => $expiry_date[0],
//                        'exp_year' => $expiry_date[1],
//                        'cvc' => $cvc
//                    ]
//                ]);
//                $response = \Stripe\PaymentIntent::create([
//                    'amount' => $this->post('total_amount') * 100,
//                    "currency" => trim($paid_config->currency_short_form),
//                    'payment_method' => $response->id,
//                    'payment_method_types' => ['card']
//                ]);
//                $response = \Stripe\PaymentIntent::retrieve($response->id)->confirm();
//    //            $response = \Stripe\Balance::retrieve();
//    //            $response = \Stripe\BalanceTransaction::all();
//    //            print_r($response);
//    //            die();
//                $this->db->insert('bs_order', ['user_id' => $user_id, 'items' => $item_ids, 'delivery_method' => $delivery_method, 'card_id' => $card_id, 'address_id' => $address_id, 'total_amount' => $total_amount, 'status' => 'success', 'transaction' => $response,'created_at' => date('Y-m-d H:i:s')]);
//                $record_id = $this->db->insert_id();
//                if ($response->status == "succeeded") {
//                    $this->db->where('id', $record_id)->update(['status' => 'success']);
//                    $this->response(['status' => "success", 'order_status' => 'success']);
//                } else {
//                    $this->db->where('id', $record_id)->update(['status' => 'fail']);
//                    $this->error_response(get_msg('stripe_transaction_failed'));
//                }
//            } catch (exception $e) {
//                $this->db->where('id', $record_id)->update(['status' => 'fail']);
//                $this->error_response(get_msg('stripe_transaction_failed'));
//            }
//        } else {
//            $this->db->insert('bs_order', ['user_id' => $user_id, 'items' => $item_ids, 'delivery_method' => $delivery_method, 'card_id' => $expiry_date, 'address_id' => $address_id, 'total_amount' => $total_amount, 'status' => 'success', 'created_at' => date('Y-m-d H:i:s')]);
//            
//            $this->response(['status' => "success", 'order_status' => 'success']);
//        }
    }
    
    public function seller_orders_post() {
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
        if (!$this->is_valid($rules)) exit;
        
        $user_id = $this->post('user_id');
        $orders = $this->db->select('bs_order_confirm.*')->from('bs_order_confirm')->join('bs_order', 'bs_order.id = bs_order_confirm.order_id')->where('seller_id', $user_id)->get()->result_array();
        
        if(count($orders)) {
            $this->response($orders);
        } else {
            $this->error_response($this->config->item( 'record_not_found'));
        }
    }
    
    public function confirm_order_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        $rules = array(
            array(
                'field' => 'order_id',
                'rules' => 'required'
            ),array(
                'field' => 'card_id',
                'rules' => 'required'
            ),array(
                'field' => 'cvv',
                'rules' => 'required'
            )
        );
        if (!$this->is_valid($rules)) exit;
        
        $posts_var = $this->post();
//        $order_id = $this->post('order_id');
//        $card_id = $this->post('card_id');
        $amount = 0;
        $track_number = '';
        if( (!isset($posts_var['shipping_carrier_id']) || empty($posts_var['shipping_carrier_id']) || is_null($posts_var['shipping_carrier_id'])) && (!isset($posts_var['package_size']) || empty($posts_var['package_size']) || is_null($posts_var['package_size'])) ) {
            if(!isset($posts_var['amount']) || empty($posts_var['amount']) || is_null($posts_var['amount'])) {
                $this->error_response("Please provide shipping info");
            } else {
                $amount = $posts_var['amount'];
            }
        } else {
            $shippingcarriers_details = $this->db->from('bs_shippingcarriers')->where('id', $posts_var['shipping_carrier_id'])->get()->row();
            $amount = $shippingcarriers_details->price;
            
            $package_details = $this->db->from('bs_packagesizes')->where('id', $shippingcarriers_details->packagesize_id)->get()->row();
            
            $buyer_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode')->from('bs_order')
                    ->join('core_users', 'bs_order.user_id = core_users.user_id')
                    ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id')
                    ->where('order_id', $posts_var['order_id'])->get()->row();
            
            $seller_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode')->from('bs_order_confirm')
                    ->join('core_users', 'bs_order_confirm.seller_id = core_users.user_id')
                    ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id')
                    ->where('order_id', $posts_var['order_id'])->get()->row();
            /*Shippo integration Start*/
            $headers = array(
                "Content-Type: application/json",
                "Authorization: ShippoToken ".SHIPPO_AUTH_TOKEN  // place your shippo private token here
                                  );

            $url = 'https://api.goshippo.com/transactions/';

            $address_from = array(
                "name"=> $seller_detail->user_name,
                "street1"=> $seller_detail->address1.','.$seller_detail->address2,
                "city"=> $seller_detail->city,
                "state"=> $seller_detail->state,
                "zip" => $seller_detail->zipcode,
                "country" => $seller_detail->country,
                "phone" => $seller_detail->user_phone,
                "email" => $seller_detail->user_email
                          );    

            $address_to = array(
                "name"=> $buyer_detail->user_name,
                "street1"=> $buyer_detail->address1.','.$buyer_detail->address2,
                "city"=> $buyer_detail->city,
                "state"=> $buyer_detail->state,
                "zip" => $buyer_detail->zipcode,
                "country" => $buyer_detail->country,
                "phone" => $buyer_detail->user_phone,
                "email" => $buyer_detail->user_email
                          );

            $parcel = array(
                "length"=> $package_details->length,
                "width"=> $package_details->width,
                "height"=> $package_details->height,
                "distance_unit"=> "in",
                "weight"=> $package_details->weight,
                "mass_unit" => "lb"
                          ); 

                $shipment = 
                        array(
                            "address_to" =>$address_to,
                            "address_from" =>$address_from,
                            "parcels"=> $parcel
                                 );

                $shipmentdata = 
                array(
                    "shipment"=> $shipment,
                    "carrier_account"=> $posts_var['shipping_carrier_id'],
                    "servicelevel_token"=> "usps_priority"
                            );                   

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($shipmentdata));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                $response = curl_exec($ch); 
//                echo '<pre>';
//                print_r($response);
                curl_close($ch);	
                $this->db->insert('bs_track_order', ['order_id' => $posts_var['order_id'], 'object_id' => '', 'status' => '', 'tracking_number' => '', 'tracking_url' => '', 'label_url' => '', 'response' => $response, 'created_at' => date('Y-m-d H:i:s')]);
                $track_number = $response->tracking_number;
            /*Shippo integration End*/
        }
        if(is_null($track_number)) {
            $this->error_response("Something wrong with shipping provided detail");
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
                'amount' => $amount * 100,
                "currency" => trim($paid_config->currency_short_form),
                'payment_method' => $response->id,
                'payment_method_types' => ['card']
            ]);
            $this->db->where('order_id', $posts_var['order_id'])->update('bs_order', ['seller_transaction' => $response]);
            if (isset($response->id)) { 
                $this->db->where('order_id', $posts_var['order_id'])->update('bs_order',['seller_transaction_status' => 'initiate', 'seller_transaction_id' => $response->id]);
                $this->response(['status' => "success", 'response' => $response, 'track_number' => $track_number]);
            } else {
                $this->db->where('order_id', $record_id)->update('bs_order',['seller_transaction_status' => 'fail']);
                $this->error_response(get_msg('stripe_transaction_failed'));
            }
        } catch (exception $e) {
            $this->db->where('order_id', $record_id)->update('bs_order',['seller_transaction_status' => 'fail']);
            $this->error_response(get_msg('stripe_transaction_failed'));
        }
    }
    
    public function track_order_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        $rules = array(
            array(
                'field' => 'track_number',
                'rules' => 'required'
            )
        );
        if (!$this->is_valid($rules)) exit;
        
        $track_number = $this->post('track_number');
        $headers = array(
            "Content-Type: application/json",
            "Authorization: ShippoToken ".SHIPPO_AUTH_TOKEN  // place your shippo private token here
                              );

            $url = 'https://api.goshippo.com/tracks/shippo/'.$track_number;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $response = curl_exec($ch); 

//            echo '<pre>';
//            print_r($response);
            curl_close($ch);
            
        $this->response(['status' => 'success', 'response' => json_decode($response)]);
    }
    
    public function seller_update_order_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        $rules = array(
            array(
                'field' => 'order_id',
                'rules' => 'required'
            ), array(
                'field' => 'status',
                'rules' => 'required'
            )
        );
        if (!$this->is_valid($rules)) exit;
        
        $record_id = $this->post('order_id');
        $status = $this->post('status');
        $this->db->where('order_id', $record_id)->update('bs_order',['seller_transaction_status' => $status]);
        
        if($status == 'succeeded') {
            $get_record = $this->db->from('bs_order')->where('order_id', $record_id)->get()->row();
            
            $items = $this->db->from('bs_items')->where_in('id', explode(',', $get_record->items))->get()->result_array();
            
            foreach ($items as $key => $value) {
                $this->db->insert('bs_order_confirm', ['order_id' => $record_id, 'item_id' => $value['id'], 'seller_id' => $value['added_user_id'], 'created_at' => date('Y-m-d H:i:s')]);
            }
        }
        
        $this->response(['status' => 'success', 'message' => 'Record save successfully']);
    }
    
    public function orders_post() {
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
        if (!$this->is_valid($rules)) exit;
        
        $user_id = $this->post('user_id');
        $orders = $this->db->from('bs_order')->where('user_id', $user_id)->get()->result_array();
        
        if(count($orders)) {
            $this->response($orders);
        } else {
            $this->error_response($this->config->item( 'record_not_found'));
        }
    }
    
    public function order_byid_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        $rules = array(
            array(
                'field' => 'order_id',
                'rules' => 'required'
            ),
        );
        if (!$this->is_valid($rules)) exit;
        
        $order_id = $this->post('order_id');
        $orders = $this->db->from('bs_order')->where('order_id', $order_id)->get()->row_array();
        
        if(count($orders)) {
            $this->response($orders);
        } else {
            $this->error_response($this->config->item( 'record_not_found'));
        }
    }
    
    public function update_order_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        $rules = array(
            array(
                'field' => 'record_id',
                'rules' => 'required'
            ), array(
                'field' => 'status',
                'rules' => 'required'
            )
        );
        if (!$this->is_valid($rules)) exit;
        
        $record_id = $this->post('record_id');
        $status = $this->post('status');
        $this->db->where('order_id', $record_id)->update('bs_order',['status' => $status]);
        
        if($status == 'succeeded') {
            $get_record = $this->db->from('bs_order')->where('order_id', $record_id)->get()->row();
            
            $items = $this->db->from('bs_items')->where_in('id', explode(',', $get_record->items))->get()->result_array();
            
            foreach ($items as $key => $value) {
                $this->db->insert('bs_order_confirm', ['order_id' => $get_record->order_id, 'item_id' => $value['id'], 'seller_id' => $value['added_user_id'], 'created_at' => date('Y-m-d H:i:s')]);
            }
        }
        
        $this->response(['status' => 'success', 'message' => 'Record save successfully']);
    }
    
    public function request_deals_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        $rules = array(
            array(
                'field' => 'user_id',
                'rules' => 'required'
            ),
            array(
                'field' => 'operation_type',
                'rules' => 'required'
            ),
        );
        if (!$this->is_valid($rules)) exit;
        
        $user_id = $this->post('user_id');
        $operation_type = $this->post('operation_type');
        $obj = $this->db->from('bs_chat_history')->where('buyer_user_id', $user_id)->get()->result();
        
        $this->ps_adapter->convert_chathistory( $obj );
        $this->custom_response( $obj );
        
    }
    
    public function wayto_delivery_post() {
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
        if (!$this->is_valid($rules)) exit;
        
        $user_id = $this->post('user_id');
        $obj = $this->db->from('bs_order')->where('user_id', $user_id)->where('status', "succeeded")->where('delivery_status', "pending")->get()->result_array();
        if(count($obj)) {
            $this->response($obj);
        } else {
            $this->error_response($this->config->item( 'record_not_found'));
        }
    }
}
