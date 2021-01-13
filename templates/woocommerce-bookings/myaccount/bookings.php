<?php
/**
 * My Bookings
 *
 * Shows customer bookings on the My Account > Bookings page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-bookings/myaccount/bookings.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/bookings-templates/
 * @author  Automattic
 * @version 1.10.0
 * @since   1.9.11
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user = wp_get_current_user();
if ( in_array( 'author', (array) $user->roles ) ) {

    if(isset($_REQUEST['action']) && $_REQUEST['action'] == "createRoom"){
        s3m_createRoom(intval($_REQUEST['booking_id']));
    }

    //The user has the "author" role
    $args = array(

        'post_type' => 'product',
        'post_status' => 'any',
        'order'          => 'ASC',
        'orderby'        => 'post_date',
        'posts_per_page' => 100,
        'author' => $user->ID,
    );
    $posts = get_posts($args);
    global $wpdb;

    ?>
     <table cellpadding="5">
         <tr><td>ID</td><td>Start</td><td>Raum Erstellen</td><td>Raum beitreten</td><td>Raum Status</td></tr>
<?php
    foreach ($posts as $post){
        //get bookings
        $bookings = $wpdb->get_results("SELECT * FROM $wpdb->postmeta
WHERE meta_key = '_booking_product_id' AND  meta_value = $post->ID", ARRAY_A);

        foreach ($bookings as $booking){
            $post_meta = get_post_meta(intval($booking['post_id']));
            if(!isset($post_meta['_video_url'][0])) $post_meta['_video_url'][0] = "";
            $erstellt = isset($post_meta['_room_created'][0]) ? $post_meta['_room_created'][0] : "nicht erstellt";
            echo '<tr><td>'.$booking['post_id'].'</td><td>'.$post_meta['_booking_start'][0].'</td><td><a href="?action=createRoom&booking_id='.$booking['post_id'].'">Jetzt Erstellen</a></td><td><a target="_blank" href="'.$post_meta['_video_url'][0].'">Jetzt beitreten</a></td><td>'.$erstellt.'</td></tr>';
        }

    }
    ?>
     </table>
    <?php
}else{


$count = 0;

if ( ! empty( $tables ) ) : ?>

	<div class="bookings-my-account-notice"></div>

	<?php foreach ( $tables as $table ) : ?>

		<h2><?php echo esc_html( $table['header'] ); ?></h2>

		<table class="shop_table my_account_bookings">
			<thead>
				<tr>
					<th scope="col" class="booking-id"><?php esc_html_e( 'ID', 'woocommerce-bookings' ); ?></th>
					<th scope="col" class="booked-product"><?php esc_html_e( 'Booked', 'woocommerce-bookings' ); ?></th>
                    <th scope="col" class="booking-link"><?php esc_html_e( 'Link', 'woocommerce-bookings' ); ?></th>

                    <th scope="col" class="order-number"><?php esc_html_e( 'Order', 'woocommerce-bookings' ); ?></th>
					<th scope="col" class="booking-start-date"><?php esc_html_e( 'Start Date', 'woocommerce-bookings' ); ?></th>
					<th scope="col" class="booking-end-date"><?php esc_html_e( 'End Date', 'woocommerce-bookings' ); ?></th>
					<th scope="col" class="booking-status"><?php esc_html_e( 'Status', 'woocommerce-bookings' ); ?></th>
					<th scope="col" class="booking-cancel"></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $table['bookings'] as $booking ) : ?>
					<?php $count++; ?>
					<tr>
						<td class="booking-id"><?php echo esc_html( $booking->get_id() ); ?></td>
						<td class="booked-product">
							<?php if ( $booking->get_product() && $booking->get_product()->is_type( 'booking' ) ) : ?>
							<a href="<?php echo esc_url( get_permalink( $booking->get_product()->get_id() ) ); ?>">
								<?php echo esc_html( $booking->get_product()->get_title() ); ?>
							</a>
							<?php endif; ?>
						</td>
                        <?php
                        $videoLink = get_post_meta($booking->get_id(), '_video_url', true);

                        ?>
                        <td class="booking-status"><a target="_blank" href="<?php echo $videoLink; ?>">Öffnen</a></td>
						<td class="order-number">
							<?php if ( $booking->get_order() ) : ?>
							<a href="<?php echo esc_url( $booking->get_order()->get_view_order_url() ); ?>">
								<?php echo esc_html( $booking->get_order()->get_order_number() ); ?>
							</a>
							<?php endif; ?>
						</td>
						<td class="booking-start-date" data-all-day="<?php echo esc_html( $booking->is_all_day() ? 'yes' : 'no' )?>" data-timezone="<?php echo esc_html( $booking->get_booking_timezone() )?>"><?php echo esc_html( $booking->get_start_date( null, null, wc_should_convert_timezone( $booking ) ) ); ?></td>
						<td class="booking-end-date" data-all-day="<?php echo esc_html( $booking->is_all_day() ? 'yes' : 'no' )?>" data-timezone="<?php echo esc_html( $booking->get_booking_timezone() )?>"><?php echo esc_html( $booking->get_end_date( null, null, wc_should_convert_timezone( $booking ) ) ); ?></td>
						<td class="booking-status"><?php echo esc_html( wc_bookings_get_status_label( $booking->get_status() ) ); ?></td>
                        <td class="booking-cancel">
							<?php if ( 'cancelled' !== $booking->get_status() && 'completed' !== $booking->get_status() && ! $booking->passed_cancel_day() ) : ?>
							<a href="<?php echo esc_url( $booking->get_cancel_url() ); ?>" class="button cancel"><?php esc_html_e( 'Cancel', 'woocommerce-bookings' ); ?></a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php do_action( 'woocommerce_before_account_bookings_pagination' ); ?>

		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
            <?php /** @var TYPE_NAME $page */
            if ( $page !== 1) : ?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( wc_get_endpoint_url( 'bookings', $page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce-bookings' ); ?></a>
			<?php endif; ?>

            <?php /** @var TYPE_NAME $bookings_per_page */
            if ( $count > $bookings_per_page ) : ?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( wc_get_endpoint_url( 'bookings', $page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce-bookings' ); ?></a>
			<?php endif; ?>
		</div>

		<?php do_action( 'woocommerce_after_account_bookings_pagination' ); ?>

	<?php endforeach; ?>

<?php else : ?>
	<div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
		<a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
			<?php esc_html_e( 'Go Shop', 'woocommerce-bookings' ); ?>
		</a>
		<?php esc_html_e( 'No bookings available yet.', 'woocommerce-bookings' ); ?>
	</div>
<?php endif; } ?>
