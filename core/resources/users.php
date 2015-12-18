<?php

Class Nock_API_Users {

	protected static $single_instance = null;

	static function init() {

		if ( self::$single_instance === null ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;

	}

	public function hooks() { }

}

add_action( 'plugins_loaded', array( Nock_API_Users::init(), 'hooks' ) );