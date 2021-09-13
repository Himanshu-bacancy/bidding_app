<?php

require_once( APPPATH . 'libraries/REST_Controller.php' );

/**
 * REST API for Notification
 */
class Meeting extends API_Controller {

    /**
     * Constructs Parent Constructor
     */
    function __construct() {
        // call the parent
        parent::__construct('Meeting');
    }

    public function send_location_list_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        $rules = array(
            array(
                'field' => 'location_list',
                'rules' => 'required'
            ),
            array(
                'field' => 'order_id',
                'rules' => 'required'
            ),
            array(
                'field' => 'user_id',
                'rules' => 'required'
            ),
            array(
                'field' => 'buyer_id',
                'rules' => 'required'
            )
        );
        if ( !$this->is_valid( $rules )) exit;

        $posts = $this->post();
        
        $this->db->insert('bs_meeting', ['sender_id' => $posts['user_id'], 'receiver_id' => $posts['buyer_id'], 'order_id' => $posts['order_id'], 'location_list' => $posts['location_list'], 'created_at' => date('Y-m-d H:i:s')]);
        $buyer = $this->db->select('device_token')->from('bs_items')
                            ->where('user_id', $posts['buyer_id'])->get()->row();
        send_push( $buyer->device_token, ["message" => "Meeting request arrived from seller", "flag" => "meeting_request"] );
    
        $this->response(['status' => 'success', 'message' => 'Locations sent']);
    }
    
    public function confirm_location_post() {
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
                'field' => 'location_id',
                'rules' => 'required'
            )
        );
        if ( !$this->is_valid( $rules )) exit;

        $posts = $this->post();
        
        $this->db->where('receiver_id',$posts['user_id'])->where('order_id',$posts['order_id'])->update('bs_meeting',['confirm_location' => $posts['location_id']]);
    
        $this->response(['status' => 'success', 'message' => 'Locations confirmed']);
    }
    
}
