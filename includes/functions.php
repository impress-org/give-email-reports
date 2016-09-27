<?php
/**
 * Helper Functions.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parse the template tags manually until parse_tags is supported outside of donation emails.
 *
 * @param   string     $message
 * @param  Give_Emails $class
 *
 * @return string
 */
function give_email_reports_render_email( $message, $class ) {

	if ( $class->get_template() === 'report' ) {
		$message = give_do_email_tags( $message, 0 );
	}

	return $message;
}

add_filter( 'give_email_message', 'give_email_reports_render_email', 10, 2 );

/**
 * Add email tags for reports.
 *
 * @param  $tags array
 *
 * @return      array
 */
function give_email_reports_add_email_tags( $tags ) {

	$report_tags = array(
		array(
			'tag'         => 'email_report_currency',
			'description' => __( 'Adds the currency setting for the site.', 'give-email-reports' ),
			'function'    => 'give_email_reports_currency'
		),
		array(
			'tag'         => 'email_report_letters_of_days_in_week',
			'description' => __( 'Adds the total amount donated for the day.', 'give-email-reports' ),
			'function'    => 'give_email_reports_letters_of_days_in_week'
		),
		array(
			'tag'         => 'email_report_daily_total',
			'description' => __( 'Adds the total amount donated for the day.', 'give-email-reports' ),
			'function'    => 'give_email_reports_daily_total'
		),
		array(
			'tag'         => 'email_report_daily_transactions',
			'description' => __( 'Adds the number of donations for the day.', 'give-email-reports' ),
			'function'    => 'give_email_reports_daily_transactions'
		),
		array(
			'tag'         => 'email_report_weekly_best_performing_forms',
			'description' => __( 'Adds the total amount donated for the week.', 'give-email-reports' ),
			'function'    => 'give_email_reports_weekly_best_performing_forms'
		),
		array(
			'tag'         => 'email_report_weekly_total',
			'description' => __( 'Adds the total amount donated for the week.', 'give-email-reports' ),
			'function'    => 'give_email_reports_weekly_total'
		),
		array(
			'tag'         => 'email_report_monthly_total',
			'description' => __( 'Adds the total amount donated for the month.', 'give-email-reports' ),
			'function'    => 'give_email_reports_monthly_total'
		),
		array(
			'tag'         => 'email_report_rolling_weekly_total',
			'description' => __( 'Adds the total amount donated for past seven days.', 'give-email-reports' ),
			'function'    => 'give_email_reports_rolling_weekly_total'
		),
		array(
			'tag'         => 'email_report_rolling_monthly_total',
			'description' => __( 'Adds the total amount donated for past thirty days.', 'give-email-reports' ),
			'function'    => 'give_email_reports_rolling_monthly_total'
		),
		array(
			'tag'         => 'email_report_cold_donation_forms',
			'description' => __( 'Displays under-performing donation forms and their last donate date.', 'give-email-reports' ),
			'function'    => 'give_email_reports_cold_donation_forms'
		),
	);

	return array_merge( $tags, $report_tags );

}

add_filter( 'give_email_tags', 'give_email_reports_add_email_tags' );

/**
 * Fetch six forms sorted by the furthest last donation date.
 *
 * @return string
 */
function give_email_reports_cold_donation_forms() {

	$args = array(
		'post_type'      => 'give_forms',
		'post_status'    => 'publish',
		'posts_per_page' => - 1,
	);

	$result = get_posts( $args );

	$last_donation_dates = array();

	$give_logs = new Give_Logging();

	if ( ! empty( $result ) ) {

		foreach ( $result as $form ) {

			$result = $give_logs->get_connected_logs( array(
				'post_parent'    => $form->ID,
				'log_type'       => 'sale',
				'posts_per_page' => 1
			) );

			if ( ! empty( $result ) ) {
				$last_donation_dates[ $form->post_title ] = $result[0]->post_date;
			}
		}

		if ( ! empty( $last_donation_dates ) ) {

			uasort( $last_donation_dates, 'give_email_reports_sort_cold_donation_forms' );
			$last_donation_dates = array_slice( $last_donation_dates, 0, 6, true );

			$color_prefix = 99;

			ob_start();
			echo '<ul>';
			foreach ( $last_donation_dates as $form => $date ):

				printf( '<li style="color: #%1$s0000; padding: 5px 0;"><span style="font-weight: bold;">%2$s</span> – Last donation <strong>%4$s ago</strong> on <strong>%3$s</strong></li>',
					$color_prefix,
					$form,
					date( 'F j, Y', strtotime( $date ) ),
					human_time_diff( strtotime( $date ) )
				);

				if ( $color_prefix > 11 ) {
					$color_prefix -= 11;
				}
			endforeach;
			echo '</ul>';

			return ob_get_clean();
		} else {
			return '<p>' . __( 'No donations found.', 'give-email-reports' ) . '</p>';
		}

	} else {
		return '<p>' . __( 'No donations found.', 'give-email-reports' ) . '</p>';
	}
}

/**
 * Sort the passed array based on the furthest donation date.
 *
 * @param  [type] $a [description]
 * @param  [type] $b [description]
 *
 * @return [type]    [description]
 */
function give_email_reports_sort_cold_donation_forms( $a, $b ) {
	return strtotime( $a ) - strtotime( $b );
}

/**
 * Returns the earnings amount for the past 7 days, including today.
 *
 * @return string
 */
function give_email_reports_rolling_weekly_total() {
	$stats = new Give_Payment_Stats();

	return give_currency_filter( give_format_amount( $stats->get_earnings( 0, '6 days ago 00:00', 'now' ) ) );

}

