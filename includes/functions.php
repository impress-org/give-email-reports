<?php
/**
 * Helper Functions.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sort the passed array based on the furthest donation date.
 *
 * @param $a
 * @param $b
 *
 * @return false|int
 */
function give_email_reports_sort_cold_donation_forms( $a, $b ) {
	return strtotime( $a ) - strtotime( $b );
}

/**
 * Returns the earnings amount for the past 7 days, including today.
 *
 * @param int $form_id Donation Form ID.
 *
 * @return string
 */
function give_email_reports_rolling_weekly_total( $form_id = 0 ) {
	$stats       = new Give_Donation_Stats();
	$stats_query = array(
		'give_forms' => array( $form_id ),
		'start_date' => '6 days ago 00:00',
		'end_date'   => 'now 23:59:59',
	);

	return give_currency_filter( give_format_amount( $stats->get_earnings( $stats_query )->total ) );

}

/**
 * Give Email reports monthly total.
 *
 * @param int $form_id Donation Form ID.
 *
 * @return string
 */
function give_email_reports_rolling_monthly_total( $form_id = 0 ) {
	$stats       = new Give_Donation_Stats();
	$stats_query = array(
		'give_forms' => array( $form_id ),
		'start_date' => '30 days ago 00:00',
		'end_date'   => 'now 23:59:59',
	);

	return give_currency_filter( give_format_amount( $stats->get_earnings( $stats_query )->total ) );
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
		$timestamp                 -= 24 * 3600;
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
 * Returns the total earnings for a specific period.
 *
 * @param string $report_period Period.
 * @param int    $form_id Donation form ID.
 *
 * @return string
 */
function give_email_reports_total( $report_period = 'today', $form_id = 0 ) {
	give_email_reports_delete_stats_transients();

	$stats = new Give_Donation_Stats();
	$stats_query = array();

	if( $form_id ) {
		$stats_query['give_forms'] = array( $form_id );
	}

	switch ( $report_period ) {
		case 'weekly':
			$stats_query['start_date'] = '6 days ago 00:00';
			$stats_query['end_date']   = 'now 23:59:59';
			break;

		case 'monthly':
			$stats_query['start_date'] = '30 days ago 00:00';
			$stats_query['end_date']   = 'now 23:59:59';
			break;

		default:
			$stats_query['start_date'] = 'today';
			break;
	}

	return give_format_amount( $stats->get_earnings( $stats_query )->total );
}

/**
 * Returns the number of transactions for today.
 *
 * @param string $report_period report period.
 * @param int    $form_id       Donation Form ID.
 *
 * @return float|int
 */
function give_email_reports_donations( $report_period, $form_id = 0 ) {

	give_email_reports_delete_stats_transients();

	$stats       = new Give_Donation_Stats();
	$stats_query = array(
		'give_forms' => $form_id,
	);

	switch ( $report_period ) {
		case 'weekly':
			$stats_query['start_date'] = '6 days ago 00:00';
			$stats_query['end_date']   = 'now 23:59:59';
			break;

		case 'monthly':
			$stats_query['start_date'] = '30 days ago 00:00';
			$stats_query['end_date']   = 'now 23:59:59';
			break;

		default:
			$stats_query['start_date'] = 'today';
			break;
	}

	return $stats->get_sales( $stats_query )->sales;
}

/**
 * Gets the total earnings for the current week.
 *
 * @param int $form_id Donation form ID.
 *
 * @return string
 */
function give_email_reports_weekly_total( $form_id = 0 ) {
	$stats = new Give_Donation_Stats();
	$stats_query = array(
		'give_forms' => array( $form_id ),
		'start_date' => 'this_week',
	);

	return give_currency_filter( give_format_amount( $stats->get_earnings( $stats_query )->total ) );
}

/**
 * Gets the total earnings for the current month
 *
 * @param int $form_id Donation form ID.
 *
 * @return string
 */
function give_email_reports_monthly_total( $form_id = 0 ) {
	$stats      = new Give_Donation_Stats();
	$stat_query = array(
		'give_forms' => array( $form_id ),
		'range'      => 'this_month',
	);

	return give_currency_filter( give_format_amount( $stats->get_earnings( $stat_query )->total ) );
}

/**
 * Gets the total earnings for the current month
 *
 * @param int $form_id Donation form ID.
 *
 * @return string
 */
function give_email_reports_yearly_total( $form_id = 0 ) {
	$stats      = new Give_Donation_Stats();
	$stat_query = array(
		'give_forms' => array( $form_id ),
		'range'      => 'this_year',
	);

	return give_currency_filter( give_format_amount( $stats->get_earnings( $stat_query )->total ) );
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


/**
 * Outputs a list of all donation forms donated to within the last 7 days,
 * ordered from most donations to least.
 *
 * @param $report_period
 *
 * @return string
 */
function give_email_reports_best_performing_forms( $report_period ) {

	$start_date = 'today';
	$end_date   = false;

	switch ( $report_period ) {
		case 'weekly':
			$start_date = '6 days ago 00:00';
			$end_date   = 'now 23:59:59';
			break;
		case 'monthly':
			$start_date = '30 days ago 00:00';
			$end_date   = 'now 23:59:59';
			break;
		case 'yearly':
			$start_date = 'this_year';
			$end_date   = 'now 23:59:59';
			break;
	}

	$donation_stats = new Give_Donation_Stats();
	$donation_stats  = $donation_stats->get_statistics(array(
		'start_date' => $start_date,
		'end_date' => $end_date,
		'statistic_type' => 'form'
	));

	ob_start();

	if ( ! empty( $donation_stats->sales ) ) {

		arsort( $donation_stats->sales );

		$color_prefix = 99;

		echo '<ul style="padding-left: 55px;padding-right: 30px;">';
		foreach ( $donation_stats->sales as $form_id => $sales ) :

			printf( '<li style="color: #00%1$s00; padding: 5px 0;"><span style="font-weight: bold;">%2$s</span> – %3$s (%4$s %5$s)</li>',
				$color_prefix,
				get_the_title( $form_id ),
				give_currency_filter( give_format_amount( $donation_stats->earnings[$form_id] ) ),
				$sales,
				_n( 'donation', 'donations', $sales, 'give-email-reports' )
			);

			if ( $color_prefix > 11 ) {
				$color_prefix -= 11;
			}
		endforeach;
		echo '</ul>';


	} else {
		echo sprintf(
			'<p style="padding-left:40px;">%s</p>',
			__( 'No donations found.', 'give-email-reports' )
		);
	}// End if().

	return ob_get_clean();
}


/**
 * Fetch six forms sorted by the furthest last donation date.
 *
 * @return string
 */
function give_email_reports_cold_donation_forms() {
	$donation_stats = new Give_Email_Report_Donation_Stats();
	$results = $donation_stats->get_cold_donation_forms( array( 'number' => 6 ) );

	ob_start();

	if ( ! empty( $results->result ) ) {

			$color_prefix = 99;

			echo '<ul style="padding-left: 55px;padding-right: 30px;">';

			/* @var stdClass $item */
			foreach ( $results->result as $item ) :

				printf( '<li style="color: #%1$s0000; padding: 5px 0;"><span style="font-weight: bold;">%2$s</span> – Last donation <strong>%4$s ago</strong> on <strong>%3$s</strong></li>',
					$color_prefix,
					get_the_title( $item->form ),
					date( 'F j, Y', strtotime( $item->date ) ),
					human_time_diff( strtotime( $item->date ) )
				);

				if ( $color_prefix > 11 ) {
					$color_prefix -= 11;
				}
			endforeach;


			echo '</ul>';

	} else {
		echo '<p style="padding-left: 40px;">' . __( 'No donations found.', 'give-email-reports' ) . '</p>';

	}// End if().

	return ob_get_clean();

}


/**
 * Get reports Donation difference.
 *
 * @param string $report_period report period.
 * @param int    $form_id Donation Form ID.
 *
 * @return mixed
 */
function give_email_reports_donation_difference( $report_period, $form_id = 0 ) {

	$current_donations = give_email_reports_donations( $report_period, $form_id );

	$stats       = new Give_Donation_Stats();
	$stats_query = array(
		'give_forms' => array( $form_id ),
	);

	$stats_query['start_date'] = '2 day ago 00:00';
	$stats_query['end_date']   = '1 day ago 23:59:59';
	$text                      = __( 'Yesterday', 'give-email-reports' );

	switch ( $report_period ) {
		case 'weekly':
			$stats_query['start_date'] = '13 days ago 00:00';
			$stats_query['end_date']   = '7 days ago 23:59:59';

			$text = __( 'last week', 'give-email-reports' );
			break;
		case 'monthly':
			$stats_query['start_date'] = '60 days ago 00:00';
			$stats_query['end_date']   = '30 days ago 23:59:59';

			$text = __( 'last month', 'give-email-reports' );
			break;
	}

	$past_donations = $stats->get_sales( $stats_query )->sales;
	$difference     = $current_donations - $past_donations;

	$output = '';
	if ( $difference == 0 ) {
		// No change
		$output = '&#9670; ' . sprintf( __( 'Same number donations as %s', 'give-email-reports' ), $text );
	} elseif ( $difference < 0 ) {
		$output = '<span style="color:#990000;">&#9662;</span> ' . sprintf( __( '%1$s donations compared to %2$s', 'give-email-reports' ), $difference, $text );
	} elseif ( $difference ) {
		$output = '<span style="color:#4EAD61;">&#9652;</span> +' . sprintf( __( '%1$s donations compared to %2$s', 'give-email-reports' ), $difference, $text );
	}

	echo $output;
}

/**
 * Delete stats transients.
 *
 * Used before sending emails so we can get all the latest stats without worrying about outdated transient data.
 *
 * @see: https://github.com/WordImpress/Give/issues/1117
 */
function give_email_reports_delete_stats_transients() {
	global $wpdb;
	$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('%_give_stats_%')" );
}


/**
 * Retrieves the emails for which email report recipients notifications are sent to (these can be changed in the Email Report Settings).
 *
 * @since 1.1
 * @return mixed
 */
function give_get_email_report_recipients() {

	$email_option = give_get_option( 'give_email_reports_recipients' );

	$emails = ! empty( $email_option ) && strlen( trim( $email_option ) ) > 0 ? explode( "\n", $email_option ) : get_bloginfo( 'admin_email' );

	return apply_filters( 'give_get_email_report_recipients', $emails );
}
