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

// prevent this file from being accessed directly
if (!defined('WB_PATH')) die('invalid call of '.$_SERVER['SCRIPT_NAME']);

// include GENERAL language file
if(!file_exists(WB_PATH .'/modules/kit_tools/languages/' .LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/kit_tools/languages/DE.php'); // Vorgabe: DE verwenden 
}
else {
	require_once(WB_PATH .'/modules/kit_tools/languages/' .LANGUAGE .'.php');
}

// include language file for MARKET PLACE
if(!file_exists(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE.php'); // Vorgabe: DE verwenden 
	if (!defined('KIT_MARKET_LANGUAGE')) define('KIT_MARKET_LANGUAGE', 'DE'); // die Konstante gibt an in welcher Sprache KIT MarketPlace aktuell arbeitet
}
else {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php');
	if (!defined('KIT_MARKET_LANGUAGE')) define('KIT_MARKET_LANGUAGE', LANGUAGE); // die Konstante gibt an in welcher Sprache KIT MarketPlace aktuell arbeitet
}

require_once(WB_PATH.'/modules/kit_tools/class.droplets.php');
require_once(WB_PATH.'/modules/kit_market/class.market.php');

global $admin;

$tables = array('dbMarketAdvertisement', 'dbMarketCategories', 'dbMarketCfg');
$error = '';

foreach ($tables as $table) {
	$create = null;
	$create = new $table();
	if (!$create->sqlTableExists()) {
		if (!$create->sqlCreateTable()) {
			$error .= sprintf('[INSTALLATION %s] %s', $table, $create->getError());
		}
	}
}

// Install Droplets
$droplets = new checkDroplets();
$droplets->droplet_path = WB_PATH.'/modules/kit_market/droplets/';

if ($droplets->insertDropletsIntoTable()) {
  $message = sprintf(tool_msg_install_droplets_success, 'kitMarketPlace');
}
else {
  $message = sprintf(tool_msg_install_droplets_failed, 'kitMarketPlace', $droplets->getError());
}
if ($message != "") {
  echo '<script language="javascript">alert ("'.$message.'");</script>';
}


// Prompt Errors
if (!empty($error)) {
	$admin->print_error($error);
}

?>