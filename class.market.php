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


class dbMarketAdvertisement extends dbConnectLE {

	const field_id							= 'ad_id';
	const field_kit_id					= 'ad_kit_id';
	const field_category				= 'ad_category';
	const field_ad_type					= 'ad_type';			// Suche/Biete
	const field_commercial			= 'ad_commercial';
	const field_title						= 'ad_title';
	const field_price						= 'ad_price';
	const field_price_type			= 'ad_price_type';
	const field_pictures				= 'ad_pictures';
	const field_text						= 'ad_text';
	const field_status					= 'ad_status';
	const field_start_date			= 'ad_start_date';
	const field_end_date				= 'ad_end_date';
	const field_timestamp				= 'ad_timestamp';

	public $field_name_array = array(
		self::field_id							=> market_field_id,
		self::field_kit_id					=> market_field_kit_id,
		self::field_category				=> market_field_category,
		self::field_ad_type					=> market_field_ad_type,
		self::field_commercial			=> market_field_commercial,
		self::field_title						=> market_field_title,
		self::field_price						=> market_field_price,
		self::field_price_type			=> market_field_price_type,
		self::field_pictures				=> market_field_price_type,
		self::field_text						=> market_field_text,
		self::field_status					=> market_field_status,
		self::field_start_date			=> market_field_start_date,
		self::field_end_date				=> market_field_end_date,
		self::field_timestamp				=> market_field_timestamp
	);

	const type_offer						= 1;
	const type_search						= 2;
	const type_undefined				= 0;

	public $type_array = array(
		self::type_offer					=> market_type_offer,
		self::type_search					=> market_type_search,
		//self::type_undefined			=> market_type_undefined
	);

	const commercial_yes				= 1;
	const commercial_no					= 2;
	const commercial_undefined	= 0;

	public $commercial_array = array(
		self::commercial_yes				=> market_commercial_yes,
		self::commercial_no					=> market_commercial_no,
		//self::commercial_undefined	=> market_commercial_undefined
	);

	const price_fixed						= 1;
	const price_asking					= 2;
	const price_give_away				= 3;
	const price_undefined				= 0;

	public $price_array = array(
		self::price_fixed					=> market_price_fixed,
		self::price_asking				=> market_price_asking,
		self::price_give_away			=> market_price_give_away,
		//self::price_undefined			=> market_price_undefined
	);

	const status_undefined			= 0;
	const status_active					= 1;
	const status_locked					= 2;
	const status_closed					= 3;
	const status_outdated				= 4;
	const status_deleted				= 5;
	const status_rejected				= 6;


	public $status_array = array(
		self::status_active				=> market_status_active,
		self::status_closed				=> market_status_closed,
		self::status_deleted			=> market_status_deleted,
		self::status_locked				=> market_status_locked,
		self::status_outdated			=> market_status_outdated,
		self::status_rejected			=> market_status_rejected,
		//self::status_undefined		=> market_status_undefined
	);

	private $createTables 		= false;

  public function __construct($createTables = false) {
  	$this->createTables = $createTables;
  	parent::__construct();
  	$this->setTableName('mod_kit_market_advertisement');
  	$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
  	$this->addFieldDefinition(self::field_kit_id, "INT(11) NOT NULL DEFAULT '-1'");
  	$this->addFieldDefinition(self::field_category, "VARCHAR(255) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_ad_type, "TINYINT NOT NULL DEFAULT '".self::type_undefined."'");
  	$this->addFieldDefinition(self::field_commercial, "TINYINT NOT NULL DEFAULT '".self::commercial_undefined."'");
  	$this->addFieldDefinition(self::field_title, "VARCHAR(255) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_price, "FLOAT NOT NULL DEFAULT '0'");
  	$this->addFieldDefinition(self::field_price_type, "TINYINT NOT NULL DEFAULT '".self::price_undefined."'");
  	$this->addFieldDefinition(self::field_pictures, "TEXT NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_text, "TEXT NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_status, "TINYINT NOT NULL DEFAULT '".self::status_undefined."'");
  	$this->addFieldDefinition(self::field_start_date, "DATE NOT NULL DEFAULT '0000-00-00'");
  	$this->addFieldDefinition(self::field_end_date, "DATE NOT NULL DEFAULT '0000-00-00'");
  	$this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");
  	$this->checkFieldDefinitions();
  	// Tabelle erstellen
  	if ($this->createTables) {
  		if (!$this->sqlTableExists()) {
  			if (!$this->sqlCreateTable()) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			}
  		}
  	}
  } // __construct()

} // class dbMarketAdvertisement

class dbMarketCategories extends dbConnectLE {

