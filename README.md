# Give - Email Reports #

Receive comprehensive donation reports via email.

## Description ##

This plugin requires the Give plugin activated to function properly. When activated, it provides admins with the ability to receive comprehensive donation reports via email.

## Installation ##

### Minimum Requirements ###

* WordPress 4.5 or greater
* PHP version 5.3 or greater
* MySQL version 5.0 or greater
* Some payment gateways require fsockopen support (for IPN access)

### Automatic installation ###

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of Give, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "Give" and click Search Plugins. Once you have found the plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking "Install Now".

### Manual installation ###

The manual installation method involves downloading our donation plugin and uploading it to your server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

### Updating ###

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

## Changelog ##

### 1.0.2: December 29th, 2018 ###
* New: Admins now have the ability to set specific emails to receive reports rather than the ones used for notifications in Give core.
* Fix: Issue in the settings when unselecting all the report frequencies at once it would cause one report to always be selected.

### 1.0.1 ###
* New: The plugin now checks to see if Give is active and up to the minimum version required to run the plugin
* Fix: PHP Warning - Missing argument 1 for give_email_reports_total() - https://github.com/WordImpress/Give-Email-Reports/issues/15
* Fix: Incorrectly passing weekly total in the monthly report.
* Fix: PHP notices displayed if give email reports option have yet to be set.

### 1.0 ###
* Initial plugin release. Yippee!