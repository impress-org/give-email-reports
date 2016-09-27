<?php

/**
 * Class Give_Email_Reports_Settings
 */
class Give_Email_Reports_Settings extends Give_Email_Reports {

	/**
	 * Give_Email_Reports_Settings constructor.
	 */
	public function __construct() {

		// Register settings.
		add_filter( 'give_settings_emails', array( $this, 'settings' ), 1 );
		add_action( 'cmb2_render_email_report_preview', array( $this, 'add_email_report_preview' ), 10, 5 );
		add_action( 'cmb2_render_email_report_weekly_schedule', array(
			$this,
			'add_email_report_weekly_schedule'
		), 10, 5 );


		//@TODO: Check this
		add_filter( 'give_settings_sanitize', array( $this, 'sanitize_settings' ), 10, 2 );

	}

	/**
	 * Add settings.
	 *
	 * @access      public
	 * @since       1.0
	 *
	 * @param       array $settings The existing Give settings array
	 *
	 * @return      array The modified Give settings array
	 */
	public function settings( $settings ) {

		$new_settings = array(
			array(
				'id'   => 'give_email_reports_settings',
				'name' => __( 'Email Reports Settings', 'give-email-reports' ),
				'type' => 'give_title',
			),
			array(
				'id'   => 'email_reports_settings',
				'name' => __( 'Preview Report', 'give-email-reports' ),
				'desc' => '',
				'type' => 'email_report_preview'
			),
			array(
				'name'              => 'Test Multi Checkbox',
				'desc'              => __( 'Select the time frames that you would like to receive reports for via email.', 'give-email-reports' ),
				'id'                => 'email_report_emails',
				'type'              => 'multicheck',
				'select_all_button' => false,
				'options'           => array(
					'daily'   => 'Daily',
					'weekly'  => 'Weekly',
					'monthly' => 'Monthly',
				),
			),
			array(
				'id'      => 'give_email_reports_daily_email_delivery_time',
				'name'    => __( 'Daily Email Delivery Time', 'give-email-reports' ),
				'desc'    => __( 'Select when you would like to receive your daily email report.', 'give-email-reports' ),
				'type'    => 'select',
				'default' => '1900',
				'options' => $this->get_email_report_times()
			),
			array(
				'id'   => 'give_email_reports_weekly_email_delivery_time',
				'name' => __( 'Weekly Email Delivery Time', 'give-email-reports' ),
				'desc' => __( 'Select when you would like to receive your weekly email report.', 'give-email-reports' ),
				'type' => 'email_report_weekly_schedule',
			),
		);

		return array_merge( $settings, $new_settings );
	}

	/**
	 * Give add email reports preview.
	 *
	 * @since 1.0
	 */
	public function add_email_report_preview() {
		ob_start();
		?>
		<a href="<?php echo esc_url( add_query_arg( array( 'give_action' => 'preview_email_report' ), home_url() ) ); ?>" class="button-secondary" target="_blank" title="<?php _e( 'Preview Email Report', 'give-email-reports' ); ?> "><?php _e( 'Preview Email Report', 'give-email-reports' ); ?></a>
		<?php
		echo ob_get_clean();
	}

	/**
	 * Give add Weekly email reports preview.
	 *
	 * @param $field
	 * @param $value
	 * @param $object_id
	 * @param $object_type
	 * @param $field_type CMB2_Types
	 */
	public function add_email_report_weekly_schedule( $field, $value, $object_id, $object_type, $field_type ) {

		$times        = $this->get_email_report_times();
		$time_options = '';
		foreach ( $times as $military => $time ) {
			$time_options .= '<option value="' . $military . '" ' . selected( $value['time'], $military, false ) . '>' . $time . '</option>';
		}

		$days         = array( 'monday' => 'Monday', 'tuesday' => 'Tuesday' );
		$days_options = '';
		foreach ( $days as $day_code => $day ) {
			$days_options .= '<option value="' . $day_code . '" ' . selected( $value['day'], $day_code, false ) . '>' . $day . '</option>';
		}
		ob_start(); ?>
		<div><p class="hidden">
				<label for="<?php echo $field_type->_id( '_day' ); ?>"><?php _e( 'Day of Week', 'give-donation-emails' ); ?></label>
			</p>
			<?php echo $field_type->select( array(
				'name'    => $field_type->_name( '[day]' ),
				'id'      => $field_type->_id( '_day' ),
				'options' => $days_options,
				'desc'    => '',
			) ); ?>
		</div>
		<div><p>
				<label for="<?php echo $field_type->_id( '_time' ); ?>'"><?php _e( 'Time of Day', 'give-donation-emails' ); ?></label>
			</p>
			<?php echo $field_type->select( array(
				'name'    => $field_type->_name( '[time]' ),
				'id'      => $field_type->_id( '_time' ),
				'default' => '1900',
				'options' => $time_options,
				'desc'    => '',
			) ); ?>
		</div>

		<?php echo ob_get_clean();
	}


	/**
	 * Sanitize the values for the give_email_reports settings.
	 *
	 * @param $value
	 * @param $key
	 *
	 * @return int
	 */
	public function sanitize_settings( $value, $key ) {

		if ( $key == 'give_email_reports_daily_email_delivery_time' ) {

			$weekly_report = give_get_option( 'give_email_reports_daily_email_delivery_time' );

			if ( $weekly_report != $value ) {
				wp_clear_scheduled_hook( 'give_email_reports_daily_email' );
			}

			return intval( $value );
		}

		return $value;
	}

}

new Give_Email_Reports_Settings();