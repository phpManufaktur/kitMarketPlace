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

define('market_desc_cfg_ad_image_dir',				'Unterverzeichnis im /MEDIA Ordner der für Bilder des Kleinanzeigen Markt verwendet wird.');
define('market_desc_cfg_ad_image_prev_width',	'max. Breite der Vorschaubilder für die Kleinanzeigen.');
define('market_desc_cfg_ad_image_types',			'Unterstützte Bildtypen (Dateiendungen) für die Kleinanzeigen.');
define('market_desc_cfg_ad_list_entries',			'Die maximale Anzahl der Kleinanzeigen, die auf den Übersichtsseiten angezeigt werden sollen.');
define('market_desc_cfg_ad_max_images',				'max. Anzahl der Bilder, die pro Kleinanzeige hochgeladen werden darf.');
define('market_desc_cfg_ad_max_image_height',	'max. Breite von hochgeladenen Bildern, die Bilder werden automatisch heruntergerechnet.');
define('market_desc_cfg_ad_max_image_width',	'max. Breite von hochgeladenen Bildern, die Bilder werden automatisch heruntergerechnet.');
define('market_desc_cfg_ad_publish_direct',		'Legen Sie fest, ob neue Kleinanzeigen sofort und ohne Prüfung veröffentlich werden sollen (1=JA, 0=NEIN).');
define('market_desc_cfg_category_01',					'Legen Sie die Kategorie der Ebene <b>01</b> fest.');
define('market_desc_cfg_category_02',					'Legen Sie die Kategorie der Ebene <b>02</b> fest.');
define('market_desc_cfg_category_03',					'Legen Sie die Kategorie der Ebene <b>03</b> fest.');
define('market_desc_cfg_category_04',					'Legen Sie die Kategorie der Ebene <b>04</b> fest.');
define('market_desc_cfg_category_05',					'Legen Sie die Kategorie der Ebene <b>05</b> fest.');
define('market_desc_cfg_exec',								'Legen Sie fest, ob kitMarketPlace ausgeführt wird oder nicht (1=JA, 0=Nein)');
define('market_desc_cfg_form_dlg_account',		'Der kitForm Dialog, der von kitMarketPlace für die Verwaltung der Kundendaten (Account) verwendet wird.');
define('market_desc_cfg_form_dlg_login',			'Der kitForm Dialog, der von kitMarketPlace für die Anmeldung von Benutzern angezeigt wird.'); 
define('market_desc_cfg_kit_category',				'KeepInTouch (KIT) Kategorie, der ein Nutzer zugeordnet sein muss, damit er ein Konto in kitMarketPlace einrichten kann.');

define('market_error_auth_wrong_category',		'<p>Ihr Benutzerkonto gestattet Ihnen leider keinen Zugriff auf die Verwaltung von kitMarketPlace.</p><p>Bitte wenden Sie sich an den Service, dieser kann Sie für kitMarketPlace freischalten!</p>');
define('market_error_no_categories',					'<p>Es sind keine Kategorien definier!</p>');
define('market_error_undefined',							'<p>Uuuups, da ist gerade etwas schiefgelaufen und das Programm weiß nicht, was es tun soll. Bitte informieren Sie den Support über dieses Problem!</p>');

define('market_field_id',											'ID');
define('market_field_kit_id',									'Kontakt ID');
define('market_field_category',								'Kategorie');
define('market_field_ad_type',								'Kleinanzeige, Typ');	
define('market_field_commercial',							'Gewerblich / Privat');
define('market_field_title',									'Titel');
define('market_field_price',									'Preis');
define('market_field_price_type',							'Preis, Typ');
define('market_field_pictures',								'Bilder');
define('market_field_text',										'Text');
define('market_field_status',									'Status');
define('market_field_start_date',							'Startdatum');
define('market_field_end_date',								'Enddatum');
define('market_field_timestamp',							'Zeitstempel'); 		

define('market_head_categories',							'Kategorien bearbeiten');
define('market_head_advertisement',						'Kleinanzeige');
define('market_head_advertisement_add',				'Kleinanzeige erstellen');
define('market_head_advertisement_overview',	'Übersicht über Ihre Kleinanzeigen');

define('market_hint_adv_category',						'Wählen Sie die Kategorie aus, in der die Kleinanzeige veröffentlicht werden soll.');
define('market_hint_adv_commercial',					'');
define('market_hint_adv_image_size',					'Sie können max. <b>%d</b> Bilder bis zu einer Größe von <b>%s</b> hochladen, die Breite und Höhe der Bilder wird automatisch begrenzt.');
define('market_hint_adv_price',								'');
define('market_hint_adv_price_type',					'');
define('market_hint_adv_text',								'');
define('market_hint_adv_title',								'');
define('market_hint_adv_type',								'');

define('market_intro_advertisement_add',			'Erstellen Sie eine neue Kleinanzeige.');
define('market_intro_advertisement_overview',	'Wählen Sie die gewünschte Kleinanzeige aus, um sie weiter zu bearbeiten.');
define('market_intro_categories',							'Bearbeiten Sie die Kategorien für kitMarketPlace oder fügen Sie neue Kategorien hinzu.');

