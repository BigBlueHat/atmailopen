<?php
// +----------------------------------------------------------------+
// | step3.php														|
// |																|
// | Function: Setup SMTP host and admin email 						|
// +----------------------------------------------------------------+
// | AtMail Open - Licensed under the Apache 2.0 Open-source License|
// | http://opensource.org/licenses/apache2.0.php                   |
// +----------------------------------------------------------------+
// | Date: May 2006													|
// +----------------------------------------------------------------+


// Make sure we are included from install/index.php, exit if not
if (!defined('ATMAIL_INSTALL_SCRIPT')) {
	// link to installer
	die("You cannot request this file directly, please use <a href=\"index.php\">the installer</a>");
}

$errors = array();

// If the form from Step 3 has been submitted
// we need to process it
if (isset($_POST['submit'])) {
	// Verify the submitted data
	$smtpHost = $_SESSION['pref']['smtphost'] = $_POST['smtphost'];
	$adminEmail = $_SESSION['pref']['admin_email'] = $_POST['admin_email'];
	
	
	// Store any LDAP info
	if (!empty($_POST['ldap_server']) && !empty($_POST['ldap_base_dn'])) {
		$_SESSION['pref']['ldap_server'] = $_POST['ldap_server'];
		$_SESSION['pref']['base_dn'] = $_POST['ldap_base_dn'];
		$_SESSION['pref']['addressbook_ldap_entries'] = 1;
		$_SESSION['pref']['autocomplete_ldap_entries'] = 1;
		$_SESSION['pref']['ldap_passwd'] = $_POST['ldap_passwd'];
	}	
	
	$webmailDir = realpath('../');

	if ($_POST['subscribe'] == 1) {
		if (ini_get('allow_url_fopen')) {
			file_get_contents("http://atmail.com/newsletter_submit.php?Email=$adminEmail&List=AO");
		} else {
			// dirty hack using <img> tag...
			$_SESSION['subscribe_hack'] = 1;
		}
	}
	
	if (empty($smtpHost))
		$smtpHost = 'localhost';

	// Try to connect to SMTP host
	set_include_path('../' . PATH_SEPARATOR . get_include_path());
	require_once('../libs/PEAR/Net/SMTP.php');
	$smtp = new Net_SMTP($smtpHost);
	$con = $smtp->connect();

	if(isset($_POST['smtp_auth'])) {
		$_SESSION['pref']['smtpauth_username'] = $_POST['smtpauth_username'];
		$_SESSION['pref']['smtpauth_password'] = $_POST['smtpauth_password'];

		if($smtp->auth($_SESSION['pref']['smtpauth_username'] , $_SESSION['pref']['smtpauth_password'] ) !== true) {
		$errors['smtp_error'] .= "The SMTP authentication details provided are incorrect. Please verify you have the correct username and password. Check your SMTP configuration for further details, or use an SMTP server which you have IP relay permissions.";
		$vars['smtphost'] = $_SESSION['pref']['smtphost'];
		$vars['smtpauth_username'] = $_SESSION['pref']['smtpauth_username'];
		$vars['smtpauth_password'] = $_SESSION['pref']['smtpauth_password'];
		$vars['admin_email'] = $_SESSION['pref']['admin_email'];
		$vars['smtp_auth_error'] = '1';

		$vars['smtp_auth_check'] = 'checked';

		} else {
			$_SESSION['pref']['smtpauth_username'] = '';
			$_SESSION['pref']['smtpauth_password'] = '';
		}
	}

	if (PEAR::isError($con)) {
		$errors['smtp_error'] = $con->getMessage();
		$vars['bad_smtp_host'] = $smtpHost;
		$vars['smtphost'] = $smtpHost;
		$vars['admin_email'] = $adminEmail;
	} else {
		$smtp->disconnect();
	}
	
	if (!preg_match('/[a-zA-Z0-9\-\.]+@[a-zA-Z0-9\-\.]+\.[a-zA-Z]+/', $adminEmail)) {
		$errors['admin_email_error'] = true;
	}
	
	if (!count($errors)){
		gotoStep(4);
	}
}

// Print the Step 3 page if no data submitted yet
// or there were errors in submitted data


// Create some default values
if(!$vars['smtphost']) {
	$vars['smtphost'] = 'localhost';
}

// merge any pref values into $vars so if we are
// returning from a latter step the values are auto
// completed
if (isset($_SESSION['pref']['smtphost']) && !count($errors)) {
	$vars = array_merge($vars, $_SESSION['pref']);
}

$vars['output'] = parse("$htmlPath/step3.phtml", array_merge($vars, $errors));
