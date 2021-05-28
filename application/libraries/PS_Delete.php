<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PanaceaSoft Database Trigger
 */
class PS_Delete {

	// codeigniter instance
	protected $CI;

	/**
	 * Constructor
	 */
	function __construct()
	{
		// get CI instance
		$this->CI =& get_instance();

		// load image library
		$this->CI->load->library( 'PS_Image' );
	}

	/**
	 * Delete the category and image under the category
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_category( $category_id, $enable_trigger = false )
	{		
		if ( ! $this->CI->Category->delete( $category_id )) {
		// if there is an error in deleting category,
			
			return false;
		}

		// prepare condition
		$conds = array( 'img_type' => 'category', 'img_parent_id' => $category_id );
		if ( $this->CI->delete_images_by( $conds )) {
			$conds = array( 'img_type' => 'category-icon', 'img_parent_id' => $category_id );

			if ( !$this->CI->delete_images_by( $conds )) {
			// if error in deleting image, 

				return false;
			}
		}

		if ( $enable_trigger ) {
		// if execute_trigger is enable, trigger to delete wallpaper related data
			if ( ! $this->delete_category_trigger( $category_id )) {
			// if error in deleteing wallpaper and wallpaper related data

				return false;
			}
		}

		return true;
	}

	function delete_language( $id, $enable_trigger = false )
	{		
		if ( ! $this->CI->Language->delete( $id )) {
		// if there is an error in deleting language,
			
			return false;
		}

		if ( $enable_trigger ) {
		// if execute_trigger is enable, trigger to delete language related data
			if ( ! $this->delete_language_trigger( $id )) {
			// if error in deleteing language and language related data

				return false;
			}
		}

		return true;
	}

	/**
	* Trigger to delete language related data when language is deleted
	* delete language related data
	*/
	function delete_language_trigger( $id )
	{
		$conds['language_id'] = $id;

		// delete Item
		if ( !$this->CI->Language_string->delete_by( $conds )) {

			return false;
		}
		return true;
	}

	/**
	 * Delete the wallpaper and image under the wallpaper
	 *
	 * @param      <type>  $id     The identifier
	 */
	// function delete_item( $id, $enable_trigger = false )
	// {
	// 	if ( ! $this->CI->Item->delete( $id )) {
	// 	// if there is an error in deleting wallpaper,
			
	// 		return false;
	// 	}  
		
	// 	// prepare condition
	// 	$conds = array( 'img_type' => 'item', 'img_parent_id' => $id );

	// 	if ( !$this->CI->delete_images_by( $conds )) {
	// 	// if error in deleting image, 

	// 		return false;
	// 	}
	

		// if ( $enable_trigger ) {
		// // if execute_trigger is enable, trigger to delete wallpaper related data

		// 	if ( !$this->delete_item_trigger( $id )) {
		// 	// if error in deleting wallpaper related data,

		// 		return false;
		// 	}
			
		// }

		//return true;
	//}
	

	/**
	 * Delete the category and image under the category
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_color( $color_id, $enable_trigger = false )
	{		
		if ( ! $this->CI->Color->delete( $color_id )) {
		// if there is an error in deleting category,
			
			return false;
		}

		

		if ( $enable_trigger ) {
		// if execute_trigger is enable, trigger to delete wallpaper related data

			if ( ! $this->delete_color_trigger( $color_id )) {
			// if error in deleteing wallpaper and wallpaper related data

				return false;
			}
		}

		return true;
	}

	/**
	 * Delete the category and image under the category
	 *
	 * @param      <type>  $id     The identifier
	 */
	// when user delete from backend all the related table delete


