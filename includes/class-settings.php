<?php

/**
 * Class Give_Email_Reports_Settings
 */
class Give_Email_Reports_Settings extends Give_Email_Reports {

	/**
	 * Give_Email_Reports_Settings constructor.
	 */
	public function __construct() {
		add_filter( 'give_email_notifications', array( $this, 'register_emails' ) );

		add_action( 'give_admin_field_email_report_daily_schedule', array( $this, 'add_email_report_daily_schedule' ), 10, 5 );
		add_action( 'give_admin_field_email_report_weekly_schedule', array( $this, 'add_email_report_weekly_schedule' ), 10, 5 );
		add_action( 'give_admin_field_email_report_monthly_schedule', array( $this, 'add_email_report_monthly_schedule' ), 10, 5 );

	}

	/**
	 * Register email notifications.
	 *
	 * @access public
	 *
	 * @param $emails
	 *
	 * @return array
	 */
	public function register_emails( $emails ) {
		$emails[] = include GIVE_EMAIL_REPORTS_DIR . 'includes/emails/class-daily-report-email.php';
		$emails[] = include GIVE_EMAIL_REPORTS_DIR . 'includes/emails/class-weekly-report-email.php';
		$emails[] = include GIVE_EMAIL_REPORTS_DIR . 'includes/emails/class-monthly-report-email.php';

		return $emails;
	}

	/**
	 * Give add daily email reports preview.
	 *
	 * @param array  $field
	 * @param string $value
	 */
	public function add_email_report_daily_schedule( $field, $value ) {
		// Setting attribute.
		$disabled_field = $this->is_cron_enabled( 'give_email_reports_daily_email' ) ? ' disabled="disabled"' : '';

		//Times.
		$times = $this->get_email_report_times();

		ob_start();
		?>
		<tr valign="top">
			<?php if ( ! empty( $field['name'] ) && ! in_array( $field['name'], array( '&nbsp;' ) ) ) : ?>
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field['name'] ); ?>"><?php echo $field['title']; ?></label>
				</th>
			<?php endif; ?>
			<td class="give-forminp">
				<div class="give-email-reports-weekly">
					<label class="hidden" for="<?php echo $field['id']; ?>'">
						<?php _e( 'Time of Day', 'give-email-reports' ); ?>
					</label>

					<select
							class="cmb2_select"
							name="<?php echo $field['id']; ?>"
							id="<?php echo $field['id']; ?>"
						<?php echo $disabled_field; ?>
					>
						<?php
						//Time select options.
						foreach ( $times as $military => $time ) {
							echo '<option value="' . $military . '" ' . selected( $value, $military, false ) . '>' . $time . '</option>';
						} ?>
					</select>

					<?php $this->print_reset_button( 'give_email_reports_daily_email' ); ?>

					<p class="give-field-description">
						<?php _e( 'Select when you would like to receive your daily email report.', 'give-email-reports' ); ?>
					</p>

