<?php

	function __AutoPilotLoader($className) {
		require str_replace('_', '/', $className) . '.php';
	}
	
	function __AutoPilot_unregister(){
		spl_autoload_unregister('__AutoPilotLoader');
	}
	spl_autoload_register('__AutoPilotLoader');
