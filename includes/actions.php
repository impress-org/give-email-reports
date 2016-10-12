<?php
/**
 * This file contain actions.
 */

function give_reset_email_report_cron(){
	if( empty( $_POST['cron'] ) ) {
		wp_send_json_error();
	}

	$cron_name = sanitize_text_field( $_POST['cron'] );

	// Unset cron related time settings.
	$give_settings = give_get_settings();
	if( ! empty( $give_settings[ "{$cron_name}_delivery_time" ] ) ) {
		unset( $give_settings[ "{$cron_name}_delivery_time" ] );
		update_option( 'give_settings', $give_settings );
	}

	wp_clear_scheduled_hook( $cron_name);
	wp_send_json_success();
}
add_action( 'wp_ajax_give_reset_email_report_cron', 'give_reset_email_report_cron' );