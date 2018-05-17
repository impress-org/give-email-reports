<?php
/**
 * This file contain actions.
 */

function give_reset_email_report_cron(){
	if( empty( $_POST['cron'] ) ) {
		wp_send_json_error();
	}

	$cron_name = sanitize_text_field( $_POST['cron'] );

	// Prevent cron to setup again.
	define( 'GIVE_DISABLE_EMAIL_REPORTS', true );

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

/**
 * Schedule cron healthcheck
 *
 * @access public
 *
 * @param array $schedules Schedules.
 *
 * @return array $schedules Schedules.
 */
function give_email_report_add_monthly_cron_schedules( $schedules ) {
	$schedules['monthly'] = array(
		'interval' => 2635200,
		'display'  => __( 'Once a month', 'give-email-reports' ),
	);

	return $schedules;
}


add_filter( 'cron_schedules', 'give_email_report_add_monthly_cron_schedules' );