	const field_id							= 'cat_id';
	const field_level_01				= 'cat_level_01';
	const field_level_02				= 'cat_level_02';
	const field_level_03				= 'cat_level_03';
	const field_level_04				= 'cat_level_04';
	const field_level_05				= 'cat_level_05';
	const field_status					= 'cat_status';
	const field_timestamp				= 'cat_timestamp';

	const status_active					= 1;
	const status_deleted				= 0;

	private $createTables 		= false;

  public function __construct($createTables = false) {
  	$this->createTables = $createTables;
  	parent::__construct();
  	$this->setTableName('mod_kit_market_categories');
  	$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
		$this->addFieldDefinition(self::field_level_01, "VARCHAR(80) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_level_02, "VARCHAR(80) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_level_03, "VARCHAR(80) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_level_04, "VARCHAR(80) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_level_05, "VARCHAR(80) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_status, "TINYINT NOT NULL DEFAULT '".self::status_active."'");
  	$this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");
  	$this->checkFieldDefinitions();
  	// Tabelle erstellen
  	if ($this->createTables) {
  		if (!$this->sqlTableExists()) {
  			if (!$this->sqlCreateTable()) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			}
  		}
  	}
  } // __construct()


} // class dbMarketCategories

class dbMarketCfg extends dbConnectLE {

	const field_id						= 'cfg_id';
	const field_name					= 'cfg_name';
	const field_type					= 'cfg_type';
	const field_value					= 'cfg_value';
	const field_label					= 'cfg_label';
	const field_description		= 'cfg_desc';
	const field_status				= 'cfg_status';
	const field_timestamp			= 'cfg_timestamp';

	const status_active				= 1;
	const status_deleted			= 0;

	const type_undefined			= 0;
	const type_array					= 7;
  const type_boolean				= 1;
  const type_email					= 2;
  const type_float					= 3;
  const type_integer				= 4;
  const type_path						= 5;
  const type_string					= 6;
  const type_url						= 8;

  public $type_array = array(
  	self::type_undefined		=> '-UNDEFINED-',
  	self::type_array				=> 'ARRAY',
  	self::type_boolean			=> 'BOOLEAN',
  	self::type_email				=> 'E-MAIL',
  	self::type_float				=> 'FLOAT',
  	self::type_integer			=> 'INTEGER',
  	self::type_path					=> 'PATH',
  	self::type_string				=> 'STRING',
  	self::type_url					=> 'URL'
  );

  private $createTables 		= false;
  private $message					= '';

  const cfgMarketExec				= 'cfgMarketExec';
  const cfgCategory_01			= 'cfgCategory_01';
  const cfgCategory_02			= 'cfgCategory_02';
  const cfgCategory_03			= 'cfgCategory_03';
  const cfgCategory_04			= 'cfgCategory_04';
  const cfgCategory_05			= 'cfgCategory_05';
  const cfgKITcategory			= 'cfgKITcategory';
  const cfgFormDlgLogin			= 'cfgFormDlgLogin';
  const cfgFormDlgAccount		= 'cfgFormDlgAccount';
  const cfgAdPublishDirect	= 'cfgAdPublishDirect';
  const cfgAdMaxImages			= 'cfgAdMaxImages';
  const cfgAdMaxImageWidth	= 'cfgAdMaXImageWidth';
  const cfgAdMaxImageHeight	= 'cfgAdMaxImageHeight';
  const cfgAdImageTypes			= 'cfgAdImageTypes';
  const cfgAdImageDir				= 'cfgAdImageDir';
  const cfgAdImagePrevWidth = 'cfgAdImagePrevWidth';
  const cfgAdListEntries		= 'cfgAdListEntries';

