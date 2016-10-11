<?php
/**
 * Admin Scripts
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load admin scripts
 *
 * @param $hook
 *
 * @since       1.0
 * @return      void
 */
function give_email_reports_admin_scripts( $hook ) {

	// Use minified libraries if SCRIPT_DEBUG is turned off
	if ( $hook == 'give_forms_page_give-settings' ) {
		wp_register_script( 'give_email_reports_admin_js', GIVE_EMAIL_REPORTS_URL . '/assets/js/admin.js', array( 'jquery' ) );
		wp_enqueue_script( 'give_email_reports_admin_js' );

	}
}

add_action( 'admin_enqueue_scripts', 'give_email_reports_admin_scripts', 100 );
