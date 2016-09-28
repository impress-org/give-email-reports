<?php
/**
 * Scripts
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Load admin scripts
 *
 * @since       1.0
 * @global      array  $give_settings_page The slug for the Give settings page
 * @global      string $post_type The type of post that we are editing
 * @return      void
 */
function give_email_reports_admin_scripts( $hook ) {

	global $give_settings_page;

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	/**
	 * @todo        This block loads styles or scripts explicitly on the
	 *                GIVE settings page.
	 */

	if ( $hook == $give_settings_page ) {
		wp_enqueue_script( 'give_email_reports_admin_js', GIVE_EMAIL_REPORTS_URL . '/assets/js/admin' . $suffix . '.js', array( 'jquery' ) );
		wp_enqueue_style( 'give_email_reports_admin_css', GIVE_EMAIL_REPORTS_URL . '/assets/css/admin' . $suffix . '.css' );
	}
}

add_action( 'admin_enqueue_scripts', 'give_email_reports_admin_scripts', 100 );


/**
 * Load frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function give_email_reports_scripts( $hook ) {
	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_script( 'give_email_reports_js', GIVE_EMAIL_REPORTS_URL . '/assets/js/scripts' . $suffix . '.js', array( 'jquery' ) );
	wp_enqueue_style( 'give_email_reports_css', GIVE_EMAIL_REPORTS_URL . '/assets/css/styles' . $suffix . '.css' );
}

add_action( 'wp_enqueue_scripts', 'give_email_reports_scripts' );