  public $config_array = array(
  	array('market_label_cfg_exec', self::cfgMarketExec, self::type_boolean, '1', 'market_desc_cfg_exec'),
  	array('market_label_category_01', self::cfgCategory_01, self::type_string, 'Kategorie', 'market_desc_cfg_category_01'),
  	array('market_label_category_02', self::cfgCategory_02, self::type_string, 'Unterkategorie', 'market_desc_cfg_category_02'),
  	array('market_label_category_03', self::cfgCategory_03, self::type_string, 'Art', 'market_desc_cfg_category_03'),
  	array('market_label_category_04', self::cfgCategory_04, self::type_string, 'Gruppe', 'market_desc_cfg_category_04'),
  	array('market_label_category_05', self::cfgCategory_05, self::type_string, 'Spezial', 'market_desc_cfg_category_05'),
  	array('market_label_cfg_kit_category', self::cfgKITcategory, self::type_string, 'kitMarketPlace', 'market_desc_cfg_kit_category'),
  	array('market_label_cfg_form_dlg_login', self::cfgFormDlgLogin, self::type_string, 'market_login', 'market_desc_cfg_form_dlg_login'),
  	array('market_label_cfg_form_dlg_account', self::cfgFormDlgAccount, self::type_string, 'kit_account', 'market_desc_cfg_form_dlg_account'),
  	array('market_label_cfg_ad_publish_direct', self::cfgAdPublishDirect, self::type_boolean, '0', 'market_desc_cfg_ad_publish_direct'),
  	array('market_label_cfg_ad_max_images', self::cfgAdMaxImages, self::type_integer, '3', 'market_desc_cfg_ad_max_images'),
  	array('market_label_cfg_ad_max_image_height', self::cfgAdMaxImageHeight, self::type_integer, '600', 'market_desc_cfg_ad_max_image_height'),
  	array('market_label_cfg_ad_max_image_width', self::cfgAdMaxImageWidth, self::type_integer, '800', 'market_desc_cfg_ad_max_image_width'),
  	array('market_label_cfg_ad_image_types', self::cfgAdImageTypes, self::type_array, 'jpg,png,tif,gif', 'market_desc_cfg_ad_image_types'),
  	array('market_label_cfg_ad_image_dir', self::cfgAdImageDir, self::type_string, 'kit_market', 'market_desc_cfg_ad_image_dir'),
  	array('market_label_cfg_ad_image_prev_width', self::cfgAdImagePrevWidth, self::type_integer, '200', 'market_desc_cfg_ad_image_prev_width'),
  	array('market_label_cfg_ad_list_entries', self::cfgAdListEntries, self::type_integer, '20', 'market_desc_cfg_ad_list_entries')
  );


