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
            array(
                'field' => 'usewallet',
                'rules' => 'required'
            ),
            array(
                'field' => 'couponid',
                'rules' => 'required'
            )
        );
        if (!$this->is_valid($rules)) exit;
        $user_id = $this->post('user_id');
        $posts_var = $this->post();
        $current_date = date('Y-m-d H:i:s');
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
                $get_item = $this->db->select('pay_shipping_by,shipping_type,shippingcarrier_id,shipping_cost_by_seller,Address_id')->from('bs_items')->where('id', $value['item_id'])->get()->row();
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

                $processing_fees = (((float)$value['price']*(float)$backend_config->processing_fees)/100)+(float)$backend_config->processing_fees_amount;

                $seller_earn = (float)$value['price'] - $service_fee - $processing_fees;
                
                $this->db->insert('bs_order', ['order_id' => $new_odr_id,'user_id' => $user_id, 'items' => $value['item_id'], 'delivery_method' => $value['delivery_method_id'],'payment_method' => 'card', 'card_id' => $card_id, 'address_id' => $value['delivery_address'], 'seller_address_id' => $get_item->Address_id, 'item_offered_price' => $value['price'], 'service_fee' => $service_fee, 'processing_fee' => $processing_fees, 'seller_earn' => $seller_earn, 'shipping_amount' => $shipping_amount, 'total_amount' => $item_price, 'status' => 'pending', 'delivery_status' => 'pending', 'transaction' => '','created_at' => $current_date,'operation_type' => DIRECT_BUY, 'coupon_id' => $posts_var['couponid']]);
                $records[$key] = $this->db->insert_id();
                if(!$item_price) {
                     /*manage qty: start*/
                    $item_detail = $this->db->from('bs_items')->where('id', $value['item_id'])->get()->row();
                    
                    $stock_update = 0;
                    if($item_detail->pieces > 1) {
                        $stock_update = $item_detail->pieces - 1;
                    }
                    $update_array['pieces'] = $stock_update;
                    if(!$stock_update) {
                        $update_array['is_sold_out'] = 1;
                    }

                    $this->db->where('id', $value['item_id'])->update('bs_items', $update_array);
                    $this->db->where('user_id', $user_id)->where('item_id', $value['item_id'])->delete('bs_cart');
                    /*manage qty: end*/
                }
                
            } else if($value['delivery_method_id'] == PICKUP_ONLY) {
                $item_detail = $this->db->from('bs_items')->where('id', $value['item_id'])->get()->row();
                
                $service_fee = ((float)$item_price * (float)$backend_config->selling_fees)/100;

                $processing_fees = (((float)$item_price * (float)$backend_config->processing_fees)/100)+(float)$backend_config->processing_fees_amount;

                $seller_earn = (float)$item_price - $service_fee - $processing_fees;
                
                $this->db->insert('bs_order', ['order_id' => $new_odr_id,'user_id' => $user_id, 'items' => $value['item_id'], 'delivery_method' => $value['delivery_method_id'], 'payment_method' => 'cash', 'card_id' => 0, 'address_id' => $value['delivery_address'], 'seller_address_id' => $item_detail->Address_id, 'item_offered_price' => $item_price, 'service_fee' => $service_fee, 'processing_fee' => $processing_fees, 'seller_earn' => $seller_earn, 'shipping_amount' => $shipping_amount, 'total_amount' => $item_price, 'status' => 'succeeded', 'delivery_status' => 'pending', 'transaction' => '','created_at' => $current_date,'operation_type' => DIRECT_BUY, 'coupon_id' => $posts_var['couponid']]);

                if($value['payin'] == PAYCARD) {
                    $records[$key] = $this->db->insert_id();
                    $card_total_amount += $item_price;
                    
                    $this->db->where('id', $this->db->insert_id())->update('bs_order',['payment_method' => 'card', 'card_id' => $posts_var['card_id']]);
                }
                
                /*manage qty: start*/
                $stock_update = 0;
                if($item_detail->pieces > 1) {
                    $stock_update = $item_detail->pieces - 1;
                }
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
            $remaining_amount = $card_total_amount;
            if($posts_var['couponid']) {
                $coupondetail = $this->db->from('bs_coupan')->where('id', $posts_var['couponid'])->get()->row();
                if($coupondetail->type) {
                    $coupon_discount = ($remaining_amount*$coupondetail->value)/100;
                    $remaining_amount = $remaining_amount - $coupon_discount;
                } else {
                    $coupon_discount = $coupondetail->value;
                    $remaining_amount = $remaining_amount - $coupon_discount;
                }
            }
            if($posts_var['usewallet'] && $remaining_amount) {
                $get_user_wallet = $this->db->select('wallet_amount')->from('core_users')->where('user_id', $user_id)->get()->row();
                if($card_total_amount > $get_user_wallet->wallet_amount) {
                    $remaining_amount = $card_total_amount - $get_user_wallet->wallet_amount;
                } else {
                    $remaining_amount = 0;
                }
            }
            
            if($remaining_amount) {
                
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
                        'amount' => round($remaining_amount * 100),
                        "currency" => trim($paid_config->currency_short_form),
                        'payment_method' => $response->id,
                        'payment_method_types' => ['card'],
                        'confirm' => true
                    ]);

                    if (isset($response->id)) { 
                        if($response->status == 'requires_action') {
                            $this->error_response('Transaction requires authorization');
                        }
                        if($posts_var['usewallet']) {
                            /* wallet management: start*/
                            $wallet_hisotry = $card_total_amount - $remaining_amount;
                            $this->db->insert('bs_wallet',['parent_id' => $new_odr_id,'user_id' => $user_id,'action' => 'minus', 'amount' => $wallet_hisotry,'type' => 'order_payment', 'created_at' => $current_date]);
                            $this->db->where('user_id', $user_id)->update('core_users',['wallet_amount' => $get_user_wallet->wallet_amount - $wallet_hisotry]);
                            /* wallet management: end*/
                        }
                        $update_order_array['status'] = $response->status;
                        $update_order_array['transaction_id'] = $response->id;
                        if($posts_var['couponid']) {
                            $update_order_array['coupon_id'] = $posts_var['couponid'];
                            $update_order_array['coupon_type'] = $coupondetail->type;
                            $update_order_array['coupon_discount'] = $coupon_discount;
                            /* generate coupon for ref user : start*/
                            $get_user_order_count = $this->db->select('id')->from('bs_order')->where('user_id', $user_id)->get()->num_rows();
                            if($get_user_order_count == 1) {
                                $get_coupon_detail = $this->db->from('bs_coupan')->where('slug', 'refer_friend')->get()->row();
                                if($coupondetail->parent_id == $get_coupon_detail->id) {
                                    $get_user_reference_referral_code = $this->db->select('reference_referral_code')->from('core_users')->where('user_id', $user_id)->get()->row();
                                    $find_owner_of_ref = $this->db->select('user_id')->from('core_users')->where('referral_code', $get_user_reference_referral_code->reference_referral_code)->get()->row();

                                    $this->db->insert('bs_coupan',['type'=> $get_coupon_detail->type,'value'=> $get_coupon_detail->user_earn,'min_purchase_amount' => $get_coupon_detail->min_purchase_amount,'status' => 1,'user_id' => $find_owner_of_ref->user_id, 'description' => 'Your referral user '.$user_id.' place first order with coupon code','created_at' => $current_date]);
                                }
                            } else {
                                $get_coupon_detail = $this->db->from('bs_coupan')->where('slug', 'refer_friend')->get()->row();
                                if($coupondetail->parent_id == $get_coupon_detail->id) { 
                                    $this->db->where('id',$posts_var['couponid'])->update('bs_coupan',['status' => 0 ]);
                                }
                            }
                            /* generate coupon for ref user : end*/
                        }

                        $this->db->where_in('id', $records)->update('bs_order',$update_order_array);
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
                        $this->db->insert('bs_stripe_error', ['order_id' => json_encode($records), 'card_id' => $card_id, 'response' => $response, 'created_at' => $current_date]);
                        $this->error_response(get_msg('stripe_transaction_failed'));
                    }
                } catch (exception $e) {
                    $this->db->where_in('id', $records)->update('bs_order',['status' => 'fail']);
                    $this->db->insert('bs_stripe_error', ['order_id' => json_encode($records), 'card_id' => $card_id, 'response' => $e->getMessage(), 'created_at' => $current_date]);
                    $this->error_response(get_msg('stripe_transaction_failed'));
                }
            } else {
                if($posts_var['usewallet']) {
                    /* wallet management: start*/
                    $this->db->insert('bs_wallet',['parent_id' => $new_odr_id,'user_id' => $user_id,'action' => 'minus', 'amount' => $card_total_amount,'type' => 'order_payment', 'created_at' => $current_date]);
                    $this->db->where('user_id', $user_id)->update('core_users',['wallet_amount' => $get_user_wallet->wallet_amount - $card_total_amount]);
                    /* wallet management: end*/
                }
                $update_order_array['status'] = 'succeeded';
                if($posts_var['couponid']) {
                    $update_order_array['coupon_id'] = $posts_var['couponid'];
                    $update_order_array['coupon_type'] = $coupondetail->type;
                    $update_order_array['coupon_discount'] = $coupon_discount;
                }
                /* generate coupon for ref user : start*/
                $get_user_order_count = $this->db->select('id')->from('bs_order')->where('user_id', $user_id)->get()->num_rows();
                if($get_user_order_count == 1) {
                    $get_coupon_detail = $this->db->from('bs_coupan')->where('slug', 'refer_friend')->get()->row();
                    if($coupondetail->parent_id == $get_coupon_detail->id) {
                        $get_user_reference_referral_code = $this->db->select('reference_referral_code')->from('core_users')->where('user_id', $user_id)->get()->row();
                        $find_owner_of_ref = $this->db->select('user_id')->from('core_users')->where('referral_code', $get_user_reference_referral_code->reference_referral_code)->get()->row();

                        $this->db->insert('bs_coupan',['type'=> $get_coupon_detail->type,'value'=> $get_coupon_detail->user_earn,'min_purchase_amount' => $get_coupon_detail->min_purchase_amount,'status' => 1,'user_id' => $find_owner_of_ref->user_id, 'description' => 'Your referral user '.$user_id.' place first order with coupon code','created_at' => $current_date]);
                    }
                }
                /* generate coupon for ref user : end*/
                $this->tracking_order(['records' => $records, 'create_offer' => 1]);
                $this->db->where_in('id', $records)->update('bs_order',$update_order_array);
                $item_ids = array_column($items,'item_id');
                foreach ($item_ids as $key => $value) {
                    $item_images = $this->db->select('img_path')->from('core_images')->where('img_type', 'item')->where('img_parent_id', $value)->get()->row();

                    $seller = $this->db->select('device_token,bs_items.id as item_id,bs_items.title as item_name')->from('bs_items')
                        ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                        ->where('bs_items.id', $value)->get()->row_array();

                    send_push( [$seller['device_token']], ["message" => "New order placed", "flag" => "order",'title' =>$seller['item_name']], ['image' => 'http://bacancy.com/biddingapp/uploads/'.$item_images->img_path, 'order_id' => $orderids[$value]] );
                }
                $response = $this->ps_security->clean_output( $response );
                $this->response(['status' => "success", 'order_status' => 'success', 'order_type' => 'card']);
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

//    public function checkout_post() {
//        $user_data = $this->_apiConfig([
//            'methods' => ['POST'],
//            'requireAuthorization' => true,
//        ]);
//        $rules = array(
//            array(
//                'field' => 'user_id',
//                'rules' => 'required'
//            ),
////            array(
////                'field' => 'item_ids',
////                'rules' => 'required'
////            ),
//            array(
//                'field' => 'delivery_method',
//                'rules' => 'required'
//            ),
//            array(
//                'field' => 'payment_method',
//                'rules' => 'required'
//            ),
//            array(
//                'field' => 'address_id',
//                'rules' => 'required'
//            ),
//            array(
//                'field' => 'card_id',
//                'rules' => 'required'
//            ),
//            array(
//                'field' => 'total_amount',
//                'rules' => 'required'
//            ),
//            array(
//                'field' => 'cvc',
//                'rules' => 'required'
//            ),
////            array(
////                'field' => 'transaction_detail',
////                'rules' => 'required'
////            )
//        );
//        if (!$this->is_valid($rules)) exit;
//        
//        $user_id            = $this->post('user_id');
////        $item_ids           = implode(',', $this->post('item_ids'));
//        $delivery_method    = $this->post('delivery_method');
//        $payment_method     = strtolower($this->post('payment_method'));
//        $address_id         = $this->post('address_id');
//        $total_amount       = $this->post('total_amount');
//        $posts_var = $this->post();
//
//        $item_ids = [];
//        if(!isset($posts_var['item_ids']) || empty($posts_var['item_ids']) || is_null($posts_var['item_ids'])) { 
//            $this->error_response("Please pass item ids");
//        } else {
//            if(is_array($posts_var['item_ids'])) {
//                $item_ids = implode(',', $posts_var['item_ids']);
//            } else {
//                $item_ids = $posts_var['item_ids'];
//            }
//        }
//        $shipping_amount = 0;
//        $backend_config = $this->Backend_config->get_one('be1');
//
//        $service_fee = ((float)$this->post('total_amount') * (float)$backend_config->selling_fees)/100;
//
//        $processing_fees = (((float)$this->post('total_amount') * (float)$backend_config->processing_fees)/100)+(float)$backend_config->processing_fees_amount;
//
//        $seller_earn = (float)$this->post('total_amount') - $service_fee - $processing_fees;
//        
//        if($payment_method == 'card') {
//            if(!isset($posts_var['card_id']) || empty($posts_var['card_id']) || is_null($posts_var['card_id'])) {
//                $this->error_response("Please pass card id");
//            }
//            if(!isset($posts_var['cvc']) || empty($posts_var['cvc']) || is_null($posts_var['cvc'])) {
//                $this->error_response("Please pass cvc");
//            }
//            $card_id = $this->post('card_id');
//            $cvc     = $this->post('cvc');
//            $card_details = $this->db->from('bs_card')->where('id', $card_id)->get()->row();
//            $expiry_date = explode('/',$card_details->expiry_date);
//            $paid_config = $this->Paid_config->get_one('pconfig1');
//            $item_price = $this->post('total_amount');
//            
//            $get_item = $this->db->select('pay_shipping_by,shipping_type,shippingcarrier_id,shipping_cost_by_seller')->from('bs_items')->where('id', $item_ids)->get()->row();
//            if($get_item->pay_shipping_by == '1') {
//                if($get_item->shipping_type == '1') {
//                    $get_shiping_detail = $this->db->from('bs_shippingcarriers')->where('id', $get_item->shippingcarrier_id)->get()->row();
//                    
//                    $item_price = $item_price + (float)$get_shiping_detail->price;
//                    $shipping_amount = $get_shiping_detail->price;
//                } else if($get_item->shipping_type == '2'){
//                    $item_price = $item_price + $get_item->shipping_cost_by_seller;   
//                    $shipping_amount = $get_item->shipping_cost_by_seller;
//                }
//            }
//            
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
//                    'amount' => $item_price * 100,
//                    "currency" => trim($paid_config->currency_short_form),
//                    'payment_method' => $response->id,
//                    'payment_method_types' => ['card']
//                ]);
//                $new_odr_id = 'odr_'.time().$user_id;
//                $this->db->insert('bs_order', ['order_id' => $new_odr_id,'user_id' => $user_id, 'items' => $item_ids, 'delivery_method' => $delivery_method,'payment_method' => 'card', 'card_id' => $card_id, 'address_id' => $address_id, 'item_offered_price' => $this->post('total_amount'), 'service_fee' => $service_fee, 'processing_fee' => $processing_fees, 'seller_earn' => $seller_earn, 'shipping_amount' => $shipping_amount, 'total_amount' => $total_amount, 'status' => 'pending', 'delivery_status' => 'pending', 'transaction' => $response,'created_at' => date('Y-m-d H:i:s')]);
//                $record_id = $this->db->insert_id();
//                if (isset($response->id)) { 
//                    $this->db->where('id', $record_id)->update('bs_order',['status' => 'initiate', 'transaction_id' => $response->id]);
//                    $this->response(['status' => "success", 'order_status' => 'success', 'intent_id' => $response->id, 'record_id' => $new_odr_id, 'client_secret' => $response->client_secret, 'response' => $response]);
//                    
//                    $items = $this->db->from('bs_items')->where_in('id', $item_ids)->get()->result_array();
//                    foreach ($items as $key => $value) {
//                        $seller_device_token = $this->db->select('device_token')->from('core_users')->where('user_id', $value['added_user_id'])->get()->row();
//                        send_push( [$seller_device_token->device_token], ["message" => "New order arrived", "flag" => "order"],['order_id' => $record_id] );
//                    }
//                } else {
//                    $this->db->where('id', $record_id)->update('bs_order',['status' => 'fail']);
//                    $this->error_response(get_msg('stripe_transaction_failed'));
//                }
//            } catch (exception $e) {
//                $this->db->where('id', $record_id)->update('bs_order',['status' => 'fail']);
//                $this->error_response(get_msg('stripe_transaction_failed'));
//            }
//        } else if($payment_method == 'cash') {
//            $this->db->insert('bs_order', ['user_id' => $user_id, 'items' => $item_ids, 'delivery_method' => $delivery_method, 'payment_method' => 'cash', 'card_id' => 0, 'address_id' => $address_id, 'item_offered_price' => $total_amount, 'service_fee' => $service_fee, 'processing_fee' => $processing_fees, 'seller_earn' => $seller_earn, 'shipping_amount' => $shipping_amount, 'total_amount' => $total_amount, 'status' => 'succeeded', 'delivery_status' => 'pending', 'transaction' => '','created_at' => date('Y-m-d H:i:s')]);
//            
//            $this->response(['status' => "success", 'order_status' => 'success']);
//        }
////        $transaction_detail = $this->post('transaction_detail');
//        
////        $this->db->insert('bs_order', ['user_id' => $user_id, 'items' => $item_ids, 'delivery_method' => $delivery_method, 'card_id' => $card_id, 'address_id' => $address_id, 'total_amount' => $total_amount, 'status' => 'success', 'transaction' => $transaction_detail,'created_at' => date('Y-m-d H:i:s')]);
////        
////        $this->response(['status' => "success", 'order_status' => 'success']);
////        if($delivery_method == 'card') {
////            $card_details = $this->db->from('bs_card')->where('id', $card_id)->get()->row();
////            $expiry_date = explode('/',$card_details->expiry_date);
////            $paid_config = $this->Paid_config->get_one('pconfig1');
////            # set stripe test key
////            \Stripe\Stripe::setApiKey(trim($paid_config->stripe_secret_key));
////            $record_id = 0;
////            try {
////                $response = \Stripe\PaymentMethod::create([
////                    'type' => 'card',
////                    'card' => [
////                        'number' => $card_details->card_number,
////                        'exp_month' => $expiry_date[0],
////                        'exp_year' => $expiry_date[1],
////                        'cvc' => $cvc
////                    ]
////                ]);
////                $response = \Stripe\PaymentIntent::create([
////                    'amount' => $this->post('total_amount') * 100,
////                    "currency" => trim($paid_config->currency_short_form),
////                    'payment_method' => $response->id,
////                    'payment_method_types' => ['card']
////                ]);
////                $response = \Stripe\PaymentIntent::retrieve($response->id)->confirm();
////    //            $response = \Stripe\Balance::retrieve();
////    //            $response = \Stripe\BalanceTransaction::all();
////    //            print_r($response);
////    //            die();
////                $this->db->insert('bs_order', ['user_id' => $user_id, 'items' => $item_ids, 'delivery_method' => $delivery_method, 'card_id' => $card_id, 'address_id' => $address_id, 'total_amount' => $total_amount, 'status' => 'success', 'transaction' => $response,'created_at' => date('Y-m-d H:i:s')]);
////                $record_id = $this->db->insert_id();
////                if ($response->status == "succeeded") {
////                    $this->db->where('id', $record_id)->update(['status' => 'success']);
////                    $this->response(['status' => "success", 'order_status' => 'success']);
////                } else {
////                    $this->db->where('id', $record_id)->update(['status' => 'fail']);
////                    $this->error_response(get_msg('stripe_transaction_failed'));
////                }
////            } catch (exception $e) {
////                $this->db->where('id', $record_id)->update(['status' => 'fail']);
////                $this->error_response(get_msg('stripe_transaction_failed'));
////            }
////        } else {
////            $this->db->insert('bs_order', ['user_id' => $user_id, 'items' => $item_ids, 'delivery_method' => $delivery_method, 'card_id' => $expiry_date, 'address_id' => $address_id, 'total_amount' => $total_amount, 'status' => 'success', 'created_at' => date('Y-m-d H:i:s')]);
////            
////            $this->response(['status' => "success", 'order_status' => 'success']);
////        }
//    }
    
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
        $date = date('Y-m-d H:i:s');
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
            
            $buyer_detail = $this->db->select('user_name,user_email,user_phone,device_token,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode,bs_addresses.id')->from('bs_order')
                    ->join('core_users', 'bs_order.user_id = core_users.user_id')
//                    ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id')
                    ->join('bs_addresses', 'bs_order.address_id = bs_addresses.id')
                    ->where('order_id', $posts_var['order_id'])->get()->row();
            
            $seller_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode,bs_addresses.id')->from('bs_order')
                    ->join('bs_items', 'bs_order.items = bs_items.id')
                    ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
//                    ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id')
                    ->join('bs_addresses', 'bs_items.address_id = bs_addresses.id')
                    ->where('order_id', $posts_var['order_id'])->get()->row();
            
            $ship_from = $seller_detail->id;
            $ship_to = $buyer_detail->id;
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
                $this->db->insert('bs_track_order', ['order_id' => $posts_var['order_id'], 'ship_from' => $ship_from, 'ship_to' => $ship_to, 'object_id' => (isset($response->object_id) ? $response->object_id: ''), 'status' => (isset($response->status) ? $response->status: 'ERROR'), 'tracking_status' => (isset($response->tracking_status) ? $response->tracking_status: ''),'tracking_number' => (isset($response->tracking_number) ? $response->tracking_number: ''), 'tracking_url' => (isset($response->tracking_url_provider) ? $response->tracking_url_provider: ''), 'label_url' => (isset($response->label_url) ? $response->label_url: ''), 'response' => json_encode($response), 'created_at' => $date]);
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
                $this->db->where('order_id', $posts_var['order_id'])->update('bs_order',['seller_transaction_status' => $response->status, 'seller_transaction_id' => $response->id, 'processed_date' => $date]);
                if(!empty($buyer_detail->device_token)) {
                    send_push( [$buyer_detail->device_token], ["message" => "Seller confirm shipping cost", "flag" => "order", 'title' => 'ALMOST THERE!'],['order_id' => $posts_var['order_id'], 'price' => $amount] );
                }
                
                $this->response(['status' => "success", 'track_number' => $track_number]);
            } else {
                $this->db->where('order_id', $record_id)->update('bs_order',['seller_transaction_status' => 'fail']);
                $this->db->insert('bs_stripe_error', ['order_id' => $record_id, 'card_id' => $card_id, 'response' => $response, 'created_at' => $date]);
                $this->error_response(get_msg('stripe_transaction_failed'));
            }
        } catch (exception $e) {
            $this->db->where('order_id', $record_id)->update('bs_order',['seller_transaction_status' => 'fail']);
            $this->db->insert('bs_stripe_error', ['order_id' => $record_id, 'card_id' => $card_id, 'response' => $e->getMessage(), 'created_at' => $date]);
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
        $orders = $this->db->select('bs_order.*,bs_order.seller_address_id as ship_from, bs_items.title, bs_items.is_sold_out, bs_track_order.status as tracking_status, bs_track_order.tracking_url, bs_track_order.label_url, seller.user_id as seller_id')->from('bs_order')
//                ->join('core_users as order_user', 'bs_order.user_id = order_user.user_id')
                ->join('bs_items', 'bs_order.items = bs_items.id')
                ->join('core_users as seller', 'bs_items.added_user_id = seller.user_id')
                ->join('bs_track_order', 'bs_order.order_id = bs_track_order.order_id', 'left')
                ->where('bs_order.order_id', $order_id)->get()->row_array();

        if(!empty($orders) && count($orders)) {
            $address_details = $this->Addresses->get_one( $orders['address_id'] );
            $orders['address_details'] = $address_details;
            
            if(!empty($orders['ship_from'])) {
                $ship_fromaddress= $this->Addresses->get_one( $orders['ship_from'] );
                $orders['ship_fromaddress'] = $ship_fromaddress;
            } else {
                 $orders['ship_fromaddress'] = "";
            }
                
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
//                $payment_response = str_replace('Stripe\\PaymentIntent JSON: ', '', $dispute_details->payment_response);
                unset($dispute_details->payment_response); 
                $orders['dispute_details'] = $dispute_details;
            } else {
                $orders['dispute_details'] = (object)[];
            }
            if($orders['is_seller_dispute']) {
                $seller_dispute_details = $this->db->from('bs_dispute')->where('order_id', $order_id)->where('is_seller_generate', 1)->get()->row();
                $orders['seller_dispute_details'] = $seller_dispute_details;
            } else {
                $orders['seller_dispute_details'] = (object)[];
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
                $return_details = $this->db->select('bs_return_order.id,bs_return_order.order_id,bs_reasons.name as reason_name,bs_return_order.description,bs_return_order.status,bs_return_order.created_at, bs_return_order.seller_response')->from('bs_return_order')
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
                
                if($return_details->status == "accept" || ($orders['is_dispute'] && $orders['dispute_details']->status == "accept") ) {
                    $return_trackin_details = $this->db->from('bs_track_order')->where('order_id',$order_id)->where('is_return', 1)->order_by('id','desc')->get()->row();

                    $return_details->tracking_status = $return_trackin_details->status;
                    $return_details->tracking_url = $return_trackin_details->tracking_url;
                    $return_details->label_url = $return_trackin_details->label_url;
                }
                $return_refund_details = $this->db->from('bs_wallet')->where('parent_id',$order_id)->where('user_id', $orders['user_id'])->where_in('type', ['cancel_order_payment','refund'])->get()->row();
                
                if(!empty($return_refund_details)) {
                    $orders['isRefund'] = 1;
                }
                $orders['return_details'] = $return_details;
            } else {
                $orders['return_details'] = (object)[];
            }
            
            if($orders['is_seller_rate']) {
                $orders['seller_rate_details'] = $this->db->from('bs_ratings')->where('order_id',$order_id)->where('from_user_id != "'.$orders['user_id'].'"')->get()->row();
            } else {
                $orders['seller_rate_details'] = (object)[];
            }
            if($orders['is_buyer_rate']) {
                $orders['buyer_rate_details'] = $this->db->from('bs_ratings')->where('order_id',$order_id)->where('from_user_id = "'.$orders['user_id'].'"')->get()->row();
            } else {
                $orders['buyer_rate_details'] = (object)[];
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
            $orders['total_item_price'] = (double)$orders['item_offered_price'] * (int)$orders['qty'];
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
                        $buyer_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode,bs_addresses.id')->from('bs_order')
                            ->join('core_users', 'bs_order.user_id = core_users.user_id')
//                            ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id')
                            ->join('bs_addresses', 'bs_order.address_id = bs_addresses.id')
                            ->where('order_id', $value->order_id)->get()->row();
                        
                        $seller_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode,bs_addresses.id')->from('bs_order')
                        ->join('bs_items', 'bs_order.items = bs_items.id')
                        ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
//                        ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id')
                        ->join('bs_addresses', 'bs_items.address_id = bs_addresses.id')
                        ->where('order_id', $value->order_id)->get()->row();
                        $ship_from = $seller_detail->id;
                        $ship_to = $buyer_detail->id;
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
                        $this->db->insert('bs_track_order', ['order_id' => $value->order_id, 'ship_from' => $ship_from, 'ship_to' => $ship_to, 'object_id' => (isset($response->object_id) ? $response->object_id: ''), 'status' => (isset($response->status) ? $response->status: 'ERROR'), 'tracking_status' => (isset($response->tracking_status) ? $response->tracking_status: ''), 'tracking_number' => (isset($response->tracking_number) ? $response->tracking_number: ''), 'tracking_url' => (isset($response->tracking_url_provider) ? $response->tracking_url_provider: ''), 'label_url' => (isset($response->label_url) ? $response->label_url: ''), 'response' => json_encode($response), 'created_at' => date('Y-m-d H:i:s')]);
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
        $obj = $this->db->select('bs_order.*,bs_order.seller_address_id as ship_from,seller.user_id as seller_id')->from('bs_order')
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
                
                $obj = $obj->where('bs_order.status !=', "fail")->order_by('bs_order.id', 'desc')->get()->result();
//                ->where('bs_order.delivery_status', "pending")->get()->result();
//                echo $this->db->last_query();die();
//        echo '<pre>';print_r($obj);die();
        if(!empty($obj)) {
            $row = [];
            foreach ($obj as $key => $value) {
                if($order_state) {
                    if(!is_null($value->completed_date)) {
                        $row[$key] = $value;
                        $row[$key]->total_item_price = (double)$value->item_offered_price * (int)$value->qty;

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
                            if(!empty($value->ship_from)) {
                                $ship_fromaddress= $this->Addresses->get_one( $get_tracking->ship_from );
                                $row[$key]->ship_fromaddress = $ship_fromaddress;
                            } else {
                                $row[$key]->ship_fromaddress = "";
                            }
                        } else {
                            $row[$key]->tracking_status = "";
                            $row[$key]->tracking_url = "";
                            $row[$key]->ship_fromaddress = "";
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
                            $return_details = $this->db->select('bs_return_order.id,bs_return_order.order_id,bs_reasons.name as reason_name,bs_return_order.description,bs_return_order.status,bs_return_order.created_at,bs_return_order.seller_response')->from('bs_return_order')
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
                                $return_details->label_url = $return_trackin_details->label_url;
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
                        $row[$key]->total_item_price = (double)$value->item_offered_price * (int)$value->qty;

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
                            if(!empty($value->ship_from)) {
                                $ship_fromaddress= $this->Addresses->get_one( $get_tracking->ship_from );
                                $row[$key]->ship_fromaddress = $ship_fromaddress;
                            } else {
                                $row[$key]->ship_fromaddress = "";
                            }
                        } else {
                            $row[$key]->tracking_status = "";
                            $row[$key]->tracking_url = "";
                            $row[$key]->ship_fromaddress = "";
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
                            $return_details = $this->db->select('bs_return_order.id,bs_return_order.order_id,bs_reasons.name as reason_name,bs_return_order.description,bs_return_order.status,bs_return_order.created_at,bs_return_order.seller_response')->from('bs_return_order')
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
                                $return_details->label_url = $return_trackin_details->label_url;
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
        $udpate_order_array['delivery_date'] = $current_date;
//        $udpate_order_array['completed_date'] = $current_date;
        $udpate_order_array['return_expiry_date'] = date('Y-m-d H:i:s', strtotime($current_date. ' + 3 days'));
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
        $date = date('Y-m-d H:i:s');
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
//                if($offer_details->seller_user_id == $posts_var['user_id'] && $offer_details->operation_type == DIRECT_BUY) {
                if($offer_details->seller_user_id == $posts_var['user_id'] && in_array($offer_details->operation_type, [DIRECT_BUY, REQUEST_ITEM]) ) {
                    $order_user_id = $offer_details->buyer_user_id;
                }
                if(($offer_details->operation_type == REQUEST_ITEM && is_null($stripe_payment_method_id)) || is_null($stripe_payment_method_id)) {
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
                        $this->db->insert('bs_stripe_error', ['chat_id' => $posts_var['offer_id'], 'card_id' => $card_id, 'response' => $e->getMessage(), 'created_at' => $date]);
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
                
                if(in_array($offer_details->operation_type, [DIRECT_BUY, REQUEST_ITEM]) ) {
                    $item_price = $offer_details->nego_price*$qty; 
                }
                
                $backend_config = $this->Backend_config->get_one('be1');
                $service_fee = ((float)$item_price * (float)$backend_config->selling_fees)/100;
                $processing_fees = (((float)$item_price * (float)$backend_config->processing_fees)/100)+(float)$backend_config->processing_fees_amount;
                $seller_earn = (float)$item_price - $service_fee - $processing_fees;
               
                if($delivery_method_id == DELIVERY_ONLY) {
                    
                    $get_item = $this->db->select('pay_shipping_by,shipping_type,shippingcarrier_id,shipping_cost_by_seller,is_confirm_with_seller,Address_id')->from('bs_items')->where('id', $posts_var['item_id'])->get()->row();

                    if($get_item->pay_shipping_by == '1') {
                        if($get_item->is_confirm_with_seller || $qty > 1) {
                            
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
                    $this->db->insert('bs_order', ['order_id' => $new_odr_id, 'offer_id' => $posts_var['offer_id'],'user_id' => $order_user_id, 'items' => $posts_var['item_id'], 'qty' => $qty, 'delivery_method' => $delivery_method_id,'payment_method' => 'card', 'card_id' => $card_id, 'address_id' => $delivery_address_id,'seller_address_id' => $get_item->Address_id, 'item_offered_price' => $offer_details->nego_price, 'service_fee' => $service_fee, 'processing_fee' => $processing_fees, 'seller_earn' => $seller_earn, 'shipping_amount' => $shipping_amount, 'total_amount' => $item_price, 'status' => 'pending', 'confirm_by_seller'=>1, 'delivery_status' => 'pending', 'transaction' => '','created_at' => $date,'operation_type' => $offer_details->operation_type]);
                    $record = $this->db->insert_id();
                    /*manage stock :start*/
                    if($offer_details->operation_type == REQUEST_ITEM) {
                        $requested_item_stock = $this->db->from('bs_items')->where('id', $offer_details->requested_item_id)->get()->row();
                        $requested_item_stock_update = $requested_item_stock->pieces - $qty;
                        $requested_item_stock_array['pieces'] = $requested_item_stock_update;
                        if(!$requested_item_stock_update) {
                            $requested_item_stock_array['is_sold_out'] = 1;
                        }
                        $this->db->where('id', $offer_details->requested_item_id)->update('bs_items', $requested_item_stock_array);
                    }
                    $item_detail = $this->db->from('bs_items')->where('id', $posts_var['item_id'])->get()->row();
                    $stock_update = $item_detail->pieces - $qty;
                    $update_array['pieces'] = $stock_update;
                    if(!$stock_update) {
                        $update_array['is_sold_out'] = 1;
                    }
                    $this->db->where('id', $posts_var['item_id'])->update('bs_items', $update_array);
                    $this->db->insert('bs_order_confirm', ['order_id' => $new_odr_id, 'item_id' => $posts_var['item_id'], 'seller_id' => $item_detail->added_user_id, 'created_at' => $date]);
                    /*manage stock :end*/
                    
                    # set stripe test key
                    \Stripe\Stripe::setApiKey(trim($paid_config->stripe_secret_key));
                    try {
                        $response = \Stripe\PaymentIntent::create([
                            'amount' => round($item_price * 100),
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
//                            $this->tracking_order(['transaction_id' => $response->id, 'create_offer' => 0]);
                            $seller = $this->db->select('device_token,bs_items.title as item_name')->from('bs_items')
                                    ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                                    ->where('bs_items.id', $posts_var['item_id'])->get()->row();
                            
                            $item_images = $this->db->select('img_path')->from('core_images')->where('img_type', 'item')->where('img_parent_id', $posts_var['item_id'])->get()->row();
                            
                            $buyer = $this->db->select('device_token')->from('core_users')
                                    ->where('core_users.user_id', $offer_details->buyer_user_id)->get()->row();
                            $send_noti_user_token = $buyer->device_token;
                            if($offer_details->buyer_user_id == $posts_var['user_id']) {
                                $send_noti_user_token = $seller->device_token;
                            }
                            send_push( [$send_noti_user_token], ["message" => "New order placed", "flag" => "order",'title' => $seller->item_name],['image' => 'http://bacancy.com/biddingapp/uploads/'.$item_images->img_path,'order_id' =>$new_odr_id] );
                            $this->db->where('id',$posts_var['offer_id'])->update('bs_chat_history',['is_offer_complete' => 1,'order_id' => $new_odr_id]);
                            $response = $this->ps_security->clean_output( $response );
                            $this->response(['status' => "success", 'order_status' => 'success', 'order_type' => 'card', 'order_id' => $new_odr_id]);
                        } else {
                            $this->db->where('id', $record)->update('bs_order',['status' => 'fail']);
                            $this->db->insert('bs_stripe_error', ['order_id' => $record, 'response' => $response, 'created_at' => $date]);
                            $this->error_response(get_msg('stripe_transaction_failed'));
                        }
                    } catch (exception $e) {
                        $this->db->where('id', $record)->update('bs_order',['status' => 'fail']);
                        $this->db->insert('bs_stripe_error', ['order_id' => $record, 'response' => $e->getMessage(), 'created_at' => $date]);
                        $this->error_response(get_msg('stripe_transaction_failed'));
                    }

                } else if($delivery_method_id == PICKUP_ONLY) {
                    if($offer_details->operation_type == EXCHANGE) {
                        if(!isset($posts_var['delivery_address_id']) || empty($posts_var['delivery_address_id']) || is_null($posts_var['delivery_address_id'])) {
                            $this->error_response("Please pass delivery address id");
                        }
                        $delivery_address_id = $posts_var['delivery_address_id'];
                    }
//                    $item_detail = $this->db->from('bs_items')->where('id', $posts_var['item_id'])->get()->row();
                    $item_detail = $this->db->from('bs_items')->where('id', $offer_details->requested_item_id)->get()->row();
                    
//                    $date = date('Y-m-d H:i:s');
                    $this->db->insert('bs_order', ['order_id' => $new_odr_id, 'offer_id' => $posts_var['offer_id'],'user_id' => $order_user_id, 'items' => ($posts_var['item_id'] ?? $offer_details->requested_item_id), 'qty' => $qty, 'delivery_method' => $delivery_method_id, 'payment_method' => 'cash', 'card_id' => 0, 'address_id' => $delivery_address_id, 'seller_address_id' => $item_detail->Address_id, 'item_offered_price' => $item_price, 'service_fee' => $service_fee, 'processing_fee' => $processing_fees, 'seller_earn' => $seller_earn, 'shipping_amount' => $shipping_amount, 'total_amount' => $item_price, 'status' => 'succeeded', 'confirm_by_seller'=>1,'delivery_status' => 'pending', 'transaction' => '','created_at' => $date, 'processed_date' => $date,'operation_type' => $offer_details->operation_type]);
                    
                    $record = $this->db->insert_id();
                    /*manage stock :start*/
                    if(in_array($offer_details->operation_type, [DIRECT_BUY, REQUEST_ITEM]) ) {
                        $stock_update = $item_detail->pieces - $qty;
                    } else{
                        $stock_update = $item_detail->pieces - 1;
                    }
                    $update_array['pieces'] = $stock_update;
                    if(!$stock_update) {
                        $update_array['is_sold_out'] = 1;
                    }
                    $this->db->where('id', $offer_details->requested_item_id)->update('bs_items', $update_array);
                    $this->db->insert('bs_order_confirm', ['order_id' => $new_odr_id, 'item_id' => $offer_details->requested_item_id, 'seller_id' => $item_detail->added_user_id, 'created_at' => $date]);
                    if($offer_details->operation_type == EXCHANGE) {
                        $item_details = $this->db->select('bs_items.pieces,bs_items.id as item_id,bs_items.added_user_id')->from('bs_exchange_chat_history')
                            ->join('bs_items', 'bs_exchange_chat_history.offered_item_id = bs_items.id')
                            ->where('bs_exchange_chat_history.chat_id', $offer_details->id)
                            ->get()->result();
                        foreach($item_details as $key => $value) {
                            $stock_update = 0;
                            if($value->pieces > 1) {
                                $stock_update = $value->pieces - 1;
                            }
                            $update_array['pieces'] = $stock_update;
                            if(!$stock_update) {
                                $update_array['is_sold_out'] = 1;
                            }
                            $this->db->where('id', $value->item_id)->update('bs_items', $update_array);
                            $this->db->insert('bs_order_confirm', ['order_id' => $new_odr_id, 'item_id' => $value->item_id, 'seller_id' => $value->added_user_id, 'created_at' => $date]);
                        } 

                        $payin_check = $posts_var['payin'];
                    } else {
                        $payin_check = $offer_details->payin;
                    }
                    /*manage stock :end*/
                    if($payin_check == PAYCARD) {
                        $this->db->where('id', $record)->update('bs_order',['payment_method' => 'card', 'card_id' => $posts_var['card_id']]);
                        # set stripe test key
                        \Stripe\Stripe::setApiKey(trim($paid_config->stripe_secret_key));
                        try {
                            $response = \Stripe\PaymentIntent::create([
                                'amount' => round($item_price * 100),
                                "currency" => trim($paid_config->currency_short_form),
                                'payment_method' => $stripe_payment_method_id,
                                'payment_method_types' => ['card'],
                                'confirm' => true
                            ]);
                            $stripe_response = $response;
                            if (isset($response->id)) { 
                                if($response->status == 'requires_action') {
                                   $this->error_response('Transaction requires authorization');
                                }
                                $this->db->where('id', $record)->update('bs_order',['status' => $response->status, 'transaction_id' => $response->id,'payment_method' => 'card', 'card_id' => $card_id]);
                                
//                                $this->tracking_order(['transaction_id' => $response->id, 'create_offer' => 0]);
                                $seller = $this->db->select('device_token,bs_items.title as item_name')->from('bs_items')
                                        ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                                        ->where('bs_items.id', $offer_details->requested_item_id)->get()->row();

                                $item_images = $this->db->select('img_path')->from('core_images')->where('img_type', 'item')->where('img_parent_id', $offer_details->requested_item_id)->get()->row();

                                $buyer = $this->db->select('device_token')->from('core_users')
                                    ->where('core_users.user_id', $offer_details->buyer_user_id)->get()->row();
                                $send_noti_user_token = $buyer->device_token;
                                if($offer_details->buyer_user_id == $posts_var['user_id']) {
                                    $send_noti_user_token = $seller->device_token;
                                }
                                send_push( [$send_noti_user_token], ["message" => "New order placed", "flag" => "order",'title' => $seller->item_name],['image' => 'http://bacancy.com/biddingapp/uploads/'.$item_images->img_path,'order_id' => $new_odr_id] );
                                $this->db->where('id',$posts_var['offer_id'])->update('bs_chat_history',['is_offer_complete' => 1,'order_id' => $new_odr_id]);
                                $response = $this->ps_security->clean_output( $response );
                                $this->response(['status' => "success", 'order_status' => 'success', 'order_type' => 'card', 'order_id' => $new_odr_id]);
                            } else {
                                $this->db->where('id', $record)->update('bs_order',['status' => 'fail']);
                                $this->db->insert('bs_stripe_error', ['order_id' => $new_odr_id, 'chat_id' => $offer_details->id, 'response' => $stripe_response, 'created_at' => $date]);
                                $this->error_response(get_msg('stripe_transaction_failed'));
                            }
                        } catch (exception $e) {
                            $this->db->where('id', $record)->update('bs_order',['status' => 'fail']);
                            $this->db->insert('bs_stripe_error', ['order_id' => $new_odr_id, 'chat_id' => $offer_details->id, 'response' => $e->getMessage(), 'created_at' => $date]);
                            $this->error_response(get_msg('stripe_transaction_failed'));
                        } 
                    } else {
                        $this->db->where('id',$posts_var['offer_id'])->update('bs_chat_history',['is_offer_complete' => 1,'order_id' => $new_odr_id]);
                        
                        $seller = $this->db->select('device_token,bs_items.title as item_name')->from('bs_items')
                                ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                                ->where('bs_items.id', $offer_details->requested_item_id)->get()->row();

                        $item_images = $this->db->select('img_path')->from('core_images')->where('img_type', 'item')->where('img_parent_id', $offer_details->requested_item_id)->get()->row();

                        $buyer = $this->db->select('device_token')->from('core_users')
                          ->where('core_users.user_id', $offer_details->buyer_user_id)->get()->row();
                        $send_noti_user_token = $buyer->device_token;
                        if($offer_details->buyer_user_id == $posts_var['user_id']) {
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
                    if(!isset($posts_var['delivery_address_id']) || empty($posts_var['delivery_address_id']) || is_null($posts_var['delivery_address_id'])) {
                        $this->error_response("Please pass delivery address id");
                    }
                    if(!isset($posts_var['delivery_method_id']) || empty($posts_var['delivery_method_id']) || is_null($posts_var['delivery_method_id'])) {
                        $this->error_response("Please pass delivery method id");
                    }
                    if(!isset($posts_var['qty']) || empty($posts_var['qty']) || is_null($posts_var['qty'])) {
                        $this->error_response("Please pass qty");
                    }
                    $requested_item_id = $this->db->from('bs_chat_history')->where('id',$posts_var['offer_id'])->get()->row()->requested_item_id;
                    
                    $item_detail = $this->db->from('bs_items')->where('id', $requested_item_id)->get()->row();
                    $delivery_address_id = $posts_var['delivery_address_id'];
                    
//                    $date = date('Y-m-d H:i:s');
//                    if($posts_var['operation_type'] == EXCHANGE) {
//                    } else {
//                        $requested_item_id = $posts_var['item_id'];
//                    }
                    $item_price = $offer_details->nego_price;
                    
                    $backend_config = $this->Backend_config->get_one('be1');
                    $service_fee = ((float)$item_price * (float)$backend_config->selling_fees)/100;

                    $processing_fees = (((float)$item_price * (float)$backend_config->processing_fees)/100)+(float)$backend_config->processing_fees_amount;

                    $seller_earn = (float)$item_price - $service_fee - $processing_fees;
                    
                    $this->db->insert('bs_order', ['order_id' => $new_odr_id, 'offer_id' => $posts_var['offer_id'],'user_id' => $offer_details->buyer_user_id, 'items' => $requested_item_id,'qty' => $posts_var['qty'], 'delivery_method' => $posts_var['delivery_method_id'], 'payment_method' => 'cash', 'card_id' => 0, 'address_id' => $delivery_address_id, 'seller_address_id' => $item_detail->Address_id,'item_offered_price' => $item_price, 'service_fee' => $service_fee, 'processing_fee' => $processing_fees, 'seller_earn' => $seller_earn, 'shipping_amount' => $shipping_amount, 'total_amount' => $item_price, 'status' => 'succeeded', 'confirm_by_seller'=>1,'delivery_status' => 'pending', 'transaction' => '','created_at' => $date, 'processed_date' => $date,'operation_type' => $offer_details->operation_type]);
                    $record = $this->db->insert_id();

                    /*manage stock :start*/
                    $item_details = $this->db->select('bs_items.pieces,bs_items.id as item_id,bs_items.added_user_id')->from('bs_exchange_chat_history')->join('bs_items', 'bs_exchange_chat_history.offered_item_id = bs_items.id')->where('bs_exchange_chat_history.chat_id', $posts_var['offer_id'])->get()->result();
                    foreach($item_details as $key => $value) {
                        $stock_update = 0;
                        if($value->pieces > 1) {
                            $stock_update = $value->pieces - 1;
                        }
                        $update_array['pieces'] = $stock_update;
                        if(!$stock_update) {
                            $update_array['is_sold_out'] = 1;
                        }
                        $this->db->where('id', $value->item_id)->update('bs_items', $update_array);
                        $this->db->insert('bs_order_confirm', ['order_id' => $new_odr_id, 'item_id' => $value->item_id, 'seller_id' => $value->added_user_id, 'created_at' => $date]);
                    } 
                    $stock_update = 0;
                    if($item_detail->pieces > 1) {
                        $stock_update = $item_detail->pieces - 1;
                    }
                    $update_array['pieces'] = $stock_update;
                    if(!$stock_update) {
                        $update_array['is_sold_out'] = 1;
                    }
                    $this->db->where('id', $requested_item_id)->update('bs_items', $update_array);
                    $this->db->insert('bs_order_confirm', ['order_id' => $new_odr_id, 'item_id' => $requested_item_id, 'seller_id' => $item_detail->added_user_id, 'created_at' => $date]);
                    /*manage stock :end*/
                    
                    $this->db->where('id',$posts_var['offer_id'])->update('bs_chat_history',['is_offer_complete' => 1,'order_id' => $new_odr_id, 'delivery_address_id' => $delivery_address_id]);

                    $seller = $this->db->select('device_token,bs_items.title as item_name')->from('bs_items')
                            ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                            ->where('bs_items.id', $offer_details->requested_item_id)->get()->row();
                    
                    $buyer = $this->db->select('device_token')->from('core_users')
                            ->where('core_users.user_id', $offer_details->buyer_user_id)->get()->row();
                    send_push( [$buyer->device_token], ["message" => "New order placed", "flag" => "order", 'title' => $seller->item_name],['order_id' => $new_odr_id] );
                    
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
        $only_debit = $this->post('only_debit');
        $condition = "";
        $orderby = "";
        if(isset($only_debit) && $only_debit) {
            $condition = " and is_debit = 1";
            $orderby = " order by id asc";
        }
        $cards = $this->db->query("SELECT * FROM `bs_card` WHERE user_id = '".$user_id."' and status = 1 ".$condition." ".$orderby." LIMIT 1")->result();
        $cardData = $cards && $cards[0] ? $cards[0] : []; 
        //$this->ps_adapter->convert_card($cardData);
        $this->response($cardData);
    }

    public function tracking_order($param) {
        if(isset($param['transaction_id'])) {
            $get_records = $this->db->from('bs_order')->where('transaction_id', $param['transaction_id'])->get()->result();
        } elseif(isset($param['records'])) {
            $get_records = $this->db->from('bs_order')->where_in('id', $param['records'])->get()->result();
        }
        $track_number = '';
        $current_date = date("Y-m-d H:i:s");
        if(isset($param['generate_label'])) {
            foreach ($get_records as $key => $value) {
                $track_exist = $this->db->from('bs_track_order')->where('order_id', $value->order_id)->order_by('id','desc')->get()->row();
                if(empty($track_exist) || $track_exist->status == 'ERROR') {
                    $get_item = $this->db->from('bs_items')->where('id', $value->items)->get()->row();
                    if($get_item->pay_shipping_by == '1') {
                        if($get_item->shipping_type == '1') { 
                            $shippingcarriers_details = $this->db->from('bs_shippingcarriers')->where('id', $get_item->shippingcarrier_id)->get()->row();
                            $package_details = $this->db->from('bs_packagesizes')->where('id', $get_item->packagesize_id)->get()->row();
                            $buyer_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode,bs_addresses.id')->from('bs_order')
                                ->join('core_users', 'bs_order.user_id = core_users.user_id')
    //                            ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id')
                                ->join('bs_addresses', 'bs_order.address_id = bs_addresses.id')
                                ->where('order_id', $value->order_id)->get()->row();

                            $seller_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode,bs_addresses.id')->from('bs_order')
                            ->join('bs_items', 'bs_order.items = bs_items.id')
                            ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
    //                        ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id')
                            ->join('bs_addresses', 'bs_items.address_id = bs_addresses.id')
                            ->where('order_id', $value->order_id)->get()->row();
                            $ship_from = $seller_detail->id;
                            $ship_to = $buyer_detail->id;
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
                            $this->db->insert('bs_track_order', ['order_id' => $value->order_id, 'ship_from' => $ship_from, 'ship_to' => $ship_to, 'object_id' => (isset($response->object_id) ? $response->object_id: ''), 'status' => (isset($response->status) ? $response->status: 'ERROR'), 'tracking_status' => (isset($response->tracking_status) ? $response->tracking_status: ''), 'tracking_number' => (isset($response->tracking_number) ? $response->tracking_number: ''), 'tracking_url' => (isset($response->tracking_url_provider) ? $response->tracking_url_provider: ''), 'label_url' => (isset($response->label_url) ? $response->label_url: ''), 'response' => json_encode($response), 'created_at' => $current_date]);
                            $track_number = isset($response->tracking_number) ? $response->tracking_number:'';

    //                        if(is_null($track_number) || empty($track_number)) {
    //                //            $this->error_response("Something wrong with shipping provided detail");
    //                            $this->response(['status' => 'error', 'message' => 'Something wrong with shipping provided detail', 'response' => $response],404);
    //                        }
                        }
                    }
                }
            }
        }
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
            $this->db->insert('bs_order_confirm', ['order_id' => $value->order_id, 'item_id' => $value->items, 'seller_id' => $item_detail->added_user_id, 'created_at' => $current_date]);

            $this->db->where('user_id', $value->user_id)->where('item_id', $value->items)->delete('bs_cart');
        }
        if($param['create_offer']) {
//            $current_date = date("Y-m-d H:i:s");
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
                send_push( [$seller['device_token']], ["message" => "Buyer request for return item", "flag" => "order", "title" => $seller['item_name']." order update"],['order_id' => $posts['order_id']] );
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
            
            $seller = $this->db->select('core_users.device_token as seller_token, bs_items.title as item_name, buyer.device_token as buyer_token')->from('bs_items')
                        ->join('bs_order', 'bs_order.items = bs_items.id')
                        ->join('core_users as buyer', 'bs_order.user_id = buyer.user_id')
                        ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                        ->where('bs_order.order_id', $posts['order_id'])->get()->row_array();
            if(!empty($seller)) {
                send_push( [$seller->seller_token, $seller->buyer_token], ["message" => "Order return request canceled", "flag" => "order", 'title' => 'RETURN CANCELLED'],['order_id' => $posts['order_id']] );
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
                    
        $buyer_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode,device_token, bs_items.title,bs_addresses.id')->from('bs_order')
                ->join('bs_items', 'bs_order.items = bs_items.id')
                ->join('core_users', 'bs_order.user_id = core_users.user_id')
                ->join('bs_addresses', 'bs_order.address_id = bs_addresses.id')
                ->where('order_id', $posts['order_id'])->get()->row();
        $update_order['seller_response'] = $posts['seller_response'];
        $update_order['status'] = 'reject';
        $title = $buyer_detail->title. " order update";
        $message = "Seller denied return request";
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
                
                $shippingcarriers_details = $this->db->from('bs_shippingcarriers')->where('id', $get_item->shippingcarrier_id)->get()->row();
            
                $package_details = $this->db->from('bs_packagesizes')->where('id', $shippingcarriers_details->packagesize_id)->get()->row();

                $seller_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode,bs_addresses.id')->from('bs_order')
                    ->join('bs_items', 'bs_order.items = bs_items.id')
                    ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                    ->join('bs_addresses', 'bs_items.address_id = bs_addresses.id')
                    ->where('order_id', $posts['order_id'])->get()->row();
                $ship_from = $buyer_detail->id;
                $ship_to = $seller_detail->id;
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

                $this->db->insert('bs_track_order', ['order_id' => $posts['order_id'], 'ship_from' => $ship_from, 'ship_to' => $ship_to, 'object_id' => (isset($response->object_id) ? $response->object_id: ''), 'status' => (isset($response->status) ? $response->status: 'ERROR'), 'tracking_status' => (isset($response->tracking_status) ? $response->tracking_status: ''), 'tracking_number' => (isset($response->tracking_number) ? $response->tracking_number: ''), 'tracking_url' => (isset($response->tracking_url_provider) ? $response->tracking_url_provider: ''), 'label_url' => (isset($response->label_url) ? $response->label_url: ''), 'response' => json_encode($response), 'is_return' => 1,'created_at' => $date]);
                $track_number = isset($response->tracking_number) ? $response->tracking_number:'';
                
                if(is_null($track_number) || empty($track_number)) {
                    $this->response(['status' => 'error', 'message' => 'Something wrong with shipping provided detail', 'response' => $response],404);
                }
            } else if($get_item->shipping_type == '2'){
                $shipping_amount = $get_item->shipping_cost_by_seller;
            }
            if($shipping_amount) {
                if($get_item->pay_shipping_by == '1') {
                    $shipping_amount = $shipping_amount * 2;
                }
                if($check_for_order->service_fee) {
                    $shipping_amount += (float)$check_for_order->service_fee;
                }
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
                        $update_order['amount'] = $shipping_amount;
                        $update_order['status'] = 'accept';
                        $update_order['payment_status'] = $response->status;
                        $update_order['transaction_id'] = $response->id;
                        $update_order['payment_response'] = $response;
                        $message = "SELLER ACCEPTED RETURN REQUEST, TIME TO SHIP YOUR RETURN. PRINT THE LABEL AND RETURN WITHIN 3 DAYS";
                    } else {
                        $this->db->insert('bs_stripe_error', ['order_id' => $posts['order_id'], 'card_id' => $card_id, 'response' => $response, 'note' => 'return order shipping error', 'created_at' => date('Y-m-d H:i:s')]);
                        $this->error_response(get_msg('stripe_transaction_failed'));
                    }
                } catch (exception $e) {
                    $this->db->insert('bs_stripe_error', ['order_id' => $posts['order_id'], 'card_id' => $card_id, 'response' => $response,'note' => 'return order shipping error', 'created_at' => date('Y-m-d H:i:s')]);
                    $this->error_response(get_msg('stripe_transaction_failed'));
                }
            } else if($get_item->shipping_type == '2') {
                $update_order['status'] = 'accept';
                $message = "Seller accepted return request";
            }
        }
        $update_order['updated_at'] = $date;
        if(!$posts['status']) {
            $this->db->where('order_id', $posts['order_id'])->update('bs_order', ['dispute_expiry_date' => date('Y-m-d H:i:s', strtotime($date. ' + 3 days'))]);
        }
        $this->db->where('order_id', $posts['order_id'])->update('bs_return_order', $update_order);
        
        if(!empty($buyer_detail)) {
            send_push( [$buyer_detail->device_token], ["message" => $message, "flag" => "order", "title" => $title],['order_id' => $posts['order_id']] );
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
                'field' => 'user_id',
                'rules' => 'required'
            ),
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
        $check_order = $this->db->from('bs_order')->where('order_id', $posts['order_id'])->get()->row();
        $check_for_dispute = $this->db->from('bs_dispute')->where('order_id', $posts['order_id'])->where('status != "close"');
        if($check_order->user_id != $posts['user_id']) {
            $check_for_dispute = $check_for_dispute->where('is_seller_generate', 1);
        }
        $check_for_dispute = $check_for_dispute->get()->row();
        $message = "Dispute already registered";
        if(empty($check_for_dispute)) {
            $insert_arr['order_id']   = $posts['order_id'];
            $insert_arr['name']       = $posts['name'];
            $insert_arr['email']      = $posts['email'];
            $insert_arr['phone']      = $posts['phone'];
            $insert_arr['message']    = $posts['message'];
            $insert_arr['status']     = 'initiate';
            $insert_arr['created_at'] = $date;
            if($check_order->user_id != $posts['user_id']) {
                $insert_arr['is_seller_generate'] = 1;
                
                $update_order['is_seller_dispute'] = 1;
                $user_identy_string = 'seller';
            } else {
                $user_identy_string = 'buyer';
                $update_order['is_dispute'] = 1;
            }
            $this->db->insert('bs_dispute', $insert_arr);
            
            $update_order['dispute_date'] = $date;
            $this->db->where('order_id', $posts['order_id'])->update('bs_order', $update_order);
            $get_record = $this->db->select('buyer.device_token,seller.device_token as seller_device_token')
                ->from('bs_order')
                ->join('bs_items', 'bs_order.items = bs_items.id')
                ->join('core_users as buyer', 'bs_order.user_id = buyer.user_id')
                ->join('core_users as seller', 'bs_items.added_user_id = seller.user_id')
                ->where('bs_order.order_id', $posts['order_id'])
                ->get()->row();
            if(!empty($get_record)) {
                send_push( [$get_record->device_token, $get_record->seller_device_token], ["message" => "DISPUTE WAS OPENED BY ".$user_identy_string, "flag" => "order", 'title' => 'DISPUTE OPENED!'],['order_id' => $posts['order_id']] );
            }
            $message = 'Dispute generated against order';
        }
        
        $this->response(['status' => "success", 'message' => $message]);
    }
    
    public function wallet_history_post() {
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
                'field' => 'type',
                'rules' => 'required'
            ),
        );
        if (!$this->is_valid($rules)) exit; 
        $posts = $this->post();
//        $date = date('Y-m-d H:i:s');
        
        $get_detail = $this->db->select('bs_wallet.id,bs_wallet.parent_id as order_id,bs_wallet.user_id,bs_wallet.amount,bs_wallet.action,bs_wallet.type,bs_wallet.created_at,bs_items.title as item_name,core_users.user_name as sellername,buyer.user_name as buyername')
                ->from('bs_wallet')
                ->join('bs_order', 'bs_wallet.parent_id = bs_order.order_id', 'left')
                ->join('bs_items', 'bs_order.items = bs_items.id', 'left')
                ->join('core_users', 'bs_items.added_user_id = core_users.user_id', 'left')
                ->join('core_users as buyer', 'bs_order.user_id = buyer.user_id', 'left')
                ->where('bs_wallet.user_id', $posts['user_id']);
        if($posts['type'] == CREDIT) {
           $get_detail = $get_detail->where('bs_wallet.action', 'plus');
        } else if($posts['type'] == DEBIT){
           $get_detail = $get_detail->where('bs_wallet.action', 'minus')->where_not_in('bs_wallet.type', ['bank_deposit', 'instantpay']); 
        } else if($posts['type'] == DEPOSIT){
           $get_detail = $get_detail->where('bs_wallet.action', 'minus')->where_in('bs_wallet.type', ['bank_deposit', 'instantpay']);
        }
        
        $get_detail = $get_detail->order_by('created_at', 'desc')->get()->result_array();
        
        if(!empty($get_detail) && count($get_detail)) {
            $row = [];
            foreach ($get_detail as $key => $value) {
                $row[$key] = $value;
                if($posts['type'] == CREDIT) {
                    $row[$key]['type'] = 'credit';
                    if($value['type'] == 'refund' || $value['type'] == 'cancel_order_payment') {
                        $row[$key]['isRefund'] = '1';
                    }
                } else if($posts['type'] == DEBIT){
                    $row[$key]['type'] = 'debit';
                } else if($posts['type'] == DEPOSIT){
                    $row[$key]['type'] = 'deposit';
                    if($value['type'] == 'bank_deposit') {
                        $row[$key]['subtype'] = 'bank';
                        $row[$key]['account_number'] = $this->db->select('account_number')->from('bs_payouts')
                                ->join('bs_bankdetails', 'bs_payouts.external_account_id = bs_bankdetails.external_account_id')
                                        ->where('bs_payouts.id', $value['order_id'])->get()->row()->account_number;
                        $row[$key]['card_number'] = '';
                    } else if($value['type'] == 'instantpay') {
                        $row[$key]['subtype'] = 'instant';
                        $row[$key]['account_number'] = '';
                        $row[$key]['card_number'] = $this->db->select('bs_card.card_number')->from('bs_payouts')
                                    ->join('bs_card', 'bs_payouts.external_account_id = bs_card.stripe_card_id')
                                    ->where('bs_payouts.id', $value['order_id'])->get()->row()->card_number;

                     }
                } else if($posts['type'] == ALL){
                    if($value['action'] == 'plus') {
                        $row[$key]['type'] = 'credit';
                        if($value['type'] == 'refund' || $value['type'] == 'cancel_order_payment') {
                            $row[$key]['isRefund'] = '1';
                        }
                    } else if($value['action'] == 'minus' && $value['type'] != 'bank_deposit' && $value['type'] != 'instantpay') {
                        $row[$key]['type'] = 'debit';
                    } else if($value['action'] == 'minus') {
                        $row[$key]['order_id'] = '';
                        
                        if($value['type'] == 'bank_deposit') {
                            $row[$key]['type'] = 'deposit';
                            $row[$key]['subtype'] = 'bank';
                            $row[$key]['account_number'] = $this->db->select('account_number')->from('bs_payouts')
                                        ->join('bs_bankdetails', 'bs_payouts.external_account_id = bs_bankdetails.external_account_id')
                                        ->where('bs_payouts.id', $value['order_id'])->get()->row()->account_number;
                            $row[$key]['card_number'] = '';
                        } else if($value['type'] == 'instantpay') {
                            $row[$key]['type'] = 'deposit';
                            $row[$key]['subtype'] = 'instant';
                            $row[$key]['account_number'] = '';
                            $row[$key]['card_number'] = $this->db->select('bs_card.card_number')->from('bs_payouts')
                                        ->join('bs_card', 'bs_payouts.external_account_id = bs_card.stripe_card_id')
                                        ->where('bs_payouts.id', $value['order_id'])->get()->row()->card_number;
                            
                        }
                    }
                }
                unset($row[$key]['action']);
            }
            $this->response($row);
        } else {
            $this->error_response($this->config->item( 'record_not_found'));
        }
    }
    
    public function wallet_balance_post() {
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
        $posts = $this->post();
//        $date = date('Y-m-d H:i:s');
        
        $get_detail = $this->db->select('core_users.wallet_amount')->from('core_users')
                ->join('bs_wallet', 'core_users.user_id = bs_wallet.user_id')
                ->where('core_users.user_id', $posts['user_id'])
                ->get()->row_array();
        $response['wallet_amount'] = "0";
        if(!empty($get_detail) && count($get_detail)) {
            $response['wallet_amount'] = $get_detail['wallet_amount'];
        }
        $this->response($response);
    }
    
    public function return_confirm_delivery_post() {
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
        $posts = $this->post();
        $date = date('Y-m-d H:i:s');
        
        $track_order = $this->db->select('bs_track_order.*, bs_items.title as item_name')->from('bs_track_order')
                ->join('bs_order', 'bs_track_order.order_id = bs_order.order_id')
                ->join('bs_items', 'bs_order.items = bs_items.id')
                ->where('bs_order.order_id',$posts['order_id'])
                ->where('bs_track_order.is_return',1)
                ->get()->row_array();
        
        $update_order['delivery_status'] = "delivered";
        $update_order['completed_date'] = $date;
        $update_order['delivery_date'] = $date;
//        $update_order['return_expiry_date'] = date('Y-m-d H:i:s', strtotime($date. ' + 3 days'));
        $this->db->where('id', $track_order['id'])->update('bs_track_order',['tracking_status' => 'DELIVERED', 'updated_at' => $date]);
        if($track_order['is_return']) {
            $buyer_detail = $this->db->select('core_users.device_token,core_users.user_id,bs_order.total_amount, core_users.wallet_amount')->from('bs_order')
                ->join('core_users', 'bs_order.user_id = core_users.user_id')
                ->where('order_id', $posts['order_id'])->get()->row_array();
            
            if(!empty($buyer_detail)) {
                send_push( [$buyer_detail['device_token']], ["message" => "Seller has received the item", "flag" => "order", "title" => $track_order['item_name']." order udpate"],['order_id' => $posts['order_id']] );
            }
            $seller = $this->db->select('device_token,bs_items.title as item_name')->from('bs_items')
                ->join('bs_order', 'bs_order.items = bs_items.id')
                ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                ->where('bs_order.order_id', $posts['order_id'])->get()->row_array();
            if(!empty($seller)) {
                send_push( [$seller['device_token']], ["message" => "RETURN HAS BEEN DELIVERED", "flag" => "order", "title" => $track_order['item_name']." order udpate"],['order_id' => $posts['order_id']] );
            }
            $update_order['return_shipment_delivered_date'] = $date;
            $update_order['seller_dispute_expiry_date'] = date('Y-m-d H:i:s', strtotime($date. ' + 1 days'));
//            $this->db->insert('bs_wallet',['parent_id' => $posts['order_id'],'user_id' => $buyer_detail['user_id'],'action' => 'plus', 'amount' => $buyer_detail['total_amount'],'type' => 'refund', 'created_at' => $date]);
//
//            $wallet_amount = $buyer_detail['wallet_amount']+$buyer_detail['total_amount'];
//            $this->db->where('user_id', $buyer_detail['user_id'])->update('core_users',['wallet_amount' => $wallet_amount]);
        }
        $this->db->where('order_id', $posts['order_id'])->update('bs_order',$update_order);
        
        $this->response(['message'=>'status updated successfuly']);
    }
    
    public function coupans_post() {
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
        $posts = $this->post();
        
        $used_coupons = $this->db->select('coupon_id')->from('bs_order')->where('user_id',$posts['user_id'])
                ->where('coupon_id != 0')->get()->result_array();
        $coupans = $this->db->select('id,type,value,min_purchase_amount,created_at,end_at')
                ->from('bs_coupan')
                ->where('status', 1)
                ->group_start()
                    ->where('user_id', $posts['user_id'])
                    ->or_where('user_id IS NULL')
                ->group_end();
        if(!empty($used_coupons)) {
            $usedcoupan_ids = array_column($used_coupons, 'coupon_id');
            $coupans =  $coupans->where_not_in('id', $usedcoupan_ids);
        }
        $coupans = $coupans->order_by('id','desc')->get()->result_array();
        if(!empty($coupans) && count($coupans)) {
            $this->response($coupans);
        } else {
            $this->error_response($this->config->item( 'record_not_found'));
        }
        
    }
    
    public function save_shipfrom_post() {
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
                'field' => 'address_id',
                'rules' => 'required'
            ),
        );
        if (!$this->is_valid($rules)) exit; 
        $posts = $this->post();
        
        $this->db->where('order_id', $posts['order_id'])->update('bs_order',['seller_address_id' => $posts['address_id']]);
         $get_user = $this->db->select('seller.device_token as seller_device_token')
                ->from('bs_order')
                ->join('bs_items', 'bs_order.items = bs_items.id')
                ->join('core_users as seller', 'bs_items.added_user_id = seller.user_id')
                ->where('bs_order.order_id', $posts['order_id'])
                ->get()->row();
         if(!empty($get_user)) {
            send_push( [$get_user->seller_device_token], ["message" => "SHIPPING ADDRESS UPDATED", "flag" => "chat", 'title' => 'JUST TO BE SURE'],['order_id' => $posts['order_id']] );
         }
        $this->response(['status' => 'success','message' => 'Address updated successfuly', 'address_id' => $posts['address_id']]);
    }
    
//    public function payout_post() {
//        $user_data = $this->_apiConfig([
//            'methods' => ['POST'],
//            'requireAuthorization' => true,
//        ]);
//        
//        $rules = array(
//            array(
//                'field' => 'user_id',
//                'rules' => 'required'
//            ),
//        );
//        if (!$this->is_valid($rules)) exit; 
//        $posts = $this->post();
//        $paid_config = $this->Paid_config->get_one('pconfig1');
//        \Stripe\Stripe::setApiKey(trim($paid_config->stripe_secret_key));
//        try {
//             $response = \Stripe\PaymentMethod::create([
//                        'type' => 'card',
//                        'card' => [
//                            'number' => '4000056655665556',
//                            'exp_month' => 11,
//                            'exp_year' => 2023,
//                            'cvc' => 123
//                        ]
//                    ]);
//            $token = \Stripe\Token::create([
//                'card' => [
//                    'number' => '4000056655665556',
//                    'exp_month' => 11,
//                    'exp_year' => 2023,
//                    'currency' => 'USD'
//                ]
//            ]);
//            $response = \Stripe\Account::createExternalAccount('acct_1L0N7KQQj1y50Q0Z', [
//                ['external_account' => $token->id,
//                    ['default_for_currency' =>true]
//                ]
//            ]);
            
//            $ch = curl_init();
//
//            curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/accounts/acct_1L0PWAQMJEMjFNno');
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
//
//            curl_setopt($ch, CURLOPT_USERPWD, $paid_config->stripe_secret_key);
//
//            $result = curl_exec($ch);
//            if (curl_errno($ch)) {
//                echo 'Error:' . curl_error($ch);
//            }
//            curl_close($ch);
//            
//            dd($result);
//            $response = \Stripe\Account::deleteExternalAccount('acct_1L0PWAQMJEMjFNno','ba_1L0Pv1QMJEMjFNnocBh1h9iS');
//            $response = \Stripe\Account::retrieve('acct_1L6Aaa4JgigiO5UG');
//            echo $response->charges_enabled.'<br>';
//            echo $response->payouts_enabled.'<br>';
//            echo '<pre>';
//            print_r($response->requirements);
//            $response = \Stripe\Account::all();
//            $response = \Stripe\Account::create([
//                'type' => 'custom',
//                'country' => 'US',
//                'email' => 'brijesh.ramavat+6@bacancy.com',
//                'capabilities' => [
//                  'card_payments' => ['requested' => true],
//                  'transfers' => ['requested' => true],
//                ],
//                'business_type' => 'individual',
//                'business_profile' => [
//                    "mcc" => "5045",
//                    "support_url" => "http://18.208.167.164/index.php",
//                    "url" => "http://18.208.167.164/index.php",
//                ],
//                'individual' => [
//                    'address' => 
//                        [
//                          'line1' => '103 N Main St',
//                          'postal_code' => '21713',
//                          'state' => 'MA',
//                          'country' => 'US',
//                          'city' =>'Boonsboro'
//                        ],
//                    'dob' => 
//                    [
//                      'day' => 25,
//                      'month' => 8,
//                      'year' => 1995
//                    ],
//                    'email' => 'brijesh.ramavat+6@bacancy.com',
//                    'first_name' => 'bacancy brijesh',
//                    'last_name' => 'dev',
//                    'phone' => '2015550124',
//                    'ssn_last_4' => '0000'
//                ],
//                'external_account' => [
//                    'object' => 'bank_account',
//                    'country' => 'US',
//                    'currency' => 'USD',
//                    'account_holder_name' => 'brijesh_dev_bacancy',
//                    'account_holder_type' => 'individual',
//                    'routing_number' => '110000000',
//                    'account_number' => '000222222227',
//                ],
//                'tos_acceptance' => ['date' => time(), 'ip' => $_SERVER['REMOTE_ADDR']],
//            ]);
//            
////            ----------------
//            $response = \Stripe\Transfer::create([
//                'amount' => 4,
//                'currency' => 'usd',
//                'destination' => 'acct_1KyvwaQS3y5Ei3gJ',
//            ]);
////            ----------------
//            $response = \Stripe\Account::createExternalAccount('acct_1KyvwaQS3y5Ei3gJ',[
//                'external_account' => [
//                    'object' => 'card',
//                    'number' => '4000056655665556',
//                    'exp_month' => '11',
//                    'exp_year' => '2024',
//                    'currency' => 'USD',
//                    'default_for_currency' => true,
//                ],
//                ['external_account' => 'tok_visa_debit']
//                
//            ]);
////            ----------------
//            
//            $response = \Stripe\Balance::retrieve();
//            $response = \Stripe\Payout::create([
//                'amount' => 2,
//                'currency' => 'usd',
//                'method' => 'instant',
//              ], [
//                'stripe_account' => 'acct_1KyvwaQS3y5Ei3gJ',
//            ]);
////            ----------------
//            $response = \Stripe\EphemeralKey::create(['customer' => 'cus_JqpKR8z1pZ3QcB'], ['stripe_version' => '2020-08-27']);
//            $response = \Stripe\Charge::create(array( 
//                'customer' => 'cus_JqpKR8z1pZ3QcB', 
//                'amount'   => 5*100, 
//                'currency' => 'USD', 
//            )); 
//            
//             $response = \Stripe\PaymentIntent::create([
//                'amount' => 1099,
//                'currency' => 'usd',
//                'application_fee_amount' => 200,
//                'payment_method' => 'pm_1KvzkQ2eZvKYlo2C9BL7g2vU',
//                'payment_method_types' => ['card'],
//                'on_behalf_of' => 'acct_1KtUQOPAThacYQMR',
//                'transfer_data' => [
//                  'destination' => 'acct_1KtUQOPAThacYQMR',
//                ],
//                 
//              ]);
//
//            dd($response);
//        }catch (exception $e) {
//            $this->error_response($e->getMessage());
//        }
//    }
    
    public function seller_shippment_post() {
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
                'field' => 'address_id',
                'rules' => 'required'
            ),
        );
        if (!$this->is_valid($rules)) exit; 
        $posts = $this->post();
        $date = date('Y-m-d H:i:s');
        $get_records = $this->db->from('bs_order')->where('order_id', $posts['order_id'])->get()->row();
        
        $track_exist = $this->db->from('bs_track_order')->where('order_id', $get_records->order_id)->order_by('id','desc')->get()->row();
        if(empty($track_exist) || $track_exist->status == 'ERROR') {
            $get_item = $this->db->from('bs_items')->where('id', $get_records->items)->get()->row();
            if($get_item->pay_shipping_by == '1') {
                if($get_item->shipping_type == '1') { 
                    $shippingcarriers_details = $this->db->from('bs_shippingcarriers')->where('id', $get_item->shippingcarrier_id)->get()->row();
                    $package_details = $this->db->from('bs_packagesizes')->where('id', $get_item->packagesize_id)->get()->row();
                    $buyer_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode')->from('bs_order')
                        ->join('core_users', 'bs_order.user_id = core_users.user_id')
//                            ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id')
                        ->join('bs_addresses', 'bs_order.address_id = bs_addresses.id')
                        ->where('order_id', $get_records->order_id)->get()->row();

                    $seller_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode')->from('core_users')
                    ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id')
                    ->where('bs_addresses.id', $posts['address_id'])->get()->row();
                    $ship_from = $posts['address_id'];
                    $ship_to = $get_records->address_id;
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
                    $this->db->insert('bs_track_order', ['order_id' => $get_records->order_id, 'ship_from' => $ship_from, 'ship_to' => $ship_to,'object_id' => (isset($response->object_id) ? $response->object_id: ''), 'status' => (isset($response->status) ? $response->status: 'ERROR'), 'tracking_status' => (isset($response->tracking_status) ? $response->tracking_status: ''), 'tracking_number' => (isset($response->tracking_number) ? $response->tracking_number: ''), 'tracking_url' => (isset($response->tracking_url_provider) ? $response->tracking_url_provider: ''), 'label_url' => (isset($response->label_url) ? $response->label_url: ''), 'response' => json_encode($response), 'created_at' => $date]);
                    $track_number = isset($response->tracking_number) ? $response->tracking_number:'';

                    if(is_null($track_number) || empty($track_number)) {
                        $this->response(['status' => 'error', 'message' => 'Something wrong with shipping provided detail', 'response' => $response],404);
                    }
                    
                    if(!is_null($track_number) && !empty($track_number)) {
                        $update_order['processed_date'] = $date;
                        $this->db->where('order_id', $get_records->order_id)->update('bs_order',$update_order);
                    }
                }
            }
        }
        $this->response(['status' => "success", 'message' => 'shipping label generated']);
    }
    
    public function add_bankdetail_post() {
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
                'field' => 'account_holder_name',
                'rules' => 'required'
            ),
            array(
                'field' => 'routing_number',
                'rules' => 'required'
            ),
            array(
                'field' => 'account_number',
                'rules' => 'required'
            ),
        );
        if (!$this->is_valid($rules)) exit; 
        $posts = $this->post();
        $date = date('Y-m-d H:i:s');
        $is_record_exist = $this->db->from('bs_bankdetails')
                   ->where('user_id', $posts['user_id'])
                   ->where('routing_number', $posts['routing_number'])
                   ->where('account_number', $posts['account_number'])
                   ->where('status', 1)
                   ->get()->num_rows();
        if($is_record_exist) {
            $this->response(['status' => 'error', 'message' => 'Account already exists'],404);
        }
            
        $this->db->insert('bs_bankdetails', ['user_id' => $posts['user_id'], 'account_holder_name' => $posts['account_holder_name'], 'routing_number' => $posts['routing_number'], 'account_number' => $posts['account_number'],'created_at' => $date,'updated_at' => $date]);
        $record_id = $this->db->insert_id();
        
        $check_record = $this->db->from('bs_bankdetails')->where('user_id', $posts['user_id'])->where('status', 1)->get()->num_rows();
        $paid_config = $this->Paid_config->get_one('pconfig1');
        \Stripe\Stripe::setApiKey(trim($paid_config->stripe_secret_key));
            
        if($check_record == 1) {
            $this->db->where('id', $record_id)->update('bs_bankdetails',['is_default' => 1]);
            
            $get_user = $this->db->select('core_users.user_name, core_users.user_email, core_users.user_phone,CONCAT(bs_addresses.address1," ",bs_addresses.address2) as line1, bs_addresses.zipcode, bs_addresses.state, bs_addresses.country_code, bs_addresses.city, bs_addresses.ssn')->from('core_users')
                    ->join('bs_addresses', 'core_users.user_id = bs_addresses.user_id')
                    ->where('core_users.user_id', $posts['user_id'])
                    ->where('bs_addresses.is_default_address', 1)
                    ->where('bs_addresses.status', 1)->get()->row();
            
            $business_profile = [
                "mcc" => "7278",
                "support_url" => "https://www.google.com",
                "url" => "https://www.google.com/",
            ];
            $dob = [
                'day' => 1,
                'month' => 1,
                'year' => 1965
            ];
            $ssn = substr(base64_decode($get_user->ssn),-4);
            $connect_id = '';
            try{
                $response = \Stripe\Account::create([
                    'type' => 'custom',
                    'country' => 'US',
                    'email' => $get_user->user_email,
                    'capabilities' => [
                      'card_payments' => ['requested' => true],
                      'transfers' => ['requested' => true],
                    ],
                    'business_type' => 'individual',
                    'business_profile' => $business_profile,
                    'individual' => [
                        'address' => 
                            [
                              'line1' => $get_user->line1,
                              'postal_code' => $get_user->zipcode,
                              'state' => $get_user->state,
                              'country' => $get_user->country_code,
                              'city' => $get_user->city
                            ],
                        'dob' => $dob,
                        'email' => $get_user->user_email,
                        'first_name' => $get_user->user_name,
                        'last_name' => 'user',
                        'phone' => empty($get_user->user_phone) ? '2015550124' : $get_user->user_phone,
                        'ssn_last_4' => empty($ssn) ? '0000' : $ssn
//                        'ssn_last_4' => '0000'
                    ],
//                    'external_account' => [
//                        'object' => 'bank_account',
//                        'country' => 'US',
//                        'currency' => 'USD',
//                        'account_holder_name' => $posts['account_holder_name'],
//                        'account_holder_type' => 'individual',
//                        'routing_number' => $posts['routing_number'],
//                        'account_number' => $posts['account_number'],
//                    ],
                    'tos_acceptance' => ['date' => time(), 'ip' => $_SERVER['REMOTE_ADDR']],
                ]);
                $connect_id = $response->id;
                $this->db->where('user_id', $posts['user_id'])->update('core_users',['connect_id' => $connect_id]);
                $bank_account = \Stripe\Account::createExternalAccount($connect_id, [
                    'external_account' => [
                        'object' => 'bank_account',
                        'country' => 'US',
                        'currency' => 'USD',
                        'account_holder_name' => $posts['account_holder_name'],
                        'account_holder_type' => 'individual',
                        'routing_number' => $posts['routing_number'],
                        'account_number' => $posts['account_number'],
                        'default_for_currency' => true,
                    ]
                ]);
                if(isset($bank_account->id)) {
                    $this->db->where('id', $record_id)->update('bs_bankdetails',['external_account_id' => $bank_account->id, 'updated_at' => $date]);
                }
                do {
                    $get_account = \Stripe\Account::retrieve($connect_id);
                    if(!empty($get_account->requirements->errors)) {
                        delete_connect_account($connect_id, trim($paid_config->stripe_secret_key));
                        $this->db->insert('bs_stripe_error', ['user_id' => $posts['user_id'], 'connect_id' => $connect_id, 'response' => $get_account, 'note' => $this->router->fetch_class().'/'.$this->router->fetch_method(), 'created_at' => $date]);
                        $this->db->where('id', $record_id)->delete('bs_bankdetails');
                        $this->response(['status' => "error", 'message' => 'Connect account creation failed', 'response' => $get_account->requirements],404);
                        break;
                    }
                }while($get_account->charges_enabled && $get_account->payouts_enabled); 
//                if(!$get_account->charges_enabled || !$get_account->payouts_enabled) {
//                    delete_connect_account($connect_id, trim($paid_config->stripe_secret_key));
//                    $this->db->insert('bs_stripe_error', ['user_id' => $posts['user_id'], 'connect_id' => $connect_id, 'response' => $get_account, 'note' => $this->router->fetch_class().'/'.$this->router->fetch_method(), 'created_at' => $date]);
//                    $this->db->where('id', $record_id)->delete('bs_bankdetails');
//                    $this->response(['status' => "error", 'message' => 'Connect account creation failed', 'response' => $get_account->requirements],404);
//                }
            } catch (exception $e) {
                $this->db->insert('bs_stripe_error', ['user_id' => $posts['user_id'],'response' => $e->getMessage(), 'note' => $this->router->fetch_class().'/'.$this->router->fetch_method(), 'created_at' => $date]);
                $this->db->where('id', $record_id)->delete('bs_bankdetails');
                delete_connect_account($connect_id, trim($paid_config->stripe_secret_key));
                $this->response(['status' => 'error', 'message' => $e->getMessage()],404);
            }
        } else {
            $get_record = $this->db->select('connect_id')->from('core_users')->where('user_id', $posts['user_id'])->get()->row();
            try{
                $bank_account = \Stripe\Account::createExternalAccount($get_record->connect_id, [
                    'external_account' => [
                        'object' => 'bank_account',
                        'country' => 'US',
                        'currency' => 'USD',
                        'account_holder_name' => $posts['account_holder_name'],
                        'account_holder_type' => 'individual',
                        'routing_number' => $posts['routing_number'],
                        'account_number' => $posts['account_number'],
                    ]
                ]);
                if(isset($bank_account->id)) {
                    $this->db->where('id', $record_id)->update('bs_bankdetails',['external_account_id' => $bank_account->id, 'updated_at' => $date]);
                }
            } catch(Exception $e) {
                $this->db->insert('bs_stripe_error', ['user_id' => $posts['user_id'],'response' => $e->getMessage(), 'note' => $this->router->fetch_class().'/'.$this->router->fetch_method(), 'created_at' => $date]);
                $this->db->where('id', $record_id)->delete('bs_bankdetails');
                $this->response(['status' => 'error', 'message' => $e->getMessage()],404);
            }
        }
        $this->response(['status' => "success", 'message' => 'Bank detail saved', 'response' => $response]);
    }
    
    public function banklist_post() {
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
        $posts = $this->post();
        $check_record = $this->db->select('id,user_id,account_holder_name,routing_number,account_number,external_account_id,is_default,created_at')->from('bs_bankdetails')->where('user_id', $posts['user_id'])->where('status', 1)->get()->result_array();
        if(!empty($check_record) && count($check_record)) {
            $this->response($check_record);
        } else {
            $this->error_response("Record not found");
        }
    }
    
    public function remove_bankdetail_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        
        $rules = array(
            array(
                'field' => 'record_id',
                'rules' => 'required'
            ),
        );
        if (!$this->is_valid($rules)) exit; 
        $posts = $this->post();
        $check_record = $this->db->select('bs_bankdetails.user_id,bs_bankdetails.is_default,bs_bankdetails.external_account_id,core_users.connect_id')->from('bs_bankdetails')
                ->join('core_users', 'bs_bankdetails.user_id = core_users.user_id')
                ->where('bs_bankdetails.id', $posts['record_id'])
                ->get()->row();
        
        $paid_config = $this->Paid_config->get_one('pconfig1');
        \Stripe\Stripe::setApiKey(trim($paid_config->stripe_secret_key));
        if($check_record->is_default) {
            $get_record = $this->db->select('id,external_account_id')->from('bs_bankdetails')->where('user_id', $check_record->user_id)->where('id !=', $posts['record_id'])->where('status', 1)->get()->row();
            if(!empty($get_record)) {
                $this->db->where('id', $get_record->id)->update('bs_bankdetails',['is_default' => 1]);
                if(!empty($get_record->external_account_id) && !is_null($get_record->external_account_id)) {
                    try {
                        \Stripe\Account::updateExternalAccount(
                                $check_record->connect_id,
                                $get_record->external_account_id,
                                ['default_for_currency' => true]
                        );
                    } catch (Exception $e) {
                        $this->error_response($e->getMessage());
                    }
                }
            }
        }
        $this->db->where('id', $posts['record_id'])->update('bs_bankdetails',['is_default' => 0,'status' => 0]);
        
        try {
            \Stripe\Account::deleteExternalAccount($check_record->connect_id,$check_record->external_account_id);
            $this->db->where('user_id', $check_record->user_id)->update('core_users',['connect_id' => null]);
        } catch (Exception $e) {
            $this->error_response($e->getMessage());
        }
        $this->response(['status' => "success", 'message' => 'Bank detail removed']);
    }
    
    public function get_default_bankaccount_post() {
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
        $posts = $this->post();
        
        $check_record = $this->db->select('id,user_id,account_holder_name,routing_number,account_number,external_account_id,is_default,created_at')->from('bs_bankdetails')->where('user_id', $posts['user_id'])->where('status', 1)->where('is_default', 1)->get()->row_array();
        if(!empty($check_record) && count($check_record)) {
            $this->response($check_record);
        } else {
            $this->error_response("Record not found");
        }
    }
    
    public function bank_transfer_post() {
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
                'field' => 'transfer_type',
                'rules' => 'required'
            ),
