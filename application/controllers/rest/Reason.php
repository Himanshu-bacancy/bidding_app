<?php
require_once( APPPATH .'libraries/REST_Controller.php' );

/**
 * REST API for News
 */
class Reason extends API_Controller
{
    /**
     * Constructs Parent Constructor
     */
    function __construct() {
        // call the parent
        parent::__construct('Reasons');
    }

    public function get_reasons_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        $type = $this->post('type');
        $data = $this->db->select('id, type, name')->from('bs_reasons')->where(array('status' => 1 ,'type' => $type))->order_by('id','desc')->get()->result_array();
        if(count($data)) {
            $this->response($data);
        } else {
            $this->error_response($this->config->item( 'record_not_found'));
        }
    }
}
