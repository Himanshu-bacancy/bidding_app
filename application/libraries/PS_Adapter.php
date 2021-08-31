<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PanaceaSoft Authentication
 */
class PS_Adapter {

	// codeigniter instance
	protected $CI;

	// login user
	protected $login_user_id;

	/**
	 * Constructor
	 */
	function __construct()
	{
		// get CI instance
		$this->CI =& get_instance();
	}

	/**
	 * Sets the login user.
	 */
	function set_login_user_id( $user_id )
	{
		$this->login_user_id = $user_id;
	}
	
	/**
	 * Gets the default photo.
	 *
	 * @param      <type>  $id     The identifier
	 * @param      <type>  $type   The type
	 */
	function get_default_photo( $id, $type )
	{
		$default_photo = "";

		// get all images
		$img = $this->CI->Image->get_all_by( array( 'img_parent_id' => $id, 'img_type' => $type ))->result();

		if ( count( $img ) > 0 ) {
		// if there are images for wallpaper,
			
			$default_photo = $img[0];
		} else {
		// if no image, return empty object

			$default_photo = $this->CI->Image->get_empty_object();
		}

		return $default_photo;
	}
	

	/**
	 * Customize wallpaper object
	 *
	 * @param      <type>  $obj    The object
	 */
	function convert_image( &$obj )
	{

	}

	/**
	 * Customize category object
	 *
	 * @param      <type>  $obj    The object
	 */
	function convert_category( &$obj )
	{
		// set default photo
		$obj->default_photo = $this->get_default_photo( $obj->cat_id, 'category' );

		//set default icon
		$obj->default_icon = $this->get_default_photo( $obj->cat_id, 'category-icon' );
	}


