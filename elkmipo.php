<?php
/**
 * @wordpress-plugin
 * Plugin Name:				ELK Minimum Items per Order for WooCommerce
 * Plugin URI:				https://github.com/kokiddp/elkmipo
 * Description:				This simple plugin allows to set a minimum number of items per order
 * Version:					1.0.0
 * Requires at least:		4.6
 * Tested up to:			5.3.2
 * Requires PHP:			7.1
 * WC requires at least:	3.0.2
 * WC tested up to:			3.9.1
 * Author:					ELK-Lab
 * Author URI:				https://www.elk-lab.com
 * License:					GPL-2.0+
 * License URI:				http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:				elkmipo
 * Domain Path:				/languages
 */

if ( !defined( 'ABSPATH' ) || !defined( 'WPINC' ) ) {
    die;
}

add_action( 'init', 'elkmipo_load_textdomain' );  
function elkmipo_load_textdomain() {
	load_plugin_textdomain( 'elkmipo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

add_action( 'admin_init', 'elkmipo_require_woocommerce' );
function elkmipo_require_woocommerce() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) ) {
    	if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	        add_action( 'admin_notices', 'elkmipo_no_woocommerce_notice' );
	        deactivate_plugins( plugin_basename( __FILE__ ) ); 

	        if ( isset( $_GET['activate'] ) ) {
	            unset( $_GET['activate'] );
	        }
	    }
    }
}

function elkmipo_no_woocommerce_notice(){
    ?><div class="error"><p><?= __( 'WooCommerce is required in order to use ELK Minimum Items per Order', 'elkmipo' ) ?></p></div><?php
}

add_filter( 'woocommerce_get_sections_products', 'elkmipo_add_settings_section' );
function elkmipo_add_settings_section( $sections ) {	
	$sections['elkmipo'] = __( 'ELK Minimum Items per Order', 'elkmipo' );
	return $sections;	
}

add_filter( 'woocommerce_get_settings_products', 'elkmipo_settings_section', 10, 2 );
function elkmipo_settings_section( $settings, $current_section ) {
	if ( $current_section == 'elkcmipo' ) {
		$elkmipo_settings = array();

		$elkmipo_settings[] = array(
			'name' => __( 'ELK Minimum Items per Order Settings', 'elkmipo' ),
			'type' => 'title',
			'desc' => __( 'The following options are used to configure ELK Minimum Items per Order', 'elkmipo' ),
			'id' => 'elkmipo'
		);

		$elkmipo_settings[] = array(
			'name'     => __( 'Minimum items', 'elkmipo' ),
			'desc_tip' => __( 'Insert the minimum number of items per order', 'elkmipo' ),
			'id'       => 'elkmipo_min_items',
			'type'     => 'number'
		);
		
		$elkmipo_settings[] = array(
			'type' => 'sectionend',
			'id' => 'elkmipo'
		);
		return $elkmipo_settings;
	}
	else {
		return $settings;
	}
}

add_action( 'woocommerce_check_cart_items', 'elkmipo_minimum_order_amount' ); 
function elkmipo_minimum_order_amount() {
	if( is_cart() || is_checkout() ) {
		$minimum = intval( get_option( 'elkmipo_min_items' ) != null ? get_option( 'elkmipo_min_items' ) : 1 );
		$cart_count = intval( WC()->cart->get_cart_contents_count() );

		if ( $cart_count < $minimum ) {
			wc_add_notice( 
				sprintf(
					__( 'In your cart there are %s items — you must have at least %s items in your cart to place your order' , 'elkmipo' ), 
					$cart_count, 
					$minimum
				), 'error' 
			);
		}
	}
}