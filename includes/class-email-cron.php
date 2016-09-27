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

		// Schedule cron event for various email.
		add_action( 'wp', array( $this, 'schedule_daily_email' ) );
		add_action( 'wp', array( $this, 'schedule_weekly_email' ) );
		add_action( 'wp', array( $this, 'schedule_monthly_email' ) );

		// Remove from cron if plugin is deactivated.
		register_deactivation_hook( __FILE__, array( $this, 'unschedule_emails' ) );

		$this->report_choices = give_get_option( 'email_report_emails' );

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
		if ( ! in_array( 'daily', $this->report_choices ) ) {
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
		if ( ! in_array( 'weekly', $this->report_choices ) ) {
			return false;
		}

		//Ensure the cron isn't already scheduled and constant isn't set
		if ( ! wp_next_scheduled( 'give_email_reports_weekly_email' ) && ! defined( 'GIVE_DISABLE_EMAIL_REPORTS' ) ) {

			$timezone         = get_option( 'timezone_string' );
			$timezone_string  = ! empty( $timezone ) ? $timezone : 'UTC';
			$target_time_zone = new DateTimeZone( $timezone_string );
			$date_time        = new DateTime( 'now', $target_time_zone );

			$time = strtotime( give_get_option( 'give_email_reports_weekly_email_delivery_time', 1800 ) . 'GMT' . $date_time->format( 'P' ), current_time( 'timestamp' ) );

			wp_schedule_event(
				$time,
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
		if ( ! in_array( 'monthly', $this->report_choices ) ) {
			return false;
		}

		//Ensure the cron isn't already scheduled and constant isn't set
		if ( ! wp_next_scheduled( 'give_email_reports_weekly_email' ) && ! defined( 'GIVE_DISABLE_EMAIL_REPORTS' ) ) {

			$timezone         = get_option( 'timezone_string' );
			$timezone_string  = ! empty( $timezone ) ? $timezone : 'UTC';
			$target_time_zone = new DateTimeZone( $timezone_string );
			$date_time        = new DateTime( 'now', $target_time_zone );

			$time = strtotime( give_get_option( 'give_email_reports_weekly_email_delivery_time', 1800 ) . 'GMT' . $date_time->format( 'P' ), current_time( 'timestamp' ) );

			wp_schedule_event(
				$time,
				'weekly',
				'give_email_reports_weekly_email'
			);
		}

		return true;
	}


}

new Give_Email_Cron();