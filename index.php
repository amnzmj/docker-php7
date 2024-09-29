<?php
	//header("Location: http://expedientes.omega-digital.com/"); exit;
	
	
	 
	defined('APPLICATION_ENV')
		|| define('APPLICATION_ENV',
			(getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

	defined('APPLICATION_PATH')
		|| define('APPLICATION_PATH',
			realpath(dirname(__FILE__) . '/application') );

	defined('PUBLIC_PATH')
		|| define('PUBLIC_PATH',
			realpath(dirname(__FILE__) ) );	
			
	set_include_path( implode( PATH_SEPARATOR, array(
		
		APPLICATION_PATH,
		realpath(APPLICATION_PATH . "/../library"),
		get_include_path()
	)));
	
	require("Autoloader.php");
	
	
	
	$app = new Expedientes_Application();
	define('__BASEURL__', $app->getConfig()->application->baseurl );	
	$app->run();