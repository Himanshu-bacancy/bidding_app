<?php

require_once( APPPATH . 'libraries/REST_Controller.php' );

/**
 * REST API for Notification
 */
class Card extends API_Controller {

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
        $card_number = $this->post('card_number');
        $expiry_date = $this->post('expiry_date');
        $address_id = $this->post('address_id');
        
        $is_record_already_exists = $this->db->select('id')->from('bs_card')->where('user_id', $user_id)->where('card_number', $card_number)->get()->num_rows();
        $success_message = "Card already added";
        if(!$is_record_already_exists) {
            $this->db->insert('bs_card', ['user_id' => $user_id, 'card_holder_name' => $card_holder_name, 'card_number' => $card_number, 'expiry_date' => $expiry_date, 'address_id' => $address_id, 'created_date' => date('Y-m-d H:i:s')]);
            $success_message = "Card added successfully";
        }
    
        $this->success_response( $success_message);
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
        
        $this->db->delete('bs_card', ['id' => $id]);
    
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
        
        $obj = $this->db->from('bs_card')->where('user_id', $user_id)->get()->result();
        
        $this->response( ['status' => "success", 'cards' => $obj]);
    }

}
