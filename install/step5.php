<?php
// +----------------------------------------------------------------+
// | step5.php														|
// |																|
// | Function: 	setup .htaccess for webadmin directory				|
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

$installDir = dirname(dirname(__FILE__));

if (isset($_POST['submit']))
{
    $pass = $_POST['password'];
	$username = $_POST['username'];

	if ($fh = @fopen("$installDir/webadmin/.htpasswd", 'w'))
	{
		// EOF error on file for some setups? Can print the page instead of exec code
		require_once("$installDir/libs/aprpwd.inc.php");

		// Need to verify the function loads.
		if(function_exists('crypt_apr_md5'))
		{
		    $pass = crypt_apr_md5($pass);
		    fwrite($fh, "$username:$pass\n");
		    fclose($fh);

		    // Add the .htaccess file if it does not exist.
		    if (!file_exists("$installDir/webadmin/.htaccess"))
		    {
		        $fh = @fopen("$installDir/webadmin/.htaccess", 'w');
		        fwrite($fh, "AuthUserFile $installDir/webadmin/.htpasswd\nAuthName WebAdmin\nAuthType Basic\nrequire valid-user\n");
		        fclose($fh);
		    }
		}


	}
	else
		$_SESSION['webadmin_insecure'] = true;

	gotoStep(6);
}


$vars['installDir'] = $installDir;
$vars['output'] = parse("$htmlPath/step5.phtml", $vars);
?>