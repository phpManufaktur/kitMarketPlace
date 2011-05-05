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

class marketBackend {
	
	const request_action							= 'act';
	const request_items								= 'its';
	const request_category_edit				=	'cate';
	const request_category_delete			= 'catd';
	const request_category_add				= 'cata';
	const request_category_select			= 'cats';
	
	const action_about								= 'abt';
	const action_categories						= 'cats';
	const action_categories_check			= 'catsc';
	const action_config								= 'cfg';
	const action_config_check					= 'cfgc';
	const action_default							= 'def';
	const action_advertisement				= 'ead';
	const action_advertisement_check	= 'eadc';
	const action_list									= 'lst';
	
	private $tab_navigation_array = array(
		self::action_advertisement			=> market_tab_advertisement,
		self::action_categories					=> market_tab_categories,
		self::action_config							=> market_tab_config,
		self::action_about							=> market_tab_about
	);
	
	private $page_link 					= '';
	private $img_url						= '';
	private $template_path			= '';
	private $error							= '';
	private $message						= '';
	
	public function __construct() {
		$this->page_link = ADMIN_URL.'/admintools/tool.php?tool=kit_market';
		$this->template_path = WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/htt/' ;
		$this->img_url = WB_URL. '/modules/'.basename(dirname(__FILE__)).'/images/';
		date_default_timezone_set(tool_cfg_time_zone);
	} // __construct()
	
