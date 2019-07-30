=== Give - Email Reports ===
Contributors: wordimpress
Tags: donation reports, donation, ecommerce, e-commerce, fundraising, fundraiser
Requires at least: 4.8
Tested up to: 5.2.1
Stable tag: 1.1.4
Requires Give: 2.4.7
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Receive comprehensive donation reports via email.

== Description ==

This plugin requires the Give plugin activated to function properly. When activated, it provides admins with the ability to receive comprehensive donation reports via email.

== Installation ==

= Minimum Requirements =

* WordPress 4.8 or greater
* PHP version 5.6 or greater
* MySQL version 5.0 or greater
* Some payment gateways require fsockopen support (for IPN access)

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of Give, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "Give" and click Search Plugins. Once you have found the plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking "Install Now".

= Manual installation =

The manual installation method involves downloading our donation plugin and uploading it to your server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.1.4: July 29th, 2019 =
* Fix: Ensure that cron jobs are set up properly for new installs without requiring the settings to be saved.

= 1.1.3: June 6th, 2019 =
* Fix: Add role check for when cron jobs are run as a routine security hardening.

= 1.1.2: July 5th, 2018 =
* New: Added uninstall.php so the plugin will delete all its settings when deleted.
* Fix: Improvements to report queries and resolved individual form stat discrepancies.

= 1.1.1: January 23rd, 2018 =
* Fix: There was an issue with saving multiple or modifying the report email's recipients. Saving would appear to have to effect modifying the field and only the admin email could be used. Now you can enter any one or additional recipients and save as expected.

= 1.1: January 17th, 2018 =
* New: Compatibility with the new Give 2.0+ email system. You'll now see all Email Reports emails under Donations > Settings > Emails.
* Tweak: Removed old CMB2 code for settings.
* Fix: Alignment issue when there are no recent donations within the report.
* Fix: Added useful filters for attaching files to reports.
* Fix: PHP warning "Illegal string offset" when settings have not been saved.

= 1.0.2: December 29th, 2018 =
* New: Admins now have the ability to set specific emails to receive reports rather than the ones used for notifications in Give core.
* Fix: Issue in the settings when unselecting all the report frequencies at once it would cause one report to always be selected.

= 1.0.1 =
* New: The plugin now checks to see if Give is active and up to the minimum version required to run the plugin
* Fix: PHP Warning - Missing argument 1 for give_email_reports_total() - https://github.com/impress-org/give-email-reports/issues/15
* Fix: Incorrectly passing weekly total in the monthly report.
* Fix: PHP notices displayed if give email reports option have yet to be set.

= 1.0 =
* Initial plugin release. Yippee!
