<?php
/**
 * Plugin Name:     Give - Email Reports
 * Plugin URI:      https://givewp.com/addons/email-reports/
 * Description:     Receive comprehensive donations reports via email.
 * Version:         1.1.2
 * Author:          WordImpress
 * Author URI:      https://wordimpress.com
 * Text Domain:     give-email-reports
 */


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin version.
if ( ! defined( 'GIVE_EMAIL_REPORTS_VERSION' ) ) {
	define( 'GIVE_EMAIL_REPORTS_VERSION', '1.1.2' );
}

// Min. Give Core version.
if ( ! defined( 'GIVE_EMAIL_REPORTS_MIN_GIVE_VERSION' ) ) {
	define( 'GIVE_EMAIL_REPORTS_MIN_GIVE_VERSION', '2.1.7' );
}

// Plugin path.
if ( ! defined( 'GIVE_EMAIL_REPORTS_FILE' ) ) {
	define( 'GIVE_EMAIL_REPORTS_FILE', __FILE__ );
}

// Plugin path.
if ( ! defined( 'GIVE_EMAIL_REPORTS_DIR' ) ) {
	define( 'GIVE_EMAIL_REPORTS_DIR', plugin_dir_path( GIVE_EMAIL_REPORTS_FILE ) );
}

// Plugin URL.
if ( ! defined( 'GIVE_EMAIL_REPORTS_URL' ) ) {
	define( 'GIVE_EMAIL_REPORTS_URL', plugin_dir_url( GIVE_EMAIL_REPORTS_FILE ) );
}

// Basename
if ( ! defined( 'GIVE_EMAIL_REPORTS_BASENAME' ) ) {
	define( 'GIVE_EMAIL_REPORTS_BASENAME', plugin_basename( GIVE_EMAIL_REPORTS_FILE ) );
}

