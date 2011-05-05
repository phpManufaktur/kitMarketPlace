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

// Deutsche Modulbeschreibung
$module_description 	= 'kitMarketPlace - der Marktplatz für KeepInTouch (KIT)';
// name of the person(s) who translated and edited this language file
$module_translation_by = 'Ralf Hertsch (phpManufaktur)';

define('market_commercial_yes',								'Gewerblich');
define('market_commercial_no',								'Privat');
define('market_commercial_undefined',					'- nicht festgelegt -');

define('market_desc_cfg_category_01',					'Legen Sie die Kategorie der Ebene <b>01</b> fest.');
define('market_desc_cfg_category_02',					'Legen Sie die Kategorie der Ebene <b>02</b> fest.');
define('market_desc_cfg_category_03',					'Legen Sie die Kategorie der Ebene <b>03</b> fest.');
define('market_desc_cfg_category_04',					'Legen Sie die Kategorie der Ebene <b>04</b> fest.');
define('market_desc_cfg_category_05',					'Legen Sie die Kategorie der Ebene <b>05</b> fest.');
define('market_desc_cfg_exec',								'Legen Sie fest, ob kitMarketPlace ausgeführt wird oder nicht (1=JA, 0=Nein)');

define('market_head_categories',							'Kategorien bearbeiten');

define('market_intro_categories',							'Bearbeiten Sie die Kategorien für kitMarketPlace odeer fügen Sie neue Kategorien hinzu.');

define('market_label_add_category',						'Kategorie hinzufügen');
define('market_label_category_01',						'Kategorie, Level 01');
define('market_label_category_02',						'Kategorie, Level 02');
define('market_label_category_03',						'Kategorie, Level 03');
define('market_label_category_04',						'Kategorie, Level 04');
define('market_label_category_05',						'Kategorie, Level 05');
define('market_label_cfg_exec',								'kitMarketPlace ausführen');

define('market_msg_category_deleted',					'<p>Die <i>%s</i> <b>%s</b> wurde gelöscht.</p>'); 
define('market_msg_category_inserted',				'<p>Die <i>%s</i> <b>%s</b> wurde hinzugefügt.</p>');
define('market_msg_category_updated',					'<p>Die <i>%s</i> <b>%s</b> wurde in <b>%s</b> geändert.</p>');

define('market_price_fixed',									'Festpreis');
define('market_price_asking',									'Verhandlungsbasis (VB)');
define('market_price_give_away',							'zu verschenken');
define('market_price_undefined',							'- nicht festgelegt -');

define('market_status_active',								'Aktiv');
define('market_status_closed',								'Abgeschlossen');
define('market_status_deleted',								'Gelöscht');
define('market_status_locked',								'Gesperrt');
define('market_status_outdated',							'Abgelaufen');
define('market_status_rejected',							'Abgelehnt');
define('market_status_undefined',							'- nicht festgelegt -');

define('market_tab_about',										'?');
define('market_tab_advertisement',						'Angebot bearbeiten');
define('market_tab_categories',								'Kategorien');
define('market_tab_config',										'Einstellungen');

define('market_text_add_new_category',				'- neue Kategorie -');

define('market_th_delete',										'ENTF');

define('market_type_offer',										'Ich biete');
define('market_type_search',									'Ich suche');
define('market_type_undefined',								'- nicht festgelegt -');

?>