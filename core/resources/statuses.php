<?php

Class Nock_API_Statuses {

	protected static $single_instance = null;

	static function init() {

		if ( self::$single_instance === null ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;

	}

	public function hooks() {

		add_action( 'init', array( $this, 'init_status_post_type' ) );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

	}

	/**
	 * Initialize the 'Status' Post Type
	 */
	public function init_status_post_type() {

	    $args = array(
			'public'		=> true,
			'label'		=> 'Statuses',
			'menu_icon'	=> 'dashicons-format-status',
	    );

	    register_post_type( 'status', $args );

	}

	public function register_routes() {

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

	}

	/**
	 * GET all statuses
	 */
	public function GET_statuses( $data ) {

		$access_token = $data->get_param('access_token');

		// Check to see whether or not the access token is valid
		if ( ! $session = nock_access_token_is_valid( $access_token ) ) {

			return array(
				'error'		=> '',
				'message'	=> '',
				'code'		=> ''
			);

		}

		$args = array(
			'post_type' => 'status',
			'post_status' => array( 'publish', 'private' ),
			'posts_per_page' => 12,
			'author' => $session['user_id']
		);

		$statuses = get_posts( $args );

		foreach ( $statuses as $index => $status ) {

			// $statuses[ $index ]->post_content = wpautop( $status->post_content );
			$images = get_attached_media( 'image', $status->ID );
			$image_url = '';
			$image_html = '';

			foreach ( $images as $image ) {
				$image_url = wp_get_attachment_url( $image->ID );
			}

			$statuses[ $index ]->image_url = $image_url;
			$statuses[ $index ]->link = "/status/{$status->ID}";

		}

	    return $statuses;

	}

	/**
	 * GET a single status
	 */
	public function GET_status( $data ) {

		$status_id = $data->get_param('id');

		$status = get_post( $status_id, ARRAY_A );

		$args = array(
			'post_id' => $status_id
		);

		$status['comments'] = get_comments( $args );

		return $status;

	}

}

add_action( 'plugins_loaded', array( Nock_API_Statuses::init(), 'hooks' ) );