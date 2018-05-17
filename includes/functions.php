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
 * @param $report_period string
 *
 * @return string
 */
function give_email_reports_total( $report_period = 'today' ) {

	give_email_reports_delete_stats_transients();
	$stats = new Give_Payment_Stats();

	switch ( $report_period ) {
		case 'weekly':
			$start_date = '6 days ago 00:00';
			$end_date   = 'now';
			break;
		case 'monthly':
			$start_date = '30 days ago 00:00';
			$end_date   = 'now';
			break;
		default:
			$start_date = 'today';
			$end_date   = false;
			break;
	}

	return give_format_amount( $stats->get_earnings( 0, $start_date, $end_date ) );
}

/**
 * Returns the number of transactions for today.
 *
 * @param $report_period
 *
 * @return float|int
 */
function give_email_reports_donations( $report_period ) {

	$stats = new Give_Payment_Stats();

	$start_date = 'today';
	$end_date   = false;

	switch ( $report_period ) {
		case 'weekly':
			$start_date = '6 days ago 00:00';
			$end_date   = 'now';
			break;
		case 'monthly':
			$start_date = '30 days ago 00:00';
			$end_date   = 'now';
			break;
	}

	return $stats->get_sales( false, $start_date, $end_date );
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
 * Gets the total earnings for the current month
 *
 * @return string
 */
function give_email_reports_yearly_total() {
	$stats = new Give_Payment_Stats();

	return give_currency_filter( give_format_amount( $stats->get_earnings( 0, 'this_year' ) ) );
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
			$end_date   = 'now';
			break;
		case 'monthly':
			$start_date = '30 days ago 00:00';
			$end_date   = 'now';
			break;
		case 'yearly':
			$start_date = 'this_year';
			$end_date   = 'now';
			break;
	}

	$args     = array(
		'number'     => - 1,
		'start_date' => $start_date,
		'end_date'   => $end_date,
		'status'     => 'publish',
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

		$color_prefix = 99;

		ob_start();
		echo '<ul style="padding-left: 55px;padding-right: 30px;">';
		foreach ( $stats as $list_item ) :

			printf( '<li style="color: #00%1$s00; padding: 5px 0;"><span style="font-weight: bold;">%2$s</span> – %3$s (%4$s %5$s)</li>',
				$color_prefix,
				$list_item['name'],
				give_currency_filter( give_format_amount( $list_item['earnings'] ) ),
				$list_item['donations'],
				_n( 'donation', 'donations', $list_item['donations'], 'give-email-reports' )
			);

			if ( $color_prefix > 11 ) {
				$color_prefix -= 11;
			}
		endforeach;
		echo '</ul>';

		return ob_get_clean();
	} else {
		return '<p style="padding-left:40px;">' . __( 'No donations found.', 'give-email-reports' ) . '</p>';
	}// End if().
}


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

	if ( ! empty( $result ) ) {

		foreach ( $result as $form ) {

			$result = new Give_Payments_Query(array(
				'give_forms'     => $form->ID,
				'posts_per_page' => 1,
			) );

			$result = $result->get_payments();

			if ( ! empty( $result ) ) {
				$last_donation_dates[ $form->post_title ] = $result[0]->post_date;
			}
		}

		if ( ! empty( $last_donation_dates ) ) {

			uasort( $last_donation_dates, 'give_email_reports_sort_cold_donation_forms' );
			$last_donation_dates = array_slice( $last_donation_dates, 0, 6, true );

			$color_prefix = 99;

			ob_start();
			echo '<ul style="padding-left: 55px;padding-right: 30px;">';
			foreach ( $last_donation_dates as $form => $date ) :

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
			return '<p style="padding-left: 40px;">' . __( 'No donations found.', 'give-email-reports' ) . '</p>';
		}
	} else {
		return '<p style="padding-left: 40px;">' . __( 'No donations found.', 'give-email-reports' ) . '</p>';
	}// End if().
}


/**
 * @param $report_period
 *
 * @return mixed
 */
function give_email_reports_donation_difference( $report_period ) {

	$current_donations = give_email_reports_donations( $report_period );

	$stats = new Give_Payment_Stats();

	$start_date = 'today';
	$end_date   = false;
	$text       = __( 'Yesterday', 'give-email-reports' );

	switch ( $report_period ) {
		case 'weekly':
			$start_date = '13 days ago 00:00';
			$end_date   = '7 days ago 24:00';
			$text       = __( 'last week', 'give-email-reports' );
			break;
		case 'monthly':
			$start_date = '30 days ago 00:00';
			$end_date   = '60 days ago 24:00';
			$text       = __( 'last month', 'give-email-reports' );
			break;
	}

	$past_donations = $stats->get_sales( false, $start_date, $end_date );
	$difference     = $current_donations - $past_donations;

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

/**
 * Clear Email report hook that are being scheduled to that form.
 *
 * @since 1.2.1
 *
 * @param $form_id
 */
function give_email_report_clear_scheduled_hook_for_form( $form_id ) {

	// check for daily email.
	$daily_cron_name = 'give_email_reports_daily_email_for_' . $form_id;
	wp_clear_scheduled_hook( $daily_cron_name );
}

/**
 * Get all the Donation form with email report is enable
 *
 * @since 1.2.1
 *
 * @param array $args Argument that need to pass in WP query.
 *
 * @return array $form_ids List of Donation Form id.
 */
function give_email_report_get_donation_form( $args = array() ) {
	$form_ids = array();

	$default = array(
		'post_type'        => 'give_forms',
		'posts_per_page'   => 10,
		'meta_key'         => '_give_email_report_options',
		'meta_value'       => 'enabled',
		'suppress_filters' => false,
	);

	/**
	 * Filter to modify get donation form who email report is being scheduled.
	 *
	 * @since 1.2.1
	 *
	 * @param array $args $args Argument that need to pass in WP query.
	 *
	 * @return array $args $args Argument that need to pass in WP query.
	 */
	$args = (array) apply_filters( 'give_email_report_get_donation_form_args', wp_parse_args( $default, $args ) );

	$posts = get_posts( $args );
	if ( ! empty( $posts ) ) {
		foreach ( $posts as $post ) {
			$form_ids[] = $post->ID;
		}
	}

	return $form_ids;
}

/**
 * Delete all form scheduled.
 *
 * @since 1.2.1
 */
function give_email_report_delete_all_form_scheduled() {
	$form_ids = give_email_report_get_donation_form();

	error_log( print_r( $form_ids, true ) . "\n", 3, WP_CONTENT_DIR . '/debug_new.log' );
	if ( ! empty( $form_ids ) ) {
		foreach ( $form_ids as $form_id ) {
			give_email_report_clear_scheduled_hook_for_form( $form_id );
		}
	}
}