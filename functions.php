<?php  



/*



Plugin Name: Voucher Tec - Integração de hotéis E-htl

Plugin URI: https://github.com/TravelTec/bookinghotels

GitHub Plugin URI: https://github.com/TravelTec/bookinghotels 

Description: Voucher Tec - Integração de hotéis E-htl é um plugin desenvolvido para agências e operadoras de turismo que precisam tratar diárias de hospedagem de fornecedores, com integração ao fornecedor E-htl.

Version: 1.0.0

Author: Travel Tec

Author URI: https://traveltec.com.br

License: GPLv2



*/  
session_start();
require_once plugin_dir_path(__FILE__) . 'includes/widget-functions.php';
require 'plugin-update-checker-4.10/plugin-update-checker.php';

add_action( 'admin_init', 'ehtl_update_checker_setting' );  

function ehtl_update_checker_setting() { 
	
	register_setting( 'vouchertec-ehtl', 'serial' ); 

        if ( ! is_admin() || ! class_exists( 'Puc_v4_Factory' ) ) {  
            return;  
        }  

        $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker( 
            'https://github.com/TravelTec/vouchertec-ehtl',  
            __FILE__,  
            'ehtl'  
        );  
	
        $myUpdateChecker->setBranch('main'); 

    }

add_action('admin_menu', 'addPluginAdminMenuEhtl');  
function addPluginAdminMenuEhtl() {  
	
		add_menu_page(  'Serial E-htl', 'Serial - Integração E-htl', 'administrator', 'vouchertec-ehtl', 'displayPluginAdminSettingsEhtl', 'dashicons-chart-area', 26 ); 
	
}
 

function displayPluginAdminSettingsEhtl() {
	require_once 'partials/vouchertec-cambio-admin-settings-display.php';
}

function valida_serial(){
	//1. checa se existe o token
	//2. checa se já existe um domínio cadastrado para o token
	//3. checa se o domínio cadastrado, caso houver, é igual ao domínio da instalação
	$serial = get_option( 'serial' );
	$serial_url = get_option( 'serial_url' );
	
	$conn = conectar_mysql_wp('162.214.165.237', 'travelte_wordpress', 'Travel#2021@', 'travelte_wordpress'); 
	
	if($serial != ""){
		$query = $conn->prepare("SELECT * FROM wp_postmeta WHERE meta_key = 'token_key' AND meta_value = '$serial'");
		$query->execute();
		$dados = $query->fetch(\PDO::FETCH_ASSOC);    
		
		//checa se existe o token
		if(!empty($dados) || $dados != null){
			$subscription_id = $dados["post_id"];
			
			$query = $conn->prepare("SELECT * FROM wp_postmeta WHERE meta_key = 'token_url' AND post_id = '$subscription_id'");
			$query->execute();
			$dados = $query->fetch(\PDO::FETCH_ASSOC);    
			
			//checa se já existe domínio cadastrado
			if(!empty($dados)){ 
				$dominio = $dados["meta_value"];

				//checa se o domínio cadastrado é igual ao da hospedagem
				if($dominio == $_SERVER['HTTP_HOST']){
					return "1";
				}else{
					return "0a";	
				}
			}else{
				return "0b";
			}
		}else{
			return "0c";
		}
	}else{
		return "0d";
	}
}


