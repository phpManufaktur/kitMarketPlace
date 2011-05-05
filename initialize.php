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

// for extended error reporting set to true!
if (!defined('KIT_DEBUG')) define('KIT_DEBUG', true);
require_once(WB_PATH.'/modules/kit_tools/debug.php');

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

if (!class_exists('dbconnectle')) 				require_once(WB_PATH.'/modules/dbconnect_le/include.php');
if (!class_exists('Dwoo')) 								require_once(WB_PATH.'/modules/dwoo/include.php');
if (!class_exists('kitContactInterface')) require_once(WB_PATH.'/modules/kit/class.interface.php');	
if (!class_exists('kitToolsLibrary'))   	require_once(WB_PATH.'/modules/kit_tools/class.tools.php');

require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/class.market.php');

global $kitLibrary;
global $parser;
global $dbMarketAd;
global $dbMarketCfg;
global $dbMarketCats;

if (!is_object($kitLibrary)) $kitLibrary = new kitToolsLibrary();
if (!is_object($parser)) $parser = new Dwoo();
if (!is_object($dbMarketAd)) $dbMarketAd = new dbMarketAdvertisement();
if (!is_object($dbMarketCfg)) $dbMarketCfg = new dbMarketCfg();
if (!is_object($dbMarketCats)) $dbMarketCats = new dbMarketCategories();

?>