<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for Itemsimilarcreteria table
 */
class Itemsimilarcreteria extends PS_Model {

	/**
	 * Constructs the required data
	 */
	function __construct() 
	{
		parent::__construct( 'bs_item_similarcreteria', 'id', 'itm_similar_' );
	}

}