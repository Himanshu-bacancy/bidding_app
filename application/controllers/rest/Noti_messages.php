<?php
require_once( APPPATH .'libraries/REST_Controller.php' );

/**
 * REST API for Notification
 */
class Noti_messages extends API_Controller
{
	/**
	 * Constructs Parent Constructor
	 */
	function __construct()
	{
		// call the parent
		parent::__construct( 'Noti' );

	}

	function all_notis2_post() 
	{

		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
		
		$limit = $this->get( 'limit' );
   		$offset = $this->get( 'offset' );

		$noti_obj = $this->Noti_message->get_all($limit,$offset)->result();

		foreach ($noti_obj as $nt)
		{
			$noti_user_data = array(
	        	"noti_id" 		=> $nt->id,
	        	"user_id" 		=> $this->post('user_id'),
	        	"device_token"  => $this->post('device_token')

	    	);

	    	if ( $this->Notireaduser->exists( $noti_user_data )) {
	    		$nt->is_read = 1;
	    	} else {
	    		$nt->is_read = 0;
	    	}

	    	
		}

    	$this->custom_response_noti( $noti_obj );

	}
    function all_notis_post() 
	{

		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
		
		$limit = $this->get( 'limit' );
   		$offset = $this->get( 'offset' );

		$noti_obj = $this->Noti_message->get_all($limit,$offset)->result_array();
        $get_user_time = $this->db->select('added_date,added_date_timestamp')->from('core_users')
                ->where('user_id', $this->post('user_id'))->get()->row();
		$row = [];
        foreach ($noti_obj as $nt => $value)
		{
            $noti_time = strtotime($value['added_date']);
            if($noti_time >= $get_user_time->added_date_timestamp) {
                $row[$nt] = $value;
                $noti_user_data = array(
                    "noti_id" 		=> $value['id'],
                    "user_id" 		=> $this->post('user_id'),
                    "device_token"  => $this->post('device_token')

                );

                if ( $this->Notireaduser->exists( $noti_user_data )) {
                    $row[$nt]['is_read'] = 1;
                } else {
                    $row[$nt]['is_read'] = 0;
                }
                $row[$nt] = (object)$row[$nt];
            }
		}
        $row = array_values($row);
    	$this->custom_response_noti( $row );

	}

	/**
	 * Convert Object
	 */
	function convert_object( &$obj )
	{

		// call parent convert object
		parent::convert_object( $obj );

		// convert customize category object
		$noti_user_data = array(
        	"noti_id" => $obj->id,
        	"user_id" => $this->post('user_id'),
        	"device_token"  => $this->post('device_token')
    	);

    	if ( !$this->Notireaduser->exists( $noti_user_data )) {
    		
    		$obj->is_read = 0;
    	} else {
    		
    		$obj->is_read = 1;
    	}

		// convert customize item object
		$this->ps_adapter->convert_noti_message( $obj );
	}
}