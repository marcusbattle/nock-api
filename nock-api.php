<?php
/*
Plugin Name: Nock API
Version: 0.1.0
Author: Marcus Battle
Description: API for Nock - Private Social Network
*/

class Nock_API {

	protected static $single_instance = null;

	public $oauth;
	public $apps;
	public $users;
	public $sessions;
	public $statuses;
	public $messages;

	static function init() {

		if ( self::$single_instance === null ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;

	}

	public function __construct() {

		// Load Includes
		require_once plugin_dir_path( __FILE__ ) . '/includes/CMB2/init.php';
		require_once plugin_dir_path( __FILE__ ) . '/includes/helpers/sessions.php';
		require_once plugin_dir_path( __FILE__ ) . '/includes/helpers/global.php';

		// Load Resources
		require_once plugin_dir_path( __FILE__ ) . 'core/resources.php';
		require_once plugin_dir_path( __FILE__ ) . 'core/resources/oauth.php';
		require_once plugin_dir_path( __FILE__ ) . 'core/resources/apps.php';
		require_once plugin_dir_path( __FILE__ ) . 'core/resources/users.php';
		require_once plugin_dir_path( __FILE__ ) . 'core/resources/sessions.php';
		require_once plugin_dir_path( __FILE__ ) . 'core/resources/statuses.php';
		require_once plugin_dir_path( __FILE__ ) . 'core/resources/messages.php';

		$this->oauth = new Nock_API_Oauth();
		$this->apps = new Nock_API_Apps();
		$this->users = new Nock_API_Users();
		$this->sessions = new Nock_API_Sessions();
		$this->statues = new Nock_API_Statuses();
		$this->messages = new Nock_API_Messages();

	}

	public function hooks() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {

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

add_action( 'plugins_loaded', array( Nock_API::init(), 'hooks' ) );


