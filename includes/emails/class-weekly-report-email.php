<?php

/**
 * This file has code to handle weekly email reports notification.
 */
class Give_Weekly_Email_Notification extends Give_Email_Notification {
	function init() {
		$this->load( array(
			'id'                    => 'weekly-report',
			'label'                 => __( 'Weekly Email Report', 'give' ),
			'description'           => __( '', 'give' ),
			'notification_status'   => 'disabled',
			'content_type_editable' => false,
			'content_type'          => 'text/html',
			'email_template'        => 'default',
			// 'form_metabox_setting' => true,
			'has_recipient_field'   => true,
			'default_email_subject' => sprintf( __( 'Weekly Donation Report for %1$s', 'give-email-reports' ), get_bloginfo( 'name' ) ),
			'default_email_message' => $this->get_default_email_message(),
		) );
	}


	/**
	 * Get extra setting field.
	 *
	 * @access public
	 *
	 * @param null $form_id
	 *
	 * @return array
	 */
	public function get_extra_setting_fields( $form_id = null ) {
		return array(
			array(
				'id'   => 'give_email_reports_weekly_email_delivery_time',
				'name' => __( 'Weekly Email Delivery Time', 'give-email-reports' ),
				'desc' => __( 'Select when you would like to receive your weekly email report.', 'give-email-reports' ),
				'type' => 'email_report_weekly_schedule',
			)
		);
	}


	/**
	 * Get default email message
	 *
	 * @access public
	 * @return string
	 */
	public function get_default_email_message() {
		// $message will be rendered during give_email_message filter.
		ob_start();
		give_get_template_part( 'emails/body-report-weekly', Give()->emails->get_template(), true );

		return ob_get_clean();
	}
}

return Give_Weekly_Email_Notification::get_instance();