	/**
	 * Customize category object
	 *
	 * @param      <type>  $obj    The object
	 */
	function convert_item( &$obj )
	{
		$conds['item_id'] = $obj->id;
		$conds['id'] = $obj->history_id;
		$paid_history = $this->CI->Paid_item->get_all_by($conds)->result();
		
		$today_date = date('Y-m-d H:i:s');


		//paid status of offline payment
		$conds_id['id'] = $obj->id;
		$is_paid = $this->CI->Item->get_one_by($conds_id)->is_paid;
		
		if (count($paid_history) > 1) {

			foreach ($paid_history as $paid) {
				$tmp_result .= $paid->item_id .",";
				  
			}
			$paid_his_item_id = rtrim($tmp_result,",");
			$item_id = explode(",", $paid_his_item_id);
			
			$progress = $this->CI->Paid_item->get_item_by_paid_progress($item_id)->result();
			

			if ( !empty($progress) ) {
				
				foreach ($progress as $pro) {
					if ($is_paid == 1) {
						if ($today_date >= $pro->start_date && $today_date <= $pro->end_date ) {
						$obj->paid_status = $this->CI->config->item('progress_label');
						} else {
							$obj->paid_status = $this->CI->config->item('not_yet_start_label');
						}
					}else{
						if ($is_paid == 0) {
							$obj->paid_status = $this->CI->config->item('waiting_for_approval_label');
						}else{
							$obj->paid_status = $this->CI->config->item('reject_label');
						}
						
					}
					
				}
			} else {
				
				$obj->paid_status = $this->CI->config->item('finished_label');
			}
			
			
		} elseif (count($paid_history)==1) {
			$start_date = $paid_history[0]->start_date;
			$end_date = $paid_history[0]->end_date;
			//print_r($today_date);die;

			if ($is_paid == 1) {
				//print_r($pro->start_date);die;
				if ($today_date >= $start_date && $today_date <= $end_date ) {
				$obj->paid_status = $this->CI->config->item('progress_label');
				} elseif ($today_date > $start_date && $today_date > $end_date) {
				$obj->paid_status = $this->CI->config->item('finished_label');
				} else {
					$obj->paid_status = $this->CI->config->item('not_yet_start_label');
				}
			}else{
				if ($is_paid == 0) {
					$obj->paid_status = $this->CI->config->item('waiting_for_approval_label');
				}else{
					$obj->paid_status = $this->CI->config->item('reject_label');
				}
			}

		
		} else {
			$obj->paid_status = $this->CI->config->item('not_available');
		}

		unset($obj->history_id);
		// photo count
		$obj->photo_count = $this->CI->Image->count_all_by(array('img_parent_id' => $obj->id));
		// set default photo
		$obj->default_photo = $this->get_default_photo( $obj->id, 'item' );

		// category object
		if ( isset( $obj->cat_id )) {

			$tmp_category = $this->CI->Category->get_one( $obj->cat_id );

			$this->convert_category( $tmp_category );

			$obj->category = $tmp_category;
		}

		// Sub Category Object
		if ( isset( $obj->sub_cat_id )) {

			$tmp_sub_category = $this->CI->Subcategory->get_one( $obj->sub_cat_id );

			$this->convert_subcategory( $tmp_sub_category );

			$obj->sub_category = $tmp_sub_category;
		}

		// Child Sub Category Object
		if ( isset( $obj->childsubcat_id )) {

			$tmp_childsub_category = $this->CI->Childsubcategory->get_one( $obj->childsubcat_id );

			$this->convert_childsubcategory( $tmp_childsub_category );

			$obj->childsub_category = $tmp_childsub_category;
		}

		// Itemtype Object
		if ( isset( $obj->item_type_id )) {

			$tmp_item_type = $this->CI->Itemtype->get_one( $obj->item_type_id );
			$obj->item_type = $tmp_item_type;
		}

		// Itemprice Object
		if ( isset( $obj->item_price_type_id )) {

			$tmp_item_price_type = $this->CI->Pricetype->get_one( $obj->item_price_type_id );
			$obj->item_price_type = $tmp_item_price_type;
		}

		// Itemcurrency Object
		if ( isset( $obj->item_currency_id )) {

			$tmp_item_currency = $this->CI->Currency->get_one( $obj->item_currency_id );
			$obj->item_currency = $tmp_item_currency;
		}

		// Itemlocation Object
		if ( isset( $obj->item_location_id )) {

			$tmp_item_location= $this->CI->Itemlocation->get_one( $obj->item_location_id );
			$obj->item_location = $tmp_item_location;
		}

		// condition of item id Object
		if ( isset( $obj->condition_of_item_id )) {

			$tmp_condition_item= $this->CI->Condition->get_one( $obj->condition_of_item_id );
			$obj->condition_of_item = $tmp_condition_item;
		}

		// condition of deal option id
		if ( isset( $obj->deal_option_id )) {

			$tmp_deal_option = $this->CI->Option->get_one( $obj->deal_option_id );
			$obj->deal_option = $tmp_deal_option ;
		}

		// sizegroup Object
		if ( isset( $obj->sizegroup_id )) {

			$tmp_sizegroup = $this->CI->Sizegroups->get_one( $obj->sizegroup_id );
			$obj->sizegroup = $tmp_sizegroup;
		}

		if ( isset( $obj->shippingcarrier_id )) {
			$tmp_shippingCarrier = $this->CI->Shippingcarriers->get_one( $obj->shippingcarrier_id );
			$obj->shipping_carrier = $tmp_shippingCarrier;
		}

		if ( isset( $obj->packagesize_id )) {
			$tmp_packageSize = $this->CI->Packagesizes->get_one( $obj->packagesize_id );
			$obj->package_size = $tmp_packageSize;
		}

		// fetch exchange category
		if (  $obj->is_exchange =='1') {

			$this->CI->db->where('item_id', $obj->id);
    		$exchange_category = $this->CI->db->get('bs_item_exchange')->result();
			//echo '<pre>'; print_r($exchange_category); die;
			foreach($exchange_category as $key=>$categoryData){
				$categoryDatail = $this->CI->Category->get_one($categoryData->cat_id);
				$categoryData->cat_name = $categoryDatail->cat_name;
				$this->convert_category($categoryData);
				$obj->deal_option = $tmp_deal_option;
			}
			//$exchange_category = $this->CI->Category->get_one( $itemconds )->result();
			$obj->exchange_category = $exchange_category ;
		}
		// fetch similar item creteria
		if (  $obj->is_accept_similar =='1') {
			$this->CI->db->where('item_id', $obj->id);
			$similar_items = $this->CI->db->get('bs_item_similarcreteria')->result();
			$newArray = [];
			$i = 0;
			foreach($similar_items as $key=>$items){
				$this->CI->db->from('bs_similar_criterias');
				$this->CI->db->where('bs_similar_criterias.id', $items->similarcreteria_id);
				$variableChe = $this->CI->db->get()->row();
				$similar_items[$key]->title = $variableChe->title;
				$similar_items[$key]->default_icon = $this->get_default_photo( $items->similarcreteria_id, 'similarcriteria_icon' );
			}
			$obj->similar_items = $similar_items ;
		}

		// fetch colors
		$this->CI->db->select('bs_item_colors.*,bs_color.name, bs_color.code'); 
		$this->CI->db->from('bs_item_colors');
		$this->CI->db->join('bs_color', 'bs_item_colors.color_id = bs_color.id');
		$this->CI->db->where('bs_item_colors.item_id', $obj->id);
		
		$item_colors = $this->CI->db->get()->result();
		$obj->item_colors = $item_colors ;

		// fetch sizegroup options
		$this->CI->db->select('bs_item_sizegroupoptions.*,bs_sizegroup_option.title, bs_sizegroup_option.description'); 
		$this->CI->db->from('bs_item_sizegroupoptions');
		$this->CI->db->join('bs_sizegroup_option', 'bs_item_sizegroupoptions.sizegroup_option_id = bs_sizegroup_option.id');
		$this->CI->db->where('bs_item_sizegroupoptions.item_id', $obj->id);
    	$sizegroup_options = $this->CI->db->get()->result();
		$obj->sizegroup_options = $sizegroup_options ;

		// Address object
		if ( isset( $obj->Address_id )) {

			$tmp_address = $this->CI->Addresses->get_one( $obj->Address_id );
			$obj->item_address = $tmp_address;
		}

		// Delivery method object
		if ( isset( $obj->delivery_method_id )) {

			$tmp_deliverymethod = $this->CI->Deliverymethods->get_one( $obj->delivery_method_id );
			$obj->item_deliverymethod = $tmp_deliverymethod;
		}

		// Brand object
		if ( isset( $obj->brand )) {

			$tmp_brand = $this->CI->Brands->get_one( $obj->brand );
			$obj->item_brand = $tmp_brand;
		}

		// Packagesize object
		if ( isset( $obj->packagesize_id ) && $obj->packagesize_id !='') {

			$tmp_packagesize = $this->CI->Packagesizes->get_one( $obj->packagesize_id );
			$obj->item_packagesize = $tmp_packagesize;
		}

		// Shippingcarrier object
		if ( isset( $obj->shippingcarrier_id ) && $obj->shippingcarrier_id !='') {

			$tmp_shippingcarrier = $this->CI->Shippingcarriers->get_one( $obj->shippingcarrier_id );
			$obj->item_shippingcarrier = $tmp_shippingcarrier;
		}

		//print_r($obj->added_user_id . "$$");die;
		// User Object
		if ( isset( $obj->added_user_id )) {
			$tmp_item_user = $this->CI->User->get_one( $obj->added_user_id );

			$this->convert_user( $tmp_item_user );
			$obj->user = $tmp_item_user;
		}

		//Need to check for Like and Favourite
		$obj->is_favourited = 0;
		$obj->is_owner = 0;

		if($this->get_login_user_id() != "") {
			//echo "1";
			//Need to check for Fav
			$conds['item_id'] = $obj->id;
			$conds['user_id']    = $this->get_login_user_id();
			$login_user_id = $conds['user_id'];


			if($obj->added_user_id == $login_user_id) {
				$obj->is_owner = 1;
			} else {
				$obj->is_owner = 0;
			}

			$fav_id = $this->CI->Favourite->get_one_by($conds)->id;
			$obj->is_favourited = 0;
			if($fav_id != "") {
				$obj->is_favourited = 1;
			} else {
				$obj->is_favourited = 0;
			}

		} else if($obj->login_user_id_post != "") {
			//echo "2";
			$conds['item_id'] 	= $obj->id;
			$conds['user_id']    = $obj->login_user_id_post;
			$login_user_id = $conds['user_id'];

			// checking for like product by user

			$obj->is_owner = 0;
			if($obj->added_user_id == $login_user_id) {
				$obj->is_owner = 1;
			} else {
				$obj->is_owner = 0;
			}
			
			$fav_id = $this->CI->Favourite->get_one_by($conds)->id;
			$obj->is_favourited = 0;
			if($fav_id != "") {
				$obj->is_favourited = 1;
			} else {
				$obj->is_favourited = 0;
			}

		}
		//echo '<pre>'; print_r($obj);die;
		if($obj->item_type_id == 1){
			$conditionChatHistory['requested_item_id'] = $obj->id;
			$chatHistory = $this->CI->Chat->get_all_by($conditionChatHistory)->result();
			//echo $conditionChatHistory;
			//$chatHistory = $this->db->query("SELECT * FROM `bs_chat_history` WHERE ".$conditionChatHistory)->result();
			$obj->big_count = count($chatHistory);
			$lowestBid = 0;
			foreach($chatHistory as $history){
				if($lowestBid > $history->nego_price){
					$lowestBid = $history->nego_price;
				}
			}
			//echo $lowestBid; die(' hello testing');
			$obj->bid_count = count($chatHistory);
			$obj->lowest_bid_amount = $lowestBid;
		}
		//print_r($obj->added_user_id);die;
		//unset($obj->login_user_id_post);

		$obj->is_favourited = $obj->is_favourited;
		$obj->is_owner = $obj->is_owner;
		$obj->gallery_images = $img = $this->CI->Image->get_all_by( array( 'img_parent_id' => $obj->id, 'img_type' => 'item' ))->result();

	}

