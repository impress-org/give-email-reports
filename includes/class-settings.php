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

		add_action(
			'give_admin_field_email_report_daily_schedule', array(
				$this,
				'add_email_report_daily_schedule',
			), 10, 2
		);
		add_action(
			'give_form_field_email_report_daily_schedule', array(
				$this,
				'form_add_email_report_daily_schedule',
			), 10, 2
		);

		add_action(
			'give_admin_field_email_report_weekly_schedule', array(
				$this,
				'add_email_report_weekly_schedule',
			), 10, 2
		);
		add_action(
			'give_form_field_email_report_weekly_schedule', array(
				$this,
				'form_add_email_report_weekly_schedule',
			), 10, 2
		);

		add_action(
			'give_admin_field_email_report_monthly_schedule', array(
				$this,
				'add_email_report_monthly_schedule',
			), 10, 2
		);
		add_action(
			'give_form_field_email_report_monthly_schedule', array(
				$this,
				'form_add_email_report_monthly_schedule',
			), 10, 2
		);

		add_filter(
			'give_admin_settings_sanitize_option_email_report_emails', array(
				$this,
				'sanitize_settings',
			), 10, 1
		);

		// Register schedule email reports on per form basis.
		add_filter( 'give_metabox_form_data_settings', array( $this, 'per_form_settings' ), 10, 2 );

		add_action( 'give_post_process_give_forms_meta', array( $this, 'save' ), 10, 1 );
	}

	/**
	 * Save Donation form meta value in Email report
	 *
	 * @param int $form_id Donation Form id.
	 *
	 * @return void
	 * @since 1.2
	 */
	public function save( $form_id ) {
		$settings = array(
			'give_email_reports_daily_email_template',
			'give_email_reports_daily_email_delivery_time',
			'give_email_reports_weekly_email_template',
			'give_email_reports_weekly_email_delivery_time',
			'give_email_reports_monthly_email_template',
			'give_email_reports_monthly_email_delivery_time',
		);

		foreach ( $settings as $setting ) {
			if ( ! isset( $_POST[ $setting ] ) ) {
				continue;
			}

			give_update_meta( $form_id, "_{$setting}", give_clean( $_POST[ $setting ] ) );
		}
	}

	/**
	 * Add Per Form Email report setting.
	 *
	 * @param array $settings Donation Form edit setting.
	 * @param array $form_id  Donation Form ID.
	 *
	 * @return array $settings Donation Form edit setting
	 * @since 1.2
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
					'description'    => __( 'This option enables an email report for just this donation form. Once enabled you will see additional subtab options for daily, weekly, and monthly email reports.', 'give-email-reports'),
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
			 * @param array $sub_menu Sub menu option for email report setting
			 * @param array $form_id  Donation Form ID.
			 *
			 * @return array $sub_menu Sub menu option for email report setting
			 * @since 1.2
			 */
			'sub-fields' => apply_filters( 'give_email_report_options_metabox_fields', array(), $form_id ),
		);

		return $settings;
	}

	/**
	 * Check if email_report_emails is empty or not.
	 *
	 * @param array $value From $_POST['email_report_emails] value.
	 *
	 * @return array The modified $value From $_POST['email_report_emails] value.
	 * @since 1.0.2
	 */
	public function sanitize_settings( $value = array() ) {
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
	 * @param array $emails emails ID.
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
	 * @param object $field Email fields.
	 * @param string $value field value.
	 */
	public function add_email_report_daily_schedule( $field, $value ) {
		// Setting attribute.
		$disabled_field = $this->is_cron_enabled( 'give_email_reports_daily_email' ) ? ' disabled="disabled"' : '';

		// Times.
		$times = $this->get_email_report_times();
		$value = ! empty( $value ) ? $value : '1900';

		ob_start();
		?>
		<tr valign="top">
			<?php if ( ! empty( $field['name'] ) && ! in_array( $field['name'], array( '&nbsp;' ) ) ) : ?>
				<th scope="row" class="titledesc">
					<label
						for="<?php echo esc_attr( $field['name'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
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
						// Time select options.
						foreach ( $times as $military => $time ) {
							echo '<option value="' . $military . '" ' . selected( $value, $military, false ) . '>' . $time . '</option>';
						}
						?>
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
	 * @param object   $field   Custom fields for daily schedule on per form basis.
	 * @param int|null $form_id Donation form ID.
	 */
	public function form_add_email_report_daily_schedule( $field, $form_id = null ) {
		$value     = '';
		$cron_name = 'give_email_reports_daily_per_form';

		if ( ! empty( $form_id ) ) {
			$value = give_get_meta( $form_id, '_give_email_reports_daily_email_delivery_time', true );
		}

		$disabled_field = $this->is_cron_enabled( $cron_name, array( 'form_id' => $form_id ) ) ? ' disabled="disabled"' : '';

		$times = $this->get_email_report_times();
		?>
		<fieldset
			class="give-field-wrap <?php echo esc_attr( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
			<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['name'] ); ?></label>
			<select class="cmb2_select" name="<?php echo esc_attr( $field['id'] ); ?>"
					id="<?php echo esc_attr( $field['id'] ); ?>" <?php echo $disabled_field; ?> >
				<?php
				// Time select options.
				foreach ( $times as $military => $time ) {
					echo '<option value="' . $military . '" ' . selected( $value, $military, false ) . '>' . $time . '</option>';
				}
				?>
			</select>

			<?php $this->print_reset_button( $cron_name, array( 'form_id' => $form_id ) ); ?>

			<p class="give-field-description">
				<?php echo esc_html( $field['desc'] ); ?>
			</p>
		</fieldset>
		<?php
	}

	/**
	 * Get all the day name.
	 *
	 * @return array $days get days.
	 */
	public function get_days() {
		// Days.
		$days = array(
			'0' => __( 'Sunday', 'give-email-reports' ),
			'1' => __( 'Monday', 'give-email-reports' ),
			'2' => __( 'Tuesday', 'give-email-reports' ),
			'3' => __( 'Wednesday', 'give-email-reports' ),
			'4' => __( 'Thursday', 'give-email-reports' ),
			'5' => __( 'Friday', 'give-email-reports' ),
			'6' => __( 'Saturday', 'give-email-reports' ),
			'7' => __( 'Sunday', 'give-email-reports' ),
		);

		return $days;
	}

	/**
	 * Give add Weekly email reports preview.
	 *
	 * @param object $field
	 * @param array  $value
	 */
	public function add_email_report_weekly_schedule( $field, $value ) {
		// Setting attribute.
		$disabled_field = $this->is_cron_enabled( 'give_email_reports_weekly_email' ) ? ' disabled="disabled"' : '';

		// Times.
		$times = $this->get_email_report_times();

		$days = $this->get_days();

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
						$selected_day = isset( $value['day'] ) ? $value['day'] : '0';
						echo '<option value="' . $day_code . '" ' . selected( $selected_day, $day_code, true ) . '>' . $day . '</option>';
					}
					?>
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
						}
						?>
					</select>

					<?php $this->print_reset_button( 'give_email_reports_weekly_email' ); ?>


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
	 * Give add weekly email reports preview.
	 *
	 * @param object   $field   Custom fields for weekly schedule on per form basis.
	 * @param int|null $form_id Donation form ID.
	 */
	public function form_add_email_report_weekly_schedule( $field, $form_id = null ) {
		$value     = '';
		$cron_name = 'give_email_reports_weekly_per_form';

		if ( ! empty( $form_id ) ) {
			$value = give_get_meta( $form_id, '_give_email_reports_weekly_email_delivery_time', true );
		}

		// Setting attribute.
		$disabled_field = $this->is_cron_enabled( $cron_name, array( 'form_id' => $form_id ) ) ? ' disabled="disabled"' : '';

		// Times.
		$times = $this->get_email_report_times();

		$days = $this->get_days();
		?>
		<fieldset
			class="give-field-wrap <?php echo esc_attr( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
			<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo $field['name']; ?></label>

			<select class="cmb2_select"
					name="<?php echo "{$field['id']}[day]"; ?> id="<?php echo "{$field['id']}_day"; ?>
			"<?php echo $disabled_field; ?>>
			<?php
			// Day select dropdown.
			$selected_day = isset( $value['day'] ) ? $value['day'] : '0';
			foreach ( $days as $day_code => $day ) {
				echo '<option value="' . $day_code . '" ' . selected( $selected_day, $day_code, true ) . '>' . $day . '</option>';
			}
			?>
			</select>

			<select class="cmb2_select" name="<?php echo "{$field['id']}[time]"; ?>"
					id="<?php echo "{$field['id']}_time"; ?>"<?php echo $disabled_field; ?>>
				<?php
				// Time select options.
				$selected_time = isset( $value['time'] ) ? $value['time'] : '1900';
				foreach ( $times as $military => $time ) {
					echo '<option value="' . $military . '" ' . selected( $selected_time, $military, false ) . '>' . $time . '</option>';
				}
				?>
			</select>

			<?php $this->print_reset_button( $cron_name, array( 'form_id' => $form_id ) ); ?>

			<p class="give-field-description">
				<?php echo $field['desc']; ?>
			</p>
		</fieldset>
		<?php
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
		$days = $this->monthly_days();

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
							$selected_day = isset( $value['day'] ) ? $value['day'] : 'first';
							echo '<option value="' . $day_code . '" ' . selected( $selected_day, $day_code, true ) . '>' . $day . '</option>';
						}
						?>
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
						}
						?>
					</select>

					<?php $this->print_reset_button( 'give_email_reports_monthly_email' ); ?>

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
	 * Give add Monthly email reports preview.
	 *
	 * @param object $field
	 * @param array  $form_id Donation form id
	 *
	 * @return void
	 */
	public function form_add_email_report_monthly_schedule( $field, $form_id ) {
		$value     = '';
		$cron_name = 'give_email_reports_monthly_per_form';

		if ( ! empty( $form_id ) ) {
			$value = give_get_meta( $form_id, '_give_email_reports_monthly_email_delivery_time', true );
		}

		// Setting attribute.
		$disabled_field = $this->is_cron_enabled( $cron_name, array( 'form_id' => $form_id ) ) ? ' disabled="disabled"' : '';

		// Times.
		$times = $this->get_email_report_times();

		// Days.
		$days = $this->monthly_days();

		ob_start();
		?>
		<fieldset
			class="give-field-wrap <?php echo esc_attr( $field['id'] ); ?>_field <?php echo esc_attr( $field['wrapper_class'] ); ?>">
			<label for="<?php echo "{$field['id']}_day"; ?>"><?php echo $field['name']; ?></label>

			<select class="cmb2_select" name="<?php echo "{$field['id']}[day]"; ?>"
					id="<?php echo "{$field['id']}_day"; ?>"<?php echo $disabled_field; ?>>
				<?php
				// Day select dropdown.
				foreach ( $days as $day_code => $day ) {
					$selected_day = isset( $value['day'] ) ? $value['day'] : 'first';
					echo '<option value="' . $day_code . '" ' . selected( $selected_day, $day_code, true ) . '>' . $day . '</option>';
				}
				?>
			</select>

			<select class="cmb2_select" name="<?php echo "{$field['id']}[time]"; ?>"
					id="<?php echo "{$field['id']}_time"; ?>"<?php echo $disabled_field; ?>>
				<?php
				// Time select options.
				foreach ( $times as $military => $time ) {
					$selected_time = isset( $value['time'] ) ? $value['time'] : '1900';
					echo '<option value="' . $military . '" ' . selected( $selected_time, $military, false ) . '>' . $time . '</option>';
				}
				?>
			</select>

			<?php $this->print_reset_button( $cron_name, array( 'form_id' => $form_id ) ); ?>

			<p class="give-field-description">
				<?php echo $field['desc']; ?>
			</p>
		</fieldset>
		<?php
		echo ob_get_clean();
	}

	/**
	 * Get days for monthly email report
	 *
	 * @return array $days Monthly days
	 * @since 1.2.
	 */
	public function monthly_days() {
		// Days.
		$days = array(
			'first' => __( 'First Day', 'give-email-reports' ),
			'last'  => __( 'Last Day', 'give-email-reports' ),
		);

		return $days;
	}

	/**
	 * Print cron reset button.
	 *
	 * @param string $cron_name Email report cron name.
	 * @param array  $args      Cron arguments.
	 *
	 * @return void.
	 */
	function print_reset_button( $cron_name, $args = array() ) {
		global $thepostid;
		if ( wp_next_scheduled( $cron_name, $args ) ) :
			?>
			<button
				class="give-reset-button button-secondary"
				data-cron="<?php echo esc_attr( $cron_name ); ?>"
				data-form_id="<?php echo esc_attr( $thepostid ); ?>"
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
	 * @param array  $args      Cron argument.
	 *
	 * @return bool
	 */
	function is_cron_enabled( $cron_name, $args = array() ) {
		return wp_next_scheduled( $cron_name, $args ) ? true : false;
	}

	/**
	 * Email report time schedule. Populates the admin field dropdown.
	 *
	 * @return array
	 */
	public function get_email_report_times() {
		return apply_filters(
			'give_email_report_times',
			array(
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
			)
		);
	}
}

new Give_Email_Reports_Settings();
