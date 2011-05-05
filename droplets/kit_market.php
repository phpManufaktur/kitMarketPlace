//:interface to kitMarket
//:Please visit http://phpManufaktur.de for informations about kitMarketPlace!
/**
 * kitMarketPlace
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 */
if (file_exists(WB_PATH.'/modules/kit_market/class.frontend.php')) {
	require_once(WB_PATH.'/modules/kit_market/class.frontend.php');
	$market = new marketFrontend();
	return $market->action();
}
else {
	return "kitMarketPlace is not installed!";
}