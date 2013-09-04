<?php

function set_active( $needle, $haystack, $dothis = NULL )
{
	var_dump( $needle );
	if ( $needle == $haystack ) return $dothis;
}