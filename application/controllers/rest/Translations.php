<?php

require_once( APPPATH . 'libraries/REST_Controller.php' );

/**
 * REST API for Notification
 */
class Translations extends API_Controller {

    /**
     * Constructs Parent Constructor
     */
    function __construct() {
        // call the parent
        parent::__construct('Translations');
    }

    public function languages_get() {
        $user_data = $this->_apiConfig([
            'methods' => ['GET'],
            'requireAuthorization' => true,
        ]);
        
        $langs = $this->db->select('id,name')->from('bs_language')->where('status', 1)->get()->result_array();
        if(count($langs)) {
            $this->response($langs);
        } else {
            $this->error_response($this->config->item( 'record_not_found'));
        }
    }
    
    public function translate_post() {
         $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        $rules = array(
            array(
                'field' => 'language_id',
                'rules' => 'required'
            )
        );
        if (!$this->is_valid($rules)) exit;
        
        $posts_var = $this->post();
        
        $translations = $this->db->select('lang_key,lang_value')->from('bs_translation')->where('language_id', $posts_var['language_id'])->get()->result_array();
        if(count($translations)) {
            $this->response($translations);
        } else {
            $this->error_response($this->config->item( 'record_not_found'));
        }
    }
}
