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
    
    public function checkout2_post() {
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
                'field' => 'card_id',
                'rules' => 'required'
            ),
            array(
                'field' => 'subtotal',
                'rules' => 'required'
            ),
            array(
                'field' => 'cvc',
                'rules' => 'required'
            ),
        );
        if (!$this->is_valid($rules)) exit;
        $user_id = $this->post('user_id');
        $posts_var = $this->post();
        
//        $items = [];
        if(!isset($posts_var['items']) || empty($posts_var['items']) || is_null($posts_var['items'])) { 
            $this->error_response("Please pass items");
        } else {
//            $clean_json = html_entity_decode($posts_var['items']);
//            $items = json_decode($clean_json,TRUE);
            $items = $posts_var['items'];
        }
        if(!isset($posts_var['card_id']) || empty($posts_var['card_id']) || is_null($posts_var['card_id'])) {
            $this->error_response("Please pass card id");
        }
        if(!isset($posts_var['cvc']) || empty($posts_var['cvc']) || is_null($posts_var['cvc'])) {
            $this->error_response("Please pass cvc");
        }
        $card_id = $posts_var['card_id'];
        $cvc     = $posts_var['cvc'];
        $card_details = $this->db->from('bs_card')->where('id', $card_id)->get()->row();
        $expiry_date = explode('/',$card_details->expiry_date);
        $paid_config = $this->Paid_config->get_one('pconfig1');
        $card_total_amount = 0;
        $records = [];
        
        foreach ($items as $key => $value) {
            $item_price = $value['price'];
            $new_odr_id = 'odr_'.time().$user_id;
            
            if($value['delivery_method_id'] == DELIVERY_ONLY) {
                $card_total_amount += $item_price;
                $get_item = $this->db->select('pay_shipping_by,shipping_type,shippingcarrier_id,shipping_cost_by_seller')->from('bs_items')->where('id', $value['item_id'])->get()->row();
                
                if($get_item->pay_shipping_by == '1') {
                    if($get_item->shipping_type == '1') {
                        $get_shiping_detail = $this->db->from('bs_shippingcarriers')->where('id', $get_item->shippingcarrier_id)->get()->row();

                        $item_price = $item_price + (float)$get_shiping_detail->price;
                        $card_total_amount += $item_price;
                    } else if($get_item->shipping_type == '2'){
                        $item_price = $item_price + $get_item->shipping_cost_by_seller;   
                        $card_total_amount += $item_price;
                    }
                }
                $this->db->insert('bs_order', ['order_id' => $new_odr_id,'user_id' => $user_id, 'items' => $value['item_id'], 'delivery_method' => $value['delivery_method_id'],'payment_method' => 'card', 'card_id' => $card_id, 'address_id' => $value['delivery_address'], 'total_amount' => $item_price, 'status' => 'pending', 'delivery_status' => 'pending', 'transaction' => '','created_at' => date('Y-m-d H:i:s'),'operation_type' => DIRECT_BUY]);
                $records[$key] = $this->db->insert_id();
                
            } else if($value['delivery_method_id'] == PICKUP_ONLY) {
                $this->db->insert('bs_order', ['order_id' => $new_odr_id,'user_id' => $user_id, 'items' => $value['item_id'], 'delivery_method' => $value['delivery_method_id'], 'payment_method' => 'cash', 'card_id' => 0, 'address_id' => $value['delivery_address'], 'total_amount' => $item_price, 'status' => 'success', 'delivery_status' => 'pending', 'transaction' => '','created_at' => date('Y-m-d H:i:s'),'operation_type' => DIRECT_BUY]);

            }
        }
        
        if($card_total_amount) {
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
                    'amount' => $card_total_amount * 100,
                    "currency" => trim($paid_config->currency_short_form),
                    'payment_method' => $response->id,
                    'payment_method_types' => ['card']
                ]);
                
                if (isset($response->id)) { 
                    $this->db->where_in('id', $records)->update('bs_order',['status' => 'initiate', 'transaction_id' => $response->id]);
                    
                    $item_ids = array_column($items,'item_id');
                    $seller = $this->db->select('device_token')->from('bs_items')
                            ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                            ->where_in('bs_items.id', $item_ids)->get()->result_array();
                    $tokens = array_column($seller, 'device_token');
                    send_push( [$tokens], ["message" => "New order arrived", "flag" => "new_order"] );
                    $response = $this->ps_security->clean_output( $response );
                    $this->response(['status' => "success", 'order_status' => 'success', 'intent_id' => $response->id, 'client_secret' => $response->client_secret, 'response' => $response, 'order_type' => 'card']);
                } else {
                    $this->db->where_in('id', $records)->update('bs_order',['status' => 'fail']);
                    $this->error_response(get_msg('stripe_transaction_failed'));
                }
            } catch (exception $e) {
                $this->db->where_in('id', $records)->update('bs_order',['status' => 'fail']);
                $this->error_response(get_msg('stripe_transaction_failed'));
            }
        } else {
            $this->response(['status' => "success", 'order_status' => 'success', 'intent_id' => '', 'record_id' => '', 'client_secret' => '', 'response' => (object)[], 'order_type' => 'cash']);
        }
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
                    $this->db->where('id', $record_id)->update('bs_order',['status' => 'initiate', 'transaction_id' => $response->id]);
                    $this->response(['status' => "success", 'order_status' => 'success', 'intent_id' => $response->id, 'record_id' => $new_odr_id, 'client_secret' => $response->client_secret, 'response' => $response]);
                    
                    $items = $this->db->from('bs_items')->where_in('id', $item_ids)->get()->result_array();
                    foreach ($items as $key => $value) {
                        $seller_device_token = $this->db->select('device_token')->from('core_users')->where('user_id', $value['added_user_id'])->get()->row();
                        send_push( $seller_device_token->device_token, ["message" => "New order arrived", "flag" => "new_order"] );
                    }
                } else {
                    $this->db->where('id', $record_id)->update('bs_order',['status' => 'fail']);
                    $this->error_response(get_msg('stripe_transaction_failed'));
                }
            } catch (exception $e) {
                $this->db->where('id', $record_id)->update('bs_order',['status' => 'fail']);
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
                $this->db->insert('bs_track_order', ['order_id' => $posts_var['order_id'], 'object_id' => (isset($response->object_id) ? $response->object_id: ''), 'status' => (isset($response->status) ? $response->status: ''), 'tracking_number' => (isset($response->tracking_number) ? $response->tracking_number: ''), 'tracking_url' => (isset($response->tracking_url_provider) ? $response->tracking_url_provider: ''), 'label_url' => (isset($response->label_url) ? $response->label_url: ''), 'response' => $response, 'created_at' => date('Y-m-d H:i:s')]);
                $track_number = isset($response->tracking_number) ? $response->tracking_number:'';
            /*Shippo integration End*/
        }
        if(is_null($track_number) || empty($track_number)) {
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
                $this->db->where('order_id', $posts_var['order_id'])->update('bs_order',['seller_transaction_status' => 'initiate', 'seller_transaction_id' => $response->id, 'processed_date' => date("Y-m-d H:i:s")]);
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
            array(
                'field' => 'operation_type',
                'rules' => 'required'),
        );
        if (!$this->is_valid($rules)) exit;
        
        $user_id = $this->post('user_id');
        $operation_type = $this->post('operation_type');
