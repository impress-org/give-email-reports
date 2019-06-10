<?php
/**
 * Perform automatic database upgrades when necessary.
 *
 * @since 1.1.0
 * @return void
 */
function give_email_reports_do_automatic_upgrades() {
	$did_upgrade  = false;
	$plugin_version = get_option( 'give_email_reports_plugin_version' );

	if ( ! $plugin_version ) {
		// 1.0 is the first version to use this option so we must add it.
		$plugin_version = '1.0';
	}

	switch ( true ) {
		case version_compare( $plugin_version, '1.1', '<' ) :
			give_email_reports_v110_upgrades();
			$did_upgrade = true;
	}

	if ( $did_upgrade || version_compare( $plugin_version, GIVE_EMAIL_REPORTS_VERSION, '<' ) ) {
		update_option( 'give_email_reports_plugin_version', GIVE_EMAIL_REPORTS_VERSION );
	}
}
add_action( 'admin_init', 'give_email_reports_do_automatic_upgrades' );


/**
 * Plugin upgrade for version 1.1.0
 */
function give_email_reports_v110_upgrades(){
	// Upgrade email status.
	$report_email_status = give_get_option( 'email_report_emails' );
	if( ! empty( $report_email_status ) ) {
		foreach ( $report_email_status as $email_status ) {
			update_option( "{$email_status}-report_notification", 'enabled' );
		}
		give_delete_option( 'email_report_emails' );
	}

	// Upgrade recipients email.
	$admin_emails = give_get_admin_notice_emails();
	$new_email_options = array(
		'daily-report_recipient',
		'weekly-report_recipient',
		'monthly-report_recipient'
	);
	if( ! empty( $admin_emails ) ) {
		foreach ( $new_email_options as $option ) {
			give_update_option( $option, $admin_emails );
		}
	}
}
