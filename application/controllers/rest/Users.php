<?php
require_once( APPPATH .'libraries/REST_Controller.php' );

/**
 * REST API for Users
 */
class Users extends API_Controller
{

	/**
	 * Constructs Parent Constructor
	 */
	function __construct()
	{
		parent::__construct( 'User' );
		$this->CI->load->library('Authorization_Token');
	}	

	/**
	 * Default Query for API
	 * @return [type] [description]
	 */
	function default_conds()
	{
		$conds = array();

		if ( $this->is_search ) {
			if($this->post('user_name') != "") {
				$conds['user_name']   = $this->post('user_name');
			}

			if($this->post('overall_rating') != "") {
				$conds['overall_rating']   = $this->post('overall_rating');
			}

			if($this->post('return_types') != "") {
				$conds['return_types']   = $this->post('return_types');
			}

			if($this->post('login_user_id') != "") {
				$conds['added_user_id']   = $this->post('login_user_id');
			}
		}

		return $conds;

	}
	

	/**
	 * Convert Object
	 */
	function convert_object( &$obj )
	{
		// call parent convert object
		parent::convert_object( $obj );

		// convert customize category object
		$this->ps_adapter->convert_user( $obj );
	}
	
	/**
	 * Users Registration
	 */
	function add_post()
	{
		// validation rules for user register
		$rules = array(
			array(
	        	'field' => 'user_name',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'user_email',
	        	'rules' => 'required|valid_email|callback_email_check'
	        ),
	        array(
	        	'field' => 'user_password',
	        	'rules' => 'required'
	        )

        );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;
        $email_verified_enable = $this->Backend_config->get_one('be1')->email_verification_enabled;

		// default set all user need to verify
		$email_verified_enable = 1 ;

        $code = generate_random_string(5);
        $added_date =  date("Y-m-d H:i:s");

        if($email_verified_enable != 1) {

	        $user_data = array(
	        	
	        	"user_name" => $this->post('user_name'), 
	        	"user_email" => $this->post('user_email'), 
	        	'user_password' => md5($this->post('user_password')),
	        	"device_token" => $this->post('device_token'),
	        	"code" =>  "",
	        	"email_verify" => 1,
	        	"status" => 1, //Need to verified status
	        	"added_date" =>  $added_date,
	        	"added_date_timestamp" => strtotime($added_date)

	        );
	    } else {
	    	$user_data = array(
	        	
	        	"user_name" => $this->post('user_name'), 
	        	"user_email" => $this->post('user_email'), 
	        	'user_password' => md5($this->post('user_password')),
	        	"device_token" => $this->post('device_token'),
	        	"code" =>  $code,
	        	"email_verify" => 1,
	        	"status" => 2, //Need to verified status
	        	"added_date" =>  $added_date,
	        	"added_date_timestamp" => strtotime($added_date)

	        );
	    	$conds['status'] = 2;
	    }

        $conds['user_email'] = $user_data['user_email'];
        
       	$user_infos = $this->User->user_exists($conds)->result();

       	if (empty($user_infos)) {

       		if ( !$this->User->save($user_data)) {

        	$this->error_response( get_msg( 'err_user_register' ));

        	} else {

    			$noti_token = array(
					"device_token" => $this->post( 'device_token' )
				);

				$noti_count = $this->Noti->count_all_by($noti_token);

				if ($noti_count == 1) {
					if ( $this->Noti->exists( $noti_token )) {
	        			$noti_id = $this->Noti->get_one_by($noti_token);
	        			$push_noti_token_id = $noti_id->push_noti_token_id;
	        			$noti_data = array(

							"user_id" => $user_data['user_id']
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        } else {
			            $noti_data = array(

							"user_id" => $user_data['user_id'],
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        }
				}else{
					$this->Noti->delete_by($noti_token);
						$noti_data = array(

							"user_id" => $user_data['user_id'],
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );

				}


	        	$subject = get_msg('user_acc_reg_label');
				
	        	if($email_verified_enable != 1) {
	        		if ( !send_user_register_email_without_verify( $user_data['user_id'], $subject )) {

					$this->error_response( get_msg( 'user_register_success_but_email_not_send' ));
				
					} 
	        	} else {

		        	if ( !send_user_register_email( $user_data['user_id'], $subject )) {
						
						$this->error_response( get_msg( 'user_register_success_but_email_not_send' ));
					
					} 
				}
        	}

       	} else {

       		//$this->error_response( get_msg( 'need_to_verify' ));
       		$user_id = $user_infos[0]->user_id;
       		$subject = get_msg('user_acc_reg_label');

       		if($email_verified_enable != 1) {
	        	if ( !send_user_register_email_without_verify( $user_id, $subject )) {
					$this->error_response( get_msg( 'user_register_success_but_email_not_send' ));
				} 
	        } else {
       			if ( !send_user_register_email( $user_id, $subject )) {
					$this->error_response( get_msg( 'user_register_success_but_email_not_send' ));
				} 
			}
			
			$userdatacs = $this->User->get_one($user_id);

			$payload = [
				'user_id' => $userdatacs->user_id ? $userdatacs->user_id : 0,
				'user_email' => $userdatacs->user_email ? $userdatacs->user_email : '',
				'user_phone' => $userdatacs->user_phone ? $userdatacs->user_phone : '',
				'device_token' => $userdatacs->device_token ? $userdatacs->device_token : '',
			];

			$token = $this->authorization_token->generateToken($payload);
			$userdatacs->token = $token;
       		$this->custom_response($userdatacs);
       		
       	}
		$userdatac = $this->User->get_one($user_data["user_id"]);

		$payload = [
			'user_id' => $userdatac->user_id ? $userdatac->user_id : 0,
			'user_email' => $userdatac->user_email ? $userdatac->user_email : '',
			'user_phone' => $userdatac->user_phone ? $userdatac->user_phone : '',
			'device_token' => $userdatac->device_token ? $userdatac->device_token : '',
		];

		$token = $this->authorization_token->generateToken($payload);
		$userdatac->token = $token;
        $this->custom_response($userdatac);
        //$this->custom_response($this->User->get_one($user_data["user_id"]));

	}


	/**
	 * Email Checking
	 *
	 * @param      <type>  $email     The identifier
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	function email_check( $email )
    {
        if ( $this->User->exists( array( 'user_email' => $email, 'status' => 1 ))) {
        	
            $this->form_validation->set_message('email_check', 'Email Exist');
            return false;
        } 

        return true;
    }

    /**
	 * Users Login
	 */
	function login_post()
	{
		//echo '<pre>'; print_r($this->post()); die(' data received');
		// validation rules for user register
		$rules = array(
			
	        array(
	        	'field' => 'user_email',
	        	'rules' => 'required|valid_email'
	        ),
	        array(
	        	'field' => 'user_password',
	        	'rules' => 'required'
	        )
        );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;
        
        if ( $this->User->exists( array( 'user_email' => $this->post( 'user_email' ),'user_password' => $this->post( 'user_password' ), 'device_token' => $this->post( 'device_token' )))) {

        //if ( $this->User->exists( array( 'user_email' => $this->post( 'user_email' ), 'user_password' => $this->post( 'user_password' )))) {

        	$email = $this->post( 'user_email' );
	        $conds['user_email'] = $email;
	        $is_banned = $this->User->get_one_by($conds)->is_banned;

	        if ( $is_banned == '1') {
	        	$this->error_response( get_msg( 'err_user_banned' ));
	        } else {
	        	$user = $this->User->get_one_by(array("user_email" => $this->post('user_email')));

        		$noti_token = array(
					"device_token" => $this->post( 'device_token' )
				);

				$noti_count = $this->Noti->count_all_by($noti_token);

				if ($noti_count == 1) {
					if ( $this->Noti->exists( $noti_token )) {
	        			$noti_id = $this->Noti->get_one_by($noti_token);
	        			$push_noti_token_id = $noti_id->push_noti_token_id;
	        			$noti_data = array(

							"user_id" => $user->user_id
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        } else {
			            $noti_data = array(

							"user_id" => $user->user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        }
				}else{
					$this->Noti->delete_by($noti_token);
						$noti_data = array(

							"user_id" => $user->user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
				}
				// you user authentication code will go here, you can compare the user with the database or whatever
				//echo '<pre>'; print_r($user); die;
				$payload = [
					'user_id' => $user->user_id ? $user->user_id : 0,
					'user_email' => $user->user_email ? $user->user_email : '',
					'user_phone' => $user->user_phone ? $user->user_phone : '',
					'device_token' => $user->device_token ? $user->device_token : '',
				];
				//echo '<pre>'; print_r($payload); die;
				$token = $this->authorization_token->generateToken($payload);

				$check_data = array(

					"user_id" => $user->user_id
				);

				
				$blacklist_data = $this->Blacklists->get_all_by($check_data);

				if(!empty($blacklist_data->result()))
				{
					foreach($blacklist_data->result() as $blacklist)
					{
						$time_difference = strtotime('now') - strtotime($blacklist->added_date);

						if( $time_difference >= $this->config->item('token_expire_time'))
                        {
							$this->Blacklists->delete( $blacklist->id);
						}
					}
				}

				$user->token = $token;
				//$data = ['data'=>$user, 'token'=>$token];
				$this->custom_response($user);
	        }
        } else {
			$this->error_response( get_msg( 'err_user_not_exist' ));

        }

	}

	/**
	* User Reset Password
	*/
	function reset_post()
	{
		// validation rules for user register
		$rules = array(
	        array(
	        	'field' => 'user_email',
	        	'rules' => 'required|valid_email'
	        )
        );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        $user_info = $this->User->get_one_by( array( "user_email" => $this->post( 'user_email' )));

        if ( isset( $user_info->is_empty_object )) {
        // if user info is empty,
        	
        	$this->error_response( get_msg( 'err_user_not_exist' ));
        }

        // generate code
        $code = md5(time().'teamps');

        // insert to reset
        $data = array(
			'user_id' => $user_info->user_id,
			'code' => $code
		);

		if ( !$this->ResetCode->save( $data )) {
		// if error in inserting,

			$this->error_response( get_msg( 'err_model' ));
		}

		// Send email with reset code
		$to = $user_info->user_email;
	    $subject = get_msg( 'pwd_reset_label' );
		$hi = get_msg( 'hi_label' );
		$msg = "<p>".$hi.",". $user_info->user_name ."</p>".
					"<p>".get_msg( 'pwd_reset_link' )."<br/>".
					"<a href='". site_url( $this->config->item( 'reset_url') .'/'. $code ) ."'>".get_msg( 'reset_link_label' )."</a></p>".
					"<p>".get_msg( 'best_regards_label' ).",<br/>". $sender_name ."</p>";
		// send email from admin
		// Need to check this bunch of
		
		if ( ! $this->ps_mail->send_from_admin( $to, $subject, $msg ) ) {
			$this->error_response( get_msg( 'err_email_not_send' ));
		}
		
		$this->success_response( get_msg( 'success_email_sent' ));
	}

	/**
	* User Profile Update
	*/

	function profile_update_post()
	{

		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
		
		// validation rules for user register
		$rules = array(
			array(
	        	'field' => 'user_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'user_name',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'user_email',
	        	'rules' => 'required|valid_email'
	        ),
	        array(
	        	'field' => 'user_phone',
	        	'rules' => 'required'
	        ),
	       
	        array(
	        	'field' => 'user_about_me',
	        	'rules' => 'required'
	        ),

	        array(
	        	'field' => 'is_show_email',
	        	'rules' => 'required'
	        ),
	       
	        array(
	        	'field' => 'is_show_phone',
	        	'rules' => 'required'
	        )
        );
		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        $user_id = $this->post('user_id');
        // user email checking
        $user_email = $this->User->get_one($user_id)->user_email;
        if ($user_email == $this->post('user_email')) {
        	$email = $this->post('user_email');
        } else {
        	$conds['user_email'] = $this->post('user_email');
        	$conds['status'] = 1;
       		$user_infos = $this->User->get_one_user_email($conds)->result();
        	if (empty($user_infos)) {
        		$email = $this->post( 'user_email' );
        	} else {
        		
		    	$this->error_response( get_msg( 'err_user_email_exist' ));
		    }		 
        }

        // user phone checking
        $user_phone = $this->User->get_one($user_id)->user_phone;
        if ($user_phone == $this->post('user_phone')) {
        	$phone = $this->post('user_phone');
        } else {
        	$conds['user_phone'] = $this->post('user_phone');
        	$conds['status'] = 1;
       		$user_infos = $this->User->get_one_user_phone($conds)->result();
        	if (empty($user_infos)) {
        		$phone = $this->post( 'user_phone' );
        	} else {	
		    	$this->error_response( get_msg( 'err_user_phone_exist' ));
		    }		 
        }

        $user_data = array(
        	"user_name"     => $this->post('user_name'), 
        	"user_email"    => $this->post('user_email'), 
        	"user_phone"    => $this->post('user_phone'),
        	"user_address"  => $this->post('user_address'),
        	"city"			=> $this->post('city'),
        	"user_about_me" => $this->post('user_about_me'),
        	"device_token" => $this->post('device_token'),
        	"is_show_email" => $this->post('is_show_email'),
        	"is_show_phone" => $this->post('is_show_phone')
        );
        // print_r($user_data);die;

        if ( !$this->User->save($user_data, $this->post('user_id'))) {
        	$this->error_response( get_msg( 'err_user_update' ));
        }
        //$this->success_response( get_msg( 'success_profile_update' ));
        $this->custom_response($this->User->get_one($user_id));
	}

	/**
	* User Profile Update
	*/
	function password_update_put()
	{
		// validation rules for user register
		$rules = array(
			array(
	        	'field' => 'user_id',
	        	'rules' => 'required|callback_id_check[User]'
	        ),
	        array(
	        	'field' => 'user_password',
	        	'rules' => 'required'
	        )
        );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        $user_data = array(
        	"user_password"     => md5($this->put('user_password'))
        );

        if ( !$this->User->save($user_data, $this->put('user_id'))) {
        	$this->error_response( get_msg( 'err_user_password_update' )); 
        }

        $this->success_response( get_msg( 'success_profile_update' ));

	}


	/**
	* User Verified Code
	*/
	function verify_post()
	{
		// validation rules for user register
		$rules = array(
			array(
	        	'field' => 'user_id',
	        	'rules' => 'required|callback_id_check[User]'
	        ),
	        array(
	        	'field' => 'code',
	        	'rules' => 'required'
	        )
        );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        $user_verify_data = array(
        	"code"     => $this->post('code'),
        	"user_id"  => $this->post('user_id'),
        	"status"   => 2
        );

        $user_id = $this->User->get_one_by($user_verify_data)->user_id;

        if($user_id  == $this->post('user_id')) {
        	$user_data = array(
	        	"code"    => " ",
	        	"status"  => 1
        	);
        	$this->User->save($user_data,$user_id);
        	$this->custom_response($this->User->get_one($user_id));

        } else {
        	$this->error_response( get_msg( 'invalid_code' )); 
        }

        

	}

	/**
	 * Users Request Code
	 */
	function request_code_post()
	{
		// validation rules for user register
		$rules = array(
	        array(
	        	'field' => 'user_email',
	        	'rules' => 'required'
	        )

        );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        	if (!$this->User->exists( array( 'user_email' => $this->post( 'user_email' ) , 'status' => 2 ))) {

        		$this->error_response( get_msg( 'err_user_not_exist' ));

        	} else {
        		$email = $this->post( 'user_email' );
		        $conds['user_email'] = $email;
		        $conds['status'] = 2;
		        
		        $user_id = $this->User->get_one_by($conds)->user_id;

		        $code = $this->User->get_one_by($conds)->code;

		        if($code == " " ) {

		        	$resend_code = generate_random_string(5);
		        	$user_data_code = array(
			        	"code"    => $resend_code
		        	);
		        	$this->User->save($user_data_code,$user_id);

		        } 

	        
		        $user_data['user_id'] = $user_id;

        		$subject = get_msg('verify_code_sent');

	        	if ( !send_user_register_email( $user_data['user_id'], $subject )) {

					$this->error_response( get_msg( 'user_register_success_but_email_not_send' ));
				
				}
					
				$this->success_response( get_msg( 'success_email_sent' ));

				
        	}

       
    }

    /**
	 * Users Registration with Facebook
	 */
	function facebook_register_post()
	{
		$rules = array(
	        array(
	        	'field' => 'facebook_id',
	        	'rules' => 'required'
	        )
        );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        //Need to check facebook_id is aleady exist or not?
        if ( !$this->User->exists( array( 'facebook_id' => $this->post( 'facebook_id' ) ))) {

        	$rules = array(
		        array(
		        	'field' => 'user_name',
		        	'rules' => 'required'
		        )
	        );

            //User not yet exist 
        	$fb_id = $this->post( 'profile_img_id' );
			$url = "https://graph.facebook.com/$fb_id/picture?width=350&height=500";
		  	$data = file_get_contents($url);
		  	$added_date = date("Y-m-d H:i:s");
		  	
		  	// for uploads

		  	$dir = "uploads/";
			$img = md5(time()).'.jpg';
		  	$ch = curl_init($url);
			$fp = fopen( 'uploads/'. $img, 'wb' );
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);

			//for thumbnail

			$dir = "uploads/thumbnail/";
			$ch = curl_init($url);
			$fp = fopen( 'uploads/thumbnail/'. $img, 'wb' );
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);

			////
			$user_data = array(
	        	"user_name" 	=> $this->post('user_name'), 
	        	'user_email'    => $this->post('user_email'), 
	        	"facebook_id" 	=> $this->post('facebook_id'),
	        	"user_profile_photo" => $img,
	        	"device_token" => $this->post('device_token'),
	        	"role_id"	=> 4,
	        	"facebook_verify" => 1,
	        	"status" 	=> 1, 
		        "code"    => ' ',
		        "added_date" =>  $added_date,
		        "added_date_timestamp" => strtotime($added_date)
        	);


        	$user_email = $user_data['user_email'];
        	//print_r($user_email);die;

        	//if (!empty($user_email)) {
        		//email exists
        		if ($user_data['user_email'] != "") {
					$cond_user_existed['user_email'] = $user_data['user_email'];
	        		$cond_user_existed['phone_id'] = "";
					$user_infos = $this->User->get_email_phone($cond_user_existed)->result();
					$user_id = $user_infos[0]->user_id;
				}
        		
        	//} 
			
        	if ( $user_id != "") {
				//user email alerady exist

				//for user name and user email
				$user_name = $this->post('user_name');
				$user_email = $this->post('user_email');

				if ($user_name == "" && $user_email == "") {
					$user_data = array(
					"user_name" => $user_infos[0]->user_name,
					"user_email" => $user_infos[0]->user_email,	
					"device_token"  => $user_data['device_token'],
					"facebook_id" 	=> $user_data['facebook_id'],
		        	"facebook_verify" => $user_data['facebook_verify'],
		        	'user_profile_photo' => $user_data['user_profile_photo'],
		        	"role_id" => $user_data['role_id'],
		        	"status" 	=> $user_data['status']
					);
				}else if ($user_name == "") {
					$user_data = array(
					"user_name" => $user_infos[0]->user_name,
					"user_email"    => $user_email,
					"device_token"  => $user_data['device_token'],
					"facebook_id" 	=> $user_data['facebook_id'],
		        	"facebook_verify" => $user_data['facebook_verify'],
		        	'user_profile_photo' => $user_data['user_profile_photo'],
		        	"role_id" => $user_data['role_id'],
		        	"status" 	=> $user_data['status']
					);
				}else if ($user_email == "") {
					$user_data = array(
					"user_name"    => $user_name,
					"user_email" => $user_infos[0]->user_email,
					"device_token"  => $user_data['device_token'], 
					"facebook_id" 	=> $user_data['facebook_id'],
		        	"facebook_verify" => $user_data['facebook_verify'],
		        	'user_profile_photo' => $user_data['user_profile_photo'],
		        	"role_id" => $user_data['role_id'],
		        	"status" 	=> $user_data['status']
					);
				}else{
					$user_data = array(
					"user_name"    => $user_name,
					"user_email"    => $user_email,
					"device_token"  => $user_data['device_token'],
					"facebook_id" 	=> $user_data['facebook_id'],
		        	"facebook_verify" => $user_data['facebook_verify'],
		        	'user_profile_photo' => $user_data['user_profile_photo'],
		        	"role_id" => $user_data['role_id'],
		        	"status" 	=> $user_data['status'] 
					);
				}

				$this->User->save($user_data,$user_id);

				$noti_token = array(
					"device_token" => $this->post( 'device_token' )
				);

				$noti_count = $this->Noti->count_all_by($noti_token);

				if ($noti_count == 1) {
					if ( $this->Noti->exists( $noti_token )) {
	        			$noti_id = $this->Noti->get_one_by($noti_token);
	        			$push_noti_token_id = $noti_id->push_noti_token_id;
	        			$noti_data = array(

							"user_id" => $user_id
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        } else {
			            $noti_data = array(

							"user_id" => $user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        }
				}else{
					$this->Noti->delete_by($noti_token);
						$noti_data = array(

							"user_id" => $user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );

				} 
				
			} else {
				//user email not exist
				if ( !$this->User->save($user_data)) {
        			$this->error_response( get_msg( 'err_user_register' ));
        		}

        		$noti_token = array(
    				"device_token" => $this->post( 'device_token' )
    			);

        		$noti_count = $this->Noti->count_all_by($noti_token);

				if ($noti_count == 1) {
					if ( $this->Noti->exists( $noti_token )) {
	        			$noti_id = $this->Noti->get_one_by($noti_token);
	        			$push_noti_token_id = $noti_id->push_noti_token_id;
	        			$noti_data = array(

							"user_id" => $user_data['user_id']
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        } else {
			            $noti_data = array(

							"user_id" => $user_data['user_id'],
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        }
				}else{
					$this->Noti->delete_by($noti_token);
						$noti_data = array(

							"user_id" => $user_data['user_id'],
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );

				} 

				// JWT token generation

				$userdatacs = $this->User->get_one($user_data['user_id']);

				$payload = [
					'user_id' => $userdatacs->user_id ? $userdatacs->user_id : 0,
					'user_email' => $userdatacs->user_email ? $userdatacs->user_email : '',
					'user_phone' => $userdatacs->user_phone ? $userdatacs->user_phone : '',
					'device_token' => $userdatacs->device_token ? $userdatacs->device_token : '',
				];
				
				$token = $this->authorization_token->generateToken($payload);

				$check_data = array(

					"user_id" => $user_data['user_id']
				);

				
				$blacklist_data = $this->Blacklists->get_all_by($check_data);

				if(!empty($blacklist_data->result()))
				{
					foreach($blacklist_data->result() as $blacklist)
					{
						$time_difference = strtotime('now') - strtotime($blacklist->added_date);

						if( $time_difference >= $this->config->item('token_expire_time'))
                        {
							$this->Blacklists->delete( $blacklist->id);
						}
					}
				}

				$userdatacs->token = $token;

        		$this->custom_response($userdatacs);

			}

			// JWT token generation

			$userdatacs = $this->User->get_one($user_infos[0]->user_id);

			$payload = [
				'user_id' => $userdatacs->user_id ? $userdatacs->user_id : 0,
				'user_email' => $userdatacs->user_email ? $userdatacs->user_email : '',
				'user_phone' => $userdatacs->user_phone ? $userdatacs->user_phone : '',
				'device_token' => $userdatacs->device_token ? $userdatacs->device_token : '',
			];
			
			$token = $this->authorization_token->generateToken($payload);

			$check_data = array(

				"user_id" => $user_infos[0]->user_id
			);

			
			$blacklist_data = $this->Blacklists->get_all_by($check_data);

			if(!empty($blacklist_data->result()))
			{
				foreach($blacklist_data->result() as $blacklist)
				{
					$time_difference = strtotime('now') - strtotime($blacklist->added_date);

					if( $time_difference >= $this->config->item('token_expire_time'))
					{
						$this->Blacklists->delete( $blacklist->id);
					}
				}
			}

			$userdatacs->token = $token;

			$this->custom_response($userdatacs);

        	//$this->custom_response($this->User->get_one($user_infos[0]->user_id));

        } else {

        	//User already exist in DB
        	$conds['facebook_id'] = $this->post( 'facebook_id' );
        	$conds1['facebook_id'] = $this->post( 'facebook_id' );
        	$user_profile_photo = $this->User->get_one_by($conds['facebook_id'])->user_profile_photo;

        	//Delete existing image 
        	@unlink('./uploads/'.$user_profile_photo);
			@unlink('./uploads/thumbnail/'.$user_profile_photo);
			//Download again
			$fb_id = $this->post( 'profile_img_id' );
			$url = "https://graph.facebook.com/$fb_id/picture?width=350&height=500";
		  	$data = file_get_contents($url);
		  	
		  	// for uploads
		  	
		  	$dir = "uploads/";
			$img = md5(time()).'.jpg';
		  	$ch = curl_init($url);
			$fp = fopen( 'uploads/'. $img, 'wb' );
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);

			// for thumbnail 

			$dir = "uploads/thumbnail/";
			$ch = curl_init($url);
			$fp = fopen( 'uploads/thumbnail/'. $img, 'wb' );
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);

			$user_data = array(
				'user_name'    	=> $this->post('user_name'), 
				'user_email'    => $this->post('user_email'),
				'user_profile_photo' => $img,
				'device_token'  => $this->post('device_token')
			);

			//for user name and user email
			$user_name = $this->post('user_name');
			$user_email = $this->post('user_email');

			if ($user_name == "" && $user_email == "") {
				$user_data = array(
				'device_token'  => $this->post('device_token'),
				'user_profile_photo' => $user_data['user_profile_photo'], 
				);
			}else if ($user_name == "") {
				$user_data = array(
				'user_email'    => $user_data['user_email'],
				'device_token'  => $user_data['device_token'],
				'user_profile_photo' => $user_data['user_profile_photo'], 
				);
			}else if ($user_email == "") {
				$user_data = array(
				'user_name'    => $user_data['user_name'],
				'device_token'  => $user_data['device_token'], 
				'user_profile_photo' => $user_data['user_profile_photo'],
				);
			}else{
				$user_data = array(
				'user_name'    => $user_data['user_name'],
				'user_email'    => $user_data['user_email'],
				'device_token'  => $user_data['device_token'],
				'user_profile_photo' => $user_data['user_profile_photo'], 
				);
			}	


			$users_data = $this->User->get_one_by($conds1);
			$user_id = $users_data->user_id;

			$conds['facebook_id'] = $this->post( 'facebook_id' );
			$user_datas = $this->User->get_one_by($conds);
			$user_id = $user_datas->user_id;

			if ( $user_datas->is_banned == 1 ) {

				$this->error_response( get_msg( 'err_user_banned' ));
			} else {
				if ( !$this->User->save($user_data,$user_id)) {
        			$this->error_response( get_msg( 'err_user_register' ));
        		}

        		$noti_token = array(
    				"device_token" => $this->post( 'device_token' )
    			);
    	        
        		$noti_count = $this->Noti->count_all_by($noti_token);

				if ($noti_count == 1) {
					if ( $this->Noti->exists( $noti_token )) {
	        			$noti_id = $this->Noti->get_one_by($noti_token);
	        			$push_noti_token_id = $noti_id->push_noti_token_id;
	        			$noti_data = array(

							"user_id" => $user_id
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        } else {
			            $noti_data = array(

							"user_id" => $user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        }
				}else{
					$this->Noti->delete_by($noti_token);
						$noti_data = array(

							"user_id" => $user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );

				} 

			}

			// JWT token generation

			$userdatacs = $this->User->get_one($user_datas->user_id);

			
			$payload = [
				'user_id' => $userdatacs->user_id ? $userdatacs->user_id : 0,
				'user_email' => $userdatacs->user_email ? $userdatacs->user_email : '',
				'user_phone' => $userdatacs->user_phone ? $userdatacs->user_phone : '',
				'device_token' => $userdatacs->device_token ? $userdatacs->device_token : '',
			];

			
			
			$token = $this->authorization_token->generateToken($payload);

			$check_data = array(

				"user_id" => $user_datas->user_id
			);

			
			$blacklist_data = $this->Blacklists->get_all_by($check_data);

			if(!empty($blacklist_data->result()))
			{
				foreach($blacklist_data->result() as $blacklist)
				{
					$time_difference = strtotime('now') - strtotime($blacklist->added_date);

					if( $time_difference >= $this->config->item('token_expire_time'))
					{
						$this->Blacklists->delete( $blacklist->id);
					}
				}
			}
			
			$userdatacs->token = $token;

			$this->custom_response($userdatacs);

        	//$this->custom_response($this->User->get_one($user_datas->user_id));

        }

	}

	/**
	* User Profile Update
	*/
	function password_update_post()
	{

		// validation rules for user register
		$rules = array(
			array(
	        	'field' => 'user_id',
	        	'rules' => 'required|callback_id_check[User]'
	        ),
	        array(
	        	'field' => 'user_password',
	        	'rules' => 'required'
	        )
        );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        $user_data = array(
        	"user_password"     => md5($this->post('user_password'))
        );

        if ( !$this->User->save($user_data, $this->post('user_id'))) {
        	$this->error_response( get_msg( 'err_user_password_update' )); 
        }

        $this->success_response( get_msg( 'success_profile_update' ));

	}

	/**
	* Trigger to delete user related data when user is deleted
	* delete user related data
	*/

	function user_delete_post( ) {

		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
		
		// validation rules for user register
		$rules = array(
			array(
	        	'field' => 'user_id',
	        	'rules' => 'required'
	        )
	    );   
	    
	    // exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        $id = $this->post('user_id');

        $conds['user_id'] = $id;

        // check user id

        $user_data = $this->User->get_one_by($conds);

        //print_r($user_data);die;


        if ( $user_data->user_id == "" ) {

        	$this->error_response( get_msg( 'invalid_user_id' ));

        }elseif ( $user_data->user_is_sys_admin == 1) {
        	$this->error_response( get_msg( 'user_not_able_delete' ));
        } else {
        	$conds_user['user_id'] = $id;
			$conds_from_user['from_user_id'] = $id;
			$conds_to_user['to_user_id'] = $id;
			$conds_added_user['added_user_id'] = $id;
			$conds_followed_user['followed_user_id'] = $id;
			$conds_buyer_user['buyer_user_id'] = $id;
			$conds_seller_user['seller_user_id'] = $id;

			//delete User
			if ( !$this->User->delete_by( $conds_user )) {

				return false;
			}

			// delete Rating
			$this->Rate->delete_by( $conds_from_user );
			$this->Rate->delete_by( $conds_to_user );

			// delete push notification users
			if ( !$this->Notireaduser->delete_by( $conds_user )) {

				return false;
			}

			// delete push notification tokens
			if ( !$this->Noti->delete_by( $conds_user )) {

				return false;
			}

			// delete items and others related table

			$items_data 	= $this->Item->get_one_by($conds_added_user);

			$item_data['item_id'] = $items_data->id;
	        $img_data['img_parent_id'] = $items_data->id;

	        $this->Chat->delete_by( $item_data );
	        $this->Paid_item->delete_by( $item_data );
	        $this->Favourite->delete_by( $item_data );
	        $this->Itemreport->delete_by( $item_data );
	        $this->Touch->delete_by( $item_data );
	        $this->Image->delete_by( $img_data );

			if ( !$this->Item->delete_by( $conds_added_user )) {

				return false;
			}

			//delete follows

			$following_user		= $this->Userfollow->get_all_by( $conds_user )->result();
			$follower_user		= $this->Userfollow->get_all_by( $conds_followed_user )->result();

			foreach ($following_user as $following) {

				$conds_follower['user_id'] = $following->followed_user_id;

				$follower_user_data = $this->User->get_one_by( $conds_follower );
				
				$follower_count = $follower_user_data->follower_count;

				$user_data = array(
				 	"follower_count" => $follower_count - 1
				 );

				 $this->User->save($user_data,$follower_user_data->user_id);
				

			}


			foreach ($follower_user as $follower) {

				$conds_follow['user_id'] = $follower->user_id;

				$following_user_data = $this->User->get_one_by( $conds_follow );

				$following_count = $following_user_data->following_count;

				$user_data = array(
				 	"following_count" => $following_count - 1
				 );

				 $this->User->save($user_data,$following_user_data->user_id);

			}
			
			$this->Userfollow->delete_by( $conds_user );
			$this->Userfollow->delete_by( $conds_followed_user );
			

			// delete Favourite
			if ( !$this->Favourite->delete_by( $conds_user )) {

				return false;
			}

			// delete Chat History

			$this->Chat->delete_by( $conds_buyer_user );
			$this->Chat->delete_by( $conds_seller_user ); 
			
			$this->success_response( get_msg( 'success_delete' ));

        }


	}


	/**
	* Return User Unread Count For Chat Notification and Blog
	*/

	function unread_count_post( ) {

		$rules = array(
			array(
	        	'field' => 'user_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'device_token',
	        	'rules' => 'required'
	        )
        );

        // exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        $blog_noti_unread_count = 0;

        //get all noti_id by user_id and device_token from bs_push_notification_users table

        $conds['user_id'] 	   = $this->post('user_id');
        $conds['device_token'] = $this->post('device_token');

        $blog_notis = $this->Notireaduser->get_all_by($conds)->result();

        

        foreach ( $blog_notis as $blog_noti ) {

			$id .= "'" .$blog_noti->noti_id . "',";
		
		}

		

		if ($id == "") {

		 	$obj = $this->Noti_message->get_all()->result();
		 	$blog_noti_unread_count = count($obj);

		} else {

			$result = rtrim($id,',');
			
			$conds_blog_noti['$id'] = $result;

			

			$obj = $this->Noti_message->get_all_not_in_noti($conds_blog_noti)->result();
			


			$blog_noti_unread_count = count($obj);
		}


		//For buyer_unread_count

        $buyer_unread_count = 0;

        $conds_chat['buyer_user_id'] = $this->post('user_id');

        $chats_buyer_unread_records = $this->Chat->get_all_by($conds_chat)->result();


        foreach ( $chats_buyer_unread_records as $chats_buyer_unread_record ) {

			$buyer_unread_count += $chats_buyer_unread_record->buyer_unread_count;
		
		}

        //For seller_unread_count

        $seller_unread_count = 0;

        $conds_chat_seller['seller_user_id'] = $this->post('user_id');

       
        $chats_seller_unread_records = $this->Chat->get_all_by($conds_chat_seller)->result();

        foreach ( $chats_seller_unread_records as $chats_seller_unread_record ) {

			$seller_unread_count += $chats_seller_unread_record->seller_unread_count;


		
		}


		$count_object = new stdClass; 
		$count_object->blog_noti_unread_count  = $blog_noti_unread_count; 
		$count_object->buyer_unread_count      = $buyer_unread_count; 
		$count_object->seller_unread_count     = $seller_unread_count; 
		

		$final_data = $this->ps_security->clean_output( $count_object );


		$this->response( $final_data );


	}

	/**
	 * Users Registration with Google
	*/
	function google_register_post()
	{
		$rules = array(
	        array(
	        	'field' => 'google_id',
	        	'rules' => 'required'
	        )
        );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        //Need to check google_id is aleady exist or not?
        if ( !$this->User->exists( 
        	array( 
        		'google_id' => $this->post( 'google_id' ) 
        		))) {

        	$rules = array(
				array(
		        	'field' => 'user_name',
		        	'rules' => 'required'
		        )
			);
        
            //User not yet exist 
        	$gg_id = $this->post( 'google_id' ) ;
			$url = $this->post('profile_photo_url');
			$added_date =  date("Y-m-d H:i:s");

			if ($url !="") {

				//for uploads

				$data = file_get_contents($url);
			  	$dir = "uploads/";
				$img = md5(time()).'.jpg';
			  	$ch = curl_init($url);
				$fp = fopen( 'uploads/'. $img, 'wb' );
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_exec($ch);
				curl_close($ch);
				fclose($fp);

				// for thumbnail

				$dir = "uploads/thumbnail/";
			  	$ch = curl_init($url);
				$fp = fopen( 'uploads/thumbnail/'. $img, 'wb' );
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_exec($ch);
				curl_close($ch);
				fclose($fp);

				$user_data = array(
		        	"user_name" 	=> $this->post('user_name'), 
		        	'user_email'    => $this->post('user_email'), 
		        	"google_id" 	=> $this->post('google_id'),
		        	"user_profile_photo" => $img,
		        	"device_token" => $this->post('device_token'),
		        	"role_id" => 4,
		        	"google_verify" => 1,
		        	"status" 	=> 1, 
			        "code"   => ' ',
			        "added_date" => $added_date,
			        "added_date_timestamp" => strtotime($added_date)
	        	);

			} else{

					$user_data = array(
		        	"user_name" 	=> $this->post('user_name'), 
		        	'user_email'    => $this->post('user_email'), 
		        	"google_id" 	=> $this->post('google_id'),
		        	"device_token" => $this->post('device_token'),
		        	"role_id" => 4,
		        	"google_verify" => 1,
		        	"status" 	=> 1, 
			        "code"   => ' ',
			        "added_date" => $added_date,
			        "added_date_timestamp" => strtotime($added_date)
        		);
			}

			if ($user_data['user_email'] != "") {
				$cond_user_existed['user_email'] = $user_data['user_email'];
        		$cond_user_existed['phone_id'] = "";
				$user_infos = $this->User->get_email_phone($cond_user_existed)->result();
				$user_id = $user_infos[0]->user_id;
			}
			        	
			if ( $user_id != "") {
				//user email alerady exist

				//for user name and user email
				$user_name = $this->post('user_name');
				$user_email = $this->post('user_email');

				if ($user_name == "" && $user_email == "") {
					$user_data = array(
					"user_name" => $user_infos[0]->user_name,
					"user_email" => $user_infos[0]->user_email,	
					"device_token"  => $user_data['device_token'],
					"google_id" 	=> $user_data['google_id'],
		        	"google_verify" => $user_data['google_verify'],
		        	'user_profile_photo' => $user_data['user_profile_photo'],
		        	"role_id" => $user_data['role_id'],
		        	"status" 	=> $user_data['status']
					);
				}else if ($user_name == "") {
					$user_data = array(
					"user_name" => $user_infos[0]->user_name,
					"user_email"    => $user_email,
					"device_token"  => $user_data['device_token'],
					"google_id" 	=> $user_data['google_id'],
		        	"google_verify" => $user_data['google_verify'],
		        	'user_profile_photo' => $user_data['user_profile_photo'],
		        	"role_id" => $user_data['role_id'],
		        	"status" 	=> $user_data['status']
					);
				}else if ($user_email == "") {
					$user_data = array(
					"user_name"    => $user_name,
					"user_email" => $user_infos[0]->user_email,
					"device_token"  => $user_data['device_token'], 
					"google_id" 	=> $user_data['google_id'],
		        	"google_verify" => $user_data['google_verify'],
		        	'user_profile_photo' => $user_data['user_profile_photo'],
		        	"role_id" => $user_data['role_id'],
		        	"status" 	=> $user_data['status']
					);
				}else{
					$user_data = array(
					"user_name"    => $user_name,
					"user_email"    => $user_email,
					"device_token"  => $user_data['device_token'],
					"google_id" 	=> $user_data['google_id'],
		        	"google_verify" => $user_data['google_verify'],
		        	'user_profile_photo' => $user_data['user_profile_photo'],
		        	"role_id" => $user_data['role_id'],
		        	"status" 	=> $user_data['status'] 
					);
				}

				$this->User->save($user_data,$user_id);

				$noti_token = array(
    				"device_token" => $this->post( 'device_token' )
    			);

    			$noti_count = $this->Noti->count_all_by($noti_token);

				if ($noti_count == 1) {
					if ( $this->Noti->exists( $noti_token )) {
	        			$noti_id = $this->Noti->get_one_by($noti_token);
	        			$push_noti_token_id = $noti_id->push_noti_token_id;
	        			$noti_data = array(

							"user_id" => $user->user_id
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        } else {
			            $noti_data = array(

							"user_id" => $user->user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        }
				}else{
					$this->Noti->delete_by($noti_token);
						$noti_data = array(

							"user_id" => $user->user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );

				}
    	        
			} else {
				//user email not exist
				if ( !$this->User->save($user_data)) {
        		$this->error_response( get_msg( 'err_user_register' ));
        		}

        		$noti_token = array(
    				"device_token" => $this->post( 'device_token' )
    			);

        		$noti_count = $this->Noti->count_all_by($noti_token);

				if ($noti_count == 1) {
					if ( $this->Noti->exists( $noti_token )) {
	        			$noti_id = $this->Noti->get_one_by($noti_token);
	        			$push_noti_token_id = $noti_id->push_noti_token_id;
	        			$noti_data = array(

							"user_id" => $user_data['user_id']
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        } else {
			            $noti_data = array(

							"user_id" => $user_data['user_id'],
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        }
				}else{
					$this->Noti->delete_by($noti_token);
						$noti_data = array(

							"user_id" => $user_data['user_id'],
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );

				}

				// JWT token generation
				$userdatacs = $this->User->get_one($user_data['user_id']);

			
				$payload = [
					'user_id' => $userdatacs->user_id ? $userdatacs->user_id : 0,
					'user_email' => $userdatacs->user_email ? $userdatacs->user_email : '',
					'user_phone' => $userdatacs->user_phone ? $userdatacs->user_phone : '',
					'device_token' => $userdatacs->device_token ? $userdatacs->device_token : '',
				];

				
				
				$token = $this->authorization_token->generateToken($payload);

				$check_data = array(

					"user_id" => $user_data['user_id']
				);

				
				$blacklist_data = $this->Blacklists->get_all_by($check_data);

				if(!empty($blacklist_data->result()))
				{
					foreach($blacklist_data->result() as $blacklist)
					{
						$time_difference = strtotime('now') - strtotime($blacklist->added_date);

						if( $time_difference >= $this->config->item('token_expire_time'))
                        {
							$this->Blacklists->delete( $blacklist->id);
						}
					}
				}
				
				$userdatacs->token = $token;

				$this->custom_response($userdatacs); 	

        		//$this->custom_response($this->User->get_one($user_data['user_id']));

			}

			// JWT token generation
			$userdatacs = $this->User->get_one($user_infos[0]->user_id);

			
			$payload = [
				'user_id' => $userdatacs->user_id ? $userdatacs->user_id : 0,
				'user_email' => $userdatacs->user_email ? $userdatacs->user_email : '',
				'user_phone' => $userdatacs->user_phone ? $userdatacs->user_phone : '',
				'device_token' => $userdatacs->device_token ? $userdatacs->device_token : '',
			];

			
			
			$token = $this->authorization_token->generateToken($payload);

			$check_data = array(

				"user_id" => $user_infos[0]->user_id
			);

			
			$blacklist_data = $this->Blacklists->get_all_by($check_data);

			if(!empty($blacklist_data->result()))
			{
				foreach($blacklist_data->result() as $blacklist)
				{
					$time_difference = strtotime('now') - strtotime($blacklist->added_date);

					if( $time_difference >= $this->config->item('token_expire_time'))
					{
						$this->Blacklists->delete( $blacklist->id);
					}
				}
			}
			
			$userdatacs->token = $token;

			$this->custom_response($userdatacs); 	

        	//$this->custom_response($this->User->get_one($user_infos[0]->user_id));

        } else {

        	//User already exist in DB
        	$conds['google_id'] = $this->post( 'google_id' );
        	$user_profile_photo = $this->User->get_one_by($conds['google_id'])->user_profile_photo;

        	//Delete existing image 
        	@unlink('./uploads/'.$user_profile_photo);
			@unlink('./uploads/thumbnail/'.$user_profile_photo);
			//Download again
			$gg_id = $this->post( 'google_id' ) ;
			$url = $this->post('profile_photo_url');
		  	
			if($url != "") {
			  	$data = file_get_contents($url);
			  	
			  	// for uploads

			  	$dir = "uploads/";
				$img = md5(time()).'.jpg';
			  	$ch = curl_init($url);
				$fp = fopen( 'uploads/'. $img, 'wb' );
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_exec($ch);
				curl_close($ch);
				fclose($fp);

				// for thumbnail
				
				$dir = "uploads/thumbnail/";
			  	$ch = curl_init($url);
				$fp = fopen( 'uploads/thumbnail/'. $img, 'wb' );
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_exec($ch);
				curl_close($ch);
				fclose($fp);

				$user_data = array(
					'user_name'    	=> $this->post('user_name'), 
					'user_email'    => $this->post('user_email'),
					'device_token'  => $this->post('device_token'), 
					'user_profile_photo' => $img	
				);
			} else {

				$user_data = array(
					'user_name'    	=> $this->post('user_name'), 
					'user_email'    => $this->post('user_email'),
					'device_token'  => $this->post('device_token')
				);
			}

			//for user name and user email
			$user_name = $this->post('user_name');
			$user_email = $this->post('user_email');

			if ($user_name == "" && $user_email == "") {
				$user_data = array(
				'device_token'  => $this->post('device_token'),
				'user_profile_photo' => $user_data['user_profile_photo'], 
				);
			}else if ($user_name == "") {
				$user_data = array(
				'user_email'    => $user_data['user_email'],
				'device_token'  => $user_data['device_token'], 
				'user_profile_photo' => $user_data['user_profile_photo'],
				);
			}else if ($user_email == "") {
				$user_data = array(
				'user_name'    => $user_data['user_name'],
				'device_token'  => $user_data['device_token'], 
				'user_profile_photo' => $user_data['user_profile_photo'],
				);
			}else{
				$user_data = array(
				'user_name'    => $user_data['user_name'],
				'user_email'    => $user_data['user_email'],
				'device_token'  => $user_data['device_token'], 
				'user_profile_photo' => $user_data['user_profile_photo'],
				);
			}	

			$conds['google_id'] = $this->post( 'google_id' );
			$user_datas = $this->User->get_one_by($conds);
			$user_id = $user_datas->user_id;
			//print_r($user_id);die;

			if ( $user_datas->is_banned == 1 ) {

				$this->error_response( get_msg( 'err_user_banned' ));
			} else {
				if ( !$this->User->save($user_data,$user_id)) {
	        		$this->error_response( get_msg( 'err_user_register' ));
	        	}

	        	$noti_token = array(
    				"device_token" => $this->post( 'device_token' )
    			);

	        	$noti_count = $this->Noti->count_all_by($noti_token);

				if ($noti_count == 1) {
					if ( $this->Noti->exists( $noti_token )) {
	        			$noti_id = $this->Noti->get_one_by($noti_token);
	        			$push_noti_token_id = $noti_id->push_noti_token_id;
	        			$noti_data = array(

							"user_id" => $user_id
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        } else {
			            $noti_data = array(

							"user_id" => $user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        }
				}else{
					$this->Noti->delete_by($noti_token);
						$noti_data = array(

							"user_id" => $user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );

				}

			}

			// JWT token generation
			$userdatacs = $this->User->get_one($user_datas->user_id);

			
			$payload = [
				'user_id' => $userdatacs->user_id ? $userdatacs->user_id : 0,
				'user_email' => $userdatacs->user_email ? $userdatacs->user_email : '',
				'user_phone' => $userdatacs->user_phone ? $userdatacs->user_phone : '',
				'device_token' => $userdatacs->device_token ? $userdatacs->device_token : '',
			];

			
			
			$token = $this->authorization_token->generateToken($payload);

			$check_data = array(

				"user_id" => $user_datas->user_id
			);

			
			$blacklist_data = $this->Blacklists->get_all_by($check_data);

			if(!empty($blacklist_data->result()))
			{
				foreach($blacklist_data->result() as $blacklist)
				{
					$time_difference = strtotime('now') - strtotime($blacklist->added_date);

					if( $time_difference >= $this->config->item('token_expire_time'))
					{
						$this->Blacklists->delete( $blacklist->id);
					}
				}
			}
			
			$userdatacs->token = $token;

			$this->custom_response($userdatacs); 	

        	//$this->custom_response($this->User->get_one($user_datas->user_id));

        }


	}

	/**
	 * Users Registration with Phone
	*/
	function phone_register_post()
	{
		$rules = array(
	        array(
	        	'field' => 'phone_id',
	        	'rules' => 'required'
			),
			array(
	        	'field' => 'user_id',
	        	'rules' => 'required'
			),
        );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        //Need to check phone_id is aleady exist or not?
        if ( !$this->User->exists( 
					//new
					array('phone_id' => $this->post( 'phone_id' ))
				)
			) {
        	//$rules = array(array('field' => 'user_name','rules' => 'required'));

	        $added_date = date("Y-m-d H:i:s");

			$user_data = array(
				//"user_id"  => $this->post('user_id'),
	        	//"user_name" 	=> $this->post('user_name'), 
	        	"user_phone"    => $this->post('user_phone'), 
	        	"phone_id" 	   => $this->post('phone_id'),
	        	"device_token" => $this->post('device_token'),
	        	"role_id" => 4,
	        	"phone_verify" => 1,
	        	"status" => 1,
	        	"added_date" =>  $added_date,
	        	"added_date_timestamp" => strtotime($added_date)
        	);

			$user_id = $this->post('user_id');
        	$conds_phone['user_phone'] = $user_data['user_phone'];
			$conds_phone['user_id'] = $user_data['user_id'];
			$user_infos = $this->User->get_one_user_phone($conds_phone)->result();
			$user_id_db = $user_infos[0]->user_id;


			//if ( $user_id_db != "" && $user_id_db == $user_id) {
			if ( !empty($user_infos)) {
				//user phone alerady exist

				//for user name and user email
				$user_name = $user_infos[0]->user_name;
				$user_phone = $this->post('user_phone');

				/*if ($user_name == "" && $user_phone == "") {
					$user_data = array(
					"user_name" => $user_infos[0]->user_name,
					"user_phone" => $user_infos[0]->user_phone,	
					"device_token"  => $user_data['device_token'],
					"phone_id" 	=> $user_data['phone_id'],
					"phone_verify" => $user_data['phone_verify'],
					"role_id" => $user_data['role_id'],
					"status" 	=> $user_data['status']
					);
				} else if ($user_name == "") {
					$user_data = array(
					"user_name" => $user_infos[0]->user_name,
					"user_phone"    => $user_phone,
					"device_token"  => $user_data['device_token'],
					"phone_id" 	=> $user_data['phone_id'],
					"phone_verify" => $user_data['phone_verify'],
					"role_id" => $user_data['role_id'],
					"status" 	=> $user_data['status']
					);
				} else if ($user_phone == "") {
					$user_data = array(
					"user_name"    => $user_name,
					"user_phone" => $user_infos[0]->user_phone,
					"device_token"  => $user_data['device_token'], 
					"phone_id" 	=> $user_data['phone_id'],
					"phone_verify" => $user_data['phone_verify'],
					"role_id" => $user_data['role_id'],
					"status" 	=> $user_data['status']
					);
				} else{*/
					$user_data = array(
					//"user_name"    => $user_name,
					"user_phone"    => $user_phone,
					"device_token"  => $user_data['device_token'],
					"phone_id" 	=> $user_data['phone_id'],
					"phone_verify" => $user_data['phone_verify'],
					"role_id" => $user_data['role_id'],
					"status" 	=> $user_data['status']
					);
				//}
				$this->User->save($user_data,$user_id);
				$noti_token = array("device_token" => $this->post( 'device_token' ));

				$noti_count = $this->Noti->count_all_by($noti_token);

				if ($noti_count == 1) {
					if ( $this->Noti->exists( $noti_token )) {
						$noti_id = $this->Noti->get_one_by($noti_token);
						$push_noti_token_id = $noti_id->push_noti_token_id;
						$noti_data = array(

							"user_id" => $user_id
							
						);
						$this->Noti->save( $noti_data, $push_noti_token_id );
					} else {
						$noti_data = array(

							"user_id" => $user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
						$this->Noti->save( $noti_data, $push_noti_token_id );
					}
				}else{
					$this->Noti->delete_by($noti_token);
						$noti_data = array(

							"user_id" => $user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
						$this->Noti->save( $noti_data, $push_noti_token_id );

				}
				
			} else {
				
				//user phone no not exist
				if ( !$this->User->save($user_data, $user_id)) {
        		$this->error_response( get_msg( 'err_user_phone_verification' ));
        		}
				
		        $noti_token = array(
    				"device_token" => $this->post( 'device_token' )
    			);

		        $noti_count = $this->Noti->count_all_by($noti_token);
				
				if ($noti_count == 1) {
					if ( $this->Noti->exists( $noti_token )) {
	        			$noti_id = $this->Noti->get_one_by($noti_token);
	        			$push_noti_token_id = $noti_id->push_noti_token_id;
	        			$noti_data = array(

							"user_id" => $user_data['user_id']
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        } else {
			            $noti_data = array(

							"user_id" => $user_data['user_id'],
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        }
				}else{
					$this->Noti->delete_by($noti_token);
						$noti_data = array(

							"user_id" => $user_data['user_id'],
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );

				} 

				//var_dump($user_data['user_id']);
        		//$this->custom_response($this->User->get_one($user_data['user_id']));
				$this->success_response( get_msg( 'success_user_mobile_verify' ));

			}

        	//$this->custom_response($this->User->get_one($user_infos[0]->user_id));
			$this->success_response( get_msg( 'success_user_mobile_verify' ));

        } else {
        	//update
        	//User already exist in DB
			$user_data = array(
				//"user_name"    	=> $this->post('user_name'), 
				"user_phone"    => $this->post('user_phone'),
				"device_token" => $this->post('device_token')
			);

			//for user name and user email
			$user_name = $this->post('user_name');
			$user_phone = $this->post('user_phone');

			/*if ($user_name == "" && $user_phone == "") {
				$user_data = array(
				'device_token'  => $this->post('device_token'), 
				);
			}else if ($user_name == "") {
				$user_data = array(
				'user_phone'    => $user_data['user_phone'],
				'device_token'  => $user_data['device_token'], 
				);
			}else if ($user_phone == "") {
				$user_data = array(
				'user_name'    => $user_data['user_name'],
				'device_token'  => $user_data['device_token'], 
				);
			}else{*/
				$user_data = array(
				//'user_name'    => $user_data['user_name'],
				'user_phone'    => $user_data['user_phone'],
				'device_token'  => $user_data['device_token'], 
				);
			//}

			$conds['phone_id'] = $this->post( 'phone_id' );
			$user_datas = $this->User->get_one_by($conds);
			//$user_id = $user_datas->user_id;
			$user_id = $this->post('user_id');

			if ( $user_datas->is_banned == 1 ) {

				$this->error_response( get_msg( 'err_user_banned' ));
				
			} else {
				if ( !$this->User->save($user_data,$user_id)) {
	        		$this->error_response( get_msg( 'err_user_register' ));
	        	}

	        	$noti_token = array(
    				"device_token" => $this->post( 'device_token' )
    			);

	        	$noti_count = $this->Noti->count_all_by($noti_token);

				if ($noti_count == 1) {
					if ( $this->Noti->exists( $noti_token )) {
	        			$noti_id = $this->Noti->get_one_by($noti_token);
	        			$push_noti_token_id = $noti_id->push_noti_token_id;
	        			$noti_data = array(

							"user_id" => $user_id
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        } else {
			            $noti_data = array(

							"user_id" => $user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        }
				}else{
					$this->Noti->delete_by($noti_token);
						$noti_data = array(

							"user_id" => $user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );

				} 

			}

        	//$this->custom_response($this->User->get_one($user_datas->user_id));
			$this->success_response( get_msg( 'success_user_mobile_verify' ));

        }

	}


	/**
	 * Users Registration with Phone
	*/
	/*function old_phone_register_post()
	{
		$rules = array(
	        array(
	        	'field' => 'phone_id',
	        	'rules' => 'required'
	        )
        );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        //Need to check phone_id is aleady exist or not?
        if ( !$this->User->exists( 
					//new
					array('phone_id' => $this->post( 'phone_id' ))
				)
			) {
        	$rules = array(array('field' => 'user_name','rules' => 'required'));

	        $added_date = date("Y-m-d H:i:s");

			$user_data = array(
	        	"user_name" 	=> $this->post('user_name'), 
	        	"user_phone"    => $this->post('user_phone'), 
	        	"phone_id" 	   => $this->post('phone_id'),
	        	"device_token" => $this->post('device_token'),
	        	"role_id" => 4,
	        	"phone_verify" => 1,
	        	"status" => 1,
	        	"added_date" =>  $added_date,
	        	"added_date_timestamp" => strtotime($added_date)
        	);


        	$conds_phone['user_phone'] = $user_data['user_phone'];
			$user_infos = $this->User->get_one_user_phone($conds_phone)->result();
			$user_id = $user_infos[0]->user_id;


			if ( $user_id != "") {
				//user phone alerady exist

				//for user name and user email
				$user_name = $this->post('user_name');
				$user_phone = $this->post('user_phone');

				if ($user_name == "" && $user_phone == "") {
					$user_data = array(
					"user_name" => $user_infos[0]->user_name,
					"user_phone" => $user_infos[0]->user_phone,	
					"device_token"  => $user_data['device_token'],
					"phone_id" 	=> $user_data['phone_id'],
					"phone_verify" => $user_data['phone_verify'],
					"role_id" => $user_data['role_id'],
					"status" 	=> $user_data['status']
					);
				} else if ($user_name == "") {
					$user_data = array(
					"user_name" => $user_infos[0]->user_name,
					"user_phone"    => $user_phone,
					"device_token"  => $user_data['device_token'],
					"phone_id" 	=> $user_data['phone_id'],
					"phone_verify" => $user_data['phone_verify'],
					"role_id" => $user_data['role_id'],
					"status" 	=> $user_data['status']
					);
				} else if ($user_phone == "") {
					$user_data = array(
					"user_name"    => $user_name,
					"user_phone" => $user_infos[0]->user_phone,
					"device_token"  => $user_data['device_token'], 
					"phone_id" 	=> $user_data['phone_id'],
					"phone_verify" => $user_data['phone_verify'],
					"role_id" => $user_data['role_id'],
					"status" 	=> $user_data['status']
					);
				} else{
					$user_data = array(
					"user_name"    => $user_name,
					"user_phone"    => $user_phone,
					"device_token"  => $user_data['device_token'],
					"phone_id" 	=> $user_data['phone_id'],
					"phone_verify" => $user_data['phone_verify'],
					"role_id" => $user_data['role_id'],
					"status" 	=> $user_data['status']
					);
				}
				$this->User->save($user_data,$user_id);
				$noti_token = array("device_token" => $this->post( 'device_token' ));

				$noti_count = $this->Noti->count_all_by($noti_token);

				if ($noti_count == 1) {
					if ( $this->Noti->exists( $noti_token )) {
						$noti_id = $this->Noti->get_one_by($noti_token);
						$push_noti_token_id = $noti_id->push_noti_token_id;
						$noti_data = array(

							"user_id" => $user_id
							
						);
						$this->Noti->save( $noti_data, $push_noti_token_id );
					} else {
						$noti_data = array(

							"user_id" => $user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
						$this->Noti->save( $noti_data, $push_noti_token_id );
					}
				}else{
					$this->Noti->delete_by($noti_token);
						$noti_data = array(

							"user_id" => $user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
						$this->Noti->save( $noti_data, $push_noti_token_id );

				} 

				
				
			} else {
				//user phone not exist
				if ( !$this->User->save($user_data)) {
        		$this->error_response( get_msg( 'err_user_register' ));
        		}
				
		        $noti_token = array(
    				"device_token" => $this->post( 'device_token' )
    			);

		        $noti_count = $this->Noti->count_all_by($noti_token);

				if ($noti_count == 1) {
					if ( $this->Noti->exists( $noti_token )) {
	        			$noti_id = $this->Noti->get_one_by($noti_token);
	        			$push_noti_token_id = $noti_id->push_noti_token_id;
	        			$noti_data = array(

							"user_id" => $user_data['user_id']
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        } else {
			            $noti_data = array(

							"user_id" => $user_data['user_id'],
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        }
				}else{
					$this->Noti->delete_by($noti_token);
						$noti_data = array(

							"user_id" => $user_data['user_id'],
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );

				} 


        		$this->custom_response($this->User->get_one($user_data['user_id']));

			}

        	$this->custom_response($this->User->get_one($user_infos[0]->user_id));

        } else {
        	//update
        	//User already exist in DB
			$user_data = array(
				"user_name"    	=> $this->post('user_name'), 
				"user_phone"    => $this->post('user_phone'),
				"device_token" => $this->post('device_token')
			);

			//for user name and user email
			$user_name = $this->post('user_name');
			$user_phone = $this->post('user_phone');

			if ($user_name == "" && $user_phone == "") {
				$user_data = array(
				'device_token'  => $this->post('device_token'), 
				);
			}else if ($user_name == "") {
				$user_data = array(
				'user_phone'    => $user_data['user_phone'],
				'device_token'  => $user_data['device_token'], 
				);
			}else if ($user_phone == "") {
				$user_data = array(
				'user_name'    => $user_data['user_name'],
				'device_token'  => $user_data['device_token'], 
				);
			}else{
				$user_data = array(
				'user_name'    => $user_data['user_name'],
				'user_phone'    => $user_data['user_phone'],
				'device_token'  => $user_data['device_token'], 
				);
			}

			$conds['phone_id'] = $this->post( 'phone_id' );
			$user_datas = $this->User->get_one_by($conds);
			$user_id = $user_datas->user_id;

			if ( $user_datas->is_banned == 1 ) {

				$this->error_response( get_msg( 'err_user_banned' ));
				
			} else {
				if ( !$this->User->save($user_data,$user_id)) {
	        		$this->error_response( get_msg( 'err_user_register' ));
	        	}

	        	$noti_token = array(
    				"device_token" => $this->post( 'device_token' )
    			);

	        	$noti_count = $this->Noti->count_all_by($noti_token);

				if ($noti_count == 1) {
					if ( $this->Noti->exists( $noti_token )) {
	        			$noti_id = $this->Noti->get_one_by($noti_token);
	        			$push_noti_token_id = $noti_id->push_noti_token_id;
	        			$noti_data = array(

							"user_id" => $user_id
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        } else {
			            $noti_data = array(

							"user_id" => $user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        }
				}else{
					$this->Noti->delete_by($noti_token);
						$noti_data = array(

							"user_id" => $user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );

				} 

			}

        	$this->custom_response($this->User->get_one($user_datas->user_id));

        }

	}*/

	/**
	 * Users Registration with Apple
	*/

	function apple_register_post()
	{
		$rules = array(
	        array(
	        	'field' => 'apple_id',
	        	'rules' => 'required'
	        )
        );

		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        //Need to check apple_id is aleady exist or not?
        if ( !$this->User->exists( 
        	array( 
        		'apple_id' => $this->post( 'apple_id' ) 
        		))) {

        	$rules = array(
				array(
		        	'field' => 'user_name',
		        	'rules' => 'required'
		        )
        	);

        	$added_date = date("Y-m-d H:i:s");
        
            $user_data = array(
	        	"user_name" 	=> $this->post('user_name'), 
	        	"user_email"    => $this->post('user_email'), 
	        	"apple_id" 	=> $this->post('apple_id'),
	        	"device_token" => $this->post('device_token'),
	        	"role_id" => 4,
	        	"apple_verify" => 1,
	        	"status" 	=> 1, 
		        "code"   => ' ',
		        "added_date" =>  $added_date,
		        "added_date_timestamp" => strtotime($added_date)
    		);

        	if ($user_data['user_email'] != "") {
				$cond_user_existed['user_email'] = $user_data['user_email'];
        		$cond_user_existed['phone_id'] = "";
				$user_infos = $this->User->get_email_phone($cond_user_existed)->result();
				$user_id = $user_infos[0]->user_id;
			}

			if ( $user_id != "") {
				//user email alerady exist

				//for user name and user email
				$user_name = $this->post('user_name');
				$user_email = $this->post('user_email');

				if ($user_name == "" && $user_email == "") {
					$user_data = array(
					"user_name" => $user_infos[0]->user_name,
					"user_email" => $user_infos[0]->user_email,	
					"device_token"  => $user_data['device_token'],
					"apple_id" 	=> $user_data['apple_id'],
		        	"apple_verify" => $user_data['apple_verify'],
		        	"role_id" => $user_data['role_id'],
		        	"status" 	=> $user_data['status']
					);
				}else if ($user_name == "") {
					$user_data = array(
					"user_name" => $user_infos[0]->user_name,
					"user_email"    => $user_email,
					"device_token"  => $user_data['device_token'],
					"apple_id" 	=> $user_data['apple_id'],
		        	"apple_verify" => $user_data['apple_verify'],
		        	"role_id" => $user_data['role_id'],
		        	"status" 	=> $user_data['status']
					);
				}else if ($user_email == "") {
					$user_data = array(
					"user_name"    => $user_name,
					"user_email" => $user_infos[0]->user_email,
					"device_token"  => $user_data['device_token'], 
					"apple_id" 	=> $user_data['apple_id'],
		        	"apple_verify" => $user_data['apple_verify'],
		        	"role_id" => $user_data['role_id'],
		        	"status" 	=> $user_data['status']
					);
				}else{
					$user_data = array(
					"user_name"    => $user_name,
					"user_email"    => $user_email,
					"device_token"  => $user_data['device_token'],
					"apple_id" 	=> $user_data['apple_id'],
		        	"apple_verify" => $user_data['apple_verify'],
		        	"role_id" => $user_data['role_id'],
		        	"status" 	=> $user_data['status'] 
					);
				}

				$this->User->save($user_data,$user_id);

				$noti_token = array(
    				"device_token" => $this->post( 'device_token' )
    			);    	        

	        	$noti_count = $this->Noti->count_all_by($noti_token);

				if ($noti_count == 1) {
					if ( $this->Noti->exists( $noti_token )) {
	        			$noti_id = $this->Noti->get_one_by($noti_token);
	        			$push_noti_token_id = $noti_id->push_noti_token_id;
	        			$noti_data = array(

							"user_id" => $user_id
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        } else {
			            $noti_data = array(

							"user_id" => $user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        }
				}else{
					$this->Noti->delete_by($noti_token);
						$noti_data = array(

							"user_id" => $user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );

				} 
				
			} else {
				//user email not exist
				if ( !$this->User->save($user_data)) {
        		$this->error_response( get_msg( 'err_user_register' ));
        		}

        		$noti_token = array(
    				"device_token" => $this->post( 'device_token' )
    			);

	        	$noti_count = $this->Noti->count_all_by($noti_token);

				if ($noti_count == 1) {
					if ( $this->Noti->exists( $noti_token )) {
	        			$noti_id = $this->Noti->get_one_by($noti_token);
	        			$push_noti_token_id = $noti_id->push_noti_token_id;
	        			$noti_data = array(

							"user_id" => $user_data['user_id']
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        } else {
			            $noti_data = array(

							"user_id" => $user_data['user_id'],
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        }
				}else{
					$this->Noti->delete_by($noti_token);
						$noti_data = array(

							"user_id" => $user_data['user_id'],
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );

				} 

				// JWT token generation
				$userdatacs = $this->User->get_one($user_data['user_id']);

			
				$payload = [
					'user_id' => $userdatacs->user_id ? $userdatacs->user_id : 0,
					'user_email' => $userdatacs->user_email ? $userdatacs->user_email : '',
					'user_phone' => $userdatacs->user_phone ? $userdatacs->user_phone : '',
					'device_token' => $userdatacs->device_token ? $userdatacs->device_token : '',
				];

				
				
				$token = $this->authorization_token->generateToken($payload);

				$check_data = array(

					"user_id" => $user_data['user_id']
				);

				
				$blacklist_data = $this->Blacklists->get_all_by($check_data);

				if(!empty($blacklist_data->result()))
				{
					foreach($blacklist_data->result() as $blacklist)
					{
						$time_difference = strtotime('now') - strtotime($blacklist->added_date);

						if( $time_difference >= $this->config->item('token_expire_time'))
                        {
							$this->Blacklists->delete( $blacklist->id);
						}
					}
				}
				
				$userdatacs->token = $token;

				$this->custom_response($userdatacs); 	

        		//$this->custom_response($this->User->get_one($user_data['user_id']));

			}

			// JWT token generation
			$userdatacs = $this->User->get_one($user_infos[0]->user_id);

			
			$payload = [
				'user_id' => $userdatacs->user_id ? $userdatacs->user_id : 0,
				'user_email' => $userdatacs->user_email ? $userdatacs->user_email : '',
				'user_phone' => $userdatacs->user_phone ? $userdatacs->user_phone : '',
				'device_token' => $userdatacs->device_token ? $userdatacs->device_token : '',
			];

			
			
			$token = $this->authorization_token->generateToken($payload);

			$check_data = array(

				"user_id" => $user_infos[0]->user_id
			);

			
			$blacklist_data = $this->Blacklists->get_all_by($check_data);

			if(!empty($blacklist_data->result()))
			{
				foreach($blacklist_data->result() as $blacklist)
				{
					$time_difference = strtotime('now') - strtotime($blacklist->added_date);

					if( $time_difference >= $this->config->item('token_expire_time'))
					{
						$this->Blacklists->delete( $blacklist->id);
					}
				}
			}
			
			$userdatacs->token = $token;

			$this->custom_response($userdatacs); 	

        	//$this->custom_response($this->User->get_one($user_infos[0]->user_id));

        } else {

        	//User already exist in DB
        	$user_data = array(
					'user_name'    	=> $this->post('user_name'), 
					'user_email'    => $this->post('user_email'),
					'device_token'  => $this->post('device_token')
				);

			//for user name
			$user_name = $this->post('user_name');
			$user_email = $this->post('user_email');

			if ($user_name == "" && $user_email == "") {
				$user_data = array(
				'device_token'  => $this->post('device_token'), 
				);
			}else if ($user_name == "") {
				$user_data = array(
				'user_email'    => $this->post('user_email'),
				'device_token'  => $this->post('device_token'), 
				);
			}else if ($user_email == "") {
				$user_data = array(
				'user_name'    => $this->post('user_name'),
				'device_token'  => $this->post('device_token'), 
				);
			}else{
				$user_data = array(
				'user_name'    => $this->post('user_name'),
				'user_email'    => $this->post('user_email'),
				'device_token'  => $this->post('device_token'), 
				);
			}


			$conds['apple_id'] = $this->post( 'apple_id' );
			$user_datas = $this->User->get_one_by($conds);
			$user_id = $user_datas->user_id;

			if ( $user_datas->is_banned == 1 ) {

				$this->error_response( get_msg( 'err_user_banned' ));
			} else {
				if ( !$this->User->save($user_data,$user_id)) {
	        		$this->error_response( get_msg( 'err_user_register' ));
	        	}

	        	$noti_token = array(
    				"device_token" => $this->post( 'device_token' )
    			);

	        	$noti_count = $this->Noti->count_all_by($noti_token);

				if ($noti_count == 1) {
					if ( $this->Noti->exists( $noti_token )) {
	        			$noti_id = $this->Noti->get_one_by($noti_token);
	        			$push_noti_token_id = $noti_id->push_noti_token_id;
	        			$noti_data = array(

							"user_id" => $user_id
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        } else {
			            $noti_data = array(

							"user_id" => $user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );
			        }
				}else{
					$this->Noti->delete_by($noti_token);
						$noti_data = array(

							"user_id" => $user_id,
							"device_token" => $this->post( 'device_token' )
							
						);
			        	$this->Noti->save( $noti_data, $push_noti_token_id );

				} 

			}

			// JWT token generation
			$userdatacs = $this->User->get_one($user_datas->user_id);

			
			$payload = [
				'user_id' => $userdatacs->user_id ? $userdatacs->user_id : 0,
				'user_email' => $userdatacs->user_email ? $userdatacs->user_email : '',
				'user_phone' => $userdatacs->user_phone ? $userdatacs->user_phone : '',
				'device_token' => $userdatacs->device_token ? $userdatacs->device_token : '',
			];

			
			
			$token = $this->authorization_token->generateToken($payload);

			$check_data = array(

				"user_id" => $user_datas->user_id
			);

			
			$blacklist_data = $this->Blacklists->get_all_by($check_data);

			if(!empty($blacklist_data->result()))
			{
				foreach($blacklist_data->result() as $blacklist)
				{
					$time_difference = strtotime('now') - strtotime($blacklist->added_date);

					if( $time_difference >= $this->config->item('token_expire_time'))
					{
						$this->Blacklists->delete( $blacklist->id);
					}
				}
			}
			
			$userdatacs->token = $token;

			$this->custom_response($userdatacs); 

        	//$this->custom_response($this->User->get_one($user_datas->user_id));

        }


	}

	/**
	 * Get reported item list by login user id
	 */
	function get_blocked_user_by_loginuser_get()
	{

		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['GET'],
            'requireAuthorization' => true,
        ]);

		// add flag for default query
		$this->is_get = true;

		// get limit & offset
		$limit = $this->get( 'limit' );
		$offset = $this->get( 'offset' );

		// get search criteria
		$default_conds = $this->default_conds();
		$user_conds = $this->get();
		$conds = array_merge( $default_conds, $user_conds );
		$conds_block['from_block_user_id'] = $this->get( 'login_user_id' );

		$blocked_datas = $this->Block->get_all_by($conds_block)->result();
		//print_r(count( $blocked_datas ));die;
		$user_data = $this->_apiConfig([
            'methods' => ['GET'],
            'requireAuthorization' => true,
        ]);
		//echo '<pre>'; print_R($user_data); die('  himanshu sharma');
		if ( count( $blocked_datas ) > 0 ) {
			foreach ($blocked_datas as $blocked_data) {
		  	$result .= "'" .$blocked_data->to_block_user_id ."',";

		  	//"'" .$to_block_user_data->to_block_user_id . "',";

		}


		if ( !empty( $limit ) && !empty( $offset )) {
		// if limit & offset is not empty
			//$data = $this->model->get_wallpaper_delete_by_userid( $conds, $limit, $offset )->result();
			$data = $this->model->get_all_by( $conds, $limit, $offset )->result();
		} else if ( !empty( $limit )) {
		// if limit is not empty

			//$data = $this->model->get_wallpaper_delete_by_userid( $conds, $limit )->result();
			$data = $this->model->get_all_by( $conds, $limit )->result();
		} else {
		// if both are empty
			//$data = $this->model->get_wallpaper_delete_by_userid( $conds )->result();
			$data = $this->model->get_all_by( $conds )->result();
		}


		$blocked_user = rtrim($result,",");

		$conds['user_id'] = $blocked_user;

		$user_list = $this->User->get_all_in_blocked_user($conds, $limit, $offset)->result();
		//print_r($user_list);die;
		
		//$this->ps_adapter->convert_item( $user_list );
		$this->custom_response( $user_list );

		} else {
			$this->error_response($this->config->item( 'record_not_found'));

		}
		

		
	}

	/**
	 * Users Logout
	 */
	function logout_post(){
		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);

		if(!empty($user_data) && $user_data['token_data']){
			// validation rules for user register
			$rules = array(	
				array(
					'field' => 'user_id',
					'rules' => 'required|callback_id_check[User]'
				)
			);
			// exit if there is an error in validation,
			if ( !$this->is_valid( $rules )) exit;
			
			$conds['user_id'] = $this->post('user_id');
			$this->Noti->delete_by($conds);

			$headers = $this->input->request_headers();
			$token_data = array(
				"token" => $headers['Authorization'],
				"user_id" => $user_data['token_data']['user_id'], 
				"added_date" => date("Y-m-d H:i:s")
			);
			$this->Blacklists->save($token_data);
			$this->success_response( get_msg( 'success_logout' ));
		}
	}

	public function block_user_post(){
		
	}
	

}