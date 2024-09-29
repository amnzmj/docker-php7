<?php

class Expedientes_Permissions{
	protected static $acl = null;
	
	protected static function setAcl(){
		$acl = new Zend_Acl();
		
		$guest = new Zend_Acl_Role( 'guest' );
		$admin = new Zend_Acl_Role( 'admin' ); 
		$user = new Zend_Acl_Role( 'user' );
		
		$acl->addRole( $guest )
			->addRole( $user, $guest )
			->addRole( $admin );
			
		$acl->addResource( new Zend_Acl_Resource('backend') )
			->addResource( new Zend_Acl_Resource('backend:index'), 'backend' )
			->addResource( new Zend_Acl_Resource('backend:login'), 'backend' )
			->addResource( new Zend_Acl_Resource('backend:error'), 'backend' )
			->addResource( new Zend_Acl_Resource('backend:aspirantes'), 'backend' )
			->addResource( new Zend_Acl_Resource('backend:documentos'), 'backend' )
			->addResource( new Zend_Acl_Resource('backend:expedientes'), 'backend' )
			->addResource( new Zend_Acl_Resource('backend:password'), 'backend' )
			->addResource( new Zend_Acl_Resource('backend:usuarios'), 'backend' )
			->addResource( new Zend_Acl_Resource('backend:convocatoria'), 'backend' );

		$acl->addResource( new Zend_Acl_Resource('frontend') )
			->addResource( new Zend_Acl_Resource('frontend:index'), 'frontend' )
			->addResource( new Zend_Acl_Resource('frontend:login'), 'frontend' )
			->addResource( new Zend_Acl_Resource('frontend:error'), 'frontend' )
			->addResource( new Zend_Acl_Resource('frontend:documentos'), 'frontend' )
			->addResource( new Zend_Acl_Resource('frontend:password'), 'frontend' )
			->addResource( new Zend_Acl_Resource('frontend:recuperar'), 'frontend' )
			->addResource( new Zend_Acl_Resource('frontend:registro'), 'frontend' );
			
		
		$acl->deny( $guest )
			->allow($guest, array('frontend', 'backend:index', 'backend:login', 'backend:error'))
			->deny( $guest, array('frontend:documentos', 'frontend:password') )
			->allow( $user, 'frontend:documentos' )
			->allow( $user, 'frontend:password' )
			->allow( $admin, 'backend' );
		
		self::$acl = $acl;
	}
	
	
	public static function getAcl(){
		
		if(self::$acl === null) self::setAcl();
		return self::$acl;
	
	
	}
}