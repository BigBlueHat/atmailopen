<?php
session_start();

// +----------------------------------------------------------------+
// | install/index.php												|
// |																|
// | Function: Install script for @Mail webmail client				|
// +----------------------------------------------------------------+
// | AtMail Open - Licensed under the Apache 2.0 Open-source License|
// | http://opensource.org/licenses/apache2.0.php                   |
// +----------------------------------------------------------------+
// | Date: May 2006													|
// +----------------------------------------------------------------+

// Setup lib paths
$path = dirname(dirname(__FILE__));
set_include_path('.' . PATH_SEPARATOR . "$path/libs" . PATH_SEPARATOR . "$path/libs/Atmail" . PATH_SEPARATOR . "$path/libs/PEAR");

// Report all errors except E_NOTICE
error_reporting(E_ALL ^ E_NOTICE);

if (file_exists('../libs/Atmail/Config.php'))
    include_once('../libs/Atmail/Config.php');




$pref['installed'] = 0;
// Security Check! Double check that the .htaccess file does not exist!!
// If the directory under which we are installed has "AllowOverride none"
// or "AccessFile" is not == ".htaccess" in httpd.conf then our .htaccess
// will not have stopped access
// Do before copying Config.php.default, otherwise will overwrite it!
if (file_exists('.htaccess') || (isset($pref) && $pref['installed']))
{
        echo <<<EOF
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>@Mail already installed</title>
</head><body>
<h1>Install Error</h1>
<p>The installer can not go any further as it seems you have already installed @Mail
on your system.</p>
<p>For added security the installer cannot proceed until you remove the
webmail/install/.htaccess file. Once removed reload the browser to continue the installation.</p>
</body></html>
EOF;

        exit;
}

/*
if (!file_exists('../../server-install.php'));
{

		$vars['install_error'] = "Server mode package of @Mail detected. The Web-based installer cannot be run for this mode, please run via the command line as root: cd /usr/local/atmail/ ; php server-install.php ";
		$vars['install_error2'] = "If you only require to install the Webmail version of @Mail, delete the /usr/local/atmail/server-install.php script and reload the browser to continue";

	    $vars['output'] = parse("html/english/install_error.html", $vars);
	    echo parse("html/english/template.html", $vars);
		exit;
}
*/

if (!file_exists('../libs/Atmail/Config.php'))
{
    if (!copy('../libs/Atmail/Config.php.default', '../libs/Atmail/Config.php'))
    {
        $libDir = dirname(dirname(__FILE__)) . '';

	    $vars['php_version'] = PHP_VERSION;
		$vars['install_error'] = "Please ensure that $libDir is writable by webserver user. See the <a href='http://support.atmail.com/php/webmail-install.html'>installation documentation</a> for the complete tutorial on installing @Mail";
		$vars['install_error2'] = "To change the permissions run on the command-line: <p><em>chown -R [webserveruser] $libDir</em><p>Where [webserveruser] is the user which runs Apache ( Generally user apache, nobody or www )";

	    $vars['output'] = parse("html/english/install_error.html", $vars);
	    echo parse("html/english/template.html", $vars);
		exit;
       //die("Please ensure that $libDir is writable by webserver user. See the installation documentation for the complete tutorial on installing @Mail");
    }
    include_once('../libs/Atmail/Config.php');
}

// Initial check for PHP version >= 4.3.0
if (version_compare(PHP_VERSION, '4.3.0', '<'))
{
    $vars['php_version'] = PHP_VERSION;
    $vars['install_error'] = 'PHP Version not Supported';
	$vars['install_error2'] = "@Mail requires PHP version >= 4.3.0. Your version is {$vars['php_version']}, the installation cannot proceed until you install a more recent version of PHP";
    $vars['output'] = parse("html/english/install_error.html", $vars);

    echo parse("html/english/template.html", $vars);
    exit;
}


//$pref['opensource'] = 1;

// Detect open-source version
if (isset($pref['opensource']))
    define('ATMAIL_OPEN_SOURCE', 1);

// Set session vars for extension checks
$_SESSION['checked_extensions'] = 1;
if (count($ext['opt']))
{
    $_SESSION['missing_ext'] = $ext['opt'];
}

$_SESSION['pref']['version'] = $pref['version'];

// Set our lang var if known
$lang = isset($_SESSION['step1']['lang']) ? $_SESSION['step1']['lang'] : 'english';

// If language $lang is not supported for installation script
// default to english
if (@is_dir("html/$lang") && $lang)
	$htmlPath = "html/$lang";
else
	$htmlPath = 'html/english';


// Set up a constant to prove to the install step scripts
// that they have been included by this script
define('ATMAIL_INSTALL_SCRIPT', 1);

// Find out which step of the install is required
$vars['step'] = $step = (!empty($_REQUEST['step'])) ? $_REQUEST['step'] : 0;

if (file_exists("step$step.php"))
{
	require_once("step$step.php");
}
else
	$vars['output'] = parse("$htmlPath/file_missing_error.html");

// Output the page
echo parse("$htmlPath/template.phtml", $vars);


//
// Functions
//


// Send user to a install step
function gotoStep($step, $args=null)
{
	if (is_array($args))
	{
		foreach ($args as $k => $v)
			$extra .= "&$k=$v";
	}

	$s = empty($_SERVER["HTTPS"]) ? "" : ($_SERVER["HTTPS"] == "on") ? "s" : "";
	$protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
	$port = ($_SERVER['SERVER_PORT'] != '80') ? ':'.$_SERVER['SERVER_PORT'] : '';
	$location = $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['SCRIPT_NAME']."?step=$step$extra";
	header("Location: $location");
	exit;
}


// Parse a HTML/Template file, and expand all vars into a value
function parse($file, $var=null)
{
	// Make Config vars available
	global $pref, $reg, $domains, $settings;
	
	ob_start();
	include($file);
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

function strleft($s1, $s2)
{
	return substr($s1, 0, strpos($s1, $s2));
}

?>
