<?php

/**
 * This file has code to handle weekly email reports notification.
 */
class Give_Weekly_Email_Notification extends Give_Email_Notification {

	/**
	 * Setup email notification.
	 *
	 * @access public
	 */
	public function init() {
		$this->load( array(
			'id'                           => 'weekly-report',
			'label'                        => __( 'Weekly Email Report', 'give-email-reports' ),
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
			'default_email_subject'        => sprintf( __( 'Weekly Donation Report for %1$s', 'give-email-reports' ), get_bloginfo( 'name' ) ),
		) );

		add_filter( 'give_email_notification_setting_fields', array( $this, 'unset_email_setting_field' ), 10, 2 );
		add_action( 'give_email_reports_weekly_email', array( $this, 'setup_email_notification' ) );
		add_action( 'give_email_reports_weekly_per_form', array( $this, 'setup_email_notification' ) );
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
	 * Get extra setting field.
	 *
	 * @access public
	 *
	 * @param null $form_id Donation form id.
	 *
	 * @return array
	 */
	public function get_extra_setting_fields( $form_id = null ) {
		return array(
			array(
				'id'      => 'give_email_reports_weekly_email_template',
				'name'    => __( 'Email template', 'give-email-reports' ),
				'desc'    => __( 'Choose your template from the available registered template types.', 'give-email-reports' ),
				'type'    => 'select',
				'default' => 'report-weekly',
				'options' => array(
					'report-weekly' => __( 'Weekly Report', 'give-email-reports' ),
				),
			),
			array(
				'id'          => 'give_email_reports_weekly_email_delivery_time',
				'name'        => __( 'Weekly Email Delivery Time', 'give-email-reports' ),
				'desc'        => __( 'Select the day of the week and time that you would like to receive the weekly report.', 'give-email-reports' ),
				'type'        => 'email_report_weekly_schedule',
				'callback'    => array( $this, 'email_report_weekly_schedule' ),
				'row_classes' => 'cmb-type-email-report-weekly-schedule',
			),
		);
	}

	/**
	 * Fire action to add report weekly schedule
	 *
	 * @since 1.2.1
	 *
	 * @param array $field custom field.
	 */
	public function email_report_weekly_schedule( $field ) {

		global $post;

		$form_id = empty( $post->ID ) ? null : absint( $post->ID );

		/**
		 * Fire action after before email send.
		 *
		 * @since 1.2.1
		 */
		do_action( 'give_form_field_email_report_weekly_schedule', $field, $form_id );
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
	 * @param int/null $form_id Donation form ID.
	 */
	public function setup_email_notification( $form_id = null ) {
		$this->send_email_notification( array( 'form_id' => $form_id ) );
	}

	/**
	 * Get default email message
	 *
	 * @access public
	 *
	 * @param  int $form_id
	 *
	 * @return string
	 */
	public function get_email_message( $form_id = null ) {

		if ( empty( $form_id ) ) {
			$email_template = give_get_option( 'give_email_reports_weekly_email_template', 'report-weekly' );
		} else {
			$email_template = give_get_meta( $form_id, '_give_email_reports_weekly_email_template', true, 'report-weekly' );
		}

		// $message will be rendered during give_email_message filter.
		ob_start();
		give_get_template_part( 'emails/body', $email_template, true );

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
}

return Give_Weekly_Email_Notification::get_instance();
