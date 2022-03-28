<?php
require_once( APPPATH .'libraries/REST_Controller.php' );

/**
 * REST API for About
 */
class Abouts extends API_Controller
{
	/**
	 * Constructs Parent Constructor
	 */
	function __construct()
	{
		// call the parent
		parent::__construct( 'About' );		
	}

	/**
	 * Convert Object
	 */
	function convert_object( &$obj )
	{
		// call parent convert object
		parent::convert_object( $obj );

		// convert customize category object
		$this->ps_adapter->convert_about( $obj );
	}

	function get_get(){
		// add flag for default query
		$this->is_get = true;

		// get id
		$id = $this->get( 'id' );

		if ( $id ) {
			
			// if 'id' is existed, get one record only
			$data = $this->model->get_one( $id );
			if ( isset( $data->is_empty_object )) {
			// if the id is not existed in the return object, the object is empty
				
				$data = array();
			}
			$this->custom_response( $data );
		}

		// get limit & offset
		$limit = $this->get( 'limit' );
		$offset = $this->get( 'offset' );


		// get search criteria
		$default_conds = $this->default_conds();
		$user_conds = $this->get();
		$conds = array_merge( $default_conds, $user_conds );

		if ( $limit ) {
			unset( $conds['limit']);
		}

		if ( $offset ) {
			unset( $conds['offset']);
		}


		if ( count( $conds ) == 0 ) {
		// if 'id' is not existed, get all	
		
			if ( !empty( $limit ) && !empty( $offset )) {
			// if limit & offset is not empty
				
				$data = $this->model->get_all( $limit, $offset )->result();
			} else if ( !empty( $limit )) {
			// if limit is not empty
				
				$data = $this->model->get_all( $limit )->result();
			} else {
			// if both are empty

				$data = $this->model->get_all()->result();
			}

			$this->custom_response( $data , $offset );
		} else {

			if ( !empty( $limit ) && !empty( $offset )) {
			// if limit & offset is not empty

				$data = $this->model->get_all_by( $conds, $limit, $offset )->result();
			} else if ( !empty( $limit )) {
			// if limit is not empty

				$data = $this->model->get_all_by( $conds, $limit )->result();
			} else {
			// if both are empty

				$data = $this->model->get_all_by( $conds )->result();
			}

			$this->custom_response( $data , $offset );
		}
	}
    
    public function helpcenter_topics_get() {
        $user_data = $this->_apiConfig([
            'methods' => ['GET'],
            'requireAuthorization' => true,
        ]);
        
        $topics = $this->db->select('id,name')->from('bs_helpcenter_topic')->where('status', 1)->get()->result_array();
        if(count($topics)) {
            $this->response($topics);
        } else {
            $this->error_response($this->config->item( 'record_not_found'));
        }
    }
    
    public function helpcenter_subtopics_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        
        $rules = array(
            array(
                'field' => 'topic_id',
                'rules' => 'required'
            ),
        );
        if ( !$this->is_valid( $rules )) exit;
        $posts = $this->post();
        
        $subtopics = $this->db->select('id,name')->from('bs_helpcenter_subtopic')->where('topic_id',$posts['topic_id'])->where('status', 1)->get()->result_array();
        if(count($subtopics)) {
            $this->response($subtopics);
        } else {
            $this->error_response($this->config->item( 'record_not_found'));
        }
    }
    
    public function helpcenter_subtopic_content_post() {
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
        
        $rules = array(
            array(
                'field' => 'subtopic_id',
                'rules' => 'required'
            ),
        );
        if ( !$this->is_valid( $rules )) exit;
        $posts = $this->post();
        
        $subtopics_content = $this->db->select('id,name,content')->from('bs_helpcenter_subtopic')->where('id',$posts['subtopic_id'])->where('status', 1)->get()->result_array();
        if(count($subtopics_content)) {
            $this->response($subtopics_content);
        } else {
            $this->error_response($this->config->item( 'record_not_found'));
        }
    }

}