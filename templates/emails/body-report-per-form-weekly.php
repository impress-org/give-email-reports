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
            <h3 style="margin: 0;"><?php echo date( 'F jS ', strtotime( '-1 week', current_time( 'timestamp' ) ) ); ?><?php echo date( '- jS, Y', current_time( 'timestamp' ) ); ?></h3>
            <p style="margin: 0;"><?php printf( __( 'Happy %1$s!', 'give-email-reports' ), date( 'l', current_time( 'timestamp' ) ) ); ?></p>
        </td>
    </tr>

    <tr>
        <td colspan="3" style="padding: 16px;">
            <h1 style="font-size: 48px; line-height: 1em; margin: 0; color:#4EAD61;">
	            <?php echo give_email_get_earning_callout( 'weekly' ) ?>
            </h1>
            <h2 style="margin: 8px 0; color: #222;"><?php echo give_email_reports_donations( 'weekly', $form_id ) . ' ' . __( 'donations this week', 'give-email-reports' ); ?></h2>
            <h3 style="margin: 0; color: #555;"><?php
				//Get number of donations increase / decrease week over week
				give_email_reports_donation_difference( 'weekly', $form_id ); ?>
            </h3>
        </td>
    </tr>

    <tr>
        <td style="padding: 30px; text-align: center;">
            <span style="display: block; font-size:22px; font-weight: bold;color:#4EAD61;"><?php echo give_email_reports_monthly_total( $form_id ); ?></span>
            <small style="display: block;font-size:16px;"><?php _e( 'this month', 'give-email-reports' ); ?></small>
        </td>
        <td style="padding: 30px; text-align: center;">
            <span style="display: block; font-size:22px; font-weight: bold;color:#4EAD61;"><?php echo give_email_reports_rolling_monthly_total( $form_id ); ?></span>
            <small style="display: block;font-size:16px;"><?php _e( 'past 30 days', 'give-email-reports' ); ?></small>
        </td>
        <td style="padding: 30px; text-align: center;">
            <span style="display: block; font-size:22px; font-weight: bold;color:#4EAD61;"><?php echo give_email_reports_yearly_total( $form_id ); ?></span>
            <small style="display: block;font-size:16px;"><?php _e( 'this year', 'give-email-reports' ); ?></small>
        </td>
    </tr>
    </tbody>
</table>
