<?php

	class URLRouter{
		
		public static function getRoute($options = array()){
			$config = Application::getConfig();
			$url = $config->application->defaults->baseURL;
			
			if(isset($options['controller']))
				$url .= $options['controller'];
			if(isset($options['action']))
				$url .= '/' . $options['action'];
			
			if(isset($options['params'])){
				$url .= '/?' . http_build_query ( $options['params'] );
			}
			
			return $url;
		}
	}