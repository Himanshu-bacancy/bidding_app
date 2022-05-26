<?php

require_once( APPPATH . 'libraries/REST_Controller.php' );
require_once( APPPATH .'libraries/stripe_lib/autoload.php' );

/**
 * REST API for Notification
 */
class Cards extends API_Controller {

    /**
     * Constructs Parent Constructor
     */
    function __construct() {
        // call the parent
        parent::__construct('Card');
    }

    public function add_card_post() {
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
                'field' => 'card_holder_name',
                'rules' => 'required'
            ),
            array(
                'field' => 'card_number',
                'rules' => 'required'
            ),
            array(
                'field' => 'expiry_date',
                'rules' => 'required'
            ),
            array(
                'field' => 'address_id',
                'rules' => 'required'
            )
        );
        if ( !$this->is_valid( $rules )) exit;

        $user_id = $this->post('user_id');
        $card_holder_name = $this->post('card_holder_name');
        $card_number = str_replace(' ', '', $this->post('card_number'));
        $expiry_date = $this->post('expiry_date');
        $address_id = $this->post('address_id');
        $expiry_arr = explode('/', $expiry_date);
        $expiry_arr[1] = '20'.$expiry_arr[1];
        $expiry_date = implode('/',$expiry_arr);
        $validate_date  = $this->validate_expirydate($expiry_date);
        if($validate_date) {
            $card_type = $this->validate_customer_card($card_number);
            if($card_type != '') {
                $is_record_already_exists = $this->db->select('id')->from('bs_card')->where('user_id', $user_id)->where('card_number', $card_number)->where('status', 1)->get()->num_rows();
                if(!$is_record_already_exists) {
                    $divide_expiry_date = explode('/',$expiry_date);
                    
                    $paid_config = $this->Paid_config->get_one('pconfig1');
                    \Stripe\Stripe::setApiKey(trim($paid_config->stripe_secret_key));
                    $response = \Stripe\PaymentMethod::create([
                        'type' => 'card',
                        'card' => [
                            'number' => $card_number,
                            'exp_month' => $divide_expiry_date[0],
                            'exp_year' => $divide_expiry_date[1],
                        ]
                    ]);
                    $debit_flag = 0;
                    if($response->card['funding'] == 'debit') {
                        $debit_flag = 1;
                    }
                    $this->db->insert('bs_card', ['user_id' => $user_id, 'card_holder_name' => $card_holder_name, 'card_number' => $card_number, 'expiry_date' => $expiry_date, 'card_type' => $card_type, 'address_id' => $address_id, 'is_debit' => $debit_flag,'created_date' => date('Y-m-d H:i:s')]);
                    $this->success_response("Card added successfully");
                } else {
                    $this->error_response("Card already added");
                }
            } else {
                $this->error_response("Invalid card number");
            }
        } else {
            $this->error_response("Card is already expired");
        }
    
    }
    
    public function remove_card_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        
        $rules = array(
            array(
                'field' => 'card_id',
                'rules' => 'required'
            ),

        );
        if ( !$this->is_valid( $rules )) exit;

        $id = $this->post('card_id');
        
        $this->db->where('id', $id)->update('bs_card', ['status' => 0]);
//        $this->db->delete('bs_card', ['id' => $id]);
    
        $this->success_response( "Card remove successfully");
    }
    
    public function card_detail_post() {
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
        if ( !$this->is_valid( $rules )) exit;

        $user_id = $this->post('user_id');
        
        $obj = $this->db->select('bs_card.id, bs_card.card_number, bs_card.card_type, card_holder_name, expiry_date, bs_card.is_debit,address_id')->from('bs_card')->where('bs_card.user_id', $user_id)->where('status', 1)->order_by('id', 'desc')->get()->result_array();
        if(count($obj)) {
        foreach ($obj as $key => $value) {
            $row[$key] = $value;
            $expiry_date = explode('/',$value['expiry_date']);
            $expiry_date[1] = substr($expiry_date[1], 2);
            $row[$key]['expiry_date'] = implode('/',$expiry_date);
            $row[$key]['address'] = $this->db->select('*')->from('bs_addresses')->where('id', $value['address_id'])->get()->row();
        }
        
        $this->response($row);
            
        } else {
            $this->error_response($this->config->item( 'record_not_found'));
        }
    }
    
    public function validate_expirydate($expiry_date = NULL){

        $return = FALSE;

        $expiry_date_array = explode('/',$expiry_date);

        if (count($expiry_date_array) == 2) {

            if (checkdate($expiry_date_array[0], '01' , $expiry_date_array[1])) {

                $expiry_date_obj = DateTime::createFromFormat('d/m/Y H:i:s', "01/" . $expiry_date_array[0] . "/" .  $expiry_date_array[1]." 00:00:00");

                $expiry_date_obj = new DateTime($expiry_date_obj->format("Y-m-t"));

                $my_date = date('d/m/Y');

                $today = DateTime::createFromFormat('d/m/Y H:i:s', $my_date ." 00:00:00");

                if($expiry_date_obj >= $today){

                    $return = TRUE;

                }

            }

        }

        return $return;

    }

    

    function validate_customer_card($card_number = NULL){

        if (isset($card_number) && $card_number != ''){

            $cardtype = array(

                "visa"       => "/^4[0-9]{12}(?:[0-9]{3})?$/",

                "mastercard" => "/^5[1-5][0-9]{14}$/",

                "amex"       => "/^3[47][0-9]{13}$/",

                "jcb"        => "/^(?:2131|1800|35\d{3})\d{11}$/",

                "dinnerclub" => "/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/",

                "discover"   => "/^6(?:011|5[0-9]{2})[0-9]{12}$/",

            );

            if (preg_match($cardtype['visa'],$card_number)) {

                return 'visa';

            } else if (preg_match($cardtype['mastercard'],$card_number)) {

                return 'mastercard';

            } else if (preg_match($cardtype['dinnerclub'],$card_number)) {

                return 'dinnerclub';

            } else if (preg_match($cardtype['jcb'],$card_number)) {

                return 'jcb';

            } else if (preg_match($cardtype['amex'],$card_number)) {

                return 'amex';

            } else if (preg_match($cardtype['discover'],$card_number)) {

                return 'discover';

            } else {

                return '';

            }

        }

    }

}
