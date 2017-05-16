<?php

/**
 * Class Give_Email_Cron
 */
class Give_Email_Cron extends Give_Email_Reports {

	/**
	 * @var array
	 */
	public $report_choices;

	/**
	 * Give_Email_Reports_Settings constructor.
	 */
	public function __construct() {

		// Schedule cron event for various emails.
		add_action( 'update_option_give_settings', array( $this, 'schedule_daily_email' ), 10, 3 );
		add_action( 'update_option_give_settings', array( $this, 'schedule_weekly_email' ), 10, 3 );
		add_action( 'update_option_give_settings', array( $this, 'schedule_monthly_email' ), 10, 3 );

		//Send emails
		add_action( 'give_email_reports_daily_email', array( $this, 'send_daily_email' ) );
		add_action( 'give_email_reports_weekly_email', array( $this, 'send_weekly_email' ) );
		add_action( 'give_email_reports_monthly_email', array( $this, 'send_monthly_email' ) );

	}

	/**
	 * Reschedule monthly email.
	 *
	 * @return false|string
	 */
	private function reschedule_monthly_email() {
		$give_settings = give_get_settings();
		$monthly       = $give_settings['give_email_reports_monthly_email_delivery_time'];

		$local_time = strtotime( "{$monthly['day']} day of next month T{$monthly['time']}", current_time( 'timestamp' ) );
		$gmt_time   = get_gmt_from_date( date( 'Y-m-d H:i:s', $local_time ), 'U' );

		wp_schedule_single_event(
			$gmt_time,
			'give_email_reports_monthly_email'
		);
	}

	/**
	 * Get list of all scheduled cron.
	 *
	 * @return mixed
	 */
	private function _get_cron_array() {
		return get_option( 'cron' );
	}

	/**
	 * Check if monthly cron exist or not.
	 *
	 * @param string $hook Cron hook name.
	 *
	 * @return bool
	 */
	private function is_next_scheduled( $hook ) {
		$crons  = $this->_get_cron_array();
		$status = false;

		if ( ! empty( $crons ) ) {
			foreach ( $crons as $timestamps ) {
				if ( is_array( $timestamps ) && in_array( $hook, $timestamps ) ) {
					$status = true;
					break;
				}
			}
		}

		return $status;
	}

	/**
	 * Triggers the daily sales report email generation and sending.
	 *
	 * Send the daily email when the cron event triggers the action.
	 */
	public function send_daily_email() {

		//Clear out the email template before we send the email.
		add_action( 'give_email_send_before', 'give_email_reports_change_email_template' );

		Give()->emails->html    = true;
		Give()->emails->heading = __( 'Daily Donation Report', 'give-email-reports' ) . '<br>' . get_bloginfo( 'name' );

		$recipients = apply_filters( 'give_email_reports_recipients', give_get_admin_notice_emails(), 'daily' );

		// $message will be rendered during give_email_message filter.
		ob_start();
		give_get_template_part( 'emails/body-report-daily', Give()->emails->get_template(), true );
		$message = ob_get_clean();

		Give()->emails->send( $recipients, sprintf( __( 'Daily Donation Report for %1$s', 'give-email-reports' ), get_bloginfo( 'name' ) ), $message );

	}

	/**
	 * Triggers the weekly sales report email generation and sending.
	 *
	 * Send the daily email when the cron event triggers the action.
	 */
	public function send_weekly_email() {

		//Clear out the email template before we send the email.
		add_action( 'give_email_send_before', 'give_email_reports_change_email_template' );

		Give()->emails->html    = true;
		Give()->emails->heading = __( 'Weekly Donation Report', 'give-email-reports' ) . '<br>' . get_bloginfo( 'name' );

		$recipients = apply_filters( 'give_email_reports_recipients', give_get_admin_notice_emails(), 'weekly' );

		// $message will be rendered during give_email_message filter.
		ob_start();
		give_get_template_part( 'emails/body-report-weekly', Give()->emails->get_template(), true );
		$message = ob_get_clean();

		Give()->emails->send( $recipients, sprintf( __( 'Weekly Donation Report for %1$s', 'give-email-reports' ), get_bloginfo( 'name' ) ), $message );

	}

	/**
	 * Triggers the monthly sales report email generation and sending.
	 *
	 * Send the daily email when the cron event triggers the action.
	 */
	public function send_monthly_email() {

		//Clear out the email template before we send the email.
		add_action( 'give_email_send_before', 'give_email_reports_change_email_template' );

		Give()->emails->html    = true;
		Give()->emails->heading = __( 'Monthly Donation Report', 'give-email-reports' ) . '<br>' . get_bloginfo( 'name' );

		$recipients = apply_filters( 'give_email_reports_recipients', give_get_admin_notice_emails(), 'monthly' );

		// $message will be rendered during give_email_message filter.
		ob_start();
		give_get_template_part( 'emails/body-report-monthly', Give()->emails->get_template(), true );
		$message = ob_get_clean();

		Give()->emails->send( $recipients, sprintf( __( 'Monthly Donation Report for %1$s', 'give-email-reports' ), get_bloginfo( 'name' ) ), $message );

		// Reschedule monthly email.
		$this->reschedule_monthly_email();
	}


