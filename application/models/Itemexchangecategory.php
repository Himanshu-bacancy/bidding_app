<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for Itemexchangecategory table
 */
class Itemexchangecategory extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct() 
	{
		parent::__construct( 'bs_item_exchange', 'id', 'itm_exchang_cat_' );
	}

}