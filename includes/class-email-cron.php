<?php
/**
 * Email Cron Management.
 *
 * @package    Give-Email-Reports
 * @subpackage Classes/Give_Email_Cron
 * @copyright  Copyright (c) 2016, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @since      1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

		add_action( 'give_post_process_give_forms_meta', array( $this, 'schedule_form_email' ), 20, 1 );

		add_action( 'before_delete_post', array( $this, 'before_delete_post' ), 20, 1 );
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
				if ( is_array( $timestamps ) && in_array( $hook, $timestamps, true ) ) {
					$status = true;
					break;
				}
			}
		}

		return $status;
	}

	/**
	 * Delete scheduled hook once donation form is deleted
	 *
	 * @since 1.2
	 *
	 * @param int $form_id Donation Form id.
	 */
	public function before_delete_post( $form_id ) {
		if( 'give_forms' === get_post_type( $form_id ) ) {
			ger_clear_form_cron( $form_id );
		}
	}

	/**
	 * Schedule the email report for Donation Form
	 *
	 * @since 1.2
	 *
	 * @param int $form_id Donation Form id.
	 */
	public function schedule_form_email( $form_id ) {

		$email_report = give_is_setting_enabled( give_get_meta( $form_id, '_give_email_report_options', true, 'disabled' ) );

		/**
		 * Check for daily email.
		 */
		$is_active       = give_is_setting_enabled( Give_Email_Notification::get_instance( 'daily-report' )->get_notification_status( $form_id ) );
		$daily_cron_name = 'give_email_reports_daily_per_form';
		if ( $is_active && $email_report ) {
			if ( ! wp_next_scheduled( $daily_cron_name, array( 'form_id' => $form_id ) ) && ! defined( 'GIVE_DISABLE_EMAIL_REPORTS' ) ) {
				$time       = give_get_meta( $form_id, '_give_email_reports_daily_email_delivery_time', true, 1800 );
				$local_time = strtotime( "T{$time}", current_time( 'timestamp' ) );
				$gmt_time   = get_gmt_from_date( date( 'Y-m-d H:i:s', $local_time ), 'U' );
				wp_schedule_event( $gmt_time, 'daily', $daily_cron_name, array( 'form_id' => $form_id ) );
			}
		} else {
			// Remove any schedule cron jobs if option is disabled.
			wp_clear_scheduled_hook( $daily_cron_name, array( 'form_id' => $form_id ) );
		}

		/**
		 * Check for weekly email.
		 */
		$is_active        = give_is_setting_enabled( Give_Email_Notification::get_instance( 'weekly-report' )->get_notification_status( $form_id ) );
		$weekly_cron_name = 'give_email_reports_weekly_per_form';
		if ( $is_active && $email_report ) {
			if ( ! wp_next_scheduled( $weekly_cron_name, array( 'form_id' => $form_id ) ) && ! defined( 'GIVE_DISABLE_EMAIL_REPORTS' ) ) {

				$time = give_get_meta( $form_id, '_give_email_reports_weekly_email_delivery_time', true, 1800 );

				// Need $weekly option set to continue.
				if ( empty( $time ) ) {
					return;
				}

				$days       = ger_get_week_days();
				$local_time = strtotime( "this {$days[ $time['day'] ]} T{$time['time']}", current_time( 'timestamp' ) );
				$gmt_time   = get_gmt_from_date( date( 'Y-m-d H:i:s', $local_time ), 'U' );

				wp_schedule_event( $gmt_time, 'weekly', $weekly_cron_name, array( 'form_id' => $form_id ) );
			}
		} else {
			// Remove any schedule cron jobs if option is disabled.
			wp_clear_scheduled_hook( $weekly_cron_name, array( 'form_id' => $form_id ) );
		}

		/**
		 * Check for monthly email.
		 */
		$is_active         = give_is_setting_enabled( Give_Email_Notification::get_instance( 'monthly-report' )->get_notification_status( $form_id ) );
		$monthly_cron_name = 'give_email_reports_monthly_per_form';
		if ( $is_active && $email_report ) {

			if ( ! wp_next_scheduled( $monthly_cron_name, array( 'form_id' => $form_id ) ) && ! defined( 'GIVE_DISABLE_EMAIL_REPORTS' ) ) {

				$monthly = give_get_meta( $form_id, '_give_email_reports_monthly_email_delivery_time', true, 1800 );

				// Must have $monthly to continue.
				if ( empty( $monthly ) ) {
					return;
				}

				$local_time = strtotime( "{$monthly['day']} day of this month T{$monthly['time']}", current_time( 'timestamp' ) );

				if ( current_time( 'timestamp' ) > $local_time ) {
					$local_time = strtotime( "{$monthly['day']} day of next month T{$monthly['time']}", current_time( 'timestamp' ) );
				}

				$gmt_time = get_gmt_from_date( date( 'Y-m-d H:i:s', $local_time ), 'U' );
				wp_schedule_single_event( $gmt_time, $monthly_cron_name, array( 'form_id' => $form_id ) );
			}
		} else {
			// Remove any schedule cron jobs if option is disabled.
			wp_clear_scheduled_hook( $monthly_cron_name, array( 'form_id' => $form_id ) );
		}
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
		// Bailout.
		if( ! Give_Admin_Settings::is_setting_page( 'emails', 'daily-report' ) ){
			return false;
		}

		$is_active = give_is_setting_enabled( Give_Email_Notification::get_instance('daily-report')->get_notification_status() );

		//Only proceed if daily email is enabled.
		if ( ! $is_active ) {
			//Remove any schedule cron jobs if option is disabled.
			wp_clear_scheduled_hook( 'give_email_reports_daily_email' );

			return false;
		}

		if ( ! wp_next_scheduled( 'give_email_reports_daily_email' ) && ! defined( 'GIVE_DISABLE_EMAIL_REPORTS' ) ) {

			$time = isset( $value['give_email_reports_daily_email_delivery_time'] ) ? $value['give_email_reports_daily_email_delivery_time'] : 1900;

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
		// Bailout.
		if( ! Give_Admin_Settings::is_setting_page( 'emails', 'weekly-report' ) ){
			return false;
		}

		$is_active = give_is_setting_enabled( Give_Email_Notification::get_instance('weekly-report')->get_notification_status() );

		//Only proceed if daily email is enabled.
		if ( ! $is_active ) {
			//Remove any schedule cron jobs if option is disabled.
			wp_clear_scheduled_hook( 'give_email_reports_weekly_email' );

			return false;
		}

		// Ensure the cron isn't already scheduled and constant isn't set.
		if ( ! wp_next_scheduled( 'give_email_reports_weekly_email' ) && ! defined( 'GIVE_DISABLE_EMAIL_REPORTS' ) ) {

			$weekly_option = isset( $value['give_email_reports_weekly_email_delivery_time'] ) ? $value['give_email_reports_weekly_email_delivery_time'] : array( 'day' => 0, 'time' => 1900 );
			$days          = ger_get_week_days();

			// Need $weekly option set to continue.
			if ( empty( $weekly_option ) ) {
				return false;
			}

			$local_time = strtotime( "this {$days[ $weekly_option['day'] ]} T{$weekly_option['time']}", current_time( 'timestamp' ) );
			$gmt_time   = get_gmt_from_date( date( 'Y-m-d H:i:s', $local_time ), 'U' );

			// Schedule the cron!
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
	 * @param array     $value
	 * @param $option
	 *
	 * @return bool
	 */
	public function schedule_monthly_email( $old_value, $value, $option ) {
		// Bailout.
		if( ! Give_Admin_Settings::is_setting_page( 'emails', 'monthly-report' ) ){
			return false;
		}

		$is_active = give_is_setting_enabled( Give_Email_Notification::get_instance('monthly-report')->get_notification_status() );

		//Only proceed if monthly email is enabled.
		if ( ! $is_active ) {
			//Remove any schedule cron jobs if option is disabled.
			wp_clear_scheduled_hook( 'give_email_reports_monthly_email' );

			return false;
		}

		// Ensure the cron isn't already scheduled and constant isn't set.
		if ( ! $this->is_next_scheduled( 'give_email_reports_monthly_email' ) && ! defined( 'GIVE_DISABLE_EMAIL_REPORTS' ) ) {

			$monthly = isset( $value['give_email_reports_monthly_email_delivery_time'] ) ? $value['give_email_reports_monthly_email_delivery_time'] : array( 'day' => 'first', 'time' => 1900 );

			// Must have $monthly to continue.
			if ( empty( $monthly ) ) {
				return false;
			}

			$local_time = strtotime( "{$monthly['day']} day of this month T{$monthly['time']}", current_time( 'timestamp' ) );

			if ( current_time( 'timestamp' ) > $local_time ) {
				$local_time = strtotime( "{$monthly['day']} day of next month T{$monthly['time']}", current_time( 'timestamp' ) );
			}

			$gmt_time = get_gmt_from_date( date( 'Y-m-d H:i:s', $local_time ), 'U' );

			// Schedule cron.
			wp_schedule_single_event(
				$gmt_time,
				'give_email_reports_monthly_email'
			);
		}

		return true;
	}

	/**
	 * Get list of weekdays.
	 * @deprecated 1.1.4
	 *
	 * @return array
	 */
	public function get_week_days() {
		give_doing_it_wrong( __FUNCTION__, __( 'Use ger_get_week_days function instead', 'give-email-reports' ), '1.1.4' );

		// Days.
		return ger_get_week_days();
	}
}

new Give_Email_Cron();
