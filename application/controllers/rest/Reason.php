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


    /** 
     * Cancel Offer 
     * Himanshu Sharma
    */
    public function cancel_offer_post(){
        // API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
		// validation rules for cancel offer
		$rules = array(
			array(
	        	'field' => 'user_id',
	        	'rules' => 'required'
	        ),
			array(
	        	'field' => 'operation_id',
	        	'rules' => 'required'
	        )
        );
		if ( !$this->is_valid( $rules )) exit;
		
		$chatId = $this->post('operation_id');
        $operationData = array('id'=>$chatId);
        if($this->Chat->exists( $operationData )){
            $offersData = $this->db->select('id')->from('bs_chat_history')->where('id', $chatId)->where('is_cancel', 0)->get()->row();
            if(!empty($offersData)){
                $chatHistoryData = array('is_cancel'=>1);
                if(!$this->Chat->Save( $chatHistoryData, $chatId )) {
                    $this->error_response( get_msg( 'err_cancel_offer' ));
                } else {
                    $reasonOperationData =  array(
                        'reason_id'=>$this->post('reason_id') ? $this->post('reason_id') : '',
                        'other_reason'=>$this->post('other_reason') ? $this->post('other_reason') : '',
                        'operation_id'=>$this->post('operation_id') ? $this->post('operation_id') : '',
                        'type'=>'cancel_offer',
                        'user_id'=>$this->post('user_id') ? $this->post('user_id') : '',
                    );
                    
                    if($this->Reason_operation->Save($reasonOperationData)){
                        $this->success_response(get_msg('offer_cancelled_success'));
                    } else {
                        $this->error_response(get_msg( 'err_cancel_offer'));
                    }
                }
            } else {
                $this->error_response(get_msg( 'offer_already_cancelled'));
            }
        } else {
            $this->error_response(get_msg( 'err_offer_does_not_exists'));
        }
		
	}


    /**
	 * Block User
     * Himanshu Sharma
	 */
	function block_user_post(){
		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
		
		// validation rules for create
		
		$rules = array(
			array(
	        	'field' => 'operation_id',
	        	'rules' => 'required|callback_id_check[User]'
	        ),
	        array(
	        	'field' => 'user_id',
	        	'rules' => 'required|callback_id_check[User]'
	        )
        );

		// validation
        if ( !$this->is_valid( $rules )) exit;
		$user_id = $this->post('user_id'); //Mary
		$operation_id = $this->post('operation_id');//Admin
		
		// prep data
        $data = array( 'user_id' => $user_id, 'operation_id' => $operation_id );
        $block_data = array( 'operation_id' => $user_id, 'user_id' => $operation_id );

        $query1 = $this->db->query("SELECT * FROM bs_reason_operation WHERE user_id = '".$user_id."' AND  operation_id = '".$operation_id."'");
        $data_count = $query1->num_rows();
        $query2 = $this->db->query("SELECT * FROM bs_reason_operation WHERE user_id = '".$operation_id."' AND  operation_id = '".$user_id."'");
        $block_data_count = $query2->num_rows();
        //delete block count is more than 0
        // echo '<pre>'; print_r($this->post());
        if ($data_count > 0 || $block_data_count > 0) {
            $this->db->where('user_id', $user_id);
            $this->db->where('operation_id', $operation_id);
            $this->db->delete('bs_reason_operation');

            $this->db->where('operation_id', $user_id);
            $this->db->where('user_id', $operation_id);
            $this->db->delete('bs_reason_operation');
        }
        $data['reason_id'] = !empty($this->post('reason_id')) ? $this->post('reason_id') : 0;
        $data['other_reason'] = !empty($this->post('other_reason')) ? $this->post('other_reason') : '';
        $data['type'] = 'block_user';
        $block_data['reason_id'] = !empty($this->post('reason_id')) ? $this->post('reason_id') : 0;
        $block_data['other_reason'] = !empty($this->post('other_reason')) ? $this->post('other_reason') : '';
        $block_data['type'] = 'block_user';
        
        //add block user
        $this->Reason_operation->save( $data );
        $this->Reason_operation->save( $block_data );
		$this->success_response( get_msg( 'success_block' ));
	}


	/**
	 *  Unblock User
     *  Himanshu Sharma
	 */
	function unblock_post(){
		// validation rules for create
		$rules = array(
			array(
	        	'field' => 'user_id',
	        	'rules' => 'required|callback_id_check[User]'
	        ),
	        array(
	        	'field' => 'operation_id',
	        	'rules' => 'required|callback_id_check[User]'
	        )
        );

		// validation
        if ( !$this->is_valid( $rules )) exit;

		$user_id = $this->post('user_id'); //Mary
		$operation_id = $this->post('operation_id');//Admin
		
		// prep data
        $data = array( 'user_id' => $user_id, 'operation_id' => $operation_id );
        $block_data = array( 'operation_id' => $user_id, 'user_id' => $operation_id );
     		
        // unblock user ( just need to delete )
        $query1 = $this->db->query("SELECT * FROM bs_reason_operation WHERE user_id = '".$user_id."' AND  operation_id = '".$operation_id."'");
        $data_count = $query1->num_rows();
        $query2 = $this->db->query("SELECT * FROM bs_reason_operation WHERE user_id = '".$operation_id."' AND  operation_id = '".$user_id."'");
        $block_data_count = $query2->num_rows();
        //delete block count is more than 0
        // echo '<pre>'; print_r($this->post());
        if ($data_count > 0 || $block_data_count > 0) {
            $this->db->where('user_id', $user_id);
            $this->db->where('operation_id', $operation_id);
            $this->db->delete('bs_reason_operation');

            $this->db->where('operation_id', $user_id);
            $this->db->where('user_id', $operation_id);
            $this->db->delete('bs_reason_operation');
        } else {
            $this->success_response( get_msg( 'no_user_unblock' ));
        }
		$this->success_response( get_msg( 'success_unblock' ));
	}

    /**
	 *  Report Item
     *  Himanshu Sharma
	 */
	function report_item_post(){
		// validation rules for create
		$rules = array(
			array(
	        	'field' => 'user_id',
	        	'rules' => 'required|callback_id_check[User]'
	        ),
	        array(
	        	'field' => 'operation_id',
	        	'rules' => 'required'
	        )
        );

		// validation
        if ( !$this->is_valid( $rules )) exit;

		$user_id = $this->post('user_id'); // User Id
		$operation_id = $this->post('operation_id');// Item Id
        // prep data
        $data = array('id' => $operation_id );
        if($this->Item->exists( $data )){	
            $reasonData = $this->db->select('id')
                            ->from('bs_reason_operation')
                            ->where('operation_id', $operation_id)
                            ->where('user_id', $user_id)
                            ->where('type', 'report_item')
                            ->get()->row();
            if(empty($reasonData)){
                $reasonOperationData =  array(
                    'reason_id'=>$this->post('reason_id') ? $this->post('reason_id') : '',
                    'other_reason'=>$this->post('other_reason') ? $this->post('other_reason') : '',
                    'operation_id'=>$this->post('operation_id') ? $this->post('operation_id') : '',
                    'type'=>'report_item',
                    'user_id'=>$this->post('user_id') ? $this->post('user_id') : '',
                );
                if($this->Reason_operation->Save($reasonOperationData)){
                    $this->success_response(get_msg('success_item_reported'));
                } else {
                    $this->error_response(get_msg( 'no_item_reported'));
                }
            } else {
                $this->error_response(get_msg( 'item_already_reported'));
            }
        } else {
            $this->error_response(get_msg( 'err_item_not_found'));
        }
	}
}
