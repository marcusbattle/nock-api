<?php

function nock_generate_random_string( $length = 16 ) {

	$string = bin2hex( openssl_random_pseudo_bytes( $length ) );

	return $string;

}