//        $this->db->from('bs_order')->where('user_id', $user_id)->get()->result_array();
        $orders =  $this->db->select('bs_order.*, bs_track_order.status as tracking_status, bs_track_order.tracking_url, order_user.user_name as order_user_name, order_user.user_email as order_user_email, order_user.user_phone as order_user_phone, seller.user_name as seller_user_name, seller.user_email as seller_user_email, seller.user_phone as seller_user_phone')->from('bs_order')
                ->join('core_users as order_user', 'bs_order.user_id = order_user.user_id')
                ->join('bs_items', 'bs_order.items = bs_items.id')
                ->join('core_users as seller', 'bs_items.added_user_id = seller.user_id')
                ->join('bs_chat_history', 'bs_order.items = bs_chat_history.requested_item_id', 'left')
                ->join('bs_track_order', 'bs_order.order_id = bs_track_order.order_id', 'left')
                ->where('bs_chat_history.operation_type', $operation_type)->where('bs_order.user_id', $user_id)->get()->result_array();   
                
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
        $orders = $this->db->select('bs_order.*, bs_items.title, bs_items.is_sold_out, bs_track_order.status as tracking_status, bs_track_order.tracking_url,seller.user_id as seller_id')->from('bs_order')
//                ->join('core_users as order_user', 'bs_order.user_id = order_user.user_id')
                ->join('bs_items', 'bs_order.items = bs_items.id')
                ->join('core_users as seller', 'bs_items.added_user_id = seller.user_id')
                ->join('bs_track_order', 'bs_order.order_id = bs_track_order.order_id', 'left')
                ->where('bs_order.order_id', $order_id)->get()->row_array();
        if(count($orders)) {
            $address_details = $this->Addresses->get_one( $orders['address_id'] );
            $orders['address_details'] = $address_details;
                
            $item_details = $this->Item->get_one( $orders['items'] );
            $this->ps_adapter->convert_item($item_details);
            $orders['item_details'] = $item_details;
            
            $buyer = $this->User->get_one( $orders['user_id'] );
            $this->ps_adapter->convert_user( $buyer );
            $orders['buyer'] = $buyer;
            
            $seller = $this->User->get_one( $orders['seller_id'] );
            $this->ps_adapter->convert_user( $seller );
            $orders['seller'] = $seller;
            $orders = $this->ps_security->clean_output( $orders );
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
        $this->db->where('transaction_id', $record_id)->update('bs_order',['status' => $status]);
        
        if($status == 'succeeded') {
            $get_record = $this->db->from('bs_order')->where('order_id', $record_id)->get()->row();
            
            $items = $this->db->from('bs_items')->where_in('id', explode(',', $get_record->items))->get()->result_array();
            foreach ($items as $key => $value) {
                $this->db->where('id', $value['id'])->update('bs_items', ['pieces' => $value['pieces']-1]);
                $this->db->insert('bs_order_confirm', ['order_id' => $get_record->order_id, 'item_id' => $value['id'], 'seller_id' => $value['added_user_id'], 'created_at' => date('Y-m-d H:i:s')]);
            }
        }
        /*Temporary hide code as per discussion with dipak for currently it's only for direct buy
         * $this->db->where('user_id', $get_record->user_id)->where('item_id', $get_record->items)->delete('bs_cart);
         */
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
            array(
                'field' => 'operation_type',
                'rules' => 'required'
            )
        );
        if (!$this->is_valid($rules)) exit;
        
        $user_id = $this->post('user_id');
        $operation_type = $this->post('operation_type');
        $obj = $this->db->select('bs_order.*, bs_items.item_type_id, bs_track_order.status as tracking_status, bs_track_order.tracking_url,seller.user_id as seller_id')->from('bs_order')
