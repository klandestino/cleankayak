<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

/**
 * Says "Hello World" to new users
 *
 * @when before_wp_load
 */
$hello_world_command = function() {
	WP_CLI::success( "Clean kayak." );
};
WP_CLI::add_command( 'clean-kayak', $hello_world_command );
