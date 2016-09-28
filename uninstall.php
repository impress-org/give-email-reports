<?php

if ( ! defined('WP_UNINSTALL_PLUGIN') ) {
	wp_die( __('Plugin uninstallation can not be executed in this fashion.') );
}

wp_clear_scheduled_hook( 'give_email_reports_daily_email' );