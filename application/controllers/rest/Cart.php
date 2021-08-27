<?php

require_once( APPPATH . 'libraries/REST_Controller.php' );

/**
 * REST API for Notification
 */
class Cart extends API_Controller {

    /**
     * Constructs Parent Constructor
     */
    function __construct() {
        // call the parent
        parent::__construct('Cart');
    }

    public function add_cart_post() {
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
                'field' => 'item_id',
                'rules' => 'required'
            ),
            array(
                'field' => 'type_id',
                'rules' => 'required'
            )
        );
        if ( !$this->is_valid( $rules )) exit;

        $user_id = $this->post('user_id');
        $item_id = $this->post('item_id');
        $type_id = $this->post('type_id');
        $color_id = ($this->post('color_id')) ?? '';
        $size_id  = ($this->post('size_id')) ?? '';
        $quantity = ($this->post('quantity')) ?? '';
        $brand = ($this->post('brand')) ?? '';
        
        $is_record_already_exists = $this->db->select('id')->from('bs_cart')->where('user_id', $user_id)->where('item_id', $item_id)->get()->num_rows();
        $success_message = "Item already added to cart";
        $alraedy_in_cart = "0";
        if(!$is_record_already_exists) {
            $is_multi_item = $this->db->select('id')->from('bs_cart')->where('item_id', $item_id)->get()->num_rows();
            $alraedy_in_cart = "1";
            if(!$is_multi_item) {
                $alraedy_in_cart = "0";
                $success_message = "Item added to cart successfully";
            }
            $this->db->insert('bs_cart', ['user_id' => $user_id, 'item_id' => $item_id, 'type_id' => $type_id, 'color_id' => $color_id, 'size_id' => $size_id, 'quantity' => $quantity, 'brand' => $brand, 'created_date' => date('Y-m-d H:i:s')]);
        }
    
        $this->response(['status' => 'success', 'message' => $success_message, 'alraedy_in_cart' => $alraedy_in_cart]);
    }
    
    public function remove_cart_item_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        
        $rules = array(
            array(
                'field' => 'id',
                'rules' => 'required'
            ),
//            array(
//                'field' => 'user_id',
//                'rules' => 'required'
//            ),
//            array(
//                'field' => 'item_id',
//                'rules' => 'required'
//            ),
        );
        if ( !$this->is_valid( $rules )) exit;

//        $user_id = $this->post('user_id');
//        $item_id = $this->post('item_id');
        $id = $this->post('id');
        
        $this->db->delete('bs_cart', ['id' => $id]);
    
        $this->success_response( "Item remove from cart successfully");
    }
    
    public function clear_cart_post() {
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
        
        $this->db->delete('bs_cart',['user_id' => $user_id]);
        
        $this->success_response("Cart clear successfully");
    }
    
    public function cart_detail_post() {
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
        
        $obj = $this->db->select('bs_cart.id as cart_id, bs_items.id as item_id, bs_items.title, bs_items.dynamic_link, bs_items.price, bs_items.delivery_method_id')->from('bs_cart')->join('bs_items', 'bs_cart.item_id = bs_items.id')
                ->where('user_id', $user_id)->get()->result();
        $sum_of_cart = $this->db->query('SELECT sum(bs_items.price) as sum FROM bs_items JOIN bs_cart ON  bs_items.id = bs_cart.item_id WHERE bs_cart.user_id = "'.$user_id.'" GROUP BY bs_cart.user_id')->row();
        foreach ($obj as $key => $value) {
            $row[$key] = $this->Item->get_one( $value->item_id );
            $this->ps_adapter->convert_item($row[$key]);
            $row[$key]->cart_id = $value->cart_id;
        }
        $row = $this->ps_security->clean_output( $row );
//        print_r($tmp_req_item);
//        die();
//        foreach ($obj as $key => $value) {
////            $row[$key] = $value;
//            die();
////            $row[$key]['default_photo'] = $this->ps_adapter->get_default_photo( $value['item_id'], 'item' );
//        }
        $this->response( ['status' => "success", 'items' => $row, 'sum' => ($sum_of_cart->sum) ?? 0]);
    }

}