/**
 * Give Email reports monthly total.
 *
 * @return string
 */
function give_email_reports_rolling_monthly_total() {
	$stats = new Give_Payment_Stats();

	return give_currency_filter( give_format_amount( $stats->get_earnings( 0, '30 days ago 00:00', 'now' ) ) );

}

/**
 * Letter days of the week.
 *
 * @return array
 */
function give_email_reports_letters_of_days_in_week() {

	$letters_of_days_in_week = array();

	$timestamp = time();

	for ( $i = 0; $i < 7; $i ++ ) {
		$letters_of_days_in_week[] = substr( date( 'D', $timestamp ), 0, 1 );
		$timestamp -= 24 * 3600;
	}

	return $letters_of_days_in_week;
}

/**
 * Returns the currency symbol for the site.
 *
 * @return string
 */
function give_email_reports_currency() {
	return give_currency_filter( '' );
}

/**
 * Returns the total earnings for today.
 *
 * @return string
 */
function give_email_reports_daily_total() {
	$stats = new Give_Payment_Stats();

	return give_format_amount( $stats->get_earnings( 0, 'today', false ) );
}

/**
 * Returns the number of transactions for today.
 *
 * @return int
 */
function give_email_reports_daily_transactions() {
	$stats = new Give_Payment_Stats();

	return $stats->get_sales( false, 'today' );
}

/**
 * Gets the total earnings for the current week.
 *
 * @return string
 */
function give_email_reports_weekly_total() {
	$stats = new Give_Payment_Stats();

	return give_currency_filter( give_format_amount( $stats->get_earnings( 0, 'this_week' ) ) );
}

/**
 * Gets the total earnings for the current month
 *
 * @return string
 */
function give_email_reports_monthly_total() {
	$stats = new Give_Payment_Stats();

	return give_currency_filter( give_format_amount( $stats->get_earnings( 0, 'this_month' ) ) );
}

/**
 * Outputs a list of all donation forms donated to within the last 7 days,
 * ordered from most donations to least.
 *
 * @return string
 */
function give_email_reports_weekly_best_performing_forms() {

	$args     = array(
		'number'     => - 1,
		'start_date' => '6 days ago 00:00',
		'end_date'   => 'now',
		'status'     => 'publish'
	);
	$query    = new Give_Payments_Query( $args );
	$payments = $query->get_payments();
	$stats    = array();

	if ( ! empty( $payments ) ) {

		foreach ( $payments as $donation ) {

			$earnings  = isset( $stats[ $donation->form_id ]['earnings'] ) ? $stats[ $donation->form_id ]['earnings'] : 0;
			$donations = isset( $stats[ $donation->form_id ]['donations'] ) ? $stats[ $donation->form_id ]['donations'] : 0;

			$stats[ $donation->form_id ] = array(
				'name'      => $donation->form_title,
				'earnings'  => $earnings + $donation->total,
				'donations' => $donations + 1,
			);

		}

		usort( $stats, 'give_email_reports_sort_best_performing_forms' );
		$color = 111111;

		ob_start();
		echo '<ul>';
		foreach ( $stats as $list_item ):

			printf( '<li style="color: #%1$s; padding: 5px 0;"><span style="font-weight: bold;">%2$s</span> – %3$s (%4$s %5$s)</li>',
				$color,
				$list_item['name'],
				give_currency_filter( give_format_amount( $list_item['earnings'] ) ),
				$list_item['sales'],
				_n( 'sale', 'sales', $list_item['sales'], 'give-email-reports' )
			);

			if ( $color < 999999 ) {
				$color += 111111;
			}
		endforeach;
		echo '</ul>';

		return ob_get_clean();
	} else {
		return '<p>' . __( 'No donations found.', 'give-email-reports' ) . '</p>';
	}
}

/**
 * Sorts the given stats based on the best-performing donation forms first.
 *
 * @param $a
 * @param $b
 *
 * @return bool
 */
function give_email_reports_sort_best_performing_forms( $a, $b ) {
	return $a['earnings'] < $b['earnings'];
}

/**
 * Triggers the daily sales report email generation and sending.
 *
 * Send the daily email when the cron event triggers the action.
 */
function give_email_reports_send_daily_email() {

	// $message will be rendered during give_email_message filter
	$message = '';

	//Clear out the email template before we send the email.
	add_action( 'give_email_send_before', 'give_email_reports_change_email_template' );

	Give()->emails->html    = true;
	Give()->emails->heading = sprintf( __( 'Daily Donation Report – %1$s', 'give-email-reports' ), get_bloginfo( 'name' ) );
	Give()->emails->send( give_get_admin_notice_emails(), sprintf( __( 'Daily Donation Report for %1$s', 'give-email-reports' ), get_bloginfo( 'name' ) ), $message );

}

add_action( 'give_email_reports_daily_email', 'give_email_reports_send_daily_email' );

/**
 * Filter the email template to load the reporting template for this email.
 *
 * @return void
 */
function give_email_reports_change_email_template() {
	add_filter( 'give_email_template', 'give_email_reports_set_email_template' );
}

/**
 * Sets the suffix to use while looking for the email template to load.
 *
 * @param  string $template_name
 *
 * @return string
 */
function give_email_reports_set_email_template( $template_name ) {
	return 'report';
}

/**
 * Triggers the weekly sales report email generation and sending.
 *
 * @todo
 */
function give_email_reports_weekly_email() {
	return false;
}