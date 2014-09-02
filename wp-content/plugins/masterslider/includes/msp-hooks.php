<?php
/**
 * Before Single Products Summary Div
 *
 * @see woocommerce_show_product_images()
 * @see woocommerce_show_product_thumbnails()
 */

function msp_show_product_images(){
	include ( MSWP_AVERTA_INC_DIR . '/templates/woo-product/product-images.php' );
}

function msp_on_plugins_loaded(){
	if( 'on' !== msp_get_setting( 'enable_single_product_slider' , 'msp_woocommerce' ) )
		return;

	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
	remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );

	add_action( 'woocommerce_before_single_product_summary', 'msp_show_product_images', 20 );
}

add_action( 'plugins_loaded', 'msp_on_plugins_loaded' );