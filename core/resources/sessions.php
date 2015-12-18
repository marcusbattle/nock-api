<?php

Class Nock_API_Sessions {

	protected static $single_instance = null;

	static function init() {

		if ( self::$single_instance === null ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;

	}

	public function hooks() {

		add_action( 'init', array( $this, 'init_session_post_type' ) );
		add_action( 'cmb2_admin_init', array( $this, 'init_app_credentials_metabox' ) );

	}

	/**
	 * Initialize the 'Session' Post Type
	 */
	public function init_session_post_type() {

	    $args = array(
			'public' 		=> true,
			'label'  		=> 'Sessions',
			'menu_icon' 	=> 'dashicons-admin-network',
			'supports'		=> array( 'title' )
	    );

	    register_post_type( 'session', $args );

	}

	public function init_app_credentials_metabox() {

		$prefix = '_nock_session_';

		$session_metabox = new_cmb2_box( array(
			'id'            => $prefix . 'metabox',
			'title'         => __( 'Session', 'nock' ),
			'object_types'  => array( 'session', ),
			'show_names' 	=> true,
		) );

		$session_metabox->add_field( array(
			'name' => __( 'Access Token', 'nock' ),
			'id'   => $prefix . 'access_token',
			'type' => 'text',
		) );

		$session_metabox->add_field( array(
			'name' => __( 'User ID', 'nock' ),
			'id'   => $prefix . 'user_id',
			'type' => 'text_small',
		) );

	}

	/**
	 * Generates a new authentication session
	 */
	public function generate_new_session( $user_id = 0, $app_id ) {

		if ( ! $user_id ) {

			return array(
				'error' 	=> '',
				'message'	=> '',
				'code' 		=> ''
			);

		}

		$access_token = nock_generate_random_string();
		$timestamp = time();

		$session_args = array(
			'post_status'		=> 'publish',
			'post_type'			=> 'session',
			'post_author'		=> $user_id,
			'post_content'		=> '',
			'post_title'		=> get_userdata( $user_id )->user_login . " - $timestamp",
		);

		$session_id = wp_insert_post( $session_args );

		if ( is_wp_error( $session_id ) ) {

			return array(
				'error' 	=> '',
				'message'	=> '',
				'code'		=> ''
			);

		}

		update_post_meta( $session_id, '_nock_session_access_token', $access_token );
		update_post_meta( $session_id, '_nock_session_timestamp', $timestamp );
		update_post_meta( $session_id, '_nock_session_app_id', $app_id );
		update_post_meta( $session_id, '_nock_session_user_id', $user_id );

		return array(
			'access_token'	=> $access_token,
			'timestamp'		=> $timestamp
		);

	}

	public function get_session( $access_token = '' ) {

		if ( empty( $access_token ) ) {
			return false;
		}

		$session_args = array(
			'post_type'			=> 'session',
			'post_status'		=> 'publish',
			'posts_per_page' 	=> 1,
			'meta_key'			=> '_nock_session_access_token',
			'meta_value'		=> $access_token,
			'fields'			=> 'ids'
		);

		$results = new WP_Query( $session_args );

		if ( $results ) {

			$session_id = $results->posts[0];

			$session = array(
				'session_id'	=> $session_id,
				'user_id' 		=> get_post_meta( $session_id, '_nock_session_user_id', true ),
			);

			return $session;

		}

		return false;

	}

}

add_action( 'plugins_loaded', array( Nock_API_Sessions::init(), 'hooks' ) );