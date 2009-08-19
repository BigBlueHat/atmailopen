<?php
// +----------------------------------------------------------------+
// | step4.php														|
// |																|
// | Function: select atmail client or server mode					|
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

// Webmail client is standalone
$_SESSION['pref']['install_type'] = "standalone";
$_SESSION['pref']['allow_Signup'] = "0";

// Required for PHP5, otherwise template values to evaluate
$errors = array();

// automatically generate serial number for demo copy
if (preg_match('/demo/i', $_SESSION['pref']['version'])) {

    if ( !preg_match('/Win/', PHP_OS) ) {
	exec('/bin/hostname',$fi8iFg);
	$kdkl=trim($fi8iFg[0]);
	}

	if(!$kdkl)
	$kdkl = $_SERVER['HTTP_HOST'];

    $f4m2 = "m1gg1dymack";

    $stuff = array();
    $t = time() + '2592000';
    array_push($stuff, $f4m2 , $kdkl, $t, 'demo');
    $psap=md5(implode('',$stuff));
    $_SESSION['reg']['serial']=$psap;
    $_SESSION['reg']['downloadid'] = 'demo';
    $_SESSION['reg']['expiry'] = $t;
    $_SESSION['reg']['hostname'] = $kdkl;

    gotoStep(5);
}

// If the form from Step 4 has been submitted
// we need to process it
if (isset($_POST['submit']))
{

	// Verify the submitted data

	$atmailMode = $_POST['mode'];

	$_SESSION['reg']['downloadid'] = $_POST['downloadid'];
	$_SESSION['reg']['serial'] = $_POST['serial'];
	$_SESSION['reg']['hostname'] = $_POST['hostname'];
	$_SESSION['reg']['expiry'] = 'never';

	$j8fk="m1gg1dymack";
	$dlgh[]=$j8fk;
	$dlgh[]=$_SESSION['reg']['hostname'];
	$dlgh[]=$_SESSION['reg']['expiry'];
	$dlgh[]=$_SESSION['reg']['downloadid'];
	$f2h2=md5(implode($dlgh,''));

	if($f2h2 != $_SESSION['reg']['serial'])
		$errors['invalid'] = "Invalid serial key provided";

	if (!count($errors))
		gotoStep(5);
}

// Find the hostname via the hostname command , only if safe_mode is disabled
if(!$vars['hostname'] && !ini_get('safe_mode') ) {
exec('/bin/hostname',$vars['hostname']);
$vars['hostname']=trim($vars['hostname'][0]);
}

// If we can't find the hostname, grab it frm the server values
if(!$vars['hostname'])
$vars['hostname'] = $_SERVER['HTTP_HOST'];

// Save for the Config.pm
$_SESSION['pref']['hostname'] = $vars['hostname'];

preg_match('/(\w+\.\w+)/',$_SESSION['pref']['version'],$m);
$vars['version']= strtolower($m[1]);

$vars['regurl'] = "http://calacode.com/reg.ehtml?hostname=" . $vars['hostname'] . "&type=1&version=" . $vars['version'];

// Print the Step 4 page if no data submitted yet
// or there were errors in submitted data
$vars['output'] = parse("$htmlPath/step4.phtml", array_merge($vars, $errors));
?>