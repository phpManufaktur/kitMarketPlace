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

require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/initialize.php');
require_once(WB_PATH.'/include/captcha/captcha.php');
require_once(WB_PATH.'/modules/kit_form/class.frontend.php');

class marketFrontend {
	
	const request_main_action					= 'rma';
	const request_account_action			= 'raa';
	const request_market_action				= 'mar';
	
	const action_market								= 'mar';
	const action_account							= 'acc';
	const action_category							= 'cat';
	const action_default							= 'def';
	const action_check_form						= 'acf';
	const action_login								= 'alo';
	const action_overview							= 'ove';
	const action_logout								= 'out';
	const action_advertisement				= 'ad';
	const action_advertisement_add		= 'aaa';
	const action_advertisement_check	= 'aaac';
	
	const session_temp_vars						= 'mtv';
	
	private $page_link 								= '';
	private $img_url									= '';
	private $template_path						= '';
	private $error										= '';
	private $message									= '';
	private $media_path								= '';
	private $media_url								= '';
	
	const param_preset								= 'preset';
	
	private $params = array(
		self::param_preset			=> 1, 
	);
	
	private $tab_main_navigation_array = array(
		self::action_market			=> market_tab_market,
		self::action_account		=> market_tab_account
	);
	
	
	private $tab_account_navigation_array = array(
		self::action_overview						=> market_tab_overview,
		self::action_advertisement_add	=> market_tab_new_advertisement,
		self::action_account						=> market_tab_account,
		self::action_logout							=> market_tab_logout
	);
	
	public function __construct() {
		global $kitLibrary;
		global $dbMarketCfg;
		$url = '';
		$_SESSION['FRONTEND'] = true;	
		$kitLibrary->getPageLinkByPageID(PAGE_ID, $url);
		$this->page_link = $url; 
		$this->template_path = WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/htt/'.$this->params[self::param_preset].'/'.KIT_MARKET_LANGUAGE.'/' ;
		$this->img_url = WB_URL. '/modules/'.basename(dirname(__FILE__)).'/images/';
		date_default_timezone_set(tool_cfg_time_zone);
		$this->media_path = WB_PATH.MEDIA_DIRECTORY.'/'.$dbMarketCfg->getValue(dbMarketCfg::cfgAdImageDir).'/';
		$this->media_url = str_replace(WB_PATH, WB_URL, $this->media_path);
	} // __construct()
	
	public function getParams() {
		return $this->params;
	} // getParams()
	
	public function setParams($params = array()) {
		$this->params = $params;
		$this->template_path = WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/htt/'.$this->params[self::param_preset].'/'.KIT_MARKET_LANGUAGE.'/';
		if (!file_exists($this->template_path)) {
			$this->setError(sprintf(form_error_preset_not_exists, '/modules/'.basename(dirname(__FILE__)).'/htt/'.$this->params[self::param_preset].'/'.KIT_MARKET_LANGUAGE.'/'));
			return false;
		}
		return true;
	} // setParams()
	
	/**
    * Set $this->error to $error
    * 
    * @param STR $error
    */
  public function setError($error) {
  	$this->error = $error;
  } // setError()

  /**
    * Get Error from $this->error;
    * 
    * @return STR $this->error
    */
  public function getError() {
    return $this->error;
  } // getError()

  /**
    * Check if $this->error is empty
    * 
    * @return BOOL
    */
  public function isError() {
    return (bool) !empty($this->error);
  } // isError

  /**
   * Reset Error to empty String
   */
  public function clearError() {
  	$this->error = '';
  }

  /** Set $this->message to $message
    * 
    * @param STR $message
    */
  public function setMessage($message) {
    $this->message = $message;
  } // setMessage()

  /**
    * Get Message from $this->message;
    * 
    * @return STR $this->message
    */
  public function getMessage() {
    return $this->message;
  } // getMessage()

  /**
    * Check if $this->message is empty
    * 
    * @return BOOL
    */
  public function isMessage() {
    return (bool) !empty($this->message);
  } // isMessage
  
  /**
   * Gibt das gewuenschte Template zurueck
   * 
   * @param STR $template
   * @param ARRAY $template_data
   */
  public function getTemplate($template, $template_data) {
  	global $parser;
  	try {
  		$result = $parser->get($this->template_path.$template, $template_data); 
  	} catch (Exception $e) {
  		$this->setError(sprintf(form_error_template_error, $template, $e->getMessage()));
  		return false;
  	}
  	return $result;
  } // getTemplate()
  
  private function setTempVars($vars=array()) {
		$_SESSION[self::session_temp_vars] = http_build_query($vars);
	} // setTempVars()
	
	private function getTempVars() {
		if (isset($_SESSION[self::session_temp_vars])) {
			parse_str($_SESSION[self::session_temp_vars], $vars);
			foreach ($vars as $key => $value) {
				if (!isset($_REQUEST[$key])) $_REQUEST[$key] = $value;
			}
			unset($_SESSION[self::session_temp_vars]);
		}
	} // getTempVars()
	
	private function createFileName($filename, $extension, $width, $height) {
		$filename = page_filename($filename);
		return sprintf('%s_%d_%d.%s', $filename, $width, $height, $extension);
	} // 
	
