<?php
/*
Plugin Name: Social API
Version: 0.1.0
Author: Marcus Battle
Description: Membership list. A way to track members. The aggregate of people living together in a more or less ordered community.
*/

class Social_API {

	protected static $single_instance = null;

	static function init() {

		if ( self::$single_instance === null ) {
			self::$single_instance = new self();
		} 

		return self::$single_instance;

	}

	public function __construct() { }

	public function hooks() { 
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {

		// Users
		register_rest_route( 'social-api/v1', '/users', array(
	        'methods' => 'GET',
	        'callback' => array( $this, 'GET_users' ),
	    ) );

		// GET Status (All)
		register_rest_route( 'social-api/v1', '/statuses', array(
	        'methods' => 'GET',
	        'callback' => array( $this, 'GET_statuses' ),
	    ) );

		// GET Status (Specific User)
		register_rest_route( 'social-api/v1', '/statuses/(?P<id>\d+)', array(
	        'methods' => 'GET',
	        'callback' => array( $this, 'GET_status' ),
	    ) );

		// GET Status (All)
		register_rest_route( 'social-api/v1', '/groups', array(
	        'methods' => 'GET',
	        'callback' => array( $this, 'GET_groups' ),
	    ) );

	}

	public function GET_users( $data ) {

		return $data;

	}

	/** 
	 * Get all statuses
	 */
	public function GET_statuses( $data ) {
		
		$args = array(
			'post_type' => 'status',
			'post_status' => array( 'publish', 'private' )
		);

		$statuses = get_posts( $args );

		foreach ( $statuses as $index => $status ) {

			// $statuses[ $index ]->post_content = wpautop( $status->post_content );

		}

	    return $statuses;

	}

	public function GET_status( $data ) {

		$status_id = $data->get_param('id');

		$status = get_post( $status_id, ARRAY_A );

		return $status;
		
	}

	public function GET_groups( $data ) {

		$groups = array(
			array(
				'id' 			=> 1,
				'name' 			=> 'Family',
				'description' 	=> 'Where the Battle Family lives',
			),
			array(
				'id' 			=> 2,
				'name' 			=> 'The Summit',
				'description' 	=> '',
			),
			array(
				'id' 			=> 3,
				'name' 			=> 'Connect50',
				'description' 	=> '',
			)
		);

		return $groups;

	}

}

add_action( 'plugins_loaded', array( Social_API::init(), 'hooks' ) );