add_action( 'wp_enqueue_scripts', 'enqueue_form_ehtl' ); 
function enqueue_form_ehtl() {

    wp_enqueue_style( 
      'flatpickr-style-ehtl', 
      'https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.css'
    );

    wp_enqueue_style( 
      'carousel-style-ehtl', 
      'https://owlcarousel2.github.io/OwlCarousel2/assets/owlcarousel/assets/owl.carousel.min.css'
    );

    wp_enqueue_style( 
      'carousel-principal-style-ehtl', 
      'https://owlcarousel2.github.io/OwlCarousel2/assets/owlcarousel/assets/owl.theme.default.min.css'
    );

    wp_enqueue_script( 
        'mask-script-ehtl',
        'https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js',
        array( 'jquery' )
    );

    wp_enqueue_script( 
        'paginate-ehtl',
        'https://cdn.jsdelivr.net/npm/jquery-paginate@1.0.1/jquery-paginate.min.js',
        array( 'jquery' )
    );

    wp_enqueue_script( 
        'flatpickr-script-ehtl',
        'https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.9/flatpickr.min.js',
        array( 'jquery' )
    );

    wp_enqueue_script( 
        'carousel-script-ehtl',
        'https://owlcarousel2.github.io/OwlCarousel2/assets/owlcarousel/owl.carousel.js',
        array( 'jquery' )
    );

    wp_enqueue_script( 
        'ehtl-ajax-script',
        plugin_dir_url( __FILE__ ) . 'includes/assets/js/form-ajax-script.js',
        array( 'jquery' ),
        false,
        true
    );

    wp_localize_script( 
        'ehtl-ajax-script',
        'wp_ajax_ehtl',
        array( 
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'dede' => 1234
        )                 
    );
} 

add_action( 'wp_ajax_get_destinos_ehtl', 'get_destinos_ehtl' );
add_action( 'wp_ajax_nopriv_get_destinos_ehtl', 'get_destinos_ehtl' );

function get_destinos_ehtl() { 
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://quasar.e-htl.com.br/oauth/access_token",
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "username=120278&password=agenciaws@0457174388050792022",
      CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache", 
        "x-detailed-error: "
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        $itens = json_decode($response, true);
        $token = $itens["access_token"]; 
    }  

    function tirarAcentosHoteisEhtl($string){
return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/"),explode(" ","a A e E i I o O u U n N"),$string);
} 
 
  $local = tirarAcentosHoteisEhtl(str_replace(" ", "%20", $_POST['local']));

    $curl = curl_init();



curl_setopt_array($curl,
array(

CURLOPT_URL => "https://quasar.e-htl.com.br/destinations/search?query=".$local."&limit=8",

CURLOPT_RETURNTRANSFER => true,

CURLOPT_ENCODING => "",

CURLOPT_MAXREDIRS => 10,

CURLOPT_TIMEOUT => 30,

CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

CURLOPT_CUSTOMREQUEST => "GET", 
CURLOPT_HTTPHEADER => array(

"authorization: Bearer $token",

"cache-control: no-cache",

"content-type: application/json"

),

));



$response = curl_exec($curl);

$err = curl_error($curl);



curl_close($curl);



if ($err) {

echo "cURL Error #:" .
$err;

} else {

    $itens = json_decode($response, true);
    $resultados = $itens["data"];

    for ($i=0; $i < count($resultados); $i++) { 

        if($resultados[$i]["attributes"]["destinationType"] == "city"){
            $valores[] = array("destino"=>$resultados[$i]['id'],"sigla"=>$resultados[$i]["attributes"]['namePt'],"end"=>utf8_encode($estado) );
        }

    }
          echo json_encode($valores); 
} 
}

add_action( 'wp_ajax_send_data_ehtl', 'send_data_ehtl' );
add_action( 'wp_ajax_nopriv_send_data_ehtl', 'send_data_ehtl' );

function send_data_ehtl() { 
    global $wpdb;

    $nome_hotel = $_POST['nome_hotel_search'];
    $img_hotel = $_POST['img_search'];
    $price_hotel = $_POST['price_search']; 

    $descricao = '';

    $post = array( 
        'post_content' => "",
        'post_status' => "publish",
        'post_title' => $nome_hotel,
        'post_parent' => '',
        'post_type' => "product",
    );

    //Create post
    $post_id = wp_insert_post( $post, $wp_error ); 

    //wp_set_object_terms( $post_id, 'Integrado', 'product_cat' );
    wp_set_object_terms( $post_id, 'simple', 'product_type');

    //wp_set_object_terms($post_id, $tag, 'product_tag');

    Generate_Featured_Image_Ehtl( $img_hotel,   $post_id );
         
    update_post_meta( $post_id, '_visibility', 'visible' );
    update_post_meta( $post_id, '_stock_status', 'instock');
    update_post_meta( $post_id, 'total_sales', '0');
    update_post_meta( $post_id, '_downloadable', 'yes');
    update_post_meta( $post_id, '_virtual', 'yes');
    update_post_meta( $post_id, '_regular_price', $price_hotel );
    update_post_meta( $post_id, '_sale_price', '' );
    update_post_meta( $post_id, '_purchase_note', "" );
    update_post_meta( $post_id, '_featured', "no" );
    update_post_meta( $post_id, '_weight', "" );
    update_post_meta( $post_id, '_length', "" );
    update_post_meta( $post_id, '_width', "" );
    update_post_meta( $post_id, '_height', "" );
    update_post_meta( $post_id, '_sku', '');
    update_post_meta( $post_id, '_product_attributes', '');
    update_post_meta( $post_id, '_sale_price_dates_from', "" );
    update_post_meta( $post_id, '_sale_price_dates_to', "" );
    update_post_meta( $post_id, '_price', $price_hotel );
    update_post_meta( $post_id, '_sold_individually', "" );
    update_post_meta( $post_id, '_manage_stock', "no" );
    update_post_meta( $post_id, '_backorders', "no" );
    update_post_meta( $post_id, '_stock', "" ); 

    echo $post_id;
}

