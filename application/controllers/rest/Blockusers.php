<?php
require_once( APPPATH .'libraries/REST_Controller.php' );

/**
 * REST API for Notification
 */
class Blockusers extends API_Controller
{
	/**
	 * Constructs Parent Constructor
	 */
	function __construct()
	{
		// call the parent
		parent::__construct( 'Block' );

	}

	/**
	 * Block User
	 */
	function add_post() 
	{
		
		// validation rules for create
		
		$rules = array(
			array(
	        	'field' => 'from_block_user_id',
	        	'rules' => 'required|callback_id_check[User]'
	        ),
	        array(
	        	'field' => 'to_block_user_id',
	        	'rules' => 'required|callback_id_check[User]'
	        )
        );

		// validation
        if ( !$this->is_valid( $rules )) exit;

		$from_block_user_id = $this->post('from_block_user_id'); //Mary
		$to_block_user_id = $this->post('to_block_user_id');//Admin
		
		// prep data
        $data = array( 'from_block_user_id' => $from_block_user_id, 'to_block_user_id' => $to_block_user_id );
        $block_data = array( 'from_block_user_id' => $to_block_user_id, 'to_block_user_id' => $from_block_user_id );

        $data_count = $this->Block->count_all_by( $data );
        $block_data_count = $this->Block->count_all_by( $block_data );

        //delete block count is more than 0
        if ($data_count > 0 || $block_data_count> 0) {
        	$this->Block->delete_by( $data );
        	$this->Block->delete_by( $block_data );
        }

		if ( $this->Block->exists( $data ) || $this->Block->exists( $data )) {
			//delete when block user is existed
			$this->Block->delete_by( $data );
			$this->Block->delete_by( $block_data );
		} else {
			//add block user
			$this->Block->save( $data );
			$this->Block->save( $block_data );

		}

		$this->success_response( get_msg( 'success_block' ));
	}


	/**
	 *  Unblock User
	 */
	function unblock_post() 
	{
		
		// validation rules for create
		
		$rules = array(
			array(
	        	'field' => 'from_block_user_id',
	        	'rules' => 'required|callback_id_check[User]'
	        ),
	        array(
	        	'field' => 'to_block_user_id',
	        	'rules' => 'required|callback_id_check[User]'
	        )
        );

		// validation
        if ( !$this->is_valid( $rules )) exit;

		$from_block_user_id = $this->post('from_block_user_id'); //Mary
		$to_block_user_id = $this->post('to_block_user_id');//Admin
		
		// prep data
        $data = array( 'from_block_user_id' => $from_block_user_id, 'to_block_user_id' => $to_block_user_id );
        $block_data = array( 'from_block_user_id' => $to_block_user_id, 'to_block_user_id' => $from_block_user_id );

     		
     		// unblock user ( just need to delete )
			
			if ( $this->Block->exists( $data ) || $this->Block->exists( $data )) {
				//delete when block user is existed
				$this->Block->delete_by( $data );
				$this->Block->delete_by( $block_data );
			} else {
				$this->success_response( get_msg( 'no_user_unblock' ));
			}
		

		$this->success_response( get_msg( 'success_unblock' ));
	}
}