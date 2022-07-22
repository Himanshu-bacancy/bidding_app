<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Disputes Controller
 */
class Disputes extends BE_Controller {

	/**
	 * Construct required variables
	 */
	function __construct() {

		parent::__construct( MODULE_CONTROL, 'Disputes' );
		///start allow module check 
		$conds_mod['module_name'] = $this->router->fetch_class();
		$module_id = $this->Module->get_one_by($conds_mod)->module_id;
		
		$logged_in_user = $this->ps_auth->get_user_info();

		$user_id = $logged_in_user->user_id;
		if(empty($this->User->has_permission( $module_id,$user_id )) && $logged_in_user->user_is_sys_admin!=1){
			return redirect( site_url('/admin') );
		}
		///end check
	}

	/**
	 * List down the disputes order
	 */
	function index() {
		
		// no publish filter
		$conds['no_publish_filter'] = 1;
		$conds['order_by'] = 1;
		$conds['order_by_field'] = "created_at";
		$conds['order_by_type'] = "desc";
		// get rows count
        
		$this->data['rows_count'] = $this->Dispute->count_all_by( $conds );
        
		// get topics
		$this->data['orders'] = $this->Dispute->get_all_by( $conds , $this->pag['per_page'], $this->uri->segment( 4 ) );
		// load index logic
		parent::index();
	}

	/**
	 * Searches for the first match.
	 */
	function search() {
		

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'topics_search' );
		
		// condition with search term
		$conds = array( 'searchterm' => $this->searchterm_handler( $this->input->post( 'searchterm' )) );
		// no publish filter
		$conds['no_publish_filter'] = 1;
		$conds['order_by'] = 1;
		$conds['order_by_field'] = "created_at";
		$conds['order_by_type'] = "desc";