				</div>
			</td>
		</tr>
		<?php
		echo ob_get_clean();
	}

	/**
	 * Give add Weekly email reports preview.
	 *
	 * @param array $field
	 * @param string $value
	 */
	public function add_email_report_weekly_schedule( $field, $value ) {
		// Setting attribute.
		$disabled_field = $this->is_cron_enabled( 'give_email_reports_weekly_email' ) ? ' disabled="disabled"' : '';

		// Times.
		$times = $this->get_email_report_times();

		// Days.
		$days = array(
			'0' => 'Sunday',
			'1' => 'Monday',
			'2' => 'Tuesday',
			'3' => 'Wednesday',
			'4' => 'Thursday',
			'5' => 'Friday',
			'6' => 'Saturday',
			'7' => 'Sunday',
		);

		ob_start();
		?>
		<tr valign="top">
			<?php if ( ! empty( $field['name'] ) && ! in_array( $field['name'], array( '&nbsp;' ) ) ) : ?>
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field['name'] ); ?>"><?php echo $field['title']; ?></label>
				</th>
			<?php endif; ?>
			<td class="give-forminp">
				<div class="give-email-reports-weekly">
					<label class="hidden"
						   for="<?php echo "{$field['id']}_day"; ?>"><?php _e( 'Day of Week', 'give-email-reports' ); ?></label>

					<select class="cmb2_select"
							name="<?php echo "{$field['id']}[day]"; ?> id="<?php echo "{$field['id']}_day"; ?>
					"<?php echo $disabled_field; ?>>
					<?php
					// Day select dropdown.
					foreach ( $days as $day_code => $day ) {
						$value['day'] = isset( $value['day'] ) ? $value['day'] : 'sunday';
						echo '<option value="' . $day_code . '" ' . selected( $value['day'], $day_code, true ) . '>' . $day . '</option>';
					} ?>
					</select>

					<label class="hidden"
						   for="<?php echo "{$field['id']}_time"; ?>'"><?php _e( 'Time of Day', 'give-email-reports' ); ?></label>

					<select class="cmb2_select" name="<?php echo "{$field['id']}[time]"; ?>"
							id="<?php echo "{$field['id']}_time"; ?>"<?php echo $disabled_field; ?>>
						<?php
						// Time select options.
						foreach ( $times as $military => $time ) {
							$value['time'] = isset( $value['time'] ) ? $value['time'] : '1900';
							echo '<option value="' . $military . '" ' . selected( $value['time'], $military, false ) . '>' . $time . '</option>';
						} ?>
					</select>

					<?php $this->print_reset_button( 'give_email_reports_weekly_email' ); ?>

					<p class="give-field-description"><?php _e( 'Select the day of the week and time that you would like to receive the weekly report.', 'give-email-reports' ); ?></p>

				</div>
			</td>
		</tr>
		<?php
		echo ob_get_clean();
	}


	/**
	 * Give add Monthly email reports preview.
	 *
	 * @param array $field
	 * @param array $value
	 */
	public function add_email_report_monthly_schedule( $field, $value ) {
		// Setting attribute.
		$disabled_field = $this->is_cron_enabled( 'give_email_reports_monthly_email' ) ? ' disabled="disabled"' : '';

		// Times.
		$times = $this->get_email_report_times();

		// Days.
		$days = array(
			'first' => 'First Day',
			'last'  => 'Last Day',
		);

		ob_start();
		?>
		<tr valign="top">
			<?php if ( ! empty( $field['name'] ) && ! in_array( $field['name'], array( '&nbsp;' ) ) ) : ?>
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field['name'] ); ?>"><?php echo $field['title']; ?></label>
				</th>
			<?php endif; ?>
			<td class="give-forminp">
				<div class="give-email-reports-monthly">
					<label class="hidden"
						   for="<?php echo "{$field['id']}_day"; ?>"><?php _e( 'Day of Month', 'give-email-reports' ); ?></label>

					<select class="cmb2_select" name="<?php echo "{$field['id']}[day]"; ?>"
							id="<?php echo "{$field['id']}_day"; ?>"<?php echo $disabled_field; ?>>
						<?php
						// Day select dropdown.
						foreach ( $days as $day_code => $day ) {
							$value['day'] = isset( $value['day'] ) ? $value['day'] : '0';
							echo '<option value="' . $day_code . '" ' . selected( $value['day'], $day_code, true ) . '>' . $day . '</option>';
						} ?>
					</select>

					<label class="hidden"
						   for="<?php echo "{$field['id']}_time"; ?>'"><?php _e( 'Time of Day', 'give-email-reports' ); ?></label>

					<select class="cmb2_select" name="<?php echo "{$field['id']}[time]"; ?>"
							id="<?php echo "{$field['id']}_time"; ?>"<?php echo $disabled_field; ?>>
						<?php
						// Time select options.
						foreach ( $times as $military => $time ) {
							$value['time'] = isset( $value['time'] ) ? $value['time'] : '1900';
							echo '<option value="' . $military . '" ' . selected( $value['time'], $military, false ) . '>' . $time . '</option>';
						} ?>
					</select>

					<?php $this->print_reset_button( 'give_email_reports_monthly_email' ); ?>

					<p class="give-field-description"><?php _e( 'Select the day of the month and time that would like to receive the monthly report.', 'give-email-reports' ); ?></p>

				</div>
			</td>
		</tr>
		<?php
		echo ob_get_clean();
	}


	/**
	 * Print cron reset button.
	 *
	 * @param string $cron_name Email report cron name.
	 *
	 * @return void.
	 */
	function print_reset_button( $cron_name ) {
		if ( wp_next_scheduled( $cron_name ) ) : ?>
			<button class="give-reset-button button-secondary" data-cron="<?php echo $cron_name; ?>"
			        data-action="give_reset_email_report_cron"><?php echo esc_html__( 'Reschedule', 'give-email-reports' ); ?></button>
			<span class="give-spinner spinner"></span>
			<?php
		endif;
	}

	/**
	 * Check if cron enabled or not.
	 *
	 * @param string $cron_name Email report cron name..
	 *
	 * @return bool
	 */
	function is_cron_enabled( $cron_name ) {
		return wp_next_scheduled( $cron_name ) ? true : false;
	}
}

new Give_Email_Reports_Settings();
