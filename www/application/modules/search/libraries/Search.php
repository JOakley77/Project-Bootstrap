<?php

class Search {
	
	public $CI;

	public function __construct()
	{
		$this->CI =& get_instance();

		$this->CI->load->library( 'zend', 'Zend/Feed' );
		$this->CI->load->library( 'zend', 'Zend/Http/Client' );
		$this->CI->load->library( 'zend', 'Zend/Dom/Query' );
		$this->CI->load->library( 'zend', 'Zend/Search/Lucene' );
		$this->CI->load->library( 'zend' );

		$this->CI->zend->load( 'Zend/Feed' );
		$this->CI->zend->load( 'Zend/Http/Client' );
		$this->CI->zend->load( 'Zend/Dom/Query' );
		$this->CI->zend->load( 'Zend/Search/Lucene' );

		// load settings from db
		$this->settings();

		// mutate some of them settings
		$this->_index_path = FCPATH . APPPATH . $this->_index_path;

		// set encoding
		Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding( $this->_character_encoding );
		Zend_Search_Lucene_Analysis_Analyzer::setDefault(
			new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive()
		);

		$this->connect();
	}

	protected function connect()
	{
		// try to open the index
		try {
			$this->index = Zend_Search_Lucene::open( $this->_index_path );
		}
		// can't open so try to create
		catch( Zend_Search_Lucene_Exception $e ) {
			try {
				$this->index = Zend_Search_Lucene::create( $this->_index_path );
			}
			catch( Zend_Search_Lucene_Exception $e ) {
				show_error( 'Unable to open or create the index: ' . $e->getMessage() );
			}
		}
	}

	public function index_pages()
	{
		$pages = explode( ',', $this->_pages_to_index );

		if ( is_array( $pages ) AND count( $pages ) > 0 ) {

			$client = new Zend_Http_Client(); 
			$client->setConfig(
				array(
					'timeout' => $this->_timeout
				)
			);

			foreach ( $pages AS $page ) {
				$meta_title	= array();
				$page_content	= array();

				$uri = base_url() . $page;

				$client->setUri( $uri );
				$response = $client->request(); 

				if ( $response->isSuccessful() ) {
					$html			= $response->getBody();
					$page_title		= $this->_query_html( $html, '//head/title' );
					$page_content	= $this->_query_html( $html, '//section' );
					$doc 			= Zend_Search_Lucene_Document_Html::loadHTML( $html );

					$doc->addField( Zend_Search_Lucene_Field::UnIndexed( 'url', $uri ) );
					$doc->addField( Zend_Search_Lucene_Field::Text( 'title', $page_title ) );
					$doc->addField( Zend_Search_Lucene_Field::UnStored( 'content', $page_content ) );
					$doc->addField( Zend_Search_Lucene_Field::UnIndexed( 'preview', $this->_set_preview( $page_content ) ) );
					$doc->addField( Zend_Search_Lucene_Field::UnIndexed( 'section', 'Page' ) );
					
					$this->index->addDocument( $doc );

					$this->CI->db->insert( 'search_log', array(
						'title'		=> $page_title,
						'action'	=> $uri,
						'time'		=> time(),
						'status'	=> TRUE
					));
				}
				// couldn't get he body
				else {
					$this->CI->db->insert( 'search_log', array(
						'title'		=> $page_title,
						'action'	=> $uri,
						'time'		=> time(),
						'status'	=> FALSE
					));
				}
				flush();
			}
		}
	}

	public function index_feeds()
	{
		$feeds = explode( ',', $this->_feeds );

		// grab each feed
		foreach ( $feeds AS $feed ) {
			$channel = Zend_Feed::import( trim( $feed ) );
			
			// index each item
			foreach ( $channel->items AS $item ) {
				if ( $item->link() AND $item->title() AND $item->description() ) {

					// add data
					$doc = new Zend_Search_Lucene_Document();
					$doc->addField( Zend_Search_Lucene_Field::Keyword( 'link', $item->link() ) );
					$doc->addField( Zend_Search_Lucene_Field::Text( 'title', $item->title() ) );
					$doc->addField( Zend_Search_Lucene_Field::Unstored( 'contents', $item->description() ) );

					$this->CI->db->insert( 'search_log', array(
						'title'		=> $item->title(),
						'action'	=> $item->link(),
						'time'		=> time(),
						'status'	=> TRUE
					));

					// set index
					$this->index->addDocument( $doc );
				}
			}
		}
	}

	protected function settings()
	{
		$query_settings = $this->CI->db->get( 'search_settings' );

		if ( $query_settings->num_rows() > 0 ) {
			foreach ( $query_settings->result() AS $row ) {
				$this->{"_".$row->key} = $row->value;
			}
		}
	}

	private function _query_html( $html, $query_str )
	{
		// init array
		$results	= array();

		// create dom obj
		$dom		= new Zend_Dom_Query( $html );

		// set queries
		$query		= $dom->queryXpath( $query_str );

		// get meta title from query
		foreach ( $query AS $result ) {
			$results[] = $result->nodeValue;
		}
		return ( string ) array_shift( $results );
	}

	private function _set_preview( $data, $limit = 400 )
	{
		return substr( $data, 0, $limit );
	}
}