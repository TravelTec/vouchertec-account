<?php  



/*
Plugin Name: Voucher Tec - Minha Conta
Plugin URI: https://github.com/TravelTec/vouchertec-account
GitHub Plugin URI: https://github.com/TravelTec/vouchertec-account 
Description: Voucher Tec - Minha Conta permite ao cliente da Travel Tec controlar e gerenciar seus pedidos, formas de pagamento e assinaturas no site.
Version: 1.0.3
Author: Travel Tec
Author URI: https://traveltec.com.br
License: GPLv2
*/  
session_start();

require 'plugin-update-checker-4.10/plugin-update-checker.php';

add_action( 'admin_init', 'account_update_checker_setting' );  

function account_update_checker_setting() {  
        if ( ! is_admin() || ! class_exists( 'Puc_v4_Factory' ) ) {  
            return;  
        }  

        $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker( 
            'https://github.com/TravelTec/vouchertec-account',  
            __FILE__,  
            'account'  
        );  
	
        $myUpdateChecker->setBranch('main'); 
}

/**
 * Register new endpoint to use inside My Account page.
 *
 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
 */
function my_custom_endpoints() {
	add_rewrite_endpoint( '2via-boleto', EP_ROOT | EP_PAGES );
}

add_action( 'init', 'my_custom_endpoints' );

/**
 * Add new query var.
 *
 * @param array $vars
 * @return array
 */
function my_custom_query_vars( $vars ) {
	$vars[] = '2via-boleto';

	return $vars;
}

add_filter( 'query_vars', 'my_custom_query_vars', 0 );

/**
 * Endpoint HTML content.
 */
function my_custom_endpoint_content() { 

	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://api.iugu.com/v1/subscriptions?api_token=5F805C1604C9C0EB241D0193ED18710284641A80947E7BE04110A0307141C524",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_HTTPHEADER => array(
	    "authorization: Bearer NUY4MDVDMTYwNEM5QzBFQjI0MUQwMTkzRUQxODcxMDI4NDY0MUE4MDk0N0U3QkUwNDExMEEwMzA3MTQxQzUyNDo=",
	    "cache-control: no-cache",
	    "postman-token: 398aa734-d6da-3c27-89dc-53e881164c10"
	  ),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
	  echo "cURL Error #:" . $err;
	} else {
		echo '<pre>';
		print_r($response);
		echo '</pre>';
	} 
}

add_action( 'woocommerce_account_2via-boleto_endpoint', 'my_custom_endpoint_content' );