//            array(
//                'field' => 'external_account_id',
//                'rules' => 'required'
//            ),
            array(
                'field' => 'amount',
                'rules' => 'required'
            ),
        );
        if (!$this->is_valid($rules)) exit; 
        $posts = $this->post();
        $date = date('Y-m-d H:i:s');
        $get_current_balance = $this->db->select('wallet_amount,connect_id,device_token')->from('core_users')->where('user_id', $posts['user_id'])->get()->row();
        
        if($get_current_balance->wallet_amount && $get_current_balance->wallet_amount > $posts['amount']) {
            $paid_config = $this->Paid_config->get_one('pconfig1');
            \Stripe\Stripe::setApiKey(trim($paid_config->stripe_secret_key));
            if($posts['transfer_type'] == 'bank_transfer') {
                if(!isset($posts['external_account_id']) || empty($posts['external_account_id']) || is_null($posts['external_account_id'])) {
                    $this->error_response("Please pass external_account_id");
                }
                try {
                    \Stripe\Account::updateExternalAccount(
                        $get_current_balance->connect_id,
                        $posts['external_account_id'],
                        ['default_for_currency' => true]
                    );

                    $response = \Stripe\Transfer::create([
                        'amount' => $posts['amount']*100,
                        'currency' => 'usd',
                        'destination' => $get_current_balance->connect_id,
                    ]);   

                    $this->db->insert('bs_payouts',['user_id' => $posts['user_id'],'connect_id' => $get_current_balance->connect_id, 'external_account_id' => $posts['external_account_id'], 'amount' => $posts['amount'],'response' => $response, 'created_at' => $date]);
                    $record_id = $this->db->insert_id();

                    $this->db->insert('bs_wallet',['parent_id' => $record_id,'user_id' => $posts['user_id'],'action' => 'minus', 'amount' => $posts['amount'],'type' => 'bank_deposit', 'created_at' => $date]);

                    $this->db->where('user_id', $posts['user_id'])->update('core_users',['wallet_amount' => $get_current_balance->wallet_amount - $posts['amount']]);
                    
                    send_push( [$get_current_balance->device_token], ["message" => "YOU HAVE INITIATE YOUR BALANCE TO YOUR BANK ACC", "flag" => "transfer", 'title' => 'JUST TO BE SURE'] );

                    $this->response(['status' => "success", 'message' => 'Amount transfered']);

                } catch (Exception $e) {
                    $this->db->insert('bs_stripe_error', ['user_id' => $posts['user_id'],'response' => $e->getMessage(), 'note' => $this->router->fetch_class().'/'.$this->router->fetch_method(), 'created_at' => $date]);
                    $this->error_response($e->getMessage());
                }
            } else{
                if(!isset($posts['card_id']) || empty($posts['card_id']) || is_null($posts['card_id'])) {
                    $this->error_response("Please pass card id");
                }
                $card_details = $this->db->from('bs_card')->where('id', $posts['card_id'])->get()->row();
                $expiry_date = explode('/',$card_details->expiry_date);
                try {
                    if(is_null($card_details->stripe_card_id)) {
                        $token = \Stripe\Token::create([
                            'card' => [
                                'number' => $card_details->card_number,
                                'exp_month' => $expiry_date[0],
                                'exp_year' => $expiry_date[1],
                                'currency' => 'USD'
                            ]
                        ]);
                        
                        $response = \Stripe\Account::createExternalAccount($get_current_balance->connect_id, [
                            ['external_account' => $token->id,
                                ['default_for_currency' => true]
                            ]
                        ]);
                        $this->db->where('id', $posts['card_id'])->update('bs_card',['stripe_card_id' => $response->id]);
                        $stripe_card_id = $response->id;
                    } else {
                        \Stripe\Account::updateExternalAccount(
                                $get_current_balance->connect_id, 
                                $card_details->stripe_card_id, 
                                ['default_for_currency' => true]
                        );
                        $stripe_card_id = $card_details->stripe_card_id;
                    }

                    $payout = \Stripe\Payout::create([
                        'amount' => $posts['amount']*100,
                        'currency' => 'usd',
                        'method' => 'instant',
                      ], [
                        'stripe_account' => $get_current_balance->connect_id,
                    ]);
                    
                    $this->db->insert('bs_payouts',['user_id' => $posts['user_id'],'connect_id' => $get_current_balance->connect_id, 'external_account_id' => $stripe_card_id, 'amount' => $posts['amount'],'response' => $payout, 'created_at' => $date]);
                    $record_id = $this->db->insert_id();

                    $this->db->insert('bs_wallet',['parent_id' => $record_id,'user_id' => $posts['user_id'],'action' => 'minus', 'amount' => $posts['amount'],'type' => 'instantpay', 'created_at' => $date]);

                    $this->db->where('user_id', $posts['user_id'])->update('core_users',['wallet_amount' => $get_current_balance->wallet_amount - $posts['amount']]);
                    
                    send_push( [$get_current_balance->device_token], ["message" => "YOU HAVE INITIATE YOUR BALANCE TO YOUR BANK ACC", "flag" => "transfer", 'title' => 'JUST TO BE SURE'] );
                    
                    $this->response(['status' => "success", 'message' => 'Amount transfered', 'payout' => $payout]);
                }catch(Exception $e) {
                    $this->db->insert('bs_stripe_error', ['user_id' => $posts['user_id'],'response' => $e->getMessage(), 'note' => $this->router->fetch_class().'/'.$this->router->fetch_method(), 'created_at' => $date]);
                    $this->error_response($e->getMessage());
                }
            }
        } else {
            $this->error_response('Not enough balance');
        }
    }
    
    public function return_confirm_shipping_post() {
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
        
        $this->db->where('order_id', $posts['order_id'])->update('bs_order', ['return_shipment_initiate_date' => $date]);
        
        $seller = $this->db->select('device_token,bs_items.title as item_name')->from('bs_items')
            ->join('bs_order', 'bs_order.items = bs_items.id')
            ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
            ->where('bs_order.order_id', $posts['order_id'])->get()->row_array();
        
        send_push( [$seller['device_token']], ["message" => "Buyer has shipped the item", "flag" => "order", 'title' => $seller['item_name'].' status update'],['order_id' => $posts['order_id']] );
        
        $this->response(['status' => "success", 'message' => 'Shipment initiated']);
    }
    
    public function seller_dispute_payment_post() {
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
                'field' => 'card_id',
                'rules' => 'required'
            ),
            array(
                'field' => 'cvc',
                'rules' => 'required'
            ),
        );
        if (!$this->is_valid($rules)) exit; 
        $posts = $this->post();
        $date = date('Y-m-d H:i:s');
        
        $buyer_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode,device_token, bs_items.title, bs_addresses.id')->from('bs_order')
                ->join('bs_items', 'bs_order.items = bs_items.id')
                ->join('core_users', 'bs_order.user_id = core_users.user_id')
                ->join('bs_addresses', 'bs_order.address_id = bs_addresses.id')
                ->where('order_id', $posts['order_id'])->get()->row();

        $check_for_order = $this->db->from('bs_order')->where('order_id', $posts['order_id'])->get()->row();
        
        $get_item = $this->db->select('pay_shipping_by,shipping_type,shippingcarrier_id,shipping_cost_by_seller')->from('bs_items')->where('id', $check_for_order->items)->get()->row();
        
         if($get_item->shipping_type == '1') {
            $get_shiping_detail = $this->db->from('bs_shippingcarriers')->where('id', $get_item->shippingcarrier_id)->get()->row();

            $shipping_amount = $get_shiping_detail->price;

            $shippingcarriers_details = $this->db->from('bs_shippingcarriers')->where('id', $get_item->shippingcarrier_id)->get()->row();

            $package_details = $this->db->from('bs_packagesizes')->where('id', $shippingcarriers_details->packagesize_id)->get()->row();

            $seller_detail = $this->db->select('user_name,user_email,user_phone,bs_addresses.address1,bs_addresses.address2,bs_addresses.city,bs_addresses.state,bs_addresses.country,bs_addresses.zipcode,bs_addresses.id')->from('bs_order')
                ->join('bs_items', 'bs_order.items = bs_items.id')
                ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                ->join('bs_addresses', 'bs_items.address_id = bs_addresses.id')
                ->where('order_id', $posts['order_id'])->get()->row();
            $ship_from = $buyer_detail->id;
            $ship_to = $seller_detail->id;
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

            $this->db->insert('bs_track_order', ['order_id' => $posts['order_id'], 'ship_from' => $ship_from, 'ship_to' => $ship_to, 'object_id' => (isset($response->object_id) ? $response->object_id: ''), 'status' => (isset($response->status) ? $response->status: 'ERROR'), 'tracking_status' => (isset($response->tracking_status) ? $response->tracking_status: ''), 'tracking_number' => (isset($response->tracking_number) ? $response->tracking_number: ''), 'tracking_url' => (isset($response->tracking_url_provider) ? $response->tracking_url_provider: ''), 'label_url' => (isset($response->label_url) ? $response->label_url: ''), 'response' => json_encode($response), 'is_return' => 1,'created_at' => date('Y-m-d H:i:s')]);
            $track_number = isset($response->tracking_number) ? $response->tracking_number:'';

            if(is_null($track_number) || empty($track_number)) {
                $this->response(['status' => 'error', 'message' => 'Something wrong with shipping provided detail', 'response' => $response],404);
            }
        } else if($get_item->shipping_type == '2'){
            $shipping_amount = $get_item->shipping_cost_by_seller;
        }
        if($shipping_amount) {
            if($get_item->pay_shipping_by == '1') {
                $shipping_amount = $shipping_amount * 2;
            }
            if($check_for_order->service_fee) {
                $shipping_amount += (float)$check_for_order->service_fee;
            }
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
                    $update_order['amount'] = $shipping_amount;
                    $update_order['status'] = 'accept';
                    $update_order['payment_status'] = $response->status;
                    $update_order['transaction_id'] = $response->id;
                    $update_order['payment_response'] = $response;
                    $message = "Seller accepted return request";
                } else {
                    $this->db->insert('bs_stripe_error', ['order_id' => $posts['order_id'], 'card_id' => $card_id, 'response' => $response, 'note' => 'return order shipping error', 'created_at' => date('Y-m-d H:i:s')]);
                    $this->error_response(get_msg('stripe_transaction_failed'));
                }
            } catch (exception $e) {
                $this->db->insert('bs_stripe_error', ['order_id' => $posts['order_id'], 'card_id' => $card_id, 'response' => $response,'note' => 'return order shipping error', 'created_at' => date('Y-m-d H:i:s')]);
                $this->error_response(get_msg('stripe_transaction_failed'));
            }
        } else if($get_item->shipping_type == '2') {
            $message = "Seller accepted return request";
        }
        $update_order['updated_at'] = $date;
        
        $this->db->where('order_id', $posts['order_id'])->where('is_seller_generate', 0)->update('bs_dispute', $update_order);
        $title = $buyer_detail->title. " order update";
        if(!empty($buyer_detail)) {
            send_push( [$buyer_detail->device_token], ["message" => $message, "flag" => "order", "title" => $title],['order_id' => $posts['order_id']] );
        }
        
        $this->response(['status' => "success", 'message' => $message]);
    }
    
    public function cancel_order_post() {
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
                'field' => 'user_id',
                'rules' => 'required'
            ),
        );
        if (!$this->is_valid($rules)) exit; 
        $posts = $this->post();
        $date = date('Y-m-d H:i:s');
        
        $check_for_order = $this->db->select('bs_order.*, bs_items.added_user_id, bs_items.title, seller.device_token as seller_token, buyer.device_token as buyer_token, bs_items.delivery_method_id, bs_items.pieces, seller.wallet_amount as seller_wallet_amount, buyer.wallet_amount as buyer_wallet_amount')->from('bs_order')
                ->join('bs_items', 'bs_order.items = bs_items.id')
                ->join('core_users as seller', 'bs_items.added_user_id = seller.user_id')
                ->join('core_users as buyer', 'bs_order.user_id = buyer.user_id')
                ->where('order_id', $posts['order_id'])->get()->row();
        if($check_for_order->delivery_status == 'pending') {
            $refund_total_amount = $check_for_order->total_amount;
            $update_order['delivery_status'] = 'cancel';
            $update_order['is_cancel'] = 1;
            $update_order['cancel_by'] = 'buyer';
            $update_order['cancel_date'] = $date;
            $update_order['completed_date'] = $date;
            $noti_user = $check_for_order->seller_token;
            
            if($check_for_order->added_user_id == $posts['user_id']) {
                $update_order['cancel_by'] = 'seller';
                $noti_user = $check_for_order->buyer_token;
                if($check_for_order->pay_shipping_by == '2' && !empty($check_for_order->seller_charge)) {
                    $refund_total_amount = $check_for_order->seller_charge;
                    
                    $this->db->insert('bs_wallet',['parent_id' => $posts['order_id'],'user_id' => $posts['user_id'], 'action' => 'plus', 'amount' => $refund_total_amount, 'type' => 'cancel_order_payment', 'created_at' => $date]);
                    $this->db->where('user_id', $posts['user_id'])->update('core_users',['wallet_amount' => $check_for_order->seller_wallet_amount + (float)$refund_total_amount]);
                }
            } else {
                $this->db->insert('bs_wallet',['parent_id' => $posts['order_id'],'user_id' => $check_for_order->user_id, 'action' => 'plus', 'amount' => $refund_total_amount, 'type' => 'cancel_order_payment', 'created_at' => $date]);
            
                $this->db->where('user_id', $check_for_order->user_id)->update('core_users',['wallet_amount' => $check_for_order->buyer_wallet_amount + (float)$refund_total_amount]);
            }
            
            $this->db->where('order_id', $posts['order_id'])->update('bs_order', $update_order);
            $this->db->where('id', $check_for_order->items)->update('bs_items', ['pieces' => $check_for_order->pieces+(int)$check_for_order->qty,'is_sold_out' => 0]);
            if($check_for_order->operation_type == REQUEST_ITEM) {
                if(!empty($check_for_order->offer_id)) {
                    $get_request_item = $this->db->select('bs_chat_history.requested_item_id, bs_chat_history.quantity, bs_items.pieces')->from('bs_chat_history')
                            ->join('bs_items', 'bs_chat_history.requested_item_id = bs_items.id')
                            ->where('bs_chat_history.id', $check_for_order->offer_id)->get()->row();
                    if(!empty($get_request_item)) {
                        $offer_qty = (int)$get_request_item->quantity;
                        if(!$offer_qty) {
                            $offer_qty = 1;
                        }
                        $this->db->where('id', $get_request_item->requested_item_id)->update('bs_items', ['pieces' => $get_request_item->pieces+$offer_qty,'is_sold_out' => 0]);
                    }
                }
            }
            
            if($check_for_order->operation_type == EXCHANGE) {
                $item_details = $this->db->select('bs_items.pieces,bs_items.id as item_id,bs_items.added_user_id')->from('bs_exchange_chat_history')->join('bs_items', 'bs_exchange_chat_history.offered_item_id = bs_items.id')->where('bs_exchange_chat_history.chat_id',$check_for_order->offer_id)->get()->result();
                foreach($item_details as $key => $value) {
                    $update_array['pieces'] = $value->pieces + 1;
                    $update_array['is_sold_out'] = 0;
                    $this->db->where('id', $value->item_id)->update('bs_items', $update_array);
                } 
            }
            
            send_push( [$noti_user], ["message" => "Order request canceled by ".$update_order['cancel_by'], "flag" => "order", "title" => $check_for_order->title.' order update'],['order_id' => $posts['order_id']] );

            $this->response(['status' => "success", 'message' => 'Order request canceled']);
        } else {
            $this->error_response("Order already proceed");
        }
    }
}
