<?php

/**
 * Class Give_Email_Reports_Settings
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
		add_action( 'init', array( $this, 'schedule_daily_email' ) );
		add_action( 'init', array( $this, 'schedule_weekly_email' ) );
		add_action( 'init', array( $this, 'schedule_monthly_email' ) );

		// Remove from cron if plugin is deactivated.
		register_deactivation_hook( __FILE__, array( $this, 'unschedule_emails' ) );

		$this->report_choices = give_get_option( 'email_report_emails' );

		//Send emails
		add_action( 'give_email_reports_daily_email', array( $this, 'send_daily_email' ) );
		add_action( 'give_email_reports_weekly_email', array( $this, 'send_weekly_email' ) );

	}

	/**
	 * Triggers the daily sales report email generation and sending.
	 *
	 * Send the daily email when the cron event triggers the action.
	 */
	public function send_daily_email() {

		// $message will be rendered during give_email_message filter
		$message = '';

		//Clear out the email template before we send the email.
		add_action( 'give_email_send_before', 'give_email_reports_change_email_template' );

		Give()->emails->html    = true;
		Give()->emails->heading = __( 'Daily Donation Report', 'give-email-reports' ) . '<br>' . get_bloginfo( 'name' );
		Give()->emails->send( give_get_admin_notice_emails(), sprintf( __( 'Daily Donation Report for %1$s', 'give-email-reports' ), get_bloginfo( 'name' ) ), $message );

	}

	/**
	 * Triggers the daily sales report email generation and sending.
	 *
	 * Send the daily email when the cron event triggers the action.
	 */
	public function send_weekly_email() {

		// $message will be rendered during give_email_message filter
		$message = '';

		//Clear out the email template before we send the email.
		add_action( 'give_email_send_before', 'give_email_reports_change_email_template' );

		Give()->emails->html    = true;
		Give()->emails->heading = __( 'Weekly Donation Report', 'give-email-reports' ) . '<br>' . get_bloginfo( 'name' );
		Give()->emails->send( give_get_admin_notice_emails(), sprintf( __( 'Weekly Donation Report for %1$s', 'give-email-reports' ), get_bloginfo( 'name' ) ), $message );

	}


	/**
	 * Unschedule the cron job for the daily email if the plugin is deactivated.
	 */
	public function unschedule_emails() {
		wp_clear_scheduled_hook( 'give_email_reports_daily_email' );
		wp_clear_scheduled_hook( 'give_email_reports_weekly_email' );
		wp_clear_scheduled_hook( 'give_email_reports_monthly_email' );
	}

	/**
	 * Schedule the daily email report
	 *
	 * Pass the selected setting in the settings panel, default to 18:00 local time
	 *
	 * @return bool
	 */
	public function schedule_daily_email() {

		//Only proceed if daily email is enabled.
		if ( empty( $this->report_choices ) || is_array( $this->report_choices ) && ! in_array( 'daily', $this->report_choices ) ) {
			//Remove any schedule cron jobs if option is disabled.
			wp_clear_scheduled_hook( 'give_email_reports_daily_email' );

			return false;
		}

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

		return true;
	}

	/**
	 * Schedule the weekly email report email.
	 *
	 * @return bool
	 */
	public function schedule_weekly_email() {

		//Only proceed if daily email is enabled.
		if ( empty( $this->report_choices ) || is_array( $this->report_choices ) && ! in_array( 'weekly', $this->report_choices ) ) {
			//Remove any schedule cron jobs if option is disabled.
			wp_clear_scheduled_hook( 'give_email_reports_weekly_email' );

			return false;
		}

		//Ensure the cron isn't already scheduled and constant isn't set.
		if ( ! wp_next_scheduled( 'give_email_reports_weekly_email' ) && ! defined( 'GIVE_DISABLE_EMAIL_REPORTS' ) ) {

			$weekly_option = give_get_option( 'give_email_reports_weekly_email_delivery_time' );

			$days = $this->get_week_days();

			$local_time = strtotime( "this {$days[ $weekly_option['day'] ]} T{$weekly_option['time']}", current_time('timestamp') );
			$gmt_time   = get_gmt_from_date( date( 'Y-m-d h:i:s', $local_time ), 'U' );

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
	 * @return bool
	 */
	public function schedule_monthly_email() {


		//Only proceed if monthly email is enabled.
		if ( empty( $this->report_choices ) || is_array( $this->report_choices ) && ! in_array( 'monthly', $this->report_choices ) ) {
			//Remove any schedule cron jobs if option is disabled.
			wp_clear_scheduled_hook( 'give_email_reports_monthly_email' );

			return false;
		}

		//Ensure the cron isn't already scheduled and constant isn't set
		if ( ! wp_next_scheduled( 'give_email_reports_monthly_email' ) && ! defined( 'GIVE_DISABLE_EMAIL_REPORTS' ) ) {

			$timezone         = get_option( 'timezone_string' );
			$timezone_string  = ! empty( $timezone ) ? $timezone : 'UTC';
			$target_time_zone = new DateTimeZone( $timezone_string );
			$date_time        = new DateTime( 'now', $target_time_zone );

			$monthly_time = give_get_option( 'give_email_reports_monthly_email_delivery_time', 1800 );

			$time = strtotime( $monthly_time['time'] . 'GMT' . $date_time->format( 'P' ), current_time( 'timestamp' ) );

			wp_schedule_event(
				$time,
				'monthly',
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
	public function get_week_days(){
		//Days.
		return array(
			'0' => 'Sunday',
			'1' => 'Monday',
			'2' => 'Tuesday',
			'3' => 'Wednesday',
			'4' => 'Thursday',
			'5' => 'Friday',
			'6' => 'Saturday',
			'7' => 'Sunday'
		);
	}

}

new Give_Email_Cron();