define('market_label_add_category',						'Kategorie hinzufügen');
define('market_label_adv_category',						'Kategorie');
define('market_label_adv_commercial',					'Angebot');
define('market_label_adv_image_upload',				'Bild hochladen');
define('market_label_adv_price',							'Preis');
define('market_label_adv_price_type',					'Preis Typ');
define('market_label_adv_status',							'Status'); 
define('market_label_adv_text',								'Text der Anzeige');
define('market_label_adv_title',							'Titel der Anzeige');
define('market_label_adv_type',								'Typ der Kleinanzeige'); 
define('market_label_category_01',						'Kategorie, Level 01');
define('market_label_category_02',						'Kategorie, Level 02');
define('market_label_category_03',						'Kategorie, Level 03');
define('market_label_category_04',						'Kategorie, Level 04');
define('market_label_category_05',						'Kategorie, Level 05');
define('market_label_cfg_ad_image_dir',				'Bilder Verzeichnis');
define('market_label_cfg_ad_image_prev_width','Breite der Vorschaubilder');
define('market_label_cfg_ad_image_types',			'zulässige Bildtypen');
define('market_label_cfg_ad_list_entries',		'Einträge in der Liste');
define('market_label_cfg_ad_max_image_height','max. Breite der Bilder');
define('market_label_cfg_ad_max_image_width',	'max. Höhe der Bilder');
define('market_label_cfg_ad_max_images',			'max. Anzahl an Bildern');
define('market_label_cfg_ad_publish_direct',	'Anzeigen sofort veröffentlichen');
define('market_label_cfg_exec',								'kitMarketPlace ausführen');
define('market_label_cfg_form_dlg_account',		'kitForm Dialog: Account');
define('market_label_cfg_form_dlg_login',			'kitForm Dialog: Login');
define('market_label_cfg_kit_category',				'KeepInTouch (KIT) Kategorie');

define('market_mail_subject_status_changed',	'Statusänderunge Ihrer Kleinanzeige');

define('market_msg_ad_inserted_locked',				'<p>Die Kleinanzeige wurde erfolgreich angelegt. Die Anzeige wird geprüft bevor sie freigeschaltet wird.</p>');
define('market_msg_ad_inserted_publish',			'<p>Die Kleinanzeige wurde erfolgreich angelegt und wird direkt veröffentlicht.</p>');
define('market_msg_ad_select_ad',							'<p>Bitte wählen Sie die Kleinanzeige die Sie einsehen möchten in der der <b>Übersicht</b> aus!</p>');
define('market_msg_ad_status_changed',				'<p>Bei der Kleinanzeige mit der <b>ID %05d</b> wurde der Status auf <b>%s</b> geändert.</p>');
define('market_msg_ad_status_mail_send',			'<p>Zu der Kleinanzeige mit der <b>ID %05d</b> wurde eine Status E-Mail versendet!</p>'); 
define('market_msg_category_deleted',					'<p>Die <i>%s</i> <b>%s</b> wurde gelöscht.</p>'); 
define('market_msg_category_inserted',				'<p>Die <i>%s</i> <b>%s</b> wurde hinzugefügt.</p>');
define('market_msg_category_needed',					'<p>Bitte wählen Sie eine passende <b>Kategorie</b> für Ihre Kleinanzeige aus!</p>');
define('market_msg_category_updated',					'<p>Die <i>%s</i> <b>%s</b> wurde in <b>%s</b> geändert.</p>');
define('market_msg_field_empty',							'<p>Das Feld <b>%s</b> muss gesetzt sein und einen Wert enthalten!</p>');
define('market_msg_file_ext_not_allowed',			'<p>Die Datei <b>%s</b> wird nicht akzeptiert, es sind nur Dateien mit den Endungen <b>%s</b> zulässig.</p>');
define('market_msg_price_needed',							'<p>Bitte geben Sie einen Preis für ihr Gesuch/Angebot an!</p>'); 

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
define('market_tab_account',									'Mein Konto');
define('market_tab_advertisement',						'Kleinanzeige');
define('market_tab_categories',								'Kategorien');
define('market_tab_config',										'Einstellungen');
define('market_tab_market',										'Kleinanzeigen');
define('market_tab_new_advertisement',				'Neue Kleinanzeige');
define('market_tab_logout',										'Abmelden');
define('market_tab_overview',									'Übersicht');

define('market_text_add_new_category',				'- neue Kategorie -');
define('market_text_select',									'- bitte auswählen -');

define('market_th_delete',										'ENTF');
define('market_th_id',												'ID');
define('market_th_kit_id',										'Kunde ID');
define('market_th_category',									'Kategorie');
define('market_th_type',											'Typ');
define('market_th_commercial',								'Privat/Kommerziell');
define('market_th_title',											'Titel');
define('market_th_price',											'Preis');
define('market_th_price_type',								'Preis Typ');
define('market_th_pictures',									'Bilder');
define('market_th_text',											'Text');
define('market_th_status',										'Status');
define('market_th_start_date',								'Startdatum');
define('market_th_end_date',									'Enddatum');
define('market_th_timestamp',									'Letzte Änderung');
define('market_type_offer',										'Ich biete');
define('market_type_search',									'Ich suche');
define('market_type_undefined',								'- nicht festgelegt -');

?>