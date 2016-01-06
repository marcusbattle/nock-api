<?php

Class Nock_API_Messages {

	protected static $single_instance = null;

	static function init() {

		if ( self::$single_instance === null ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;

	}

	public function hooks() {

		add_action( 'init', array( $this, 'init_message_post_type' ) );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

	}

	/**
	 * Initialize the 'Message' Post Type
	 */
	public function init_message_post_type() {

	    $args = array(
			'public'		=> true,
			'label'		=> 'Messages',
			'menu_icon'	=> 'dashicons-format-status',
	    );

	    register_post_type( 'message', $args );

	}

	public function register_routes() {

	}

}

add_action( 'plugins_loaded', array( Nock_API_Messages::init(), 'hooks' ) );