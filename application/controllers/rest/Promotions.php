<?php

require_once( APPPATH . 'libraries/REST_Controller.php' );

/**
 * REST API for Notification
 */
class Promotions extends API_Controller {

    /**
     * Constructs Parent Constructor
     */
    function __construct() {
        // call the parent
        parent::__construct('Promotions');
    }

    public function plans_get() {
        $user_data = $this->_apiConfig([
            'methods' => ['GET'],
            'requireAuthorization' => true,
        ]);
        
        $plans = $this->db->select('id,name,code,price,days')->from('bs_promotingitemplans')->where('status', 1)->order_by('id','desc')->get()->result_array();
        if(count($plans)) {
            $this->response($plans);
        } else {
            $this->error_response($this->config->item( 'record_not_found'));
        }
    }
}
