<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for Itemcolors table
 */
class Itemcolors extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct() 
	{
		parent::__construct( 'bs_item_colors', 'id', 'itmcolr_' );
	}

}