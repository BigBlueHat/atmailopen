<?php
// +----------------------------------------------------------------+
// | step2.php														|
// |																|
// | Function: Collect DB Server info and create @Mail database		|
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

$errors = array();

// If the form from Step 2 has been submitted
// we need to process it
if (isset($_POST['submit']))
{
	// Verify the submitted data
	$sqlType = $_POST['sqltype'];
	
	// We only require these settings for MySQL
	if ($sqlType == 'mysql') {
		$sqlUser = $_POST['sqluser'];
		$sqlPass = $_POST['sqlpass'];
		$sqlHost = $_POST['sqlhost'];
		$dbName  = $_POST['dbname'];
		
		if (empty($sqlUser))
			$errors['sqluser_error'] = true;

		if (empty($sqlHost))
			$sqlHost = 'localhost';
	
		if (empty($dbName))
			$errors['dbname_empty'] =  true;
			
	} else {
		$dbName = dirname(dirname(__FILE__)) . '/sqlite/atmail.db';
		
		if (!createSQLiteDir()) {
			die('failed to create sqlite dir!');
		}
	}
	
	// We support mysql and sqlite at the moment
	// (actually just mysql for AtMail Open for the moment)
	if ($sqlType != 'mysql' && $sqlType != 'sqlite')
		$errors['sqltype_error'] = '<span class="error">Invalid SQL type selected</span>';

	// Attempt connection to SQL server
	set_include_path('../' . PATH_SEPARATOR . get_include_path());
	include_once('../libs/PEAR/DB.php');


	// Save data if there are no errors
	if (!count($errors))
	{
		// Store values in $_SESSION['pref'] for writing to
		// Config.php at end of install
		$_SESSION['pref']['sql_type']  = $sqlType;
		$_SESSION['pref']['sql_user']  = $sqlUser;
		$_SESSION['pref']['sql_host']  = $sqlHost;
		$_SESSION['pref']['sql_pass']  = $sqlPass;
		$_SESSION['pref']['sql_table'] = $dbName;

        // MySQL
        if ($sqlType == 'mysql') 
        {
        	
			if ($_POST['create_db'])
			{
				$db = DB::connect("$sqlType://$sqlUser:$sqlPass@$sqlHost");
	
				if (DB::isError($db))
					$errors['db_connect_error'] = $db->getDebugInfo();
				else
				{
					// lets see if the DB exists
					$dbNames = $db->getListOf('databases');
					if (!in_array($dbName, $dbNames))
					{
						// Create the @Mail Database
						$res = $db->query("CREATE DATABASE $dbName");
	
						if (DB::isError($res))
							$errors['db_create_error'] = $res->getDebugInfo();
						else
						{
							// select the DB
							$res = $db->query("use $dbName");
						}
					}
					else
						$errors['db_exists'] = true;
				}
	
			}
			else
			{
				$db = DB::connect("$sqlType://$sqlUser:$sqlPass@$sqlHost/$dbName");
	
				if (DB::isError($db))
					$errors['db_create_error'] = $db->getDebugInfo();
			}
			
			if (!count($errors) && $_POST['create_tables'])
			{
				$tablesCreated = array();
	
				// Populate the @Mail DB
				$file = file('atmail.mysql');
	
				foreach ($file as $line)
				{
					$line = trim($line);
	
					// ignore comments and empty lines
					if (preg_match('/^[\-#]+/', $line) || empty($line))
						continue;

			                if (preg_match('/^\/\*/', $line) || empty($line))
                        		        continue;
	
					// If we find the end of an sql statement
					// append the line to $sql and execute it
					if (preg_match('/;$/', $line))
					{
						$sql .= "$line\n";
						$res = $db->query($sql);
	
						// Check for an error
						if (DB::isError($res))
						{
							$errors['table_create_error'] = $res->getDebugInfo();
	
							// Remove the last table from the array as this
							// is the one the error occured on it was not created
							$trash = array_pop($tablesCreated);
	
							// Now clean up, removing all the tables created
							// to this point
							foreach ($tablesCreated as $table)
								$db->query("DROP TABLE $table");
	
							break;
						}
					}
					// If we find the beginning of a statement
					// reset $sql to $line
					elseif (preg_match('/CREATE TABLE `?([a-z_]+)`?/i', $line, $m))
					{
						$sql = "$line\n";
						$tablesCreated[] = $m[1];
					}
					elseif (preg_match('/^(CREATE|INSERT)/', $line)) {
						$sql = "$line\n";
					}
	
					// Otherwise it must be more of the same statement
					// so append it to $sql
					else
						$sql .= "$line\n";
				}
			}
        }
        
        elseif ($sqlType == 'sqlite')
        {
        	// Back up any existing DB, just in case
        	if (file_exists($dbName)) {
        		rename($dbName, $dbName.'-'.time().'.bak');
        	}
        	
            $db = DB::connect("sqlite:///$dbName");

	        if (DB::isError($db)) {
	        	$errors['db_connect_error'] = $db->getDebugInfo();
	        } else {
	      		$schema = dirname(__FILE__) . '/atmail.sqlite';
	      		$query = file_get_contents($schema);
	        	$res = sqlite_exec($query, $db->connection);
	        	
	        	if ($res === false) {
	        		$errors['table_create_error'] = sqlite_last_error($db->connection);
	        		unlink('../sqlite/atmail.db');
	        	}
	        }
        }

		if (!count($errors))
			gotoStep(3);
	}
}

// Print the step 2 page if no data submitted yet
// or there were errors in submitted data


// merge any pref values into $vars so if we are
// returning from a latter step the values are auto
// completed
if (isset($_SESSION['pref']['sql_type']))
	$vars = array_merge($vars, $_SESSION['pref']);

// Create some default values
$vars['sql_host'] = isset($sqlHost) ? $sqlHost : 'localhost';

// If running Darwin, mysql does not accept connections on localhost, needs 127.0.0.1
if(PHP_OS == 'Darwin')
$vars['sql_host'] = '127.0.0.1';

if (!isset($vars['sql_table']) && !isset($errors['db_exists'])) {
	$vars['sql_table'] = 'atmail';
}

$vars['check_create_db'] = (isset($_REQUEST['submit']) && !isset($_REQUEST['create_db'])) ? '' : 'checked';
$vars['check_create_tables'] = (isset($_REQUEST['submit']) && !isset($_REQUEST['create_tables'])) ? '' : 'checked';

if(!isset($vars['sql_user']))
$vars['sql_user'] = 'root';

$vars['output'] = parse("$htmlPath/step2.phtml", array_merge($vars, $errors));

if (isset($_SESSION['pref']['sql_type']))
{
	$vars['output'] = str_replace("<option value=\"{$vars['output']}\">",
								  "<option value=\"{$vars['output']}\" selected>",
								  $vars['output']);
}


function createSQLiteDir()
{
	if (!is_dir('../sqlite')) {
		if (!mkdir('../sqlite')) {
			return false;	
		}
	}
	
	if (file_exists('../sqlite/.htaccess')) {
		return true;
	}
	
	$htaccess = <<<EOF
order deny, allow
deny from all
EOF;
	
	if ($fh = fopen('../sqlite/.htaccess', 'w')) {
		if (fwrite($fh, $htaccess) == strlen($htaccess)) {
			$return = true;
		} else {
			$return = false;
		}
	} else {
		$return = false;
	}
	
	fclose($fh);
	return $return;
}

?>
