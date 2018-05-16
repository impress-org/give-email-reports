<?php

/**
 * Class Give_Email_Reports_Settings
 */
class Give_Email_Reports_Settings {

	/**
	 * Give_Email_Reports_Settings constructor.
	 */
	public function __construct() {
		add_filter( 'give_email_notifications', array( $this, 'register_emails' ) );

		add_action( 'give_admin_field_email_report_daily_schedule', array( $this, 'add_email_report_daily_schedule' ), 10, 2 );
		add_action( 'give_form_field_email_report_daily_schedule', array( $this, 'form_add_email_report_daily_schedule' ), 10, 2 );

		add_action( 'give_admin_field_email_report_weekly_schedule', array(
			$this,
			'add_email_report_weekly_schedule',
		), 10, 2 );
		add_action( 'give_admin_field_email_report_monthly_schedule', array(
			$this,
			'add_email_report_monthly_schedule',
		), 10, 2 );

		add_filter( 'give_admin_settings_sanitize_option_email_report_emails', array(
			$this,
			'give_admin_settings_sanitize_option_email_report_emails',
		), 10, 1 );

		// Register schedule email reports on per form basis.
		add_filter( 'give_metabox_form_data_settings', array( $this, 'per_form_settings' ), 10, 2 );
	}

	/**
	 * Add Per Form Email report setting.
	 *
	 * @since 1.2.1
	 *
	 * @param array $settings Donation Form edit setting.
	 * @param array $form_id Donation Form ID.
	 *
	 * @return array $settings Donation Form edit setting
	 */
	public function per_form_settings( $settings, $form_id ) {
		// Email notification setting.
		$settings['email_report_options'] = array(
			'id'         => 'email_report_options',
			'title'      => __( 'Email Reports', 'give-email-reports' ),
			'icon-html'  => '<span class="dashicons dashicons-email-alt"></span>',
			'fields'     => array(
				array(
					'name'    => __( 'Email Report Options', 'give-email-reports' ),
					'id'      => '_give_email_report_options',
					'type'    => 'radio_inline',
					'default' => 'disabled',
					'options' => array(
						'enabled'  => __( 'Enabled', 'give-email-reports' ),
						'disabled' => __( 'Disabled', 'give-email-reports' ),
					),
				),
			),

			/**
			 * Filter the email notification settings.
			 *
			 * @since 1.2.1
			 *
			 * @param array $sub_menu Sub menu option for email report setting
			 * @param array $form_id Donation Form ID.
			 *
			 * @return array $sub_menu Sub menu option for email report setting
			 */
			'sub-fields' => apply_filters( 'give_email_report_options_metabox_fields', array(), $form_id ),
		);

		return $settings;
	}

