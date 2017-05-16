<?php

/**
 * This file has code to handle monthly email reports notification.
 */
class Give_Monthly_Email_Notification extends Give_Email_Notification {
	function init() {
		$this->load( array(
			'id'                    => 'monthly-report',
			'label'                 => __( 'Monthly Email Report', 'give' ),
			'description'           => __( '', 'give' ),
			'notification_status'   => 'disabled',
			'content_type_editable' => false,
			'content_type'          => 'text/html',
			'email_template'        => 'default',
			// 'form_metabox_setting' => true,
			'has_recipient_field'   => true,
			'default_email_subject' => sprintf( __( 'Monthly Donation Report for %1$s', 'give-email-reports' ), get_bloginfo( 'name' ) ),
			'default_email_message' => $this->get_default_email_message(),
		) );

		add_filter( 'give_email_notification_setting_fields', array( $this, 'unset_email_setting_field' ), 10, 2 );
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
				'id'   => 'give_email_reports_monthly_email_template',
				'name' => __( 'Email template', 'give-email-reports' ),
				'desc' => __( 'Choose your template from the available registered template types.', 'give-email-reports' ),
				'type' => 'select',
				'default' => 'report-monthly',
				'options' => array(
					'report-monthly' => __( 'Monthly Report', 'give-email-reports' ),
				),
			),
			array(
				'id'   => 'give_email_reports_monthly_email_delivery_time',
				'name' => __( 'Monthly Email Delivery Time', 'give-email-reports' ),
				'desc' => __( 'Select when you would like to receive your monthly email report.', 'give-email-reports' ),
				'type' => 'email_report_monthly_schedule',
			),
		);
	}


	/**
	 * unset email message setting field.
	 *
	 * @access public
	 * @param array                   $settings
	 * @param Give_Email_Notification $email
	 *
	 * @return array
	 */
	public function unset_email_setting_field( $settings, $email ) {
		if ( $this->config['id'] === $email->config['id'] ) {
			foreach ( $settings as $index => $setting ) {
				if ( "{$this->config['id']}_email_message" === $setting['id'] ) {
					unset( $settings[ $index ] );
					break;
				}
			}
		}

		return array_values( $settings );
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
		give_get_template_part( 'emails/body', give_get_option( 'give_email_reports_daily_email_template', 'report-monthly' ), true );

		return ob_get_clean();
	}
}

return Give_Monthly_Email_Notification::get_instance();