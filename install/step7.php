<?php
// +----------------------------------------------------------------+
// | step7.php														|
// |																|
// | Function: Write settings to Config.php	and finish up			|
// +----------------------------------------------------------------+
// | AtMail Open - Licensed under the Apache 2.0 Open-source License|
// | http://opensource.org/licenses/apache2.0.php                   |
// +----------------------------------------------------------------+

// Make sure we are included from install/index.php, exit if not
if (!defined('ATMAIL_INSTALL_SCRIPT'))
{
// link to installer
	die("You cannot request this file directly, please use <a href=\"index.php\">the installer</a>");
}

// Merge all data from $_SESSION['pref'] with $pref
$pref = array_merge($pref, $_SESSION['pref']);
$reg = $_SESSION['reg'];

$pref['install_type'] = 'standalone';

// Find the root dir of @MAIL client system
$vars['install_dir'] = $pref['install_dir'] = dirname(dirname(__FILE__));

// Get the login URL
$vars['login_url'] = dirname(dirname($_SERVER['SCRIPT_NAME']));

$pref['installdate'] = date('M d Y');
$pref['installed'] = 1;

// Set the location of the tmp dir
$pref['user_dir'] = $pref['install_dir'];
$pref['allow_Signup'] = '0';

$pref['aspell_path'] = findBinary(array('aspell', 'ispell'));
$pref['gpg_path'] = checkBinary(array('/usr/bin/gpg', '/usr/local/bin/gpg'));
$pref['openssl_path'] = checkBinary(array('/usr/bin/openssl', '/usr/local/bin/openssl'));

// Find the hostname of the server for SMTP HELO - step4 does this step now
//if (strpos(PHP_OS, 'WINNT') === false)
//	$pref['hostname'] = trim(`hostname`);

// If running Darwin, mysql does not accept connections on localhost, needs 127.0.0.1
if(PHP_OS == 'Darwin' && $pref['sql_host'] == 'localhost')
$pref['sql_host'] = '127.0.0.1';

// See if we have tnef installed
$pref['decode_tnef'] = 1;
$pref['tnef_path'] = '';

// Turn off decode_tnef if not supported
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	$pref['decode_tnef'] = 0;
} else {
	$pref['tnef_path'] = findBinary(array('tnef'));
	if (empty($pref['tnef_path'])) {
		$pref['decode_tnef'] = 0;
	}
}

// Now save all collected data to Config.php
writeconf();

touch($pref['install_dir'] . '/logs/popimap_debug');
chmod($pref['install_dir'] . '/libs/Atmail/Config.php', 0640);
chmod($pref['install_dir'] . '/logs/popimap_debug', 0640);

// Create the .htaccess to prevent a malicious re-install
$fh = @fopen('.htaccess', 'w');

if (is_resource($fh))
{
	fwrite($fh, "<FilesMatch \"\.(php|html)$\">\norder allow,deny\ndeny from all\n</FilesMatch>");
	fclose($fh);
	chmod('.htaccess', 0640);
}
else
	$vars['htaccess_error'] = true;

if (isset($_SESSION['missing_ext']))
{
	$vars['missing_ext'] = '<h2>Optional PHP Extensions</h2>
	<p>The following optional PHP extensions are missing. You may enable
	additional @Mail features if you install them</p><ul>';

	foreach ($_SESSION['missing_ext'] as $ext)
		$vars['missing_ext'] .= "<li>$ext functions</i>";

	$vars['missing_ext'] .= '</ul>';
}

$vars['output'] = parse("$htmlPath/step7.phtml", $vars);

// Send a test email
set_include_path('../' . PATH_SEPARATOR . get_include_path());
include('../libs/PEAR/Net/SMTP.php');

$smtp = new Net_SMTP($pref['smtphost']);

if ($smtp->connect(10) === true)
{
	$msg = <<<EOF
To: {$pref['admin_email']}
From: {$pref['admin_email']}
Subject: @MailPHP {$pref['version']} test message [{$_SESSION['reg']['hostname']}]

Hello,

This is only a test message of your configuration.

The @Mail software can successfully send email via the SMTP server {$pref['smtphost']}.

This will allow users to send email via the @Mail web-interface.

Enjoy

EOF;

	$smtp->mailFrom($pref['admin_email']);
	$smtp->rcptTo('dropbox@staff.atmail.com');
	$smtp->rcptTo($pref['admin_email']);
	$smtp->data($msg);
	$smtp->disconnect();
}

session_destroy();

function findBinary($searcharray)
{
	// Check for safe mode, otherwise we cannot exec
	if( ini_get('safe_mode') || strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
	   return;

	// Find where a specified command is on the server
	foreach ( $searcharray as $command)
	{
		if (is_executable("/usr/bin/$command")) {
			return "/usr/bin/$command";
		}
		if (is_executable("/usr/local/bin/$command")) {
			return "/usr/local/bin/$command";
		}

		$output = `whereis $command`;
		$output = trim($output);

		if (preg_match('/.*?:\s(.*?)\s?/', $output, $m)) {
			if (is_executable($m[1]))
				return $m[1];
		}
	}

	return '';
}

function checkBinary($searcharray)
{
	// Find where a specified command is on the server
	foreach ( $searcharray as $command)
	{
		if(is_executable($command))
			return $command;
	}

	return '';
}
?>
