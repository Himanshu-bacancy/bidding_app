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
//            array(
//                'field' => 'cvc',
//                'rules' => 'required'
//            ),
            array(
                'field' => 'transaction_detail',
                'rules' => 'required'
            )
        );
        if (!$this->is_valid($rules)) exit;
        
        $user_id            = $this->post('user_id');
        $item_ids           = $this->post('item_ids');
        $delivery_method    = $this->post('delivery_method');
        $address_id         = $this->post('address_id');
        $total_amount       = $this->post('total_amount');
        $card_id            = $this->post('card_id');
//        $cvc                = $this->post('cvc');
        $transaction_detail = $this->post('transaction_detail');
        
        $this->db->insert('bs_order', ['user_id' => $user_id, 'items' => $item_ids, 'delivery_method' => $delivery_method, 'card_id' => $card_id, 'address_id' => $address_id, 'total_amount' => $total_amount, 'status' => 'success', 'transaction' => $transaction_detail,'created_at' => date('Y-m-d H:i:s')]);
        
        $this->response(['status' => "success", 'order_status' => 'success']);
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