	/**
	 * Customize chatitem object
	 *
	 * @param      <type>  $obj    The object
	 */
	function convert_chatitem( &$obj )
	{
			
		// photo count
		$obj->photo_count = $this->CI->Image->count_all_by(array('img_parent_id' => $obj->id));
		// set default photo
		$obj->default_photo = $this->get_default_photo( $obj->id, 'item' );

		// category object
		if ( isset( $obj->cat_id )) {
			$tmp_category = $this->CI->Category->get_one( $obj->cat_id );

			$this->convert_category( $tmp_category );

			$obj->category = $tmp_category;
		}

		// Sub Category Object
		if ( isset( $obj->sub_cat_id )) {
			$tmp_sub_category = $this->CI->Subcategory->get_one( $obj->sub_cat_id );

			$this->convert_subcategory( $tmp_sub_category );

			$obj->sub_category = $tmp_sub_category;
		}

		// Itemtype Object
		if ( isset( $obj->item_type_id )) {
			$tmp_item_type = $this->CI->Itemtype->get_one( $obj->item_type_id );

			$obj->item_type = $tmp_item_type;
		}

		// Itemprice Object
		if ( isset( $obj->item_price_type_id )) {
			$tmp_item_price_type = $this->CI->Pricetype->get_one( $obj->item_price_type_id );

			$obj->item_price_type = $tmp_item_price_type;
		}

		// Itemcurrency Object
		if ( isset( $obj->item_currency_id )) {
			$tmp_item_currency = $this->CI->Currency->get_one( $obj->item_currency_id );

			$obj->item_currency = $tmp_item_currency;
		}

		// Itemlocation Object
		if ( isset( $obj->item_location_id )) {
			$tmp_item_location= $this->CI->Itemlocation->get_one( $obj->item_location_id );

			$obj->item_location = $tmp_item_location;
		}

		// condition of item id Object
		if ( isset( $obj->condition_of_item_id )) {
			$tmp_condition_item= $this->CI->Condition->get_one( $obj->condition_of_item_id );

			$obj->condition_of_item = $tmp_condition_item;
		}

		// Chat User Object
		if ( isset( $obj->chat_user_id )) {
			$conds['chat_user_id'] = $obj->chat_user_id;

			$tmp_item_user = $this->CI->User->get_all_in_user($conds)->result();
			//print_r($tmp_item_user);die;

			$this->convert_user( $tmp_item_user );

			$obj->chat_user = $tmp_item_user;
		}


		//Need to check for Like and Favourite
		$obj->is_favourited = 0;
		$obj->is_owner = 0;
		
		if($this->get_login_user_id() != "") {
			//Need to check for Fav
			$conds['item_id'] = $obj->id;
			$conds['user_id'] = $this->get_login_user_id();
			$login_user_id    = $conds['user_id'];
		
			// checking for item by user
			if($obj->added_user_id == $login_user_id) {
				$obj->is_owner = 1;
			} else {
				$obj->is_owner = 0;
			}


			$fav_id = $this->CI->Favourite->get_one_by($conds)->fav_id;

			$obj->is_favourited = 0;
			if($fav_id != "") {
				$obj->is_favourited = 1;
			} else {
				$obj->is_favourited = 0;
			}

		} else if($obj->login_user_id_post != "") {
			$conds['item_id'] = $obj->id;
			$conds['user_id'] = $obj->login_user_id_post;
			$login_user_id    = $conds['user_id'];

			// checking for like product by user
			$obj->is_owner = 0;
			if($obj->added_user_id == $login_user_id) {
				$obj->is_owner = 1;
			} else {
				$obj->is_owner = 0;
			}

			$fav_id = $this->CI->Favourite->get_one_by($conds)->id;
			$obj->is_favourited = 0;
			if($fav_id != "") {
				$obj->is_favourited = 1;
			} else {
				$obj->is_favourited = 0;
			}

		}

		
		unset($obj->login_user_id_post);

		$obj->is_favourited = $obj->is_favourited;
		$obj->is_owner = $obj->is_owner;

	}


	/**
	 * Sets the login user.
	 */
	function get_login_user_id()
	{
		return $this->login_user_id;
	}


	
	/**
	 * Customize follower user object
	 *
	 * @param      <type>  $obj    The object
	 */
	function convert_user_follow( &$obj )
	{
		// user object
		if ( isset( $obj->followed_user_id )) {
			$tmp_user = $this->CI->User->get_one( $obj->followed_user_id );

			$this->convert_user( $tmp_user );

			$obj->user_followed = $tmp_user;
		}
	}