	/**
	 * Schedule the daily email report
	 *
	 * Pass the selected setting in the settings panel, default to 18:00 local time
	 *
	 * @param $old_value
	 * @param $value
	 * @param $option
	 *
	 * @return bool
	 */
	public function schedule_daily_email( $old_value, $value, $option ) {

		$report_choices = isset( $value['email_report_emails'] ) ? $value['email_report_emails'] : '';

		//Only proceed if daily email is enabled.
		if ( empty( $report_choices ) || is_array( $report_choices ) && ! in_array( 'daily', $report_choices ) ) {
			//Remove any schedule cron jobs if option is disabled.
			wp_clear_scheduled_hook( 'give_email_reports_daily_email' );

			return false;
		}

		if ( ! wp_next_scheduled( 'give_email_reports_daily_email' ) && ! defined( 'GIVE_DISABLE_EMAIL_REPORTS' ) ) {

			$time = isset( $value['give_email_reports_daily_email_delivery_time'] ) ? $value['give_email_reports_daily_email_delivery_time'] : 1800;

			$local_time = strtotime( "T{$time}", current_time( 'timestamp' ) );
			$gmt_time   = get_gmt_from_date( date( 'Y-m-d H:i:s', $local_time ), 'U' );

			wp_schedule_event(
				$gmt_time,
				'daily',
				'give_email_reports_daily_email'
			);
		}

		return true;
	}

	/**
	 * Schedule the weekly email report email.
	 *
	 * @param $old_value
	 * @param $value
	 * @param $option
	 *
	 * @return bool
	 */
	public function schedule_weekly_email( $old_value, $value, $option ) {

		$report_choices = isset( $value['email_report_emails'] ) ? $value['email_report_emails'] : '';

		//Only proceed if daily email is enabled.
		if ( empty( $report_choices ) || is_array( $report_choices ) && ! in_array( 'weekly', $report_choices ) ) {
			//Remove any schedule cron jobs if option is disabled.
			wp_clear_scheduled_hook( 'give_email_reports_weekly_email' );

			return false;
		}

		//Ensure the cron isn't already scheduled and constant isn't set.
		if ( ! wp_next_scheduled( 'give_email_reports_weekly_email' ) && ! defined( 'GIVE_DISABLE_EMAIL_REPORTS' ) ) {

			$weekly_option = isset( $value['give_email_reports_weekly_email_delivery_time'] ) ? $value['give_email_reports_weekly_email_delivery_time'] : '';
			$days          = $this->get_week_days();

			//Need $weekly option set to continue.
			if ( empty( $weekly_option ) ) {
				return false;
			}

			$local_time = strtotime( "this {$days[ $weekly_option['day'] ]} T{$weekly_option['time']}", current_time( 'timestamp' ) );
			$gmt_time   = get_gmt_from_date( date( 'Y-m-d H:i:s', $local_time ), 'U' );

			//Schedule the cron!
			wp_schedule_event(
				$gmt_time,
				'weekly',
				'give_email_reports_weekly_email'
			);
		}

		return true;
	}


	/**
	 * Schedule the monthly email report email.
	 *
	 * @param $old_value
	 * @param $value
	 * @param $option
	 *
	 * @return bool
	 */
	public function schedule_monthly_email( $old_value, $value, $option ) {

		$report_choices = isset( $value['email_report_emails'] ) ? $value['email_report_emails'] : '';

		//Only proceed if monthly email is enabled.
		if ( empty( $report_choices ) || is_array( $report_choices ) && ! in_array( 'monthly', $report_choices ) ) {
			//Remove any schedule cron jobs if option is disabled.
			wp_clear_scheduled_hook( 'give_email_reports_monthly_email' );

			return false;
		}

		//Ensure the cron isn't already scheduled and constant isn't set.
		if ( ! $this->is_next_scheduled( 'give_email_reports_monthly_email' ) && ! defined( 'GIVE_DISABLE_EMAIL_REPORTS' ) ) {

			$monthly = isset( $value['give_email_reports_monthly_email_delivery_time'] ) ? $value['give_email_reports_monthly_email_delivery_time'] : '';

			//Must have $monthly to continue.
			if ( empty( $monthly ) ) {
				return false;
			}

			$local_time = strtotime( "{$monthly['day']} day of this month T{$monthly['time']}", current_time( 'timestamp' ) );

			if ( current_time( 'timestamp' ) > $local_time ) {
				$local_time = strtotime( "{$monthly['day']} day of next month T{$monthly['time']}", current_time( 'timestamp' ) );
			}

			$gmt_time = get_gmt_from_date( date( 'Y-m-d H:i:s', $local_time ), 'U' );

			//Schedule cron.
			wp_schedule_single_event(
				$gmt_time,
				'give_email_reports_monthly_email'
			);
		}

		return true;
	}

	/**
	 * Get list of weekdays.
	 *
	 * @return array
	 */
	public function get_week_days() {
		//Days.
		return array(
			'0' => 'Sunday',
			'1' => 'Monday',
			'2' => 'Tuesday',
			'3' => 'Wednesday',
			'4' => 'Thursday',
			'5' => 'Friday',
			'6' => 'Saturday',
			'7' => 'Sunday',
		);
	}
}

new Give_Email_Cron();