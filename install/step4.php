<?php
// +----------------------------------------------------------------+
// | step4.php														|
// |																|
// | Function: 	Do any data migration               				|
// +----------------------------------------------------------------+
// | AtMail Open - Licensed under the Apache 2.0 Open-source License|
// | http://opensource.org/licenses/apache2.0.php                   |
// +----------------------------------------------------------------+
// | Date: December 2007											|
// +----------------------------------------------------------------+

// Make sure we are included from install/index.php, exit if not
if (!defined('ATMAIL_INSTALL_SCRIPT')) {
	// link to installer
	die("You cannot request this file directly, please use <a href=\"index.php\">the installer</a>");
}

$installDir = dirname(dirname(__FILE__));

if (isset($_REQUEST['migrate'])) {
    switch ($_REQUEST['migrate']) {
    	case 'horde' : migrate_horde($_REQUEST['dbname'], $_REQUEST['dbuser'], $_REQUEST['dbpass']);break;
    	case 'rc'    : migrate_rc($_REQUEST['dbname'], $_REQUEST['dbuser'], $_REQUEST['dbpass']); break;
    	case 'sq'    : migrate_sq($_REQUEST['dbname'], $_REQUEST['dbuser'], $_REQUEST['dbpass']); break;
    	default      : break;
    }
    
    echo "<pre>MIGRATION OUTPUT\n\n" . htmlentities(file_get_contents('migrate-output.txt')) . '</pre>';
	exit;
}

$vars['output'] = parse("$htmlPath/step4.phtml", $vars);

/**
 * Start migration functions
 */

/**
 * Migrate data from Horde
 */
function migrate_horde($database, $user, $pass='', $host='')
{
    $database = escapeshellarg($database);
    $user = escapeshellarg($user);
    $pass = escapeshellarg($pass);
    $host = escapeshellarg($hpost);
    
	`php ../modules/migrate-abook-horde.php $database $user:$pass $host > migrate-output.txt`;
}


/**
 * Migrate RoundCube data
 */
function migrate_rc($database, $user, $pass='', $host='')
{
    $database = escapeshellarg($database);
    $user = escapeshellarg($user);
    $pass = escapeshellarg($pass);
    $host = escapeshellarg($hpost);
    
	`php ../modules/migrate-roundcube.php $database $user:$pass $host > migrate-output.txt`;
}


/**
 * Migrate SquirrelMail data
 */
function migrate_sq($database, $user, $pass='', $host='')
{
    $database = escapeshellarg($database);
    $user = escapeshellarg($user);
    $pass = escapeshellarg($pass);
    $host = escapeshellarg($hpost);
    
	`php ../modules/migrate-abook-squirrelmail.php $database $user:$pass $host > migrate-output.txt`;
}

