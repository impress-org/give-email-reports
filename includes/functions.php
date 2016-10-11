<?php
/**
 * Helper Functions.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the email
 *
 * @param   string     $message
 * @param  Give_Emails $class
 *
 * @return string
 */
function give_email_reports_render_email( $message, $class ) {

	//Only report templates
	if ( $class->get_template() === 'report' ) {




	}

	return $message;
}

//add_filter( 'give_email_message', 'give_email_reports_render_email', 10, 2 );

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
			echo '<ul style="padding-left: 55px;padding-right: 30px;">';
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
function give_email_reports_best_performing_forms() {

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
		echo '<ul style="padding-left: 55px;padding-right: 30px;">';
		foreach ( $stats as $list_item ):

			printf( '<li style="color: #%1$s; padding: 5px 0;"><span style="font-weight: bold;">%2$s</span> – %3$s (%4$s %5$s)</li>',
				$color,
				$list_item['name'],
				give_currency_filter( give_format_amount( $list_item['earnings'] ) ),
				$list_item['donations'],
				_n( 'donation', 'donations', $list_item['donations'], 'give-email-reports' )
			);

			if ( $color < 999999 ) {
				$color += 111111;
			}
		endforeach;
		echo '</ul>';

		return ob_get_clean();
	} else {
		return '<p style="padding-left:40px;">' . __( 'No donations found.', 'give-email-reports' ) . '</p>';
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