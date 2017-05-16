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