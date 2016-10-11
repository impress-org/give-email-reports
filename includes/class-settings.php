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
		add_action( 'cmb2_render_email_report_monthly_schedule', array(
			$this,
			'add_email_report_monthly_schedule'
		), 10, 5 );

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

		$email_reports_settings = array(
			array(
				'name' => __( 'Email Reports Settings', 'give-email-reports' ),
				'desc' => '<hr>',
				'id'   => 'give_title',
				'type' => 'give_title'
			),
			array(
				'id'   => 'email_reports_settings',
				'name' => __( 'Preview Report', 'give-email-reports' ),
				'desc' => '',
				'type' => 'email_report_preview'
			),
			array(
				'name'              => __( 'Report Frequency', 'give-email-reports' ),
				'desc'              => __( 'Select the time frames that you would like to receive email reports.', 'give-email-reports' ),
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
				'row_classes'    => 'cmb-type-email-report-daily-schedule',
				'default' => '1900',
				'options' => $this->get_email_report_times()
			),
			array(
				'id'   => 'give_email_reports_weekly_email_delivery_time',
				'name' => __( 'Weekly Email Delivery Time', 'give-email-reports' ),
				'desc' => __( 'Select when you would like to receive your weekly email report.', 'give-email-reports' ),
				'type' => 'email_report_weekly_schedule',
			),
			array(
				'id'   => 'give_email_reports_monthly_email_delivery_time',
				'name' => __( 'Monthly Email Delivery Time', 'give-email-reports' ),
				'desc' => __( 'Select when you would like to receive your monthly email report.', 'give-email-reports' ),
				'type' => 'email_report_monthly_schedule',
			),
		);

		return array_merge( $settings, $email_reports_settings );
	}

	/**
	 * Give add email reports preview.
	 *
	 * @since 1.0
	 */
	public function add_email_report_preview() {
		ob_start();
		?>
		<a href="<?php echo esc_url( add_query_arg( array( 'give_action' => 'preview_email_report' ), home_url() ) ); ?>"
		   class="button-secondary" target="_blank"
		   title="<?php _e( 'Preview Email Report', 'give-email-reports' ); ?> "><?php _e( 'Preview Email Report', 'give-email-reports' ); ?></a>
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

		//Times.
		$times = $this->get_email_report_times();

		//Days.
		$days = array(
			'0' => 'Sunday',
			'1' => 'Monday',
			'2' => 'Tuesday',
			'3' => 'Wednesday',
			'4' => 'Thursday',
			'5' => 'Friday',
			'6' => 'Saturday',
			'7' => 'Sunday'
		);

		ob_start(); ?>
		<div class="give-email-reports-weekly">
			<label class="hidden"
			       for="<?php echo $field_type->_id( '_day' ); ?>"><?php _e( 'Day of Week', 'give-donation-emails' ); ?></label>

			<select class="cmb2_select" name="<?php echo $field_type->_name( '[day]' ); ?>"
			        id="<?php echo $field_type->_id( '_day' ); ?>">
				<?php
				//Day select dropdown
				foreach ( $days as $day_code => $day ) {
					$value['day'] = isset( $value['day'] ) ? $value['day'] : 'sunday';
					echo '<option value="' . $day_code . '" ' . selected( $value['day'], $day_code, true ) . '>' . $day . '</option>';
				} ?>
			</select>

			<label class="hidden"
			       for="<?php echo $field_type->_id( '_time' ); ?>'"><?php _e( 'Time of Day', 'give-donation-emails' ); ?></label>

			<select class="cmb2_select" name="<?php echo $field_type->_name( '[time]' ); ?>"
			        id="<?php echo $field_type->_id( '_time' ); ?>">
				<?php
				//Time select options.
				foreach ( $times as $military => $time ) {
					$value['time'] = isset( $value['time'] ) ? $value['time'] : '1900';
					echo '<option value="' . $military . '" ' . selected( $value['time'], $military, false ) . '>' . $time . '</option>';
				} ?>
			</select>

			<p class="cmb2-metabox-description"><?php _e( 'Select the day of the week you would like to receive the weekly report.', 'give-email-reports' ); ?></p>

		</div>

		<?php echo ob_get_clean();
	}


	/**
	 * Give add Monthly email reports preview.
	 *
	 * @param $field
	 * @param $value
	 * @param $object_id
	 * @param $object_type
	 * @param $field_type CMB2_Types
	 */
	public function add_email_report_monthly_schedule( $field, $value, $object_id, $object_type, $field_type ) {

		//Times.
		$times = $this->get_email_report_times();

		//Days.
		$days = array(
			'0' => 'First Day',
			'1' => 'Last Day',
		);

		ob_start(); ?>
		<div class="give-email-reports-monthly">
			<label class="hidden"
			       for="<?php echo $field_type->_id( '_day' ); ?>"><?php _e( 'Day of Month', 'give-donation-emails' ); ?></label>

			<select class="cmb2_select" name="<?php echo $field_type->_name( '[day]' ); ?>"
			        id="<?php echo $field_type->_id( '_day' ); ?>">
				<?php
				//Day select dropdown
				foreach ( $days as $day_code => $day ) {
					$value['day'] = isset( $value['day'] ) ? $value['day'] : '0';
					echo '<option value="' . $day_code . '" ' . selected( $value['day'], $day_code, true ) . '>' . $day . '</option>';
				} ?>
			</select>

			<label class="hidden"
			       for="<?php echo $field_type->_id( '_time' ); ?>'"><?php _e( 'Time of Day', 'give-donation-emails' ); ?></label>

			<select class="cmb2_select" name="<?php echo $field_type->_name( '[time]' ); ?>"
			        id="<?php echo $field_type->_id( '_time' ); ?>">
				<?php
				//Time select options.
				foreach ( $times as $military => $time ) {
					$value['time'] = isset( $value['time'] ) ? $value['time'] : '1900';
					echo '<option value="' . $military . '" ' . selected( $value['time'], $military, false ) . '>' . $time . '</option>';
				} ?>
			</select>

			<p class="cmb2-metabox-description"><?php _e( 'Select the day of the month would like to receive the monthly report.', 'give-email-reports' ); ?></p>

		</div>

		<?php echo ob_get_clean();
	}


}

new Give_Email_Reports_Settings();