		// pagination
		$this->data['rows_count'] = $this->Dispute->count_all_by( $conds );
		// search data
		$this->data['hctopic'] = $this->Dispute->get_all_by( $conds, $this->pag['per_page'], $this->uri->segment( 4 ) );
		// load add list
		parent::search();
	}

	/**
	 * Create new one
	 */
	function add() {

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'topic_add' );

		// call the core add logic
		parent::add();
	}

	/**
	 * Update the existing one
	 */
	function edit( $id ) {

		// breadcrumb urls
		$this->data['action_title'] = get_msg( 'topic_edit' );

		// load user
		$this->data['topic'] = $this->Dispute->get_one( $id );

		// call the parent edit logic
		parent::edit( $id );
	}

	/**
	 * Saving Logic
	 * 1) upload image
	 * 2) save Topics
	 * 3) save image
	 * 4) check transaction status
	 *
	 * @param      boolean  $id  The dispute identifier
	 */
	function save( $id = false ) {
		// start the transaction
		$this->db->trans_start();
		
		/** 
		 * Insert Topics Records 
		 */
		$data = array();

		// prepare dispute name
		if ( $this->has_data( 'name' )) {
			$data['name'] = $this->get_data( 'name' );
		}

        $data['created_at'] = date('Y-m-d H:i:s');
		// save dispute
		if ( ! $this->Dispute->save( $data, $id )) {
		// if there is an error in inserting user data,	

			// rollback the transaction
			$this->db->trans_rollback();

			// set error message
			$this->data['error'] = get_msg( 'err_model' );
			
			return;
		}


		/** 
		 * Check Transactions 
		 */

		// commit the transaction
		if ( ! $this->check_trans()) {
        	
			// set flash error message
			$this->set_flash_msg( 'error', get_msg( 'err_model' ));
		} else {

			if ( $id ) {
			// if user id is not false, show success_add message
				
				$this->set_flash_msg( 'success', get_msg( 'success_topic_edit' ));
			} else {
			// if user id is false, show success_edit message

				$this->set_flash_msg( 'success', get_msg( 'success_topic_add' ));
			}
		}

		redirect( $this->module_site_url());
	}


	

	/**
	 * Delete the record
	 * 1) delete Topics
	 * 2) delete image from folder and table
	 * 3) check transactions
	 */
	function delete( $id ) {

		// start the transaction
		$this->db->trans_start();

		// check access
		$this->check_access( DEL );
		
		// delete topics and images
		if ( !$this->ps_delete->delete_hctopic( $id )) {

			// set error message
			$this->set_flash_msg( 'error', get_msg( 'err_model' ));

			// rollback
			$this->trans_rollback();

			// redirect to list view
			redirect( $this->module_site_url());
		}
			
		/**
		 * Check Transcation Status
		 */
		if ( !$this->check_trans()) {

			$this->set_flash_msg( 'error', get_msg( 'err_model' ));	
		} else {
        	
			$this->set_flash_msg( 'success', get_msg( 'success_topic_delete' ));
		}
		
		redirect( $this->module_site_url());
	}


	
	/**
	 * Determines if valid input.
	 *
	 * @return     boolean  True if valid input, False otherwise.
	 */
	function is_valid_input( $id = 0 ) {

		$rule = 'required|callback_is_valid_name['. $id  .']';

		$this->form_validation->set_rules( 'name', get_msg( 'topic_name' ), $rule);

		if ( $this->form_validation->run() == FALSE ) {
		// if there is an error in validating,

			return false;
		}

		return true;
	}

	/**
	 * Determines if valid name.
	 *
	 * @param      <type>   $name  The  name
	 * @param      integer  $id     The  identifier
	 *
	 * @return     boolean  True if valid name, False otherwise.
	 */
	function is_valid_name( $name, $id = 0 )
	{		
		 $conds['name'] = $name;

		 	if ( strtolower( $this->Dispute->get_one( $id )->name ) == strtolower( $name )) {
			// if the name is existing name for that user id,
				return true;
			} else if ( $this->Dispute->exists( ($conds ))) {
			// if the name is existed in the system,
				$this->form_validation->set_message('is_valid_name', get_msg( 'err_dup_name' ));
				return false;
			}
			return true;
	}
	/**
	 * Check Disputes name via ajax
	 *
	 * @param      boolean  $id  The id identifier
	 */
	function ajx_exists( $id = false )
	{
		// get topics name

		$name = $_REQUEST['name'];

		if ( $this->is_valid_name( $name, $id )) {

		// if the topics name is valid,
			
			echo "true";
		} else {
		// if invalid topics name,
			
			echo "false";
		}
	}

	/**
	 * Publish the record
	 *
	 * @param      integer  $id  The Disputes identifier
	 */
	function ajx_publish( $id = 0, $order_id )
	{
        $date = date('Y-m-d H:i:s');
        $topic_data = array( 'status'=>'accept','updated_at' =>  $date);
        // prepare data
			
		// save data
		if ( $this->Dispute->save( $topic_data, $id )) {
            $get_detail = $this->db->from('bs_dispute')->where('id', $id)->get()->row();
            $past_record = $this->db->select('bs_return_order.id, bs_return_order.amount, bs_order.order_id, bs_order.user_id, bs_order.item_offered_price, bs_order.qty, bs_order.total_amount, bs_order.seller_charge, bs_order.seller_earn, bs_items.title, bs_items.pay_shipping_by, bs_items.added_user_id, buyer.wallet_amount, buyer.device_token, seller.wallet_amount as seller_wallet_amount, seller.device_token as seller_device_token')
                ->from('bs_order')
                ->join('bs_return_order', 'bs_order.order_id = bs_return_order.order_id')
                ->join('bs_items', 'bs_order.items = bs_items.id')
                ->join('core_users as buyer', 'bs_order.user_id = buyer.user_id')
                ->join('core_users as seller', 'bs_items.added_user_id = seller.user_id')
                ->where('bs_order.order_id', $order_id)
            ->get()->row_array();
            if($get_detail->is_seller_generate) {
                /*shipping charge of return accept + seller earning on order:start*/
                $total_credited = (float)$past_record['item_offered_price']*(int)$past_record['qty'];
                $this->db->insert('bs_wallet',['parent_id' => $order_id,'user_id' => $past_record['added_user_id'],'action' => 'plus', 'amount' => $total_credited,'type' => 'refund', 'created_at' => $date]);
                
                $this->db->where('user_id', $past_record['added_user_id'])->update('core_users',['wallet_amount' => $past_record['seller_wallet_amount']+$total_credited]);
                /*shipping charge of return accept + seller earning on order:end*/
                
                send_push( [$past_record['seller_device_token']], ["message" => "Refund has been added to yout wallet", "flag" => "order", 'title' => $past_record['title']." order update"],['order_id' => $order_id] );
                
                send_push( [$past_record['device_token']], ["message" => "Dispute accpeted", "flag" => "order", 'title' => $past_record['title']." order update"],['order_id' => $order_id] );
            } else {
                send_push( [$past_record['device_token'],$past_record['seller_device_token']], ["message" => "Dispute against Seller Has been accpeted", "flag" => "order", 'title' => $past_record['title']." order update"],['order_id' => $order_id] );
            }
//            $buyer = $this->db->select('device_token')->from('bs_order')
//                    ->join('core_users', 'bs_order.user_id = core_users.user_id')
//                    ->where('order_id', $order_id)->get()->row();
//            
//            $seller = $this->db->select('device_token,title')->from('bs_order')
//                        ->join('bs_items', 'bs_order.items = bs_items.id')
//                        ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
//                        ->where('order_id', $order_id)->get()->row();
            
//            send_push( [$buyer->device_token,$seller->device_token], ["message" => "Dispute against Seller Has been accpeted", "flag" => "order", 'title' => $seller->title." order update"],['order_id' => $order_id] );
                
			echo 'true';
		} else {
			echo 'false';
		}
	}
	
	/**
	 * Unpublish the records
	 *
	 * @param      integer  $id  The dispute identifier
	 */
	function ajx_unpublish( $id = 0, $order_id )
	{
		
		$date = date('Y-m-d H:i:s');
		// prepare data
		$topic_data = array( 'status'=> 'reject','updated_at' => $date );
		// save data
		if ( $this->Dispute->save( $topic_data, $id )) {
            $get_detail = $this->db->from('bs_dispute')->where('id', $id)->get()->row();
            if($get_detail->is_seller_generate) {
                $past_record = $this->db->select('bs_return_order.id, bs_return_order.amount, bs_order.order_id, bs_order.user_id, bs_order.item_offered_price, bs_order.qty, bs_order.shipping_amount, bs_order.total_amount, bs_order.seller_charge, bs_order.seller_earn, bs_items.title, bs_items.pay_shipping_by, bs_items.added_user_id, buyer.wallet_amount, buyer.device_token, seller.wallet_amount as seller_wallet_amount, seller.device_token as seller_device_token')
                    ->from('bs_order')
                    ->from('bs_return_order', 'bs_order.order_id = bs_return_order.order_id')
                    ->join('bs_items', 'bs_order.items = bs_items.id')
                    ->join('core_users as buyer', 'bs_order.user_id = buyer.user_id')
                    ->join('core_users as seller', 'bs_items.added_user_id = seller.user_id')
                    ->where('bs_order.order_id', $order_id);
                /*shipping charge of return accept + seller earning on order:start*/
                $total_credited = (float)$past_record['item_offered_price']*(int)$past_record['qty'];
                if($past_record['pay_shipping_by'] == '1') {
                    $total_credited += $past_record['shipping_amount'];
                }
                $this->db->insert('bs_wallet',['parent_id' => $order_id,'user_id' => $past_record['user_id'],'action' => 'plus', 'amount' => $total_credited,'type' => 'refund', 'created_at' => $date]);
                
                $this->db->where('user_id', $past_record['user_id'])->update('core_users',['wallet_amount' => $past_record['wallet_amount']+$total_credited]);
                /*shipping charge of return accept + seller earning on order:end*/
                
                send_push( [$past_record['device_token']], ["message" => "Refund has been added to yout wallet", "flag" => "order", 'title' => $past_record['title']." order update"],['order_id' => $order_id] );
                
                send_push( [$past_record['seller_device_token']], ["message" => "Dispute rejected", "flag" => "order", 'title' => $past_record['title']." order update"],['order_id' => $order_id] );
            } else {
                $buyer = $this->db->select('device_token')->from('bs_order')
                        ->join('core_users', 'bs_order.user_id = core_users.user_id')
                        ->where('order_id', $order_id)->get()->row();
                
                $seller = $this->db->select('device_token,title,bs_order.seller_earn,wallet_amount,bs_items.added_user_id')->from('bs_order')
                            ->join('bs_items', 'bs_order.items = bs_items.id')
                            ->join('core_users', 'bs_items.added_user_id = core_users.user_id')
                            ->where('order_id', $order_id)->get()->row();

                $this->db->insert('bs_wallet', ['parent_id' => $order_id, 'user_id' => $seller->added_user_id, 'action' => 'plus', 'amount' => $seller->seller_earn, 'type' => 'complete_order', 'created_at' => $date]);

                $this->db->where('user_id', $seller->added_user_id)->update('core_users', ['wallet_amount' => $seller->wallet_amount + (float)$seller->seller_earn ]);

                send_push( [$buyer->device_token,$seller->device_token], ["message" => "Dispute against Seller Has been rejected", "flag" => "order", 'title' => $seller->title." order update"],['order_id' => $order_id] );
            }
            
			echo 'true';
		} else {
			echo 'false';
		}
	}
}