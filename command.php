<?php

if ( ! class_exists( 'WP_CLI' ) || ! class_exists( 'Kayak_Subscriber_Manager' ) ) {
	return;
}

/**
 * Says "Hello World" to new users
 *
 * @when before_wp_load
 */
$clean_kayak_command = function() {
	$response = WP_CLI::launch_self( 'user list --role=subscriber', array(), array( 'format' => 'json' ), false, true );
	$users = json_decode( $response->stdout );
	foreach ( $users as $user ) {
		WP_CLI::log( "Användare med mail: {$user->user_email}" );
	}
};
WP_CLI::add_command( 'clean-kayak', $clean_kayak_command );