	function delete_user( $user_id ) {


    	$conds_user['user_id'] = $user_id;
		$conds_from_user['from_user_id'] = $user_id;
		$conds_to_user['to_user_id'] = $user_id;
		$conds_added_user['added_user_id'] = $user_id;
		$conds_followed_user['followed_user_id'] = $user_id;
		$conds_buyer_user['buyer_user_id'] = $user_id;
		$conds_seller_user['seller_user_id'] = $user_id;

		//delete User
		if ( !$this->CI->User->delete_by( $conds_user )) {

			return false;
		}

		// delete Rating
		$this->CI->Rate->delete_by( $conds_from_user );
		$this->CI->Rate->delete_by( $conds_to_user );

		// delete push notification users
		if ( !$this->CI->Notireaduser->delete_by( $conds_user )) {

			return false;
		}

		// delete push notification tokens
		if ( !$this->CI->Noti->delete_by( $conds_user )) {

			return false;
		}

		// delete items and others related with item

		$items_data 	= $this->CI->Item->get_one_by($conds_added_user);

		$item_data['item_id'] = $items_data->id;
        $img_data['img_parent_id'] = $items_data->id;

        $this->CI->Chat->delete_by( $item_data );
        $this->CI->Paid_item->delete_by( $item_data );
        $this->CI->Favourite->delete_by( $item_data );
        $this->CI->Itemreport->delete_by( $item_data );
        $this->CI->Touch->delete_by( $item_data );
        $this->CI->Image->delete_by( $img_data );

		if ( !$this->CI->Item->delete_by( $conds_added_user )) {

			return false;
		}

		//delete follows

		$following_user		= $this->CI->Userfollow->get_all_by( $conds_user )->result();
		$follower_user		= $this->CI->Userfollow->get_all_by( $conds_followed_user )->result();

		foreach ($following_user as $following) {

			$conds_follower['user_id'] = $following->followed_user_id;

			$follower_user_data = $this->CI->User->get_one_by( $conds_follower );
			
			$follower_count = $follower_user_data->follower_count;

			$user_data = array(
			 	"follower_count" => $follower_count - 1
			 );

			 $this->CI->User->save($user_data,$follower_user_data->user_id);
			

		}


		foreach ($follower_user as $follower) {

			$conds_follow['user_id'] = $follower->user_id;

			$following_user_data = $this->CI->User->get_one_by( $conds_follow );

			$following_count = $following_user_data->following_count;

			$user_data = array(
			 	"following_count" => $following_count - 1
			 );

			 $this->CI->User->save($user_data,$following_user_data->user_id);

		}
		
		$this->CI->Userfollow->delete_by( $conds_user );
		$this->CI->Userfollow->delete_by( $conds_followed_user );
		

		// delete Favourite
		if ( !$this->CI->Favourite->delete_by( $conds_user )) {

			return false;
		}

		// delete Chat History

		$this->CI->Chat->delete_by( $conds_buyer_user );
		$this->CI->Chat->delete_by( $conds_seller_user ); 

		return true;
			

	}

	/**
	 * Trigger to delete wallpaper and related data when category is deleted
	 * delete wallpaper
	 * delete wallpaper images
	 */
	function delete_category_trigger( $cat_id )
	{
		
		return true;
	}

	/**
	 * Trigger to delete item and related data when category is deleted
	 * delete item
	 * delete item images
	 */
	// function delete_item_trigger( $item_id )
	// {
	// 	$conds_item['item_id'] = $item_id;
	// 	$conds_id['id'] = $item_id;

	// 	// delete Item
	// 	if ( !$this->CI->Item->delete_by( $conds_id )) {

	// 		return false;
	// 	}

	// 	// delete Paid history
	// 	if ( !$this->CI->Paid_item->delete_by( $conds_item )) {

	// 		return false;
	// 	}

	// 	// delete chat history
	// 	if ( !$this->CI->Chat->delete_by( $conds_item )) {

	// 		return false;
	// 	}

	// 	// delete favourite
	// 	if ( !$this->CI->Favourite->delete_by( $conds_item )) {

	// 		return false;
	// 	}

	// 	// delete item reports
	// 	if ( !$this->CI->Itemreport->delete_by( $conds_item )) {

	// 		return false;
	// 	}

	// 	// delete touches
	// 	if ( !$this->CI->Touch->delete_by( $conds_item )) {

	// 		return false;
	// 	}

	// 	return true;

	// }

	/**
	 * Trigger to unlink wallpaper and related data when color is deleted
	 * remove color_id from wallpaper
	 */
	function delete_color_trigger( $color_id )
	{
		
		$conds['color_id'] = $color_id;
		$wallpapers = $this->CI->Wallpaper->get_all_by($conds)->result();
		foreach ( $wallpapers as $wallpaper ) {
			$data['color_id'] = "";
			$this->CI->Wallpaper->save($data, $wallpaper->wallpaper_id);
		}
		return true;
		
		
	}