	/**
    * Set $this->error to $error
    * 
    * @param STR $error
    */
  public function setError($error) {
  	$debug = debug_backtrace();
    $caller = next($debug);
  	$this->error = sprintf('[%s::%s - %s] %s', basename($caller['file']), $caller['function'], $caller['line'], $error);
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
   * Return Version of Module
   *
   * @return FLOAT
   */
  public function getVersion() {
    // read info.php into array
    $info_text = file(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.php');
    if ($info_text == false) {
      return -1; 
    }
    // walk through array
    foreach ($info_text as $item) {
      if (strpos($item, '$module_version') !== false) {
        // split string $module_version
        $value = explode('=', $item);
        // return floatval
        return floatval(preg_replace('([\'";,\(\)[:space:][:alpha:]])', '', $value[1]));
      } 
    }
    return -1;
  } // getVersion()
  
  public function getTemplate($template, $template_data) {
  	global $parser;
  	try {
  		$result = $parser->get($this->template_path.$template, $template_data); 
  	} catch (Exception $e) {
  		$this->setError(sprintf(tool_error_template_error, $template, $e->getMessage()));
  		return false;
  	}
  	return $result;
  } // getTemplate()
  
  
  /**
   * Verhindert XSS Cross Site Scripting
   * 
   * @param REFERENCE $_REQUEST Array
   * @return $request
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
	
  public function action() {
  	$html_allowed = array();
  	foreach ($_REQUEST as $key => $value) {
  		if (!in_array($key, $html_allowed)) {
  			$_REQUEST[$key] = $this->xssPrevent($value);	  			
  		} 
  	}
    isset($_REQUEST[self::request_action]) ? $action = $_REQUEST[self::request_action] : $action = self::action_default;
  	switch ($action):
  	case self::action_advertisement:
  		$this->show(self::action_advertisement, $this->dlgEditAdvertisement());
  		break;
  	case self::action_advertisement_check:
  		$this->show(self::action_advertisement, $this->checkEditAdvertisement());
  		break;
  	case self::action_categories:
  		$this->show(self::action_categories, $this->dlgCategories());
  		break;
  	case self::action_categories_check:
  		$this->show(self::action_categories, $this->checkCategories());
  		break;
  	case self::action_config:
  		$this->show(self::action_config, $this->dlgConfig());
  		break;
  	case self::action_config_check:
  		$this->show(self::action_config, $this->checkConfig());
  		break;
  	case self::action_about:
  		$this->show(self::action_about, $this->dlgAbout());
  		break;
  	default:
  		$this->show(self::action_list, $this->dlgList());
  		break;
  	endswitch;
  } // action
	
  	
  /**
   * Ausgabe des formatierten Ergebnis mit Navigationsleiste
   * 
   * @param $action - aktives Navigationselement
   * @param $content - Inhalt
   * 
   * @return ECHO RESULT
   */
  public function show($action, $content) {
  	$navigation = array();
  	foreach ($this->tab_navigation_array as $key => $value) {
  		$navigation[] = array(
  			'active' 	=> ($key == $action) ? 1 : 0,
  			'url'			=> sprintf('%s&%s=%s', $this->page_link, self::request_action, $key),
  			'text'		=> $value
  		);
  	}
  	$data = array(
  		'WB_URL'			=> WB_URL,
  		'navigation'	=> $navigation,
  		'error'				=> ($this->isError()) ? 1 : 0,
  		'content'			=> ($this->isError()) ? $this->getError() : $content
  	);
  	echo $this->getTemplate('backend.body.htt', $data);
  } // show()
	
  public function dlgList() {
  	return __METHOD__;
  } // dlgList()
  
  public function dlgAbout() {
  	$data = array(
  		'version'					=> sprintf('%01.2f', $this->getVersion()),
  		'img_url'					=> $this->img_url.'/kit_market_logo_505x386.jpg',
  		'release_notes'		=> file_get_contents(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.txt'),
  	);
  	return $this->getTemplate('backend.about.htt', $data);
  } // dlgAbout()
  
  public function dlgConfig() {
		global $dbMarketCfg;
		$SQL = sprintf(	"SELECT * FROM %s WHERE NOT %s='%s' ORDER BY %s",
										$dbMarketCfg->getTableName(),
										dbMarketCfg::field_status,
										dbMarketCfg::status_deleted,
										dbMarketCfg::field_name);
		$config = array();
		if (!$dbMarketCfg->sqlExec($SQL, $config)) {
			$this->setError($dbMarketCfg->getError());
			return false;
		}
		$count = array();
		$header = array(
			'identifier'	=> tool_header_cfg_identifier,
			'value'				=> tool_header_cfg_value,
			'description'	=> tool_header_cfg_description
		);
		
		$items = array();
		// bestehende Eintraege auflisten
		foreach ($config as $entry) {
			$id = $entry[dbMarketCfg::field_id];
			$count[] = $id;
			$value = (isset($_REQUEST[dbMarketCfg::field_value.'_'.$id])) ? $_REQUEST[dbMarketCfg::field_value.'_'.$id] : $entry[dbMarketCfg::field_value];
			$value = str_replace('"', '&quot;', stripslashes($value));
			$items[] = array(
				'id'					=> $id,
				'identifier'	=> constant($entry[dbMarketCfg::field_label]),
				'value'				=> $value,
				'name'				=> sprintf('%s_%s', dbMarketCfg::field_value, $id),
				'description'	=> constant($entry[dbMarketCfg::field_description])  
			);
		}
		$data = array(
			'form_name'						=> 'market_cfg',
			'form_action'					=> $this->page_link,
			'action_name'					=> self::request_action,
			'action_value'				=> self::action_config_check,
			'items_name'					=> self::request_items,
			'items_value'					=> implode(",", $count), 
			'head'								=> tool_header_cfg,
			'intro'								=> $this->isMessage() ? $this->getMessage() : sprintf(tool_intro_cfg, 'kitMarketPlace'),
			'is_message'					=> $this->isMessage() ? 1 : 0,
			'items'								=> $items,
			'btn_ok'							=> tool_btn_ok,
			'btn_abort'						=> tool_btn_abort,
			'abort_location'			=> $this->page_link,
			'header'							=> $header
		);
		return $this->getTemplate('backend.config.htt', $data);
	} // dlgConfig()
	
	/**
	 * Ueberprueft Aenderungen die im Dialog dlgConfig() vorgenommen wurden
	 * und aktualisiert die entsprechenden Datensaetze.
	 * 
	 * @return STR DIALOG dlgConfig()
	 */
	public function checkConfig() {
		global $dbMarketCfg;
		$message = '';
		// ueberpruefen, ob ein Eintrag geaendert wurde
		if ((isset($_REQUEST[self::request_items])) && (!empty($_REQUEST[self::request_items]))) {
			$ids = explode(",", $_REQUEST[self::request_items]);
			foreach ($ids as $id) {
				if (isset($_REQUEST[dbMarketCfg::field_value.'_'.$id])) {
					$value = $_REQUEST[dbMarketCfg::field_value.'_'.$id];
					$where = array();
					$where[dbMarketCfg::field_id] = $id; 
					$config = array();
					if (!$dbMarketCfg->sqlSelectRecord($where, $config)) {
						$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbMarketCfg->getError()));
						return false;
					}
					if (sizeof($config) < 1) {
						$this->setError(sprintf(tool_error_cfg_id, $id));
						return false;
					}
					$config = $config[0];
					if ($config[dbMarketCfg::field_value] != $value) {
						// Wert wurde geaendert
							if (!$dbMarketCfg->setValue($value, $id) && $dbMarketCfg->isError()) {
								$this->setError($dbMarketCfg->getError());
								return false;
							}
							elseif ($dbMarketCfg->isMessage()) {
								$message .= $dbMarketCfg->getMessage();
							}
							else {
								// Datensatz wurde aktualisiert
								$message .= sprintf(tool_msg_cfg_id_updated, $config[dbMarketCfg::field_name]);
							}
					}
				}
			}		
		}		
		$this->setMessage($message);
		return $this->dlgConfig();
	} // checkConfig()
  
	public function dlgCategories() {
		global $dbMarketCats;
		global $dbMarketCfg;
		
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
		
		/**
		 * Kategorien durchlaufen, Editierfelder fuer die Ebenen setzen, Moeglichkeit zum Loeschen einfuegen 
		 */
		$items = array();
		$all_items = array();
		$u=1;
		$check_array = array();
		$cat_list = array();
		$cat_list[] = array(
			'text'	=> market_text_add_new_category,
			'id'		=> -1
		);
		foreach ($categories as $category) {
			$items[$u]['id'] = $category[dbMarketCategories::field_id];
			$all_items[] = $category[dbMarketCategories::field_id];
			$path = array();
			for ($i=1;$i<6;$i++) {
				$items[$u]['value'][$i] = $category[sprintf('cat_level_%02d', $i)];
				if ((!empty($category[sprintf('cat_level_%02d', $i)]) && (isset($category[sprintf('cat_level_%02d', $i+1)]) && empty($category[sprintf('cat_level_%02d', $i+1)]))) || 
						(($i == 5) && (!empty($category[sprintf('cat_level_%02d', $i)])))) {
					$items[$u]['edit'][$i] = 1;
				}
				else {
					$items[$u]['edit'][$i] = 0;
				}
				$items[$u]['request']['edit'][$i] = sprintf('%s_%d', self::request_category_edit, $category[dbMarketCategories::field_id]);
				if (!empty($category[sprintf('cat_level_%02d', $i)]) && ($i < 5)) $path[] = $category[sprintf('cat_level_%02d', $i)];
			}
			$items[$u]['request']['delete'] = self::request_category_delete;
			$path_str = implode(' > ', $path);
			if (!in_array($path_str, $check_array)) {
				$check_array[] = $path_str;
				$cat_list[] = array(
					'text' 	=> $path_str,
					'id'		=> $category[dbMarketCategories::field_id]
				);
			}
			$u++;
		}
		
		$header = array();
		for ($i=1; $i<6; $i++) {
			$header['level'][$i] = $dbMarketCfg->getValue(sprintf('cfgCategory_%02d', $i));
		}
		$header['delete'] = market_th_delete;
		
		$data = array(
			'form_name'				=> 'form_cats',
			'form_action'			=> $this->page_link,
			'action_name'			=> self::request_action,
			'action_value'		=> self::action_categories_check,
			'items_name'			=> self::request_items,
			'items_value'			=> implode(',', $all_items),
			'btn_abort'				=> tool_btn_abort,
			'abort_location'	=> $this->page_link,
			'btn_ok'					=> tool_btn_ok,
			'categories'			=> $items,
			'header'					=> $header,
			'head'						=> market_head_categories,
			'is_message'			=> $this->isMessage() ? 1 : 0,
			'intro'						=> $this->isMessage() ? $this->getMessage() : market_intro_categories,
			'select_cat_name'	=> self::request_category_select,
			'select_cat_value'=> $cat_list,
			'add_cat_label'		=> market_label_add_category,
			'add_cat_name'		=> self::request_category_add
		);
		return $this->getTemplate('backend.categories.htt', $data);
	} // dlgCategories()
	
	public function checkCategories() {
		global $dbMarketCats;
		global $dbMarketCfg;
		
		$message = '';
		if (!isset($_REQUEST[self::request_items])) {
			$this->setError(sprintf(tool_error_request_missing, self::request_items)); return false;
		}
		if (!empty($_REQUEST[self::request_items])) {
			$SQL = sprintf( "SELECT * FROM %s WHERE %s IN (%s)",
											$dbMarketCats->getTableName(),
											dbMarketCategories::field_id,
											$_REQUEST[self::request_items]);
			$categories = array();
			if (!$dbMarketCats->sqlExec($SQL, $categories)) {
				$this->setError($dbMarketCats->getError()); return false;
			}
		}
		else {
			$categories = array();
		}
		
		// Kategorie hinzufuegen?
		if (isset($_REQUEST[self::request_category_add]) && !empty($_REQUEST[self::request_category_add])) {
			$add_cat = $_REQUEST[self::request_category_add];
			$id = -1;
			if ($_REQUEST[self::request_category_select] == -1) {
				// neue Kategorie der obersten Ebene
				$data = array(
					dbMarketCategories::field_level_01 => $add_cat
				);
				if (!$dbMarketCats->sqlInsertRecord($data, $id)) {
					$this->setError($dbMarketCats->getError()); return false;
				}
				$message .= sprintf(market_msg_category_inserted, $dbMarketCfg->getValue(dbMarketCfg::cfgCategory_01), $add_cat);
			} 
			else { 
				// untergeordnete Kategorie hinzufuegen
				$where = array(
					dbMarketCategories::field_id => $_REQUEST[self::request_category_select]
				);
				$data = array();
				if (!$dbMarketCats->sqlSelectRecord($where, $data)) {
					$this->setError($dbMarketCats->getError()); return false;
				}
				if (count($data) < 1) {
					$this->setError(sprintf(tool_error_id_invalid, $_REQUEST[self::request_category_select])); return false;
				}
				$data = $data[0];
				for ($i=1; $i < 6; $i++) {
					if (empty($data[sprintf('cat_level_%02d', $i)])) {
						$data[sprintf('cat_level_%02d', $i)] = $add_cat;
						break;
					}
				}
				if (!$dbMarketCats->sqlInsertRecord($data, $id)) {
					$this->setError($dbMarketCats->getError()); return false;
				}
				$message .= sprintf(market_msg_category_inserted, $dbMarketCfg->getValue(sprintf('cfgCategory_%02d', $i)), $add_cat);
			}
		}
		
		// sollen Kategorien geloescht werden?
		$delete_categories = (isset($_REQUEST[self::request_category_delete])) ? $_REQUEST[self::request_category_delete] : array();
		
		foreach ($categories as $category) {
			if (isset($_REQUEST[sprintf('%s_%d', self::request_category_edit, $category[dbMarketCategories::field_id])]) && 
					!empty($_REQUEST[sprintf('%s_%d', self::request_category_edit, $category[dbMarketCategories::field_id])])) {
				$check = '';
				$level = 0;
				for ($i=1; $i<6; $i++) {
					if (!empty($category[sprintf('cat_level_%02d', $i)])) {
						$check = $category[sprintf('cat_level_%02d', $i)];
						$level = $i;
					}
					else {
						break;
					}
				}
				if ($check !== $_REQUEST[sprintf('%s_%d', self::request_category_edit, $category[dbMarketCategories::field_id])]) {
					// Datensatz bzw. Datensaetze aktualisieren
					$where = array(
						sprintf('cat_level_%02d', $level) => $check,
						dbMarketCategories::field_status => dbMarketCategories::status_active
					);
					$data = array(
						sprintf('cat_level_%02d', $level) => $_REQUEST[sprintf('%s_%d', self::request_category_edit, $category[dbMarketCategories::field_id])],
					);					
					if (!$dbMarketCats->sqlUpdateRecord($data, $where)) {
						$this->setError($dbMarketCats->getError()); return false;
					}
					$message .= sprintf(sprintf(market_msg_category_updated, $dbMarketCfg->getValue(sprintf('cfgCategory_%02d', $level)), $check, $_REQUEST[sprintf('%s_%d', self::request_category_edit, $category[dbMarketCategories::field_id])]));
				}
			}
			if (in_array($category[dbMarketCategories::field_id], $delete_categories)) {
				// Datensatz bzw. Datensaetze loeschen
				$check = '';
				$level = 0;
				// Ebene ermitteln
				for ($i=1; $i<6; $i++) {
					if (!empty($category[sprintf('cat_level_%02d', $i)])) {
						$check = $category[sprintf('cat_level_%02d', $i)];
						$level = $i;
					}
					else {
						break;
					} 
				}
				$where = array(
					sprintf('cat_level_%02d', $level) => $check,
					dbMarketCategories::field_status => dbMarketCategories::status_active
				);	
				$data = array(
					dbMarketCategories::field_status => dbMarketCategories::status_deleted
				);
				if (!$dbMarketCats->sqlUpdateRecord($data, $where)) {
					$this->setError($dbMarketCats->getError()); return false;
				}
				$message .= sprintf(market_msg_category_deleted, $dbMarketCfg->getValue(sprintf('cfgCategory_%02d', $level)), $check);
			}
		}	
		
		// Benachrichtigungen setzen und Dialog wieder anzeigen
		$this->setMessage($message);
		return $this->dlgCategories();
	} // checkCategories()
	
	public function dlgEditAdvertisement() {
		global $bdMarketAd;
		
	} // dlgEditAdvertisement()
	
	public function checkEditAdvertisement() {
		
	} // checkAdvertisement()
	
} // class marketBackend

?>