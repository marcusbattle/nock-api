<?php

Class Nock_API_Apps {

	protected static $single_instance = null;

	static function init() {

		if ( self::$single_instance === null ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;

	}

	public function hooks() {

		add_action( 'init', array( $this, 'register_app_post_type' ) );
		add_action( 'cmb2_admin_init', array( $this, 'init_app_credentials_metabox' ) );
		add_action( 'cmb2_render_consumer_key', array( $this, 'cmb2_render_consumer_key' ), 10, 5 );
		add_action( 'cmb2_render_consumer_secret', array( $this, 'cmb2_render_consumer_secret' ), 10, 5 );

	}

	/**
	 * Registers the 'App' post type
	 */
	public function register_app_post_type() {

		$args = array(
			'public' 	=> true,
			'label'  	=> 'Apps',
			'menu_icon' => 'dashicons-smartphone'
		);

		register_post_type( 'nock-app', $args );

	}

	/**
	 * Initialize the 'App Credentials' metabox and fields
	 */
	public function init_app_credentials_metabox() {

		$prefix = '_nock_app_';

		$credentials_box = new_cmb2_box( array(
			'id'            => $prefix . 'credentials_metabox',
			'title'         => __( 'App Credentials', 'nock' ),
			'object_types'  => array( 'nock-app' ),
			'show_names' 	=> true,
		) );

		$credentials_box->add_field( array(
			'id'   => $prefix . 'consumer_key',
			'name' => __( 'Consumer Key', 'nock' ),
			'type' => 'consumer_key',
		) );

		$credentials_box->add_field( array(
			'id'   => $prefix . 'consumer_secret',
			'name' => __( 'Consumer Secret', 'nock' ),
			'type' => 'consumer_secret',
		) );

	}

	public function cmb2_render_consumer_key( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {

		if ( empty( $escaped_value ) ) {
			$escaped_value = nock_generate_random_string( 25 );
		}

		echo $field_type_object->input( array(
			'type' 	=> 'text',
			'value'	=> $escaped_value
		) );

	}

	public function cmb2_render_consumer_secret( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {

		if ( empty( $escaped_value ) ) {
			$escaped_value = nock_generate_random_string( 50 );
		}

		echo $field_type_object->input( array(
			'type' 	=> 'text',
			'value'	=> $escaped_value
		) );

	}

}

add_action( 'plugins_loaded', array( Nock_API_Apps::init(), 'hooks' ) );