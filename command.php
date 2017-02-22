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
		$isactive = email_has_active_subscription( $user->user_email );
		if ( $isactive ) {
			WP_CLI::log( "Är aktiv!" );
		} else {
			WP_CLI::log( "Är inaktiv!" );
		}
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
				if ( has_active_subscription( $customer->CUSNO ) ) {
					return true;
				}
			}
		}
	} catch (SoapFault $soapFault) {
	}
	return false;
}


function has_active_subscription( $cusno ) {

	// Pupulate SOAP arguments
	$args = array(
		'lCusno' => $cusno,
		'sSource' => 'CRX',
		'dteLimitDate' => date('Y-m-d'),
		'sSubsno' => '',
		'sExtno' => ''
	);

	// Create SoapClient

	try {
		$client = new SoapClient('http://91.209.29.10:8080/KayakWebServiceARB/KayakWebService.asmx?WSDL', array('trace' => 1));
		$response = $client->GetSubscriptions_CII($args);
		$xml = simplexml_load_string($response->GetSubscriptions_CIIResult->any);
		if($xml->NewDataSet->Table){
			foreach ( $xml->NewDataSet->Table as $table){
				if( $table->SUBSSTATE == '01' && strtotime($table->SUBSSTARTDATE) <= strtotime('now') && strtotime($table->SUBSENDDATE) >= strtotime('now') ){
					return true;
				}
			}
		}
	} catch (SoapFault $soapFault) {
	}

	return false;
}

WP_CLI::add_command( 'clean-kayak', $clean_kayak_command );
