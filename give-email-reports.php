<?php
/**
 * Plugin Name:     Give - Email Reports
 * Plugin URI:      https://givewp.com/addons/email-reports/
 * Description:     Receive comprehensive donations reports via email.
 * Version:         1.1.4
 * Author:          GiveWP
 * Author URI:      https://wordimpress.com
 * Text Domain:     give-email-reports
 */


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin version.
if ( ! defined( 'GIVE_EMAIL_REPORTS_VERSION' ) ) {
	define( 'GIVE_EMAIL_REPORTS_VERSION', '1.1.4' );
}

// Min. Give Core version.
if ( ! defined( 'GIVE_EMAIL_REPORTS_MIN_GIVE_VERSION' ) ) {
	define( 'GIVE_EMAIL_REPORTS_MIN_GIVE_VERSION', '2.4.7' );
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
		 * Notices (array)
		 *
		 * @since 1.0
		 *
		 * @var array
		 */
		public $notices = array();

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
				self::$instance->setup();
			}

			return self::$instance;
		}

		/**
		 * Setup Give Email Report.
		 *
		 * @since  1.1.3
		 * @access private
		 */
		private function setup() {
			add_action( 'give_init', array( $this, 'init' ), 10 );
			add_action( 'admin_init', array( $this, 'check_environment' ), 999 );
			add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
		}

		/**
		 * Init the plugin after plugins_loaded so environment variables are set.
		 *
		 * @since 1.1.3
		 */
		public function init() {

			if ( ! $this->get_environment_warning() ) {
				return;
			}

			self::$instance->load_textdomain();
			self::$instance->includes();
			self::$instance->hooks();
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

		/**
		 * Check plugin environment.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return bool
		 */
		public function check_environment() {
			// Flag to check whether plugin file is loaded or not.
			$is_working = true;

			// Load plugin helper functions.
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}

			/* Check to see if Give is activated, if it isn't deactivate and show a banner. */
			// Check for if give plugin activate or not.
			$is_give_active = defined( 'GIVE_PLUGIN_BASENAME' ) ? is_plugin_active( GIVE_PLUGIN_BASENAME ) : false;

			if ( empty( $is_give_active ) ) {
				// Show admin notice.
				$this->add_admin_notice( 'prompt_give_activate', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> plugin installed and activated for Give - Email Reports to activate.', 'give-email-reports' ), 'https://givewp.com' ) );
				$is_working = false;
			}

			return $is_working;
		}

		/**
		 * Check plugin for Give environment.
		 *
		 * @since  1.1.3
		 * @access public
		 *
		 * @return bool
		 */
		public function get_environment_warning() {
			// Flag to check whether plugin file is loaded or not.
			$is_working = true;

			// Verify dependency cases.
			if (
				defined( 'GIVE_VERSION' )
				&& version_compare( GIVE_VERSION, GIVE_EMAIL_REPORTS_MIN_GIVE_VERSION, '<' )
			) {

				/* Min. Give. plugin version. */
				// Show admin notice.
				$this->add_admin_notice( 'prompt_give_incompatible', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> core version %s for the Give - Email Reports add-on to activate.', 'give-email-reports' ), 'https://givewp.com', GIVE_EMAIL_REPORTS_MIN_GIVE_VERSION ) );
				$is_working = false;
			}

			return $is_working;
		}

		/**
		 * Allow this class and other classes to add notices.
		 *
		 * @since 1.1.3
		 *
		 * @param $slug
		 * @param $class
		 * @param $message
		 */
		public function add_admin_notice( $slug, $class, $message ) {
			$this->notices[ $slug ] = array(
				'class'   => $class,
				'message' => $message,
			);
		}

		/**
		 * Display admin notices.
		 *
		 * @since 1.1.3
		 */
		public function admin_notices() {

			$allowed_tags = array(
				'a'      => array(
					'href'  => array(),
					'title' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'br'     => array(),
				'em'     => array(),
				'span'   => array(
					'class' => array(),
				),
				'strong' => array(),
			);

			foreach ( (array) $this->notices as $notice_key => $notice ) {
				echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
				echo wp_kses( $notice['message'], $allowed_tags );
				echo '</p></div>';
			}

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
Give_Email_Reports_load();

/**
 * This file is included outside the `Give_Email_Reports` class because during
 * deactivation of Give Core, it will also deactivate Give Email Reports plugin
 * and the ger_delete_all_form_scheduled() function is dependent on the below file
 * which runs on the deactivation hook.
 */
require_once GIVE_EMAIL_REPORTS_DIR . 'includes/give-independent-functions.php';

/**
 * Unschedule the cron job for the report email if the plugin is deactivated.
 * Note: only for internal use
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

/**
 * Schedule the cron job for the daily report email if the plugin is activated.
 * Note: only for internal use
 *
 * @since 1.1.4
 */
function give_email_reports_schedule_emails() {
	if ( ! function_exists( 'ger_get_week_days' ) ) {
		require_once GIVE_EMAIL_REPORTS_DIR . 'includes/functions.php';
	}

	// Setup initial cron jobs.

	$cron_array = array(
		// Daily.
		array(
			'setting'  => give_get_option( 'give_email_reports_daily_email_delivery_time', 1900 ),
			'interval' => 'daily',
			'hook'     => 'give_email_reports_daily_email',
		),
		// Weekly.
		array(
			'setting'  => give_get_option( 'give_email_reports_weekly_email_delivery_time', array(
				'day'  => 0,
				'time' => 1900,
			) ),
			'interval' => 'weekly',
			'hook'     => 'give_email_reports_weekly_email',
		),
		// Monthly.
		array(
			'setting'  => give_get_option( 'give_email_reports_monthly_email_delivery_time', array(
				'day'  => 'first',
				'time' => 1900,
			) ),
			'interval' => 'monthly',
			'hook'     => 'give_email_reports_monthly_email',
		),
	);

	foreach ( $cron_array as $cron ) {
		if ( ! give_is_setting_enabled( give_get_option( "{$cron['interval']}-report_notification", 'enabled' ) ) ) {
			continue;
		}

		if ( false !== strpos( $cron['hook'], 'monthly' ) ) {
			$local_time = strtotime( "{$cron['setting']['day']} day of this month T{$cron['setting']['time']}", current_time( 'timestamp' ) );

			if ( current_time( 'timestamp' ) > $local_time ) {
				$local_time = strtotime( "{$cron['setting']['day']} day of next month T{$cron['setting']['time']}", current_time( 'timestamp' ) );
			}

			$gmt_time = get_gmt_from_date( date( 'Y-m-d H:i:s', $local_time ), 'U' );

			// Schedule cron.
			wp_schedule_single_event(
				$gmt_time,
				'give_email_reports_monthly_email'
			);

		} else {
			if ( false !== strpos( $cron['hook'], 'weekly' ) ) {
				$days     = ger_get_week_days();
				$time_str = "this {$days[ $cron['setting']['day'] ]} T{$cron['setting']['time']}";
			}else{
				$time_str = "T{$cron['setting']}";
			}

			$local_time = strtotime( $time_str, current_time( 'timestamp' ) );
			$gmt_time   = get_gmt_from_date( date( 'Y-m-d H:i:s', $local_time ), 'U' );

			wp_schedule_event(
				$gmt_time,
				$cron['interval'],
				$cron['hook']
			);
		}

		give_update_option( "give_email_reports_{$cron['interval']}_email_delivery_time", $cron['setting'] );

	}

}

register_activation_hook( GIVE_EMAIL_REPORTS_FILE, 'give_email_reports_schedule_emails' );
