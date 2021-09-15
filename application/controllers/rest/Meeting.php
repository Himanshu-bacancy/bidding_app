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
        $buyer = $this->db->select('device_token')->from('core_users')
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
    
    public function generate_qr_post() {
        $this->load->library('ciqrcode');

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
        if ( !$this->is_valid( $rules )) exit;
        $posts = $this->post();
        
        $file_path = 'uploads/qrcode/qr_'.$posts['order_id'].'.png';
        $params['data'] = $posts['order_id'];
        $params['level'] = 'H';
        $params['size'] = 10;
        $params['savename'] = FCPATH.$file_path;
        $this->ciqrcode->generate($params);
        
        $this->db->where('order_id',$posts['order_id'])->update('bs_order',['qrcode' => $file_path]);
        
        $get_user = $this->db->select('user_id')->from('bs_order')->where('order_id', $posts['order_id'])->get()->row();
        
        $buyer = $this->db->select('device_token')->from('core_users')
                            ->where('user_id', $get_user->buyer_id)->get()->row();
        send_push( $buyer->device_token, ["message" => "Qr code received for order", "flag" => "qr-code_request"] );
        
        $this->response(['status' => 'success', 'message' => 'Qr code generated', 'file_path' => $file_path]);
    }
    
    public function scan_qr_post() {
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
        );
        if ( !$this->is_valid( $rules )) exit;
        $posts = $this->post();

        $get_user = $this->db->select('user_id')->from('bs_order')->where('order_id', $posts['order_id'])->get()->row();
        if($get_user->user_id == $posts['user_id']) {
            $this->db->where('order_id',$posts['order_id'])->update('bs_order',['delivery_status' => 'qr-verified','scanqr_date' => date('Y-m-d H:i:s')]);
            $this->response(['status' => 'success', 'message' => 'Qr code verified']);
        } else {
            $this->response(['status' => 'error', 'message' => 'Invalid']);
        }
    }
    
}
