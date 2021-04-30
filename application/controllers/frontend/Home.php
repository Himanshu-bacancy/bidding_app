<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Front End Controller
 */
class Home extends FE_Controller 
{

	/**
	 * constructs required variables
	 */
	function __construct()
	{
		parent::__construct( NO_AUTH_CONTROL, 'HOME' );

	}

	/**
	 * Home Page
	 */
	function privacy_policy()
	{
		$content = $this->Privacy_policy->get_one('privacy1')->content;
		$this->data['content'] = $content;
		$this->load_template( 'privacy_policy' );
	}

	/** for user data deletion */
	function data_deletion_policy()
	{
		$content = $this->Data_deletion->get_one('datadelete1')->content;
		$this->data['content'] = $content;
		$this->load_template( 'data_deletion_policy' );
	}
}