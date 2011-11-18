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
	$params = $market->getParams();
	$params[marketFrontend::param_preset] = (isset($preset)) ? (int) $preset : 1;
	$params[marketFrontend::param_lepton_groups] = (isset($lepton_groups)) ? $lepton_groups : '';
	if (!$market->setParams($params)) return $market->getError();
	return $market->action();
}
else {
	return "kitMarketPlace is not installed!";
}