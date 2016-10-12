<?php
/**
 * Email reports uninstall.
 */

//Sanity check: if uninstall.php is not called by WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	wp_die( __( 'Plugin uninstallation can not be executed in this fashion.', 'give-email-reports' ) );
}

wp_clear_scheduled_hook( 'give_email_reports_daily_email' );
wp_clear_scheduled_hook( 'give_email_reports_weekly_email' );
wp_clear_scheduled_hook( 'give_email_reports_monthly_email' );
