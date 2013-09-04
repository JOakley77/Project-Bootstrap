<?php
class MY_Controller extends MX_Controller {

	public $app_title;
	public $active_segment;

	public $user;

	public function __construct() 
	{
		// site some global objects
		$this->app_title 		= $this->config->item( 'app_title' );
		$this->active_segment 	= $this->get_active_page();

		// template configuration
		$this->template
			->set_layout( 'default' )
			->set_partial( 'meta_header', 'partials/meta_header' )
			->set_partial( 'meta_footer', 'partials/meta_footer' )
			->set_partial( 'header'		, 'partials/header' )
			->set_partial( 'footer'		, 'partials/footer' );

		// template variables
		$this->template
			->set( 'base_url'		, base_url() )
			->set( 'app_title'		, $this->app_title )
			->set( 'active_segment'	, $this->active_segment );

		$this->get_active_page();
	}

	public function get_active_page()
	{
		$active_segment = ( string ) $this->uri->segment( 1 );
		return url_title( $active_segment );
	}

	public static function object_to_array($d) {
		if (is_object($d))
			$d = get_object_vars($d);

		return is_array($d) ? array_map(__METHOD__, $d) : $d;
	}

	public static function array_to_object($d) {
		return is_array($d) ? (object) array_map(__METHOD__, $d) : $d;
	}
}