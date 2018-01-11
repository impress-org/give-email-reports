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
			'has_preview_header'    => false,
			'content_type'          => 'text/html',
			'email_template'        => 'default',
			'has_recipient_field'   => true,
			'default_email_subject' => sprintf( __( 'Monthly Donation Report for %1$s', 'give-email-reports' ), get_bloginfo( 'name' ) ),
			'default_email_message' => $this->get_default_email_message(),
		) );

		add_filter( 'give_email_notification_setting_fields', array( $this, 'unset_email_setting_field' ), 10, 2 );
		add_action( 'give_email_reports_monthly_email', array( $this, 'setup_email_notification' ) );
	}

	/**
	 * Get recipient(s).
	 *
	 * Note: in case of admin notification this fx will return array of emails otherwise empty string or email of donor.
	 *
	 * @access public
	 *
	 * @param int $form_id
	 *
	 * @return string|array
	 */
	public function get_recipient( $form_id = null ) {
		if ( empty( $this->recipient_email ) && $this->config['has_recipient_field'] ) {
			$this->recipient_email = Give_Email_Notification_Util::get_value( $this, Give_Email_Setting_Field::get_prefix( $this, $form_id ) . 'recipient', $form_id );
		}

		/**
		 *  Filter the emails
		 *
		 * @since 1.0
		 * @deprecated
		 */
		$this->recipient_email = apply_filters(
			'give_email_reports_recipients',
			$this->recipient_email,
			'daily'
		);

		/**
		 * Filter the recipients
		 */
		return apply_filters(
			"give_{$this->config['id']}_get_recipients",
			give_check_variable(
				$this->recipient_email,
				'empty',
				Give()->emails->get_from_address()
			),
			$this,
			$form_id
		);
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
				'id'      => 'give_email_reports_monthly_email_template',
				'name'    => __( 'Email template', 'give-email-reports' ),
				'desc'    => __( 'Choose your template from the available registered template types.', 'give-email-reports' ),
				'type'    => 'select',
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
	 *
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
		give_get_template_part( 'emails/body', give_get_option( 'give_email_reports_monthly_email_template', 'report-monthly' ), true );

		return ob_get_clean();
	}

	/**
	 *  Setup email data.
	 *
	 * @access public
	 */
	public function setup_email_data() {
		Give()->emails->heading = __( 'Monthly Donation Report', 'give-email-reports' ) . '<br>' . get_bloginfo( 'name' );
	}

	/**
	 * Setup email notification.
	 *
	 * @access public
	 *
	 */
	public function setup_email_notification() {
		$this->setup_email_data();
		$this->send_email_notification();
		$this->reschedule_monthly_email();
	}

	/**
	 * Reschedule monthly email.
	 *
	 * @return false|string
	 */
	private function reschedule_monthly_email() {
		$monthly = give_get_option( 'give_email_reports_monthly_email_delivery_time' );

		$local_time = strtotime( "{$monthly['day']} day of next month T{$monthly['time']}", current_time( 'timestamp' ) );
		$gmt_time   = get_gmt_from_date( date( 'Y-m-d H:i:s', $local_time ), 'U' );

		wp_schedule_single_event(
			$gmt_time,
			'give_email_reports_monthly_email'
		);
	}
}

return Give_Monthly_Email_Notification::get_instance();
