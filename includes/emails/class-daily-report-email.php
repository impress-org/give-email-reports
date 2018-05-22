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
			'id'                    => 'daily-report',
			'label'                 => __( 'Daily Email Report', 'give-email-reports' ),
			'description'           => '',
			'content_type_editable' => false,
			'has_recipient_field'   => true,
			'form_metabox_setting'  => true,
			'notification_status'   => 'enabled',
			'form_metabox_id'       => 'give_email_report_options_metabox_fields',
			'default_email_subject' => sprintf( __( 'Daily Donation Report for %1$s', 'give-email-reports' ), get_bloginfo( 'name' ) ),
			'content_type'          => 'text/html',
		) );

		add_filter( 'give_email_notification_setting_fields', array( $this, 'unset_email_setting_field' ), 10, 2 );
		add_action( 'give_email_reports_daily_email', array( $this, 'setup_email_notification' ), 10, 0 );
		add_action( 'give_email_reports_daily_per_form', array( $this, 'setup_email_notification' ), 10, 1 );
	}

	/**
	 * Get notification status.
	 *
	 * @since  1.2
	 * @access public
	 *
	 * @param int $form_id Donation Form ID.
	 *
	 * @return bool
	 */
	public function get_notification_status( $form_id = null ) {
		$notification_status = empty( $form_id )
			? give_get_option( "{$this->config['id']}_notification", $this->config['notification_status'] )
			: give_get_meta( $form_id, Give_Email_Setting_Field::get_prefix( $this, $form_id ) . 'notification', true, 'disabled' );

		/**
		 * Filter the notification status.
		 *
		 * @since 1.8
		 */
		return apply_filters( "give_{$this->config['id']}_get_notification_status", $notification_status, $this, $form_id );
	}

	/**
	 * Register email settings to form metabox.
	 *
	 * @since  1.2
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
	 * Get extra setting field.
	 *
	 * @access public
	 *
	 * @param null $form_id Donation Form ID.
	 *
	 * @return array
	 */
	public function get_extra_setting_fields( $form_id = null ) {
		$default = 'report-daily';
		$option  = array(
			'report-daily' => __( 'Daily Report', 'give-email-reports' ),
		);

		if ( ! empty( $form_id ) ) {
			$default = give_get_meta( $form_id, '_give_email_reports_daily_email_template', true, 'report-per-form-daily' );
			$option  = array(
				'report-per-form-daily' => __( 'Form Daily Report', 'give-email-reports' ),
			);
		}

		return array(
			array(
				'id'      => 'give_email_reports_daily_email_template',
				'name'    => __( 'Email template', 'give-email-reports' ),
				'desc'    => __( 'Choose your template from the available registered template types.', 'give-email-reports' ),
				'type'    => 'select',
				'default' => $default,
				'options' => $option,
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
	 * @since 1.2
	 *
	 * @param array $field custom field.
	 */
	public function email_report_daily_schedule( $field ) {

		global $post;

		$form_id = empty( $post->ID ) ? null : absint( $post->ID );

		/**
		 * Fire action after before email send.
		 *
		 * @since 1.2
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
	 * Setup email notification.
	 *
	 * @access public
	 *
	 * @param int $form_id Donation form ID.
	 */
	public function setup_email_notification( $form_id = null ) {

		if ( ! empty( $form_id ) ) {
			add_filter( 'give_daily-report_is_email_notification_active', array( $this, 'is_email_notification_active' ), 10, 3 );
		}

		$this->setup_email_data( $form_id );
		$this->send_email_notification( array( 'form_id' => $form_id ) );
	}

	/**
	 * Filter to modify email notification is the email is send on per form basis.
	 *
	 * @param bool                    $notification_status True if notification is enable and false when disable.
	 * @param Give_Email_Notification $email Class instances Give_Email_Notification.
	 * @param int                     $form_id Donation Form ID.
	 *
	 * @return bool $notification_status True if notification is enable and false when disable.
	 */
	public function is_email_notification_active( $notification_status, $email, $form_id ) {
		if ( empty( $form_id ) ) {
			return $notification_status;
		}

		return true;
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
		if ( $this->config['has_recipient_field'] ) {
			$this->recipient_email = Give_Email_Notification_Util::get_value( $this, Give_Email_Setting_Field::get_prefix( $this, $form_id ) . 'recipient', $form_id );
		}

		/**
		 *  Filter the emails
		 *
		 * @since 1.0
		 * @deprecated
		 */
		$this->recipient_email = apply_filters( 'give_email_reports_recipients', $this->recipient_email, 'daily' );

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
	 *  Setup email data.
	 *
	 * @access public
	 *
	 * @param int $form_id Donation form ID.
	 */
	public function setup_email_data( $form_id = null ) {
		$heading = empty( $form_id )
			? sprintf( __( 'Daily Donation Report <br> %s', 'give-email-reports' ), get_bloginfo( 'name' ) )
			: sprintf( __( 'Daily Donation Report for <br> "%s"', 'give-email-reports' ), get_the_title( $form_id ) );

		Give()->emails->heading = $heading;
	}

	/**
	 * Get default email message
	 *
	 * @access public
	 *
	 * @param  int $form_id Donation Form ID.
	 *
	 * @return string
	 */
	public function get_email_message( $form_id = null ) {

		$email_template = empty( $form_id )
			? give_get_option( 'give_email_reports_daily_email_template', 'report-daily' )
			: give_get_meta( $form_id, '_give_email_reports_daily_email_template', true, 'report-daily' );


		// $message will be rendered during give_email_message filter.
		ob_start();
		include give_get_template_part( 'emails/body', $email_template, false );

		/**
		 * Filter the message.
		 *
		 * @since 1.2
		 */
		return apply_filters( "give_{$this->config['id']}_get_email_message", ob_get_clean(), $this, $form_id );
	}
}

return Give_Daily_Email_Notification::get_instance();
