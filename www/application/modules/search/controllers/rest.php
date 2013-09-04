<?php
class Rest extends REST_Controller {

	public function __construct() 
	{
		parent::__construct();

		$this->load->library( 'search' );

		// $this->load->config( 'search/search' );
	}

	public function index_get()
	{
		// set query criteria
		$query = $this->input->get( 'q' );

		// set limit
		$limit = $this->input->get( 'limit' );

		// load up search
		$this->load->library( 'zend', 'Zend/Search/Lucene' );
		$this->load->library( 'zend' );
		$this->zend->load( 'Zend/Search/Lucene' );

		// $index = new Zend_Search_Lucene( $this->config->item( 'index_path' ) );
		$index = new Zend_Search_Lucene( $this->search->_index_path );

		$results = $index->find( $query );

		if ( count( $results ) > 0 ) {
			$resultset = array();

			foreach ( $results AS $result ) {
				$resultset[] = array(
					'title'		=> $result->title,
					'url'		=> $result->url,
					'score'		=> sprintf( '%.2f', $result->score ),
					'preview'	=> $result->preview,
					'section'	=> $result->section
				);
			}
			$this->response( $resultset );
		}
		else {
			$this->response(array());
		}
	}
}