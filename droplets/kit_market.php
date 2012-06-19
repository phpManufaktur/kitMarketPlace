//:kitMarketPlace - your market place for small advertisements
//:[[kit_market?preset=1&lepton_groups=GROUPS&switch=SWITCH]] Parameters: "preset" - select the number of the preset from \modules\kit_market\htt\ to use (default=1), "lepton_groups" - select one or more LEPTON or WB group for authentication (optional), "switch" - enables special settings
/**
 * kitIdea
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2011 - 2012
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */
if (file_exists(WB_PATH.'/modules/kit_market/class.frontend.php')) {
	require_once(WB_PATH.'/modules/kit_market/class.frontend.php');
	$market = new marketFrontend();
	$params = $market->getParams();
	$params[marketFrontend::param_preset] = (isset($preset)) ? (int) $preset : 1;
	$params[marketFrontend::param_lepton_groups] = (isset($lepton_groups)) ? $lepton_groups : '';
	$params[marketFrontend::param_switch] = (isset($switch)) ? strtolower($switch) : '';
	if (!$market->setParams($params)) return $market->getError();
	return $market->action();
}
else {
	return "kitMarketPlace is not installed!";
}