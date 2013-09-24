<?php

// set character encoding
$config[ 'character_encoding' ] = 'utf-8';

// the index path relative to the application root
$config[ 'index_path' ] 		= 'search';

// page crawl timeout
$config[ 'timeout' ] 			= 30;

// create a seperate indexing log
$config[ 'log_requests' ]		= TRUE;

// clear log before starting new index
$config[ 'clear_log_before_new_index' ] = TRUE;

// indexer log path
$config[ 'log_path' ]			= 'logs';

// index log file name
$config[ 'log_name' ]			= 'search_index-' . date( 'Y-m-d' ) . '.log';