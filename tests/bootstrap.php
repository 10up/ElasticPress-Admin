<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once( $_tests_dir . '/includes/functions.php' );

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

function _manually_load_plugin() {
	$host = getenv( 'EP_HOST' );
	if ( empty( $host ) ) {
		$host = 'http://localhost:9200';
	}

	define( 'EP_HOST', $host );

	// Require ElasticPress
	// @todo add better support for whatever the elasticpress plugin folder is named
	require( dirname( __FILE__ ) . '/../../elasticpress/elasticpress.php' );

	require( dirname( __FILE__ ) . '/../elasticpress_admin.php' );

	$tries = 5;
	$sleep = 3;
	do {
		$response = wp_remote_get( EP_HOST );
		if ( 200 == wp_remote_retrieve_response_code( $response ) ) {
			// Looks good!
			break;
		} else {
			printf( "\nInvalid response from ES, sleeping %d seconds and trying again...\n", $sleep );
			sleep( $sleep );
		}
	} while ( --$tries );

	if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
		exit( 'Could not connect to ElasticPress server.' );
	}

	require_once( dirname( __FILE__ ) . '/includes/functions.php' );
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require( $_tests_dir . '/includes/bootstrap.php' );
require_once( dirname( __FILE__ ) . '/includes/class-epa-test-base.php' );