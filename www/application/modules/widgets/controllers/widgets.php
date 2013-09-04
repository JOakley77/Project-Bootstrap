<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Widgets extends Widgets_Controller {

	protected $path;
	protected $widget;

	public function __construct()
	{
		parent::__construct();
	}

	public function run( $file )
	{        
        $args		= func_get_args();

        $module 	= '';
        $folder 	= '';
        $class_name = '';

        /* is module in filename? */
        if ( ( $pos = strrpos( $file, '/' ) ) !== FALSE ) {
            $module 	= substr( $file, 0, $pos );
            $file		= substr( $file, $pos + 1 );
            $folder		= strtolower( $file );
            $class_name	= 'Widget_' . ucfirst( $file );
        }

        list( $path, $file ) = Modules::find( $file, $module, 'widgets/' . $folder . '/' );

        if ( $path === FALSE ) {
            $path = APPPATH . 'widgets/';
        }

        Modules::load_file( $file, $path );

        $widget = new $class_name();

        $widget->module_path = $path;

        return call_user_func_array( array( $widget, 'run' ), array_slice( $args, 1 ) );    
    }

    public function render( $view, $data = array() )
    {
        extract( $data );
        include $this->module_path . 'views/' . $view . EXT;
    }

    public function load( $object )
    {
        $this->$object = load_class( ucfirst( $object ) );
    }

    public function __get( $var )
    {
        global $CI;
        return $CI->$var;
    }
}