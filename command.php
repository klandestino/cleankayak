<?php

if ( ! class_exists( 'WP_CLI' ) ) {
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

	$kayak = new Kayak_Subscriber_Manager();

	foreach ( $users as $user ) {
		WP_CLI::log( "Användare med mail: {$user->user_email}" );
		$isactive = $kayak->email_has_active_subscription( $user->user_email );
		WP_CLI::log( "Är aktiv: $isactive}" );
	}
};
WP_CLI::add_command( 'clean-kayak', $clean_kayak_command );