add_action( 'wp_ajax_send_data_resorts', 'send_data_resorts' );
add_action( 'wp_ajax_nopriv_send_data_resorts', 'send_data_resorts' );

function send_data_resorts() { 
    global $wpdb;

    $nome_hotel = $_POST['nome_hotel_search'];
    $img_hotel = $_POST['img_search']; 

    $descricao = '';

    $post = array( 
        'post_content' => "",
        'post_status' => "publish",
        'post_title' => $nome_hotel,
        'post_parent' => '',
        'post_type' => "_resorts",
    );

    //Create post
    $post_id = wp_insert_post( $post, $wp_error );  

    Generate_Featured_Image_Ehtl( $img_hotel,   $post_id ); 

    $post = get_post($post_id); 
    $slug = $post->post_name;

    echo $slug;
}


function Generate_Featured_Image_Ehtl( $image_url, $post_id  ){
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    $filename = basename($image_url);
    if(wp_mkdir_p($upload_dir['path']))     $file = $upload_dir['path'] . '/' . $filename;
    else                                    $file = $upload_dir['basedir'] . '/' . $filename;
    file_put_contents($file, $image_data);

    $wp_filetype = wp_check_filetype($filename, null );
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    $res1= wp_update_attachment_metadata( $attach_id, $attach_data );
    $res2= set_post_thumbnail( $post_id, $attach_id );
    return $attach_id; 
}


add_action( 'init', 'init_sopt_380202_ehtl' );
add_filter( 'query_vars', 'query_vars_sopt_380202_ehtl' );
add_action( 'parse_request', 'parse_request_sopt_380202_ehtl');

function init_sopt_380202_ehtl() {
    add_rewrite_rule( 'busca-hoteis/([^&]+)/([^&]+)/([^&]+)/([^&]+)/([^&]+)/([^&]+)', 'index.php?busca=new&destino=$matches[1]&checkin=$matches[2]&checkout=$matches[3]&adt=$matches[4]&chd=$matches[5]&qts=$matches[6]', 'top' );
}

function query_vars_sopt_380202_ehtl( $query_vars ) { 
    $query_vars[] = 'busca-hoteis';
    $query_vars[] = 'destino';
    $query_vars[] = 'checkin';
    $query_vars[] = 'checkout';
    $query_vars[] = 'adt';
    $query_vars[] = 'chd';
    $query_vars[] = 'qts';
    return $query_vars;
}

function parse_request_sopt_380202_ehtl( $wp ) {
    if ( array_key_exists( 'busca-hoteis', $wp->query_vars ) ) { 
        require('includes/templates/list-search.php');
    }
}


add_filter( 'woocommerce_add_to_cart_validation', 'remove_cart_item_before_add_to_cart_ehtl', 20, 3 );
function remove_cart_item_before_add_to_cart_ehtl( $passed, $product_id, $quantity ) {
    if( ! WC()->cart->is_empty() )
        WC()->cart->empty_cart();
    return $passed;
}