if ( ! class_exists( 'Give_Email_Reports' ) ) {

	/**
	 * Main Give_Email_Reports class.
	 *
	 * @since       1.0
	 */
	class Give_Email_Reports {

		/**
		 * @var         Give_Email_Reports $instance The one true Give_Email_Reports.
		 *
		 * @since       1.0
		 */
		private static $instance;

		/**
		 * Get active instance.
		 *
		 * @access      public
		 * @since       1.0
		 * @return      object self::$instance The one true Give_Email_Reports
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new Give_Email_Reports();
				self::$instance->load_textdomain();
				self::$instance->includes();
				self::$instance->hooks();
			}

			return self::$instance;
		}

		/**
		 * Include necessary files.
		 *
		 * @access      private
		 * @since       1.0
		 * @return      bool
		 */
		private function includes() {

			require_once GIVE_EMAIL_REPORTS_DIR . 'includes/give-email-reports-activation.php';

			if ( ! class_exists( 'Give' ) ) {
				return false;
			}

			require_once GIVE_EMAIL_REPORTS_DIR . 'includes/upgrades.php';
			require_once GIVE_EMAIL_REPORTS_DIR . 'includes/actions.php';
			require_once GIVE_EMAIL_REPORTS_DIR . 'includes/class-settings.php';
			require_once GIVE_EMAIL_REPORTS_DIR . 'includes/class-email-cron.php';
			require_once GIVE_EMAIL_REPORTS_DIR . 'includes/functions.php';
			require_once GIVE_EMAIL_REPORTS_DIR . 'includes/scripts.php';
		}

		/**
		 * Run action and filter hooks.
		 *
		 * @access      private
		 * @since       1.0
		 * @return      void
		 */
		private function hooks() {

			// Render the email report preview.
			add_filter( 'give_template_paths', array( $this, 'add_template_paths' ) );
			add_action( 'template_redirect', array( $this, 'report_preview' ) );
			add_filter( 'give_email_templates', array( $this, 'add_email_report_template' ) );
			add_filter( 'give_email_content_type', array( $this, 'change_email_content_type' ), 10, 2 );

			// Handle licensing.
			if ( class_exists( 'Give_License' ) ) {
				new Give_License( GIVE_EMAIL_REPORTS_FILE, 'Email Reports', GIVE_EMAIL_REPORTS_VERSION, 'WordImpress' );
			}
		}


		/**
		 * Internationalization.
		 *
		 * @access      public
		 * @since       1.0
		 * @return      void
		 */
		public function load_textdomain() {

			// Set filter for language directory
			$lang_dir = GIVE_EMAIL_REPORTS_DIR . '/languages/';
			$lang_dir = apply_filters( 'give_email_reports_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'give-email-reports' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'give-email-reports', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/give-email-reports/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/give-email-reports/ folder
				load_textdomain( 'give-email-reports', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/give-email-reports/languages/ folder
				load_textdomain( 'give-email-reports', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'give-email-reports', false, $lang_dir );
			}
		}

		/**
		 * Add the custom template path for the email reporting templates.
		 *
		 * @param array $file_paths priority-based paths to check for templates
		 *
		 * @return mixed
		 */
		public function add_template_paths( $file_paths ) {
			$file_paths[20] = trailingslashit( GIVE_EMAIL_REPORTS_DIR ) . 'templates/';

			return $file_paths;
		}

		/**
		 * Add email report template.
		 *
		 * @param $templates
		 *
		 * @return mixed
		 */
		public function add_email_report_template( $templates ) {

			$templates['report'] = __( 'Email Report Template', 'give-email-reports' );

			return $templates;
		}

		/**
		 * Change email content type.
		 *
		 * @param $content_type
		 * @param $class
		 *
		 * @return string
		 */
		public function change_email_content_type( $content_type, $class ) {
			return 'text/html';
		}


		/**
		 * Displays the email preview.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function report_preview() {

			// Sanity check: need the following vars to get started.
			if ( empty( $_GET['give_action'] ) || empty( $_GET['report'] ) ) {
				return;
			}

			if ( 'preview_email_report' !== $_GET['give_action'] ) {
				return;
			}

			if ( ! current_user_can( 'manage_give_settings' ) ) {
				return;
			}

			// $message will be rendered during give_email_message filter.
			ob_start();
			give_get_template_part( 'emails/body-report-' . $_GET['report'], Give()->emails->get_template(), true );
			$message = ob_get_clean();

			// Swap out the email template before we send the email.
			add_action( 'give_email_header', 'give_email_reports_change_email_template' );

			Give()->emails->html    = true;
			Give()->emails->heading = sprintf( __( '%s Donations Report', 'give-email-reports' ), ucfirst( $_GET['report'] ) ) . '<br>' . get_bloginfo( 'name' );

			echo Give()->emails->build_email( $message );

			exit;

		}
	}
}// End if().

/**
 * The main function responsible for returning the one true Give_Email_Reports instance to functions everywhere.
 *
 * @since       1.0
 *
 * @return object Give_Email_Reports
 */
function Give_Email_Reports_load() {
	return Give_Email_Reports::instance();
}

add_action( 'plugins_loaded', 'Give_Email_Reports_load' );

/**
 * This file is included outside the `Give_Email_Reports` class because during
 * deactivation of Give Core, it will also deactivate Give Email Reports plugin
 * and the ger_delete_all_form_scheduled() function is dependent on the below file
 * which runs on the deactivation hook.
 */
require_once GIVE_EMAIL_REPORTS_DIR . 'includes/give-independent-functions.php';

/**
 * Unschedule the cron job for the daily email if the plugin is deactivated.
 *
 * @since 1.0
 */
function give_email_reports_unschedule_emails() {
	wp_clear_scheduled_hook( 'give_email_reports_daily_email' );
	wp_clear_scheduled_hook( 'give_email_reports_weekly_email' );
	wp_clear_scheduled_hook( 'give_email_reports_monthly_email' );

	// delete all scheduled for form.
	ger_delete_all_form_scheduled();
}

// Remove from cron if plugin is deactivated.
register_deactivation_hook( GIVE_EMAIL_REPORTS_FILE, 'give_email_reports_unschedule_emails' );