	/**
	 * Check if email_report_emails is empty or not.
	 *
	 * @since 1.0.2
	 *
	 * @param array $value From $_POST['email_report_emails] value
	 *
	 * @return array The modified $value From $_POST['email_report_emails] value.
	 */
	function give_admin_settings_sanitize_option_email_report_emails( $value = array() ) {
		// Check if value is not null.
		if ( is_null( $value ) ) {
			$value = array();
		}

		return $value;
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
	 * @param object $field
	 * @param string $value
	 */
	public function add_email_report_daily_schedule( $field, $value ) {
		// Setting attribute.
		$disabled_field = $this->is_cron_enabled( 'give_email_reports_daily_email' ) ? ' disabled="disabled"' : '';

		// Times.
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
						<?php echo $field['desc']; ?>
					</p>

				</div>
			</td>
		</tr>
		<?php
		echo ob_get_clean();
	}

	/**
	 * Give add daily email reports preview.
	 *
	 * @param object $field Custom fields for daily schedule on per form basis.
	 * @param int $form_id Donation form ID.
	 */
	public function form_add_email_report_daily_schedule( $field, $form_id = null ) {
		$value = '';

		$cron_name = empty( $form_id ) ? 'give_email_reports_daily_email' : 'give_email_reports_daily_email_for_' . $form_id;

		// Setting attribute.
		$disabled_field = $this->is_cron_enabled( $cron_name ) ? ' disabled="disabled"' : '';

		// Times.
		$times = $this->get_email_report_times();
		?>
        <fieldset
                class="give-field-wrap <?php esc_attr_e( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
            <label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo $field['name']; ?></label>
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

			<?php $this->print_reset_button( $cron_name ); ?>

            <p class="give-field-description">
				<?php echo $field['desc']; ?>
            </p>
        </fieldset>
		<?php
	}

	/**
	 * Give add Weekly email reports preview.
	 *
	 * @param object $field
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
						$selected_day = isset( $value['day'] ) ? $value['day'] : 'sunday';
						echo '<option value="' . $day_code . '" ' . selected( $selected_day, $day_code, true ) . '>' . $day . '</option>';
					} ?>
					</select>

					<label class="hidden"
					       for="<?php echo "{$field['id']}_time"; ?>'"><?php _e( 'Time of Day', 'give-email-reports' ); ?></label>

					<select class="cmb2_select" name="<?php echo "{$field['id']}[time]"; ?>"
					        id="<?php echo "{$field['id']}_time"; ?>"<?php echo $disabled_field; ?>>
						<?php
						// Time select options.
						foreach ( $times as $military => $time ) {
							$selected_time = isset( $value['time'] ) ? $value['time'] : '1900';
							echo '<option value="' . $military . '" ' . selected( $selected_time, $military, false ) . '>' . $time . '</option>';
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
	 * @param object $field
	 * @param array  $value
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
							$selected_day = isset( $value['day'] ) ? $value['day'] : '0';
							echo '<option value="' . $day_code . '" ' . selected( $selected_day, $day_code, true ) . '>' . $day . '</option>';
						} ?>
					</select>

					<label class="hidden"
						   for="<?php echo "{$field['id']}_time"; ?>'"><?php _e( 'Time of Day', 'give-email-reports' ); ?></label>

					<select class="cmb2_select" name="<?php echo "{$field['id']}[time]"; ?>"
							id="<?php echo "{$field['id']}_time"; ?>"<?php echo $disabled_field; ?>>
						<?php
						// Time select options.
						foreach ( $times as $military => $time ) {
							$selected_time = isset( $value['time'] ) ? $value['time'] : '1900';
							echo '<option value="' . $military . '" ' . selected( $selected_time, $military, false ) . '>' . $time . '</option>';
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
			<button
					class="give-reset-button button-secondary"
					data-cron="<?php echo $cron_name; ?>"
					data-action="give_reset_email_report_cron"
			>
				<?php echo esc_html__( 'Reschedule', 'give-email-reports' ); ?>
			</button>
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

	/**
	 * Email report time schedule. Populates the admin field dropdown.
	 *
	 * @return array
	 */
	public function get_email_report_times() {
		return apply_filters( 'give_email_report_times', array(
			'0100' => __( '1:00 AM', 'give-email-reports' ),
			'0200' => __( '2:00 AM', 'give-email-reports' ),
			'0300' => __( '3:00 AM', 'give-email-reports' ),
			'0400' => __( '4:00 AM', 'give-email-reports' ),
			'0500' => __( '5:00 AM', 'give-email-reports' ),
			'0600' => __( '6:00 AM', 'give-email-reports' ),
			'0700' => __( '7:00 AM', 'give-email-reports' ),
			'0800' => __( '8:00 AM', 'give-email-reports' ),
			'0900' => __( '9:00 AM', 'give-email-reports' ),
			'1000' => __( '10:00 AM', 'give-email-reports' ),
			'1100' => __( '11:00 AM', 'give-email-reports' ),
			'1200' => __( '12:00 AM', 'give-email-reports' ),
			'1300' => __( '1:00 PM', 'give-email-reports' ),
			'1400' => __( '2:00 PM', 'give-email-reports' ),
			'1500' => __( '3:00 PM', 'give-email-reports' ),
			'1600' => __( '4:00 PM', 'give-email-reports' ),
			'1700' => __( '5:00 PM', 'give-email-reports' ),
			'1800' => __( '6:00 PM', 'give-email-reports' ),
			'1900' => __( '7:00 PM', 'give-email-reports' ),
			'2000' => __( '8:00 PM', 'give-email-reports' ),
			'2100' => __( '9:00 PM', 'give-email-reports' ),
			'2200' => __( '10:00 PM', 'give-email-reports' ),
			'2300' => __( '11:00 PM', 'give-email-reports' ),
			'2400' => __( '12:00 PM', 'give-email-reports' ),
		) );
	}
}

new Give_Email_Reports_Settings();
