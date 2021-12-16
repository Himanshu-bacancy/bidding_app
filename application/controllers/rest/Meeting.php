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
//            array(
//                'field' => 'location_list',
//                'rules' => 'required'
//            ),
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
        
        if(!isset($posts['location_list']) || empty($posts['location_list']) || is_null($posts['location_list'])) { 
            $this->error_response("Please pass location list");
        } 
        $date = date('Y-m-d H:i:s');
        $this->db->where('order_id',$posts['order_id'])->update('bs_order',['share_meeting_list_date' => $date]);
        
        $this->db->insert('bs_meeting', ['sender_id' => $posts['user_id'], 'receiver_id' => $posts['buyer_id'], 'order_id' => $posts['order_id'], 'location_list' => json_encode($posts['location_list']), 'created_at' => $date]);
        $get_item = $this->db->select('bs_items.title')->from('bs_order')->join('bs_items', 'bs_order.items = bs_items.id')->where('bs_order.order_id', $posts['order_id'])->get()->row();
        
        $buyer = $this->db->select('device_token')->from('core_users')
                            ->where('user_id', $posts['buyer_id'])->get()->row();
        send_push( [$buyer->device_token], ["message" => "Meeting request arrived from seller", "flag" => "order", 'order_id' => $posts['order_id'], 'title' => $get_item->title." order update"] );
    
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
            )
        );
        if ( !$this->is_valid( $rules )) exit;
        $posts = $this->post();
        if(!isset($posts['location_id']) || empty($posts['location_id']) || is_null($posts['location_id'])) { 
            $this->error_response("Please pass location id");
        } 
        $date = date('Y-m-d H:i:s');
        $this->db->where('receiver_id',$posts['user_id'])->where('order_id',$posts['order_id'])->update('bs_meeting',['confirm_location' => json_encode($posts['location_id']), 'updated_at' => $date]);
        
        $this->db->where('order_id',$posts['order_id'])->update('bs_order',['confirm_meeting_date' => $date]);
        
        $get_item = $this->db->select('bs_items.title,bs_order.offer_id,bs_items.added_user_id')->from('bs_order')->join('bs_items', 'bs_order.items = bs_items.id')->where('bs_order.order_id', $posts['order_id'])->get()->row();
        
        $buyer = $this->db->select('device_token')->from('core_users')
                            ->where('user_id', $posts['user_id'])->get()->row();
        send_push( [$buyer->device_token], ["message" => "Meeting location confirmed by buyer", "flag" => "order", 'order_id' => $posts['order_id'], 'title' => $get_item->title." order update"] );
        
        send_push( [$buyer->device_token], ["message" => "SET DAY AND TIME TO PICK UP YOUR ITEM- SEND A MESSAGE TO THE SELLER", "flag" => "chat", 'chat_id' => $get_item->offer_id, 'title' => $get_item->title." order update"] );
        $seller = $this->db->select('device_token')->from('core_users')
                            ->where('user_id', $get_item->added_user_id)->get()->row();
        
        send_push( [$seller->device_token], ["message" => "SET DAY AND TIME TO PICK UP YOUR ITEM- SEND A MESSAGE TO THE BUYER", "flag" => "chat", 'chat_id' => $get_item->offer_id, 'title' => $get_item->title." order update"] );
        
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
        $return_file_path2 = 'qrcode/qr_'.$posts['order_id'].'.png';
        $params['data'] = $posts['order_id'];
        $params['level'] = 'H';
        $params['size'] = 10;
        $params['savename'] = FCPATH.$file_path;
        $this->ciqrcode->generate($params);
        
        $this->db->where('order_id',$posts['order_id'])->update('bs_order',['qrcode' => $return_file_path2,'generate_qr_date' => date('Y-m-d H:i:s')]);
        
//        $get_user = $this->db->select('user_id')->from('bs_order')->where('order_id', $posts['order_id'])->get()->row();
        
        $get_user = $this->db->select('bs_order.user_id,bs_items.title')->from('bs_order')
                ->join('bs_items', 'bs_order.items = bs_items.id')
                ->where('bs_order.order_id', $posts['order_id'])->get()->row();
        
        $buyer = $this->db->select('device_token')->from('core_users')
                            ->where('user_id', $get_user->buyer_id)->get()->row();
        send_push( [$buyer->device_token], ["message" => "Qr code received for order", "flag" => "order", 'order_id' => $posts['order_id'], 'title' => $get_user->title." order update"] );
        
        $this->response(['status' => 'success', 'message' => 'Qr code generated', 'file_path' => $return_file_path2]);
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
            $date = date('Y-m-d H:i:s');
            $update_order['delivery_status'] = 'qr-verified';
            $update_order['scanqr_date'] = $date;
            if($get_user->delivery_method_id == PICKUP_ONLY) {
                $update_order['pickup_date'] = $date;
                $update_order['completed_date'] = $date;
            }
            $this->db->where('order_id',$posts['order_id'])->update('bs_order',$update_order);
            $this->response(['status' => 'success', 'message' => 'Qr code verified']);
        } else {
            $this->response(['status' => 'error', 'message' => 'Invalid']);
        }
    }
    
}