//                ->join('core_users as order_user', 'bs_order.user_id = order_user.user_id')
                ->join('bs_items', 'bs_order.items = bs_items.id')
                ->join('core_users as seller', 'bs_items.added_user_id = seller.user_id')
                ->join('bs_track_order', 'bs_order.order_id = bs_track_order.order_id', 'left');
                if($operation_type == SELLING) {
                    $obj = $obj->where('bs_items.added_user_id', $user_id);
                } else {
                    $obj = $obj->where(['bs_order.user_id'=> $user_id, 'bs_order.operation_type'=> $operation_type]);
                }
                $obj = $obj->get()->result();
//                ->where('bs_order.status', "succeeded")
//                ->where('bs_order.delivery_status', "pending")->get()->result();
        if(!empty($obj)) {
            $row = [];
            foreach ($obj as $key => $value) {
                $row[$key] = $value;
                
                $address_details = $this->Addresses->get_one( $value->address_id );
                $row[$key]->address_details = $address_details;
                
                $item_details = $this->Item->get_one( $value->items );
                $this->ps_adapter->convert_item($item_details);
                $row[$key]->item_details = $item_details;
                
                $buyer = $this->User->get_one( $value->user_id );
                $this->ps_adapter->convert_user( $buyer );
                $row[$key]->buyer = $buyer;
                
                $seller = $this->User->get_one( $value->seller_id );
                $this->ps_adapter->convert_user( $seller );
                $row[$key]->seller = $seller;
                
                $row[$key]->order_state = is_null($value->completed_date) ? 'in_process' : 'complete';
                
                if(!is_null($value->share_meeting_list_date)) {
                    $row[$key]->meeting_location = $this->db->from('bs_meeting')->where('order_id', $value->order_id)->get()->row()->location_list;
                } else {
                    $row[$key]->meeting_location = "";
            }
            }
            $row = $this->ps_security->clean_output( $row );
            $this->response($row);
        } else {
            $this->error_response($this->config->item( 'record_not_found'));
        }
    }
    
    public function confirm_shipment_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        $rules = array(
            array(
                'field' => 'order_id',
                'rules' => 'required'
            )
        );
        if (!$this->is_valid($rules)) exit;
        
        $posts = $this->post();
        
        $get_user = $this->db->select('user_id')->from('bs_order')->where('order_id', $posts['order_id'])->get()->row();
        $buyer = $this->db->select('device_token')->from('core_users')
                            ->where('user_id', $get_user->buyer_id)->get()->row();
        send_push( $buyer->device_token, ["message" => "Shipment confirm for the order", "flag" => "confirm_shipment"] );
        
        $this->db->where('order_id',$posts['order_id'])->update('bs_order',['delivery_status' => 'pickup','pickup_date' => date('Y-m-d H:i:s')]);
    
        $this->response(['status' => 'success', 'message' => 'Shipment confimed successfully']);
    }
    
    public function confirm_delivery_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        $rules = array(
            array(
                'field' => 'order_id',
                'rules' => 'required'
            )
        );
        if (!$this->is_valid($rules)) exit;
        
        $posts = $this->post();
        
        $this->db->where('order_id',$posts['order_id'])->update('bs_order',['delivery_status' => 'delivered','completed_date' => date('Y-m-d H:i:s')]);
    
        $this->response(['status' => 'success', 'message' => 'Order delivered']);
    }
    
    public function return_shipping_label_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        $rules = array(
            array(
                'field' => 'order_id',
                'rules' => 'required'
            )
        );
        if (!$this->is_valid($rules)) exit;
        
        $posts = $this->post();
        
        $get_label = $this->db->select('label_url')->from('bs_track_order')->where('order_id', $posts['order_id'])->get()->row();
        
        if(!empty($get_label)) {
            $this->response(['status' => 'success', 'message' => '', 'label_url' => $get_label->label_url]);
        } else {
            $this->error_response($this->config->item( 'record_not_found'));
        }
    }
    
    public function confirm_offer_post() {
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
//                'field' => 'card_id',
//                'rules' => 'required'
//            ),
            array(
                'field' => 'offer_id',
                'rules' => 'required'
            ),
//            array(
//                'field' => 'cvc',
//                'rules' => 'required'
//            ),
//            array(
//                'field' => 'delivery_method_id',
//                'rules' => 'required'
//            ),
//            array(
//                'field' => 'price',
//                'rules' => 'required'
//            ),
//            array(
//                'field' => 'item_id',
//                'rules' => 'required'
//            ),
//            array(
//                'field' => 'delivery_address',
//                'rules' => 'required'
//            ),
//            array(
//                'field' => 'operation_type',
//                'rules' => 'required'
//            ),
        );
        if (!$this->is_valid($rules)) exit;
        $posts_var = $this->post();
        
        $offer_details = $this->db->from('bs_chat_history')->where('id', $posts_var['offer_id'])->get()->row();
        
        if(!$offer_details->is_offer_complete) {
            if($offer_details->seller_user_id != $posts_var['user_id']) {
                if(!isset($posts_var['card_id']) || empty($posts_var['card_id']) || is_null($posts_var['card_id'])) {
                    $this->error_response("Please pass card id");
                }
                if(!isset($posts_var['cvc']) || empty($posts_var['cvc']) || is_null($posts_var['cvc'])) {
                    $this->error_response("Please pass cvc");
                }
                if(!isset($posts_var['delivery_method_id']) || empty($posts_var['delivery_method_id']) || is_null($posts_var['delivery_method_id'])) {
                    $this->error_response("Please pass delivery_method_id");
                }
                if(!isset($posts_var['price']) || empty($posts_var['price']) || is_null($posts_var['price'])) {
                    $this->error_response("Please pass price");
                }
                if($posts_var['operation_type'] != EXCHANGE) {
                if(!isset($posts_var['item_id']) || empty($posts_var['item_id']) || is_null($posts_var['item_id'])) {
                    $this->error_response("Please pass item_id");
                }
                }
                if(!isset($posts_var['delivery_address']) || empty($posts_var['delivery_address']) || is_null($posts_var['delivery_address'])) {
                    $this->error_response("Please pass delivery_address");
                }
                if(!isset($posts_var['operation_type']) || empty($posts_var['operation_type']) || is_null($posts_var['operation_type'])) {
                    $this->error_response("Please pass operation_type");
                }
                if($posts_var['operation_type'] == DIRECT_BUY) {
                    if(!isset($posts_var['qty']) || empty($posts_var['qty']) || is_null($posts_var['qty'])) {
                        $this->error_response("Please pass qty");
                    }
                }
                $card_id = $posts_var['card_id'];
                $cvc     = $posts_var['cvc'];
                $card_details = $this->db->from('bs_card')->where('id', $card_id)->get()->row();
                $expiry_date = explode('/',$card_details->expiry_date);
                $paid_config = $this->Paid_config->get_one('pconfig1');
                $item_price = $posts_var['price'];
                $new_odr_id = 'odr_'.time().$posts_var['user_id'];
                if($posts_var['delivery_method_id'] == DELIVERY_ONLY) {
                    $get_item = $this->db->select('pay_shipping_by,shipping_type,shippingcarrier_id,shipping_cost_by_seller')->from('bs_items')->where('id', $posts_var['item_id'])->get()->row();

                    if($get_item->pay_shipping_by == '1') {
                        if($get_item->shipping_type == '1') {
                            $get_shiping_detail = $this->db->from('bs_shippingcarriers')->where('id', $get_item->shippingcarrier_id)->get()->row();

                            $item_price = $item_price + (float)$get_shiping_detail->price;
                        } else if($get_item->shipping_type == '2'){
                            $item_price = $item_price + $get_item->shipping_cost_by_seller;   
                        }
                    }
                    $this->db->insert('bs_order', ['order_id' => $new_odr_id, 'offer_id' => $posts_var['offer_id'],'user_id' => $posts_var['user_id'], 'items' => $posts_var['item_id'], 'qty' => ($posts_var['qty'] ?? ''), 'delivery_method' => $posts_var['delivery_method_id'],'payment_method' => 'card', 'card_id' => $card_id, 'address_id' => $posts_var['delivery_address'], 'total_amount' => $item_price, 'status' => 'pending', 'confirm_by_seller'=>1, 'delivery_status' => 'pending', 'transaction' => '','created_at' => date('Y-m-d H:i:s'),'operation_type' => $posts_var['operation_type']]);
                    $record = $this->db->insert_id();

                    # set stripe test key
                    \Stripe\Stripe::setApiKey(trim($paid_config->stripe_secret_key));
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

                        if (isset($response->id)) { 
                            $this->db->where('id', $record)->update('bs_order',['status' => 'initiate', 'transaction_id' => $response->id]);

                            $seller = $this->db->select('device_token')->from('bs_items')
                                    ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                                    ->where('bs_items.id', $posts_var['item_id'])->get()->row();
                            send_push( $seller->device_token, ["message" => "New order arrived", "flag" => "new_order"] );
                            $this->db->where('id',$posts_var['offer_id'])->update('bs_chat_history',['is_offer_complete' => 1,'order_id' => $record]);
                            $response = $this->ps_security->clean_output( $response );
                            $this->response(['status' => "success", 'order_status' => 'success', 'intent_id' => $response->id, 'client_secret' => $response->client_secret, 'response' => $response, 'order_type' => 'card', 'order_id' => $new_odr_id]);
                        } else {
                            $this->db->where('id', $record)->update('bs_order',['status' => 'fail']);
                            $this->error_response(get_msg('stripe_transaction_failed'));
                        }
                    } catch (exception $e) {
                        $this->db->where('id', $record)->update('bs_order',['status' => 'fail']);
                        $this->error_response(get_msg('stripe_transaction_failed'));
                    }

                } else if($posts_var['delivery_method_id'] == PICKUP_ONLY) {
                    $this->db->insert('bs_order', ['order_id' => $new_odr_id, 'offer_id' => $posts_var['offer_id'],'user_id' => $posts_var['user_id'], 'items' => ($posts_var['item_id'] ?? ''), 'delivery_method' => $posts_var['delivery_method_id'], 'payment_method' => 'cash', 'card_id' => 0, 'address_id' => $posts_var['delivery_address'], 'total_amount' => $item_price, 'status' => 'success', 'confirm_by_seller'=>1,'delivery_status' => 'pending', 'transaction' => '','created_at' => date('Y-m-d H:i:s'),'operation_type' => $posts_var['operation_type']]);
                    $record = $this->db->insert_id();

                    $this->db->where('id',$posts_var['offer_id'])->update('bs_chat_history',['is_offer_complete' => 1,'order_id' => $record]);
                    
                    

                    $this->response(['status' => "success", 'order_status' => 'success', 'intent_id' => '', 'client_secret' => '', 'response' => (object)[], 'order_type' => 'cash', 'order_id' => $new_odr_id]);
                }
                } else {
                    $buyer = $this->db->select('device_token')->from('core_users')
                            ->where('core_users.user_id', $offer_details->buyer_user_id)->get()->row();
                    send_push( $buyer->device_token, ["message" => "Offer confirmed", "flag" => "offer_confirmed_by_seller"] );
            }
        } else {
            $this->error_response("Offer already completed");
        }
    }


    /**
     * Himanshu Sharma
     * Order/Payment receipt
     */
    public function order_receipt_get(){
        $user_data = $this->_apiConfig([
            'methods' => ['GET'],
            'requireAuthorization' => true,
        ]);
        $rules = array(
            array(
                'field' => 'order_id',
                'rules' => 'required'
            )
        );
        if (!$this->is_valid($rules)) exit; 
        $order_id = $this->get('order_id');
        //$orderData = $this->Order->get_one_by(array('order_id' => $order_id));
        $orderData = $this->db->query("SELECT * FROM `bs_order` WHERE order_id = '".$order_id."'")->result();
        $this->ps_adapter->convert_order($orderData);
        $this->custom_response($orderData);
    }
}
