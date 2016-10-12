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

	// Use minified libraries if SCRIPT_DEBUG is turned off.
	if ( $hook == 'give_forms_page_give-settings' ) {

		/**
		 * Scripts.
		 */
		wp_register_script( 'give_email_reports_admin_js', GIVE_EMAIL_REPORTS_URL . '/assets/js/admin.js', array( 'jquery' ) );
		wp_enqueue_script( 'give_email_reports_admin_js' );

		/**
		 * Styles.
		 */
		wp_register_style( 'give_email_reports_admin_css', GIVE_EMAIL_REPORTS_URL . '/assets/css/admin.css' );
		wp_enqueue_style( 'give_email_reports_admin_css' );
	}
}

add_action( 'admin_enqueue_scripts', 'give_email_reports_admin_scripts', 100 );
