<?php
/**
 * kitMarketPlace
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {    
    if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php'); 
} else {
    $oneback = "../";
    $root = $oneback;
    $level = 1;
    while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
        $root .= $oneback;
        $level += 1;
    }
    if (file_exists($root.'/framework/class.secure.php')) { 
        include($root.'/framework/class.secure.php'); 
    } else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}
// end include class.secure.php
 
// include GENERAL language file
if(!file_exists(WB_PATH .'/modules/kit_tools/languages/' .LANGUAGE .'.php')) {
    require_once(WB_PATH .'/modules/kit_tools/languages/DE.php'); // Vorgabe: DE verwenden
}
else {
    require_once(WB_PATH .'/modules/kit_tools/languages/' .LANGUAGE .'.php');
}

// include language file
if(!file_exists(WB_PATH .'/modules/kit_tools/languages/' .LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/kit_tools/languages/DE.php'); // Vorgabe: DE verwenden 
	if (!defined('KIT_MARKET_LANGUAGE')) define('KIT_MARKET_LANGUAGE', 'DE'); // die Konstante gibt an in welcher Sprache KIT MarketPlace aktuell arbeitet
}
else {
	require_once(WB_PATH .'/modules/kit_tools/languages/' .LANGUAGE .'.php');
	if (!defined('KIT_MARKET_LANGUAGE')) define('KIT_MARKET_LANGUAGE', LANGUAGE); // die Konstante gibt an in welcher Sprache KIT MarketPlace aktuell arbeitet
}

require_once WB_PATH.'/modules/kit_tools/class.droplets.php';
require_once WB_PATH.'/modules/kit_form/class.form.php';

global $admin;
$error = '';

// import forms from /forms to kitForm
$kitForm = new dbKITform();
$dir_name = WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/forms/';
$folder = opendir($dir_name);
$names = array();
while (false !== ($file = readdir($folder))) {
    $ff = array();
    $ff = explode('.', $file);
    $ext = end($ff);
    if ($ext	==	'kit_form') {
        $names[] = $file;
    }
}
closedir($folder);
$message = '';
foreach ($names as $file_name) {
    $form_file = $dir_name.$file_name;
    $form_id = -1;
    $msg = '';
    if (!$kitForm->importFormFile($form_file, '', $form_id, $msg, true)) {
        if ($kitForm->isError()) $error .= sprintf('[IMPORT FORM %s] %s', $file_name, $kitForm->getError());
    }
    $message .= $msg;
}

// remove Droplets
$dbDroplets = new dbDroplets();
// the array contains the droplets to remove
$droplets = array();
foreach ($droplets as $droplet) {
    $where = array(dbDroplets::field_name => $droplet);
    if (!$dbDroplets->sqlDeleteRecord($where)) {
        $message .= sprintf('[UPGRADE] Error uninstalling Droplet: %s', $dbDroplets->getError());
    }
}

// Install Droplets
$droplets = new checkDroplets();
$droplets->droplet_path = WB_PATH.'/modules/kit_idea/droplets/';

if ($droplets->insertDropletsIntoTable()) {
    $message .= sprintf(tool_msg_install_droplets_success, 'kitMarketPlace');
}
else {
    $message .= sprintf(tool_msg_install_droplets_failed, 'kitMarketPlace', $droplets->getError());
}
if ($message != "") {
    echo '<script language="javascript">alert ("'.$message.'");</script>';
}

// Prompt Errors
if (!empty($error)) {
	$admin->print_error($error);
}

?>