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
 
// include language file
if(!file_exists(WB_PATH .'/modules/kit_tools/languages/' .LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/kit_tools/languages/DE.php'); // Vorgabe: DE verwenden 
	if (!defined('KIT_FORM_LANGUAGE')) define('KIT_MARKET_LANGUAGE', 'DE'); // die Konstante gibt an in welcher Sprache KIT MarketPlace aktuell arbeitet
}
else {
	require_once(WB_PATH .'/modules/kit_tools/languages/' .LANGUAGE .'.php');
	if (!defined('KIT_FORM_LANGUAGE')) define('KIT_MARKET_LANGUAGE', LANGUAGE); // die Konstante gibt an in welcher Sprache KIT MarketPlace aktuell arbeitet
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

// Prompt Errors
if (!empty($error)) {
	$admin->print_error($error);
}

?>