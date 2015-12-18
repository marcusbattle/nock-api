<?php

/**
 * Checks to see whether or not an access token is valid
 *
 * @param  string 	$access_token
 * @return integer	$session_id
 */
function nock_access_token_is_valid( $access_token = '' ) {

	if ( class_exists('Nock_API_Sessions') ) {

		$sessions_resource = new Nock_API_Sessions();

		$session = $sessions_resource->get_session( $access_token );

		if ( ! $session ) {
			return false;
		}

		return $session;

	}

}