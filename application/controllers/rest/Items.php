<?php
require_once( APPPATH .'libraries/REST_Controller.php' );

/**
 * REST API for News
 */
class Items extends API_Controller
{

	/**
	 * Constructs Parent Constructor
	 */
	function __construct()
	{
		parent::__construct( 'Item' );
		$this->load->library( 'PS_Image' );
	}

	/**
	 * Default Query for API
	 * @return [type] [description]
	 */
	function default_conds()
	{
		$conds = array();

		if ( $this->is_get ) {
		// if is get record using GET method

			// get default setting for GET_ALL_CATEGORIES
			//$setting = $this->Api->get_one_by( array( 'api_constant' => GET_ALL_CATEGORIES ));
			
			$conds['order_by'] = 1;
			$conds['order_by_field'] = $setting->order_by_field;
			$conds['order_by_type'] = $setting->order_by_type;
		}
		

		if ( $this->is_search ) {

			//$setting = $this->Api->get_one_by( array( 'api_constant' => SEARCH_WALLPAPERS ));

			if($this->post('searchterm') != "") {
				$conds['searchterm']   = $this->post('searchterm');
			}

			if($this->post('brand_id') != "") {
				$conds['brand_id']   = $this->post('brand_id');
			}

			if($this->post('cat_id') != "") {
				$conds['cat_id']   = $this->post('cat_id');
			}

			if($this->post('sub_cat_id') != "") {
				$conds['sub_cat_id']   = $this->post('sub_cat_id');
			}

			if($this->post('item_type_id') != "") {
				$conds['item_type_id']   = $this->post('item_type_id');
			}

			if($this->post('item_currency_id') != "") {
				$conds['item_currency_id']   = $this->post('item_currency_id');
			}
			
			if($this->post('lat') != "" && $this->post('lng') != "" && $this->post('miles') != "" && $this->post('item_location_id') != "") {
				$conds['item_location_id']   = $this->post('item_location_id');
			} if($this->post('lat') != "" && $this->post('lng') != "" && $this->post('miles') != "" && $this->post('item_location_id') == "") {
				$conds['item_location_id']   ="";
			} else {
				if($this->post('item_location_id') != "") {
					$conds['item_location_id']   = $this->post('item_location_id');
				}
			}

			if($this->post('deal_option_id') != "") {
				$conds['deal_option_id']   = $this->post('deal_option_id');
			}

			if($this->post('condition_of_item_id') != "") {
				$conds['condition_of_item_id']   = $this->post('condition_of_item_id');
			}

			if($this->post('min_price') != "") {
				$conds['min_price']   = $this->post('min_price');
			}

			if($this->post('max_price') != "") {
				$conds['max_price']   = $this->post('max_price');
			}

			if($this->post('brand') != "") {
				$conds['brand']   = $this->post('brand');
			}

			if($this->post('lat') != "") {
				$conds['lat']   = $this->post('lat');
			}

			if($this->post('lng') != "") {
				$conds['lng']   = $this->post('lng');
			}

			if($this->post('miles') != "") {
				$conds['miles']   = $this->post('miles');
			}

			if($this->post('added_user_id') != "") {
				$conds['added_user_id']   = $this->post('added_user_id');
			}

			if($this->post('is_paid') != "") {
				$conds['is_paid']   = $this->post('is_paid');
			}

			if($this->post('status') != "") {
				$conds['status']   = $this->post('status');
			} else {
				$conds['status']   = 1;
			}
			
			if($this->post('is_draft') == "1") {
				$conds['is_draft']   = 1;
			} else {
				$conds['is_draft']   = 0;
			}

			$conds['item_search'] = 1;
			$conds['order_by'] = 1;
			$conds['order_by_field']    = $this->post('order_by');
			$conds['order_by_type']     = $this->post('order_type');
				
		}

		return $conds;
	}

	function add_post() {

		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);

		$approval_enable = $this->App_setting->get_one('app1')->is_approval_enabled;
		if ($approval_enable == 1) {
			$status = 0;
		} else {
			$status = 1;
		}
		// validation rules for user register
		$rules = array(
			array(
	        	'field' => 'cat_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'sub_cat_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'item_type_id',
	        	'rules' => 'required'
	        ),
	       
	        array(
	        	'field' => 'item_currency_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'condition_of_item_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'item_location_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'title',
	        	'rules' => 'required'
	        ),
	         array(
	        	'field' => 'lat',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'lng',
	        	'rules' => 'required'
	        )

        );

