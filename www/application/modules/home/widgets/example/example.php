<?php

class Widget_Example extends Widgets {
	
	public function run( $options = NULL )
	{
		$data = array(
			'Record 1', 'Record 2', 'Record 3'
		);

		if ( is_string( $options ) )
			$label = $options;
		else
			$label = 'Default Label';

		$this->render( 'example', array(
			'label'	=> $label,
			'data'	=> $data 
		) );
	}
}