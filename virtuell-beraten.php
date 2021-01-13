<?php

/*
Plugin Name: Virtuell Beraten
Plugin URI: https://www.s3-medien.de
Description: Additions for Woocommerce
Version: 1.0
Author: tstark
Author URI: https://www.s3-medien.de
*/

/**
 * @param $order_id
 */
//s3m_woocommerce_order_status_completed(149);
//s3m_createBooking(149);
function s3m_woocommerce_order_status_completed( $order_id ) {
    error_log( "Order processing for order $order_id on site ", 0 );
    $data = $order_id.get_site_url();
    $booking_token = hash('sha512', $data, false);

    $endpoint = 'http://videoapi.s3-medien.de/api/room/book';

    $args = array(
        'post_parent' => $order_id,
        'post_type' => 'wc_booking',
        'post_status' => 'any'
    );

    $bookings = get_posts($args);

    //var_dump($bookings);
    //error_log('bookings: '.count($bookings));

    if(count($bookings) > 0){
        s3m_createBooking($bookings[0]->ID);
    }
}

function s3m_createBooking($order_id){
    $data = $order_id.get_site_url();
    $booking_token = hash('sha512', $data, false);

    $endpoint = 'http://videoapi.s3-medien.de/api/room/book';

    $booking_meta = get_post_meta($order_id);

    //20210121110000
    $start_date = DateTime::createFromFormat('YmdHis', $booking_meta['_booking_start'][0]);
    $end_date = DateTime::createFromFormat('YmdHis', $booking_meta['_booking_end'][0]);

    $diff = $start_date->diff($end_date);
    $duration = $diff->days * 24 * 60;
    $duration += $diff->h * 60;
    $duration += $diff->i;

    $body = array(
        'booking_token' => $booking_token,
        'start_date' => $start_date->format('Y-m-d H:i'),
        'duration' => $duration,
        'max_participants' => '10'
    );

    //$body = wp_json_encode( $body );

    $options = [
        'body'        => $body,
        'headers'     => [
            'Authorization' => 'Bearer F43MOUs3Tah01DpGQqRGWQXLSNTAYHmqtj84Ti9z',
            //'Content-Type' => 'application/json',
        ],
        'timeout'     => 60,
        'redirection' => 5,
        'blocking'    => true,
        //'httpversion' => '1.0',
        //'sslverify'   => false,
        'data_format' => 'body',
    ];

    $response = wp_remote_post( $endpoint, $options );

    //var_dump($response['body']);

    $data = json_decode($response['body']);

    update_post_meta($order_id, '_video_url', $data->link);
    update_post_meta($order_id, '_room_id', $data->room_id);
    //send mail to customer

}

//add_action( 'woocommerce_order_status_processing', 's3m_woocommerce_order_status_completed', 10, 1 );
//add_action( 'woocommerce_order_status_completed', 's3m_woocommerce_order_status_completed', 10, 1 );
add_action('woocommerce_booking_paid', 's3m_createBooking', 10, 1 );
//add_action('woocommerce_before_thankyou', 's3m_woocommerce_order_status_completed');

function s3m_createVideoConferenceRoom($order_id){
    //error_log( "Order processing for order $order_id on site ".get_site_url(), 0 );

    $args = array(
        'post_parent' => $order_id,
        'post_type' => 'wc_booking',
        'post_status' => 'any',
    );

    $bookings = get_posts($args);

    if(count($bookings) > 0) {
        s3m_createRoom($bookings[0]->ID);
    }
}

function s3m_createRoom($order_id){
    $data = $order_id.get_site_url();
    $booking_token = hash('sha512', $data, false);

    $endpoint = 'http://videoapi.s3-medien.de/api/room/create';
    $booking_meta = get_post_meta($order_id);

    //var_dump($booking_meta);

    //20210121110000
    $start_date = DateTime::createFromFormat('YmdHis', $booking_meta['_booking_start'][0]);
    $end_date = DateTime::createFromFormat('YmdHis', $booking_meta['_booking_end'][0]);

    $diff = $start_date->diff($end_date);
    $duration = $diff->days * 24 * 60;
    $duration += $diff->h * 60;
    $duration += $diff->i;

    $body = array(
        'booking_token' => $booking_token,
        'start_date' => $start_date->format('Y-m-d H:i'),
        'duration' => $duration,
        'max_participants' => '10',
        'room_id' => $booking_meta['_room_id'][0]
    );

    //$body = wp_json_encode( $body );

    $options = [
        'body' => $body,
        'headers' => [
            'Authorization' => 'Bearer F43MOUs3Tah01DpGQqRGWQXLSNTAYHmqtj84Ti9z',
            //'Content-Type' => 'application/json',
        ],
        'timeout' => 60,
        'redirection' => 5,
        'blocking' => true,
        //'httpversion' => '1.0',
        //'sslverify'   => false,
        'data_format' => 'body',
    ];

    $response = wp_remote_post($endpoint, $options);
    $data = json_decode($response['body']);

    update_post_meta($order_id, '_video_url', $data->link);
    update_post_meta($order_id, '_room_created', date("Y-m-d H:i:s"));
}