        $lat = $this->post('lat');
		$lng = $this->post('lng');
        $location = location_check($lat,$lng);

        // exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;
        
	  	$item_data = array(

        	"cat_id" => $this->post('cat_id'), 
        	"sub_cat_id" => $this->post('sub_cat_id'),
        	"item_type_id" => $this->post('item_type_id'),
        	"item_price_type_id" => $this->post('item_price_type_id'),
        	"item_currency_id" => $this->post('item_currency_id'), 
        	"condition_of_item_id" => $this->post('condition_of_item_id'),
        	"item_location_id" => $this->post('item_location_id'),
        	"deal_option_remark" => $this->post('deal_option_remark'),
        	"description" => $this->post('description'),
        	"highlight_info" => $this->post('highlight_info'),
        	"price" => $this->post('price'),
        	"deal_option_id" => $this->post('deal_option_id'),
        	"brand" => $this->post('brand'),
        	"business_mode" => $this->post('business_mode'),
        	"is_sold_out" => $this->post('is_sold_out'),
        	"title" => $this->post('title'),
        	"address" => $this->post('address'),
        	"lat" => $this->post('lat'),
        	"lng" => $this->post('lng'),
        	"status" => $status,
        	"id" => $this->post('id'),
        	"added_user_id" => $this->post('added_user_id'),
        	"added_date" =>  date("Y-m-d H:i:s")
        	
        );

		$id = $item_data['id'];
		
		if($id != ""){
			$status = $this->Item->get_one($id)->status;
			$item_data['status'] = $status;
		 	$this->Item->save($item_data,$id);
		 	///start deep link update item tb by MN
			$description = $item_data['description'];
			$title = $item_data['title'];
			$conds_img = array( 'img_type' => 'item', 'img_parent_id' => $id );
	        $images = $this->Image->get_all_by( $conds_img )->result();
			$img = $this->ps_image->upload_url . $images[0]->img_path;
			$deep_link = deep_linking_shorten_url($description,$title,$img,$id);
			$itm_data = array(
				'dynamic_link' => $deep_link
			);
			$this->Item->save($itm_data,$id);
			///End

		} else{

		 	$this->Item->save($item_data);

		 	$id = $item_data['id'];
		 	///start deep link update item tb by MN
			$description = $item_data['description'];
			$title = $item_data['title'];
			$conds_img = array( 'img_type' => 'item', 'img_parent_id' => $id );
	        $images = $this->Image->get_all_by( $conds_img )->result();
			$img = $this->ps_image->upload_url . $images[0]->img_path;
			$deep_link = deep_linking_shorten_url($description,$title,$img,$id);
			$itm_data = array(
				'dynamic_link' => $deep_link
			);
			$this->Item->save($itm_data,$id);
			///End
		}
		 
		$obj = $this->Item->get_one( $id );
		
