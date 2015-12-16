<?php

Class Social_API_Users {

	protected static $single_instance = null;

	static function init() {

		if ( self::$single_instance === null ) {
			self::$single_instance = new self();
		} 

		return self::$single_instance;

	}

	public function __construct() { }

	public function hooks() { }

	public function generate_access_token( $user_id ) {

		$token = bin2hex( openssl_random_pseudo_bytes(16) );

		$result = update_user_meta( $user_id, 'nock_access_token', $token );

		return $token;

	}

	public function access_token_is_valid( $access_token = '' ) {

		if ( empty( $access_token ) ) {
			return false;
		}

		$args = array(
			'meta_key' => 'nock_access_token',
			'meta_value' => $access_token
		);

		$users = get_users( $args );

		if( $users  ) {
			return $users[0]->ID;
		}

	}

}

add_action( 'plugins_loaded', array( Social_API_Users::init(), 'hooks' ) );