<?php
// +----------------------------------------------------------------+
// | step0.php														|
// |																|
// | Function: Check all dependencies								|
// +----------------------------------------------------------------+
// | AtMail Open - Licensed under the Apache 2.0 Open-source License|
// | http://opensource.org/licenses/apache2.0.php                   |
// +----------------------------------------------------------------+
// | Date: May 2008													|
// +----------------------------------------------------------------+


// Make sure we are included from install/index.php, exit if not
if (!defined('ATMAIL_INSTALL_SCRIPT'))
{
	// link to installer
	die("You cannot request this file directly, please use <a href=\"index.php\">the installer</a>");
}

if ( !function_exists('sys_get_temp_dir') )
{
    // Based on http://www.phpit.net/
    // article/creating-zip-tar-archives-dynamically-php/2/
    function sys_get_temp_dir()
    {
        // Try to get from environment variable
        if ( !empty($_ENV['TMP']) )
        {
            return realpath( $_ENV['TMP'] );
        }
        else if ( !empty($_ENV['TMPDIR']) )
        {
            return realpath( $_ENV['TMPDIR'] );
        }
        else if ( !empty($_ENV['TEMP']) )
        {
            return realpath( $_ENV['TEMP'] );
        }

        // Detect by creating a temporary file
        else
        {
            // Try to use system's temporary directory
            // as random name shouldn't exist
            $temp_file = tempnam( md5(uniqid(rand(), TRUE)), '' );
            if ( $temp_file )
            {
                $temp_dir = realpath( dirname($temp_file) );
                unlink( $temp_file );
                return $temp_dir;
            }
            else
            {
                return FALSE;
            }
        }
    }
}

$errors = array();

// If the form from Stage 1 has been submitted
// we need to process it
if (isset($_POST['submit']))
{
	header('Location: index.php?step=1');
}

// Check for required php extensions

   $ext = array('req' => array(), 'opt' => array());

    // Check for session support
    if (!function_exists('session_start'))
        $ext['req'][] = 'session';

    // Check for PCRE
    if (!defined('PREG_PATTERN_ORDER'))
        $ext['req'][] = 'pcre';

    // Check for mysql
    if (!defined('MYSQL_NUM'))
        $ext['opt'][] = 'mysql';

    if (!extension_loaded('sqlite')) {
    	$ext['opt'][] = 'sqlite';
    }
    
    // Check for LDAP
    if (!defined('LDAP_DEREF_NEVER'))
        $ext['opt'][] = 'ldap';

	// Check Mbstring
	if(!extension_loaded('mbstring')) {
    	$ext['opt'][] = 'mbstring';
		$_SESSION['pref']['allow_utf7_folders'] = '0';
	} else	{
		$_SESSION['pref']['allow_utf7_folders'] = '1';
	}
    
	if (!extension_loaded('openssl')) {
		$ext['opt'][] = 'openssl';
		$_SESSION['pref']['mail_type_ssl'] = 'deny';
	} else {
		$_SESSION['pref']['mail_type_ssl'] = 'allow';
	}
	
    if(!function_exists('iconv')) {
    	$ext['opt'][] = 'iconv';
		$_SESSION['pref']['iconv'] = '0';
	} else	{
		$_SESSION['pref']['iconv'] = '1';
	}

	if(ini_get('safe_mode'))	{
		$ext['req'][] = 'safe_mode';
	}

	if(!ini_get('register_globals'))
		$ext['ini'][] = 'register_globals';

	if(!ini_get('file_uploads'))
		$ext['ini'][] = 'file_uploads';

	if(!ini_get('magic_quotes_gpc'))
		$ext['ini'][] = 'magic_quotes_gpc';

	// Get the filesize
	$size = ini_get('upload_max_filesize');
	$size = str_replace('M', '', $size);

	if(ini_get('upload_max_filesize') < 16)	{
		$ext['ini'][] = 'upload_max_filesize';
		$vars['upload_max_filesize'] = ini_get('upload_max_filesize');
	}

	// Get the post max size
	$size = ini_get('post_max_size');
	$size = str_replace('M', '', $size);

	if(ini_get('post_max_size') < 16)	{
		$ext['ini'][] = 'post_max_size';
		$vars['post_max_size'] = ini_get('post_max_size');
	}
	
	// check for demo version and ionCube loader
	if(preg_match('/demo/i', $_SESSION['pref']['version']) && extension_loaded('ionCube Loader'))
	{
		$vars['ionext'] = '1';
	}
	elseif (preg_match('/demo/i', $_SESSION['pref']['version']) && !extension_loaded('ionCube Loader'))
	{

		if (strlen(ini_get('enable_dl')) == 0) {
		    $ext['req'][] = 'ioncube';
        } else {
        	$ext['opt'][] = 'ioncube';
        }

	    $vars['version'] = 'demo';

	    ob_start();
	    phpinfo(INFO_GENERAL);
	    $php_info = ob_get_contents();
	    ob_end_clean();

	    foreach (split("\n",$php_info) as $line) {
	        if (eregi('command',$line)) {
	            continue;
	        }


	        if (eregi("configuration file.*(</B></td><TD ALIGN=\"left\">| => |v\">)([^ <]*)(.*</td.*)?",$line,$match)) {
	            $php_ini_path = $match[2];

	            if (!@file_exists($php_ini_path)) {
	                $php_ini_path = '';
	            }
	        }


	        if (strlen(ini_get('enable_dl')) == 0) {
	            $ext['ini'][] = 'enable_dl';
	        }
	    }

	    $oc = strtolower(substr(php_uname(),0,3));
		if(preg_match('/x86_64/', php_uname()) && $oc == 'lin')
		$oc = 'lin64';

        $vars['ioncube_lib'] = realpath('../ioncube/'.$oc.'/ioncube_loader_'.$oc.'_'.substr(phpversion(),0,3).(($oc=='win')?'.dll':'.so'));
	    $vars['php_ini_path'] = $php_ini_path;

    }



    if (count($ext['req']) || in_array('mysql', $ext['opt']) && in_array('sqlite', $ext['opt']))
    {
		// Don't let the user proceed
		if ((!$var['version'] == 'demo' && !in_array('ioncube', $ext['req'])) || ($vars['version'] == 'demo')) {
		    $vars['error'] = '1';
		}

        //$var['output'] = parse('html/extensions_required.html', $ext);
        //echo parse('html/english/template.html', $var);
        //exit;
    }

$vars['output'] = parse("$htmlPath/step0.phtml", array_merge($errors, $vars, $ext));

// Preselect the language if already known e.g. we have
// returned from a later step
if ($lang)
	$vars['output'] = str_replace("<option value=\"$lang\">", "<option value=\"$lang\" selected>", $vars['output']);


?>
