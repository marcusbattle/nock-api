<?php
/*
Plugin Name: Social API
Version: 0.1.0
Author: Marcus Battle
Description: Membership list. A way to track members. The aggregate of people living together in a more or less ordered community.
*/

class Social_API {

	protected static $single_instance = null;
	protected $apps;
	protected $users;

	static function init() {

		if ( self::$single_instance === null ) {
			self::$single_instance = new self();
		} 

		return self::$single_instance;

	}

	public function __construct() { 

		require_once plugin_dir_path( __FILE__ ) . '/core/apps.php';
		require_once plugin_dir_path( __FILE__ ) . '/core/users.php';

		$this->apps = new Social_API_Apps();
		$this->users = new Social_API_Users();

	}

	public function hooks() { 
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {

		// Oauth
		register_rest_route( 'social-api/v1', '/oauth/access_token', array(
	        'methods' => 'POST',
	        'callback' => array( $this, 'POST_oauth_access_token' ),
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

		// GET Groups (All)
		register_rest_route( 'social-api/v1', '/groups', array(
	        'methods' => 'GET',
	        'callback' => array( $this, 'GET_groups' ),
	    ) );

		register_rest_route( 'social-api/v1', '/networks', array(
	        'methods' => 'GET',
	        'callback' => array( $this, 'GET_networks' ),
	    ) );

	}

	public function POST_oauth_access_token( $data ) {

		$params = wp_parse_args( $data->get_params(), array(
			'oauth_consumer_key'	=> '',
			'oauth_consumer_secret'	=> '',
			'x_auth_username' 		=> '',
			'x_auth_password' 		=> '',
			'x_auth_mode' 			=> '',
		) );

		if ( empty( $params['oauth_consumer_key'] ) || empty( $params['oauth_consumer_secret'] ) ) {
			return array( 'error' => 'incorrect_client_credentials' );
		}

		if ( $params['x_auth_mode'] == 'client_auth' ) {

			$user = $this->check_xauth_credentials( $params );
		
			if ( ! $user || is_wp_error( $user ) ) {
				
				return array( 'errors' => array(
					'code' => 200,
					'message' => 'The username/password are incorrect.'
				) );

			}

		}

		if ( $this->apps->check_app_credentials( $params['oauth_consumer_key'], $params['oauth_consumer_secret'] ) ) {
			
			$access_token = $this->users->generate_access_token( $user->ID );

			return array( 
				'access_token' => $access_token,
				'refresh_token' => '',
				'token_type' => 'Bearer',
				'expires' => 123456789
			);

		}

		return array( 'error' => 'incorrect_client_credentials' );

	}

	/** 
	 * Get all statuses
	 */
	public function GET_statuses( $data ) {
		
		$access_token = $data->get_param('access_token');
		
		if ( ! $user_id = $this->users->access_token_is_valid( $access_token ) ) {
			return false;
		} 

		$args = array(
			'post_type' => 'status',
			'post_status' => array( 'publish', 'private' ),
			'posts_per_page' => 10,
			'author' => $user_id
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
			$statuses[ $index ]->link = "status/{$status->ID}";
		}

	    return $statuses;

	}

	public function GET_status( $data ) {

		$status_id = $data->get_param('id');

		$status = get_post( $status_id, ARRAY_A );

		$args = array(
			'post_id' => $status_id
		);

		$status['comments'] = get_comments( $args );

		return $status;
		
	}

	public function GET_groups( $data ) {

		$groups = array(
			array(
				'id'	=> 1,
				'name'	=> 'Family',
				'link'	=> 'groups/1'
			),
			array(
				'id'	=> 2,
				'name'	=> 'The Summit Leadership',
				'link'	=> 'groups/2'
			),
			array(
				'id'	=> 3,
				'name'	=> 'Connect50',
				'link'	=> 'groups/3'
			)
		);

		return $groups;

	}

	public function GET_networks( $data ) {

		return get_blogs_of_user( 1 );

		return $data;

	}

	public function check_xauth_credentials( $params = array() ) {

		$username = isset( $params['x_auth_username'] ) ? $params['x_auth_username'] : '';
		$password = isset( $params['x_auth_password'] ) ? $params['x_auth_password'] : '';

		if ( empty( $username ) ) {
			return false;
		}

		$user = get_user_by( 'login', $username );

		if ( $user && wp_check_password( $password, $user->data->user_pass, $user->ID) ) {
			return $user;
		}

		return false;

	}

	public function get_http_post_data( $data ) {

		if ( array_filter( $data->get_params() ) ) {
			return $data;
		}
		
		// Get the post data for Angular JS POST/GET
		$post_data = file_get_contents("php://input");
		$post_data = json_decode( $post_data );

		foreach ( $post_data as $key => $value ) {
			$data->set_param( $key, $value );
		}

		return $data;

	}

}

add_action( 'plugins_loaded', array( Social_API::init(), 'hooks' ) );


