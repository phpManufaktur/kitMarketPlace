<?php

/**
 * kitMarketPlace
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2011 - 2012
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
  if (defined('LEPTON_VERSION'))
    include(WB_PATH.'/framework/class.secure.php');
}
else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root.'/framework/class.secure.php')) {
    include($root.'/framework/class.secure.php');
  }
  else {
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


global $admin;

$tables = array('dbMarketAdvertisement', 'dbMarketCategories', 'dbMarketCfg');
$error = '';

foreach ($tables as $table) {
	$delete = null;
	$delete = new $table();
	if ($delete->sqlTableExists()) {
		if (!$delete->sqlDeleteTable()) {
			$error .= sprintf('[UNINSTALL] %s', $delete->getError());
		}
	}
}

// remove Droplets
$dbDroplets = new dbDroplets();
$droplets = array();
foreach ($droplets as $droplet) {
    $where = array(dbDroplets::field_name => $droplet);
    if (!$dbDroplets->sqlDeleteRecord($where)) {
        $error .= sprintf('[UNINSTALL] Error uninstalling Droplet: %s', $dbDroplets->getError());
    }
}

// Prompt Errors
if (!empty($error)) {
	$admin->print_error($error);
}

?>