add_action( 'woocommerce_before_calculate_totals', 'custom_cart_items_prices_ehtl', 10, 1 );
function custom_cart_items_prices_ehtl( $cart ) {

    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
        return;

    // Loop through cart items
    foreach ( $cart->get_cart() as $cart_item ) {

        // Get an instance of the WC_Product object
        $product = $cart_item['data']; 
        $quantity =  $cart_item['quantity']; 

        // Get the product name (Added Woocommerce 3+ compatibility)
        $original_name = method_exists( $product, 'get_name' ) ? $product->get_name() : $product->post->post_title;

        // SET THE NEW NAME
        $new_name = $product->post->post_title.' <br> '.$_SESSION['teste'].' <br>';

        // Set the new name (WooCommerce versions 2.5.x to 3+)
        if( method_exists( $product, 'set_name' ) )
            $product->set_name( $new_name );
        else
            $product->post->post_title = $new_name;
    }
}

if ($_SESSION['tipo_plugin'] == 'tarifario') { 
  add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields_ehtl_form' );
    function custom_override_checkout_fields_ehtl_form( $fields ) {
        if ($_SESSION['tipo_tarifario'] == 0) {  
            unset($fields['billing']['billing_company']);
            unset($fields['billing']['billing_address_1']);
            unset($fields['billing']['billing_address_2']);
            unset($fields['billing']['billing_city']);
            unset($fields['billing']['billing_postcode']);
            unset($fields['billing']['billing_country']);
            unset($fields['billing']['billing_state']); 
            unset($fields['order']['order_comments']); 
            unset($fields['account']['account_username']);
            unset($fields['account']['account_password']);
            unset($fields['account']['account_password-2']);
            
        }
        return $fields;
    }

    function payment_gateway_disable_country_ehtl( $available_gateways ) {
        if ($_SESSION['tipo_plugin'] == 'hoteis' || $_SESSION['tipo_plugin'] == 'resorts') { 
                    unset(  $available_gateways['cod'] );
            unset(  $available_gateways['woo-mercado-pago-basic'] );
            unset(  $available_gateways['juno-credit-card'] );
            unset(  $available_gateways['juno-bank-slip'] );
            return $available_gateways;
        }else if ($_SESSION['tipo_plugin'] == 'tarifario') {
                    unset(  $available_gateways['misha'] ); 
            if (!is_wc_endpoint_url( 'order-pay' )) { 
                global $woocommerce;
                    unset(  $available_gateways['paypal'] );
                if ($_SESSION['tipo_tarifario'] == 1) { 
                    unset(  $available_gateways['cod'] );
                    unset(  $available_gateways['cod'] );
                }else if ($_SESSION['tipo_tarifario'] == 0) { 
                    unset(  $available_gateways['woo-mercado-pago-basic'] );
                    unset(  $available_gateways['juno-credit-card'] );
                    unset(  $available_gateways['juno-bank-slip'] );
                } 
                return $available_gateways;
            }else{
                unset(  $available_gateways['cod'] );
                return $available_gateways;
            }
        }
    }
    add_filter( 'woocommerce_available_payment_gateways', 'payment_gateway_disable_country_ehtl' );

    add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields_ehtl' );
    function custom_override_checkout_fields_ehtl( $fields ) {
        if ($_SESSION['tipo_plugin'] == 'hoteis' || $_SESSION['tipo_plugin'] == 'resorts') { 
            return $fields;
        }else if($_SESSION['tipo_plugin'] == 'tarifario'){
            if ($_SESSION['tipo_tarifario'] == 0) {  
                unset($fields['billing']['billing_company']);
                unset($fields['billing']['billing_address_1']);
                unset($fields['billing']['billing_address_2']);
                unset($fields['billing']['billing_city']);
                unset($fields['billing']['billing_postcode']);
                unset($fields['billing']['billing_country']);
                unset($fields['billing']['billing_state']); 
                unset($fields['billing']['billing_number']); 
                unset($fields['billing']['billing_neighborhood']); 
                unset($fields['order']['order_comments']); 
                unset($fields['account']['account_username']);
                unset($fields['account']['account_password']);
                unset($fields['account']['account_password-2']);
                
            }
            return $fields;
        }
    }
}