		$this->ps_adapter->convert_item( $obj );
		$this->custom_response( $obj );

	}

	/**
	 * Add / Update item of login users
	 * 1) Add / Edit item
	 * 2) Add item colors (optional)
	 * 3) Add item exchange category (optional)
	 * 4) Add item similar creteria (optional)
	 * 5) Add item sizegroup otions (optional)
	 * @param      <type>   $cat_id  The  cat_id
	 * @param      <type>   $sub_cat_id  The  sub_cat_id
	 * @param      <type>   $childsubcat_id  The  childsubcat_id
	 * @param      <type>   $item_type_id  The  item_type_id
	 * @param      <type>   $condition_of_item_id  The  condition_of_item_id
	 * @param      <type>   $title  The  title
	 * @param      <type>   $delivery_method_id  The  delivery_method_id
	 * @param      <type>   $address_id  The  address_id
	 * @param      <type>   $description  The  description
	 * @param      <type>   $price  (optional)
	 * @param      <type>   $brand  THe brand id (optional)
	 * @param      <type>   $id  (optional)
	 * @param      <type>   $sizegroup_id  (optional)
	 * @param      <type>   $is_all_colors (optional) 0/1
	 * @param      <type>   $pieces  (optional)
	 * @param      <type>   $is_negotiable  (optional) 0/1
	 * @param      <type>   $negotiable_percentage  (optional)
	 * @param      <type>   $expiration_date_days  (optional)
	 
	 * @param      <type>   $observation  (optional)
	 * @param      <type>   $is_draft  (optional) 0/1
	 * @param      <type>   $pay_shipping_by  (optional) '1 for buyer, 2 for seller'
	 * @param      <type>   $shipping_type  (optional) '1 for prepaid-label, 2 for manual-delivery
	 * @param      <type>   $packagesize_id  (optional)
	 * @param      <type>   $shippingcarrier_id  (optional)
	 * @param      <type>   $shipping_cost_by_seller  (optional)
	 * @param      <type>   $is_confirm_with_seller  (optional) 0/1
	 * @param      <type>   $is_exchange (optional) 0/1
	 * @param      <type>   $is_accept_similar  (optional) 0/1
	 * @param      <type>   $is_confirm (optional) 0/1
	 * @param     <type>    $pickup_distance  (optional)
	 * @param      <type>   $similar_items  (optional) array
	 * @param      <type>   $exchange_category  (optional) array
	 * @param      <type>   $color_ids  (optional) array
	 * @param      <type>   $sizegroupoption_ids  (optional) array
	 */

	function additem_post() {

		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);

		$todate = date('Y-m-d');
		
		$approval_enable = $this->App_setting->get_one('app1')->is_approval_enabled;
		if ($approval_enable == 1) {
			$status = 0;
		} else {
			$status = 1;
		}
		// validation rules for user register
		$rules = array(
			array(
	        	'field' => 'cat_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'sub_cat_id',
	        	'rules' => 'required'
	        ),
			array(
	        	'field' => 'childsubcat_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'item_type_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'condition_of_item_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'title',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'delivery_method_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'address_id',
	        	'rules' => 'required'
	        ),
	        array(
	        	'field' => 'price',
	        	'rules' => 'required'
	        )

        );

        // $lat = $this->post('lat');
		// $lng = $this->post('lng');
        // $location = location_check($lat,$lng);

        // exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;
        
	  	$item_data = array(

        	"cat_id" => $this->post('cat_id'), 
        	"sub_cat_id" => $this->post('sub_cat_id'),
        	"item_type_id" => $this->post('item_type_id'),
        	"condition_of_item_id" => $this->post('condition_of_item_id'),
        	"description" => $this->post('description'),
        	"price" => $this->post('price'),
        	"brand" => $this->post('brand'),
        	"title" => $this->post('title'),
        	"status" => $status,
        	"id" => $this->post('id'),
			"delivery_method_id" => $this->post('delivery_method_id'),
			"address_id" => $this->post('address_id'),
			"childsubcat_id" => $this->post('childsubcat_id'),
			"sizegroup_id" => $this->post('sizegroup_id'),
			"childsubcat_id" => $this->post('childsubcat_id'),
			"is_all_colors" => $this->post('is_all_colors'),
			"pieces" => $this->post('pieces'),
			"is_negotiable" => $this->post('is_negotiable'),
			"negotiable_percentage" => $this->post('negotiable_percentage'),
			"expiration_date_days" => $this->post('expiration_date_days'),
			"expiration_date" => !empty($this->post('expiration_date_days')) ? date('Y-m-d', strtotime($todate. ' + '.$this->post('expiration_date_days').' days')) : '',
			"pickup_distance" => $this->post('pickup_distance'),
			"observation" => $this->post('observation'),
			"is_draft" => $this->post('is_draft'),
			"pay_shipping_by" => ($this->post('item_type_id')=='1')?'':$this->post('pay_shipping_by'),
			"shipping_type" => ($this->post('item_type_id')=='1')?'':$this->post('shipping_type'),
			"packagesize_id" => ($this->post('item_type_id')=='1')?'':$this->post('packagesize_id'),
			"shippingcarrier_id" => ($this->post('item_type_id')=='1')?'':$this->post('shippingcarrier_id'),
			"shipping_cost_by_seller" => ($this->post('item_type_id')=='1')?'':$this->post('shipping_cost_by_seller'),
			"is_confirm_with_seller" => ($this->post('item_type_id')=='1')?'':$this->post('is_confirm_with_seller'),
			"is_exchange" => ($this->post('item_type_id')=='1')?'':$this->post('is_exchange'),
			"is_accept_similar" => $this->post('is_accept_similar'),
			"is_confirm" => $this->post('is_confirm'),
        	"added_user_id" => $this->post('added_user_id'),
        	"added_date" =>  date("Y-m-d H:i:s")
        	
        );

		// check if similar items are selected

		if($this->post('is_accept_similar')=='1' && (count($this->post('similar_items'))<=0 || empty($this->post('similar_items'))))
		{
			$this->error_response( get_msg( 'select_similar_items' ));
		}

		// check if exchange categories are selected
		if($this->post('is_exchange')=='1' && (count($this->post('exchange_category'))<=0 || empty($this->post('exchange_category'))))
		{
			$this->error_response( get_msg( 'select_exchange_category' ));
		}

		// check address id

		$address_id = $this->post('address_id');
		$conds['id'] = $address_id;
		$address_data = $this->Addresses->get_one_by($conds);
		if ( $address_data->id == "") {
			$this->error_response( get_msg( 'invalid_address_id' ));
		}

		// check delivery method id
		$deliverycheck  = $this->Deliverymethods->get_one($this->post('delivery_method_id'));
		if(isset($deliverycheck->is_empty_object))
		{
			$this->error_response( get_msg( 'delivery_method_not_found' ));
		}


		$id = $item_data['id'];
		
		if($id != ""){
			$status = $this->Item->get_one($id)->status;
			$item_data['status'] = $status;
		 	$this->Item->save($item_data,$id);
		 	///start deep link update item tb by MN
			$description = $item_data['description'];
			$title = $item_data['title'];
			$conds_img = array( 'img_type' => 'item', 'img_parent_id' => $id );
	        $images = $this->Image->get_all_by( $conds_img )->result();
			$img = $this->ps_image->upload_url . $images[0]->img_path;
			$deep_link = deep_linking_shorten_url($description,$title,$img,$id);
			$itm_data = array(
				'dynamic_link' => $deep_link
			);
			$this->Item->save($itm_data,$id);
			///End

		} else{

		 	$this->Item->save($item_data);

		 	$id = $item_data['id'];
		 	///start deep link update item tb by MN
			$description = $item_data['description'];
			$title = $item_data['title'];
			$conds_img = array( 'img_type' => 'item', 'img_parent_id' => $id );
	        $images = $this->Image->get_all_by( $conds_img )->result();
			$img = $this->ps_image->upload_url . $images[0]->img_path;
			$deep_link = deep_linking_shorten_url($description,$title,$img,$id);
			$itm_data = array(
				'dynamic_link' => $deep_link
			);
			$this->Item->save($itm_data,$id);
			///End

		}
		 
		//$itemcheckdata = array( 'item_id' => $id );

		if($this->post('item_type_id')!='1')
		{
			$this->db->where('item_id', $id);
    		$this->db->delete('bs_item_similarcreteria');
		}
		
		if(count($this->post('similar_items'))>0 && $this->post('item_type_id')=='1')
		{
			
			$this->db->where('item_id', $id);
    		$this->db->delete('bs_item_similarcreteria');
			
			foreach($this->post('similar_items') as $similaritemcreteria)
			{
				$similar_data = array(
					"similarcreteria_id" => $similaritemcreteria, 
					"item_id" => $id,
					"added_date" =>  date("Y-m-d H:i:s")
				);
				$this->Itemsimilarcreteria->save($similar_data);
			}
		}

		if($this->post('item_type_id')=='1')
		{
			$this->db->where('item_id', $id);
    		$this->db->delete('bs_item_exchange');
		}

		if(count($this->post('exchange_category'))>0 && ($this->post('item_type_id')=='2' || $this->post('item_type_id')=='3'))
		{
			$this->db->where('item_id', $id);
    		$this->db->delete('bs_item_exchange');

			foreach($this->post('exchange_category') as $catid)
			{
				$exchange_data = array(
					"cat_id" => $catid, 
					"item_id" => $id,
					"added_date" =>  date("Y-m-d H:i:s")
				);
				$this->Itemexchangecategory->save($exchange_data);
			}
		}

		if(count($this->post('color_ids'))>0)
		{
			$this->db->where('item_id', $id);
    		$this->db->delete('bs_item_colors');

			foreach($this->post('color_ids') as $colorid)
			{
				$color_data = array(
					"color_id" => $colorid, 
					"item_id" => $id,
					"added_date" =>  date("Y-m-d H:i:s")
				);
				$this->Itemcolors->save($color_data);
			}
		}

		if(count($this->post('sizegroupoption_ids'))>0)
		{
			$this->db->where('item_id', $id);
    		$this->db->delete('bs_item_sizegroupoptions');

			foreach($this->post('sizegroupoption_ids') as $optionid)
			{
				$option_data = array(
					"sizegroup_option_id" => $optionid, 
					"item_id" => $id,
					"added_date" =>  date("Y-m-d H:i:s")
				);
				$this->Itemsizegroupoptions->save($option_data);
			}
		}


		$obj = $this->Item->get_one( $id );

		$this->db->where('item_id', $id);
    	$colordata = $this->db->get('bs_item_colors');

		$this->db->where('item_id', $id);
    	$sizegroupoptiondata = $this->db->get('bs_item_sizegroupoptions');

		$this->db->where('item_id', $id);
    	$exchangecatdata = $this->db->get('bs_item_exchange');

		$this->db->where('item_id', $id);
    	$similaritemdata = $this->db->get('bs_item_similarcreteria');

		
		$obj->item_colors = $colordata->result();
		$obj->sizegroup_options = $sizegroupoptiondata->result();
		$obj->exchange_category = $exchangecatdata->result();
		$obj->similar_item = $similaritemdata->result();

		$this->ps_adapter->convert_item( $obj );
		$this->custom_response( $obj );

	}

	/**
	 * Search item with filters
	 * @param      <type>   $cat_id  The  cat_id
	 * @param      <type>   $sub_cat_id  The  sub_cat_id
	 * @param      <type>   $childsubcat_id  The  childsubcat_id
	 * @param      <type>   $item_type_id  The  item_type_id
	 * @param      <type>   $condition_of_item_id  The  condition_of_item_id
	 * @param      <type>   $title  The  title
	 * @param      <type>   $delivery_method_id  The  delivery_method_id
	 * @param      <type>   $brand  THe brand id
	 * @param      <type>   $sizegroup_id 
	 * @param      <type>   $color_id array
	 * @param      <type>   $sizegroupoption_id  array
	 */

	function searchitem_post()
	{
		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);	

		// add flag for default query
		$this->is_search = true;

		// add default conds
		$default_conds = $this->default_conds();
		$user_conds = $this->get();

		$post_conds = $this->post();
		$conds = array_merge( $default_conds, $user_conds );

		$conds = array_merge( $post_conds, $conds );

		
		// check empty condition
		$final_conds = array();
		foreach( $conds as $key => $value ) {
    
		    if($key != "status") {
			    if ( !empty( $value )) {
			     $final_conds[$key] = $value;
			    }
		    }

		    if($key == "status") {
		    	$final_conds[$key] = $value;
		    }


		}
		$conds = $final_conds;
		//echo '<pre>'; print_r($conds); die;
		$limit = $this->get( 'limit' );
		$offset = $this->get( 'offset' );
		
		if ($conds['item_search']==1) {

			/* For User Block */

			//user block check with login_user_id
			$conds_login_block['from_block_user_id'] = $this->get_login_user_id();
			$login_block_count = $this->Block->count_all_by($conds_login_block);
			//print_r($login_block_count);die;

			// user blocked existed by login user
			if ($login_block_count > 0) {
				// get the blocked user by login user
				$to_block_user_datas = $this->Block->get_all_by($conds_login_block)->result();

				foreach ( $to_block_user_datas as $to_block_user_data ) {

					$to_block_user_id .= "'" .$to_block_user_data->to_block_user_id . "',";
			
				}

				// get block user's item

				$result_users = rtrim($to_block_user_id,',');
				$conds_user['added_user_id'] = $result_users;

				$item_users = $this->Item->get_all_in_item( $conds_user )->result();

				foreach ( $item_users as $item_user ) {

					$id .= $item_user->id .",";
				
				}

				// get all item without block user's item

				$result_items = rtrim($id,',');
				$item_id = explode(",", $result_items);
				//print_r($item_id);die;
				//$conds['id'] = $result_items;

			}	

			/* For Item Report */

			//item report check with login_user_id
			$conds_report['reported_user_id'] = $this->get_login_user_id();
			$reported_data_count = $this->Itemreport->count_all_by($conds_report);

			// item reported existed by login user
			if ($reported_data_count > 0) {
				// get the reported item data
				$item_reported_datas = $this->Itemreport->get_all_by($conds_report)->result();

				foreach ( $item_reported_datas as $item_reported_data ) {

					$item_ids .= "'" .$item_reported_data->item_id . "',";
			
				}

				// get block user's item

				$result_reports = rtrim($item_ids,',');
				$conds_item['id'] = $result_reports;

				$item_reports = $this->Item->get_all_in_report( $conds_item )->result();

				foreach ( $item_reports as $item_report ) {

					$ids .= $item_report->id .",";
				
				}

				// get all item without block user's item

				$result_items = rtrim($ids,',');
				$reported_item_id = explode(",", $result_items);
				//$conds['id'] = $result_items;
			}

			//  color id condition 
			if ( isset( $conds['color_id'] ) && !empty( $conds['color_id'] )) {

				foreach($conds['color_id'] as $colorid)
				{
					if ( $colorid != "") {
						if( $colorid != '0'){
						
							$this->db->select('*');
							$this->db->from('bs_item_colors');
							$this->db->where( 'color_id', $colorid );
							$colorfilter = $this->db->get();
							foreach($colorfilter->result() as $coloritem)
							{
								$colorids .= $coloritem->item_id .",";
							}
						}
					}	
				}
				
						
			}

			if(isset($colorids) && $colorids !='')
			{
				$color_items = rtrim($colorids,',');
				$colored_item_id = explode(",", $color_items);	
			}
			
			//  sizegroupoption id condition 

			if ( isset( $conds['sizegroupoption_id'] ) && !empty( $conds['sizegroupoption_id'] )) {

				foreach($conds['sizegroupoption_id'] as $optionid)
				{
					if ( $optionid != "") {
						if( $optionid != '0'){
						
							$this->db->select('*');
							$this->db->from('bs_item_sizegroupoptions');
							$this->db->where( 'sizegroup_option_id', $optionid );
							$sizeoptionfilter = $this->db->get();
							foreach($sizeoptionfilter->result() as $sizeoptionitem)
							{
								$sizeoptionids .= $sizeoptionitem->item_id .",";
							}
						}
					}	
				}
			}

			if(isset($sizeoptionids) && $sizeoptionids !='')
			{
				$sizeoption_items = rtrim($sizeoptionids,',');
				$sizeoption_item_id = explode(",", $sizeoption_items);	
			}
			
			if ($conds['is_paid'] == "only_paid_item") {

				//$conds['item_id'] = $item_id;
				//$conds['reported_item_id'] = $reported_item_id;
				$conds['is_paid'] = 1 ;

				$conds['coloritem_id'] = $colored_item_id;
				$conds['sizeoption_item_id'] = $sizeoption_item_id;
				$conds['address_item_id'] = $address_item_id;
				$conds['itemtype_item_id'] = $itemtype_item_id;
				$conds['childsubcat_item_id'] = $childsubcat_item_id;
				$conds['deliverymethod_item_id'] = $deliverymethod_item_id;
				$conds['itemcondition_item_id'] = $itemcondition_item_id;
				$conds['brand_items_id'] = $brand_items_id;
				
				if ( !empty( $limit ) && !empty( $offset )) {
				// if limit & offset is not empty
				$data = $this->model->get_all_item_by_paid( $conds, $limit, $offset )->result();


				} else if ( !empty( $limit )) {
					// if limit is not empty
					$data = $this->model->get_all_item_by_paid( $conds, $limit )->result();

				} else {
					// if both are empty
					$data = $this->model->get_all_item_by_paid( $conds )->result();

				}
			} elseif ($conds['is_paid'] == "paid_item_first") {
				$result = "";

				//$conds['item_id'] = $item_id;
				//$conds['reported_item_id'] = $reported_item_id;
				$conds['is_paid'] = 1;
				
				$conds['coloritem_id'] = $colored_item_id;
				$conds['sizeoption_item_id'] = $sizeoption_item_id;
				$conds['address_item_id'] = $address_item_id;
				$conds['itemtype_item_id'] = $itemtype_item_id;
				$conds['childsubcat_item_id'] = $childsubcat_item_id;
				$conds['deliverymethod_item_id'] = $deliverymethod_item_id;
				$conds['itemcondition_item_id'] = $itemcondition_item_id;
				$conds['brand_items_id'] = $brand_items_id;
				if ( !empty( $limit ) && !empty( $offset )) {
					// if limit & offset is not empty
					$data = $this->model->get_all_item_by_paid_date( $conds, $limit, $offset )->result();


				} else if ( !empty( $limit )) {
					// if limit is not empty
					$data = $this->model->get_all_item_by_paid_date( $conds, $limit )->result();

				} else {
					// if both are empty
					$data_paid = $this->model->get_all_item_by_paid_date( $conds )->result();

				}
			} else {

				//$conds['item_id'] = $item_id;
				//$conds['reported_item_id'] = $reported_item_id;
				$conds['is_paid'] = 0;
				$conds['coloritem_id'] = $colored_item_id;
				$conds['sizeoption_item_id'] = $sizeoption_item_id;
				$conds['address_item_id'] = $address_item_id;
				$conds['itemtype_item_id'] = $itemtype_item_id;
				$conds['childsubcat_item_id'] = $childsubcat_item_id;
				$conds['deliverymethod_item_id'] = $deliverymethod_item_id;
				$conds['itemcondition_item_id'] = $itemcondition_item_id;
				$conds['brand_items_id'] = $brand_items_id;
				

				if ( !empty( $limit ) && !empty( $offset )) {
					// if limit & offset is not empty
					$data = $this->model->get_all_by_itemnew( $conds, $limit, $offset )->result();


					} else if ( !empty( $limit )) {
						// if limit is not empty
						$data = $this->model->get_all_by_itemnew( $conds, $limit )->result();

					} else {
						// if both are empty
						$data = $this->model->get_all_by_itemnew( $conds )->result();

					}
				
				}	
			
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
		}

		$this->custom_response( $data );
	}

	/**
	 * Delete image from database and folder
	 * @param      <type>   $image_id  The  image_id
	 
	 */
	function deleteimage_post()
	{
		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);

		// validation rules for image delete
		$rules = array(
			array(
	        	'field' => 'image_id',
	        	'rules' => 'required'
	        )
	    );   
	    
	    // exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        $id = $this->post('image_id');

        $conds['img_id'] = $id;

        // check image id

        $image_data = $this->Image->get_one_by($conds);

        
		if ( $image_data->img_id == "") {

        	$this->error_response( get_msg( 'invalid_image_id' ));

        } else {
			//@unlink('./uploads/'.$image_data->img_path);
			$img_path = './uploads/'.$image_data->img_path;
			unlink( $img_path );
			$this->db->where('img_id', $id);
        	$this->db->delete('core_images');

			$this->success_response( get_msg( 'success_delete' ));
		}

	}

	/**
	 * auto suggestion item with category
	 * @param      <type>   $text  The  to search the item
	 
	 */
	function autosearch_post()
	{
		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);

		// validation rules for image delete
		$rules = array(
			array(
	        	'field' => 'text',
	        	'rules' => 'required'
	        )
	    );   
	    
	    // exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        $title = $this->post('text');

        $requesttype = $this->db->select('name,id')->from('bs_items_types')->where('id', 1)->where('status', 1)->get()->row();
		$sellingtype = $this->db->select('name,id')->from('bs_items_types')->where('id', 2)->where('status', 1)->get()->row();
		$sellingexchangetype = $this->db->select('name,id')->from('bs_items_types')->where('id', 3)->where('status', 1)->get()->row();

		$typeArr = array($requesttype,$sellingtype,$sellingexchangetype);

		foreach($typeArr as $typekey => $type)
		{
			$this->db->select('bs_items.id,bs_items.title, bs_categories.cat_name as catname, CONCAT(bs_items.title, " in ", bs_categories.cat_name) AS display_text'); 
			$this->db->from('bs_categories');
			$this->db->join('bs_items', 'bs_categories.cat_id = bs_items.cat_id');
			$this->db->where("title LIKE '%$title%'");
			$this->db->where('item_type_id', $type->id);
			$this->db->where('bs_items.status', 1);
			$searchresult = $this->db->get()->result();
			//echo count($searchresult);
			if(count($searchresult)<='0')
			{
				unset($typeArr[$typekey]);
			}
			else
			{
				$typeArr[$typekey]->searchresult = $searchresult;
			}
			
		}

		if(count($typeArr)>0)
		{
			$this->response($typeArr);
		}
		else
		{
			$this->error_response( get_msg( 'record_not_found' ) );
		}

		

	}


	/**
	* Trigger to delete item related data when item is deleted
	* delete item related data
	*/

	function item_delete_post( ) {

		// validation rules for item register
		$rules = array(
			array(
	        	'field' => 'item_id',
	        	'rules' => 'required'
	        )
	    );   
	    
	    // exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;

        $id = $this->post('item_id');

        $conds['id'] = $id;

        // check user id

        $item_data = $this->Item->get_one_by($conds);

        //print_r($item_data);die;


        if ( $item_data->id == "" || $item_data->status == "-1" ) {

        	$this->error_response( get_msg( 'invalid_item_id' ));

        } else {

        	// delete Item -just updated status - modified by PP @18Dec2020
        	$itm_data['status'] = -1 ;
			if ( !$this->Item->save( $itm_data,$id )) {

				return false;
			}

        	// $conds_id['id'] = $id;
         	// $conds_item['item_id'] = $id;
        	// $conds_img['img_parent_id'] = $id;

			// // delete Item
			// if ( !$this->Item->delete_by( $conds_id )) {

			// 	return false;
			// }

			
			// // delete chat history
			// if ( !$this->Chat->delete_by( $conds_item )) {

			// 	return false;
			// }

			// // delete favourite
			// if ( !$this->Favourite->delete_by( $conds_item )) {

			// 	return false;
			// }

			// // delete item reports
			// if ( !$this->Itemreport->delete_by( $conds_item )) {

			// 	return false;
			// }

			// // delete touches
			// if ( !$this->Touch->delete_by( $conds_item )) {

			// 	return false;
			// }

			// // delete images
			// if ( !$this->Image->delete_by( $conds_img )) {

			// 	return false;
			// }

			// // delete paid item
			// if ( !$this->Paid_item->delete_by( $conds_item )) {

			// 	return false;
			// }
			
			$this->success_response( get_msg( 'success_delete' ));

        }


	}

	/**
	 * Update Price 
	 */
	function sold_out_from_itemdetails_post()
	{
		// validation rules for chat history
		$rules = array(
			array(
	        	'field' => 'item_id',
	        	'rules' => 'required'
	        )
        );


		// exit if there is an error in validation,
        if ( !$this->is_valid( $rules )) exit;
        $id = $this->post('item_id');
        $item_sold_out = array(

        	"is_sold_out" => 1, 

        );

        $this->Item->save($item_sold_out,$id);
        $conds['id'] = $id;
        
        $obj = $this->Item->get_one_by($conds);

        $this->ps_adapter->convert_item( $obj );
        $this->custom_response($obj);
    }


	/**
	 * Convert Object
	 */
	function convert_object( &$obj )
	{

		// call parent convert object
		parent::convert_object( $obj );

		// convert customize item object
		$this->ps_adapter->convert_item( $obj );
	}

	/**
	* Get drafted items from item database table
	*/
	function get_drafted_item_post(){
		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['GET'],
            'requireAuthorization' => true,
        ]);
		$userId = $this->post('user_id');
		$itemId = $this->post('item_id');

		$this->db->where('added_user_id', $userId);
		$this->db->where('is_draft', 1);
    	$itemData = $this->db->get('bs_items');

        $items = $itemData->result();
		$this->ps_adapter->convert_item( $items );
		$result['data'] = $items; 
        $result['item_count'] = count($items); 
        $this->custom_response($result);
	}



	/**
	* Get drafted items from item database table
	*/
	function deactivate_item_get(){
		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['GET'],
            'requireAuthorization' => true,
        ]);
		$userId = !empty($user_data['token_data']) && !empty($user_data['token_data']['user_id']) ? $user_data['token_data']['user_id'] : '';
		$itemId = $this->get('id');

		$this->db->where('added_user_id', $userId);
		$this->db->where('id', $itemId);
    	$itemData = $this->db->get('bs_items');
        $items = $itemData->result_array();
		if(!empty($items)){
			$id = ($items && $items[0]['id']) ? $items[0]['id'] : 0;
			$itm_data['status'] = 0 ;
			if ( !$this->Item->save( $itm_data,$id )) {
				return false;
			}
			$this->success_response( get_msg( 'success_deactivated' ));
		} else {
			$this->error_response( get_msg( 'record_not_found' ) );
		}	
	}

	/**
	* Get drafted items from item database table
	*/
	function get_exchange_item_post(){
		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
		$userId = $this->post('user_id');
		$itemId = $this->post('item_id');

		$itemData = $this->Item->get_one_by(array('id' => $itemId));
		$itemTypeId = $itemData->item_type_id ? $itemData->item_type_id : 3;
		$this->db->where('item_id', $itemId);
    	$getExchangeData = $this->db->get('bs_item_exchange');
		$itemsInExchange = $getExchangeData->result_array();
		if(!empty($itemsInExchange)){
			$catIds = [];
			foreach($itemsInExchange as $categoryData){
				$catIds[] = $categoryData['cat_id'];
			}
			$this->db->where('item_type_id', $itemTypeId);
			$this->db->where('added_user_id', $userId);
			$this->db->where_in('cat_id', $catIds);	
			$exchangeItems = $this->db->get('bs_items');
			$exchangeData = $exchangeItems->result_array();
			$this->custom_response($exchangeData);
		} else {
			$this->error_response( get_msg( 'record_not_found' ) );
		}	
	}

}