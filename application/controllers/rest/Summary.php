<?php

require_once( APPPATH . 'libraries/REST_Controller.php' );

/**
 * REST API for Notification
 */
class Summary extends API_Controller {

    /**
     * Constructs Parent Constructor
     */
    function __construct() {
        // call the parent
        parent::__construct('Summary');
    }

    public function summary_post() {
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
        $request_arr = [];
        $request_arr['request_items'] = $this->db->select('id')->from('bs_items')->where('item_type_id',REQUEST_ITEM)->where('added_user_id', $posts['user_id'])->get()->num_rows();
        
        $request_arr['in_process_orders'] = $this->db->select('id')->from('bs_order')->where('operation_type',REQUEST_ITEM)->where('user_id', $posts['user_id'])->get()->num_rows();
        
        $request_arr['offer_received'] = $this->db->select('id')->from('bs_chat_history')->where('operation_type',REQUEST_ITEM)->where('buyer_user_id', $posts['user_id'])->get()->num_rows();
        
        $request_arr['saved_later'] = $this->db->select('id')->from('bs_items')->where('item_type_id',REQUEST_ITEM)->where('added_user_id', $posts['user_id'])->where('is_draft', 1)->get()->num_rows();
        
        $request_arr['deals'] = $this->db->select('id')->from('bs_chat_history')->where('operation_type',REQUEST_ITEM)->where('buyer_user_id', $posts['user_id'])->where('is_offer_complete', 1)->get()->num_rows();
        
        $direct_buy_arr = [];
        $direct_buy_arr['items_in_cart'] = $this->db->select('id')->from('bs_cart')->where('type_id',DIRECT_BUY)->where('user_id', $posts['user_id'])->get()->num_rows();
        
        $direct_buy_arr['favourites'] = $this->db->select('id')->from('bs_favourite')->where('user_id', $posts['user_id'])->get()->num_rows();
        
        $direct_buy_arr['offer_sent'] = $this->db->select('id')->from('bs_chat_history')->where('operation_type',DIRECT_BUY)->where('seller_user_id', $posts['user_id'])->get()->num_rows();
        
        $direct_buy_arr['deals'] = $this->db->select('id')->from('bs_chat_history')->where('operation_type',DIRECT_BUY)->where('buyer_user_id', $posts['user_id'])->where('is_offer_complete', 1)->get()->num_rows();
        
        $direct_buy_arr['in_process_orders'] = $this->db->select('id')->from('bs_order')->where('operation_type',DIRECT_BUY)->where('user_id', $posts['user_id'])->get()->num_rows();
        
        $selling_arr = [];
        $selling_arr['posted_items'] = $this->db->select('id')->from('bs_items')->where('item_type_id',SELLING)->where('added_user_id', $posts['user_id'])->get()->num_rows();
        
        $selling_arr['in_process_orders'] = $this->db->select('id')->from('bs_order')->where('operation_type',SELLING)->where('user_id', $posts['user_id'])->get()->num_rows();
        
        $selling_arr['offer_received'] = $this->db->select('id')->from('bs_chat_history')->where('operation_type',SELLING)->where('seller_user_id', $posts['user_id'])->get()->num_rows();
        
        $selling_arr['saved_later'] = $this->db->select('id')->from('bs_items')->where('item_type_id',SELLING)->where('added_user_id', $posts['user_id'])->where('is_draft', 1)->get()->num_rows();
        
        $selling_arr['deals'] = $this->db->select('id')->from('bs_chat_history')->where('operation_type',SELLING)->where('seller_user_id', $posts['user_id'])->where('is_offer_complete', 1)->get()->num_rows();
        
        $exchange_arr = [];
        $exchange_arr['request_items'] = $this->db->select('id')->from('bs_items')->where('item_type_id',EXCHANGE)->where('added_user_id', $posts['user_id'])->get()->num_rows();
        
        $exchange_arr['in_process_orders'] = $this->db->select('id')->from('bs_order')->where('operation_type',EXCHANGE)->where('user_id', $posts['user_id'])->get()->num_rows();
        
        $exchange_arr['offer_received'] = $this->db->select('id')->from('bs_chat_history')->where('operation_type',EXCHANGE)->group_start()->where('buyer_user_id', $posts['user_id'])->or_where('seller_user_id', $posts['user_id'])->group_end()->get()->num_rows();
        
        $exchange_arr['saved_later'] = $this->db->select('id')->from('bs_items')->where('item_type_id',EXCHANGE)->where('added_user_id', $posts['user_id'])->where('is_draft', 1)->get()->num_rows();
        
        $exchange_arr['deals'] = $this->db->select('id')->from('bs_chat_history')->where('operation_type',EXCHANGE)->where('buyer_user_id', $posts['user_id'])->where('is_offer_complete', 1)->get()->num_rows();
        
        $request_arr = $this->ps_security->clean_output( $request_arr );
        $direct_buy_arr = $this->ps_security->clean_output( $direct_buy_arr );
        $selling_arr = $this->ps_security->clean_output( $selling_arr );
        $exchange_arr = $this->ps_security->clean_output( $exchange_arr );
        
        $this->response(['status' => 'success', 'request' => $request_arr, 'direct_buy' => $direct_buy_arr ,'selling' => $selling_arr, 'exchange' => $exchange_arr]);
    }
}