  public function __construct($createTables = false) {
  	$this->createTables = $createTables;
  	parent::__construct();
  	$this->setTableName('mod_kit_market_config');
  	$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
  	$this->addFieldDefinition(self::field_name, "VARCHAR(32) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_type, "TINYINT UNSIGNED NOT NULL DEFAULT '".self::type_undefined."'");
  	$this->addFieldDefinition(self::field_value, "VARCHAR(255) NOT NULL DEFAULT ''", false, false, true);
  	$this->addFieldDefinition(self::field_label, "VARCHAR(64) NOT NULL DEFAULT 'ed_str_undefined'");
  	$this->addFieldDefinition(self::field_description, "VARCHAR(255) NOT NULL DEFAULT 'ed_str_undefined'");
  	$this->addFieldDefinition(self::field_status, "TINYINT UNSIGNED NOT NULL DEFAULT '".self::status_active."'");
  	$this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");
  	$this->setIndexFields(array(self::field_name));
  	$this->setAllowedHTMLtags('<a><abbr><acronym><span>');
  	$this->checkFieldDefinitions();
  	// Tabelle erstellen
  	if ($this->createTables) {
  		if (!$this->sqlTableExists()) {
  			if (!$this->sqlCreateTable()) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			}
  		}
  	}
  	// Default Werte garantieren
  	if ($this->sqlTableExists()) {
  		$this->checkConfig();
  	}
  	date_default_timezone_set(tool_cfg_time_zone);
  } // __construct()

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
   * Aktualisiert den Wert $new_value des Datensatz $name
   *
   * @param $new_value STR - Wert, der uebernommen werden soll
   * @param $id INT - ID des Datensatz, dessen Wert aktualisiert werden soll
   *
   * @return BOOL Ergebnis
   *
   */
  public function setValueByName($new_value, $name) {
  	$where = array();
  	$where[self::field_name] = $name;
  	$config = array();
  	if (!$this->sqlSelectRecord($where, $config)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	if (sizeof($config) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_cfg_name, $name)));
  		return false;
  	}
  	return $this->setValue($new_value, $config[0][self::field_id]);
  } // setValueByName()

  /**
   * Haengt einen Slash an das Ende des uebergebenen Strings
   * wenn das letzte Zeichen noch kein Slash ist
   *
   * @param STR $path
   * @return STR
   */
  public function addSlash($path) {
  	$path = substr($path, strlen($path)-1, 1) == "/" ? $path : $path."/";
  	return $path;
  }

  /**
   * Wandelt einen String in einen Float Wert um.
   * Geht davon aus, dass Dezimalzahlen mit ',' und nicht mit '.'
   * eingegeben wurden.
   *
   * @param STR $string
   * @return FLOAT
   */
  public function str2float($string) {
  	$string = str_replace('.', '', $string);
		$string = str_replace(',', '.', $string);
		$float = floatval($string);
		return $float;
  }

  public function str2int($string) {
  	$string = str_replace('.', '', $string);
		$string = str_replace(',', '.', $string);
		$int = intval($string);
		return $int;
  }

	/**
	 * Ueberprueft die uebergebene E-Mail Adresse auf logische Gueltigkeit
	 *
	 * @param STR $email
	 * @return BOOL
	 */
	public function validateEMail($email) {
		//if(eregi("^([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}$", $email)) {
		// PHP 5.3 compatibility - eregi is deprecated
		if(preg_match("/^([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}$/i", $email)) {
			return true; }
		else {
			return false; }
	}

  /**
   * Aktualisiert den Wert $new_value des Datensatz $id
   *
   * @param $new_value STR - Wert, der uebernommen werden soll
   * @param $id INT - ID des Datensatz, dessen Wert aktualisiert werden soll
   *
   * @return BOOL Ergebnis
   */
  public function setValue($new_value, $id) {
  	$value = '';
  	$where = array();
  	$where[self::field_id] = $id;
  	$config = array();
  	if (!$this->sqlSelectRecord($where, $config)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	if (sizeof($config) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_cfg_id, $id)));
  		return false;
  	}
  	$config = $config[0];
  	switch ($config[self::field_type]):
  	case self::type_array:
  		// Funktion geht davon aus, dass $value als STR uebergeben wird!!!
  		$worker = explode(",", $new_value);
  		$data = array();
  		foreach ($worker as $item) {
  			$data[] = trim($item);
  		};
  		$value = implode(",", $data);
  		break;
  	case self::type_boolean:
  		$value = (bool) $new_value;
  		$value = (int) $value;
  		break;
  	case self::type_email:
  		if ($this->validateEMail($new_value)) {
  			$value = trim($new_value);
  		}
  		else {
  			$this->setMessage(sprintf(tool_msg_invalid_email, $new_value));
  			return false;
  		}
  		break;
  	case self::type_float:
  		$value = $this->str2float($new_value);
  		break;
  	case self::type_integer:
  		$value = $this->str2int($new_value);
  		break;
  	case self::type_url:
  	case self::type_path:
  		$value = $this->addSlash(trim($new_value));
  		break;
  	case self::type_string:
  		$value = (string) trim($new_value);
  		// Hochkommas demaskieren
  		$value = str_replace('&quot;', '"', $value);
  		break;
  	endswitch;
  	unset($config[self::field_id]);
  	$config[self::field_value] = (string) $value;
  	if (!$this->sqlUpdateRecord($config, $where)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	return true;
  } // setValue()

  /**
   * Gibt den angeforderten Wert zurueck
   *
   * @param $name - Bezeichner
   *
   * @return WERT entsprechend des TYP
   */
  public function getValue($name) {
  	$result = '';
  	$where = array();
  	$where[self::field_name] = $name;
  	$config = array();
  	if (!$this->sqlSelectRecord($where, $config)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	if (sizeof($config) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_cfg_name, $name)));
  		return false;
  	}
  	$config = $config[0];
  	switch ($config[self::field_type]):
  	case self::type_array:
  		$result = explode(",", $config[self::field_value]);
  		break;
  	case self::type_boolean:
  		$result = (bool) $config[self::field_value];
  		break;
  	case self::type_email:
  	case self::type_path:
  	case self::type_string:
  	case self::type_url:
  		$result = (string) utf8_decode($config[self::field_value]);
  		break;
  	case self::type_float:
  		$result = (float) $config[self::field_value];
  		break;
  	case self::type_integer:
  		$result = (integer) $config[self::field_value];
  		break;
  	default:
  		$result = utf8_decode($config[self::field_value]);
  		break;
  	endswitch;
  	return $result;
  } // getValue()

  public function checkConfig() {
  	foreach ($this->config_array as $item) {
  		$where = array();
  		$where[self::field_name] = $item[1];
  		$check = array();
  		if (!$this->sqlSelectRecord($where, $check)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			return false;
  		}
  		if (sizeof($check) < 1) {
  			// Eintrag existiert nicht
  			$data = array();
  			$data[self::field_label] = $item[0];
  			$data[self::field_name] = $item[1];
  			$data[self::field_type] = $item[2];
  			$data[self::field_value] = $item[3];
  			$data[self::field_description] = $item[4];
  			if (!$this->sqlInsertRecord($data)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  				return false;
  			}
  		}
  	}
  	return true;
  }

} // class dbMarketCfg


?>