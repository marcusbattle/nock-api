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
		add_action( 'init', array( $this, 'init_mention_taxonomy' ) );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		add_action( 'save_post', array( $this, 'after_insert_update_status' ), 20, 2 );

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

	public function init_mention_taxonomy() {

		$labels = array(
			'name'              => _x( 'Mentions', 'taxonomy general name' ),
			'singular_name'     => _x( 'mention', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Mentions' ),
			'all_items'         => __( 'All Mentions' ),
			'parent_item'       => __( 'Parent Mention' ),
			'parent_item_colon' => __( 'Parent Mention:' ),
			'edit_item'         => __( 'Edit Mention' ),
			'update_item'       => __( 'Update Mention' ),
			'add_new_item'      => __( 'Add New Mention' ),
			'new_item_name'     => __( 'New Mention Name' ),
			'menu_name'         => __( 'Mentions' ),
		);

		$args = array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
		);

		register_taxonomy(
			'mention',
			'status',
			$args
		);

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

		// POST Status
		register_rest_route( 'social-api/v1', '/statuses/new', array(
	        'methods' => 'POST',
	        'callback' => array( $this, 'POST_status' ),
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

	public function POST_status( $data ) {

		$access_token = $data->get_param('access_token');
		$status_content = $data->get_param('status');

		// Check to see whether or not the access token is valid
		if ( ! $session = nock_access_token_is_valid( $access_token ) ) {

			return array(
				'error'		=> '',
				'message'	=> '',
				'code'		=> ''
			);

		}

		$args = array(
			'post_type' 	=> 'status',
			'post_status' 	=> 'publish',
			'post_content'	=> $status_content,
			'post_author'	=> $session['user_id']
		);

		$status_id = wp_insert_post( $args );

		if ( is_wp_error( $status_id ) ) {

		}

		$this->after_insert_update_status( $status_id, $args );

		return $status_id;

	}

	public function after_insert_update_status( $status_id, $status ) {

		$status = (object) $status;

		if ( 'status' != $status->post_type ) {
			return;
		}

		$this->find_mentions_in_status( $status_id, $status->post_content );

	}

	private function find_mentions_in_status( $status_id, $status_content ) {

		// Find all of the mentions
		preg_match( "/@[a-Z0-9_]+/i", $status_content, $mentions );

		if ( $mentions ) {

			// Check to see if the username actually exists
			foreach ( $mentions as $mention ) {

				echo $mention;

			}

			exit;
			// Tag the status with those mentions
			// wp_set_object_terms( $status_id, $mentions, 'mention' );

		}

	}

}

add_action( 'plugins_loaded', array( Nock_API_Statuses::init(), 'hooks' ) );