	/**
	 * Delete history for API
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_history( $type_id, $type_name, $enable_trigger = false )
	{		

		
		if( $type_name == "subcategory") {

			if ( ! $this->CI->Subcategory->delete( $type_id )) {
			// if there is an error in deleting product,
				
				return false;
			} else {
				//product is successfully deleted so need to save in log table
				$data_delete['type_id']   = $type_id;
				$data_delete['type_name'] = $type_name;

				$this->CI->Delete_history->save($data_delete);
			}

		} if( $type_name == "child_subcategory") {

			if ( ! $this->CI->Childsubcategory->delete( $type_id )) {
			// if there is an error in deleting product,
				
				return false;
			} else {
				//product is successfully deleted so need to save in log table
				// $data_delete['type_id']   = $type_id;
				// $data_delete['type_name'] = $type_name;

				// $this->CI->Delete_history->save($data_delete);
			}

		} else if ( $type_name == "category" ) {

			if ( ! $this->CI->Category->delete( $type_id )) {
			// if there is an error in deleting product,
				
				return false;
			} else {
				//product is successfully deleted so need to save in log table
				$data_delete['type_id']   = $type_id;
				$data_delete['type_name'] = $type_name;


				//$this->CI->Product_delete->save($data_delete);
				$this->CI->Delete_history->save($data_delete);
			}
		} else if ( $type_name == "item" ) {
			// delete Item -just updated status - modified by PP @18Dec2020
			$itm_delete['status'] = -1;

			//if ( ! $this->CI->Item->delete( $type_id )) {
			if ( ! $this->CI->Item->save( $itm_delete,$type_id )) {
			// if there is an error in deleting product,
				
				return false;
			} else {
				//product is successfully deleted so need to save in log table
				$data_delete['type_id']   = $type_id;
				$data_delete['type_name'] = $type_name;

				$this->CI->Delete_history->save($data_delete);
			}
		}


		// prepare condition
		if($type_name == "subcategory") {
		
			$conds = array( 'img_type' => 'subcategory', 'img_parent_id' => $type_id );
			if ( $this->CI->delete_images_by( $conds )) {
			$conds = array( 'img_type' => 'subcategory-icon', 'img_parent_id' => $type_id );
			// if error in deleting image,
				if ( !$this->CI->delete_images_by( $conds )) {
				
				// if error in deleting image, 

					return false;
				
				} 
			}
		
		} if($type_name == "child_subcategory") {
		
			$conds = array( 'img_type' => 'child_subcategory', 'img_parent_id' => $type_id );
			if ( $this->CI->delete_images_by( $conds )) {
			$conds = array( 'img_type' => 'child_subcategory-icon', 'img_parent_id' => $type_id );
			// if error in deleting image,
				if ( !$this->CI->delete_images_by( $conds )) {
				
				// if error in deleting image, 

					return false;
				
				} 
			}
		
		} else if($type_name == "category") {

			$conds = array( 'img_type' => 'category', 'img_parent_id' => $type_id );
			if ( $this->CI->delete_images_by( $conds )) {
			$conds = array( 'img_type' => 'category-icon', 'img_parent_id' => $type_id );
			// if error in deleting image,
				if ( !$this->CI->delete_images_by( $conds )) {
				
				// if error in deleting image, 

					return false;
				
				} 
			}

		} 

		// else if($type_name == "item") {

		// 	$conds = array( 'img_type' => 'item', 'img_parent_id' => $type_id );
		// 	if ( !$this->CI->Image->delete_by( $conds )) {
		// 		return false;
		// 	}

		//}

		
		if ( $enable_trigger ) {
		// if execute_trigger is enable, trigger to delete wallpaper related data
			if( $type_name == "subcategory" ) {
				if ( !$this->delete_subcategory_trigger( $type_id )) {
				// if error in deleting wallpaper related data,

					return false;
				}
			} else if( $type_name == "category" ) {

				if ( !$this->delete_category_trigger( $type_id )) {
					// if error in deleting wallpaper related data,
					return false;
				}

			} 

				// else if( $type_name == "item" ) {

				// 	if ( !$this->delete_item_trigger( $type_id )) {
				// 	// if error in deleting wallpaper related data,
				// 		return false;
				// 	}

				// }
				
		}

		return true;
	}

	/**
	 * Delete Image by id and type
	 *
	 * @param      <type>  $conds  The conds
	 */
	function delete_images_by( $conds )
	{
		// get all images
		$images = $this->CI->Image->get_all_by( $conds );
		
		if ( !empty( $images )) {
		// if images are not empty,

			foreach ( $images->result() as $img ) {
			// loop and delete each image

				if ( ! $this->CI->ps_image->delete_images( $img->img_path ) ) {
				// if there is an error in deleting images

					return false;
				}
			}
		}

		if ( ! $this->CI->Image->delete_by( $conds )) {
		// if error in deleting from database,

			return false;
		}

		return true;
	}

