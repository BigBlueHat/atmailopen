<?php
// +----------------------------------------------------------------+
// | step1.php														|
// |																|
// | Function: Select Language										|
// +----------------------------------------------------------------+
// | AtMail Open - Licensed under the Apache 2.0 Open-source License|
// | http://opensource.org/licenses/apache2.0.php                   |
// +----------------------------------------------------------------+
// | Date: May 2006													|
// +----------------------------------------------------------------+


// Make sure we are included from install/index.php, exit if not
if (!defined('ATMAIL_INSTALL_SCRIPT'))
{
	// link to installer
	die("You cannot request this file directly, please use <a href=\"index.php\">the installer</a>");
}

$supportedLanguages = array();

// Skip to the next stage if using Darwin/OSX , we saw the license on the install wizard
if(PHP_OS == 'Darwin') {
	$_SESSION['pref']['Language'] = 'english';
	header('Location: index.php?step=2');
}

// array of languages supported by @Mail WebMail client
foreach (glob('../html/*') as $file)
{
	if ($file != '.' && $file != '..' && @is_dir("$file/xp"))
		$supportedLanguages[] = basename($file);
}

$errors = array();

// If the form from Stage 1 has been submitted
// we need to process it
if (isset($_POST['submit']) && isset($_REQUEST['atmail_lang']))
{
	if (in_array($_REQUEST['atmail_lang'], $supportedLanguages))
		$_SESSION['step1']['lang'] = $lang = $_REQUEST['atmail_lang'];
	else
		$errors['invalid_lang'] = true;

	// Save data if there are no errors
	if (!count($errors))
	{
		// save the setting in session for saving
		// to Config.php at end of installation
		$_SESSION['pref']['Language'] = $lang;

		// Send on to next step
		header('Location: index.php?step=2');
		exit;
	}
}

asort($supportedLanguages);

$vars['supportedLanguages'] = $supportedLanguages;
$vars['output'] .= parse("$htmlPath/step1.phtml", array_merge($errors, $vars));

?>
