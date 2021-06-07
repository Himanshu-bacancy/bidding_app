<?php
require_once( APPPATH .'libraries/REST_Controller.php' );

/**
 * REST API for Childsubcategories
 */
class Childsubcategories extends API_Controller
{

	/**
	 * Constructs Parent Constructor
	 */
	function __construct()
	{
		parent::__construct( 'Childsubcategory' );
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
			$conds['sub_color'] = 'color';
			$conds['sub_brand'] = 'brand';
			$conds['sub_size'] = 'size';
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

		// convert customize item object
		$this->ps_adapter->convert_childsubcategory( $obj );
	}

	/**
	 * Fetch Child Subcategory
	 * 1) fetch child subcategory data
	 * 2) Fetch all filters with details
	 * @param      <type>   $cat_id  The  category id
	 * @param      <type>   $sub_cat_id  The  sub category id
	 *  @param      <type>   $id  The  child sub category id (optional)
	 */
	function getdata_post()
	{
		// API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);
		// if id is passed in post param
		$id = $this->post( 'id' );

		if ( $id ) {
			
			// if 'id' is existed, get one record only
			$data = $this->model->get_one( $id );

			if ( isset( $data->is_empty_object )) {
			// if the id is not existed in the return object, the object is empty
				
				$data = array();
			}

			if($data->is_color_filter =='1')
			{
				$condscstm = array();
				$condscstm['no_publish_filter'] = 1;

				$colorarray = $this->Color->get_all_by( $condscstm);
				
				$data->colors = $colorarray->result();
			}

			if($data->is_brand_filter =='1')
			{
				$condscstm = array();
				$condscstm['no_publish_filter'] = 1;

				$brandarray = $this->Brands->get_all_by( $condscstm);
				
				$data->brands = $brandarray->result();
			}

			if($data->is_size_filter =='1')
			{
				$sizegroups = $this->Sizegroups->get_all();
				$selectedSizeGroups = $this->Childsubcategory->getSelectedSizegroups($data->id);
				$options=array();
				
				foreach($selectedSizeGroups as $sizekey => $sgroups) {
					$sizearray = $this->Sizegroups->get_one($sgroups);
					
					
					$sizearr = json_decode(json_encode($sizearray), true);

					if(count($sizearray)>0)
					{
						
						$condscstm = array();
						$condscstm['no_publish_filter'] = 1;
						$condscstm['sizegroup_id'] = $sizearr['id'];
						$sizearr['options']=$this->Sizegroup_option->get_all_by( $condscstm)->result();
						
					}
					$selectedSizeGroups[$sizekey]=$sizearr;
				}
				$data->sizes =$selectedSizeGroups ;
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
		

		// if ( count( $conds ) == 0 ) {
		// // if 'id' is not existed, get all	
		
		// 	if ( !empty( $limit ) && !empty( $offset )) {
		// 	// if limit & offset is not empty
				
		// 		$data = $this->model->get_all( $limit, $offset )->result();
		// 	} else if ( !empty( $limit )) {
		// 	// if limit is not empty
				
		// 		$data = $this->model->get_all( $limit )->result();
		// 	} else {
		// 	// if both are empty

		// 		$data = $this->model->get_all()->result();
		// 	}

		// 	$this->custom_response( $data , $offset );
		// } 
		else {

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
				
			);

			// exit if there is an error in validation,
			if ( !$this->is_valid( $rules )) exit;

			// Fetch childsubcategory with cat is and sub cat id

			if($this->post( 'cat_id' ) && $this->post( 'sub_cat_id' ))
			{
				$conds['cat_id'] =  $this->post( 'cat_id' );
				$conds['sub_cat_id'] = $this->post( 'sub_cat_id' );
				
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
					
					foreach($data as $childkey => $colordata)
					{
						if($colordata->is_color_filter =='1')
						{
							$condscstm = array();
							$condscstm['no_publish_filter'] = 1;
		
							$colorarray = $this->Color->get_all_by( $condscstm);
							
							$data[$childkey]->colors = $colorarray->result();
						}
					}
					
					
		
					foreach($data as $childkey => $branddata)
					{
						if($branddata->is_brand_filter =='1')
						{
							$condscstm = array();
							$condscstm['no_publish_filter'] = 1;
		
							$brandarray = $this->Brands->get_all_by( $condscstm);
							
							$data[$childkey]->brands = $brandarray->result();
						}
					}
					
		
					foreach($data as $childkey => $sizedata)
					{
						if($sizedata->is_size_filter =='1')
						{
							$sizegroups = $this->Sizegroups->get_all();
							$selectedSizeGroups = $this->Childsubcategory->getSelectedSizegroups($sizedata->id);
							$options=array();
							
							foreach($selectedSizeGroups as $sizekey => $sgroups) {
								$sizearray = $this->Sizegroups->get_one($sgroups);
								
								
								$sizearr = json_decode(json_encode($sizearray), true);
	
								if(count($sizearray)>0)
								{
									
									$condscstm = array();
									$condscstm['no_publish_filter'] = 1;
									$condscstm['sizegroup_id'] = $sizearr['id'];
									$sizearr['options']=$this->Sizegroup_option->get_all_by( $condscstm)->result();
									
								}
								$selectedSizeGroups[$sizekey]=$sizearr;
							}
							$data[$childkey]->sizes =$selectedSizeGroups ;
						}
					}
					
					$this->custom_response( $data , $offset );
			}
		}
	}

}