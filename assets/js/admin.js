/*!
 * Give Email Reports Admin Forms JS
 *
 * @description: The Give Admin Settings scripts. Only enqueued on the give-settings page; used for tabs and other show/hide functionality
 * @package:     Give
 * @since:       1.0
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
jQuery.noConflict();
jQuery(document).ready(function ($) {

	/**
	 * Email Report Checkboxes Toggle Show/Hide
	 */
	var $reports = jQuery('input[name="email_report_emails[]"]');

	$reports.on('change', function () {

		var val     = $(this).val();
		var checked = $(this).prop('checked');

		//Show fields
		if (checked) {
			$('.cmb-type-email-report-' + val + '-schedule').show();
		} else {
			//Hide fields
			$('.cmb-type-email-report-' + val + '-schedule').hide();
		}

	}).change();

});