	/**
	 * Delete the group
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_module_group( $group_id, $enable_trigger = false )
	{		
		if ( ! $this->CI->Module_group->delete( $group_id )) {
		// if there is an error in deleting Coupon,
			
			return false;
		}
		return true;
	}

	/**
	 * Delete the module
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_module( $module_id, $enable_trigger = false )
	{		
		if ( ! $this->CI->Module->delete( $module_id )) {
		// if there is an error in deleting Coupon,
			
			return false;
		}
		return true;
	}

	function delete_report( $id, $enable_trigger = false )
	{		
		if ( ! $this->CI->Itemreport->delete( $id )) {
		// if there is an error in deleting Coupon,
			
			return false;
		}
		return true;
	}

	function delete_pending( $id, $enable_trigger = false )
	{		
		if ( ! $this->CI->Pending->delete( $id )) {
		// if there is an error in deleting Coupon,
			
			return false;
		}
		return true;
	}

	function delete_disable( $id, $enable_trigger = false )
	{		
		if ( ! $this->CI->Disable->delete( $id )) {
		// if there is an error in deleting Coupon,
			
			return false;
		}
		return true;
	}

	function delete_reject( $id, $enable_trigger = false )
	{		
		if ( ! $this->CI->Reject->delete( $id )) {
		// if there is an error in deleting Coupon,
			
			return false;
		}
		return true;
	}

	function delete_subcategory_trigger( $sub_cat_id )
	{
		// get all product and delete the wallpaper under the subcategory
		$items = $this->CI->Item->get_all_by( array( 'sub_cat_id' => $sub_cat_id, 'status' => 1 ))->result();
		if ( !empty( $items )) {
		// if the wallpaper list not empty
			
			// loop all the wallpaper
			foreach ( $items as $item ) {
				// delete wallpaper and images
				//$enable_trigger = true;
				$itm_delete['status'] = -1;
				//if ( !$this->delete_item( $item->id, $enable_trigger )) {
				if ( !$this->CI->Item->save( $itm_delete,$item->id )) {
				// if error in deleting wallpaper,

					return false;
				} 
			}
		}
		return true;
	}

	/**
	 * Delete the currency record
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_currency( $currency_id )
	{		
		if ( ! $this->CI->Currency->delete( $currency_id )) {
		// if there is an error in deleting currency,
			
			return false;
		}


		return true;
	}

	/**
	 * Delete the price record
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_price( $price_id )
	{		
		if ( ! $this->CI->Pricetype->delete( $price_id )) {
		// if there is an error in deleting currency,
			
			return false;
		}


		return true;
	}

	/**
	 * Delete the type record
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_type( $type_id )
	{		
		if ( ! $this->CI->Itemtype->delete( $type_id )) {
		// if there is an error in deleting currency,
			
			return false;
		}


		return true;
	}

	/**
	 * Delete the type record
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_location( $location_id )
	{		
		if ( ! $this->CI->Itemlocation->delete( $location_id )) {
		// if there is an error in deleting currency,
			
			return false;
		}


		return true;
	}

	/**
	 * Delete the Discount and image under the Product
	 *
	 * @param      <type>  $id     The identifier
	*/
	function delete_feed( $feed_id, $enable_trigger = false )
	{		
		if ( ! $this->CI->Feed->delete( $feed_id )) {
		// if there is an error in deleting Product,
			
			return false;
		}

		// prepare condition
		$conds = array( 'img_type' => 'feed', 'img_parent_id' => $feed_id );

		if ( !$this->CI->delete_images_by( $conds )) {
			
			return false;
			
		}
		return true;
	}

	/**
	* Delete All Chat History
	*/

	function delete_chat_history()
	{

		if ( ! $this->CI->Chat->delete_all()) {
			return false;
		}

		return true;

	}

