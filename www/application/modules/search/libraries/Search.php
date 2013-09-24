<?php

class Search {
	
	public $CI;

	// Lucene index stores
	protected $_index_types	= array(
		'Text', 'UnIndexed', 'Unstored'
	);

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

		// if set to clear before each index
		if ( $this->_clear_log_before_new_index === TRUE AND file_exists( FCPATH . APPPATH . $this->_log_path . '/' . $this->_log_name ) ) {
			unlink( FCPATH . APPPATH . $this->_log_path . '/' . $this->_log_name );
		}

		// setup search logs
		$this->log();

		// LOG > initialization
		$this->log( 'info', 'Search Indexing initialized' );

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
			$this->log( 'info', 'Index opened' );
		}
		// can't open so try to create
		catch( Zend_Search_Lucene_Exception $e ) {
			try {
				$this->log( 'info', 'New Index created' );
				$this->index = Zend_Search_Lucene::create( $this->_index_path );
			}
			catch( Zend_Search_Lucene_Exception $e ) {
				$this->log( 'error', 'Error creating index (' . $e->getMessage() . ')' );
				show_error( 'Unable to open or create the index: ' . $e->getMessage() );
			}
		}
	}

	public function index_pages( $pages )
	{
		if ( ! is_array( $pages ) OR count( $pages ) <= 0 ) 
			return;

		$this->log( null, 'break' );
		$this->log( 'info', 'Initializing page index ' );

		$client = new Zend_Http_Client(); 
		$client->setConfig(
			array(
				'timeout' => $this->_timeout
			)
		);

		foreach ( $pages AS $page ) {

			$this->log( 'info', '---------------------------------------' );
			$this->log( 'info', 'Starting page indexing ' . $page );
			$this->log( 'info', '=======================================' );

			$meta_title		= array();
			$page_content	= array();

			$uri 			= base_url() . $page;

			$client->setUri( $uri );
			$response = $client->request(); 

			if ( $response->isSuccessful() ) {
				
				// set objects
				$html			= $response->getBody();
				$page_title		= $this->_query_html( $html, '//head/title' );
				$page_content	= $this->_query_html( $html, '//section' );
				$doc 			= Zend_Search_Lucene_Document_Html::loadHTML( $html );

				// remove any existing entries found for this record since Lucene doesn't autoupdate
				$hits = $this->index->find( 'title:' . $page_title );

				foreach ($hits as $hit) {
					$this->index->delete( $hit->id );
				}

				// add fields to index
				$doc->addField( Zend_Search_Lucene_Field::UnIndexed( 'url', $uri ) );
				$doc->addField( Zend_Search_Lucene_Field::Text( 'title', $page_title ) );
				$doc->addField( Zend_Search_Lucene_Field::UnStored( 'content', $page_content ) );
				$doc->addField( Zend_Search_Lucene_Field::UnIndexed( 'preview', $this->_set_preview( $page_content ) ) );
				$doc->addField( Zend_Search_Lucene_Field::UnIndexed( 'group', 'Page' ) );
				
				$this->index->addDocument( $doc );

				// log success msg
				$this->log( 'info', 'Indexed: ' . $page_title );
				$this->log( NULL, 'break' );
			}

			// couldn't get the body
			else {
				$this->log( 'warn', 'Failed to Index: ' . $page_title );
			}
		}

		$this->log( 'info', '---------------------------------------' );
		$this->log( 'info', 'Ending page indexing ' . $page );

		// optimize the index after completion
		$this->index->optimize();
		$this->log( 'info', 'Optimized index' );
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

					// remove any existing entries found for this record since Lucene doesn't autoupdate
					$hits = $this->index->find( 'title:' . $page_title );

					foreach ($hits as $hit) {
						$this->index->delete( $hit->id );
					}
					
					$this->index->addDocument( $doc );

					// add data
					$doc = new Zend_Search_Lucene_Document();
					$doc->addField( Zend_Search_Lucene_Field::Keyword( 'link', $item->link() ) );
					$doc->addField( Zend_Search_Lucene_Field::Text( 'title', $item->title() ) );
					$doc->addField( Zend_Search_Lucene_Field::Unstored( 'contents', $item->description() ) );

					// set log msg
					$this->log( 'info', 'Indexed: ' . $item->title() );

					// set index
					$this->index->addDocument( $doc );
				}
			}
		}

		// optimize the index after completion
		$this->index->optimize();

		$this->log( 'info', 'Optimized index' );
		$this->log( 'info', 'Feed indexing complete' );
	}

	public function index_table( $table, $fields = NULL, $meta_data = NULL )
	{
		$this->CI->load->dbutil();

		$_table = ( string ) preg_replace( '/[^\w\d\-\_]/', '', $table );

		$this->log( NULL, 'break' );
		$this->log( 'info', '---------------------------------------' );
		$this->log( 'info', 'Starting table index for ' . $table );
		$this->log( 'info', '=======================================' );

		if ( $this->CI->db->table_exists( $_table ) ) {
			
			// init for mapping the requested fields to 
			// what we've found in the database
			$fields_map			= array();

			// store a list of columns to select
			$_query_select		= array();
			
			// get a list of which fields we have in the database to compare
			// what was requested with what was found
			$fields_in_table 	= $this->CI->db->list_fields( $_table );
			$fields_meta		= $this->CI->db->field_data( $_table );

			// if fields are requested we should validate the existence + content type
			if ( ! is_null( $fields ) AND is_array( $fields ) AND count( $fields ) > 0 ) {
				
				// looking for a very specific array type so if at any point information is missing
				// we should error out.
				foreach ( $fields AS $key => $field ) {
					
					// make sure we have those magic keys
					if ( ! isset( $field[ 'key' ] ) OR ! isset( $field[ 'set_as' ] ) OR ! isset( $field[ 'type' ] ) )
						return;

					if ( in_array( $field[ 'key' ], $fields_in_table ) ) {

						// make sure the requested _type is valid
						if ( ! in_array( $field[ 'type' ], $this->_index_types ) )
							return;

						// sanitize the _set_as key
						$field[ 'set_as' ] = ( string ) preg_replace( '/[^\w\d]/', '_', $field[ 'set_as' ] );

						// clone current array key into the map
						$fields_map[ $field[ 'key' ] ] = $field;

						// push the select field
						array_push( $_query_select, $field[ 'key' ] );
					}
				}
			}

			// had plans to guess storage types based on column type but 
			// now I don't want to...at the moment.
			else {
				$this->log( 'error', 'Missing field array map' );
				show_error( 'Missing field array map' );
				return;
			}

			// let's query the database for the requested fields
			$query = $this->CI->db->select( implode( ',', $_query_select ) )->get( $_table );

			// make sure we have some rows and get cracking on indexing this stuff
			if ( $query->num_rows() > 0 ) {

				// last bit of redundancy to do for now until I get this fixed up a bit
				// and that is store all of the results in a new array with a compiled
				// list of data
				$data_to_index 	= new stdClass;

				$results 		= $query->result();

				foreach ( $results AS $i => $row ) {
					
					// initialize the new document
					if ( ! isset( $data_to_index->{ $i } ) ) {
						$data_to_index->{ $i } 			= new stdClass;
						$data_to_index->{ $i }->index 	= new Zend_Search_Lucene_Document();
					}

					foreach ( $row AS $key => $v ) {

						/* [ OBJECT TO INDEX ]
						============================= */

						// set object key
						if ( ! isset( $data_to_index->{ $i } ) )
							$data_to_index->{ $i }->{ $fields_map[ $key ][ 'key' ] } = new stdClass;

						// set index
						$data_to_index->{ $i }->index->addField( 
							Zend_Search_Lucene_Field::{ $fields_map[ $key ][ 'type' ] }( 
								$fields_map[ $key ][ 'set_as' ], 
								$results[ $i ]->{ $key }
							) 
						);

						$this->log( 'info', 'Indexed: [Table:' . $_table . '] [Field:' . $fields_map[ $key ][ 'key' ]  . "]	-  " . $results[ $i ]->{ $key } );
					}

					/* [ META : URL ]
					============================= */
					if ( isset( $meta_data[ 'url' ] ) ) {

						// init matches array
						$url_pattern_matches = array();

						foreach ( $meta_data[ 'url' ][ 'pattern' ][ 1 ] AS $url_pattern ) {
							if ( isset( $results[ $i ]->{ $url_pattern } ) )
								array_push( $url_pattern_matches, $results[ $i ]->{ $url_pattern } );
							else
								array_push( $url_pattern_matches, '' );
						}

						// @TODO > add method to validate these and lose some of this damn redundancy!
						// make sure the array element validates
						if ( ! isset( $meta_data[ 'url' ][ 'key' ] ) OR ! isset( $meta_data[ 'url' ][ 'set_as' ] ) OR ! isset( $meta_data[ 'url' ][ 'type' ] ) )
							continue;
						else {
							$data_to_index->{ $i }->index->addField( Zend_Search_Lucene_Field::{ $meta_data[ 'url' ][ 'type' ] }( 
								$meta_data[ 'url' ][ 'set_as' ], 
								vsprintf( $meta_data[ 'url' ][ 'pattern' ][ 0 ], $url_pattern_matches )
							) );

							// set log
							$this->log( 'info', 'Indexed: [Table:' . $_table . "] [Field:URL]\t-  " . vsprintf( $meta_data[ 'url' ][ 'pattern' ][ 0 ], $url_pattern_matches ) );
						}
					}

					/* [ META : GROUP ]
					============================= */
					if ( isset( $meta_data[ 'group' ] ) ) {

						// only requirement is to match `key`
						if ( ! isset( $meta_data[ 'group' ][ 'key' ] ) )
							return;
						else {
							$data_to_index->{ $i }->index->addField( Zend_Search_Lucene_Field::UnIndexed( 'group', $meta_data[ 'group' ][ 'key' ] ) );

							// set log
							$this->log( 'info', 'Indexed: [Table:' . $_table . '] [Group:' . $meta_data[ 'group' ][ 'key' ]  . ']' );
						}
					}

					// add document to index
					$this->index->addDocument( $data_to_index->{ $i }->index );

					$this->log( NULL, 'break' );
				}
			}
			else {
				$this->log( 'error', 'No database results while querying ' . $_table );
			}

			// optimize the index after completion
			$this->index->optimize();

			$this->log( 'info', 'Optimized index' );
			$this->log( 'info', 'Table [' . $_table . '] indexing complete' );

			$this->log( 'info', '---------------------------------------' );
		}
		else {
			$this->log( 'error', 'Table: ' . $table . ' does not exist' );
			show_error( 'Table: ' . $table . ' does not exist' );
		}
	}

	public function delete_index()
	{
		$index = glob( $this->_index_path . '/*' );

		foreach ( $index AS $file ) {
			unlink( $file );
		}

		$this->log( 'warn', 'Deleted the current index' );

		$this->connect();
	}

	private function log( $group = NULL, $msg = NULL )
	{
		// initialize the log when no options are set
		if ( $this->_log_requests === TRUE ) {
			$log_file = FCPATH . APPPATH . $this->_log_path . '/' . $this->_log_name;

			if ( file_exists( $log_file ) AND is_writable( $log_file ) ) {
				$this->log_file = fopen( $log_file, 'a' );
			}
			else {
				if ( ! $this->log_file = fopen( $log_file, 'a' ) ) {
					show_error( 'Cannot open log file for writing' );
				}
			}
		}

		// write to log file
		if ( ! $this->log_file )
			show_error( 'Log file is corrupt. Please figure that out!' );
		else {
			if ( ! is_null( $msg ) AND $this->_log_requests === TRUE ) {
				if ( $msg == 'break' )
					fwrite( $this->log_file, "\n" );
				else
					fwrite( $this->log_file, '[' . strtoupper( $group ) . '][' . date( 'm-d-y h:i:s' ) . '] : ' . $msg . "\n" );
			}
		}

		fclose( $this->log_file );
	}

	private function settings()
	{
		$settings = $this->CI->load->config( 'search', TRUE, TRUE );

		foreach ( $settings AS $key => $value ) {
			$this->{"_".$key} = $value;
		}

		$this->log( 'info', 'Search indexing configuration completed' );
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