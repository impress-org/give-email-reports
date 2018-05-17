<?php

/**
 * This file has code to handle daily email reports notification.
 */
class Give_Daily_Email_Notification extends Give_Email_Notification {

	/**
	 * Setup email notification.
	 *
	 * @access public
	 */
	public function init() {
		$this->load( array(
			'id'                           => 'daily-report',
			'label'                        => __( 'Daily Email Report', 'give-email-reports' ),
			'description'                  => '',
			'notification_status'          => 'disabled',
			'notification_status_editable' => array(
				'list_mode' => false,
			),
			'content_type_editable'        => false,
			'has_preview_header'           => false,
			'content_type'                 => 'text/html',
			'email_template'               => 'default',
			'has_recipient_field'          => true,
			'form_metabox_setting'         => true,
			'form_metabox_id'              => 'give_email_report_options_metabox_fields',
			'default_email_subject'        => sprintf( __( 'Daily Donation Report for %1$s', 'give-email-reports' ), get_bloginfo( 'name' ) ),
		) );

		add_filter( 'give_email_notification_setting_fields', array( $this, 'unset_email_setting_field' ), 10, 2 );
		add_action( 'give_email_reports_daily_email', array( $this, 'setup_email_notification' ) );
	}

	/**
	 * Register email settings to form metabox.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param array $settings meta box setting.
	 * @param int   $form_id Donation Form ID.
	 *
	 * @return array
	 */
	public function add_metabox_setting_field( $settings, $form_id ) {
		$settings[] = array(
			'id'     => $this->config['id'],
			'title'  => $this->config['label'],
			'fields' => $this->get_setting_fields( $form_id ),
		);

		return $settings;
	}

	/**
	 * Get recipient(s).
	 *
	 * Note: in case of admin notification this fx will return array of emails otherwise empty string or email of donor.
	 *
	 * @access public
	 *
	 * @param int $form_id Donation form id.
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
		 *
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
	 * @param null $form_id Donation Form ID.
	 *
	 * @return array
	 */
	public function get_extra_setting_fields( $form_id = null ) {
		$default = empty( $form_id ) ? 'report-daily' : give_get_meta( $form_id, '_give_email_reports_daily_email_template', true, 'report-daily' );

		return array(
			array(
				'id'      => 'give_email_reports_daily_email_template',
				'name'    => __( 'Email template', 'give-email-reports' ),
				'desc'    => __( 'Choose your template from the available registered template types.', 'give-email-reports' ),
				'type'    => 'select',
				'default' => $default,
				'options' => array(
					'report-daily' => __( 'Daily Report', 'give-email-reports' ),
				),
			),
			array(
				'id'          => 'give_email_reports_daily_email_delivery_time',
				'name'        => __( 'Daily Email Delivery Time', 'give-email-reports' ),
				'desc'        => __( 'Select when you would like to receive your daily email report.', 'give-email-reports' ),
				'type'        => 'email_report_daily_schedule',
				'callback'    => array( $this, 'email_report_daily_schedule' ),
				'row_classes' => 'cmb-type-email-report-daily-schedule',
				'default'     => '1900',
			),
		);
	}

	/**
	 * Fire action to add report daily schedule
	 *
	 * @since 1.2.1
	 *
	 * @param array $field custom field.
	 */
	public function email_report_daily_schedule( $field ) {

		global $post;

		$form_id = empty( $post->ID ) ? null : absint( $post->ID );

		/**
		 * Fire action after before email send.
		 *
		 * @since 1.2.1
		 */
		do_action( 'give_form_field_email_report_daily_schedule', $field, $form_id );
	}

	/**
	 * Unset email message setting field.
	 *
	 * @access public
	 *
	 * @param array                   $settings Email setting for donation form.
	 * @param Give_Email_Notification $email Email Notification instances.
	 *
	 * @return array $settings Email setting for donation form.
	 */
	public function unset_email_setting_field( $settings, $email ) {
		if ( $this->config['id'] === $email->config['id'] ) {

			$option = array(
				'enabled'  => __( 'Enabled', 'give-email-reports' ),
				'disabled' => __( 'Disabled', 'give-email-reports' ),
			);

			foreach ( $settings as $index => $setting ) {

				if ( in_array( $setting['id'], array( "{$this->config['id']}_email_message", "_give_{$this->config['id']}_email_message" ), true ) ) {
					unset( $settings[ $index ] );
				}

				if ( "_give_{$this->config['id']}_notification" === $setting['id'] ) {
					$settings[ $index ]['options'] = $option;
					$settings[ $index ]['default'] = 'disabled';
				}
			}
		}

		return array_values( $settings );
	}

	/**
	 * Get default email message
	 *
	 * @access public
	 *
	 * @param  int $form_id Donation form id.
	 *
	 * @return string
	 */
	public function get_email_message( $form_id = null ) {
		// $message will be rendered during give_email_message filter.
		ob_start();
		give_get_template_part( 'emails/body', give_get_option( 'give_email_reports_daily_email_template', 'report-daily' ), true );

		/**
		 * Filter the message.
		 *
		 * @since 2.0
		 */
		return apply_filters(
			"give_{$this->config['id']}_get_email_message",
			ob_get_clean(),
			$this,
			$form_id
		);
	}

	/**
	 *  Setup email data.
	 *
	 * @access public
	 */
	public function setup_email_data() {
		Give()->emails->heading = __( 'Daily Donation Report', 'give-email-reports' ) . '<br>' . get_bloginfo( 'name' );
	}

	/**
	 * Setup email notification.
	 *
	 * @access public
	 */
	public function setup_email_notification() {
		$this->setup_email_data();
		$this->send_email_notification();
	}
}

return Give_Daily_Email_Notification::get_instance();
