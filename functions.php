<?php  



/*
Plugin Name: Voucher Tec - Minha Conta
Plugin URI: https://github.com/TravelTec/vouchertec-account
GitHub Plugin URI: https://github.com/TravelTec/vouchertec-account 
Description: Voucher Tec - Minha Conta permite ao cliente da Travel Tec controlar e gerenciar seus pedidos, formas de pagamento e assinaturas no site.
Version: 1.0.2
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
	echo '<p>2ª via boleto Iugu</p>';
}

add_action( 'woocommerce_account_2via-boleto_endpoint', 'my_custom_endpoint_content' );
