<?php
require_once( APPPATH .'libraries/REST_Controller.php' );

/**
 * REST API for News
 */
class Reasons extends API_Controller
{
    
    /**
     * Constructs Parent Constructor
     */
    function __construct() {
        // call the parent
        parent::__construct('Reasons');
    }

    
    public function report_item_reason_get() {
        $user_data = $this->_apiConfig([
            'methods' => ['GET'],
            'requireAuthorization' => true,
        ]);
        
        $data = $this->db->select('id,name')->from('bs_reportitemreasons')->where('status', 1)->order_by('id','desc')->get()->result_array();
        if(count($data)) {
            $this->response($data);
        } else {
            $this->error_response($this->config->item( 'record_not_found'));
        }
    }
}