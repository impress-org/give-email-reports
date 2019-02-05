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
            <h3 style="margin: 0;"><?php echo date( 'F j, Y', current_time( 'timestamp' ) ); ?></h3>
            <p style="margin: 0;"><?php printf( __( 'Happy %1$s!', 'give-email-reports' ), date( 'l', current_time( 'timestamp' ) ) ); ?></p>
        </td>
    </tr>

    <tr>
        <td colspan="3" style="padding: 16px;">
            <h1 style="font-size: 48px; line-height: 1em; margin: 0; color:#4EAD61;">
	            <?php echo give_email_get_earning_callout( 'daily' ) ?>
            </h1>
            <h2 style="margin: 8px 0; color: #222;"><?php echo give_email_reports_donations( 'daily', $form_id ) . ' ' . __( 'donations today', 'give-email-reports' ); ?></h2>
            <h3 style="margin: 0; color: #333;"><?php echo give_email_reports_rolling_weekly_total( $form_id ) . ' ' . __( 'past seven days', 'give-email-reports' ); ?></h3>
        </td>
    </tr>

    <tr>
        <td style="padding: 30px; text-align: center;">
			<span
                    style="display: block; font-size:22px; font-weight: bold;color:#4EAD61;"><?php echo give_email_reports_weekly_total( $form_id ); ?></span>
            <small style="display: block;font-size:16px;"><?php _e( 'this week', 'give-email-reports' ); ?></small>
        </td>
        <td style="padding: 30px; text-align: center;">
			<span
                    style="display: block; font-size:22px; font-weight: bold;color:#4EAD61;"><?php echo give_email_reports_monthly_total( $form_id ); ?></span>
            <small style="display: block;font-size:16px;"><?php _e( 'this month', 'give-email-reports' ); ?></small>
        </td>
        <td style="padding: 30px; text-align: center;">
			<span
                    style="display: block; font-size:22px; font-weight: bold;color:#4EAD61;"><?php echo give_email_reports_rolling_monthly_total( $form_id ); ?></span>
            <small style="display: block;font-size:16px;"><?php _e( 'past 30 days', 'give-email-reports' ); ?></small>
        </td>
    </tr>
    </tbody>
</table>