	/**
	 * Customize about object
	 *
	 * @param      <type>  $obj    The object
	 */
	function convert_about( &$obj )
	{
		$obj->privacypolicy = $this->CI->Privacy_policy->get_one('privacy1')->content;
		// set default photo
		$obj->default_photo = $this->get_default_photo( $obj->about_id, 'about' );

		//user data deletion
		$obj->datadelection = $this->CI->Data_deletion->get_one('datadelete1')->content;

	}

	/**
	 * Customize user object
	 *
	 * @param      <type>  $obj    The object
	 */
	function convert_user( &$obj , $need_return = false)
	{
		
		$conds['added_user_id'] = $obj->user_id;
		//print_r($obj->user_id);die;
		$obj->rating_count = $this->CI->Rate->count_all_by(array('to_user_id' => $obj->user_id));

        //is follower or not

		$obj->is_followed = 0;
		
		 if($obj->followed_user_id != "") {
			
			$conds1['user_id']    = $obj->following_user_id;
			$conds1['followed_user_id'] = $obj->followed_user_id;
			
			// checking follower
			
			$followed_user_id = $this->CI->Userfollow->get_one_by($conds1)->id;
			//print_r($followed_user_id);die;
			$obj->is_followed = 0;
			if($followed_user_id != "") {
				$obj->is_followed = 1;
			} else {
				$obj->is_followed = 0;
			}

		}
		

		
		unset($obj->following_user_id);
		unset($obj->followed_user_id);
		unset($obj->user_password);

		$obj->is_followed = $obj->is_followed;

        //rating details 
		
		$total_rating_count = 0;
		$total_rating_value = 0;

		$five_star_count = 0;
		$five_star_percent = 0;

		$four_star_count = 0;
		$four_star_percent = 0;

		$three_star_count = 0;
		$three_star_percent = 0;

		$two_star_count = 0;
		$two_star_percent = 0;

		$one_star_count = 0;
		$one_star_percent = 0;


		

		//Rating Total how much ratings for this product
		$conds_rating['to_user_id'] = $obj->user_id;
		$total_rating_count = $this->CI->Rate->count_all_by($conds_rating);
		$sum_rating_value = $this->CI->Rate->sum_all_by($conds_rating)->result()[0]->rating;


		//Rating Value such as 3.5, 4.3 and etc
		if($total_rating_count > 0) {
			$total_rating_value = number_format((float) ($sum_rating_value  / $total_rating_count), 1, '.', '');
		} else {
			$total_rating_value = 0;
		}

		//For 5 Stars rating
		$conds_five_star_rating['rating'] = 5;
		$conds_five_star_rating['to_user_id'] = $obj->user_id;
		$five_star_count = $this->CI->Rate->count_all_by($conds_five_star_rating);
		if($total_rating_count > 0) {
			$five_star_percent = number_format((float) ((100 / $total_rating_count) * $five_star_count), 1, '.', '');
		} else {
			$five_star_percent = 0;
		}

		//For 4 Stars rating
		$conds_four_star_rating['rating'] = 4;
		$conds_four_star_rating['to_user_id'] = $obj->user_id;
		$four_star_count = $this->CI->Rate->count_all_by($conds_four_star_rating);
		if($total_rating_count > 0) {
			$four_star_percent = number_format((float) ((100 / $total_rating_count) * $four_star_count), 1, '.', '');
		} else {
			$four_star_percent = 0;
		}


		//For 3 Stars rating
		$conds_three_star_rating['rating'] = 3;
		$conds_three_star_rating['to_user_id'] = $obj->user_id;
		$three_star_count = $this->CI->Rate->count_all_by($conds_three_star_rating);
		if($total_rating_count > 0) {
			$three_star_percent = number_format((float) ((100 / $total_rating_count) * $three_star_count), 1, '.', '');
		} else {
			$three_star_percent = 0;
		}


		//For 2 Stars rating
		$conds_two_star_rating['rating'] = 2;
		$conds_two_star_rating['to_user_id'] = $obj->user_id;
		$two_star_count = $this->CI->Rate->count_all_by($conds_two_star_rating);

		if($total_rating_count > 0) {
			$two_star_percent = number_format((float) ((100 / $total_rating_count) * $two_star_count), 1, '.', '');
		} else {
			$two_star_percent = 0;
		}

		//For 1 Stars rating
		$conds_one_star_rating['rating'] = 1;
		$conds_one_star_rating['to_user_id'] = $obj->user_id;
		$one_star_count = $this->CI->Rate->count_all_by($conds_one_star_rating);

		if($total_rating_count > 0) {
		$one_star_percent = number_format((float) ((100 / $total_rating_count) * $one_star_count), 1, '.', '');
		} else {
			$one_star_percent = 0;
		}


		$rating_std = new stdClass();
		@$rating_std->five_star_count = $five_star_count; 
		$rating_std->five_star_percent = $five_star_percent;

		$rating_std->four_star_count = $four_star_count;
		$rating_std->four_star_percent = $four_star_percent;

		$rating_std->three_star_count = $three_star_count;
		$rating_std->three_star_percent = $three_star_percent;

		$rating_std->two_star_count = $two_star_count;
		$rating_std->two_star_percent = $two_star_percent;

		$rating_std->one_star_count = $one_star_count;
		$rating_std->one_star_percent = $one_star_percent;

		//print_r($total_rating_count . "##" . $total_rating_value);die;

		$rating_std->total_rating_count = $total_rating_count;
		$rating_std->total_rating_value = $total_rating_value;


		$obj->rating_details = $rating_std;

		$this->CI->db->where('user_id', $obj->user_id);
		$this->CI->db->where('is_default_address', 1);
    	$default_address = $this->CI->db->get('bs_addresses')->row();
		$obj->default_address = $default_address;

		if(!empty($obj->accept_delivery_id))
		{
			$this->CI->db->where('id', $obj->accept_delivery_id);
			$default_deliverymethod = $this->CI->db->get('bs_deliverymethod')->row();
			$obj->default_deliverymethod = $default_deliverymethod;
		}

		if($need_return)
		{
			return $obj;
		} 

	}

