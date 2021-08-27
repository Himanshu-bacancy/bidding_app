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


		// validation rules for police station
		$rules = array(
			array(
	        	'field' => 'type',
	        	'rules' => 'required'
	        )
        );

        if ( !$this->is_valid( $rules )) exit;
        $type = $this->post('type');
        $this->db->where('type', $type);
		$this->db->where('status', 1);
        $this->db->order_by('id','desc');
    	$data = $this->db->get('bs_reasons')->result();
        //echo '<pre>'; print_r($data); die(' hello testing');
        if(count($data)) {
            $this->custom_response($data);
        } else {
            $this->error_response('record_not_found');
        }
    }
}
