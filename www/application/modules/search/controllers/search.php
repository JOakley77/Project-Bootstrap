<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$this->template->title( $this->app_title, 'Search' );
		$this->template->build( 'search' );
	}
}

/* End of file home.php */
/* Location: ./application/modules/home/home.php */