	private function createTweakedFile($filename, $extension, $file_path, $new_width, $new_height, $origin_width, $origin_height) {
		switch ($extension):
	  	case 'gif':
	  		$origin_image = imagecreatefromgif($file_path);
	      break;
	    case 'jpeg':
	    case 'jpg':
      	$origin_image = imagecreatefromjpeg($file_path);
	      break;
	    case 'png':
	      $origin_image = imagecreatefrompng($file_path);
	      break;
	    default: 
	      // unsupported image type
	      return false;
	  	endswitch;
	  	
	  // create new image of $new_width and $new_height
    $new_image = imagecreatetruecolor($new_width, $new_height);
    // Check if this image is PNG or GIF, then set if Transparent  
    if (($extension == 'gif') OR ($extension == 'png')) {
      imagealphablending($new_image, false);
      imagesavealpha($new_image,true);
      $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
      imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
    }
    imagecopyresampled($new_image, $origin_image, 0, 0, 0, 0, $new_width, $new_height, $origin_width, $origin_height);
    
    
    $new_file = $this->createFileName($filename, $extension, $new_width, $new_height);
    $new_file = dirname($file_path).'/'.$new_file;
    //Generate the file, and rename it to $newfilename
    switch ($extension): 
      case 'gif': 
      	imagegif($new_image, $new_file); 
       	break;
      case 'jpg':
      case 'jpeg': 
       	imagejpeg($new_image, $new_file); 
       	break;
      case 'png': 
       	imagepng($new_image, $new_file); 
       	break;
      default:  
       	// unsupported image type
       	return false;
    endswitch;
    if (!chmod($new_file, 0755)) {
    	$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_chmod, basename($new_file))));
    }
    return $new_file;	  
	} // createTweakedFile()
	
	
  /**
   * Verhindert XSS Cross Site Scripting
   * 
   * @param REFERENCE ARRAY $request
   * @return ARRAY $request
   */
	public function xssPrevent(&$request) { 
  	if (is_string($request)) {
	    $request = html_entity_decode($request);
	    $request = strip_tags($request);
	    $request = trim($request);
	    $request = stripslashes($request);
  	}
	  return $request;
  } // xssPrevent()
	
  /**
   * GENERELLER ACTION HANDLER FUER DEN MARKTPLATZ
   * @return STR result
   */
  public function action() { 
  	// temporaere Variablen in $_REQUESTs umschreiben...
  	$this->getTempVars();
  	
  	$html_allowed = array();
  	foreach ($_REQUEST as $key => $value) {
  		if (!in_array($key, $html_allowed)) {
  			$_REQUEST[$key] = $this->xssPrevent($value);	  			
  		} 
  	}
    $action = isset($_REQUEST[self::request_main_action]) ? $_REQUEST[self::request_main_action] : self::action_default;
    
  	switch ($action):
  	case self::action_account:
  		// Zum persoenlichen KONTO wechseln
  		return $this->show_main(self::action_account, $this->accountAction());
  	case self::action_default:
  	default:
  		// Anzeige des OEFFENTLICHEN MARKTPLATZ
  		return $this->show_main(self::action_market, $this->marketAction());
  	endswitch;
  } // action
  
  
  /**
   * Ausgabe des formatierten Ergebnis mit Navigationsleiste (Kleinanzeigen/Konto)
   * 
   * @param STR $action - aktives Navigationselement
   * @param STR $content - Inhalt
   * 
   * @return STR dialog
   */
  public function show_main($action, $content) {
  	$navigation = array();
  	foreach ($this->tab_main_navigation_array as $key => $value) {
  		$navigation[] = array(
  			'active' 	=> ($key == $action) ? 1 : 0,
  			'url'			=> sprintf('%s%s%s=%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', self::request_main_action, $key),
  			'text'		=> $value
  		);
  	}
  	$data = array(
  		'WB_URL'			=> WB_URL,
  		'navigation'	=> $navigation,
  		'error'				=> ($this->isError()) ? 1 : 0,
  		'content'			=> ($this->isError()) ? $this->getError() : $content
  	);
  	return $this->getTemplate('body.htt', $data);
  } // show()
	
  
  /**
   * Action Handler fuer das KONTO DES BESUCHERS
   */
  public function accountAction() {
  	global $kitContactInterface;
  	
  	$action = isset($_REQUEST[self::request_account_action]) ? $_REQUEST[self::request_account_action] : self::action_default;
  	
  	if (!$this->accountIsAuthenticated()) {
  		$action = self::action_login;
		}
		
  	switch ($action):
  	case self::action_logout:
  		$kitContactInterface->logout();
  		// wichtig: kein break, direkt den login Dialog anzeigen!
  	case self::action_login:
  		$this->setTempVars(array(self::request_main_action => self::action_account));
			$result = $this->accountLoginDlg();
			if (is_string($result)) return $result; // Login ist noch nicht erfolgreich
			if (is_bool($result) && ($result == false)) return false; // Fehler...
			// nach erfolgreichem Login die Uebersicht anzeigen
			return $this->accountShow(self::action_overview, $this->accountOverview());
  	case self::action_account:
  		return $this->accountShow(self::action_account, $this->accountAccountDlg());
  	case self::action_advertisement_add:
  		return $this->accountShow(self::action_advertisement_add, $this->accountAdvertisementAdd());
  	case self::action_advertisement_check:
  		return $this->accountShow(self::action_advertisement_add, $this->accountAdvertisementCheck());
  	case self::action_default:
  	default:
  		return $this->accountShow(self::action_overview, $this->accountOverview());
  	endswitch;
  } // accountAction()
  
	/**
   * Ausgabe des formatierten Ergebnis mit Navigationsleiste (Konto)
   * 
   * @param action - aktives Navigationselement
   * @param content - Inhalt
   * 
   * @return ECHO RESULT
   */
  public function accountShow($action, $content) {
  	$navigation = array();
  	foreach ($this->tab_account_navigation_array as $key => $value) {
  		$navigation[] = array(
  			'active' 	=> ($key == $action) ? 1 : 0,
  			'url'			=> sprintf('%s%s%s=%s&%s=%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', self::request_main_action, self::action_account, self::request_account_action, $key),
  			'text'		=> $value
  		);
  	}
  	$data = array(
  		'WB_URL'			=> WB_URL,
  		'navigation'	=> $navigation,
  		'error'				=> ($this->isError()) ? 1 : 0,
  		'content'			=> ($this->isError()) ? $this->getError() : $content
  	);
  	return $this->getTemplate('body.account.htt', $data);
  } // accountShow()
	
  
	
	private function accountIsAuthenticated() {
		global $kitContactInterface;
		global $dbMarketCfg;
		
		if ($kitContactInterface->isAuthenticated()) {
			// Pruefung der Kategorien - MUSS INS BACKEND!
			$cat = $dbMarketCfg->getValue(dbMarketCfg::cfgKITcategory);
			if (!$kitContactInterface->existsCategory(kitContactInterface::category_type_intern, $cat)) {
				if (!$kitContactInterface->addCategory(kitContactInterface::category_type_intern, $cat, $cat)) {
					$this->setError(sprintf('[%s - %] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
					return false;
				}
			}
			if (!$kitContactInterface->getCategories($_SESSION[kitContactInterface::session_kit_contact_id], $categories)) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
				return false;
			}
			if (in_array($cat, $categories)) {
				return true;
			}
			else {
				$this->setError(market_error_auth_wrong_category);
				return false;
			}
		}
		return false;
	} // accountIsAuthenticated()
	
	/**
	 * Login Dialog fuer das Kundenkonto
	 * @return STR dialog BOOL on success or error
	 */
	public function accountLoginDlg() {
		global $kitContactInterface;
		global $dbMarketCfg;
		
		$dlg = $dbMarketCfg->getValue(dbMarketCfg::cfgFormDlgLogin);
		
		$form = new formFrontend();
		$params = $form->getParams();
		$params[formFrontend::param_form] = $dlg;
		$params[formFrontend::param_return] = true;
		$form->setParams($params);
		
		$result = $form->action();
		if (is_string($result)) {
			return $result;
		}
		elseif (is_bool($result) && ($result == false) && $form->isError()) {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $form->getError())); 
			return false;
		}
		elseif (is_bool($result) && ($result == true)) {
			return true;
		}
		else {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, market_error_undefined));
			return false;
		}
	} // accountLoginDlg()
	
	
	public function accountAccountDlg() {
		global $kitContactInterface;
		global $dbMarketCfg;
		$this->setTempVars(array(self::request_main_action => self::action_account, self::request_account_action => self::action_account));
			
		$dlg = $dbMarketCfg->getValue(dbMarketCfg::cfgFormDlgAccount);
		$form = new formFrontend();
		$params = $form->getParams();
		$params[formFrontend::param_form] = $dlg;
		$params[formFrontend::param_return] = true;
		$form->setParams($params);
		return $form->action();
	} // accountAccountDlg()
	
	public function accountAdvertisementAdd() {
		global $dbMarketAd;
		global $dbMarketCats;
		global $dbMarketCfg;
		global $kitLibrary;
		
		$ad_id = (isset($_REQUEST[dbMarketAdvertisement::field_id])) ? $_REQUEST[dbMarketAdvertisement::field_id] : -1;
		
		
		if ($ad_id > 0) {
			// Kleinanzeige auslesen
			$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s' AND %s!='%s'",
											$dbMarketAd->getTableName(),
											dbMarketAdvertisement::field_id,
											$ad_id,
											dbMarketAdvertisement::field_status,
											dbMarketAdvertisement::status_deleted);
			$ad = array();
			if (!$dbMarketAd->sqlExec($SQL, $ad)) {
				$this->setError($dbMarketAd->getError());
				return false;
			}
			if (count($ad) < 1) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $ad_id)));
				return false;
			}
			$ad = $ad[0];
		}
		else {
			$ad = $dbMarketAd->getFields();
			$ad[dbMarketAdvertisement::field_id] = -1;
			$ad[dbMarketAdvertisement::field_category] = -1;
			$ad[dbMarketAdvertisement::field_status] = dbMarketAdvertisement::status_locked;
			$ad[dbMarketAdvertisement::field_kit_id] = $_SESSION[kitContactInterface::session_kit_contact_id];
			$ad[dbMarketAdvertisement::field_ad_type] = dbMarketAdvertisement::type_offer;
			$ad[dbMarketAdvertisement::field_commercial] = dbMarketAdvertisement::commercial_no;
			$ad[dbMarketAdvertisement::field_price_type] = dbMarketAdvertisement::price_asking;
		}
		
		foreach ($ad as $key => $value) {
			switch ($key):
			default:
				if (isset($_REQUEST[$key])) $ad[$key] = $_REQUEST[$key];
			endswitch;
		}
		
		// Kategorien auslesen
		$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s' ORDER BY %s, %s, %s, %s, %s ASC",
										$dbMarketCats->getTableName(),
										dbMarketCategories::field_status,
										dbMarketCategories::status_active,
										dbMarketCategories::field_level_01,
										dbMarketCategories::field_level_02,
										dbMarketCategories::field_level_03,
										dbMarketCategories::field_level_04,
										dbMarketCategories::field_level_05);
		$categories = array();
		if (!$dbMarketCats->sqlExec($SQL, $categories)) {
			$this->setError($dbMarketCats->getError()); return false;
		}
		
		// Liste der Kategorien erstellen
		$category_list = array();
		$category_list[] = array(
			'text'				=> market_text_select,
			'value'				=> -1,
			'selected'		=> ($ad[dbMarketAdvertisement::field_category] == -1) ? 1 : 0
		);
		$category_check = array();
		foreach ($categories as $category) {
			$path = array();
			for ($i=1;$i<6;$i++) {
				if (!empty($category[sprintf('cat_level_%02d', $i)])) $path[] = $category[sprintf('cat_level_%02d', $i)];
			}
			$path_str = implode(' > ', $path);
			if (!in_array($path_str, $category_check)) {
				$category_check[] = $path_str;
				$category_list[] = array(
					'text' 			=> $path_str,
					'value'			=> $category[dbMarketCategories::field_id],
					'selected'	=> ($category[dbMarketCategories::field_id] == $ad[dbMarketAdvertisement::field_category]) ? 1 : 0
				);
			}
		}
		
		// Kleinanzeige TYP
		$ad_types = array();
		foreach ($dbMarketAd->type_array as $value => $text) {
			$ad_types[] = array(
				'text'			=> $text,
				'value'			=> $value,
				'selected'	=> ($value == $ad[dbMarketAdvertisement::field_ad_type]) ? 1 : 0
			);
		}
		
		// Gewerblich / Privat
		$commercial_array = array();
		foreach ($dbMarketAd->commercial_array as $value => $text) {
			$commercial_array[] = array(
				'text'			=> $text,
				'value'			=> $value,
				'selected'	=> ($value == $ad[dbMarketAdvertisement::field_commercial]) ? 1 : 0
			);
		}
		
		// Preis Typ
		$price_types = array();
		foreach ($dbMarketAd->price_array as $value => $text) {
			$price_types[] = array(
				'text'			=> $text,
				'value'			=> $value,
				'selected'	=> ($value == $ad[dbMarketAdvertisement::field_price_type]) ? 1 : 0
			);
		}
	
		$form = array(
			'name'			=> 'advertisement_add',
			'action'		=> array(	'name'		=> self::request_main_action,
														'value'		=> self::action_account,
														'link'		=> $this->page_link	),
			'account'		=> array(	'name'		=> self::request_account_action,
														'value'		=> self::action_advertisement_check),
			'id'				=> array( 'name'		=> dbMarketAdvertisement::field_id,
														'value'		=> $ad_id ),
			'title'			=> market_head_advertisement_add,
			'response'	=> array(	'text'				=> ($this->isMessage()) ? $this->getMessage() : market_intro_advertisement_add,
														'is_message'	=> ($this->isMessage()) ? 1 : 0),
			'btn'				=> array(	'ok'					=> tool_btn_ok,
														'abort'				=> tool_btn_abort ),
			'header'		=> array(	'id'					=> market_th_id,
														'kit_id'			=> market_th_kit_id,
														'category'		=> market_th_category,
														'type'				=> market_th_type,
														'commercial'	=> market_th_commercial,
														'title'				=> market_th_title,
														'price'				=> market_th_price,
														'price_type'	=> market_th_price_type,
														'pictures'		=> market_th_pictures,
														'text'				=> market_th_text,
														'status'			=> market_th_status,
														'start_date'	=> market_th_start_date,
														'end_date'		=> market_th_end_date,
														'timestamp'		=> market_th_timestamp)
		);
		
		$post_max_size = $kitLibrary->convertBytes(ini_get('post_max_size'));
		$upload_max_filesize = $kitLibrary->convertBytes(ini_get('upload_max_filesize'));
		$max_size = ($post_max_size >= $upload_max_filesize) ? $upload_max_filesize : $post_max_size;
		$max_size = $kitLibrary->bytes2Str($max_size);
		$max_images = $dbMarketCfg->getValue(dbMarketCfg::cfgAdMaxImages);
		$max_image_width = $dbMarketCfg->getValue(dbMarketCfg::cfgAdMaxImageWidth);
		$max_image_height = $dbMarketCfg->getValue(dbMarketCfg::cfgAdMaxImageHeight);
		$prev_width = $dbMarketCfg->getValue(dbMarketCfg::cfgAdImagePrevWidth);
		$file_types = implode(', ', $dbMarketCfg->getValue(dbMarketCfg::cfgAdImageTypes));
		$images = array();
		$pictures = (!empty($ad[dbMarketAdvertisement::field_pictures])) ? explode(',', $ad[dbMarketAdvertisement::field_pictures]) : array();
		
		$upl_path = $this->media_path.$ad[dbMarketAdvertisement::field_kit_id].'/'.$ad[dbMarketAdvertisement::field_id].'/';
		$upl_url = str_replace(WB_PATH, WB_URL, $upl_path);
		$prev_path = $upl_path.$prev_width.'/';
		$prev_url = str_replace(WB_PATH, WB_URL, $prev_path);
		
		$tmp_dir = $this->media_path.$ad[dbMarketAdvertisement::field_kit_id].'/tmp/';
					
		foreach ($pictures as $picture) {
			if (!file_exists($upl_path.$picture)) {
				if (file_exists($tmp_dir.$picture)) {
					// Datei befindet sich noch im TEMP Verzeichnis, verschieben...
					if (!file_exists($upl_path)) {
						// Verzeichnis erstellen
						if (!mkdir($upl_path, 0755, true)) {
							$this->setError(sprintf('[%s - %s] %s', sprintf(tool_error_mkdir, $upl_path)));
							return false;
						}
					}
					if (!rename($tmp_dir.$picture, $upl_path.$picture)) {
						$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_move_file, $tmp_dir.$picture, $upl_path.$picture)));
						return false;
					}
				}
				else {
					// Datei nicht gefunden
					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_missing_file, $upl_path.$picture)));
					return false;
				}
			}
			// Dateiinformationen
			$path_parts = pathinfo($upl_path.$picture);
			// Breite und Hoehe festhalten
			list($full_width, $full_height) = getimagesize($upl_path.$picture);
			// Vorschaubild pruefen
			if (!file_exists($prev_path.$picture)) {
				$factor = $prev_width/$full_width;
  			$new_height = ceil($full_height*$factor);
  			$new_width = ceil($full_width*$factor);
  			if (false === ($new_file = $this->createTweakedFile($path_parts['filename'], $path_parts['extension'], $upl_path.$picture, 
  	  																												$new_width, $new_height, $full_width, $full_height))) {
  	  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_tweaking_file, $upl_path.$picture)));
  	  			return false;																										
  	  	}
  	  	if (!file_exists($prev_path)) {
  	  		if (!mkdir($prev_path, 0755, true)) {
  	  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_mkdir, $prev_path)));
  	  			return false;
  	  		}
  	  	}	
  			if (!rename($new_file, $prev_path.$picture)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_move_file, $new_file, $prev_path.$picture)));
  				return false;
  			}	
			}
			list($prev_width, $prev_height) = getimagesize($prev_path.$picture);
			$images[] = array(
				'fullsize'		=> array(	'url'			=> $upl_url.$picture,
																'width'		=> $full_width,
																'height'	=> $full_height	),
				'preview'			=> array( 'url'			=> $prev_url.$picture,
																'width'		=> $prev_width,
																'height'	=> $prev_height )
			);
		}
		$advertisement = array(
			'category'		=> array(	'label'				=> market_label_adv_category,
															'name'				=> dbMarketAdvertisement::field_category,
															'values'			=> $category_list,
															'hint'				=> market_hint_adv_category),
			'type'				=> array( 'label'				=> market_label_adv_type,
															'name'				=> dbMarketAdvertisement::field_ad_type,
															'values'			=> $ad_types,
															'hint'				=> market_hint_adv_type),
			'commercial'	=> array(	'label'				=> market_label_adv_commercial,
															'name'				=> dbMarketAdvertisement::field_commercial,
															'values'			=> $commercial_array,
															'hint'				=> market_hint_adv_commercial),
			'title'				=> array( 'label'				=> market_label_adv_title,
															'name'				=> dbMarketAdvertisement::field_title,
															'value'				=> $ad[dbMarketAdvertisement::field_title],
															'hint'				=> market_hint_adv_title),
			'text'				=> array(	'label'				=> market_label_adv_text,
															'name'				=> dbMarketAdvertisement::field_text,
															'value'				=> $ad[dbMarketAdvertisement::field_text],
															'hint'				=> market_hint_adv_text),
			'price'				=> array(	'label'				=> market_label_adv_price,
															'name'				=> dbMarketAdvertisement::field_price,
															'value'				=> $ad[dbMarketAdvertisement::field_price],
															'hint'				=> market_hint_adv_price),
			'price_type'	=> array(	'label'				=> market_label_adv_price_type,
															'name'				=> dbMarketAdvertisement::field_price_type,
															'values'			=> $price_types,
															'hint'				=> market_hint_adv_price_type),
			'image'				=> array(	'label'				=> market_label_adv_image_upload,
															'name'				=> dbMarketAdvertisement::field_pictures,
															'values'			=> $images,
															'hint'				=> sprintf(market_hint_adv_image_size, $max_images, $max_size),
															'max_img' 		=> $max_images,
															'max_size'		=> $max_size,
															'max_width'		=> $max_image_width,
															'max_height'	=> $max_image_height,
															'file_types'	=> $file_types),
			'status'			=> array(	'name'				=> dbMarketAdvertisement::field_status,
															'value'				=> array(	'closed'	=> array( 'value'			=> dbMarketAdvertisement::status_closed,
																																					'selected'	=> ($ad[dbMarketAdvertisement::field_status] == dbMarketAdvertisement::status_closed) ? 1 : 0)))		
		); 
		$data = array(
			'form'					=> $form,
			'advertisement'	=> $advertisement
		);
		return  $this->getTemplate('account.advertisement.add.htt', $data);
	} // accountAdvertisementAdd()
	
	public function accountAdvertisementCheck() {
		global $dbMarketAd;
		global $kitLibrary;
		global $dbMarketCfg;
		
		// pruefen ob die ID gesetzt ist
		if (!isset($_REQUEST[dbMarketAdvertisement::field_id])) {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_request_missing, dbMarketAdvertisement::field_id)));
			return false;
		}
		$ad_id = $_REQUEST[dbMarketAdvertisement::field_id];
		
		// ID auslesen oder Default Werte setzen
		if ($ad_id > 0) {
			$where = array(
				dbMarketAdvertisement::field_id => $ad_id
			);
			$old_advertisement = array();
			if (!$dbMarketAd->sqlSelectRecord($where, $old_advertisement)) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbMarketAd->getError()));
				return false;
			}
			if (count($old_advertisement) < 1) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $ad_id)));
				return false;
			}
			$old_advertisement = $old_advertisement[0];
		}
		else {
			// Default Werte fuer einen neuen Eintrag
			$old_advertisement = $dbMarketAd->getFields();
			$old_advertisement[dbMarketAdvertisement::field_kit_id] = $_SESSION[kitContactInterface::session_kit_contact_id];
			$old_advertisement[dbMarketAdvertisement::field_status] = dbMarketAdvertisement::status_locked;
			$old_advertisement[dbMarketAdvertisement::field_price_type] = dbMarketAdvertisement::price_asking;
		}
		
		$changed = false;
		$checked = true;
		$message = '';
		$new_advertisement = $old_advertisement;
		$publish_directly = $dbMarketCfg->getValue(dbMarketCfg::cfgAdPublishDirect);
		
		// Felder durchlaufen und pruefen
		foreach ($old_advertisement as $key => $value) {
			switch ($key):
			case dbMarketAdvertisement::field_id:
			case dbMarketAdvertisement::field_start_date:
			case dbMarketAdvertisement::field_end_date:
			case dbMarketAdvertisement::field_timestamp:
			case dbMarketAdvertisement::field_pictures:
			case dbMarketAdvertisement::field_kit_id:
				// nichts tun, ueberspringen
				continue;
			case dbMarketAdvertisement::field_price:
				// Preisangabe pruefen
				if (!isset($_REQUEST[$key])) {
					// Feld muss gesetzt sein
					$message .= sprintf(market_msg_field_empty, $dbMarketAd->field_name_array[$key]);
					$checked = false;
				}
				else {
					// Preis TYP muss bereits gesetzt sein...
					$new_advertisement[dbMarketAdvertisement::field_price_type] = $_REQUEST[dbMarketAdvertisement::field_price_type];
					$price = $kitLibrary->str2float($_REQUEST[$key], tool_cfg_thousand_separator, tool_cfg_decimal_separator);
					if ($price < 0) $price = 0;
					if ($new_advertisement[dbMarketAdvertisement::field_price_type] == dbMarketAdvertisement::price_give_away) {
						$price = 0;
					}
					if (($price == 0) && ($new_advertisement[dbMarketAdvertisement::field_price_type] != dbMarketAdvertisement::price_give_away)) {
						$message .= market_msg_price_needed;
						$checked = false;
						break;
					}
					if ($price != $old_advertisement[$key]) {
						// Preis hat sich geaendert
						$changed = true;
						$new_advertisement[$key] = $price;
						break;
					}
					$new_advertisement[$key] = $price;
				}
				break;
			case dbMarketAdvertisement::field_category:
				if (!isset($_REQUEST[$key])) {
					// Feld muss gesetzt sein
					$message .= sprintf(market_msg_field_empty, $dbMarketAd->field_name_array[$key]);
					$checked = false;
				}
				elseif ($_REQUEST[$key] < 1) {
					$message .= market_msg_category_needed;
					$checked = false;
				}
				elseif ($_REQUEST[$key] != $old_advertisement[$key]) {
					$changed = true;
					$new_advertisement[$key] = $_REQUEST[$key];
				}
				else {
					$new_advertisement[$key] = $_REQUEST[$key];
				}
				break;
			case dbMarketAdvertisement::field_status:
				if (isset($_REQUEST[$key])) {
					$new_advertisement[$key] = dbMarketAdvertisement::status_closed;
					$changed = true;
				}
				break;
			default:
				// allgemeine Felder
				if (!isset($_REQUEST[$key]) || empty($_REQUEST[$key])) {
					// Feld muss gesetzt sein und darf nicht leer sein!
					$checked = false;
					$message .= sprintf(market_msg_field_empty, $dbMarketAd->field_name_array[$key]);
				}
				elseif ($_REQUEST[$key] != $old_advertisement[$key]) {
					$changed = true;
					$new_advertisement[$key] = $_REQUEST[$key];
				}
				else {
					$new_advertisement[$key] = $_REQUEST[$key];
				}
				break;
			endswitch;
		}
		
		$pictures = (!empty($new_advertisement[dbMarketAdvertisement::field_pictures])) ? explode(',', $new_advertisement[dbMarketAdvertisement::field_pictures]) : array();
		// Pruefen, ob eine Datei uebertragen wurde...
		if (isset($_FILES[dbMarketAdvertisement::field_pictures]) && (is_uploaded_file($_FILES[dbMarketAdvertisement::field_pictures]['tmp_name']))) {
			if ($_FILES[dbMarketAdvertisement::field_pictures]['error'] == UPLOAD_ERR_OK) {
				$file_original = $_FILES[dbMarketAdvertisement::field_pictures]['name'];
				$tmp = explode('.', $file_original);
				$ext = strtolower(end($tmp));
				$allowed_filetypes = $dbMarketCfg->getValue(dbMarketCfg::cfgAdImageTypes);
				if (!in_array($ext, $allowed_filetypes)) {
					$exts = implode(', ', $allowed_filetypes);
					$message .= sprintf(market_msg_file_ext_not_allowed, $_FILES[dbMarketAdvertisement::field_pictures]['name'], $exts);
					@unlink($_FILES[dbMarketAdvertisement::field_pictures]['tmp_name']);
					$checked = false;
				}
				else {
					$tmp_file = $_FILES[dbMarketAdvertisement::field_pictures]['tmp_name'];
					$ad_dir = ($ad_id > 0) ? $ad_id : 'tmp';
					$upload_dir = $this->media_path.$_SESSION[kitContactInterface::session_kit_contact_id].'/'.$ad_dir.'/';
					if (!file_exists($upload_dir)) {
						if (!mkdir($upload_dir, 0755, true)) {
							$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_mkdir, $upload_dir)));
							return false;
						}
					}
					
					$ufile = page_filename($_FILES[dbMarketAdvertisement::field_pictures]['name']);
					$upl_file = $upload_dir.$ufile;
					if (!move_uploaded_file($tmp_file, $upl_file)) {
						// error moving file
						$this->setError(sprintf(tool_error_upload_move_file, $upl_file)); 
						return false;
					}
					if (!chmod($upl_file, 0755)) {
    				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_chmod, basename($upl_file))));
    			}
    
					// Upload erfolgreich - Bild Abmessungen pruefen
					$path_parts = pathinfo($upl_file);
				
					list($origin_width, $origin_height) = getimagesize($upl_file);
  				$max_width = $dbMarketCfg->getValue(dbMarketCfg::cfgAdMaxImageWidth);
  				$max_height = $dbMarketCfg->getValue(dbMarketCfg::cfgAdMaxImageHeight);
  				$max_prev_width = $dbMarketCfg->getValue(dbMarketCfg::cfgAdImagePrevWidth);
  				
  				if (($origin_width > $max_width) || ($origin_height > $max_height)) {
  					// Bild runterrechnen
  					$factor = ($origin_width > $max_width) ? $max_width/$origin_width : $max_height/$origin_height;
  					$new_height = ceil($origin_height*$factor);
  					$new_width = ceil($origin_width*$factor);
  					if (false === ($new_file = $this->createTweakedFile($path_parts['filename'], $ext, $upl_file, 
  	  																												$new_width, $new_height, $origin_width, $origin_height))) {
  	  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_tweaking_file, $upl_file)));
  	  				return false;																										
  	  			}
  	  			@unlink($upl_file);
  	  			if (!rename($new_file, $upl_file)) {
  	  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_rename_file, $new_file, $upl_file)));
  	  				return false;
  	  			} 
  	  			$origin_width = $new_width;
  	  			$origin_height = $new_height; 	  			 	
  				}
					
  				// Vorschaubild anlegen
  				$factor = $max_prev_width/$origin_width;
  				$new_height = ceil($origin_height*$factor);
  				$new_width = ceil($origin_width*$factor);
  				if (false === ($new_file = $this->createTweakedFile($path_parts['filename'], $ext, $upl_file, 
  	  																												$new_width, $new_height, $origin_width, $origin_height))) {
  	  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_tweaking_file, $upl_file)));
  	  				return false;																										
  	  		}
  	  		if (!file_exists($upload_dir.$max_prev_width)) {
  	  			if (!mkdir($upload_dir.$max_prev_width, 0755, true)) {
  	  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_mkdir, $upload_dir.$max_prev_width)));
  	  				return false;
  	  			}
  	  		}	
  				if (!rename($new_file, $upload_dir.$max_prev_width.'/'.$ufile)) {
  					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_move_file, $new_file, $upload_dir.$max_prev_width.'/'.$ufile)));
  					return false;
  				}
  	  		
					if (!in_array($ufile, $pictures)) $pictures[] = $ufile;
					$changed = true;
					$message .= sprintf(tool_msg_upload_success, $_FILES[dbMarketAdvertisement::field_pictures]['name']);
				}          	
			}
			else {
				switch ($_FILES[dbMarketAdvertisement::field_pictures]['error']):
				case UPLOAD_ERR_INI_SIZE:
					$error = sprintf(tool_error_upload_ini_size, ini_get('upload_max_filesize'));
					break;
				case UPLOAD_ERR_FORM_SIZE:
					$error = tool_error_upload_form_size;
					break;
				case UPLOAD_ERR_PARTIAL:
					$error = sprintf(tool_error_upload_partial, $_FILES[dbMarketAdvertisement::field_pictures]['name']);
					break;
				default:
					$error = tool_error_upload_undefined_error;
				endswitch;
				$this->setError($error);
				return false;
			}	
		}
		$new_advertisement[dbMarketAdvertisement::field_pictures] = implode(',', $pictures);
	
			
		if ($checked && $changed) {
			// Daten sind geprueft und haben sich veraendert
			if ($ad_id > 0) {
				// Datensatz aktualisieren
				$where = array(
					dbMarketAdvertisement::field_id => $ad_id
				);
				unset($new_advertisement[dbMarketAdvertisement::field_timestamp]);
				if (!$dbMarketAd->sqlUpdateRecord($new_advertisement, $where)) {
					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbMarketAd->getError()));
					return false;
				}
				$message .= sprintf(tool_msg_record_updated, $ad_id);
			}
			else {
				// neuen Datensatz anlegen
				$status = $publish_directly ? dbMarketAdvertisement::status_active : dbMarketAdvertisement::status_locked;
				$new_advertisement[dbMarketAdvertisement::field_status] = $status;
				unset($new_advertisement[dbMarketAdvertisement::field_timestamp]);
				
				if (!$dbMarketAd->sqlInsertRecord($new_advertisement, $ad_id)) {
					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbMarketAd->getError()));
					return false;
				}
				$message .= $publish_directly ? market_msg_ad_inserted_locked : market_msg_ad_inserted_publish;
			}
			foreach ($dbMarketAd->getFields() as $key => $value) {
				unset($_REQUEST[$key]);
			}
			$_REQUEST[dbMarketAdvertisement::field_id] = $ad_id;				
		}
		
		$this->setMessage($message);
		return $this->accountAdvertisementAdd();
	} // accountAdvertisementCheck()
	
	public function accountOverview() {
		global $dbMarketAd;
		global $dbMarketCats;
		
		$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s' AND %s!='%s' ORDER BY %s DESC",
										$dbMarketAd->getTableName(),
										dbMarketAdvertisement::field_kit_id,
										$_SESSION[kitContactInterface::session_kit_contact_id],
										dbMarketAdvertisement::field_status,
										dbMarketAdvertisement::status_deleted,
										dbMarketAdvertisement::field_timestamp);
		$ads = array();
		if (!$dbMarketAd->sqlExec($SQL, $ads)) {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbMarketAd->getError()));
			return false;
		}
		if (count($ads) < 1) {
			// es gibt keine Eintraege
		}
		else {
			// Liste ausgeben
			$list = array();
			foreach ($ads as $ad) {
				$cat = array();
				$where = array(dbMarketCategories::field_id => $ad[dbMarketAdvertisement::field_category]);
				$category = array();
				if (!$dbMarketCats->sqlSelectRecord($where, $category)) {
					$this->setError(sprintf('[%s - %] %s', __METHOD__, __LINE__, $dbMarketCats->getError()));
					return false;
				}
				if (count($category) < 1) {
					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $ad[dbMarketAdvertisement::field_category])));
					return false;
				}
				$category = $category[0];
				for ($i=1;$i<6;$i++) {
					if (!empty($category[sprintf('cat_level_%02d', $i)])) $cat[] = $category[sprintf('cat_level_%02d', $i)];
				}
				$cat_str = implode(' > ', $cat);
				$list[] = array(
					'id'				=> array(	'value'		=> $ad[dbMarketAdvertisement::field_id],
																'link'		=> sprintf(	'%s%s%s=%s&%s=%s&%s=%s', 
																											$this->page_link, 
																											(strpos($this->page_link, '?') === false) ? '?' : '&', 
																											self::request_main_action, 
																											self::action_account, 
																											self::request_account_action, 
																											self::action_advertisement_add, 
																											dbMarketAdvertisement::field_id, 
																											$ad[dbMarketAdvertisement::field_id])),
					'type'			=> array(	'offer'		=> ($ad[dbMarketAdvertisement::field_ad_type] == dbMarketAdvertisement::type_offer) ? 1 : 0),
					'title'			=> array(	'text'		=> $ad[dbMarketAdvertisement::field_title]),
					'category'	=> array(	'text'		=> $cat_str)
				);
 			}
 			
			$form = array(
				'head'					=> market_head_advertisement_overview,
				'is_message'		=> $this->isMessage() ? 1 : 0,
				'message'				=> $this->isMessage() ? $this->getMessage() : market_intro_advertisement_overview,
				'header'		=> array(	'id'					=> market_th_id,
														'kit_id'			=> market_th_kit_id,
														'category'		=> market_th_category,
														'type'				=> market_th_type,
														'commercial'	=> market_th_commercial,
														'title'				=> market_th_title,
														'price'				=> market_th_price,
														'price_type'	=> market_th_price_type,
														'pictures'		=> market_th_pictures,
														'text'				=> market_th_text,
														'status'			=> market_th_status,
														'start_date'	=> market_th_start_date,
														'end_date'		=> market_th_end_date,
														'timestamp'		=> market_th_timestamp)
			);
			$data = array(
				'form'					=> $form,
				'advertisement'	=> $list
			);
			return $this->getTemplate('account.advertisement.overview.list.htt', $data);
		}
	} // accountOverview()
	
	
	
	/**
	 * FUNKTION DES OEFFENTLICHEN MARKTPLATZES
	 */
	
	public function marketAction() {
		
		$action = (isset($_REQUEST[self::request_market_action])) ? $_REQUEST[self::request_market_action] : self::action_default;
		$cat = -1;
		switch ($action):
		case self::action_advertisement:
			$content = $this->marketShowAdvertisement();
			break;
		case self::action_category:
  		// bestimmte Kategorie anzeigen
  		$content = $this->marketShowCategory($cat);
  		break;
		default:
			$content = $this->marketShowCategory($cat);
			break;
		endswitch;
		
		$data = array(
			'content'			=> $content,
			'category'		=> $cat,	
			'categories'	=> $this->marketCategoriesList()
		);
		return $this->getTemplate('body.market.htt', $data);
	} // marketAction()
	
	/**
	 * Navigationsliste durch die Kategorien
	 * 
	 * @return ARRAY categories
	 */
	public function marketCategoriesList() {
		global $dbMarketCats;
		
		$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s' ORDER BY %s, %s, %s, %s, %s ASC",
										$dbMarketCats->getTableName(),
										dbMarketCategories::field_status,
										dbMarketCategories::status_active,
										dbMarketCategories::field_level_01,
										dbMarketCategories::field_level_02,
										dbMarketCategories::field_level_03,
										dbMarketCategories::field_level_04,
										dbMarketCategories::field_level_05);
		$categories = array();
		if (!$dbMarketCats->sqlExec($SQL, $categories)) {
			$this->setError($dbMarketCats->getError()); return false;
		}
		
		$cat_list = array();
		
		foreach ($categories as $cat) {
			$text = '';
			for ($i=1; $i < 6; $i++) {
				if (!empty($cat[sprintf('cat_level_%02d', $i)])) {
					$level = $i;
					$text = $cat[sprintf('cat_level_%02d', $i)];
				}
			}
			$cat_list[] = array(
				1					=> $cat[dbMarketCategories::field_level_01],
				2					=> $cat[dbMarketCategories::field_level_02],
				3					=> $cat[dbMarketCategories::field_level_03],
				4					=> $cat[dbMarketCategories::field_level_04],
				5					=> $cat[dbMarketCategories::field_level_05],
				'id'			=> $cat[dbMarketCategories::field_id],
				'text'		=> $text,
				'link'		=> sprintf(	'%s%s%s=%s&%s=%s',
															$this->page_link,
															(strpos($this->page_link, '?') === false) ? '?' : '&',
															self::request_market_action,
															self::action_category,
															dbMarketCategories::field_id,
															$cat[dbMarketCategories::field_id]
															),
				'level'		=> $level,
				'parent'	=> ($level > 1) ? $cat[sprintf('cat_level_%02d', $level-1)] : $cat[dbMarketCategories::field_level_01], 
			);	
		}
		
		return $cat_list;
	} // marketCategoriesList()
	
	public function marketShowCategory(&$cat_id=-1) {
		global $dbMarketAd;
		global $dbMarketCats;
		global $dbMarketCfg;
		global $kitContactInterface;
		
		$cat_id = isset($_REQUEST[dbMarketCategories::field_id]) ? $_REQUEST[dbMarketCategories::field_id] : -1;
		$max_entries = $dbMarketCfg->getValue(dbMarketCfg::cfgAdListEntries);
		
		if ($cat_id < 1) {
			// Startseite - keine Kategorie gewaehlt
			$SQL = sprintf( "SELECT * FROM %s,%s WHERE %s=%s AND %s='%s' ORDER BY %s DESC LIMIT %s",
											$dbMarketAd->getTableName(),
											$dbMarketCats->getTableName(),
											dbMarketAdvertisement::field_category,
											dbMarketCategories::field_id,
											dbMarketAdvertisement::field_status,
											dbMarketAdvertisement::status_active,
											dbMarketAdvertisement::field_timestamp,
											$max_entries);
		}
		else {
			// Kategorie ausgewaehlt
			$where = array(dbMarketCategories::field_id => $cat_id);
			$category = array();
			if (!$dbMarketCats->sqlSelectRecord($where, $category)) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbMarketCats->getError()));
				return false;
			}
			if (count($category) < 1) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $cat_id)));
				return false;
			}
			$category = $category[0];
			$fields = '';
			for ($i=1;$i<6;$i++) {
				if (!empty($category[sprintf('cat_level_%02d', $i)])) {
					if (!empty($fields)) $fields .= ' AND ';
					$fields .= sprintf("cat_level_%02d='%s'", $i, $category[sprintf('cat_level_%02d', $i)]);	
				}
				else {
					break;
				}				
			}
			if (empty($fields)) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, market_error_undefined));
				return false;
			}
			$SQL = sprintf( "SELECT * FROM %s,%s WHERE %s=%s AND %s='%s' AND %s ORDER BY %s DESC",
											$dbMarketAd->getTableName(),
											$dbMarketCats->getTableName(),
											dbMarketAdvertisement::field_category,
											dbMarketCategories::field_id,
											dbMarketAdvertisement::field_status,
											dbMarketAdvertisement::status_active,
											$fields,
											dbMarketAdvertisement::field_timestamp);
		}
		
		$advertisements = array();
		if (!$dbMarketAd->sqlExec($SQL, $advertisements)) {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbMarketAd->getError()));
			return false;
		}
		
		$items = array();
		foreach ($advertisements as $ad) {
			
			$max_image_width = $dbMarketCfg->getValue(dbMarketCfg::cfgAdMaxImageWidth);
			$max_image_height = $dbMarketCfg->getValue(dbMarketCfg::cfgAdMaxImageHeight);
			$prev_width = $dbMarketCfg->getValue(dbMarketCfg::cfgAdImagePrevWidth);
			$images = array();
			$pictures = (!empty($ad[dbMarketAdvertisement::field_pictures])) ? explode(',', $ad[dbMarketAdvertisement::field_pictures]) : array();
			
			$upl_path = $this->media_path.$ad[dbMarketAdvertisement::field_kit_id].'/'.$ad[dbMarketAdvertisement::field_id].'/';
			$upl_url = str_replace(WB_PATH, WB_URL, $upl_path);
			$prev_path = $upl_path.$prev_width.'/';
			$prev_url = str_replace(WB_PATH, WB_URL, $prev_path);
			
			$tmp_dir = $this->media_path.$ad[dbMarketAdvertisement::field_kit_id].'/tmp/';

			$i=0;
			foreach ($pictures as $picture) {
				$i++;
				if (!file_exists($upl_path.$picture)) {
					if (file_exists($tmp_dir.$picture)) {
						// Datei befindet sich noch im TEMP Verzeichnis, verschieben...
						if (!file_exists($upl_path)) {
							// Verzeichnis erstellen
							if (!mkdir($upl_path, 0755, true)) {
								$this->setError(sprintf('[%s - %s] %s', sprintf(tool_error_mkdir, $upl_path)));
								return false;
							}
						}
						if (!rename($tmp_dir.$picture, $upl_path.$picture)) {
							$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_move_file, $tmp_dir.$picture, $upl_path.$picture)));
							return false;
						}
					}
					else {
						// Datei nicht gefunden
						$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_missing_file, $upl_path.$picture)));
						return false;
					}
				}
				// Dateiinformationen
				$path_parts = pathinfo($upl_path.$picture);
				// Breite und Hoehe festhalten
				list($full_width, $full_height) = getimagesize($upl_path.$picture);
				// Vorschaubild pruefen
				if (!file_exists($prev_path.$picture)) {
					$factor = $prev_width/$full_width;
	  			$new_height = ceil($full_height*$factor);
	  			$new_width = ceil($full_width*$factor);
	  			if (false === ($new_file = $this->createTweakedFile($path_parts['filename'], $path_parts['extension'], $upl_path.$picture, 
	  	  																												$new_width, $new_height, $full_width, $full_height))) {
	  	  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_tweaking_file, $upl_path.$picture)));
	  	  			return false;																										
	  	  	}
	  	  	if (!file_exists($prev_path)) {
	  	  		if (!mkdir($prev_path, 0755, true)) {
	  	  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_mkdir, $prev_path)));
	  	  			return false;
	  	  		}
	  	  	}	
	  			if (!rename($new_file, $prev_path.$picture)) {
	  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_move_file, $new_file, $prev_path.$picture)));
	  				return false;
	  			}	
				}
				list($prev_width, $prev_height) = getimagesize($prev_path.$picture);
				$images[$i] = array(
					'fullsize'		=> array(	'url'			=> $upl_url.$picture,
																	'width'		=> $full_width,
																	'height'	=> $full_height	),
					'preview'			=> array( 'url'			=> $prev_url.$picture,
																	'width'		=> $prev_width,
																	'height'	=> $prev_height )
				);
			}
			
			// Kategorien
			$kat = array();
			for ($i=1;$i<6;$i++) {
				if (!empty($ad[sprintf('cat_level_%02d', $i)])) $kat[] = $ad[sprintf('cat_level_%02d', $i)];
			}
			$kat_str = implode(' > ', $kat);
			
			// Kontakt
			if (!$kitContactInterface->getContact($ad[dbMarketAdvertisement::field_kit_id], $contact)) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
				return false;
			}
			$items[] = array(
				'id'					=> $ad[dbMarketAdvertisement::field_id],
				'images'			=> $images,
				'category'		=> $kat_str,
				'type'				=> $dbMarketAd->type_array[$ad[dbMarketAdvertisement::field_ad_type]],
				'commercial'	=> $dbMarketAd->commercial_array[$ad[dbMarketAdvertisement::field_commercial]],
				'title'				=> $ad[dbMarketAdvertisement::field_title],
				'text'				=> $ad[dbMarketAdvertisement::field_text],
				'price'				=> array(	'value'	=> $ad[dbMarketAdvertisement::field_price],
																'text'	=> number_format($ad[dbMarketAdvertisement::field_price], 2, tool_cfg_decimal_separator, tool_cfg_thousand_separator)),
				'price_type'	=> $dbMarketAd->price_array[$ad[dbMarketAdvertisement::field_price_type]],
				'link'				=> sprintf(	'%s%s%s=%s&%s=%s',
																	$this->page_link,
																	(strpos($this->page_link, '?') === false) ? '?' : '&',
																	self::request_market_action,
																	self::action_advertisement,
																	dbMarketAdvertisement::field_id,
																	$ad[dbMarketAdvertisement::field_id] ),	
				'contact'			=> $contact			
			);
		}
		
		$data = array(
			'category'		=> $cat_id,
			'items'				=> $items
		);
		return $this->getTemplate('market.advertisement.list.htt', $data);
	} // marketShowCategory()
	
	public function marketShowAdvertisement() {
		global $dbMarketAd;
		global $dbMarketCfg;
		global $kitContactInterface;
		
		$ad_id = (isset($_REQUEST[dbMarketAdvertisement::field_id])) ? $_REQUEST[dbMarketAdvertisement::field_id] : -1;
		
		if ($ad_id < 1) {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_request_missing, dbMarketAdvertisement::field_id)));
			return false;
		}
		
		$where = array(dbMarketAdvertisement::field_id => $ad_id);
		$ad = array();
		if (!$dbMarketAd->sqlSelectRecord($where, $ad)) {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbMarketAd->getError()));
			return false;
		}
		if (count($ad) < 1) {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $ad_id)));
			return false;
		}
		$ad = $ad[0];
		
		// Bilder einlesen
		$max_image_width = $dbMarketCfg->getValue(dbMarketCfg::cfgAdMaxImageWidth);
		$max_image_height = $dbMarketCfg->getValue(dbMarketCfg::cfgAdMaxImageHeight);
		$prev_width = $dbMarketCfg->getValue(dbMarketCfg::cfgAdImagePrevWidth);
		$images = array();
		$pictures = (!empty($ad[dbMarketAdvertisement::field_pictures])) ? explode(',', $ad[dbMarketAdvertisement::field_pictures]) : array();
		
		$upl_path = $this->media_path.$ad[dbMarketAdvertisement::field_kit_id].'/'.$ad[dbMarketAdvertisement::field_id].'/';
		$upl_url = str_replace(WB_PATH, WB_URL, $upl_path);
		$prev_path = $upl_path.$prev_width.'/';
		$prev_url = str_replace(WB_PATH, WB_URL, $prev_path);
		
		$tmp_dir = $this->media_path.$ad[dbMarketAdvertisement::field_kit_id].'/tmp/';

		$i=0;
		foreach ($pictures as $picture) {
			$i++;
			if (!file_exists($upl_path.$picture)) {
				if (file_exists($tmp_dir.$picture)) {
					// Datei befindet sich noch im TEMP Verzeichnis, verschieben...
					if (!file_exists($upl_path)) {
						// Verzeichnis erstellen
						if (!mkdir($upl_path, 0755, true)) {
							$this->setError(sprintf('[%s - %s] %s', sprintf(tool_error_mkdir, $upl_path)));
							return false;
						}
					}
					if (!rename($tmp_dir.$picture, $upl_path.$picture)) {
						$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_move_file, $tmp_dir.$picture, $upl_path.$picture)));
						return false;
					}
				}
				else {
					// Datei nicht gefunden
					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_missing_file, $upl_path.$picture)));
					return false;
				}
			}
			// Dateiinformationen
			$path_parts = pathinfo($upl_path.$picture);
			// Breite und Hoehe festhalten
			list($full_width, $full_height) = getimagesize($upl_path.$picture);
			// Vorschaubild pruefen
			if (!file_exists($prev_path.$picture)) {
				$factor = $prev_width/$full_width;
  			$new_height = ceil($full_height*$factor);
  			$new_width = ceil($full_width*$factor);
  			if (false === ($new_file = $this->createTweakedFile($path_parts['filename'], $path_parts['extension'], $upl_path.$picture, 
  	  																												$new_width, $new_height, $full_width, $full_height))) {
  	  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_tweaking_file, $upl_path.$picture)));
  	  			return false;																										
  	  	}
  	  	if (!file_exists($prev_path)) {
  	  		if (!mkdir($prev_path, 0755, true)) {
  	  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_mkdir, $prev_path)));
  	  			return false;
  	  		}
  	  	}	
  			if (!rename($new_file, $prev_path.$picture)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_move_file, $new_file, $prev_path.$picture)));
  				return false;
  			}	
			}
			list($prev_width, $prev_height) = getimagesize($prev_path.$picture);
			$images[$i] = array(
				'fullsize'		=> array(	'url'			=> $upl_url.$picture,
																'width'		=> $full_width,
																'height'	=> $full_height	),
				'preview'			=> array( 'url'			=> $prev_url.$picture,
																'width'		=> $prev_width,
																'height'	=> $prev_height )
			);
		}
		
		// Kategorien
		$kat = array();
		for ($i=1;$i<6;$i++) {
			if (!empty($ad[sprintf('cat_level_%02d', $i)])) $kat[] = $ad[sprintf('cat_level_%02d', $i)];
		}
		$kat_str = implode(' > ', $kat);
		
		// Kontakt
		if (!$kitContactInterface->getContact($ad[dbMarketAdvertisement::field_kit_id], $contact)) {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
			return false;
		}
		$item = array(
			'id'					=> $ad[dbMarketAdvertisement::field_id],
			'images'			=> $images,
			'category'		=> $kat_str,
			'type'				=> $dbMarketAd->type_array[$ad[dbMarketAdvertisement::field_ad_type]],
			'commercial'	=> $dbMarketAd->commercial_array[$ad[dbMarketAdvertisement::field_commercial]],
			'title'				=> $ad[dbMarketAdvertisement::field_title],
			'text'				=> $ad[dbMarketAdvertisement::field_text],
			'price'				=> array(	'value'	=> $ad[dbMarketAdvertisement::field_price],
															'text'	=> number_format($ad[dbMarketAdvertisement::field_price], 2, tool_cfg_decimal_separator, tool_cfg_thousand_separator)),
			'price_type'	=> $dbMarketAd->price_array[$ad[dbMarketAdvertisement::field_price_type]],
			'link'				=> sprintf(	'%s%s%s=%s&%s=%s',
																$this->page_link,
																(strpos($this->page_link, '?') === false) ? '?' : '&',
																self::request_market_action,
																self::action_advertisement,
																dbMarketAdvertisement::field_id,
																$ad[dbMarketAdvertisement::field_id] ),	
			'contact'			=> $contact			
		);
		
		$data = array(
			'advertisement' => $item
		);
		return $this->getTemplate('market.advertisement.detail.htt', $data);
	} // marketShowAdvertisement
	
} // class marketFrontend

?>