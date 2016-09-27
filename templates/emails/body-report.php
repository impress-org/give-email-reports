<?php
/**
 * Report Email Body.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<table style="text-align: center !important; width: 100%; table-layout: fixed;">
	<tbody>
	<tr>
		<td colspan="3" style="padding: 0 0 25px;">
			<h3 style="margin: 0;"><?php echo date( 'F j, Y' ); ?></h3>
			<p style="margin: 0;"><?php printf( __( 'Happy %1$s!', 'give-email-reports' ), date( 'l', current_time( 'timestamp' ) ) ); ?></p>
		</td>
	</tr>

	<tr>
		<td colspan="3" style="padding: 16px;">
			<h1 style="font-size: 48px; line-height: 1em; margin: 0; color:#4EAD61;">
				<?php if ( give_get_option( 'currency_position' ) == 'before' ): ?>
					<span style="font-size: 20px; vertical-align: super;"><?php echo give_currency_filter( '' ); ?></span><?php endif; ?>{email_report_daily_total}<?php if ( give_get_option( 'currency_position' ) == 'after' ): ?>
					<span style="font-size: 20px; vertical-align: super;">{email_report_currency}</span><?php endif; ?>
			</h1>
			<h2 style="margin: 8px 0; color: #222;">{email_report_daily_transactions} <?php _e( 'donations today', 'give-email-reports' ); ?></h2>
			<h3 style="margin: 0; color: #333;">{email_report_rolling_weekly_total} <?php _e( 'past seven days', 'give-email-reports' ); ?></h3>
		</td>
	</tr>

	<tr>
		<td style="padding: 30px; text-align: center;">
			<span style="display: block; font-size:18px; font-weight: bold;color:#4EAD61;">{email_report_weekly_total}</span>
			<small style="display: block;font-size:14px;"><?php _e( 'this week', 'give-email-reports' ); ?></small>
		</td>
		<td style="padding: 30px; text-align: center;">
			<span style="display: block; font-size:18px; font-weight: bold;color:#4EAD61;">{email_report_monthly_total}</span>
			<small style="display: block;font-size:14px;"><?php _e( 'this month', 'give-email-reports' ); ?></small>
		</td>
		<td style="padding: 30px; text-align: center;">
			<span style="display: block; font-size:18px; font-weight: bold;color:#4EAD61;">{email_report_rolling_monthly_total}</span>
			<small style="display: block;font-size:14px;"><?php _e( 'past 30 days', 'give-email-reports' ); ?></small>
		</td>
	</tr>

	<!-- daily amount sold chart, past thirty days -->

	<tr>
		<td colspan="3" style="text-align: left !important;">
			<h3 style="margin: 0; padding-left: 40px;"><?php _e( 'The best performing donation forms this week:', 'give-email-reports' ); ?></h3>
			{email_report_weekly_best_performing_forms}
		</td>
	</tr>

	<tr>
		<td colspan="3" style="text-align: left !important;">
			<h3 style="margin: 0; padding-left: 40px;"><?php _e( 'These forms have not received a donation in awhile:', 'give-email-reports' ); ?></h3>
			{email_report_cold_donation_forms}
		</td>
	</tr>

	</tbody>
</table>
