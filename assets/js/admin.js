/*!
 * Give Email Reports Admin Forms JS
 *
 * @description: The Give Admin Settings scripts. Only enqueued on the give-settings page; used for tabs and other show/hide functionality
 * @package:     Give
 * @since:       1.0
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, GiveWP
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

    /**
     * Reset button click
     */
    $('.give-reset-button').on('click', function (e) {
        e.preventDefault();

        var data = {
                action: $(this).data('action'),
                cron: $(this).data('cron'),
                form_id: $(this).data('form_id')
            },
            reset_button = $(this),
            parent = reset_button.closest('div'),
            spinner = $(this).next();

        $.ajax({
            method: 'POST',
            url: ajaxurl,
            data: data,
            beforeSend: function () {
                spinner.addClass('is-active');
            },
            success: function (res) {
                if (true == res.success) {
                    parent.find('select').removeAttr('disabled');
                    reset_button.hide();
                    spinner.removeClass('is-active');
                }
            }
        }).always(function(){
			spinner.removeClass('is-active');
		});
    });

    // show or hide sub menu on page load
    var $selector = 'body.post-type-give_forms #give-metabox-form-data li.email_report_options_tab';
    setTimeout(function () {
        if ('enabled' === $('body.post-type-give_forms input[name="_give_email_report_options"]:checked').val()) {
            $($selector + ' ul').removeClass('give-hidden');
        } else {
            $($selector + ' ul').addClass('give-hidden');
            $($selector + ' ul').removeClass('give-metabox-sub-tabs');
        }
    }, 100);

    // show or hide sub menu on donation form page.
    $('body.post-type-give_forms').on('change', 'input[name="_give_email_report_options"]', function () {
        $($selector + ' ul').addClass('give-hidden');
        $($selector + ' ul').removeClass('give-metabox-sub-tabs');
        if ('enabled' === $(this).val()) {
            $($selector + ' ul').removeClass('give-hidden');
            $($selector + ' ul').addClass('give-metabox-sub-tabs');
        }
    });

});
