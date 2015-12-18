<?php

Class Nock_API_Oauth extends Nock_Resources {

	protected static $single_instance = null;

	static function init() {

		if ( self::$single_instance === null ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;

	}

	public function hooks() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {

		register_rest_route( 'social-api/v1', '/oauth/access_token', array(
	        'methods' => 'POST',
	        'callback' => array( $this, 'POST_oauth_access_token' ),
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

		if ( 'client_auth' == $params['x_auth_mode'] ) {

			$user = $this->check_xauth_credentials( $params );

			if ( ! $user || is_wp_error( $user ) ) {

				return array( 'errors' => array(
					'code' => 200,
					'message' => 'The username/password are incorrect.'
				) );

			}

		}

		// Check to see if the
		if ( $app_id = $this->check_app_credentials( $params['oauth_consumer_key'], $params['oauth_consumer_secret'] ) ) {

			if ( class_exists( 'Nock_API_Sessions' ) ) {

				$sessions = new Nock_API_Sessions();

				return $access_token = $sessions->generate_new_session( $user->ID, $app_id );

			}

		}

		return array( 'error' => 'incorrect_client_credentials' );

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

	public function check_app_credentials( $key = '', $secret = '' ) {

		$args = array(
			'post_type' => 'nock-app',
			'meta_query' => array(
				array(
					'key' => '_nock_app_consumer_key',
					'value' => $key,
				),
				array(
					'key' => '_nock_app_consumer_secret',
					'value' => $secret,
				)
			),
			'fields' => 'ids'
		);

		$apps = get_posts( $args );

		if ( $apps ) {
			return $apps[0];
		}

		return false;

	}

}

add_action( 'plugins_loaded', array( Nock_API_Oauth::init(), 'hooks' ) );