<?php
/**
 * This file contain actions.
 */

function give_reset_email_report_cron() {

	if ( empty( $_POST['cron'] ) || ! current_user_can( 'manage_give_settings' ) ) {
		wp_send_json_error();
	}

	$cron_name = give_clean( $_POST['cron'] );

	// Prevent cron to setup again.
	define( 'GIVE_DISABLE_EMAIL_REPORTS', true );

	if ( empty( $_POST['form_id'] ) ) {
		wp_clear_scheduled_hook( $cron_name );
		// Unset cron related time settings.
		$give_settings = give_get_settings();
		if ( ! empty( $give_settings[ "{$cron_name}_delivery_time" ] ) ) {
			unset( $give_settings[ "{$cron_name}_delivery_time" ] );
			update_option( 'give_settings', $give_settings );
		}
	} else {
		wp_clear_scheduled_hook( $cron_name, array( 'form_id' => absint( $_POST['form_id'] ) ) );
	}
	wp_send_json_success();
}

add_action( 'wp_ajax_give_reset_email_report_cron', 'give_reset_email_report_cron' );
