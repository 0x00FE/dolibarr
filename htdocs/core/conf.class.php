<?php
/* Copyright (C) 2003-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      Xavier Dutoit        <doli@sydesy.com>
 * Copyright (C) 2004-2009 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin      	<regis@dolibarr.fr>
 * Copyright (C) 2006 	   Jean Heimburger    	<jean@tiaris.info>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 *	\file       	htdocs/core/conf.class.php
 *	\ingroup		core
 *  \brief      	Fichier de la classe de stockage de la config courante
 *  \remarks		La config est stockee dans le fichier conf/conf.php
 *  \version    	$Id$
 */


/**
 *  \class      Conf
 *  \brief      Classe de stockage de la config courante
 *  \todo       Deplacer ce fichier dans htdocs/lib
 */
class Conf
{
    /** \public */
	//! Object with database handler
	var $db;

	//! Charset for HTML output and for storing data in memory
	var $character_set_client='UTF-8';	// UTF-8, ISO-8859-1

	var $dol_document_root;

	var $monnaie;		// Used to store current currency
	var $css;			// Used to store current css (from theme)
	var $top_menu;
	var $left_menu;

	var $entity=1;		// By default

	var $css_modules=array();
	var $tabs_modules=array();

	var $logbuffer=array();


	/**
	*      \brief      Load setup values into conf object
	*      \param      $db			    Handler d'acces base
	*      \param      $entity			Id of company
	*      \return     int         		< 0 if KO, >= 0 if OK
	*/
	function setValues($db)
	{
		dol_syslog("Conf::setValues");

		$this->global->PRODUIT_CONFIRM_DELETE_LINE=1;	// Par defaut, a oui
		
		/*
		 * Definition de toutes les Constantes globales d'environnement
		 * - En constante php (TODO a virer)
		 * - En $this->global->key=value
		 */
		$sql = "SELECT name, value, entity FROM ".MAIN_DB_PREFIX."const ";
		$sql.= " WHERE entity = 0 OR entity = ".$this->entity;
		$result = $db->query($sql);
		if ($result)
		{
			$numr = $db->num_rows($result);
			$i = 0;

			while ($i < $numr)
			{
				$objp = $db->fetch_object($result);
				$key=$objp->name;
				$value=$objp->value;
				if ($key)
				{
					if (! defined("$key")) define ("$key", $value);	// In some cases, the constant might be already forced (Example: SYSLOG_FILE during install)
					$this->global->$key=$value;
					// If this is constant for a css file activated by a module
					if (eregi('^MAIN_MODULE_([A-Z_]+)_CSS$',$key) && $value)
					{
						$this->css_modules[]=$value;
					}
					// If this is constant for a new tab page activated by a module
					if (eregi('^MAIN_MODULE_([A-Z_]+)_TABS_',$key) && $value)
					{
						$params=split(':',$value,2);
						$this->tabs_modules[$params[0]][]=$value;
						//print 'xxx'.$params[0].'-'.$value;
					}
					// If this is a module constant
					if (eregi('^MAIN_MODULE_([A-Z]+)$',$key,$reg) && $value)
					{
						$module=strtolower($reg[1]);
						//print "Module ".$module." is enabled<br>\n";
						$this->$module->enabled=true;
					}
				}
				$i++;
			}
		}
		$db->free($result);

		// On reprend parametres du fichier de config conf.php
		// \TODO Mettre tous les param de conf DB dans une propriete de la classe


		// Nettoyage variables des gestionnaires de menu
		// conf->menu_top et conf->menu_left sont definis dans main.inc.php (selon user)
		if (! $this->global->MAIN_MENU_BARRETOP) $this->global->MAIN_MENU_BARRETOP="eldy_backoffice.php";
		if (! $this->global->MAIN_MENUFRONT_BARRETOP) $this->global->MAIN_MENUFRONT_BARRETOP="eldy_backoffice.php";
		if (! $this->global->MAIN_MENU_BARRELEFT) $this->global->MAIN_MENU_BARRELEFT="eldy_backoffice.php";
		if (! $this->global->MAIN_MENUFRONT_BARRELEFT) $this->global->MAIN_MENUFRONT_BARRELEFT="eldy_backoffice.php";

		// Variable globales LDAP
		if (empty($this->global->LDAP_FIELD_FULLNAME)) $this->global->LDAP_FIELD_FULLNAME='';
		if (! isset($this->global->LDAP_KEY_USERS)) $this->global->LDAP_KEY_USERS=$this->global->LDAP_FIELD_FULLNAME;
		if (! isset($this->global->LDAP_KEY_GROUPS)) $this->global->LDAP_KEY_GROUPS=$this->global->LDAP_FIELD_FULLNAME;
		if (! isset($this->global->LDAP_KEY_CONTACTS)) $this->global->LDAP_KEY_CONTACTS=$this->global->LDAP_FIELD_FULLNAME;
		if (! isset($this->global->LDAP_KEY_MEMBERS)) $this->global->LDAP_KEY_MEMBERS=$this->global->LDAP_FIELD_FULLNAME;


		// Load translation object with current language
		if (empty($this->global->MAIN_LANG_DEFAULT)) $this->global->MAIN_LANG_DEFAULT="en_US";

		// Other global parameters
		$this->users->dir_output=DOL_DATA_ROOT."/users";

		// For backward compatibility
		$this->comptaexpert->enabled=defined("MAIN_MODULE_COMPTABILITE_EXPERT")?MAIN_MODULE_COMPTABILITE_EXPERT:0;
		$this->compta->enabled=defined("MAIN_MODULE_COMPTABILITE")?MAIN_MODULE_COMPTABILITE:0;
		$this->webcal->enabled=defined('MAIN_MODULE_WEBCALENDAR')?MAIN_MODULE_WEBCALENDAR:0;
		$this->propal->enabled=defined("MAIN_MODULE_PROPALE")?MAIN_MODULE_PROPALE:0;

		// Module agenda
		$this->agenda->dir_temp=DOL_DATA_ROOT."/agenda/temp";

		// Module externalrss
		$this->externalrss->dir_temp=DOL_DATA_ROOT."/rss/temp";

		// Module commande client
		$this->commande->dir_output=DOL_DATA_ROOT."/commande";
		$this->commande->dir_temp  =DOL_DATA_ROOT."/commande/temp";

		// Module expeditions
		$this->expedition->dir_output=DOL_DATA_ROOT."/expedition";
		$this->expedition->dir_temp  =DOL_DATA_ROOT."/expedition/temp";

		// Sous module bons d'expedition
		$this->expedition_bon->enabled=defined("MAIN_SUBMODULE_EXPEDITION")?MAIN_SUBMODULE_EXPEDITION:0;
		$this->expedition_bon->dir_output=DOL_DATA_ROOT."/expedition/sending";
		$this->expedition_bon->dir_temp  =DOL_DATA_ROOT."/expedition/sending/temp";
		// Sous module bons de livraison
		$this->livraison_bon->enabled=defined("MAIN_SUBMODULE_LIVRAISON")?MAIN_SUBMODULE_LIVRAISON:0;
		$this->livraison_bon->dir_output=DOL_DATA_ROOT."/expedition/receipt";
		$this->livraison_bon->dir_temp  =DOL_DATA_ROOT."/expedition/receipt/temp";

		// Module societe
		$this->societe->dir_output=DOL_DATA_ROOT."/societe";
		$this->societe->dir_temp  =DOL_DATA_ROOT."/societe/temp";
		$this->societe->dir_logos =DOL_DATA_ROOT."/societe/logos";
		if (defined('SOCIETE_OUTPUTDIR') && SOCIETE_OUTPUTDIR) { $this->societe->dir_output=SOCIETE_OUTPUTDIR; }    # Pour passer outre le rep par defaut
		// Module commercial
		$this->commercial->dir_output=DOL_DATA_ROOT."/comm";
		$this->commercial->dir_temp  =DOL_DATA_ROOT."/comm/temp";

		// Module taxes et charges sociales
		$this->tax->dir_output=DOL_DATA_ROOT."/taxes";
		$this->tax->dir_temp  =DOL_DATA_ROOT."/taxes/temp";

		// Module comptaexpert
		$this->comptaexpert->dir_output=DOL_DATA_ROOT."/comptaexpert";
		$this->comptaexpert->dir_temp  =DOL_DATA_ROOT."/comptaexpert/temp";
		// Module compta
		$this->compta->dir_output=DOL_DATA_ROOT."/compta";
		$this->compta->dir_temp  =DOL_DATA_ROOT."/compta/temp";
		// Module banque
		$this->banque->dir_output=DOL_DATA_ROOT."/banque";
		$this->banque->dir_temp  =DOL_DATA_ROOT."/banque/temp";
		// Module don
		$this->don->dir_output=DOL_DATA_ROOT."/dons";
		$this->don->dir_temp  =DOL_DATA_ROOT."/dons/temp";
		// Module fournisseur
		$this->fournisseur->dir_output=DOL_DATA_ROOT."/fournisseur";
		$this->fournisseur->dir_temp=DOL_DATA_ROOT."/fournisseur/temp";
		$this->fournisseur->commande->dir_output=DOL_DATA_ROOT."/fournisseur/commande";
		$this->fournisseur->commande->dir_temp  =DOL_DATA_ROOT."/fournisseur/commande/temp";
		$this->fournisseur->facture->dir_output =DOL_DATA_ROOT."/fournisseur/facture";
		$this->fournisseur->facture->dir_temp   =DOL_DATA_ROOT."/fournisseur/facture/temp";
		// Module ficheinter
		$this->fichinter->dir_output=DOL_DATA_ROOT."/ficheinter";
		$this->fichinter->dir_temp  =DOL_DATA_ROOT."/ficheinter/temp";
		if (defined('FICHEINTER_OUTPUTDIR') && FICHEINTER_OUTPUTDIR) { $this->fichinter->dir_output=FICHEINTER_OUTPUTDIR; }    # Pour passer outre le rep par defaut
		// Module adherent
		$this->adherent->dir_output=DOL_DATA_ROOT."/adherent";
		$this->adherent->dir_tmp=DOL_DATA_ROOT."/adherent/temp";
		// Module produit
		$this->produit->dir_output=DOL_DATA_ROOT."/produit";
		$this->produit->dir_temp  =DOL_DATA_ROOT."/produit/temp";
		$this->produit->MultiPricesEnabled=defined("PRODUIT_MULTIPRICES")?PRODUIT_MULTIPRICES:0;
		// Module service
		$this->service->dir_output=DOL_DATA_ROOT."/produit";
		$this->service->dir_temp  =DOL_DATA_ROOT."/produit/temp";

		// Module droipret
		$this->droitpret->cat=defined('DROITPRET_CAT')?DROITPRET_CAT:'';
		$this->droitpret->cat=defined('DROITPRET_MAIL')?DROITPRET_MAIL:'';
		$this->droitpret->dir_temp=DOL_DATA_ROOT."/droitpret/temp";

		// Module contrat
		$this->contrat->dir_output=DOL_DATA_ROOT."/contracts";
		// Module prelevement
		$this->prelevement->dir_output=DOL_DATA_ROOT."/prelevement";
		$this->prelevement->dir_temp  =DOL_DATA_ROOT."/prelevement/temp";
		// Module webcal
		$this->webcal->db->type=defined('PHPWEBCALENDAR_TYPE')?PHPWEBCALENDAR_TYPE:'__dolibarr_main_db_type__';
		$this->webcal->db->host=defined('PHPWEBCALENDAR_HOST')?PHPWEBCALENDAR_HOST:'';
		$this->webcal->db->port=defined('PHPWEBCALENDAR_PORT')?PHPWEBCALENDAR_PORT:'';
		$this->webcal->db->user=defined('PHPWEBCALENDAR_USER')?PHPWEBCALENDAR_USER:'';
		$this->webcal->db->pass=defined('PHPWEBCALENDAR_PASS')?PHPWEBCALENDAR_PASS:'';
		$this->webcal->db->name=defined('PHPWEBCALENDAR_DBNAME')?PHPWEBCALENDAR_DBNAME:'';
		$this->webcal->dir_temp=DOL_DATA_ROOT.'/webcal/temp';
		// Module phenix
		$this->phenix->db->type=defined('PHPPHENIX_TYPE')?PHPPHENIX_TYPE:'__dolibarr_main_db_type__';
		$this->phenix->db->host=defined('PHPPHENIX_HOST')?PHPPHENIX_HOST:'';
		$this->phenix->db->port=defined('PHPPHENIX_PORT')?PHPPHENIX_PORT:'';
		$this->phenix->db->user=defined('PHPPHENIX_USER')?PHPPHENIX_USER:'';
		$this->phenix->db->pass=defined('PHPPHENIX_PASS')?PHPPHENIX_PASS:'';
		$this->phenix->db->name=defined('PHPPHENIX_DBNAME')?PHPPHENIX_DBNAME:'';
		$this->phenix->cookie=defined('PHPPHENIX_COOKIE')?PHPPHENIX_COOKIE:'';
		// Module mantis
		$this->mantis->db->type=defined('PHPMANTIS_TYPE')?PHPMANTIS_TYPE:'__dolibarr_main_db_type__';
		$this->mantis->db->host=defined('PHPMANTIS_HOST')?PHPMANTIS_HOST:'';
		$this->mantis->db->port=defined('PHPMANTIS_PORT')?PHPMANTIS_PORT:'';
		$this->mantis->db->user=defined('PHPMANTIS_USER')?PHPMANTIS_USER:'';
		$this->mantis->db->pass=defined('PHPMANTIS_PASS')?PHPMANTIS_PASS:'';
		$this->mantis->db->name=defined('PHPMANTIS_DBNAME')?PHPMANTIS_DBNAME:'';
		// Module facture
		$this->facture->dir_output=DOL_DATA_ROOT."/facture";
		$this->facture->dir_temp  =DOL_DATA_ROOT."/facture/temp";
		if (defined('FAC_OUTPUTDIR') && FAC_OUTPUTDIR) { $this->facture->dir_output=FAC_OUTPUTDIR; }                # Pour passer outre le rep par defaut
		// Module propal
		if (! defined("PROPALE_NEW_FORM_NB_PRODUCT")) define("PROPALE_NEW_FORM_NB_PRODUCT", 4);
		$this->propal->dir_output=DOL_DATA_ROOT."/propale";
		$this->propal->dir_temp  =DOL_DATA_ROOT."/propale/temp";
		if (defined('PROPALE_OUTPUTDIR') && PROPALE_OUTPUTDIR) { $this->propal->dir_output=PROPALE_OUTPUTDIR; }    # Pour passer outre le rep par defaut
		// Module telephonie
		$this->telephonie->dir_output=DOL_DATA_ROOT."/telephonie";
		$this->telephonie->dir_temp  =DOL_DATA_ROOT."/telephonie/temp";
		// Module domaine
		$this->domaine->enabled=0;
		// Module voyage
		$this->voyage->enabled=0;
		// Module actions
		$this->actions->dir_output=DOL_DATA_ROOT."/action";
		$this->actions->dir_temp  =DOL_DATA_ROOT."/action/temp";
		// Module export
		$this->export->dir_output=DOL_DATA_ROOT."/export";
		$this->export->dir_temp  =DOL_DATA_ROOT."/export/temp";
		// Module import
		$this->import->dir_output=DOL_DATA_ROOT."/import";
		$this->import->dir_temp  =DOL_DATA_ROOT."/import/temp";
		// Module ldap
		$this->ldap->dir_temp=DOL_DATA_ROOT."/ldap/temp";
		// Module FCKeditor
		$this->fckeditor->dir_output=DOL_DATA_ROOT."/fckeditor";
		// Module ECM
		$this->ecm->dir_output=DOL_DATA_ROOT."/ecm";
		// Module emailing
		$this->mailings->dir_temp=DOL_DATA_ROOT."/mailings/temp";
		// Module oscommerce 1
		$this->boutique->livre->enabled=defined("BOUTIQUE_LIVRE")?BOUTIQUE_LIVRE:0;
		$this->boutique->album->enabled=defined("BOUTIQUE_ALBUM")?BOUTIQUE_ALBUM:0;
		// Module Barcode
		$this->barcode->dir_temp=DOL_DATA_ROOT."/barcode/temp";

		/*
		 * Modification de quelques variable de conf en fonction des Constantes
		 */

		// System tools
		if (empty($this->global->SYSTEMTOOLS_MYSQLDUMP)) $this->global->SYSTEMTOOLS_MYSQLDUMP="mysqldump";

		// societe
		if (empty($this->global->SOCIETE_CODECLIENT_ADDON))      $this->global->SOCIETE_CODECLIENT_ADDON="mod_codeclient_leopard";
		if (empty($this->global->SOCIETE_CODEFOURNISSEUR_ADDON)) $this->global->SOCIETE_CODEFOURNISSEUR_ADDON=$this->global->SOCIETE_CODECLIENT_ADDON;
		if (empty($this->global->SOCIETE_CODECOMPTA_ADDON))      $this->global->SOCIETE_CODECOMPTA_ADDON="mod_codecompta_panicum";

		// securite
		if (empty($this->global->USER_PASSWORD_GENERATED)) $this->global->USER_PASSWORD_GENERATED='standard';

		// conf->box_max_lines
		$this->box_max_lines=5;
		if (isset($this->global->MAIN_BOXES_MAXLINES)) $this->box_max_lines=$this->global->MAIN_BOXES_MAXLINES;

		// conf->use_preview_tabs
		$this->use_preview_tabs=0;
		if (isset($this->global->MAIN_USE_PREVIEW_TABS)) $this->use_preview_tabs=$this->global->MAIN_USE_PREVIEW_TABS;

		// conf->use_javascript_ajax
		$this->use_javascript_ajax=1;
		if (isset($this->global->MAIN_DISABLE_JAVASCRIPT)) $this->use_javascript_ajax=! $this->global->MAIN_DISABLE_JAVASCRIPT;
		// If no javascript_ajax, Ajax features are disabled.
		if (! $this->use_javascript_ajax)
		{
			$this->global->PRODUIT_USE_SEARCH_TO_SELECT=0;
			$this->global->MAIN_CONFIRM_AJAX=0;
		}

		// conf->use_popup_calendar
		$this->use_popup_calendar="";	// Pas de date popup par defaut
		if (isset($this->global->MAIN_POPUP_CALENDAR)) $this->use_popup_calendar=$this->global->MAIN_POPUP_CALENDAR;

		// conf->monnaie
		if (empty($this->global->MAIN_MONNAIE)) $this->global->MAIN_MONNAIE='EUR';
		$this->monnaie=$this->global->MAIN_MONNAIE;

		// $this->compta->mode = Option du module Comptabilite (simple ou expert):
		// Defini le mode de calcul des etats comptables (CA,...)
		$this->compta->mode = 'RECETTES-DEPENSES';  // By default
		if (isset($this->global->COMPTA_MODE)) {
			// Peut etre 'RECETTES-DEPENSES' ou 'CREANCES-DETTES'
		    $this->compta->mode = $this->global->COMPTA_MODE;
		}

		// $this->defaulttx
		if (isset($this->global->FACTURE_TVAOPTION) && $this->global->FACTURE_TVAOPTION == 'franchise')
		{
			$this->defaulttx='0';		// Taux par defaut des factures clients
		}
		else {
			$this->defaulttx='';		// Pas de taux par defaut des factures clients, le premier sera pris
		}

		// $this->liste_limit = constante de taille maximale des listes
		if (empty($this->global->MAIN_SIZE_LISTE_LIMIT)) $this->global->MAIN_SIZE_LISTE_LIMIT=25;
		$this->liste_limit=$this->global->MAIN_SIZE_LISTE_LIMIT;

		// $this->produit->limit_size = constante de taille maximale des select de produit
		if (! isset($this->global->PRODUIT_LIMIT_SIZE)) $this->global->PRODUIT_LIMIT_SIZE=100;
		$this->produit->limit_size=$this->global->PRODUIT_LIMIT_SIZE;

		// $this->theme et $this->css
		if (empty($this->global->MAIN_THEME)) $this->global->MAIN_THEME="eldy";
		$this->theme=$this->global->MAIN_THEME;
		$this->css  = "theme/".$this->theme."/".$this->theme.".css";

		// $this->email_from = email pour envoi par dolibarr des mails automatiques
		$this->email_from = "dolibarr-robot@domain.com";
		if (! empty($this->global->MAIN_MAIL_EMAIL_FROM))
		{
			$this->email_from = $this->global->MAIN_MAIL_EMAIL_FROM;
		}
		// $this->notification->email_from = email pour envoi par Dolibarr des notifications
	    $this->notification->email_from=$this->email_from;
		if (! empty($this->global->NOTIFICATION_EMAIL_FROM))
		{
		    $this->notification->email_from=$this->global->NOTIFICATION_EMAIL_FROM;
		}

		// $this->mailing->email_from = email pour envoi par Dolibarr des mailings
		$this->mailing->email_from=$this->email_from;;
		if (! empty($this->global->MAILING_EMAIL_FROM))
		{
		    $this->mailing->email_from=$this->global->MAILING_EMAIL_FROM;
		}

		// Defini MAIN_GRAPH_LIBRARY
		if (empty($this->global->MAIN_GRAPH_LIBRARY))
		{
			$this->global->MAIN_GRAPH_LIBRARY = 'artichow';
		}

		// Format de la date
		// \todo Mettre les 4 formats dans fichier langue
		$this->format_date_short="%d/%m/%Y";
		$this->format_hour_short="%H:%M";
		$this->format_date_text_short="%d %b %Y";
		$this->format_date_text="%d %B %Y";
		$this->format_date_hour_short="%d/%m/%Y %H:%M";
		$this->format_date_hour_text_short="%d %b %Y %H:%M";
		$this->format_date_hour_text="%d %B %Y %H:%M";

		$this->format_date_short_java="dd/MM/yyyy";

		// Limites decimales si non definie (peuvent etre egale a 0)
		if (! isset($this->global->MAIN_MAX_DECIMALS_UNIT))  $this->global->MAIN_MAX_DECIMALS_UNIT=5;
		if (! isset($this->global->MAIN_MAX_DECIMALS_TOT))   $this->global->MAIN_MAX_DECIMALS_TOT=2;
		if (! isset($this->global->MAIN_MAX_DECIMALS_SHOWN)) $this->global->MAIN_MAX_DECIMALS_SHOWN=8;

		// Define umask
		if (empty($conf->global->MAIN_UMASK)) $conf->global->MAIN_UMASK='0664';

		/* \todo Ajouter une option Gestion de la TVA dans le module compta qui permet de desactiver la fonction TVA
		 * (pour particuliers ou liberaux en franchise)
		 * En attendant, valeur forcee a 1 car toujours interessant a avoir meme ceux qui veulent pas.
		 */
		$this->compta->tva=1;

		// Delais de tolerance des alertes
		$this->actions->warning_delay=$this->global->MAIN_DELAY_ACTIONS_TODO*24*60*60;
		$this->commande->traitement->warning_delay=$this->global->MAIN_DELAY_ORDERS_TO_PROCESS*24*60*60;
		$this->propal->cloture->warning_delay=$this->global->MAIN_DELAY_PROPALS_TO_CLOSE*24*60*60;
		$this->propal->facturation->warning_delay=$this->global->MAIN_DELAY_PROPALS_TO_BILL*24*60*60;
		$this->facture->fournisseur->warning_delay=$this->global->MAIN_DELAY_SUPPLIER_BILLS_TO_PAY*24*60*60;
		$this->facture->client->warning_delay=$this->global->MAIN_DELAY_CUSTOMER_BILLS_UNPAYED*24*60*60;
		$this->contrat->services->inactifs->warning_delay=$this->global->MAIN_DELAY_NOT_ACTIVATED_SERVICES*24*60*60;
		$this->contrat->services->expires->warning_delay=$this->global->MAIN_DELAY_RUNNING_SERVICES*24*60*60;
		$this->adherent->cotisation->warning_delay=$this->global->MAIN_DELAY_MEMBERS*24*60*60;
		$this->bank->rappro->warning_delay=$this->global->MAIN_DELAY_TRANSACTIONS_TO_CONCILIATE*24*60*60;
		$this->bank->cheque->warning_delay=(isset($this->global->MAIN_DELAY_CHEQUES_TO_DEPOSIT)?$this->global->MAIN_DELAY_CHEQUES_TO_DEPOSIT:0)*24*60*60;
	}

}

?>