	/**
	 * Customize noti object
	 *
	 * @param      <type>  $obj    The object
	 */
	function convert_noti_message( &$obj )
	{
		
		
		if($this->get_login_user_id() != "") {
			$noti_user_data = array(
	        	"noti_id" => $obj->id,
	        	"user_id" => $this->get_login_user_id()
	    	);
			if ( !$this->CI->Notireaduser->exists( $noti_user_data )) {
				$obj->is_read = 0;
			} else {
				$obj->is_read = 1;
			}
		} 
		


		// set default photo
		$obj->default_photo = $this->get_default_photo( $obj->id, 'noti' );
	}

	/**
	 * Customize noti object
	 *
	 * @param      <type>  $obj    The object
	 */
	function convert_noti( &$obj )
	{
		
		
		if($this->get_login_user_id() != "") {
			$noti_user_data = array(
	        	"noti_id" => $obj->id,
	        	"user_id" => $this->get_login_user_id()
	    	);
			if ( !$this->CI->Notireaduser->exists( $noti_user_data )) {
				$obj->is_read = 0;
			} else {
				$obj->is_read = 1;
			}
		} 
		


		// set default photo
		$obj->default_photo = $this->get_default_photo( $obj->id, 'noti' );
	}

	/**
	 * Customize request category object
	 *
	 * @param      <type>  $obj    The object
	 */
	function convert_request_category( &$obj )
	{
		
	}

	/**
	 * Customize request category object
	 *
	 * @param      <type>  $obj    The object
	 */
	function convert_subcategory( &$obj )
	{
		// set default photo
		$obj->default_photo = $this->get_default_photo( $obj->id, 'sub_category' );
	}

	/**
	 * Customize request childsubcategory object
	 *
	 * @param      <type>  $obj    The object
	 */
	function convert_childsubcategory( &$obj )
	{
		// set default photo
		$obj->default_photo = $this->get_default_photo( $obj->id, 'childsubcategory_cover' );

		if($obj->is_color_filter =='1')
			{
				$condscstm = array();
				$condscstm['no_publish_filter'] = 1;

				$colorarray = $this->CI->Color->get_all_by( $condscstm);
				
				$obj->colors = $colorarray->result();
			}

			if($obj->is_brand_filter =='1')
			{
				$condscstm = array();
				$condscstm['no_publish_filter'] = 1;

				$brandarray = $this->CI->Brands->get_all_by( $condscstm);
				
				$obj->brands = $brandarray->result();
			}

			if($obj->is_size_filter =='1')
			{
				$sizegroups = $this->CI->Sizegroups->get_all();
				$selectedSizeGroups = $this->CI->Childsubcategory->getSelectedSizegroups($obj->id);
				$options=array();
				
				foreach($selectedSizeGroups as $sizekey => $sgroups) {
					$sizearray = $this->CI->Sizegroups->get_one($sgroups);
					
					
					$sizearr = json_decode(json_encode($sizearray), true);

					if(count($sizearray)>0)
					{
						
						$condscstm = array();
						$condscstm['no_publish_filter'] = 1;
						$condscstm['sizegroup_id'] = $sizearr['id'];
						$sizearr['options']=$this->CI->Sizegroup_option->get_all_by( $condscstm)->result();
						
					}
					$selectedSizeGroups[$sizekey]=$sizearr;
				}
				$obj->sizes =$selectedSizeGroups ;
			}

		//set default icon
		$obj->default_icon = $this->get_default_photo( $obj->id, 'childsubcategory_icon' );
	}

	function convert_packagesize( &$obj )
	{
		//set default icon
		$obj->default_icon = $this->get_default_photo( $obj->id, 'packagesize_icon' );
	}

	function convert_shippingcarrier( &$obj )
	{
		//set default icon
		$obj->default_icon = $this->get_default_photo( $obj->id, 'shippingcarrier_icon' );
	}

	/*
	 * Customize feed object
	 *
	 * @param      <type>  $obj    The object
	 */
	function convert_feed( &$obj )
	{
		// set default photo
		$obj->default_photo = $this->get_default_photo( $obj->id, 'blog' );

	}

	/*
	 * Customize feed object
	 *
	 * @param      <type>  $obj    The object
	 */
	function convert_paid_history( &$obj )
	{
		$today_date = date('Y-m-d H:i:s');

		$conds['id'] = $obj->item_id;

		$is_paid = $this->CI->Item->get_one_by($conds)->is_paid;

		if ($obj->payment_method == "offline" && $is_paid == "0") {
			$obj->paid_status = $this->CI->config->item('waiting_for_approval_label');
		} elseif ($obj->payment_method == "offline" && $is_paid == "2") {
			$obj->paid_status = $this->CI->config->item('reject_label');
		} elseif ($obj->payment_method == "offline" && $is_paid == "1") {
			if ($today_date >= $obj->start_date && $today_date <= $obj->end_date) {
			$obj->paid_status = $this->CI->config->item('progress_label');
			} elseif ($today_date > $obj->start_date && $today_date > $obj->end_date) {
				$obj->paid_status = $this->CI->config->item('finished_label');
			} elseif ($today_date < $obj->end_date && $today_date < $obj->end_date) {
				$obj->paid_status = $this->CI->config->item('not_yet_start_label');
			}
		} elseif ($today_date >= $obj->start_date && $today_date <= $obj->end_date) {
			$obj->paid_status = $this->CI->config->item('progress_label');
		} elseif ($today_date > $obj->start_date && $today_date > $obj->end_date) {
			$obj->paid_status = $this->CI->config->item('finished_label');
		} elseif ($today_date < $obj->end_date && $today_date < $obj->end_date) {
			$obj->paid_status = $this->CI->config->item('not_yet_start_label');
		} else {
			$obj->paid_status = $this->CI->config->item('not_available');
		}
		// item object
		if ( isset( $obj->item_id )) {
			$tmp_item = $this->CI->Item->get_one( $obj->item_id );
			$tmp_item->history_id = $obj->id;

			$this->convert_item( $tmp_item );

			$obj->item = $tmp_item;
		}

	}

