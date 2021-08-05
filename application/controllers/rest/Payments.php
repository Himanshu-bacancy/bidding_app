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
            array(
                'field' => 'item_ids',
                'rules' => 'required'
            ),
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
        $payment_method     = $this->post('payment_method');
        $address_id         = $this->post('address_id');
        $total_amount       = $this->post('total_amount');
        $posts_var = $this->post();
        $item_ids = [];
        if(!isset($posts_var['item_ids']) || empty($posts_var['item_ids']) || is_null($posts_var['item_ids'])) { 
            $this->error_response("Please pass item ids");
        } else {
            $item_ids = $posts_var['item_ids'];
            if(is_array($posts_var['item_ids'])) {
                $item_ids = implode(',', $posts_var['item_ids']);
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
                    'amount' => $this->post('total_amount') * 100,
                    "currency" => trim($paid_config->currency_short_form),
                    'payment_method' => $response->id,
                    'payment_method_types' => ['card'],
                    'capture_method' => 'manual'
                ]);
                
                $this->db->insert('bs_order', ['user_id' => $user_id, 'items' => $item_ids, 'delivery_method' => $delivery_method,'payment_method' => 'card', 'card_id' => $card_id, 'address_id' => $address_id, 'total_amount' => $total_amount, 'status' => 'pending', 'delivery_status' => 'pending', 'transaction' => $response,'created_at' => date('Y-m-d H:i:s')]);
                $record_id = $this->db->insert_id();
                if (isset($response->id)) {
                    $this->db->where('id', $record_id)->update(['status' => 'initiate']);
                    $this->response(['status' => "success", 'order_status' => 'success', 'intent_id' => $response->id, 'record_id' => $record_id]);
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
}
