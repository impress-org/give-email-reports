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
 * @param string $hook Page hook id.
 *
 * @since       1.0
 *
 * @return      void
 */
function give_email_reports_admin_scripts( $hook ) {

	$load_script = false;

	if ( 'give_forms_page_give-settings' === $hook ) {
		$load_script = true;
	}

	if ( empty( $load_script ) && in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		$screen = get_current_screen();
		if ( is_object( $screen ) && 'give_forms' === $screen->post_type ) {
			$load_script = true;
		}
	}

	// Use minified libraries if SCRIPT_DEBUG is turned off.
	if ( $load_script ) {

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
