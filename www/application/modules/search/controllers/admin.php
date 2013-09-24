<?php

class Admin extends MY_Controller {

	public $vars;
	
	public function __construct()
	{
		parent::__construct();

		$this->vars = array();
		$this->load->helper( 'text' );
		$this->load->library( 'search' );
	}

	public function index()
	{

		// start with a fresh index
		// i know there's a better way to do this but right now I'm tired and don't
		// feel like making it work "the right way"
		$this->search->delete_index();

		// index these pages on the site
		$this->search->index_pages(
			array( 
				'board', 
				'membership', 
				'contact' 
			)
		);

		// index this database table
		// this is quite a bit of setup
		// so some time in the future it would be
		// nice to add a bit more automation to the library
		// as this will suck to do on each table needed
		$this->search->index_table( 
			
			// database table to crawl
			'documents', 
			
			// multidimensional array of fields:
			// - key 	= database field
			// - set_as = index key to associate with
			// - type	= index type
			array( 
				'title'	=> array(
					'key'		=> 'title',
					'set_as'	=> 'title',
					'type'		=> 'Text'
				), 
				'desc'	=> array(
					'key'		=> 'desc',
					'set_as'	=> 'contents',
					'type'		=> 'Unstored'
				), 
				'file'	=> array(
					'key'		=> 'file',
					'set_as'	=> 'file',
					'type'		=> 'UnIndexed'
				)
			),

			// meta data to add to index
			array(

				// needs a URL to send users to when matched
				'url'	=> array(
					'key'		=> 'url',
					'set_as'	=> 'url',
					'type'		=> 'UnIndexed',
					'pattern'	=> array( base_url() . 'documents/%s/%s', array( 'file', 'id' ) )
				),

				// optional group to add the index results to
				'group'	=> array(
					'key'		=> 'documents'
				)
			) 
		);

		$this->search->index_table( 
			
			// database table to crawl
			'events', 
			
			// multidimensional array of fields:
			// - key 	= database field
			// - set_as = index key to associate with
			// - type	= index type
			array( 
				'title'	=> array(
					'key'		=> 'title',
					'set_as'	=> 'title',
					'type'		=> 'Text'
				), 
				'where'	=> array(
					'key'		=> 'where',
					'set_as'	=> 'contents',
					'type'		=> 'Unstored'
				), 
				'link'	=> array(
					'key'		=> 'link',
					'set_as'	=> 'link',
					'type'		=> 'UnIndexed'
				), 
				'start'	=> array(
					'key'		=> 'start',
					'set_as'	=> 'start',
					'type'		=> 'UnIndexed'
				), 
				'end'	=> array(
					'key'		=> 'end',
					'set_as'	=> 'end',
					'type'		=> 'UnIndexed'
				)
			),

			// meta data to add to index
			array(

				// needs a URL to send users to when matched
				'url'	=> array(
					'key'		=> 'url',
					'set_as'	=> 'url',
					'type'		=> 'UnIndexed',
					'pattern'	=> array( base_url() . 'events/%s', array( 'title' ) )
				),

				// optional group to add the index results to
				'group'	=> array(
					'key'		=> 'events'
				)
			) 
		);
	}

	public function all()
	{
		header('Content-type: text/json');
		header('Content-type: application/json');
		
		$this->truncate_log();
		$this->search->index_pages();
		// $this->search->index_feeds();
		$this->search->index_db();

		exit;
	}

	public function pages()
	{
		header('Content-type: text/json');
		header('Content-type: application/json');
		
		$this->truncate_log();
		$this->search->index_pages();

		exit;
	}

	// public function feeds()
	// {
	// 	header('Content-type: text/json');
	// 	header('Content-type: application/json');
		
	// 	$this->truncate_log();
	// 	$this->search->index_feeds();

	// 	exit;
	// }

	public function db()
	{
		header('Content-type: text/json');
		header('Content-type: application/json');
		
		$this->truncate_log();
		$this->search->index_db();

		exit;
	}

	public function get_status()
	{
		header('Content-type: text/json');
		header('Content-type: application/json');
		
		$query = $this->db->get( 'search_log' );

		if ( $query->num_rows() > 0 ) {
			foreach ( $query->result() AS $row ) {
				echo '<tr>';
				echo '<td title="' . $row->action . '">' . $row->title . '</td>';
				echo '<td align="center" class="' . ( $row->status ? 'success' : 'fail' ) . '"><span class="glyphicon glyphicon-' . ( $row->status ? 'plus-sign' : 'remove-circle' ) . '"></span></td>';
				echo '</tr>';
			}
		}
		exit;
	}

	public function truncate_log()
	{
		$this->db->truncate( 'search_log' );
	}
}