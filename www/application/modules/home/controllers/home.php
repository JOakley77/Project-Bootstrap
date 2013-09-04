<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$this->template->title( $this->app_title, 'Welcome' );
		$this->template->build( 'home' );
	}
}

/* End of file home.php */
/* Location: ./application/modules/home/home.php */