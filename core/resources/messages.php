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

		add_filter( 'manage_message_posts_columns', array( $this, 'message_column_headers' ) );
		add_action( 'manage_message_posts_custom_column', array( $this, 'message_columns' ), 10, 2 );

	}

	/**
	 * Initialize the 'Message' Post Type
	 */
	public function init_message_post_type() {

	    $args = array(
			'public'		=> true,
			'label'		=> 'Messages',
			'menu_icon'	=> 'dashicons-testimonial',
	    );

	    register_post_type( 'message', $args );

	}

	public function register_routes() {

	}

	public function message_column_headers( $columns ) {

		unset( $columns['date'] );

		$columns['title'] = 'Message';
		$columns['from'] = 'From';
		$columns['to'] = 'To';
		$columns['date'] = 'Date';

	    return $columns;

	}

	public function message_columns( $column, $message_id ) {

		switch ( $column ) {

			case 'to':
				echo get_post_meta( $message_id, 'message_to', true );
				break;

			case 'from':
				echo get_post_meta( $message_id, 'message_from', true );
				break;

		}

	}

}

add_action( 'plugins_loaded', array( Nock_API_Messages::init(), 'hooks' ) );