<?php

/*
Plugin Name: Virtuell Beraten
Plugin URI: https://www.s3-medien.de
Description: Additions for Woocommerce
Version: 1.0
Author: tstark
Author URI: https://www.s3-medien.de
*/

function mysite_woocommerce_order_status_completed( $order_id ) {
    error_log( "Order complete for order $order_id", 0 );
}
add_action( 'woocommerce_order_status_completed', 'mysite_woocommerce_order_status_completed', 10, 1 );