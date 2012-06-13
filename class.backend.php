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

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {    
    if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php'); 
} else {
    $oneback = "../";
    $root = $oneback;
    $level = 1;
    while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
        $root .= $oneback;
        $level += 1;
    }
    if (file_exists($root.'/framework/class.secure.php')) { 
        include($root.'/framework/class.secure.php'); 
    } else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}
// end include class.secure.php

require_once (WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/initialize.php');

class marketBackend {
    
    const request_action = 'act';
    const request_items = 'its';
    const request_category_edit = 'cate';
    const request_category_delete = 'catd';
    const request_category_add = 'cata';
    const request_category_select = 'cats';
    
    const action_about = 'abt';
    const action_categories = 'cats';
    const action_categories_check = 'catsc';
    const action_config = 'cfg';
    const action_config_check = 'cfgc';
    const action_default = 'def';
    const action_advertisement = 'ead';
    const action_list = 'lst';
    const action_list_check = 'lstc';
    
    private $tab_navigation_array = array(
    self::action_list => market_tab_overview, 
    self::action_advertisement => market_tab_advertisement, 
    self::action_categories => market_tab_categories, 
    self::action_config => market_tab_config, 
    self::action_about => market_tab_about);
    
    private $page_link = '';
    private $img_url = '';
    private $template_path = '';
    private $error = '';
    private $message = '';
    private $media_path = '';
    private $media_url = '';

    public function __construct() {
        global $dbMarketCfg;
        $this->page_link = ADMIN_URL . '/admintools/tool.php?tool=kit_market';
        $this->template_path = WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/htt/';
        $this->img_url = WB_URL . '/modules/' . basename(dirname(__FILE__)) . '/images/';
        date_default_timezone_set(tool_cfg_time_zone);
        $this->media_path = WB_PATH . MEDIA_DIRECTORY . '/' . $dbMarketCfg->getValue(dbMarketCfg::cfgAdImageDir) . '/';
        $this->media_url = str_replace(WB_PATH, WB_URL, $this->media_path);
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
        return (bool) ! empty($this->error);
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
        return (bool) ! empty($this->message);
    } // isMessage

    
    /**
     * Return Version of Module
     *
     * @return FLOAT
     */
    public function getVersion() {
        // read info.php into array
        $info_text = file(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/info.php');
        if ($info_text == false) {
            return - 1;
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
        return - 1;
    } // getVersion()

    
    public function getTemplate($template, $template_data) {
        global $parser;
        try {
            $result = $parser->get($this->template_path . $template, $template_data);
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
            if (! in_array($key, $html_allowed)) {
                $_REQUEST[$key] = $this->xssPrevent($value);
            }
        }
        isset($_REQUEST[self::request_action]) ? $action = $_REQUEST[self::request_action] : $action = self::action_default;
        switch ($action) :
            case self::action_advertisement:
                $this->show(self::action_advertisement, $this->dlgViewAdvertisement());
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
            case self::action_list_check:
                $this->show(self::action_list, $this->dlgListCheck());
                break;
            case self::action_list:
            default:
                $this->show(self::action_list, $this->dlgList());
                break;
        endswitch
        ;
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
            $navigation[] = array('active' => ($key == $action) ? 1 : 0, 
            'url' => sprintf('%s&%s=%s', $this->page_link, self::request_action, $key), 
            'text' => $value);
        }
        $data = array('WB_URL' => WB_URL, 'navigation' => $navigation, 
        'error' => ($this->isError()) ? 1 : 0, 
        'content' => ($this->isError()) ? $this->getError() : $content);
        echo $this->getTemplate('backend.body.htt', $data);
    } // show()

    
    /**
     * Liefert den vollstaendigen Pfad auf die angegebene Kategorie
     * 
     * @param INT $category_id
     * @return STR Kategorie BOOL FALSE bei Fehler
     */
    private function getCategoryString($category_id) {
        global $dbMarketCats;
        
        $cat = array();
        $where = array(dbMarketCategories::field_id => $category_id);
        $category = array();
        if (! $dbMarketCats->sqlSelectRecord($where, $category)) {
            $this->setError(sprintf('[%s - %] %s', __METHOD__, __LINE__, $dbMarketCats->getError()));
            return false;
        }
        if (count($category) < 1) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $category_id)));
            return false;
        }
        $category = $category[0];
        for ($i = 1; $i < 6; $i ++) {
            if (! empty($category[sprintf('cat_level_%02d', $i)])) $cat[] = $category[sprintf('cat_level_%02d', $i)];
        }
        $cat_str = implode(' > ', $cat);
        return $cat_str;
    } // getCategoryString()

    
    public function dlgList() {
        global $dbMarketAd;
        global $dbMarketCats;
        
        $SQL = sprintf("SELECT * FROM %s WHERE %s!='%s' ORDER BY %s DESC", $dbMarketAd->getTableName(), dbMarketAdvertisement::field_status, dbMarketAdvertisement::status_deleted, dbMarketAdvertisement::field_timestamp);
        $ads = array();
        if (! $dbMarketAd->sqlExec($SQL, $ads)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbMarketAd->getError()));
            return false;
        }
        $list = array();
        $items = array();
        foreach ($ads as $ad) {
            // Kategorien
            $cat_str = $this->getCategoryString($ad[dbMarketAdvertisement::field_category]);
            
            $status = array();
            foreach ($dbMarketAd->status_array as $value => $text) {
                $status[] = array('value' => $value, 'text' => $text, 
                'selected' => ($ad[dbMarketAdvertisement::field_status] == $value) ? 1 : 0);
            }
            $list[] = array(
            'id' => array('value' => $ad[dbMarketAdvertisement::field_id], 
            'link' => sprintf('%s&%s=%s&%s=%s', $this->page_link, self::request_action, self::action_advertisement, dbMarketAdvertisement::field_id, $ad[dbMarketAdvertisement::field_id])), 
            'timestamp' => array(
            'text' => date(tool_cfg_datetime_str, strtotime($ad[dbMarketAdvertisement::field_timestamp])), 
            'unix' => strtotime($ad[dbMarketAdvertisement::field_timestamp])), 
            'type' => array(
            'offer' => ($ad[dbMarketAdvertisement::field_ad_type] == dbMarketAdvertisement::type_offer) ? 1 : 0), 
            'title' => array('text' => $ad[dbMarketAdvertisement::field_title]), 
            'category' => array('text' => $cat_str), 
            'status' => array('values' => $status, 
            'text' => $dbMarketAd->status_array[$ad[dbMarketAdvertisement::field_status]], 
            'name' => sprintf('%s_%s', dbMarketAdvertisement::field_status, $ad[dbMarketAdvertisement::field_id])));
            $items[] = $ad[dbMarketAdvertisement::field_id];
        }
        $form = array('name' => 'market_list', 
        'action' => array('link' => $this->page_link, 
        'name' => self::request_action, 'value' => self::action_list_check), 
        'items' => array('name' => self::request_items, 
        'value' => implode(",", $items)), 
        'title' => market_head_advertisement_overview, 
        'is_message' => $this->isMessage() ? 1 : 0, 
        'message' => $this->isMessage() ? $this->getMessage() : market_intro_advertisement_overview, 
        'header' => array('id' => market_th_id, 'kit_id' => market_th_kit_id, 
        'category' => market_th_category, 'type' => market_th_type, 
        'commercial' => market_th_commercial, 'title' => market_th_title, 
        'price' => market_th_price, 'price_type' => market_th_price_type, 
        'pictures' => market_th_pictures, 'text' => market_th_text, 
        'status' => market_th_status, 'start_date' => market_th_start_date, 
        'end_date' => market_th_end_date, 'timestamp' => market_th_timestamp), 
        'btn' => array('ok' => tool_btn_ok));
        $data = array('form' => $form, 'advertisement' => $list);
        return $this->getTemplate('backend.advertisement.list.htt', $data);
    } // dlgList()

    
    public function dlgListCheck() {
        global $dbMarketAd;
        global $dbMarketCats;
        
        if (! isset($_REQUEST[self::request_items])) {
            $this->setError(sprintf('[%s - %] %s', __METHOD__, __LINE__, sprintf(tool_error_request_missing, self::request_items)));
            return false;
        }
        if (empty($_REQUEST[self::request_items])) return $this->dlgList();
        
        $SQL = sprintf("SELECT * FROM %s WHERE %s IN (%s)", $dbMarketAd->getTableName(), dbMarketAdvertisement::field_id, $_REQUEST[self::request_items]);
        $ads = array();
        if (! $dbMarketAd->sqlExec($SQL, $ads)) {
            $this->setError(sprintf('[%s - %] %s', __METHOD__, __LINE__, $dbMarketAd->getError()));
            return false;
        }
        $message = '';
        foreach ($ads as $ad) {
            if (isset($_REQUEST[sprintf('%s_%s', dbMarketAdvertisement::field_status, $ad[dbMarketAdvertisement::field_id])])) {
                $new_status = $_REQUEST[sprintf('%s_%s', dbMarketAdvertisement::field_status, $ad[dbMarketAdvertisement::field_id])];
                if ($new_status != $ad[dbMarketAdvertisement::field_status]) {
                    $old_status = $ad[dbMarketAdvertisement::field_status];
                    $where = array(
                    dbMarketAdvertisement::field_id => $ad[dbMarketAdvertisement::field_id]);
                    $data = array(
                    dbMarketAdvertisement::field_status => $new_status);
                    if (! $dbMarketAd->sqlUpdateRecord($data, $where)) {
                        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbMarketAd->getError()));
                        return false;
                    }
                    $message .= sprintf(market_msg_ad_status_changed, $ad[dbMarketAdvertisement::field_id], $dbMarketAd->status_array[$new_status]);
                    /**
                     * @todo Statusmeldungen verschicken?
                     */
                    if (! $this->sendStatusMail($ad[dbMarketAdvertisement::field_kit_id], $ad[dbMarketAdvertisement::field_id], $old_status)) {
                        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                        return false;
                    }
                    $message .= sprintf(market_msg_ad_status_mail_send, $ad[dbMarketAdvertisement::field_id]);
                }
            }
        }
        $this->setMessage($message);
        return $this->dlgList();
    } // dlgListCheck()

    
    /**
     * Versendet eine E-Mail an den Kunden und teilt die Aenderung des STATUS mit
     * 
     * @param INT $contact_id
     * @param INT $advertisement_id
     * @param INT $old_status
     * @return BOOL
     */
    public function sendStatusMail($contact_id, $advertisement_id, $old_status) {
        global $dbMarketAd;
        global $kitContactInterface;
        
        // Kontaktdaten
        $contact = array();
        if (! $kitContactInterface->getContact($contact_id, $contact)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
            return false;
        }
        
        // Kleinanzeige
        $where = array(
        dbMarketAdvertisement::field_id => $advertisement_id);
        $ad = array();
        if (! $dbMarketAd->sqlSelectRecord($where, $ad)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbMarketAd->getError()));
            return false;
        }
        if (count($ad) < 1) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $advertisement_id)));
            return false;
        }
        $ad = $ad[0];
        
        $advertisement = array('id' => $ad[dbMarketAdvertisement::field_id], 
        'kit_id' => $ad[dbMarketAdvertisement::field_kit_id], 
        'category' => array(
        'id' => $ad[dbMarketAdvertisement::field_category], 
        'text' => $this->getCategoryString($ad[dbMarketAdvertisement::field_category])), 
        'type' => array('value' => $ad[dbMarketAdvertisement::field_ad_type], 
        'text' => $dbMarketAd->type_array[$ad[dbMarketAdvertisement::field_ad_type]]), 
        'commercial' => array(
        'value' => $ad[dbMarketAdvertisement::field_commercial], 
        'text' => $dbMarketAd->commercial_array[$ad[dbMarketAdvertisement::field_commercial]]), 
        'title' => $ad[dbMarketAdvertisement::field_title], 
        'price' => array('value' => $ad[dbMarketAdvertisement::field_price], 
        'text' => number_format($ad[dbMarketAdvertisement::field_price], 2, tool_cfg_decimal_separator, tool_cfg_thousand_separator)), 
        'price_type' => array(
        'value' => $ad[dbMarketAdvertisement::field_price_type], 
        'text' => $dbMarketAd->price_array[$ad[dbMarketAdvertisement::field_price_type]]), 
        'text' => $ad[dbMarketAdvertisement::field_text], 
        'status' => array('value' => $ad[dbMarketAdvertisement::field_status], 
        'text' => $dbMarketAd->status_array[$ad[dbMarketAdvertisement::field_status]]), 
        'status_old' => array('value' => $old_status, 
        'text' => $dbMarketAd->status_array[$old_status]), 
        'timestamp' => array(
        'value' => $ad[dbMarketAdvertisement::field_timestamp], 
        'text' => date(tool_cfg_datetime_str, strtotime($ad[dbMarketAdvertisement::field_timestamp])), 
        'unix' => strtotime($ad[dbMarketAdvertisement::field_timestamp])));
        $data = array('contact' => $contact, 'advertisement' => $advertisement);
        $status_mail = $this->getTemplate('backend.mail.advertisement.status.changed', $data);
        
        $mail = new kitMail();
        if (! $mail->mail(market_mail_subject_status_changed, $status_mail, SERVER_EMAIL, SERVER_EMAIL, array(
        $contact[kitContactInterface::kit_email] => $contact[kitContactInterface::kit_email]), false)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_mail_sending, $contact[kitContactInterface::kit_email])));
            return false;
        }
        
        return true;
    } // sendStatusMail()

    
    /**
     * Information ueber kitMarketPlace
     * 
     * @return STR dialog
     */
    public function dlgAbout() {
        $data = array('version' => sprintf('%01.2f', $this->getVersion()), 
        'img_url' => $this->img_url . '/kit_market_logo_505x386.jpg', 
        'release_notes' => file_get_contents(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/info.txt'));
        return $this->getTemplate('backend.about.htt', $data);
    } // dlgAbout()

    
    /**
     * Dialog zur Konfiguration und Anpassung von kitMarketPlace
     * 
     * @return STR dialog
     */
    public function dlgConfig() {
        global $dbMarketCfg;
        $SQL = sprintf("SELECT * FROM %s WHERE NOT %s='%s' ORDER BY %s", $dbMarketCfg->getTableName(), dbMarketCfg::field_status, dbMarketCfg::status_deleted, dbMarketCfg::field_name);
        $config = array();
        if (! $dbMarketCfg->sqlExec($SQL, $config)) {
            $this->setError($dbMarketCfg->getError());
            return false;
        }
        $count = array();
        $header = array('identifier' => tool_header_cfg_identifier, 
        'value' => tool_header_cfg_value, 
        'description' => tool_header_cfg_description);
        
        $items = array();
        // bestehende Eintraege auflisten
        foreach ($config as $entry) {
            $id = $entry[dbMarketCfg::field_id];
            $count[] = $id;
            $value = (isset($_REQUEST[dbMarketCfg::field_value . '_' . $id])) ? $_REQUEST[dbMarketCfg::field_value . '_' . $id] : $entry[dbMarketCfg::field_value];
            $value = str_replace('"', '&quot;', stripslashes($value));
            $items[] = array('id' => $id, 
            'identifier' => constant($entry[dbMarketCfg::field_label]), 
            'value' => $value, 
            'name' => sprintf('%s_%s', dbMarketCfg::field_value, $id), 
            'description' => constant($entry[dbMarketCfg::field_description]));
        }
        $data = array('form_name' => 'market_cfg', 
        'form_action' => $this->page_link, 
        'action_name' => self::request_action, 
        'action_value' => self::action_config_check, 
        'items_name' => self::request_items, 
        'items_value' => implode(",", $count), 'head' => tool_header_cfg, 
        'intro' => $this->isMessage() ? $this->getMessage() : sprintf(tool_intro_cfg, 'kitMarketPlace'), 
        'is_message' => $this->isMessage() ? 1 : 0, 'items' => $items, 
        'btn_ok' => tool_btn_ok, 'btn_abort' => tool_btn_abort, 
        'abort_location' => $this->page_link, 'header' => $header);
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
        if ((isset($_REQUEST[self::request_items])) && (! empty($_REQUEST[self::request_items]))) {
            $ids = explode(",", $_REQUEST[self::request_items]);
            foreach ($ids as $id) {
                if (isset($_REQUEST[dbMarketCfg::field_value . '_' . $id])) {
                    $value = $_REQUEST[dbMarketCfg::field_value . '_' . $id];
                    $where = array();
                    $where[dbMarketCfg::field_id] = $id;
                    $config = array();
                    if (! $dbMarketCfg->sqlSelectRecord($where, $config)) {
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
                        if (! $dbMarketCfg->setValue($value, $id) && $dbMarketCfg->isError()) {
                            $this->setError($dbMarketCfg->getError());
                            return false;
                        } elseif ($dbMarketCfg->isMessage()) {
                            $message .= $dbMarketCfg->getMessage();
                        } else {
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

    
    /**
     * Dialog zum Erstellen und Bearbeiten von Kategorien
     * 
     * @return STR dialog
     */
    public function dlgCategories() {
        global $dbMarketCats;
        global $dbMarketCfg;
        
        $SQL = sprintf("SELECT * FROM %s WHERE %s='%s' ORDER BY %s, %s, %s, %s, %s ASC", $dbMarketCats->getTableName(), dbMarketCategories::field_status, dbMarketCategories::status_active, dbMarketCategories::field_level_01, dbMarketCategories::field_level_02, dbMarketCategories::field_level_03, dbMarketCategories::field_level_04, dbMarketCategories::field_level_05);
        $categories = array();
        if (! $dbMarketCats->sqlExec($SQL, $categories)) {
            $this->setError($dbMarketCats->getError());
            return false;
        }
        
        /**
         * Kategorien durchlaufen, Editierfelder fuer die Ebenen setzen, Moeglichkeit zum Loeschen einfuegen 
         */
        $items = array();
        $all_items = array();
        $u = 1;
        $check_array = array();
        $cat_list = array();
        $cat_list[] = array('text' => market_text_add_new_category, 'id' => - 1);
        foreach ($categories as $category) {
            $items[$u]['id'] = $category[dbMarketCategories::field_id];
            $all_items[] = $category[dbMarketCategories::field_id];
            $path = array();
            for ($i = 1; $i < 6; $i ++) {
                $items[$u]['value'][$i] = $category[sprintf('cat_level_%02d', $i)];
                if ((! empty($category[sprintf('cat_level_%02d', $i)]) && (isset($category[sprintf('cat_level_%02d', $i + 1)]) && empty($category[sprintf('cat_level_%02d', $i + 1)]))) || (($i == 5) && (! empty($category[sprintf('cat_level_%02d', $i)])))) {
                    $items[$u]['edit'][$i] = 1;
                } else {
                    $items[$u]['edit'][$i] = 0;
                }
                $items[$u]['request']['edit'][$i] = sprintf('%s_%d', self::request_category_edit, $category[dbMarketCategories::field_id]);
                if (! empty($category[sprintf('cat_level_%02d', $i)]) && ($i < 5)) $path[] = $category[sprintf('cat_level_%02d', $i)];
            }
            $items[$u]['request']['delete'] = self::request_category_delete;
            $path_str = implode(' > ', $path);
            if (! in_array($path_str, $check_array)) {
                $check_array[] = $path_str;
                $cat_list[] = array('text' => $path_str, 
                'id' => $category[dbMarketCategories::field_id]);
            }
            $u ++;
        }
        
        $header = array();
        for ($i = 1; $i < 6; $i ++) {
            $header['level'][$i] = $dbMarketCfg->getValue(sprintf('cfgCategory_%02d', $i));
        }
        $header['delete'] = market_th_delete;
        
        $data = array('form_name' => 'form_cats', 
        'form_action' => $this->page_link, 
        'action_name' => self::request_action, 
        'action_value' => self::action_categories_check, 
        'items_name' => self::request_items, 
        'items_value' => implode(',', $all_items), 
        'btn_abort' => tool_btn_abort, 'abort_location' => $this->page_link, 
        'btn_ok' => tool_btn_ok, 'categories' => $items, 'header' => $header, 
        'head' => market_head_categories, 
        'is_message' => $this->isMessage() ? 1 : 0, 
        'intro' => $this->isMessage() ? $this->getMessage() : market_intro_categories, 
        'select_cat_name' => self::request_category_select, 
        'select_cat_value' => $cat_list, 
        'add_cat_label' => market_label_add_category, 
        'add_cat_name' => self::request_category_add);
        return $this->getTemplate('backend.categories.htt', $data);
    } // dlgCategories()

    
    /**
     * Ueberprueft Aenderungen die im Dialog dlgCategories() vorgenommen wurden
     * und aktualisiert den Datensatz oder legt einen neuen an.
     * 
     * @return STR dlgCategories()
     */
    public function checkCategories() {
        global $dbMarketCats;
        global $dbMarketCfg;
        
        $message = '';
        if (! isset($_REQUEST[self::request_items])) {
            $this->setError(sprintf(tool_error_request_missing, self::request_items));
            return false;
        }
        if (! empty($_REQUEST[self::request_items])) {
            $SQL = sprintf("SELECT * FROM %s WHERE %s IN (%s)", $dbMarketCats->getTableName(), dbMarketCategories::field_id, $_REQUEST[self::request_items]);
            $categories = array();
            if (! $dbMarketCats->sqlExec($SQL, $categories)) {
                $this->setError($dbMarketCats->getError());
                return false;
            }
        } else {
            $categories = array();
        }
        
        // Kategorie hinzufuegen?
        if (isset($_REQUEST[self::request_category_add]) && ! empty($_REQUEST[self::request_category_add])) {
            $add_cat = $_REQUEST[self::request_category_add];
            $id = - 1;
            if ($_REQUEST[self::request_category_select] == - 1) {
                // neue Kategorie der obersten Ebene
                $data = array(
                dbMarketCategories::field_level_01 => $add_cat);
                if (! $dbMarketCats->sqlInsertRecord($data, $id)) {
                    $this->setError($dbMarketCats->getError());
                    return false;
                }
                $message .= sprintf(market_msg_category_inserted, $dbMarketCfg->getValue(dbMarketCfg::cfgCategory_01), $add_cat);
            } else {
                // untergeordnete Kategorie hinzufuegen
                $where = array(
                dbMarketCategories::field_id => $_REQUEST[self::request_category_select]);
                $data = array();
                if (! $dbMarketCats->sqlSelectRecord($where, $data)) {
                    $this->setError($dbMarketCats->getError());
                    return false;
                }
                if (count($data) < 1) {
                    $this->setError(sprintf(tool_error_id_invalid, $_REQUEST[self::request_category_select]));
                    return false;
                }
                $data = $data[0];
                for ($i = 1; $i < 6; $i ++) {
                    if (empty($data[sprintf('cat_level_%02d', $i)])) {
                        $data[sprintf('cat_level_%02d', $i)] = $add_cat;
                        break;
                    }
                }
                if (! $dbMarketCats->sqlInsertRecord($data, $id)) {
                    $this->setError($dbMarketCats->getError());
                    return false;
                }
                $message .= sprintf(market_msg_category_inserted, $dbMarketCfg->getValue(sprintf('cfgCategory_%02d', $i)), $add_cat);
            }
        }
        
        // sollen Kategorien geloescht werden?
        $delete_categories = (isset($_REQUEST[self::request_category_delete])) ? $_REQUEST[self::request_category_delete] : array();
        
        foreach ($categories as $category) {
            if (isset($_REQUEST[sprintf('%s_%d', self::request_category_edit, $category[dbMarketCategories::field_id])]) && ! empty($_REQUEST[sprintf('%s_%d', self::request_category_edit, $category[dbMarketCategories::field_id])])) {
                $check = '';
                $level = 0;
                for ($i = 1; $i < 6; $i ++) {
                    if (! empty($category[sprintf('cat_level_%02d', $i)])) {
                        $check = $category[sprintf('cat_level_%02d', $i)];
                        $level = $i;
                    } else {
                        break;
                    }
                }
                if ($check !== $_REQUEST[sprintf('%s_%d', self::request_category_edit, $category[dbMarketCategories::field_id])]) {
                    // Datensatz bzw. Datensaetze aktualisieren
                    $where = array(
                    sprintf('cat_level_%02d', $level) => $check, 
                    dbMarketCategories::field_status => dbMarketCategories::status_active);
                    $data = array(
                    sprintf('cat_level_%02d', $level) => $_REQUEST[sprintf('%s_%d', self::request_category_edit, $category[dbMarketCategories::field_id])]);
                    if (! $dbMarketCats->sqlUpdateRecord($data, $where)) {
                        $this->setError($dbMarketCats->getError());
                        return false;
                    }
                    $message .= sprintf(sprintf(market_msg_category_updated, $dbMarketCfg->getValue(sprintf('cfgCategory_%02d', $level)), $check, $_REQUEST[sprintf('%s_%d', self::request_category_edit, $category[dbMarketCategories::field_id])]));
                }
            }
            if (in_array($category[dbMarketCategories::field_id], $delete_categories)) {
                // Datensatz bzw. Datensaetze loeschen
                $check = '';
                $level = 0;
                // Ebene ermitteln
                for ($i = 1; $i < 6; $i ++) {
                    if (! empty($category[sprintf('cat_level_%02d', $i)])) {
                        $check = $category[sprintf('cat_level_%02d', $i)];
                        $level = $i;
                    } else {
                        break;
                    }
                }
                $where = array(sprintf('cat_level_%02d', $level) => $check, 
                dbMarketCategories::field_status => dbMarketCategories::status_active);
                $data = array(
                dbMarketCategories::field_status => dbMarketCategories::status_deleted);
                if (! $dbMarketCats->sqlUpdateRecord($data, $where)) {
                    $this->setError($dbMarketCats->getError());
                    return false;
                }
                $message .= sprintf(market_msg_category_deleted, $dbMarketCfg->getValue(sprintf('cfgCategory_%02d', $level)), $check);
            }
        }
        
        // Benachrichtigungen setzen und Dialog wieder anzeigen
        $this->setMessage($message);
        return $this->dlgCategories();
    } // checkCategories()

    
    public function dlgViewAdvertisement() {
        global $dbMarketAd;
        global $kitContactInterface;
        global $kitLibrary;
        global $dbMarketCfg;
        
        if (! isset($_REQUEST[dbMarketAdvertisement::field_id])) {
            $data = array(
            'form' => array('title' => market_head_advertisement, 
            'is_message' => 1, 'message' => market_msg_ad_select_ad));
            return $this->getTemplate('backend.message.htt', $data);
        }
        
        $where = array(
        dbMarketAdvertisement::field_id => $_REQUEST[dbMarketAdvertisement::field_id]);
        $ad = array();
        if (! $dbMarketAd->sqlSelectRecord($where, $ad)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbMarketAd->getError()));
            return false;
        }
        if (count($ad) < 1) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $_REQUEST[dbMarketAdvertisement::field_id])));
            return false;
        }
        $ad = $ad[0];
        
        $contact = array();
        if (! $kitContactInterface->getContact($ad[dbMarketAdvertisement::field_kit_id], $contact)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
            return false;
        }
        $contact['kit_id'] = $ad[dbMarketAdvertisement::field_kit_id];
        
        $form = array(
        'kit_link' => sprintf('%s/admintools/tool.php?tool=kit&act=con&%s=%s', ADMIN_URL, dbKITcontact::field_id, $ad[dbMarketAdvertisement::field_kit_id]));
        
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
        $pictures = (! empty($ad[dbMarketAdvertisement::field_pictures])) ? explode(',', $ad[dbMarketAdvertisement::field_pictures]) : array();
        
        $upl_path = $this->media_path . $ad[dbMarketAdvertisement::field_kit_id] . '/' . $ad[dbMarketAdvertisement::field_id] . '/';
        $upl_url = str_replace(WB_PATH, WB_URL, $upl_path);
        $prev_path = $upl_path . $prev_width . '/';
        $prev_url = str_replace(WB_PATH, WB_URL, $prev_path);
        
        $tmp_dir = $this->media_path . $ad[dbMarketAdvertisement::field_kit_id] . '/tmp/';
        
        foreach ($pictures as $picture) {
            if (! file_exists($upl_path . $picture)) {
                if (file_exists($tmp_dir . $picture)) {
                    // Datei befindet sich noch im TEMP Verzeichnis, verschieben...
                    if (! file_exists($upl_path)) {
                        // Verzeichnis erstellen
                        if (! mkdir($upl_path, 0755, true)) {
                            $this->setError(sprintf('[%s - %s] %s', sprintf(tool_error_mkdir, $upl_path)));
                            return false;
                        }
                    }
                    if (! rename($tmp_dir . $picture, $upl_path . $picture)) {
                        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_move_file, $tmp_dir . $picture, $upl_path . $picture)));
                        return false;
                    }
                } else {
                    // Datei nicht gefunden
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_missing_file, $upl_path . $picture)));
                    return false;
                }
            }
            // Dateiinformationen
            $path_parts = pathinfo($upl_path . $picture);
            // Breite und Hoehe festhalten
            list ($full_width, $full_height) = getimagesize($upl_path . $picture);
            // Vorschaubild pruefen
            if (! file_exists($prev_path . $picture)) {
                $factor = $prev_width / $full_width;
                $new_height = ceil($full_height * $factor);
                $new_width = ceil($full_width * $factor);
                if (false === ($new_file = $this->createTweakedFile($path_parts['filename'], $path_parts['extension'], $upl_path . $picture, $new_width, $new_height, $full_width, $full_height))) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_tweaking_file, $upl_path . $picture)));
                    return false;
                }
                if (! file_exists($prev_path)) {
                    if (! mkdir($prev_path, 0755, true)) {
                        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_mkdir, $prev_path)));
                        return false;
                    }
                }
                if (! rename($new_file, $prev_path . $picture)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_move_file, $new_file, $prev_path . $picture)));
                    return false;
                }
            }
            list ($prev_width, $prev_height) = getimagesize($prev_path . $picture);
            $images[] = array(
            'fullsize' => array('url' => $upl_url . $picture, 
            'width' => $full_width, 'height' => $full_height), 
            'preview' => array('url' => $prev_url . $picture, 
            'width' => $prev_width, 'height' => $prev_height));
        }
        
        $advertisement = array(
        'type' => array('label' => market_label_adv_type, 
        'text' => $dbMarketAd->type_array[$ad[dbMarketAdvertisement::field_ad_type]]), 
        'category' => array('label' => market_label_adv_category, 
        'text' => $this->getCategoryString($ad[dbMarketAdvertisement::field_category])), 
        'status' => array('label' => market_label_adv_status, 
        'text' => $dbMarketAd->status_array[$ad[dbMarketAdvertisement::field_status]]), 
        'commercial' => array('label' => market_label_adv_commercial, 
        'text' => $dbMarketAd->commercial_array[$ad[dbMarketAdvertisement::field_commercial]]), 
        'title' => array('label' => market_label_adv_title, 
        'text' => $ad[dbMarketAdvertisement::field_text]), 
        'text' => array('label' => market_label_adv_text, 
        'text' => $ad[dbMarketAdvertisement::field_text]), 
        'price' => array('label' => market_label_adv_price, 
        'text' => number_format($ad[dbMarketAdvertisement::field_price], 2, tool_cfg_decimal_separator, tool_cfg_thousand_separator)), 
        'price_type' => array('label' => market_label_adv_price_type, 
        'text' => $dbMarketAd->price_array[$ad[dbMarketAdvertisement::field_price_type]]), 
        'image' => array('label' => market_label_adv_image_upload, 
        'name' => dbMarketAdvertisement::field_pictures, 'values' => $images, 
        'hint' => sprintf(market_hint_adv_image_size, $max_images, $max_size), 
        'max_img' => $max_images, 'max_size' => $max_size, 
        'max_width' => $max_image_width, 'max_height' => $max_image_height, 
        'file_types' => $file_types));
        
        $data = array('form' => $form, 'advertisement' => $advertisement, 
        'contact' => $contact);
        return $this->getTemplate('backend.advertisement.view.htt', $data);
    } // dlgViewAdvertisement()


} // class marketBackend


?>