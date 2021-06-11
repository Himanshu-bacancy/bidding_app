<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for Itemsizegroupoptions table
 */
class Itemsizegroupoptions extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct() 
	{
		parent::__construct( 'bs_item_sizegroupoptions', 'id', 'itm_sizeoption_' );
	}

}