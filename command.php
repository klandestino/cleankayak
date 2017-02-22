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

	foreach ( $users as $user ) {
		WP_CLI::log( "Användare med mail: {$user->user_email}" );
		email_has_active_subscription( $user->user_email );
		WP_CLI::log( "Är aktiv: $isactive}" );
	}
};

function email_has_active_subscription( $email ) {
	try {
		$client = new SoapClient('http://91.209.29.10:8080/KayakWebServiceARB/KayakWebService.asmx?WSDL', array('trace' => 1));
		$args = array(
			'sEmail' => $email
		);
		$response = $client->GetCusByEmail($args);
		
		$xml = simplexml_load_string($response->GetCusByEmailResult->any);
		if($xml->NewDataSet->CUSTOMER){
			foreach ( $xml->NewDataSet->CUSTOMER as $customer){
				if ( Kayak_Subscriber_Manager::has_active_subscription( $customer->CUSNO ) ) {
					return true;
				}
			}
		}
	} catch (SoapFault $soapFault) {
		var_dump($soapFault);
	}
	return false;
}

WP_CLI::add_command( 'clean-kayak', $clean_kayak_command );
