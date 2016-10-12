<?php
/**
 * This file contain actions.
 */

function give_reset_email_report_cron(){
	if( empty( $_POST['cron'] ) ) {
		wp_send_json_error();
	}

	wp_clear_scheduled_hook( sanitize_text_field( $_POST['cron'] ) );
	wp_send_json_success();
}
add_action( 'wp_ajax_give_reset_email_report_cron', 'give_reset_email_report_cron' );