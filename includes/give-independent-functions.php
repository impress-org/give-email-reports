<?php
/**
 * This file contains functions which are independent of Give.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clear Email report hook that are being scheduled to that form.
 *
 * @since 1.2
 *
 * @param int $form_id Donation Form id.
 */
function ger_clear_form_cron( $form_id ) {
	$crons = array(
		'give_email_reports_daily_per_form',
		'give_email_reports_weekly_per_form',
		'give_email_reports_monthly_per_form',
	);

	foreach ( $crons as $cron ) {
		wp_clear_scheduled_hook( $cron, array( 'form_id' => $form_id ) );
	}
}

/**
 * Delete all form scheduled.
 *
 * @since 1.2
 */
function ger_delete_all_form_scheduled() {
	$form_ids = array();
	global $wpdb;

	$query = "
        SELECT DISTINCT {$wpdb->prefix}give_formmeta.form_id 
        FROM {$wpdb->prefix}give_formmeta 
        WHERE 
        {$wpdb->prefix}give_formmeta.meta_key = '%s'
        AND
        {$wpdb->prefix}give_formmeta.meta_value = '%s'
    ";

	$query = $wpdb->prepare( $query, '_give_email_report_options', 'enabled' );

	/**
	 * Filter to modify get donation form who email report is being scheduled.
	 *
	 * @since 1.2
	 *
	 * @param string $query $args Argument that need to pass in SQL query.
	 *
	 * @return string $query $args Argument that need to pass in SQL query.
	 */
	$query = (string) apply_filters( 'ger_get_donation_form_args', $query );

	$forms = $wpdb->get_col( $query );

	if ( ! empty( $forms ) ) {
		foreach ( $forms as $form ) {
			$form_ids[] = absint( $form );
		}
	}

	if ( ! empty( $form_ids ) ) {
		foreach ( $form_ids as $form_id ) {
			ger_clear_form_cron( $form_id );
		}
	}
}

/**
 * Deletes all settings created by this plugin.
 *
 * @since 1.2
 */
function ger_delete_settings() {
	$periods = array(
		'daily',
		'weekly',
		'monthly',
	);

	foreach ( $periods as $period ) {
		give_delete_option( "{$period}-report_notification" );
		give_delete_option( "{$period}-report_email_subject" );
		give_delete_option( "{$period}-report_email_header" );
		give_delete_option( "give_email_reports_{$period}_email_template" );
		give_delete_option( "give_email_reports_{$period}_email_delivery_time" );
	}
}

