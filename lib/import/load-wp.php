<?php
define( 'TRADERABSPATH', dirname( __FILE__ ) . '/' );

if ( !defined( 'ABSPATH' ) ) {

    $wp_did_header = true;

// Load the WordPress library.
    require_once(TRADERABSPATH . '../../../../../wp-load.php');

// Set up the WordPress query.
    wp();

    wp_set_current_user(1);
}