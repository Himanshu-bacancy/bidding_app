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
        $orderids= [];
        $backend_config = $this->Backend_config->get_one('be1');
        foreach ($items as $key => $value) {
            
            $item_price = $value['price'];
            $new_odr_id = 'odr_'.time().$user_id;
            $orderids[$value['item_id']] = $new_odr_id;
            $shipping_amount = 0;
            if($value['delivery_method_id'] == DELIVERY_ONLY) {
                $card_total_amount += $item_price;
                $get_item = $this->db->select('pay_shipping_by,shipping_type,shippingcarrier_id,shipping_cost_by_seller')->from('bs_items')->where('id', $value['item_id'])->get()->row();
                if($get_item->pay_shipping_by == '1') {
                    if($get_item->shipping_type == '1') {
                        $get_shiping_detail = $this->db->from('bs_shippingcarriers')->where('id', $get_item->shippingcarrier_id)->get()->row();

                        $item_price = $item_price + (float)$get_shiping_detail->price;
                        $shipping_amount = $get_shiping_detail->price;
                        $card_total_amount += (float)$get_shiping_detail->price;
                    } else if($get_item->shipping_type == '2'){
                        $item_price = $item_price + $get_item->shipping_cost_by_seller;   
                        $shipping_amount = $get_item->shipping_cost_by_seller;
                        $card_total_amount += $get_item->shipping_cost_by_seller;
                    }
                }
                $service_fee = ((float)$value['price']*(float)$backend_config->selling_fees)/100;

                $processing_fees = ((float)$value['price']*(float)$backend_config->processing_fees)/100;

                $seller_earn = (float)$value['price'] - $service_fee - $processing_fees;
                
                $this->db->insert('bs_order', ['order_id' => $new_odr_id,'user_id' => $user_id, 'items' => $value['item_id'], 'delivery_method' => $value['delivery_method_id'],'payment_method' => 'card', 'card_id' => $card_id, 'address_id' => $value['delivery_address'], 'item_offered_price' => $value['price'], 'service_fee' => $service_fee, 'processing_fee' => $processing_fees, 'seller_earn' => $seller_earn, 'shipping_amount' => $shipping_amount, 'total_amount' => $item_price, 'status' => 'pending', 'delivery_status' => 'pending', 'transaction' => '','created_at' => date('Y-m-d H:i:s'),'operation_type' => DIRECT_BUY]);
                $records[$key] = $this->db->insert_id();
                
                if(!$item_price) {
                     /*manage qty: start*/
                    $item_detail = $this->db->from('bs_items')->where('id', $value['item_id'])->get()->row();

                    $stock_update = $item_detail->pieces - 1;
                    $update_array['pieces'] = $stock_update;
                    if(!$stock_update) {
                        $update_array['is_sold_out'] = 1;
                    }

                    $this->db->where('id', $value['item_id'])->update('bs_items', $update_array);
                    $this->db->where('user_id', $user_id)->where('item_id', $value['item_id'])->delete('bs_cart');
                    /*manage qty: end*/
                    
                }
                
            } else if($value['delivery_method_id'] == PICKUP_ONLY) {
                $service_fee = ((float)$item_price * (float)$backend_config->selling_fees)/100;

                $processing_fees = ((float)$item_price * (float)$backend_config->processing_fees)/100;

                $seller_earn = (float)$item_price - $service_fee - $processing_fees;
                
                $this->db->insert('bs_order', ['order_id' => $new_odr_id,'user_id' => $user_id, 'items' => $value['item_id'], 'delivery_method' => $value['delivery_method_id'], 'payment_method' => 'cash', 'card_id' => 0, 'address_id' => $value['delivery_address'], 'item_offered_price' => $item_price, 'service_fee' => $service_fee, 'processing_fee' => $processing_fees, 'seller_earn' => $seller_earn, 'shipping_amount' => $shipping_amount, 'total_amount' => $item_price, 'status' => 'success', 'delivery_status' => 'pending', 'transaction' => '','created_at' => date('Y-m-d H:i:s'),'operation_type' => DIRECT_BUY]);

                if($value['payin'] == PAYCARD) {
                    $records[$key] = $this->db->insert_id();
                    $card_total_amount += $item_price;
                }
                
                /*manage qty: start*/
                $item_detail = $this->db->from('bs_items')->where('id', $value['item_id'])->get()->row();
                
                $stock_update = $item_detail->pieces - 1;
                $update_array['pieces'] = $stock_update;
                if(!$stock_update) {
                    $update_array['is_sold_out'] = 1;
                }
                
                $this->db->where('id', $value['item_id'])->update('bs_items', $update_array);
                $this->db->where('user_id', $user_id)->where('item_id', $value['item_id'])->delete('bs_cart');
                /*manage qty: end*/
                
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
                    'payment_method_types' => ['card'],
                    'confirm' => true
                ]);
                
                if (isset($response->id)) { 
                    if($response->status == 'requires_action') {
                        $this->error_response('Transaction requires authorization');
                    }
                    $this->db->where_in('id', $records)->update('bs_order',['status' => $response->status, 'transaction_id' => $response->id]);
                    $this->tracking_order(['transaction_id' => $response->id, 'create_offer' => 1]);
                    $item_ids = array_column($items,'item_id');
                    
                    foreach ($item_ids as $key => $value) {
                        $item_images = $this->db->select('img_path')->from('core_images')->where('img_type', 'item')->where('img_parent_id', $value)->get()->row();
                        
                        $seller = $this->db->select('device_token,bs_items.id as item_id,bs_items.title as item_name')->from('bs_items')
                            ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                            ->where('bs_items.id', $value)->get()->row_array();
                        
                        send_push( [$seller['device_token']], ["message" => "New order placed", "flag" => "order",'title' =>$seller['item_name']], ['image' => 'http://bacancy.com/biddingapp/uploads/'.$item_images->img_path, 'order_id' => $orderids[$value]] );
                    }
                    
//                    $seller = $this->db->select('device_token,bs_items.id as item_id,bs_items.title as item_name')->from('bs_items')
//                            ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
//                            ->where_in('bs_items.id', $item_ids)->get()->result_array();
//                    $tokens = array_column($seller, 'device_token');
//                    foreach ($seller as $key => $value) {
//                        $item_images = $this->db->select('img_path')->from('core_images')->where('img_type', 'item')->where('img_parent_id', $value['item_id'])->get()->row();
//                        
//                        send_push( [$value['device_token']], ["message" => "New order placed", "flag" => "order",'title' =>$value['item_name']], ['image' => 'http://bacancy.com/biddingapp/uploads/'.$item_images->img_path] );
//                    }
                    
//                    send_push( [$tokens], ["message" => "New order arrived", "flag" => "order", 'order_ids' => implode(',', $records)] );
                    $response = $this->ps_security->clean_output( $response );
                    $this->response(['status' => "success", 'order_status' => 'success', 'order_type' => 'card']);
                } else {
                    $this->db->where_in('id', $records)->update('bs_order',['status' => 'fail']);
                    $this->error_response(get_msg('stripe_transaction_failed'));
                }
            } catch (exception $e) {
                $this->db->where_in('id', $records)->update('bs_order',['status' => 'fail']);
                $this->error_response(get_msg('stripe_transaction_failed'));
            }
        } else {
            $item_ids = array_column($items,'item_id');
//            $seller = $this->db->select('device_token,bs_items.id as item_id,bs_items.title as item_name')->from('bs_items')
//                    ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
//                    ->where_in('bs_items.id', $item_ids)->get()->result_array();
//            $tokens = array_column($seller, 'device_token');
//
//            foreach ($seller as $key => $value) {
//                $item_images = $this->db->select('img_path')->from('core_images')->where('img_type', 'item')->where('img_parent_id', $value['item_id'])->get()->row();
//
//                send_push( [$value['device_token']], ["message" => "New order placed", "flag" => "order",'title' =>$value['item_name']], ['image' => 'http://bacancy.com/biddingapp/uploads/'.$item_images->img_path] );
//            }
            
            foreach ($item_ids as $key => $value) {
                $item_images = $this->db->select('img_path')->from('core_images')->where('img_type', 'item')->where('img_parent_id', $value)->get()->row();

                $seller = $this->db->select('device_token,bs_items.id as item_id,bs_items.title as item_name')->from('bs_items')
                    ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                    ->where('bs_items.id', $value)->get()->row_array();

                send_push( [$seller['device_token']], ["message" => "New order placed", "flag" => "order",'title' =>$seller['item_name']], ['image' => 'http://bacancy.com/biddingapp/uploads/'.$item_images->img_path, 'order_id' => $orderids[$value]] );
            }
                    
            $this->response(['status' => "success", 'order_status' => 'success', 'order_type' => 'cash']);
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
        $shipping_amount = 0;
        $backend_config = $this->Backend_config->get_one('be1');

        $service_fee = ((float)$this->post('total_amount') * (float)$backend_config->selling_fees)/100;

        $processing_fees = ((float)$this->post('total_amount') * (float)$backend_config->processing_fees)/100;

        $seller_earn = (float)$this->post('total_amount') - $service_fee - $processing_fees;
        
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
                    $shipping_amount = $get_shiping_detail->price;
                } else if($get_item->shipping_type == '2'){
                    $item_price = $item_price + $get_item->shipping_cost_by_seller;   
                    $shipping_amount = $get_item->shipping_cost_by_seller;
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
                $this->db->insert('bs_order', ['order_id' => $new_odr_id,'user_id' => $user_id, 'items' => $item_ids, 'delivery_method' => $delivery_method,'payment_method' => 'card', 'card_id' => $card_id, 'address_id' => $address_id, 'item_offered_price' => $this->post('total_amount'), 'service_fee' => $service_fee, 'processing_fee' => $processing_fees, 'seller_earn' => $seller_earn, 'shipping_amount' => $shipping_amount, 'total_amount' => $total_amount, 'status' => 'pending', 'delivery_status' => 'pending', 'transaction' => $response,'created_at' => date('Y-m-d H:i:s')]);
                $record_id = $this->db->insert_id();
                if (isset($response->id)) { 
                    $this->db->where('id', $record_id)->update('bs_order',['status' => 'initiate', 'transaction_id' => $response->id]);
                    $this->response(['status' => "success", 'order_status' => 'success', 'intent_id' => $response->id, 'record_id' => $new_odr_id, 'client_secret' => $response->client_secret, 'response' => $response]);
                    
                    $items = $this->db->from('bs_items')->where_in('id', $item_ids)->get()->result_array();
                    foreach ($items as $key => $value) {
                        $seller_device_token = $this->db->select('device_token')->from('core_users')->where('user_id', $value['added_user_id'])->get()->row();
                        send_push( [$seller_device_token->device_token], ["message" => "New order arrived", "flag" => "order"],['order_id' => $record_id] );
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
            $this->db->insert('bs_order', ['user_id' => $user_id, 'items' => $item_ids, 'delivery_method' => $delivery_method, 'payment_method' => 'cash', 'card_id' => 0, 'address_id' => $address_id, 'item_offered_price' => $total_amount, 'service_fee' => $service_fee, 'processing_fee' => $processing_fees, 'seller_earn' => $seller_earn, 'shipping_amount' => $shipping_amount, 'total_amount' => $total_amount, 'status' => 'success', 'delivery_status' => 'pending', 'transaction' => '','created_at' => date('Y-m-d H:i:s')]);
            
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
//                    ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id')
                    ->join('bs_addresses', 'bs_order.address_id = bs_addresses.id')
                    ->where('order_id', $posts_var['order_id'])->get()->row();
            
            $seller_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode')->from('bs_order')
                    ->join('bs_items', 'bs_order.items = bs_items.id')
                    ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
//                    ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id')
                    ->join('bs_addresses', 'bs_order.address_id = bs_addresses.id')
                    ->where('order_id', $posts_var['order_id'])->get()->row();
            
           
            /*Shippo integration Start*/
            $headers = array(
                "Content-Type: application/json",
                "Authorization: ShippoToken ".SHIPPO_AUTH_TOKEN  // place your shippo private token here
                                  );

            $url = 'https://api.goshippo.com/transactions/';

            $address_from = array(
                "name"=> $seller_detail->user_name,
                "street1"=> $seller_detail->address1,
                "city"=> $seller_detail->city,
                "state"=> $seller_detail->state,
                "zip" => $seller_detail->zipcode,
                "country" => $seller_detail->country,
                "phone" => $seller_detail->user_phone,
                "email" => $seller_detail->user_email
                          );    

            $address_to = array(
                "name"=> $buyer_detail->user_name,
                "street1"=> $buyer_detail->address1,
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
                    "carrier_account"=> $shippingcarriers_details->shippo_object_id,
                    "servicelevel_token"=> "usps_priority"
                            );                   
//                echo '<pre>';print_r($shipmentdata);die();
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($shipmentdata));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                $response = json_decode(curl_exec($ch)); 
                curl_close($ch);	
//                $response = json_decode($response);
//                echo '<pre>';
//                echo $response->object_id.'<br>';
//                print_r($response);die();
                $this->db->insert('bs_track_order', ['order_id' => $posts_var['order_id'], 'object_id' => (isset($response->object_id) ? $response->object_id: ''), 'status' => (isset($response->status) ? $response->status: 'ERROR'), 'tracking_number' => (isset($response->tracking_number) ? $response->tracking_number: ''), 'tracking_url' => (isset($response->tracking_url_provider) ? $response->tracking_url_provider: ''), 'label_url' => (isset($response->label_url) ? $response->label_url: ''), 'response' => json_encode($response), 'created_at' => date('Y-m-d H:i:s')]);
                $track_number = isset($response->tracking_number) ? $response->tracking_number:'';
            /*Shippo integration End*/
        }
        if(is_null($track_number) || empty($track_number)) {
//            $this->error_response("Something wrong with shipping provided detail");
            $this->response(['status' => 'error', 'message' => 'Something wrong with shipping provided detail', 'response' => $response],404);
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
                'payment_method_types' => ['card'],
                'confirm' => true
            ]);
            $this->db->where('order_id', $posts_var['order_id'])->update('bs_order', ['seller_charge' => $amount,'seller_transaction' => $response]);
            if (isset($response->id)) { 
                if($response->status == 'requires_action') {
                    $this->error_response('Transaction requires authorization');
                }
                $this->db->where('order_id', $posts_var['order_id'])->update('bs_order',['seller_transaction_status' => $response->status, 'seller_transaction_id' => $response->id, 'processed_date' => date("Y-m-d H:i:s")]);
                $this->response(['status' => "success", 'track_number' => $track_number]);
            } else {
                $this->db->where('order_id', $record_id)->update('bs_order',['seller_transaction_status' => 'fail']);
                $this->db->insert('bs_stripe_error', ['order_id' => $record_id, 'card_id' => $card_id, 'response' => $response, 'created_at' => date('Y-m-d H:i:s')]);
                $this->error_response(get_msg('stripe_transaction_failed'));
            }
        } catch (exception $e) {
            $this->db->where('order_id', $record_id)->update('bs_order',['seller_transaction_status' => 'fail']);
            $this->db->insert('bs_stripe_error', ['order_id' => $record_id, 'card_id' => $card_id, 'response' => $e->getMessage(), 'created_at' => date('Y-m-d H:i:s')]);
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
        $orders =  $this->db->select('bs_order.*, bs_track_order.status as tracking_status, bs_track_order.tracking_url, bs_track_order.label_url, order_user.user_name as order_user_name, order_user.user_email as order_user_email, order_user.user_phone as order_user_phone, seller.user_name as seller_user_name, seller.user_email as seller_user_email, seller.user_phone as seller_user_phone')->from('bs_order')
                ->join('core_users as order_user', 'bs_order.user_id = order_user.user_id')
                ->join('bs_items', 'bs_order.items = bs_items.id')
                ->join('core_users as seller', 'bs_items.added_user_id = seller.user_id')
                ->join('bs_chat_history', 'bs_order.items = bs_chat_history.requested_item_id', 'left')
                ->join('bs_track_order', 'bs_order.order_id = bs_track_order.order_id', 'left')
                ->where('bs_chat_history.operation_type', $operation_type)
                ->where('bs_order.user_id', $user_id);
                if($operation_type == DIRECT_BUY) {
                    $orders = $orders->where('bs_chat_history.is_cart_offer', 0);
                }
                $orders = $orders->get()->result_array();   
        
        if(!empty($orders) && count($orders)) {
            $rowdetails = [];
            $row = [];
            foreach ($orders as $key => $value) {
                $row[$key] = $value;
                $item_details = $this->Item->get_one( $value['items'] );
                $this->ps_adapter->convert_item($item_details);
                $row[$key]['requested_item_detail'] = retreive_custom_data($item_details,['id', 'cat_id', 'sub_cat_id', 'item_type_id', 'condition_of_item_id', 'description', 'price', 'title', 'status', 'added_date', 'added_user_id', 'default_photo']);
                if($operation_type == EXCHANGE) {
                    if(!is_null($value[offer_id])) {
                        $offered_item_details = $this->db->select('offered_item_id,who_pay')->from('bs_exchange_chat_history')->where('bs_exchange_chat_history.chat_id', $value['offer_id'])->get()->result();
                        foreach ($offered_item_details as $k => $v) { 
                            $rowdetails[$k] = $this->Item->get_one( $v->offered_item_id );
                            $this->ps_adapter->convert_item($rowdetails[$k]);
                            $who_pay = $v->who_pay;
                            $rowdetails[$k] = retreive_custom_data($rowdetails[$k],['id', 'cat_id', 'sub_cat_id', 'item_type_id', 'condition_of_item_id', 'description', 'price', 'title', 'status', 'added_date', 'added_user_id', 'default_photo']);
                        }
                    }
                    $row[$key]['who_pay'] = $who_pay;
                    $row[$key]['exchange_item_detail'] = $rowdetails;
                } 
            }
            $this->response($row);
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
        $orders = $this->db->select('bs_order.*, bs_items.title, bs_items.is_sold_out, bs_track_order.status as tracking_status, bs_track_order.tracking_url, bs_track_order.label_url, seller.user_id as seller_id')->from('bs_order')
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
            
            if(!empty($item_details->packagesize_id)) {
                $package_details = $this->Packagesizes->get_one( $item_details->packagesize_id );
                $this->ps_adapter->convert_packagesize( $package_details );
                $item_details->package_details = $package_details;
            } else {
                $item_details->package_details = (object)[];
            }
            
            if(!empty($item_details->shippingcarrier_id)) {
                $shipping_details = $this->Shippingcarriers->get_one( $item_details->shippingcarrier_id );
                $this->ps_adapter->convert_shippingcarrier( $shipping_details );
                $item_details->shipping_details = $shipping_details;
            } else {
                $item_details->shipping_details = (object)[];
            }
                
            $orders['item_details'] = $item_details;
            $orders['requested_item_detail'] = retreive_custom_data($item_details,['id', 'cat_id', 'sub_cat_id', 'item_type_id', 'condition_of_item_id', 'description', 'price', 'title', 'status', 'added_date', 'added_user_id', 'default_photo']);
            
            $buyer = $this->User->get_one( $orders['user_id'] );
            $this->ps_adapter->convert_user( $buyer );
            $orders['buyer'] = $buyer;
           
            $seller = $this->User->get_one( $orders['seller_id'] );
            $this->ps_adapter->convert_user( $seller );
            $orders['seller'] = $seller;
            
            $orders['order_state'] = is_null($orders['completed_date']) ? 'in_process' : 'complete';
                
            if(!is_null($orders['share_meeting_list_date'])) {
                $orders['meeting_location'] = json_decode($this->db->from('bs_meeting')->where('order_id', $orders['order_id'])->get()->row()->location_list, true);
            } else {
                $orders['meeting_location'] = ""; 
            }
            
            if(!is_null($orders['confirm_meeting_date'])) {
                $orders['confirm_location'] = json_decode($this->db->from('bs_meeting')->where('order_id', $orders['order_id'])->get()->row()->confirm_location, true);
            } else {
                $orders['confirm_location'] = "";
            }
//            $orders['service_fee'] = "0";
//            $orders['processing_fee'] = "0";
//            $orders['you_earn'] = "0";
            $orders['tax_charged_to_buyer'] = "0";
            
            if($orders['is_dispute']) {
                $dispute_details = $this->db->from('bs_dispute')->where('order_id', $order_id)->get()->row();
                $orders['dispute_details'] = $dispute_details;
            } else {
                $orders['dispute_details'] = (object)[];
            }
            if($orders['is_return']) {
                $created_at = date_create($orders['created_at']);
                $date = new DateTime("now");
                $date2 = date_create($date->format('Y-m-d H:i:s'));
                $diff = date_diff($created_at, $date2)->format("%a");

                $orders['is_return_expire'] = 0;
                if($diff > 3) {
                    $orders['is_return_expire'] = 1;
                } 
                $return_details = $this->db->select('bs_return_order.id,bs_return_order.order_id,bs_reasons.name as reason_name,bs_return_order.description,bs_return_order.status,bs_return_order.created_at')->from('bs_return_order')
                        ->join('bs_reasons', "bs_return_order.reason_id = bs_reasons.id")
                        ->where('order_id', $order_id)
                        ->get()->row();
                $return_details->images = $this->Image->get_all_by( array( 'img_parent_id' => $order_id, 'img_type' => 'return_order' ))->result();
                if(is_null($return_details->updated_at)) {
                    $created_at = date_create($return_details->created_at);
                    $date = new DateTime("now");
                    $diff = date_diff($created_at, $date2)->format("%a");
                    if($diff > 1) { 
                        $orders['is_return_expire'] = 1;
                    }
                }
                if($return_details->status == "accept") {
                    $return_trackin_details = $this->db->from('bs_track_order')->where('order_id',$order_id)->where('is_return', 1)->order_by('id','desc')->get()->row();

                    $return_details->tracking_status = $return_trackin_details->status;
                    $return_details->tracking_url = $return_trackin_details->tracking_url;
                }
                
                $orders['return_details'] = $return_details;
            } else {
                $orders['return_details'] = (object)[];
            }
            
            if($orders['operation_type'] == EXCHANGE) {
                $offered_item_details = $this->db->select('offered_item_id,who_pay')->from('bs_exchange_chat_history')->where('bs_exchange_chat_history.chat_id', $orders['offer_id'])->get()->result();
                
                foreach ($offered_item_details as $key => $value) {
                    $row_item_details[$key] = $this->Item->get_one( $value->offered_item_id );
                    $this->ps_adapter->convert_item($row_item_details[$key]);
            
                    if(!empty($row_item_details[$key]->packagesize_id)) {
                        $package_details = $this->Packagesizes->get_one( $row_item_details[$key]->packagesize_id );
                        $this->ps_adapter->convert_packagesize( $package_details );
                        $row_item_details[$key]->package_details = $package_details;
                    } else {
                        $row_item_details[$key]->package_details = (object)[];
                    }
            
                    if(!empty($row_item_details[$key]->shippingcarrier_id)) {
                        $shipping_details = $this->Shippingcarriers->get_one( $row_item_details[$key]->shippingcarrier_id );
                        $this->ps_adapter->convert_shippingcarrier( $shipping_details );
                        $row_item_details[$key]->shipping_details = $shipping_details;
                    } else {
                        $row_item_details[$key]->shipping_details = (object)[];
                    }
                    $who_pay = $value->who_pay;
                    $exchange_item_detail[$key] = retreive_custom_data($row_item_details[$key],['id', 'cat_id', 'sub_cat_id', 'item_type_id', 'condition_of_item_id', 'description', 'price', 'title', 'status', 'added_date', 'added_user_id', 'default_photo']);
                }
                $orders['offered_item_detail'] = $row_item_details;
                $orders['who_pay'] = $who_pay;
                $orders['exchange_item_detail'] = $exchange_item_detail;
                
            } else {
                $orders['offered_item_detail'] = (object)[];
            }
            $seller_transaction = str_replace('Stripe\\PaymentIntent JSON: ', '', $orders['seller_transaction']);
            $orders['seller_transaction'] = json_decode($seller_transaction);
//            $orders = $this->ps_security->clean_output( $orders );
            $this->custom_response((object) $orders);
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
        $get_records = $this->db->from('bs_order')->where('transaction_id', $record_id)->get()->result();
        foreach ($get_records as $key => $value) {
            $track_exist = $this->db->from('bs_track_order')->where('order_id', $value->order_id)->order_by('id','desc')->get()->row();
            if(empty($track_exist) || $track_exist->status == 'ERROR') {
                $get_item = $this->db->from('bs_items')->where('id', $value->items)->get()->row();
                if($get_item->pay_shipping_by == '1') {
                    if($get_item->shipping_type == '1') { 
                        $shippingcarriers_details = $this->db->from('bs_shippingcarriers')->where('id', $get_item->shippingcarrier_id)->get()->row();
                        $package_details = $this->db->from('bs_packagesizes')->where('id', $get_item->packagesize_id)->get()->row();
                        $buyer_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode')->from('bs_order')
                            ->join('core_users', 'bs_order.user_id = core_users.user_id')
//                            ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id')
                            ->join('bs_addresses', 'bs_order.address_id = bs_addresses.id')
                            ->where('order_id', $value->order_id)->get()->row();
                        
                        $seller_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode')->from('bs_order')
                        ->join('bs_items', 'bs_order.items = bs_items.id')
                        ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
//                        ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id')
                        ->join('bs_addresses', 'bs_order.address_id = bs_addresses.id')
                        ->where('order_id', $value->order_id)->get()->row();
                        
                        $headers = array(
                            "Content-Type: application/json",
                            "Authorization: ShippoToken ".SHIPPO_AUTH_TOKEN  // place your shippo private token here
                                              );
                        $url = 'https://api.goshippo.com/transactions/';
                        $address_from = array(
                            "name"=> $seller_detail->user_name,
                            "street1"=> $seller_detail->address1,
                            "city"=> $seller_detail->city,
                            "state"=> $seller_detail->state,
                            "zip" => $seller_detail->zipcode,
                            "country" => $seller_detail->country,
                            "phone" => $seller_detail->user_phone,
                            "email" => $seller_detail->user_email
                                      );
                        $address_to = array(
                            "name"=> $buyer_detail->user_name,
                            "street1"=> $buyer_detail->address1,
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
                                "carrier_account"=> $shippingcarriers_details->shippo_object_id,
                                "servicelevel_token"=> "usps_priority"
                                        );
                         $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $url);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($shipmentdata));
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                            $response = json_decode(curl_exec($ch)); 
                            curl_close($ch);
                        $this->db->insert('bs_track_order', ['order_id' => $value->order_id, 'object_id' => (isset($response->object_id) ? $response->object_id: ''), 'status' => (isset($response->status) ? $response->status: 'ERROR'), 'tracking_number' => (isset($response->tracking_number) ? $response->tracking_number: ''), 'tracking_url' => (isset($response->tracking_url_provider) ? $response->tracking_url_provider: ''), 'label_url' => (isset($response->label_url) ? $response->label_url: ''), 'response' => json_encode($response), 'created_at' => date('Y-m-d H:i:s')]);
                        $track_number = isset($response->tracking_number) ? $response->tracking_number:'';
                        
//                        if(is_null($track_number) || empty($track_number)) {
//                //            $this->error_response("Something wrong with shipping provided detail");
//                            $this->response(['status' => 'error', 'message' => 'Something wrong with shipping provided detail', 'response' => $response],404);
//                        }
                    }
                }
            }
        }
        
        $str = 'failed';
        if($status) {
            $current_date = date("Y-m-d H:i:s");
            foreach ($get_records as $key => $value) {
                $create_offer['requested_item_id'] = $value->items;
                $create_offer['buyer_user_id'] = $value->user_id;
                
                $item_detail = $this->db->from('bs_items')->where('id', $value->items)->get()->row();
                $create_offer['seller_user_id'] = $item_detail->added_user_id;
                $create_offer['nego_price'] = $value->item_offered_price;
                
                $create_offer['type'] = 'to_seller';
                $create_offer['operation_type'] = DIRECT_BUY;
                $create_offer['quantity'] = $value->qty;
                $create_offer['added_date'] = $current_date;
                $create_offer['is_offer_complete'] = 1;
                $create_offer['order_id'] = $value->order_id;
                $create_offer['is_cart_offer'] = 1;
                $this->Chat->save($create_offer);	
                $obj = $this->Chat->get_one_by($create_offer);
                
                $update_order['offer_id'] = $obj->id;
                if(!is_null($track_number) && !empty($track_number)) {
                    $update_order['processed_date'] = $current_date;
                }
                $this->db->where('order_id', $value->order_id)->update('bs_order',$update_order);
            }
            $str = 'succeeded';
        }
        $this->db->where('transaction_id', $record_id)->update('bs_order',['status' => $str]);
        
        if($status) {
            foreach ($get_records as $key => $value) {
                $item_detail = $this->db->from('bs_items')->where('id', $value->items)->get()->row();
                if($value->operation_type == DIRECT_BUY) {
                    $stock_update = $item_detail->pieces - $value->qty;
                } else {
                    $stock_update = $item_detail->pieces - 1;
                }
                
                $update_array['pieces'] = $stock_update;
                if(!$stock_update) {
                    $update_array['is_sold_out'] = 1;
                }
                $this->db->where('id', $value->items)->update('bs_items', $update_array);
                $this->db->insert('bs_order_confirm', ['order_id' => $value->order_id, 'item_id' => $value->items, 'seller_id' => $item_detail->added_user_id, 'created_at' => date('Y-m-d H:i:s')]);
                
                $this->db->where('user_id', $value->user_id)->where('item_id', $value->items)->delete('bs_cart');
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
            array(
                'field' => 'operation_type',
                'rules' => 'required'
            ),
            array(
                'field' => 'order_state',
                'rules' => 'required'
            )
        );
        if (!$this->is_valid($rules)) exit;
        
        $user_id = $this->post('user_id');
        $order_state = $this->post('order_state');
        $operation_type = $this->post('operation_type');
        $obj = $this->db->select('bs_order.*,seller.user_id as seller_id')->from('bs_order')
                ->join('core_users as order_user', 'bs_order.user_id = order_user.user_id')
                ->join('bs_items', 'bs_order.items = bs_items.id')
                ->join('core_users as seller', 'bs_items.added_user_id = seller.user_id');
//                ->join('bs_track_order', 'bs_order.order_id = bs_track_order.order_id', 'left');
                if($operation_type == SELLING) {
                    
//                    $obj = $obj->where(['bs_items.added_user_id'=> $user_id, 'bs_items.item_type_id'=> $operation_type]);
                    $obj = $obj->where('bs_items.added_user_id', $user_id)
                             ->where('bs_order.operation_type !=', EXCHANGE);
                } else if($operation_type == EXCHANGE){
                    $obj = $obj->group_start()->where('bs_items.added_user_id', $user_id)
                            ->or_where('bs_order.user_id', $user_id)->group_end()
                            ->where('bs_order.operation_type', $operation_type);
                } else {
                    $obj = $obj->where(['bs_order.user_id'=> $user_id, 'bs_order.operation_type'=> $operation_type]);
                }
                $obj = $obj->order_by('bs_order.id', 'desc')->get()->result();
//                ->where('bs_order.status', "succeeded")
//                ->where('bs_order.delivery_status', "pending")->get()->result();
//                echo $this->db->last_query();die();
//        echo '<pre>';print_r($obj);die();
        if(!empty($obj)) {
            $row = [];
            foreach ($obj as $key => $value) {
                if($order_state) {
                    if(!is_null($value->completed_date)) {
                        $row[$key] = $value;

                        $address_details = $this->Addresses->get_one( $value->address_id );
                        $row[$key]->address_details = $address_details;

                        $item_details = $this->Item->get_one( $value->items );
                        $this->ps_adapter->convert_item($item_details);

                        if(!empty($item_details->packagesize_id)) {
                            $package_details = $this->Packagesizes->get_one( $item_details->packagesize_id );
                            $this->ps_adapter->convert_packagesize( $package_details );
                            $item_details->package_details = $package_details;
                        } else {
                            $item_details->package_details = (object)[];
                        }
                        if(!empty($item_details->shippingcarrier_id)) {
                            $shipping_details = $this->Shippingcarriers->get_one( $item_details->shippingcarrier_id );
                            $this->ps_adapter->convert_shippingcarrier( $shipping_details );
                            $item_details->shipping_details = $shipping_details;
                        } else {
                            $item_details->shipping_details = (object)[];
                        }
                        $row[$key]->item_details = $item_details;
                        $row[$key]->requested_item_detail = retreive_custom_data($item_details,['id', 'cat_id', 'sub_cat_id', 'item_type_id', 'condition_of_item_id', 'description', 'price', 'title', 'status', 'added_date', 'added_user_id', 'default_photo']);
                
                        $buyer = $this->User->get_one( $value->user_id );
                        $this->ps_adapter->convert_user( $buyer );
                        $row[$key]->buyer = $buyer;

                        $seller = $this->User->get_one( $value->seller_id );
                        $this->ps_adapter->convert_user( $seller );
                        $row[$key]->seller = $seller;

                        $row[$key]->order_state = is_null($value->completed_date) ? 'in_process' : 'complete';

                        if(!is_null($value->share_meeting_list_date)) {
                            $row[$key]->meeting_location = json_decode($this->db->from('bs_meeting')->where('order_id', $value->order_id)->get()->row()->location_list, true);
                        } else {
                            $row[$key]->meeting_location = "";
                        }
                        
                        if(!is_null($value->confirm_meeting_date)) {
                            $row[$key]->confirm_location = json_decode($this->db->from('bs_meeting')->where('order_id', $value->order_id)->get()->row()->confirm_location, true);
                        } else {
                            $row[$key]->confirm_location = "";
                        }
                        $get_tracking = $this->db->from('bs_track_order')->where('order_id', $value->order_id)->order_by('id', 'desc')->get()->row();
                        if(!empty($get_tracking)) {
                            $row[$key]->tracking_status = $get_tracking->status;
                            $row[$key]->tracking_url = $get_tracking->tracking_url;
                        } else {
                            $row[$key]->tracking_status = "";
                            $row[$key]->tracking_url = "";
                        }
                        
                        if($value->is_return) {
                            $created_at = date_create($value->created_at);
                            $date = new DateTime("now");
                            $date2 = date_create($date->format('Y-m-d H:i:s'));
                            $diff = date_diff($created_at, $date2)->format("%a");

                            $row[$key]->is_return_expire = 0;
                            if($diff > 3) {
                                $row[$key]->is_return_expire = 1;
                            } 
                            $return_details = $this->db->select('bs_return_order.id,bs_return_order.order_id,bs_reasons.name as reason_name,bs_return_order.description,bs_return_order.status,bs_return_order.created_at')->from('bs_return_order')
                                    ->join('bs_reasons', "bs_return_order.reason_id = bs_reasons.id")
                                    ->where('order_id', $value->order_id)
                                    ->get()->row();
                            $return_details->images = $this->Image->get_all_by( array( 'img_parent_id' => $value->order_id, 'img_type' => 'return_order' ))->result();
                            
                            if(is_null($return_details->updated_at)) {
                                $created_at = date_create($return_details->created_at);
                                $date = new DateTime("now");
                                $diff = date_diff($created_at, $date2)->format("%a");
                                if($diff > 1) { 
                                    $row[$key]->is_return_expire = 1;
                                }
                            }
                            if($return_details->status == "accept") {
                                $return_trackin_details = $this->db->from('bs_track_order')->where('order_id',$value->order_id)->where('is_return', 1)->order_by('id','desc')->get()->row();

                                $return_details->tracking_status = $return_trackin_details->status;
                                $return_details->tracking_url = $return_trackin_details->tracking_url;
                            }
                            $row[$key]->return_details = $return_details;
                        } else {
                            $row[$key]->return_details = (object)[];
                        }
                        
                        if($operation_type == EXCHANGE) {
                            if(!is_null($value->offer_id)) {
                                $offered_item_details = $this->db->select('offered_item_id,who_pay')->from('bs_exchange_chat_history')->where('bs_exchange_chat_history.chat_id', $value->offer_id)->get()->result();
                                foreach ($offered_item_details as $k => $v) { 
                                    $rowdetails[$k] = $this->Item->get_one( $v->offered_item_id );
                                    $this->ps_adapter->convert_item($rowdetails[$k]);
                                    $who_pay = $v->who_pay;
                                    $rowdetails[$k] = retreive_custom_data($rowdetails[$k],['id', 'cat_id', 'sub_cat_id', 'item_type_id', 'condition_of_item_id', 'description', 'price', 'title', 'status', 'added_date', 'added_user_id', 'default_photo']);
                                }
                            }
                            $row[$key]->who_pay = $who_pay;
                            $row[$key]->exchange_item_detail = $rowdetails;
                        }
                    }
                } else {
                    if(is_null($value->completed_date)) {
                        $row[$key] = $value;

                        $address_details = $this->Addresses->get_one( $value->address_id );
                        $row[$key]->address_details = $address_details;

                        $item_details = $this->Item->get_one( $value->items );
                        $this->ps_adapter->convert_item($item_details);

                        if(!empty($item_details->packagesize_id)) {
                            $package_details = $this->Packagesizes->get_one( $item_details->packagesize_id );
                            $this->ps_adapter->convert_packagesize( $package_details );
                            $item_details->package_details = $package_details;
                        } else {
                            $item_details->package_details = (object)[];
                        }
                        if(!empty($item_details->shippingcarrier_id)) {
                            $shipping_details = $this->Shippingcarriers->get_one( $item_details->shippingcarrier_id );
                            $this->ps_adapter->convert_shippingcarrier( $shipping_details );
                            $item_details->shipping_details = $shipping_details;
                        } else {
                            $item_details->shipping_details = (object)[];
                        }
                        $row[$key]->item_details = $item_details;
                        $row[$key]->requested_item_detail = retreive_custom_data($item_details,['id', 'cat_id', 'sub_cat_id', 'item_type_id', 'condition_of_item_id', 'description', 'price', 'title', 'status', 'added_date', 'added_user_id', 'default_photo']);

                        $buyer = $this->User->get_one( $value->user_id );
                        $this->ps_adapter->convert_user( $buyer );
                        $row[$key]->buyer = $buyer;

                        $seller = $this->User->get_one( $value->seller_id );
                        $this->ps_adapter->convert_user( $seller );
                        $row[$key]->seller = $seller;


                        $row[$key]->order_state = $order_state;

                        if(!is_null($value->share_meeting_list_date)) {
                            $row[$key]->meeting_location = json_decode($this->db->from('bs_meeting')->where('order_id', $value->order_id)->get()->row()->location_list, true);
                        } else {
                            $row[$key]->meeting_location = "";
                        }
                        if(!is_null($value->confirm_meeting_date)) {
                            $row[$key]->confirm_location = json_decode($this->db->from('bs_meeting')->where('order_id', $value->order_id)->get()->row()->confirm_location, true);
                        } else {
                            $row[$key]->confirm_location = "";
                        }
                        
                        $get_tracking = $this->db->from('bs_track_order')->where('order_id', $value->order_id)->order_by('id', 'desc')->get()->row();
                        if(!empty($get_tracking)) {
                            $row[$key]->tracking_status = $get_tracking->status;
                            $row[$key]->tracking_url = $get_tracking->tracking_url;
                        } else {
                            $row[$key]->tracking_status = "";
                            $row[$key]->tracking_url = "";
                        }
                        
                        if($value->is_return) {
                            $created_at = date_create($value->created_at);
                            $date = new DateTime("now");
                            $date2 = date_create($date->format('Y-m-d H:i:s'));
                            $diff = date_diff($created_at, $date2)->format("%a");

                            $row[$key]->is_return_expire = 0;
                            if($diff > 3) {
                                $row[$key]->is_return_expire = 1;
                            } 
                            $return_details = $this->db->select('bs_return_order.id,bs_return_order.order_id,bs_reasons.name as reason_name,bs_return_order.description,bs_return_order.status,bs_return_order.created_at')->from('bs_return_order')
                                    ->join('bs_reasons', "bs_return_order.reason_id = bs_reasons.id")
                                    ->where('order_id', $value->order_id)
                                    ->get()->row();
                            $return_details->images = $this->Image->get_all_by( array( 'img_parent_id' => $value->order_id, 'img_type' => 'return_order' ))->result();
                            if(is_null($return_details->updated_at)) {
                                $created_at = date_create($return_details->created_at);
                                $date = new DateTime("now");
                                $diff = date_diff($created_at, $date2)->format("%a");
                                if($diff > 1) { 
                                    $row[$key]->is_return_expire = 1;
                                }
                            }
                            if($return_details->status == "accept") {
                                $return_trackin_details = $this->db->from('bs_track_order')->where('order_id',$value->order_id)->where('is_return', 1)->order_by('id','desc')->get()->row();

                                $return_details->tracking_status = $return_trackin_details->status;
                                $return_details->tracking_url = $return_trackin_details->tracking_url;
                            }
                            $row[$key]->return_details = $return_details;
                        } else {
                            $row[$key]->return_details = (object)[];
                        }
                        
                        if($operation_type == EXCHANGE) {
                            if(!is_null($value->offer_id)) {
                                $offered_item_details = $this->db->select('offered_item_id,who_pay')->from('bs_exchange_chat_history')->where('bs_exchange_chat_history.chat_id', $value->offer_id)->get()->result();
                                foreach ($offered_item_details as $k => $v) { 
                                    $rowdetails[$k] = $this->Item->get_one( $v->offered_item_id );
                                    $this->ps_adapter->convert_item($rowdetails[$k]);
                                    $who_pay = $v->who_pay;
                                    $rowdetails[$k] = retreive_custom_data($rowdetails[$k],['id', 'cat_id', 'sub_cat_id', 'item_type_id', 'condition_of_item_id', 'description', 'price', 'title', 'status', 'added_date', 'added_user_id', 'default_photo']);
                                }
                            }
                            $row[$key]->who_pay = $who_pay;
                            $row[$key]->exchange_item_detail = $rowdetails;
                        } 
                    }
                }
            }
            if(!empty($row)) {
                $row = array_values($row);
//            $row = $this->ps_security->clean_output( $row );
                $this->custom_response($row);
            } else {
                $this->error_response($this->config->item( 'record_not_found'));
            }
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
        
        $get_user = $this->db->select('bs_order.user_id,bs_items.title')->from('bs_order')->join('bs_items', 'bs_order.items = bs_items.id')->where('bs_order.order_id', $posts['order_id'])->get()->row();
        $buyer = $this->db->select('device_token')->from('core_users')
                            ->where('user_id', $get_user->user_id)->get()->row();
        
        send_push( [$buyer->device_token], ["message" => "Item has been shipped by seller", "flag" => "order", 'title' => $get_user->title." order update"],['order_id'=>$posts['order_id']] );
        
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
        $current_date = date('Y-m-d H:i:s');
        $get_order = $this->db->select('*')->from('bs_order')->where('bs_order.order_id', $posts['order_id'])->get()->row();
        $udpate_order_array['delivery_status'] = 'delivered';
        $udpate_order_array['completed_date'] = $current_date;
        if(is_null($get_order->processed_date)) {
            $udpate_order_array['processed_date'] = $current_date;
        }
        if(is_null($get_order->pickup_date)) {
            $udpate_order_array['pickup_date'] = $current_date;
        }
        if(is_null($get_order->scanqr_date)) {
            $udpate_order_array['scanqr_date'] = $current_date;
        }
        if(is_null($get_order->rate_date)) {
            $udpate_order_array['rate_date'] = $current_date;
        }
        if(is_null($get_order->generate_qr_date)) {
            $udpate_order_array['generate_qr_date'] = $current_date;
        }
        if(is_null($get_order->share_meeting_list_date)) {
            $udpate_order_array['share_meeting_list_date'] = $current_date;
        }
        if(is_null($get_order->confirm_meeting_date)) {
            $udpate_order_array['confirm_meeting_date'] = $current_date;
        }
        
        $get_user = $this->db->select('bs_items.title,bs_items.added_user_id')->from('bs_order')->join('bs_items', 'bs_order.items = bs_items.id')->where('bs_order.order_id', $posts['order_id'])->get()->row();
        $seller = $this->db->select('device_token')->from('core_users')
                            ->where('user_id', $get_user->added_user_id)->get()->row();
        
        send_push( [$seller->device_token], ["message" => "Item has been received by buyer", "flag" => "order", 'title' => $get_user->title." order update"],['order_id'=>$posts['order_id']] );
        
        $this->db->where('order_id',$posts['order_id'])->update('bs_order',$udpate_order_array);
    
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
        $card_id = $offer_details->card_id;
        $cvc = $offer_details->cvc;
        $delivery_address_id = $offer_details->delivery_address_id;
        $stripe_payment_method_id = $offer_details->stripe_payment_method_id;
        $delivery_method_id = $offer_details->delivery_method_id;
        $qty = $offer_details->quantity;
        
        $paid_config = $this->Paid_config->get_one('pconfig1');
        if(!$offer_details->is_offer_complete) {
            $new_odr_id = 'odr_'.time().$posts_var['user_id'];
            $shipping_amount = 0;
            if( ($offer_details->seller_user_id != $posts_var['user_id']) || ($offer_details->seller_user_id == $posts_var['user_id'] && in_array($offer_details->operation_type, [DIRECT_BUY, REQUEST_ITEM]) ) ) {
                $order_user_id = $posts_var['user_id'];
                if($offer_details->seller_user_id == $posts_var['user_id'] && $offer_details->operation_type == DIRECT_BUY) {
                    $order_user_id = $offer_details->buyer_user_id;
                }
                if($offer_details->operation_type == REQUEST_ITEM && is_null($stripe_payment_method_id)) {
                    if(!isset($posts_var['card_id']) || empty($posts_var['card_id']) || is_null($posts_var['card_id'])) {
                        $this->error_response("Please pass card id");
                    }
                    if(!isset($posts_var['cvc']) || empty($posts_var['cvc']) || is_null($posts_var['cvc'])) {
                        $this->error_response("Please pass cvc");
                    }
                    if(!isset($posts_var['delivery_address_id']) || empty($posts_var['delivery_address_id']) || is_null($posts_var['delivery_address_id'])) {
                        $this->error_response("Please pass delivery address id");
                    }
                    
                    $delivery_address_id = $posts_var['delivery_address_id'];
                    $card_id = $posts_var['card_id'];
                    $cvc = $posts_var['cvc'];
                    
                    $card_details = $this->db->from('bs_card')->where('id', $card_id)->get()->row();
                    $expiry_date = explode('/',$card_details->expiry_date);
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
                        $stripe_payment_method_id = $response->id;
                        
                        $chat_data_update['card_id'] = $card_id;
                        $chat_data_update['delivery_address_id'] = $delivery_address_id;
                        $chat_data_update['stripe_payment_method_id'] = $stripe_payment_method_id;
                        $chat_data_update['stripe_payment_method'] = $response;
                        
                        $this->db->where('id', $posts_var['offer_id'])->update('bs_chat_history', $chat_data_update);
                    } catch (exception $e) {
                        $this->db->insert('bs_stripe_error', ['chat_id' => $posts_var['offer_id'], 'card_id' => $card_id, 'response' => $e->getMessage(), 'created_at' => date('Y-m-d H:i:s')]);
                        $this->error_response(get_msg('stripe_transaction_failed'));
                    }
                }
                if($offer_details->operation_type != DIRECT_BUY && !$offer_details->delivery_method_id) {
                    if(!isset($posts_var['delivery_method_id']) || empty($posts_var['delivery_method_id']) || is_null($posts_var['delivery_method_id'])) {
                        $this->error_response("Please pass delivery method id");
                    }
                    $delivery_method_id = $posts_var['delivery_method_id'];
                }

                if($offer_details->operation_type != EXCHANGE) {
                    if(!isset($posts_var['item_id']) || empty($posts_var['item_id']) || is_null($posts_var['item_id'])) {
                        $this->error_response("Please pass item_id");
                    }
                } else {
                    $requested_item_id = $this->db->from('bs_chat_history')->where('id',$posts_var['offer_id'])->get()->row()->requested_item_id;
                }
//                if(!isset($posts_var['operation_type']) || empty($posts_var['operation_type']) || is_null($posts_var['operation_type'])) {
//                    $this->error_response("Please pass operation_type");
//                }
//                if($posts_var['operation_type'] == DIRECT_BUY) {
//                    if(!isset($posts_var['qty']) || empty($posts_var['qty']) || is_null($posts_var['qty'])) {
//                        $this->error_response("Please pass qty");
//                    }
//                }
                $item_price = $offer_details->nego_price;
                
                $backend_config = $this->Backend_config->get_one('be1');
                $service_fee = ((float)$item_price * (float)$backend_config->selling_fees)/100;
                $processing_fees = ((float)$item_price * (float)$backend_config->processing_fees)/100;
                $seller_earn = (float)$item_price - $service_fee - $processing_fees;
               
                if($delivery_method_id == DELIVERY_ONLY) {
                    
                    $get_item = $this->db->select('pay_shipping_by,shipping_type,shippingcarrier_id,shipping_cost_by_seller,is_confirm_with_seller')->from('bs_items')->where('id', $posts_var['item_id'])->get()->row();

                    if($get_item->pay_shipping_by == '1') {
                        if($get_item->is_confirm_with_seller) {
                            
                            $applied_shipping_price = $offer_details->shipping_amount;
                            if(!is_null($offer_details->shippingcarrier_id)) {
                                $get_shiping_detail = $this->db->from('bs_shippingcarriers')->where('id', $offer_details->shippingcarrier_id)->get()->row();
                                $applied_shipping_price = $get_shiping_detail->price;
                            }
                            $item_price = $item_price + (float)$applied_shipping_price;
                            $shipping_amount = $applied_shipping_price;
                            
                        } else if($get_item->shipping_type == '1') {
                            $get_shiping_detail = $this->db->from('bs_shippingcarriers')->where('id', $get_item->shippingcarrier_id)->get()->row();

                            $item_price = $item_price + (float)$get_shiping_detail->price;
                            $shipping_amount = $get_shiping_detail->price;
                            
                        } else if($get_item->shipping_type == '2'){
                            $item_price = $item_price + $get_item->shipping_cost_by_seller;   
                            $shipping_amount = $get_item->shipping_cost_by_seller;
                        }
                    }
                    $this->db->insert('bs_order', ['order_id' => $new_odr_id, 'offer_id' => $posts_var['offer_id'],'user_id' => $order_user_id, 'items' => $posts_var['item_id'], 'qty' => $qty, 'delivery_method' => $delivery_method_id,'payment_method' => 'card', 'card_id' => $card_id, 'address_id' => $delivery_address_id, 'item_offered_price' => $offer_details->nego_price, 'service_fee' => $service_fee, 'processing_fee' => $processing_fees, 'seller_earn' => $seller_earn, 'shipping_amount' => $shipping_amount, 'total_amount' => $item_price, 'status' => 'pending', 'confirm_by_seller'=>1, 'delivery_status' => 'pending', 'transaction' => '','created_at' => date('Y-m-d H:i:s'),'operation_type' => $offer_details->operation_type]);
                    $record = $this->db->insert_id();
                    /*manage stock :start*/
                    $item_detail = $this->db->from('bs_items')->where('id', $posts_var['item_id'])->get()->row();
                    $stock_update = $item_detail->pieces - $qty;
                    $update_array['pieces'] = $stock_update;
                    if(!$stock_update) {
                        $update_array['is_sold_out'] = 1;
                    }
                    $this->db->where('id', $posts_var['item_id'])->update('bs_items', $update_array);
                    $this->db->insert('bs_order_confirm', ['order_id' => $new_odr_id, 'item_id' => $posts_var['item_id'], 'seller_id' => $item_detail->added_user_id, 'created_at' => date('Y-m-d H:i:s')]);
                    
                    if($offer_details->operation_type == REQUEST_ITEM) {
                        $requested_item_stock = $this->db->from('bs_items')->where('id', $offer_details->requested_item_id)->get()->row();
                        $requested_item_stock_update = $requested_item_stock->pieces - 1;
                        $requested_item_stock_array['pieces'] = $requested_item_stock_update;
                        if(!$requested_item_stock_update) {
                            $requested_item_stock_array['is_sold_out'] = 1;
                        }
                        $this->db->where('id', $offer_details->requested_item_id)->update('bs_items', $requested_item_stock_array);
                    }
                    /*manage stock :end*/
                    
                    # set stripe test key
                    \Stripe\Stripe::setApiKey(trim($paid_config->stripe_secret_key));
                    try {
                        $response = \Stripe\PaymentIntent::create([
                            'amount' => $item_price * 100,
                            "currency" => trim($paid_config->currency_short_form),
                            'payment_method' => $stripe_payment_method_id,
                            'payment_method_types' => ['card'],
                            'confirm' => true
                        ]);

                        if (isset($response->id)) { 
                            if($response->status == 'requires_action') {
                                $this->error_response('Transaction requires authorization');
                            }
                            $this->db->where('id', $record)->update('bs_order',['status' => $response->status, 'transaction_id' => $response->id]);
                            $this->tracking_order(['transaction_id' => $response->id, 'create_offer' => 0]);
                            $seller = $this->db->select('device_token,bs_items.title as item_name')->from('bs_items')
                                    ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                                    ->where('bs_items.id', $posts_var['item_id'])->get()->row();
                            
                            $item_images = $this->db->select('img_path')->from('core_images')->where('img_type', 'item')->where('img_parent_id', $posts_var['item_id'])->get()->row();
                            
                            $buyer = $this->db->select('device_token')->from('core_users')
                                    ->where('core_users.user_id', $offer_details->buyer_user_id)->get()->row();
                            $send_noti_user_token = $buyer->device_token;
                            if($offer_details->buyer_user_id != $posts_var['user_id']) {
                                $send_noti_user_token = $seller->device_token;
                            }
                            send_push( [$send_noti_user_token], ["message" => "New order placed", "flag" => "order",'title' => $seller->item_name],['image' => 'http://bacancy.com/biddingapp/uploads/'.$item_images->img_path,'order_id' =>$new_odr_id] );
                            $this->db->where('id',$posts_var['offer_id'])->update('bs_chat_history',['is_offer_complete' => 1,'order_id' => $new_odr_id]);
                            $response = $this->ps_security->clean_output( $response );
                            $this->response(['status' => "success", 'order_status' => 'success', 'order_type' => 'card', 'order_id' => $new_odr_id]);
                        } else {
                            $this->db->where('id', $record)->update('bs_order',['status' => 'fail']);
                            $this->db->insert('bs_stripe_error', ['order_id' => $record, 'response' => $response, 'created_at' => date('Y-m-d H:i:s')]);
                            $this->error_response(get_msg('stripe_transaction_failed'));
                        }
                    } catch (exception $e) {
                        $this->db->where('id', $record)->update('bs_order',['status' => 'fail']);
                        $this->db->insert('bs_stripe_error', ['order_id' => $record, 'response' => $e->getMessage(), 'created_at' => date('Y-m-d H:i:s')]);
                        $this->error_response(get_msg('stripe_transaction_failed'));
                    }

                } else if($delivery_method_id == PICKUP_ONLY) {
                    $date = date('Y-m-d H:i:s');
                    $this->db->insert('bs_order', ['order_id' => $new_odr_id, 'offer_id' => $posts_var['offer_id'],'user_id' => $order_user_id, 'items' => ($posts_var['item_id'] ?? $requested_item_id),'qty' => $qty, 'delivery_method' => $delivery_method_id, 'payment_method' => 'cash', 'card_id' => 0, 'address_id' => $delivery_address_id, 'item_offered_price' => $item_price, 'service_fee' => $service_fee, 'processing_fee' => $processing_fees, 'seller_earn' => $seller_earn, 'shipping_amount' => $shipping_amount, 'total_amount' => $item_price, 'status' => 'success', 'confirm_by_seller'=>1,'delivery_status' => 'pending', 'transaction' => '','created_at' => $date, 'processed_date' => $date,'operation_type' => $offer_details->operation_type]);
                    
                    $record = $this->db->insert_id();
                    /*manage stock :start*/
                    $item_detail = $this->db->from('bs_items')->where('id', $posts_var['item_id'])->get()->row();
                    $stock_update = $item_detail->pieces - 1;
                    $update_array['pieces'] = $stock_update;
                    if(!$stock_update) {
                        $update_array['is_sold_out'] = 1;
                    }
                    $this->db->where('id', $posts_var['item_id'])->update('bs_items', $update_array);
                    $this->db->insert('bs_order_confirm', ['order_id' => $new_odr_id, 'item_id' => $posts_var['item_id'], 'seller_id' => $item_detail->added_user_id, 'created_at' => date('Y-m-d H:i:s')]);
                    /*manage stock :end*/
                    if($offer_details->payin == PAYCARD) {
                        # set stripe test key
                        \Stripe\Stripe::setApiKey(trim($paid_config->stripe_secret_key));
                        try {
                            $response = \Stripe\PaymentIntent::create([
                                'amount' => $item_price * 100,
                                "currency" => trim($paid_config->currency_short_form),
                                'payment_method' => $stripe_payment_method_id,
                                'payment_method_types' => ['card'],
                                'confirm' => true
                            ]);
                            if (isset($response->id)) { 
                                if($response->status == 'requires_action') {
                                   $this->error_response('Transaction requires authorization');
                                }
                                $this->db->where('id', $record)->update('bs_order',['status' => $response->status, 'transaction_id' => $response->id,'payment_method' => 'card', 'card_id' => $card_id]);
                                
                                $this->tracking_order(['transaction_id' => $response->id, 'create_offer' => 0]);
                                $seller = $this->db->select('device_token,bs_items.title as item_name')->from('bs_items')
                                        ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                                        ->where('bs_items.id', $posts_var['item_id'])->get()->row();

                                $item_images = $this->db->select('img_path')->from('core_images')->where('img_type', 'item')->where('img_parent_id', $posts_var['item_id'])->get()->row();

                                $buyer = $this->db->select('device_token')->from('core_users')
                                    ->where('core_users.user_id', $offer_details->buyer_user_id)->get()->row();
                                $send_noti_user_token = $buyer->device_token;
                                if($offer_details->buyer_user_id != $posts_var['user_id']) {
                                    $send_noti_user_token = $seller->device_token;
                                }
                                send_push( [$send_noti_user_token], ["message" => "New order placed", "flag" => "order",'title' => $seller->item_name],['image' => 'http://bacancy.com/biddingapp/uploads/'.$item_images->img_path,'order_id' => $new_odr_id] );
                               $this->db->where('id',$posts_var['offer_id'])->update('bs_chat_history',['is_offer_complete' => 1,'order_id' => $new_odr_id]);
                                $response = $this->ps_security->clean_output( $response );
                                $this->response(['status' => "success", 'order_status' => 'success', 'order_type' => 'card', 'order_id' => $new_odr_id]);
                            } else {
                                $this->db->where('id', $record)->update('bs_order',['status' => 'fail']);
                                $this->error_response(get_msg('stripe_transaction_failed'));
                            }
                        } catch (exception $e) {
                            $this->db->where('id', $record)->update('bs_order',['status' => 'fail']);
                            $this->db->insert('bs_stripe_error', ['order_id' => $record, 'response' => $e->getMessage(), 'created_at' => date('Y-m-d H:i:s')]);
                            $this->error_response(get_msg('stripe_transaction_failed'));
                        } 
                    } else {
                        $this->db->where('id',$posts_var['offer_id'])->update('bs_chat_history',['is_offer_complete' => 1,'order_id' => $new_odr_id]);
                        
                        $seller = $this->db->select('device_token,bs_items.title as item_name')->from('bs_items')
                                ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                                ->where('bs_items.id', $posts_var['item_id'])->get()->row();

                        $item_images = $this->db->select('img_path')->from('core_images')->where('img_type', 'item')->where('img_parent_id', $posts_var['item_id'])->get()->row();

                        $buyer = $this->db->select('device_token')->from('core_users')
                          ->where('core_users.user_id', $offer_details->buyer_user_id)->get()->row();
                        $send_noti_user_token = $buyer->device_token;
                        if($offer_details->buyer_user_id != $posts_var['user_id']) {
                            $send_noti_user_token = $seller->device_token;
                        }
                        send_push( [$send_noti_user_token], ["message" => "New order placed", "flag" => "order",'title' => $seller->item_name],['image' => 'http://bacancy.com/biddingapp/uploads/'.$item_images->img_path,'order_id' => $new_odr_id] );

                        $this->response(['status' => "success", 'order_status' => 'success', 'order_type' => 'cash', 'order_id' => $new_odr_id]);
                    }
                }
            } else {
                if($offer_details->operation_type != EXCHANGE) {
                    $buyer = $this->db->select('device_token')->from('core_users')
                        ->where('core_users.user_id', $offer_details->buyer_user_id)->get()->row();
                    send_push( [$buyer->device_token], ["message" => "Offer confirmed", "flag" => "offer_confirmed_by_seller"] );
                    $this->response(['status' => "success", 'message' => 'Notification sent successfully']);
                } else {
                    $date = date('Y-m-d H:i:s');
//                    if($posts_var['operation_type'] == EXCHANGE) {
                    $requested_item_id = $this->db->from('bs_chat_history')->where('id',$posts_var['offer_id'])->get()->row()->requested_item_id;
//                    } else {
//                        $requested_item_id = $posts_var['item_id'];
//                    }
                    $item_price = $offer_details->nego_price;
                    
                    $backend_config = $this->Backend_config->get_one('be1');
                    $service_fee = ((float)$item_price * (float)$backend_config->selling_fees)/100;

                    $processing_fees = ((float)$item_price * (float)$backend_config->processing_fees)/100;

                    $seller_earn = (float)$item_price - $service_fee - $processing_fees;
                    
                    $this->db->insert('bs_order', ['order_id' => $new_odr_id, 'offer_id' => $posts_var['offer_id'],'user_id' => $offer_details->buyer_user_id, 'items' => $requested_item_id,'qty' => $qty, 'delivery_method' => $delivery_method_id, 'payment_method' => 'cash', 'card_id' => 0, 'address_id' => $delivery_address_id, 'item_offered_price' => $item_price, 'service_fee' => $service_fee, 'processing_fee' => $processing_fees, 'seller_earn' => $seller_earn, 'shipping_amount' => $shipping_amount, 'total_amount' => $item_price, 'status' => 'success', 'confirm_by_seller'=>1,'delivery_status' => 'pending', 'transaction' => '','created_at' => $date, 'processed_date' => $date,'operation_type' => $offer_details->operation_type]);
                    $record = $this->db->insert_id();

                    /*manage stock :start*/
                    $item_details = $this->db->select('bs_items.pieces,bs_items.id as item_id,bs_items.added_user_id')->from('bs_exchange_chat_history')->join('bs_items', 'bs_exchange_chat_history.offered_item_id = bs_items.id')->where('bs_exchange_chat_history.chat_id', $posts_var['offer_id'])->get()->result();
                    foreach($item_details as $key => $value) {
                        $stock_update = $value->pieces - 1;
                        $update_array['pieces'] = $stock_update;
                        if(!$stock_update) {
                            $update_array['is_sold_out'] = 1;
                        }
                        $this->db->where('id', $value->item_id)->update('bs_items', $update_array);
                        $this->db->insert('bs_order_confirm', ['order_id' => $new_odr_id, 'item_id' => $value->item_id, 'seller_id' => $value->added_user_id, 'created_at' => $date]);
                    } 
                    $item_detail = $this->db->from('bs_items')->where('id', $requested_item_id)->get()->row();
                    $stock_update = $item_detail->pieces - 1;
                    $update_array['pieces'] = $stock_update;
                    if(!$stock_update) {
                        $update_array['is_sold_out'] = 1;
                    }
                    $this->db->where('id', $requested_item_id)->update('bs_items', $update_array);
                    $this->db->insert('bs_order_confirm', ['order_id' => $new_odr_id, 'item_id' => $requested_item_id, 'seller_id' => $item_detail->added_user_id, 'created_at' => $date]);
                    /*manage stock :end*/
                    
                    $this->db->where('id',$posts_var['offer_id'])->update('bs_chat_history',['is_offer_complete' => 1,'order_id' => $new_odr_id]);

                    $buyer = $this->db->select('device_token')->from('core_users')
                            ->where('core_users.user_id', $offer_details->buyer_user_id)->get()->row();
                    send_push( [$buyer->device_token], ["message" => "Offer confirmed", "flag" => "order"],['order_id' => $new_odr_id] );
                    
                    $this->response(['status' => "success", 'order_status' => 'success', 'order_type' => 'cash', 'order_id' => $new_odr_id, 'message' => 'Notification sent successfully']);
                }
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




    /**
     * Himanshu Sharma
     * Function to get the card details
     */
    public function get_default_card_post(){
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
        if (!$this->is_valid($rules)) exit; 
        $user_id = $this->post('user_id');
        $cards = $this->db->query("SELECT * FROM `bs_card` WHERE user_id = '".$user_id."' and status = 1 LIMIT 1")->result();
        $cardData = $cards && $cards[0] ? $cards[0] : []; 
        //$this->ps_adapter->convert_card($cardData);
        $this->custom_response($cardData);
    }

    public function tracking_order($param) {
        $get_records = $this->db->from('bs_order')->where('transaction_id', $param['transaction_id'])->get()->result();
        
        $current_date = date("Y-m-d H:i:s");
        foreach ($get_records as $key => $value) {
            $track_exist = $this->db->from('bs_track_order')->where('order_id', $value->order_id)->order_by('id','desc')->get()->row();
            if(empty($track_exist) || $track_exist->status == 'ERROR') {
                $get_item = $this->db->from('bs_items')->where('id', $value->items)->get()->row();
                if($get_item->pay_shipping_by == '1') {
                    if($get_item->shipping_type == '1') { 
                        $shippingcarriers_details = $this->db->from('bs_shippingcarriers')->where('id', $get_item->shippingcarrier_id)->get()->row();
                        $package_details = $this->db->from('bs_packagesizes')->where('id', $get_item->packagesize_id)->get()->row();
                        $buyer_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode')->from('bs_order')
                            ->join('core_users', 'bs_order.user_id = core_users.user_id')
//                            ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id')
                            ->join('bs_addresses', 'bs_order.address_id = bs_addresses.id')
                            ->where('order_id', $value->order_id)->get()->row();
                        
                        $seller_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode')->from('bs_order')
                        ->join('bs_items', 'bs_order.items = bs_items.id')
                        ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
//                        ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id')
                        ->join('bs_addresses', 'bs_order.address_id = bs_addresses.id')
                        ->where('order_id', $value->order_id)->get()->row();
                        
                        $headers = array(
                            "Content-Type: application/json",
                            "Authorization: ShippoToken ".SHIPPO_AUTH_TOKEN  // place your shippo private token here
                                              );
                        $url = 'https://api.goshippo.com/transactions/';
                        $address_from = array(
                            "name"=> $seller_detail->user_name,
                            "street1"=> $seller_detail->address1,
                            "city"=> $seller_detail->city,
                            "state"=> $seller_detail->state,
                            "zip" => $seller_detail->zipcode,
                            "country" => $seller_detail->country,
                            "phone" => $seller_detail->user_phone,
                            "email" => $seller_detail->user_email
                                      );
                        $address_to = array(
                            "name"=> $buyer_detail->user_name,
                            "street1"=> $buyer_detail->address1,
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
                                "carrier_account"=> $shippingcarriers_details->shippo_object_id,
                                "servicelevel_token"=> "usps_priority"
                                        );
                         $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $url);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($shipmentdata));
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                            $response = json_decode(curl_exec($ch)); 
                            curl_close($ch);
                        $this->db->insert('bs_track_order', ['order_id' => $value->order_id, 'object_id' => (isset($response->object_id) ? $response->object_id: ''), 'status' => (isset($response->status) ? $response->status: 'ERROR'), 'tracking_number' => (isset($response->tracking_number) ? $response->tracking_number: ''), 'tracking_url' => (isset($response->tracking_url_provider) ? $response->tracking_url_provider: ''), 'label_url' => (isset($response->label_url) ? $response->label_url: ''), 'response' => json_encode($response), 'created_at' => date('Y-m-d H:i:s')]);
                        $track_number = isset($response->tracking_number) ? $response->tracking_number:'';
                        
//                        if(is_null($track_number) || empty($track_number)) {
//                //            $this->error_response("Something wrong with shipping provided detail");
//                            $this->response(['status' => 'error', 'message' => 'Something wrong with shipping provided detail', 'response' => $response],404);
//                        }
                    }
                }
            }
        }
        if($param['create_offer']) {
            $current_date = date("Y-m-d H:i:s");
            foreach ($get_records as $key => $value) {
                $create_offer['requested_item_id'] = $value->items;
                $create_offer['buyer_user_id'] = $value->user_id;

                $item_detail = $this->db->from('bs_items')->where('id', $value->items)->get()->row();
                $create_offer['seller_user_id'] = $item_detail->added_user_id;
                $create_offer['nego_price'] = $value->item_offered_price;

                $create_offer['type'] = 'to_seller';
                $create_offer['operation_type'] = DIRECT_BUY;
                $create_offer['quantity'] = $value->qty;
                $create_offer['added_date'] = $current_date;
                $create_offer['is_offer_complete'] = 1;
                $create_offer['order_id'] = $value->order_id;
                $create_offer['is_cart_offer'] = 1;
                $this->Chat->save($create_offer);	
                $obj = $this->Chat->get_one_by($create_offer);

                $update_order['offer_id'] = $obj->id;
                if(!is_null($track_number) && !empty($track_number)) {
                    $update_order['processed_date'] = $current_date;
                }
                $this->db->where('order_id', $value->order_id)->update('bs_order',$update_order);
                
                $this->db->where('user_id', $value->user_id)->where('item_id', $value->items)->delete('bs_cart');
            }
        }
        
    }
    
    public function return_order_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        $rules = array(
            array(
                'field' => 'order_id',
                'rules' => 'required'
            ),
            array(
                'field' => 'reason_id',
                'rules' => 'required'
            ),
            array(
                'field' => 'description',
                'rules' => 'required'
            )
        );
        if (!$this->is_valid($rules)) exit; 
        $posts = $this->post();
        
        $date = date('Y-m-d H:i:s');
        $exist_for_return = $this->db->from('bs_order')->where('order_id',$posts['order_id'])->where('is_return', 0)->get()->row();
        if(!empty($exist_for_return)) {
            $this->db->where('order_id', $posts['order_id'])->update('bs_order', ['is_return' => 1, 'return_date' => $date]);
            $this->db->insert('bs_return_order', ['order_id' => $posts['order_id'],'reason_id' => $posts['reason_id'], 'description' => $posts['description'], 'status' =>'initiate','created_at' => $date]);

            $seller = $this->db->select('device_token,bs_items.title as item_name')->from('bs_items')
                    ->join('bs_order', 'bs_order.items = bs_items.id')
                    ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                    ->where('bs_order.order_id', $posts['order_id'])->get()->row_array();
            if(!empty($seller)) {
                send_push( [$seller->device_token], ["message" => "Order Returned", "flag" => "order"],['order_id' => $posts['order_id']] );
            }

            $this->response(['status' => "success", 'message' => 'Order return request initiate successfully']);
        } else {
            $this->error_response("Order already returned");
        }
    }
    
    public function cancel_return_request_post() {
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
        $date = date('Y-m-d H:i:s');
        
        $check_for_order = $this->db->from('bs_return_order')->where('order_id', $posts['order_id'])->get()->row();
        if($check_for_order->status == 'initiate') {
            $this->db->where('order_id', $posts['order_id'])->update('bs_return_order', ['status' => 'cancel', 'cancel_by' => 'buyer', 'updated_at' => $date]);
            
            $seller = $this->db->select('device_token,bs_items.title as item_name')->from('bs_items')
                        ->join('bs_order', 'bs_order.items = bs_items.id')
                        ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                        ->where('bs_order.order_id', $posts['order_id'])->get()->row_array();
            if(!empty($seller)) {
                send_push( [$seller->device_token], ["message" => "Order return request canceled", "flag" => "order"],['order_id' => $posts['order_id']] );
            }

            $this->response(['status' => "success", 'message' => 'Order return canceled']);
        } else {
            $this->error_response("Order already proceed");
        }
    }
    
    public function return_request_action_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        $rules = array(
            array(
                'field' => 'order_id',
                'rules' => 'required'
            ),
            array(
                'field' => 'status',
                'rules' => 'required'
            ),
            array(
                'field' => 'seller_response',
                'rules' => 'required'
            )
        );
        if (!$this->is_valid($rules)) exit; 
        $posts = $this->post();
        $date = date('Y-m-d H:i:s');
                    
        $buyer_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode,device_token')->from('bs_order')
                ->join('core_users', 'bs_order.user_id = core_users.user_id')
                ->join('bs_addresses', 'bs_order.address_id = bs_addresses.id')
                ->where('order_id', $posts['order_id'])->get()->row();
        $update_order['seller_response'] = $posts['seller_response'];
        $update_order['status'] = 'reject';
        $message = "Order return request rejected by seller";
        if($posts['status']) {
            if(!isset($posts['card_id']) || empty($posts['card_id']) || is_null($posts['card_id'])) {
                $this->error_response("Please pass card id");
            }
            if(!isset($posts['cvc']) || empty($posts['cvc']) || is_null($posts['cvc'])) {
                $this->error_response("Please pass cvc");
            }
            
            $check_for_order = $this->db->from('bs_order')->where('order_id', $posts['order_id'])->get()->row();
            $get_item = $this->db->select('pay_shipping_by,shipping_type,shippingcarrier_id,shipping_cost_by_seller')->from('bs_items')->where('id', $check_for_order->items)->get()->row();

            if($get_item->shipping_type == '1') {
                $get_shiping_detail = $this->db->from('bs_shippingcarriers')->where('id', $get_item->shippingcarrier_id)->get()->row();

                $shipping_amount = $get_shiping_detail->price;
            } else if($get_item->shipping_type == '2'){
                $shipping_amount = $get_item->shipping_cost_by_seller;
            }
            
            $shippingcarriers_details = $this->db->from('bs_shippingcarriers')->where('id', $get_item->shippingcarrier_id)->get()->row();
            
            $package_details = $this->db->from('bs_packagesizes')->where('id', $shippingcarriers_details->packagesize_id)->get()->row();
            
            $seller_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode')->from('bs_order')
                ->join('bs_items', 'bs_order.items = bs_items.id')
                ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                ->join('bs_addresses', 'bs_order.address_id = bs_addresses.id')
                ->where('order_id', $posts['order_id'])->get()->row();
           
            /*Shippo integration Start*/
            $headers = array(
                "Content-Type: application/json",
                "Authorization: ShippoToken ".SHIPPO_AUTH_TOKEN  // place your shippo private token here
            );

            $url = 'https://api.goshippo.com/transactions/';

            $address_from = array(
                "name"    => $buyer_detail->user_name,
                "street1" => $buyer_detail->address1,
                "city"    => $buyer_detail->city,
                "state"   => $buyer_detail->state,
                "zip"     => $buyer_detail->zipcode,
                "country" => $buyer_detail->country,
                "phone"   => $buyer_detail->user_phone,
                "email"   => $buyer_detail->user_email
            );    

            $address_to = array(
                "name"    => $seller_detail->user_name,
                "street1" => $seller_detail->address1,
                "city"    => $seller_detail->city,
                "state"   => $seller_detail->state,
                "zip"     => $seller_detail->zipcode,
                "country" => $seller_detail->country,
                "phone"   => $seller_detail->user_phone,
                "email"   => $seller_detail->user_email
            );

            $parcel = array(
                "length"    => $package_details->length,
                "width"     => $package_details->width,
                "height"    => $package_details->height,
                "distance_unit" => "in",
                "weight"    => $package_details->weight,
                "mass_unit" => "lb"
            ); 

            $shipment = 
                array(
                    "address_to" => $address_to,
                    "address_from" => $address_from,
                    "parcels"    => $parcel
            );

            $shipmentdata = 
            array(
                "shipment"           => $shipment,
                "carrier_account"    => $shippingcarriers_details->shippo_object_id,
                "servicelevel_token" => "usps_priority"
            );                   
//            echo '<pre>';print_r($shipmentdata);die();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($shipmentdata));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $response = json_decode(curl_exec($ch)); 
            curl_close($ch);
            
//            echo '<pre>';
//            echo $response->object_id.'<br>';
//            print_r($response);die();

            $this->db->insert('bs_track_order', ['order_id' => $posts['order_id'], 'object_id' => (isset($response->object_id) ? $response->object_id: ''), 'status' => (isset($response->status) ? $response->status: 'ERROR'), 'tracking_number' => (isset($response->tracking_number) ? $response->tracking_number: ''), 'tracking_url' => (isset($response->tracking_url_provider) ? $response->tracking_url_provider: ''), 'label_url' => (isset($response->label_url) ? $response->label_url: ''), 'response' => json_encode($response), 'is_return' => 1,'created_at' => date('Y-m-d H:i:s')]);
            $track_number = isset($response->tracking_number) ? $response->tracking_number:'';
            /*Shippo integration End*/
//            dd($track_number);
            if(!empty($track_number)) {
                $card_id = $posts['card_id'];
                $cvc     = $posts['cvc'];
                $card_details = $this->db->from('bs_card')->where('id', $card_id)->get()->row();
                $expiry_date = explode('/',$card_details->expiry_date);
                $paid_config = $this->Paid_config->get_one('pconfig1');

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
                        'amount' => $shipping_amount * 100,
                        "currency" => trim($paid_config->currency_short_form),
                        'payment_method' => $response->id,
                        'payment_method_types' => ['card'],
                        'confirm' => true
                    ]);

                    if (isset($response->id)) { 
                        if($response->status == 'requires_action') {
                            $this->error_response('Transaction requires authorization');
                        }
                        $update_order['status'] = 'accept';
                        $update_order['payment_status'] = $response->status;
                        $update_order['transaction_id'] = $response->id;
                        $update_order['payment_response'] = $response;
                        $message = "Order return request accepted by seller";
                    } else {
                        $this->db->insert('bs_stripe_error', ['order_id' => $posts['order_id'], 'card_id' => $card_id, 'response' => $response, 'note' => 'return order shipping error', 'created_at' => date('Y-m-d H:i:s')]);
                        $this->error_response(get_msg('stripe_transaction_failed'));
                    }
                } catch (exception $e) {
                    $this->db->insert('bs_stripe_error', ['order_id' => $posts['order_id'], 'card_id' => $card_id, 'response' => $response,'note' => 'return order shipping error', 'created_at' => date('Y-m-d H:i:s')]);
                    $this->error_response(get_msg('stripe_transaction_failed'));
                }
            }
           
        }
        $update_order['updated_at'] = $date;
        $this->db->where('order_id', $posts['order_id'])->update('bs_return_order', $update_order);
        
        if(!empty($buyer_detail)) {
            send_push( [$buyer_detail->device_token], ["message" => $message, "flag" => "order"],['order_id' => $posts['order_id']] );
        }

        $this->response(['status' => "success", 'message' => $message]);
       
    }
    
    public function generate_dispute_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        $rules = array(
            array(
                'field' => 'order_id',
                'rules' => 'required'
            ),
            array(
                'field' => 'name',
                'rules' => 'required'
            ),
            array(
                'field' => 'email',
                'rules' => 'required'
            ),
            array(
                'field' => 'phone',
                'rules' => 'required'
            ),
            array(
                'field' => 'message',
                'rules' => 'required'
            )
        );
        if (!$this->is_valid($rules)) exit; 
        $posts = $this->post();
        $date = date('Y-m-d H:i:s');
        
        $check_for_dispute = $this->db->from('bs_dispute')->where('order_id', $posts['order_id'])->where('status != "close"')->get()->row();
        $message = "Dispute already registered";
        if(empty($check_for_dispute)) {
            $this->db->insert('bs_dispute', ['order_id' => $posts['order_id'],'name' => $posts['name'],'email' => $posts['email'],'phone' => $posts['phone'], 'message' => $posts['message'], 'status' =>'initiate','created_at' => $date]);
            
            $update_order['is_dispute'] = 1;
            $update_order['dispute_date'] = $date;
            $this->db->where('order_id', $posts['order_id'])->update('bs_order', $update_order);
            
            $message = 'Dispute generated against order';
        }
        
        $this->response(['status' => "success", 'message' => $message]);
    }
}
