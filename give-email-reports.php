<?php
/**
 * Plugin Name:     Give - Email Reports
 * Plugin URI:      https://givewp.com/addons/email-reports/
 * Description:     Receive comprehensive donations reports via email.
 * Version:         1.0
 * Author:          WordImpress
 * Author URI:      https://wordimpress.com
 * Text Domain:     give-email-reports
 */


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
				self::$instance->setup_constants();
				self::$instance->load_textdomain();
				self::$instance->includes();
				self::$instance->hooks();
			}

			return self::$instance;
		}

		/**
		 * Setup plugin constants.
		 *
		 * @access      private
		 * @since       1.0
		 * @return      void
		 */
		private function setup_constants() {

			// Plugin version
			if ( ! defined( 'GIVE_EMAIL_REPORTS_VERSION' ) ) {
				define( 'GIVE_EMAIL_REPORTS_VERSION', '1.0' );
			}

			// Plugin path
			if ( ! defined( 'GIVE_EMAIL_REPORTS_DIR' ) ) {
				define( 'GIVE_EMAIL_REPORTS_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin URL
			if ( ! defined( 'GIVE_EMAIL_REPORTS_URL' ) ) {
				define( 'GIVE_EMAIL_REPORTS_URL', plugin_dir_url( __FILE__ ) );
			}

		}

		/**
		 * Include necessary files.
		 *
		 * @access      private
		 * @since       1.0
		 * @return      void
		 */
		private function includes() {
			require_once GIVE_EMAIL_REPORTS_DIR . 'includes/functions.php';
		}

		/**
		 * Run action and filter hooks.
		 *
		 * @access      private
		 * @since       1.0
		 * @return      void
		 */
		private function hooks() {

			// Register settings.
			add_filter( 'give_settings_emails', array( $this, 'settings' ), 1 );
			add_action( 'cmb2_render_email_report_preview', array( $this, 'add_email_report_preview' ), 10, 5 );

			// Render the email report preview.
			add_action( 'template_redirect', array( $this, 'give_email_reports_display_email_report_preview' ) );
			add_filter( 'give_template_paths', array( $this, 'add_template_paths' ) );
			add_filter( 'give_email_templates', array( $this, 'add_email_report_template' ) );
			add_filter( 'give_email_content_type', array( $this, 'change_email_content_type' ), 10, 2 );


			//@TODO: Check this
			add_filter( 'give_settings_sanitize', array( $this, 'sanitize_settings' ), 10, 2 );

			// Schedule cron event for daily email.
			add_action( 'wp', array( $this, 'schedule_daily_email' ) );

			// Remove from cron if plugin is deactivated.
			register_deactivation_hook( __FILE__, array( $this, 'unschedule_daily_email' ) );


			// Handle licensing.
			if ( class_exists( 'Give_License' ) ) {
				new Give_License( __FILE__, 'Give Email Reports', GIVE_EMAIL_REPORTS_VERSION, 'WordImpress' );
			}
		}

		/**
		 * Sanitize the values for the give_email_reports settings.
		 *
		 * @param $value
		 * @param $key
		 *
		 * @return int
		 */
		public function sanitize_settings( $value, $key ) {

			if ( $key == 'give_email_reports_daily_email_delivery_time' ) {

				$weekly_report = give_get_option( 'give_email_reports_daily_email_delivery_time' );

				if ( $weekly_report != $value ) {
					wp_clear_scheduled_hook( 'give_email_reports_daily_email' );
				}

				return intval( $value );
			}

			return $value;
		}

		/**
		 * Unschedule the cron job for the daily email if the plugin is deactivated.
		 */
		public function unschedule_daily_email() {
			return wp_clear_scheduled_hook( 'give_email_reports_daily_email' );
		}

		/**
		 * Schedule the daily email report in cron.mail_reports_display_email_report_preview
		 *
		 * Pass the selected setting in the settings panel, but default to 18:00 local time
		 */
		public function schedule_daily_email() {

			if ( ! wp_next_scheduled( 'give_email_reports_daily_email' ) && ! defined( 'GIVE_DISABLE_EMAIL_REPORTS' ) ) {

				$timezone         = get_option( 'timezone_string' );
				$timezone_string  = ! empty( $timezone ) ? $timezone : 'UTC';
				$target_time_zone = new DateTimeZone( $timezone_string );
				$date_time        = new DateTime( 'now', $target_time_zone );

				wp_schedule_event(
					strtotime( give_get_option( 'give_email_reports_daily_email_delivery_time', 1800 ) . 'GMT' . $date_time->format( 'P' ), current_time( 'timestamp' ) ),
					'daily',
					'give_email_reports_daily_email'
				);
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
		 */
		public function add_template_paths( $file_paths ) {
			$file_paths[20] = trailingslashit( plugin_dir_path( __FILE__ ) ) . 'templates/';

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
		 * @return string
		 */
		public function change_email_content_type( $content_type, $klass ) {
			return 'text/html';
		}

		/**
		 * Add settings.
		 *
		 * @access      public
		 * @since       1.0
		 *
		 * @param       array $settings The existing Give settings array
		 *
		 * @return      array The modified Give settings array
		 */
		public function settings( $settings ) {

			$new_settings = array(
				array(
					'id'   => 'give_email_reports_settings',
					'name' => __( 'Email Reports Settings', 'give-email-reports' ),
					'type' => 'give_title',
				),
				array(
					'id'   => 'email_reports_settings',
					'name' => __( 'Preview Report', 'give-email-reports' ),
					'desc' => '',
					'type' => 'email_report_preview'
				),
				array(
					'id'      => 'give_email_reports_daily_email_delivery_time',
					'name'    => __( 'Daily Email Delivery Time', 'give-email-reports' ),
					'desc'    => __( 'Select when you would like to receive your daily email report.', 'give-email-reports' ),
					'type'    => 'select',
					'options' => array(
						'1300' => __( '1:00 PM', 'give-email-reports' ),
						'1400' => __( '2:00 PM', 'give-email-reports' ),
						'1500' => __( '3:00 PM', 'give-email-reports' ),
						'1600' => __( '4:00 PM', 'give-email-reports' ),
						'1700' => __( '5:00 PM', 'give-email-reports' ),
						'1800' => __( '6:00 PM', 'give-email-reports' ),
						'1900' => __( '7:00 PM', 'give-email-reports' ),
						'2000' => __( '8:00 PM', 'give-email-reports' ),
						'2100' => __( '9:00 PM', 'give-email-reports' ),
						'2200' => __( '10:00 PM', 'give-email-reports' ),
						'2300' => __( '11:00 PM', 'give-email-reports' ),
					)
				),
			);

			return array_merge( $settings, $new_settings );
		}

		/**
		 * Give add email reports preview.
		 *
		 * @since 1.0
		 */
		public function add_email_report_preview() {
			ob_start();
			?>
			<a href="<?php echo esc_url( add_query_arg( array( 'give_action' => 'preview_email_report' ), home_url() ) ); ?>" class="button-secondary" target="_blank" title="<?php _e( 'Preview Email Report', 'give-email-reports' ); ?> "><?php _e( 'Preview Email Report', 'give-email-reports' ); ?></a>
			<?php
			echo ob_get_clean();
		}

		/**
		 * Displays the email preview.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function give_email_reports_display_email_report_preview() {

			if ( empty( $_GET['give_action'] ) ) {
				return;
			}

			if ( 'preview_email_report' !== $_GET['give_action'] ) {
				return;
			}

			if ( ! current_user_can( 'manage_give_settings' ) ) {
				return;
			}

			// $message will be rendered during give_email_message filter.
			$message = '';

			// Swap out the email template before we send the email.
			add_action( 'give_email_header', 'give_email_reports_change_email_template' );

			Give()->emails->html    = true;
			Give()->emails->heading = sprintf( __( 'Daily Donations Report – %1$s', 'give-email-reports' ), get_bloginfo( 'name' ) );

			echo Give()->emails->build_email( $message );

			exit;

		}

	}
}

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
