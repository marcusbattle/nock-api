<?php

Class Social_API_Apps {

	protected static $single_instance = null;

	static function init() {

		if ( self::$single_instance === null ) {
			self::$single_instance = new self();
		} 

		return self::$single_instance;

	}

	public function __construct() {
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . '/includes/CMB2/init.php';

	}

	public function hooks() { 

		add_action( 'init', array( $this, 'register_app_post_type' ) );
		add_action( 'cmb2_admin_init', array( $this, 'init_app_credentials_metabox' ) );
		add_action( 'cmb2_render_consumer_key', array( $this, 'cmb2_render_consumer_key' ), 10, 5 );
		add_action( 'cmb2_render_consumer_secret', array( $this, 'cmb2_render_consumer_secret' ), 10, 5 );

	}

	/**
	 * Registers the 'App' post type
	 * 
	 * @return NULL
	 */
	public function register_app_post_type() {
		
		$args = array(
			'public' 	=> true,
			'label'  	=> 'Apps',
			'menu_icon' => 'dashicons-smartphone'
		);

		register_post_type( 'api_app', $args );

	}

	/**
	 * Initialize the 'App Credentials' metabox and fields
	 * @return [type] [description]
	 */
	public function init_app_credentials_metabox() {

		$prefix = '_api_app_';

		$credentials_box = new_cmb2_box( array(
			'id'            => $prefix . 'credentials_metabox',
			'title'         => __( 'App Credentials', 'cmb2' ),
			'object_types'  => array( 'api_app' ),
			'show_names' 	=> true,
		) );

		$credentials_box->add_field( array(
			'id'   => $prefix . 'consumer_key',
			'name' => __( 'Consumer Key', 'cmb2' ),
			'type' => 'consumer_key',
		) );

		$credentials_box->add_field( array(
			'id'   => $prefix . 'consumer_secret',
			'name' => __( 'Consumer Secret', 'cmb2' ),
			'type' => 'consumer_secret',
		) );

	}

	public function cmb2_render_consumer_key( $field, $escaped_value, $object_id, $object_type, $field_type_object ) { 

		if ( empty( $escaped_value ) ) {
			$escaped_value = $this->generate_random_string( 25 );
		}

		echo $field_type_object->input( array( 
			'type' 	=> 'text',
			'value'	=> $escaped_value
		) );

	}

	public function cmb2_render_consumer_secret( $field, $escaped_value, $object_id, $object_type, $field_type_object ) { 

		if ( empty( $escaped_value ) ) {
			$escaped_value = $this->generate_random_string( 50 );
		}

		echo $field_type_object->input( array( 
			'type' 	=> 'text',
			'value'	=> $escaped_value
		) );

	}

	/**
	 * Generates a random string
	 * 
	 * @param  integer $length
	 * @return string $random_string
	 */
	public function generate_random_string( $length = 10 ) {

		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $characters_length = strlen( $characters );
	    $random_string = '';
	    
	    for ( $i = 0; $i < $length; $i++ ) {
	        $random_string .= $characters[ rand( 0, $characters_length - 1 ) ];
	    }

	    return $random_string;

	}

	public function check_app_credentials( $key = '', $secret = '' ) {
		
		$args = array(
			'post_type' => 'api_app',
			'meta_query' => array(
				array(
					'key' => '_api_app_consumer_key',
					'value' => $key,
				),
				array(
					'key' => '_api_app_consumer_secret',
					'value' => $secret,
				)
			)
		);

		$apps = get_posts( $args );

		return $apps;
		
	}

}

add_action( 'plugins_loaded', array( Social_API_Apps::init(), 'hooks' ) );