	/**
	 * Delete the notification and image under the notification
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_noti( $id, $enable_trigger = false )
	{		
		if ( ! $this->CI->Noti_message->delete( $id )) {
		// if there is an error in deleting notification,
			
			return false;
		}

		// prepare condition
		$conds = array( 'img_type' => 'noti', 'img_parent_id' => $id );

		if ( !$this->CI->delete_images_by( $conds )) {
		// if error in deleting image, 

			return false;
		}

		if ( $enable_trigger ) {
		// if execute_trigger is enable, trigger to delete wallpaper related data

			if ( ! $this->delete_noti_trigger( $id )) {
			// if error in deleteing wallpaper and wallpaper related data

				return false;
			}
		}

		return true;
	}

	// delete condition

	
	function delete_condition( $condition_id )
	{		
		if ( ! $this->CI->Condition->delete( $condition_id )) {
		// if there is an error in deleting condition,
			
			return false;
		}


		return true;
	}


	// delete deal option

	
	function delete_option( $option_id )
	{		
		if ( ! $this->CI->Option->delete( $option_id )) {
		// if there is an error in deleting condition,
			
			return false;
		}


		return true;
	}

	function delete_offline( $id, $enable_trigger = false )
	{		
		if ( ! $this->CI->Offline_payment->delete( $id )) {
		// if there is an error in deleting category,
			
			return false;
		}

		// prepare condition
		$conds = array( 'img_type' => 'offline_payment', 'img_parent_id' => $id );

		if ( $this->CI->delete_images_by( $conds )) {
			$conds = array( 'img_type' => 'offline_icon', 'img_parent_id' => $id );

			if ( !$this->CI->delete_images_by( $conds )) {
			// if error in deleting image, 

				return false;
			}
		}

		if ( $enable_trigger ) {
		// if execute_trigger is enable, trigger to delete wallpaper related data
			if ( ! $this->delete_offline_trigger( $id )) {
			// if error in deleteing wallpaper and wallpaper related data

				return false;
			}
		}

		return true;
	}
    
   	function delete_purchase( $id, $enable_trigger = false )
	{		
		if ( ! $this->CI->In_app_purchase->delete( $id )) {
		// if there is an error in deleting category,
			
			return false;
		}

		if ( $enable_trigger ) {
		// if execute_trigger is enable, trigger to delete wallpaper related data
			if ( ! $this->delete_offline_trigger( $id )) {
			// if error in deleteing wallpaper and wallpaper related data

				return false;
			}
		}

		return true;
	}
	
	/**
	 * Delete the brand record
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_brand( $price_id )
	{		
		if ( ! $this->CI->Brands->delete( $price_id )) {
		// if there is an error in deleting currency,
			
			return false;
		}


		return true;
	}


	/**
	 * Delete the Blockuserreason record
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_blockuserreason( $price_id )
	{		
		if ( ! $this->CI->Blockuserreasons->delete( $price_id )) {
		// if there is an error in deleting currency,
			
			return false;
		}


		return true;
	}

	/**
	 * Delete the Reportitemreason record
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_reportitemreason( $price_id )
	{		
		if ( ! $this->CI->Reportitemreasons->delete( $price_id )) {
		// if there is an error in deleting currency,
			
			return false;
		}


		return true;
	}


	/**
	 * Delete the Returnreason record
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_returnreason( $price_id )
	{		
		if ( ! $this->CI->Returnreasons->delete( $price_id )) {
			
			return false;
		}


		return true;
	}

	/**
	 * Delete the Promotingitemplan record
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_promotingitemplan( $price_id )
	{		
		if ( ! $this->CI->Promotingitemplans->delete( $price_id )) {
			
			return false;
		}


		return true;
	}

	/**
	 * Delete the Address record
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_address( $address_id )
	{		
		echo $address_id;
		die;
		if ( ! $this->CI->Addresses->delete( $address_id )) {
			
			return false;
		}


		return true;
	}

	/**
	 * Delete the deliverymethod record
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_deliverymethod( $price_id )
	{		
		if ( ! $this->CI->Deliverymethods->delete( $price_id )) {
		// if there is an error in deleting currency,
			
			return false;
		}


		return true;
	}

	/**
	 * Delete the deliverymethod record
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_packagesize( $package_id )
	{		
		if ( ! $this->CI->Packagesizes->delete( $package_id )) {
			return false;
		}


		return true;
	}

	/**
	 * Delete the sizegroup record
	 *
	 * @param      <type>  $id     The identifier
	 */
	function delete_sizegroup( $sizeGroup_id )
	{		
		if ( ! $this->CI->Sizegroups->delete( $sizeGroup_id )) {
		// if there is an error in deleting currency,
			
			return false;
		}

		if ( ! $this->delete_sizegroup_option_trigger( $sizeGroup_id )) {
		// if error in deleteing sizegroup_option related data

			return false;
		}

		return true;
	}

	/**
	 * Trigger to delete sizegroup_option data when sizeGroup_id is deleted
	 * delete sizegroup_option
	 */
	function delete_sizegroup_option_trigger( $sizeGroup_id )
	{
		//var_dump($this->CI->Sizegroup_option); exit;
		$conds = array('sizegroup_id' => $sizeGroup_id );
		
		if ( ! $this->CI->Sizegroup_option->delete_by( $conds )) {
		// if there is an error in deleting currency,
			
			return false;
		}
		
		return true;
	}

}