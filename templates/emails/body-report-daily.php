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
			<h3 style="margin: 0;">{current_date}</h3>
			<p style="margin: 0;"><?php echo __( 'Happy {day_of_week_name}', 'give-email-reports' ); ?></p>
		</td>
	</tr>

	<tr>
		<td colspan="3" style="padding: 16px;">
			<h1 style="font-size: 48px; line-height: 1em; margin: 0; color:#4EAD61;">
				<span style="font-size: 20px; vertical-align: super;">
					{donation_total_today}
				</span>
			</h1>
			<h2 style="margin: 8px 0; color: #222;"><?php echo  '{donation_count_today} ' . __( 'donations today', 'give-email-reports' ); ?></h2>
			<h3 style="margin: 0; color: #333;"><?php echo '{donation_total_past_week} ' . __( 'past seven days', 'give-email-reports' ); ?></h3>
		</td>
	</tr>

	<tr>
		<td style="padding: 30px; text-align: center;">
			<span
				style="display: block; font-size:22px; font-weight: bold;color:#4EAD61;">{donation_total_this_week}</span>
			<small style="display: block;font-size:16px;"><?php _e( 'this week', 'give-email-reports' ); ?></small>
		</td>
		<td style="padding: 30px; text-align: center;">
			<span
				style="display: block; font-size:22px; font-weight: bold;color:#4EAD61;">{donation_total_this_month}</span>
			<small style="display: block;font-size:16px;"><?php _e( 'this month', 'give-email-reports' ); ?></small>
		</td>
		<td style="padding: 30px; text-align: center;">
			<span
				style="display: block; font-size:22px; font-weight: bold;color:#4EAD61;">{donation_total_past_month}</span>
			<small style="display: block;font-size:16px;"><?php _e( 'past 30 days', 'give-email-reports' ); ?></small>
		</td>
	</tr>
	<tr>
		<td colspan="3" style="text-align: left !important;">
			<h3 style="margin: 0; padding-left: 40px;"><?php _e( 'The best performing donation forms this week:', 'give-email-reports' ); ?></h3>
			{best_performing_forms_weekly}
		</td>
	</tr>

	<tr>
		<td colspan="3" style="text-align: left !important;">
			<h3 style="margin: 0; padding-left: 40px;"><?php _e( 'These forms have not received a donation in awhile:', 'give-email-reports' ); ?></h3>
			{not_getting_donation_forms_list}
		</td>
	</tr>

	</tbody>
</table>