	/**
	 * Customize tag object
	 *
	 * @param      <type>  $obj    The object
	 */
	function convert_rating( &$obj )
	{
		// set user object

		if ( is_array( $obj )) {
			
			for ($i=0; $i < count($obj) ; $i++) { 
				if ( isset( $obj[$i]->from_user_id )) {

					$tmp_from_user_id = $this->CI->User->get_one( $obj[$i]->from_user_id );
					//print_r($tmp_from_user_id);die;
					$this->convert_user( $tmp_from_user_id );
					//print_r($a);die;
					$obj[$i]->from_user = $tmp_from_user_id;
				}


				// set user object
				if ( isset( $obj[$i]->to_user_id )) {
					$tmp_to_user_id = $this->CI->User->get_one( $obj[$i]->to_user_id );

					$this->convert_user( $tmp_to_user_id );

					$obj[$i]->to_user = $tmp_to_user_id;
				}
			}
			

		}else {

			if ( isset( $obj->from_user_id )) {

					$tmp_from_user_id = $this->CI->User->get_one( $obj->from_user_id );
					//print_r($tmp_from_user_id);die;
					$this->convert_user( $tmp_from_user_id );
					//print_r($a);die;
					$obj->from_user = $tmp_from_user_id;
				}


				// set user object
				if ( isset( $obj->to_user_id )) {
					$tmp_to_user_id = $this->CI->User->get_one( $obj->to_user_id );

					$this->convert_user( $tmp_to_user_id );

					$obj->to_user = $tmp_to_user_id;
				}

		}
	}

	/**
	 * Customize chatitem object
	 *
	 * @param      <type>  $obj    The object
	 */
	function convert_chathistory( &$obj , $is_exchange_detail_req = true )
	{	
		if ( is_array( $obj )) {
			for ($i=0; $i < count($obj) ; $i++) { 

				if ( $obj[$i]->nego_price == '0') {
					$obj[$i]->is_offer = 0;
					$obj[$i]->offer_amount = 0;
					
				} else {
					$obj[$i]->is_offer = 1;
					$obj[$i]->offer_amount = $obj[$i]->nego_price;

				}
				// item object
				if ( isset( $obj[$i]->item_id )) {
					$tmp_item = $this->CI->Item->get_one( $obj[$i]->item_id );

					$this->convert_item( $tmp_item );

					$obj[$i]->item = $tmp_item;
				}

				//set users objects
				if ( isset( $obj[$i]->buyer_user_id )) {

					$tmp_buyer_user_id = $this->CI->User->get_one( $obj[$i]->buyer_user_id );
					$this->convert_user( $tmp_buyer_user_id );
					$obj[$i]->buyer = $tmp_buyer_user_id;
				}

				if ( isset( $obj[$i]->seller_user_id )) {
					$tmp_seller_user_id = $this->CI->User->get_one( $obj[$i]->seller_user_id );

					$this->convert_user( $tmp_seller_user_id );

					$obj[$i]->seller = $tmp_seller_user_id;
				}

				if ( isset( $obj[$i]->operation_type ) && $obj[$i]->operation_type == EXCHANGE && isset($obj[$i]->id) ) {
					$tmp_exchange_ids = $this->CI->ExchangeChatHistory->get_all_in_exchange_chat_item( array('chat_id' => $obj[$i]->id) );

					// foreach($tmp_exchange_ids as $offered){
					// 	$obj[$i]->offered_item_id[] = $offered->offered_item_id;
					// }
					$this->convert_exchange_chat_history( $tmp_exchange_ids );
					// $obj[$i]->exchange_chat_detail = $tmp_exchange_ids;
				
					foreach($tmp_exchange_ids as $exchange_data){
						$obj[$i]->exchange_item_id[] = $exchange_data->offered_item_id;
						$this->convert_item( $exchange_data->offered_item_detail );
						$obj[$i]->exchange_item_detail[] = $exchange_data->offered_item_detail;
						
					}
				} else{
					// IF OFFERED ITEM ID EXIST THEN GET DETAILS OF ITEM
					if ( isset( $obj[$i]->offered_item_id )) {
						$tmp_offered_item = $this->CI->Item->get_one( $obj[$i]->offered_item_id );
	
						$this->convert_item( $tmp_offered_item );
	
						$obj[$i]->offered_item_detail = $tmp_offered_item;
					}
				}
	
				// IF REQUESTED ITEM ID EXIST THEN GET DETAILS OF ITEM
				if ( isset( $obj[$i]->requested_item_id )) {
					$tmp_req_item = $this->CI->Item->get_one( $obj[$i]->requested_item_id );
	
					$this->convert_item( $tmp_req_item );
	
					$obj[$i]->requested_item_detail = $tmp_req_item;
				}
			}
		} else {


			if ( $obj->nego_price == '0') {
				$obj->is_offer = 0;
				$obj->offer_amount = 0;
				
			} else {
				$obj->is_offer = 1;
				$obj->offer_amount = $obj->nego_price;

			}

			if ( isset( $obj->item_id )) {
				$tmp_item = $this->CI->Item->get_one( $obj->item_id );

				$this->convert_item( $tmp_item );

				$obj->item = $tmp_item;
			}

			// set users objects
			
			if ( isset( $obj->buyer_user_id )) {

				$tmp_buyer_user_id = $this->CI->User->get_one( $obj->buyer_user_id );
				$this->convert_user( $tmp_buyer_user_id );
				$obj->buyer = $tmp_buyer_user_id;
			}


			if ( isset( $obj->seller_user_id )) {
				$tmp_seller_user_id = $this->CI->User->get_one( $obj->seller_user_id );

				$this->convert_user( $tmp_seller_user_id );

				$obj->seller = $tmp_seller_user_id;
			}

			// IF OPERATION TYPE EXCHANGE THEN GET EXCHANGE DATA AND OFFER DETAIL ITEM DATA
			if ( isset( $obj->operation_type ) && $obj->operation_type == EXCHANGE && isset($obj->id) ) {
				
				$tmp_exchange_ids = $this->CI->ExchangeChatHistory->get_all_in_exchange_chat_item( array('chat_id' => $obj->id) );

				// foreach($tmp_exchange_ids as $offered){
				// 	$obj->offered_item_id[] = $offered->offered_item_id;
				// }

				if($is_exchange_detail_req == true){
					$this->convert_exchange_chat_history( $tmp_exchange_ids );
				}
				// $obj->exchange_chat_detail = $tmp_exchange_ids;
				
				foreach($tmp_exchange_ids as $exchange_data){
					$obj->exchange_item_id[] = $offered->offered_item_id;
					$obj->exchange_item_detail[] = $exchange_data->offered_item_detail;
				}
			} else{
				// IF OFFERED ITEM ID EXIST THEN GET DETAILS OF ITEM
				if ( isset( $obj->offered_item_id )) {
					$tmp_offered_item = $this->CI->Item->get_one( $obj->offered_item_id );

					$this->convert_item( $tmp_offered_item );

					$obj->offered_item_detail = $tmp_offered_item;
				}
			}

			// IF REQUESTED ITEM ID EXIST THEN GET DETAILS OF ITEM
			if ( isset( $obj->requested_item_id )) {
				$tmp_req_item = $this->CI->Item->get_one( $obj->requested_item_id );

				$this->convert_item( $tmp_req_item );

				$obj->requested_item_detail = $tmp_req_item;
			}

		}	
	}


	/**
	 * Customize follwing user object
	 *
	 * @param      <type>  $obj    The object
	 */
	function convert_follow_user( &$obj )
	{
		// user object
		if ( isset( $obj->user_id )) {
			$tmp_user = $this->CI->User->get_one( $obj->user_id );

			$this->convert_user( $tmp_user );

			$obj->user_followed = $tmp_user;
		}
	}

	function convert_follow_user_list( &$obj, $followed_user_id )
	{

		if ( is_array( $obj )) {

			for ($i=0; $i < count($obj) ; $i++) { 

				$obj[$i]->is_followed = 0;
			
				 if($followed_user_id != "") {
					
					// $conds1['user_id']    = $following_user_id;
					// $conds1['followed_user_id'] = $obj[$i]->user_id;
					$conds1['user_id']    = $obj[$i]->user_id;
					$conds1['followed_user_id'] = $followed_user_id;
					// checking follower
					
					$followed_user_id = $this->CI->Userfollow->get_one_by($conds1)->id;
					//print_r($followed_user_id);die;
					$obj[$i]->is_followed = 0;
					if($followed_user_id != "") {
						$obj[$i]->is_followed = 1;
					} else {
						$obj[$i]->is_followed = 0;
					}

				}
				
				unset($followed_user_id);

				$obj[$i]->is_followed = $obj[$i]->is_followed;

				if ( isset( $obj[$i]->user_id )) {

					$obj[$i]->rating_count = $this->CI->Rate->count_all_by(array('to_user_id' => $obj[$i]->user_id));

						//rating details 
					$total_rating_count = 0;
					$total_rating_value = 0;

					$five_star_count = 0;
					$five_star_percent = 0;

					$four_star_count = 0;
					$four_star_percent = 0;

					$three_star_count = 0;
					$three_star_percent = 0;

					$two_star_count = 0;
					$two_star_percent = 0;

					$one_star_count = 0;
					$one_star_percent = 0;

					//Rating Total how much ratings for this product
					$conds_rating['to_user_id'] = $obj[$i]->user_id;
					
					$total_rating_count = $this->CI->Rate->count_all_by($conds_rating);
					$sum_rating_value = $this->CI->Rate->sum_all_by($conds_rating)->result()[0]->rating;


					//Rating Value such as 3.5, 4.3 and etc
					if($total_rating_count > 0) {
						$total_rating_value = number_format((float) ($sum_rating_value  / $total_rating_count), 1, '.', '');
					} else {
						$total_rating_value = 0;
					}

					//For 5 Stars rating

					$conds_five_star_rating['rating'] = 5;
					$conds_five_star_rating['to_user_id'] = $obj->id;
					$five_star_count = $this->CI->Rate->count_all_by($conds_five_star_rating);
					if($total_rating_count > 0) {
						$five_star_percent = number_format((float) ((100 / $total_rating_count) * $five_star_count), 1, '.', '');
					} else {
						$five_star_percent = 0;
					}

					//For 4 Stars rating
					$conds_four_star_rating['rating'] = 4;
					$conds_four_star_rating['to_user_id'] = $obj->id;
					$four_star_count = $this->CI->Rate->count_all_by($conds_four_star_rating);
					if($total_rating_count > 0) {
						$four_star_percent = number_format((float) ((100 / $total_rating_count) * $four_star_count), 1, '.', '');
					} else {
						$four_star_percent = 0;
					}


					//For 3 Stars rating
					$conds_three_star_rating['rating'] = 3;
					$conds_three_star_rating['to_user_id'] = $obj->id;
					$three_star_count = $this->CI->Rate->count_all_by($conds_three_star_rating);
					if($total_rating_count > 0) {
						$three_star_percent = number_format((float) ((100 / $total_rating_count) * $three_star_count), 1, '.', '');
					} else {
						$three_star_percent = 0;
					}


					//For 2 Stars rating
					$conds_two_star_rating['rating'] = 2;
					$conds_two_star_rating['to_user_id'] = $obj->id;
					$two_star_count = $this->CI->Rate->count_all_by($conds_two_star_rating);

					if($total_rating_count > 0) {
						$two_star_percent = number_format((float) ((100 / $total_rating_count) * $two_star_count), 1, '.', '');
					} else {
						$two_star_percent = 0;
					}

					//For 1 Stars rating
					$conds_one_star_rating['rating'] = 1;
					$conds_one_star_rating['to_user_id'] = $obj->id;
					$one_star_count = $this->CI->Rate->count_all_by($conds_one_star_rating);

					if($total_rating_count > 0) {
					$one_star_percent = number_format((float) ((100 / $total_rating_count) * $one_star_count), 1, '.', '');
					} else {
						$one_star_percent = 0;
					}


					$rating_std = new stdClass();
					@$rating_std->five_star_count = $five_star_count; 
					$rating_std->five_star_percent = $five_star_percent;

					$rating_std->four_star_count = $four_star_count;
					$rating_std->four_star_percent = $four_star_percent;

					$rating_std->three_star_count = $three_star_count;
					$rating_std->three_star_percent = $three_star_percent;

					$rating_std->two_star_count = $two_star_count;
					$rating_std->two_star_percent = $two_star_percent;

					$rating_std->one_star_count = $one_star_count;
					$rating_std->one_star_percent = $one_star_percent;

					$rating_std->total_rating_count = $total_rating_count;
					$rating_std->total_rating_value = $total_rating_value;

					$obj[$i]->rating_details = $rating_std;	
					
				}

			
			}

		} else {
			$obj->is_followed = 0;
			
			 if($followed_user_id != "") {
				
				// $conds1['user_id']    = $following_user_id;
				// $conds1['followed_user_id'] = $obj[$i]->user_id;
				$conds1['user_id']    = $obj[$i]->user_id;
				$conds1['followed_user_id'] = $followed_user_id;
				// checking follower
				
				$followed_user_id = $this->CI->Userfollow->get_one_by($conds1)->id;
				//print_r($followed_user_id);die;
				$obj->is_followed = 0;
				if($followed_user_id != "") {
					$obj->is_followed = 1;
				} else {
					$obj->is_followed = 0;
				}

			}
			
			unset($followed_user_id);

			$obj->is_followed = $obj->is_followed;
			$obj->rating_count = $this->CI->Rate->count_all_by(array('to_user_id' => $obj->user_id));
			//rating details 
			$total_rating_count = 0;
			$total_rating_value = 0;

			$five_star_count = 0;
			$five_star_percent = 0;

			$four_star_count = 0;
			$four_star_percent = 0;

			$three_star_count = 0;
			$three_star_percent = 0;

			$two_star_count = 0;
			$two_star_percent = 0;

			$one_star_count = 0;
			$one_star_percent = 0;

			//Rating Total how much ratings for this product
			$conds_rating['to_user_id'] = $obj->to_user_id;
			$total_rating_count = $this->CI->Rate->count_all_by($conds_rating);
			$sum_rating_value = $this->CI->Rate->sum_all_by($conds_rating)->result()[0]->rating;

			//Rating Value such as 3.5, 4.3 and etc
			if($total_rating_count > 0) {
				$total_rating_value = number_format((float) ($sum_rating_value  / $total_rating_count), 1, '.', '');
			} else {
				$total_rating_value = 0;
			}

			//For 5 Stars rating

			$conds_five_star_rating['rating'] = 5;
			$conds_five_star_rating['to_user_id'] = $obj->id;
			$five_star_count = $this->CI->Rate->count_all_by($conds_five_star_rating);
			if($total_rating_count > 0) {
				$five_star_percent = number_format((float) ((100 / $total_rating_count) * $five_star_count), 1, '.', '');
			} else {
				$five_star_percent = 0;
			}

			//For 4 Stars rating
			$conds_four_star_rating['rating'] = 4;
			$conds_four_star_rating['to_user_id'] = $obj->id;
			$four_star_count = $this->CI->Rate->count_all_by($conds_four_star_rating);
			if($total_rating_count > 0) {
				$four_star_percent = number_format((float) ((100 / $total_rating_count) * $four_star_count), 1, '.', '');
			} else {
				$four_star_percent = 0;
			}


			//For 3 Stars rating
			$conds_three_star_rating['rating'] = 3;
			$conds_three_star_rating['to_user_id'] = $obj->id;
			$three_star_count = $this->CI->Rate->count_all_by($conds_three_star_rating);
			if($total_rating_count > 0) {
				$three_star_percent = number_format((float) ((100 / $total_rating_count) * $three_star_count), 1, '.', '');
			} else {
				$three_star_percent = 0;
			}


			//For 2 Stars rating
			$conds_two_star_rating['rating'] = 2;
			$conds_two_star_rating['to_user_id'] = $obj->id;
			$two_star_count = $this->CI->Rate->count_all_by($conds_two_star_rating);

			if($total_rating_count > 0) {
				$two_star_percent = number_format((float) ((100 / $total_rating_count) * $two_star_count), 1, '.', '');
			} else {
				$two_star_percent = 0;
			}

			//For 1 Stars rating
			$conds_one_star_rating['rating'] = 1;
			$conds_one_star_rating['to_user_id'] = $obj->id;
			$one_star_count = $this->CI->Rate->count_all_by($conds_one_star_rating);

			if($total_rating_count > 0) {
			$one_star_percent = number_format((float) ((100 / $total_rating_count) * $one_star_count), 1, '.', '');
			} else {
				$one_star_percent = 0;
			}


			$rating_std = new stdClass();
			@$rating_std->five_star_count = $five_star_count; 
			$rating_std->five_star_percent = $five_star_percent;

			$rating_std->four_star_count = $four_star_count;
			$rating_std->four_star_percent = $four_star_percent;

			$rating_std->three_star_count = $three_star_count;
			$rating_std->three_star_percent = $three_star_percent;

			$rating_std->two_star_count = $two_star_count;
			$rating_std->two_star_percent = $two_star_percent;

			$rating_std->one_star_count = $one_star_count;
			$rating_std->one_star_percent = $one_star_percent;

			$rating_std->total_rating_count = $total_rating_count;
			$rating_std->total_rating_value = $total_rating_value;


			$obj[$i]->rating_details = $rating_std;	

		}


	}
			
	function convert_exchange_chat_history( &$obj, $item_detail_req = true )
	{
		// set user object
		if ( is_array( $obj )) {
			
			for ($i=0; $i < count($obj) ; $i++) { 
				if ( isset( $obj[$i]->offered_item_id )) {
					
					$tmp_item = $this->CI->Item->get_one( $obj[$i]->offered_item_id );
					// $this->convert_item( $tmp_item );
					$obj[$i]->offered_item_detail = $tmp_item;
				}
			}
			

		}else {

			if ( isset( $obj->offered_item_id )) {
					
				$tmp_item = $this->CI->Item->get_one( $obj->offered_item_id );
				// $this->convert_item( $tmp_item );
				$obj->offered_item_detail = $tmp_item;
			}

		}
	}

}