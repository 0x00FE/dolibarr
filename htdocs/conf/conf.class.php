<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      Xavier Dutoit        <doli@sydesy.com>
 * Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2006 	   Jean Heimburger   	<jean@tiaris.info>
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
 *
 * $Id$
 * $Source$
 */

/**
    	\file       htdocs/conf/conf.class.php
		\brief      Fichier de la classe de stockage de la config courante
		\version    $Revision$
*/


/**
        \class      Conf
		\brief      Classe de stockage de la config courante
		\todo       Deplacer ce fichier dans htdocs/lib
*/

class Conf
{
    /** \public */
    var $db;            // Objet des caract�ristiques de connexions
                        // base db->host, db->name, db->user, db->pass, db->type
    var $maxfilesize = 2000000;     // Taille max des fichiers upload�s

    var $externalrss;
    var $commande;
    var $ficheinter;
    var $commercial;
    var $societe;
    var $expedition;
    var $compta;
    var $banque;
    var $don;
    var $caisse;
    var $fournisseur;
    var $adherent;
    var $produit;
    var $service;
    var $stock;
    var $boutique;
    var $projet;
    var $postnuke;
    var $webcal;
    var $propal;
    var $categorie;
	var $oscommerce2;


	/**
	 *      \brief      Positionne toutes les variables de configuration
	 *      \param      $db			Handler d'acc�s base
	 *      \return     int         < 0 si erreur, >= 0 si succ�s
	 */
	function setValues($db)
	{
	    dolibarr_syslog("functions.inc.php::setValues");

		/*
		 * Definition de toutes les Constantes globales d'environnement
		 * - En constante php (\todo a virer)
		 * - En $this->global->key=value
		 */
		$sql = "SELECT name, value FROM ".MAIN_DB_PREFIX."const";
		$result = $db->query($sql);
		if ($result)
		{
		    $numr = $db->num_rows($result);
		    $i = 0;

		    while ($i < $numr)
		    {
		        $objp = $db->fetch_object($result);
		        $key=$objp->name;
		        $value=$objp->value; // Pas de stripslashes (ne s'applique pas sur lecture en base mais apr�s POST quand get_magic_quotes_gpc()==1)
		        define ("$key", $value);
		        $this->global->$key=$value;
		        $i++;
		    }
		}
		$db->free($result);



		/*
		 * Nettoyage variables des gestionnaires de menu
		 * conf->menu_top et conf->menu_left sont d�finis dans main.inc.php (selon user)
		 */
		if (! $this->global->MAIN_MENU_BARRETOP) $this->global->MAIN_MENU_BARRETOP="default.php";
		if (! $this->global->MAIN_MENUFRONT_BARRETOP) $this->global->MAIN_MENUFRONT_BARRETOP="default.php";
		if (! $this->global->MAIN_MENU_BARRELEFT) $this->global->MAIN_MENU_BARRELEFT="default.php";
		if (! $this->global->MAIN_MENUFRONT_BARRELEFT) $this->global->MAIN_MENUFRONT_BARRELEFT="default.php";

		/*
		 * Charge l'objet de traduction et positionne langage courant global
		 */
		if (! $this->global->MAIN_LANG_DEFAULT) $this->global->MAIN_LANG_DEFAULT="fr_FR";

		/*
		 * Autres parametres globaux de configurations
		 */
		$this->users->dir_output=DOL_DATA_ROOT."/users";

		/*
		 * Autorisation globale d'uploader (necessaire pour desactiver dans la demo)
		 * conf->upload peut etre �cras�e dans main.inc.php (selon user)
		 */
		if ($this->global->MAIN_UPLOAD_DOC) $this->upload = 1;
		else $this->upload = 0;

		/*
		 * Definition des parametres d'activation de module et dependants des modules
		 * Chargement d'include selon etat activation des modules
		 */

		// Module bookmark4u
		$this->bookmark4u->enabled=defined('MAIN_MODULE_BOOKMARK4U')?MAIN_MODULE_BOOKMARK4U:0;
		$this->bookmark->enabled=defined('MAIN_MODULE_BOOKMARK')?MAIN_MODULE_BOOKMARK:0;

		// Module deplacement
		$this->deplacement->enabled=defined("MAIN_MODULE_DEPLACEMENT")?MAIN_MODULE_DEPLACEMENT:0;

		// Module mailing
		$this->mailing->enabled=defined("MAIN_MODULE_MAILING")?MAIN_MODULE_MAILING:0;
	
		// Module notification
		$this->notification->enabled=defined("MAIN_MODULE_NOTIFICATION")?MAIN_MODULE_NOTIFICATION:0;

		// Module externalrss
		$this->externalrss->enabled=defined("MAIN_MODULE_EXTERNALRSS")?MAIN_MODULE_EXTERNALRSS:0;
		
		// Module commande client
		$this->commande->enabled=defined("MAIN_MODULE_COMMANDE")?MAIN_MODULE_COMMANDE:0;
		$this->commande->dir_output=DOL_DATA_ROOT."/commande";
		$this->commande->dir_images=DOL_DATA_ROOT."/commande/images";

		// Module expeditions
		$this->expedition->enabled=defined("MAIN_MODULE_EXPEDITION")?MAIN_MODULE_EXPEDITION:0;
		// Sous module bons d'expedition
		$this->expedition_bon->enabled=defined("MAIN_SUBMODULE_EXPEDITION")?MAIN_SUBMODULE_EXPEDITION:0;
		$this->expedition->dir_output=DOL_DATA_ROOT."/expedition";
		$this->expedition->dir_images=DOL_DATA_ROOT."/expedition/images";
		// Sous module bons de livraison
		$this->livraison->enabled=defined("MAIN_SUBMODULE_LIVRAISON")?MAIN_SUBMODULE_LIVRAISON:0;
		$this->livraison->dir_output=DOL_DATA_ROOT."/livraison";
		$this->livraison->dir_images=DOL_DATA_ROOT."/livraison/images";

		// Module societe
		$this->societe->enabled=defined("MAIN_MODULE_SOCIETE")?MAIN_MODULE_SOCIETE:0;
		$this->societe->dir_output=DOL_DATA_ROOT."/societe";
		$this->societe->dir_images=DOL_DATA_ROOT."/societe/images";
		$this->societe->dir_logos=DOL_DATA_ROOT."/societe/logos";
		if (defined('SOCIETE_OUTPUTDIR') && SOCIETE_OUTPUTDIR) { $this->societe->dir_output=SOCIETE_OUTPUTDIR; }    # Pour passer outre le rep par defaut
		// Module commercial
		$this->commercial->enabled=defined("MAIN_MODULE_COMMERCIAL")?MAIN_MODULE_COMMERCIAL:0;
		$this->commercial->dir_output=DOL_DATA_ROOT."/rapport";
		// Module taxes et charges sociales
		$this->tax->enabled=defined("MAIN_MODULE_TAX")?MAIN_MODULE_TAX:0;
		$this->tax->dir_output=DOL_DATA_ROOT."/taxes";
		$this->tax->dir_images=DOL_DATA_ROOT."/taxes/images";
		// Module comptaexpert
		$this->comptaexpert->enabled=defined("MAIN_MODULE_COMPTABILITE_EXPERT")?MAIN_MODULE_COMPTABILITE_EXPERT:0;
		$this->comptaexpert->dir_output=DOL_DATA_ROOT."/comptaexpert";
		$this->comptaexpert->dir_images=DOL_DATA_ROOT."/comptaexpert/images";
		// Module compta
		$this->compta->enabled=defined("MAIN_MODULE_COMPTABILITE")?MAIN_MODULE_COMPTABILITE:0;
		$this->compta->dir_output=DOL_DATA_ROOT."/compta";
		$this->compta->dir_images=DOL_DATA_ROOT."/compta/images";
		// Module banque
		$this->banque->enabled=defined("MAIN_MODULE_BANQUE")?MAIN_MODULE_BANQUE:0;
		$this->banque->dir_output=DOL_DATA_ROOT."/banque";
		$this->banque->dir_images=DOL_DATA_ROOT."/banque/images";
		// Module don
		$this->don->enabled=defined("MAIN_MODULE_DON")?MAIN_MODULE_DON:0;
		$this->don->dir_output=DOL_DATA_ROOT."/dons";
		$this->don->dir_images=DOL_DATA_ROOT."/dons/images";
		// Module syslog
		$this->syslog->enabled=defined("MAIN_MODULE_SYSLOG")?MAIN_MODULE_SYSLOG:0;
		// Module fournisseur
		$this->fournisseur->enabled=defined("MAIN_MODULE_FOURNISSEUR")?MAIN_MODULE_FOURNISSEUR:0;
		$this->fournisseur->dir_output=DOL_DATA_ROOT."/fournisseur";
		$this->fournisseur->commande->dir_output=DOL_DATA_ROOT."/fournisseur/commande";
		$this->fournisseur->commande->dir_images=DOL_DATA_ROOT."/fournisseur/commande/images";
		$this->fournisseur->facture->dir_output=DOL_DATA_ROOT."/fournisseur/facture";
		$this->fournisseur->facture->dir_images=DOL_DATA_ROOT."/fournisseur/facture/images";
		// Module ficheinter
		$this->fichinter->enabled=defined("MAIN_MODULE_FICHEINTER")?MAIN_MODULE_FICHEINTER:0;
		$this->fichinter->dir_output=DOL_DATA_ROOT."/ficheinter";
		$this->fichinter->dir_images=DOL_DATA_ROOT."/ficheinter/images";
		if (defined('FICHEINTER_OUTPUTDIR') && FICHEINTER_OUTPUTDIR) { $this->fichinter->dir_output=FICHEINTER_OUTPUTDIR; }    # Pour passer outre le rep par defaut
		// Module adherent
		$this->adherent->enabled=defined("MAIN_MODULE_ADHERENT")?MAIN_MODULE_ADHERENT:0;
		$this->adherent->dir_output=DOL_DATA_ROOT."/adherent";
		// Module produit
		$this->produit->enabled=defined("MAIN_MODULE_PRODUIT")?MAIN_MODULE_PRODUIT:0;
		$this->produit->dir_output=DOL_DATA_ROOT."/produit";
		$this->produit->dir_images=DOL_DATA_ROOT."/produit/images";
		$this->produit->MultiPricesEnabled=defined("PRODUIT_MULTIPRICES")?PRODUIT_MULTIPRICES:0;
		// Module service
		$this->service->enabled=defined("MAIN_MODULE_SERVICE")?MAIN_MODULE_SERVICE:0;
		$this->service->dir_output=DOL_DATA_ROOT."/produit";
		$this->service->dir_images=DOL_DATA_ROOT."/produit/images";
		// Module stock
		$this->stock->enabled=defined("MAIN_MODULE_STOCK")?MAIN_MODULE_STOCK:0;
		// Module code barre
		$this->barcode->enabled=defined("MAIN_MODULE_BARCODE")?MAIN_MODULE_BARCODE:0;
		// Module categorie
		$this->categorie->enabled=defined("MAIN_MODULE_CATEGORIE")?MAIN_MODULE_CATEGORIE:0;
		// Module contrat
		$this->contrat->enabled=defined("MAIN_MODULE_CONTRAT")?MAIN_MODULE_CONTRAT:0;
		// Module projet
		$this->projet->enabled=defined("MAIN_MODULE_PROJET")?MAIN_MODULE_PROJET:0;
		// Module oscommerce 1
		$this->boutique->enabled=defined("MAIN_MODULE_BOUTIQUE")?MAIN_MODULE_BOUTIQUE:0;
		$this->boutique->livre->enabled=defined("BOUTIQUE_LIVRE")?BOUTIQUE_LIVRE:0;
		$this->boutique->album->enabled=defined("BOUTIQUE_ALBUM")?BOUTIQUE_ALBUM:0;
		// Module oscommerce 2
		$this->oscommerce2->enabled=defined("MAIN_MODULE_OSCOMMERCEWS")?MAIN_MODULE_OSCOMMERCEWS:0;
		// Module postnuke
		$this->postnuke->enabled=defined("MAIN_MODULE_POSTNUKE")?MAIN_MODULE_POSTNUKE:0;
		// Module clicktodial
		$this->clicktodial->enabled=defined("MAIN_MODULE_CLICKTODIAL")?MAIN_MODULE_CLICKTODIAL:0;
		// Module prelevement
		$this->prelevement->enabled=defined("MAIN_MODULE_PRELEVEMENT")?MAIN_MODULE_PRELEVEMENT:0;
		$this->prelevement->dir_output=DOL_DATA_ROOT."/prelevement";
		$this->prelevement->dir_images=DOL_DATA_ROOT."/prelevement/images";
		// Module webcal
		$this->webcal->enabled=defined('MAIN_MODULE_WEBCALENDAR')?MAIN_MODULE_WEBCALENDAR:0;
		$this->webcal->db->type=defined('PHPWEBCALENDAR_TYPE')?PHPWEBCALENDAR_TYPE:'mysql';
		$this->webcal->db->host=defined('PHPWEBCALENDAR_HOST')?PHPWEBCALENDAR_HOST:'';
		$this->webcal->db->user=defined('PHPWEBCALENDAR_USER')?PHPWEBCALENDAR_USER:'';
		$this->webcal->db->pass=defined('PHPWEBCALENDAR_PASS')?PHPWEBCALENDAR_PASS:'';
		$this->webcal->db->name=defined('PHPWEBCALENDAR_DBNAME')?PHPWEBCALENDAR_DBNAME:'';
		// Module facture
		$this->facture->enabled=defined("MAIN_MODULE_FACTURE")?MAIN_MODULE_FACTURE:0;
		$this->facture->dir_output=DOL_DATA_ROOT."/facture";
		$this->facture->dir_images=DOL_DATA_ROOT."/facture/images";
		if (defined('FAC_OUTPUTDIR') && FAC_OUTPUTDIR) { $this->facture->dir_output=FAC_OUTPUTDIR; }                # Pour passer outre le rep par defaut
		// Module propal
		$this->propal->enabled=defined("MAIN_MODULE_PROPALE")?MAIN_MODULE_PROPALE:0;
		if (! defined("PROPALE_NEW_FORM_NB_PRODUCT")) define("PROPALE_NEW_FORM_NB_PRODUCT", 4);
		$this->propal->dir_output=DOL_DATA_ROOT."/propale";
		$this->propal->dir_images=DOL_DATA_ROOT."/propale/images";
		if (defined('PROPALE_OUTPUTDIR') && PROPALE_OUTPUTDIR) { $this->propal->dir_output=PROPALE_OUTPUTDIR; }    # Pour passer outre le rep par defaut
		// Module telephonie
		$this->telephonie->enabled=defined("MAIN_MODULE_TELEPHONIE")?MAIN_MODULE_TELEPHONIE:0;
		$this->telephonie->dir_output=DOL_DATA_ROOT."/telephonie";
		$this->telephonie->dir_images=DOL_DATA_ROOT."/telephonie/images";
		// Module energie
		$this->energie->enabled=defined("MAIN_MODULE_ENERGIE")?MAIN_MODULE_ENERGIE:0;
		// Module domaine
		$this->domaine->enabled=0;
		// Module voyage
		$this->voyage->enabled=0;
		// Module actionscomm
		$this->actionscomm->dir_output=DOL_DATA_ROOT."/action";
		// Module export
		$this->export->enabled=defined("MAIN_MODULE_EXPORT")?MAIN_MODULE_EXPORT:0;
		$this->export->dir_ouput=DOL_DATA_ROOT."/export";
		// Module ldap
		$this->ldap->enabled=defined("MAIN_MODULE_LDAP")?MAIN_MODULE_LDAP:0;
		// Module FCKeditor
		$this->fckeditor->enabled=defined("MAIN_MODULE_FCKEDITOR")?MAIN_MODULE_FCKEDITOR:0;
		$this->fckeditor->dir_output=DOL_DATA_ROOT."/fckeditor";


		/*
		 * Modification de quelques variable de conf en fonction des Constantes
		 */

		// societe
		if (! $this->global->SOCIETE_CODECLIENT_ADDON) $this->global->SOCIETE_CODECLIENT_ADDON="mod_codeclient_leopard";
		if (! $this->global->SOCIETE_CODEFOURNISSEUR_ADDON) $this->global->SOCIETE_CODEFOURNISSEUR_ADDON="mod_codeclient_leopard";
		if (! $this->global->SOCIETE_CODECOMPTA_ADDON) $this->global->SOCIETE_CODECOMPTA_ADDON="mod_codecompta_panicum";
		// Pour compatibilite ascendante:
		if ($this->global->CODECLIENT_ADDON) $this->global->SOCIETE_CODECLIENT_ADDON=$this->global->CODECLIENT_ADDON;
		if ($this->global->CODEFOURNISSEUR_ADDON) $this->global->SOCIETE_CODEFOURNISSEUR_ADDON=$this->global->CODEFOURNISSEUR_ADDON;

		// securite
		if (! $this->global->USER_PASSWORD_GENERATED) $this->global->USER_PASSWORD_GENERATED='standard';

		// conf->use_preview_tabs
		$this->use_preview_tabs=1;
		if (isset($this->global->MAIN_USE_PREVIEW_TABS)) $this->use_preview_tabs=$this->global->MAIN_USE_PREVIEW_TABS;

		// conf->use_javascript
		$this->use_javascript=1;
		if (isset($this->global->MAIN_DISABLE_JAVASCRIPT)) $this->use_javascript=! $this->global->MAIN_DISABLE_JAVASCRIPT;

		// conf->use_ajax
		$this->use_ajax=0; // Pas d' Ajax par defaut
		if (isset($this->global->MAIN_DISABLE_AJAX)) $this->use_ajax=! $this->global->MAIN_DISABLE_AJAX;

		// conf->use_popup_calendar
		$this->use_popup_calendar="";	// Pas de date popup par defaut
		if (isset($this->global->MAIN_POPUP_CALENDAR)) $this->use_popup_calendar=$this->global->MAIN_POPUP_CALENDAR;

		// conf->monnaie
		if (! $this->global->MAIN_MONNAIE) $this->global->MAIN_MONNAIE='EUR';
		$this->monnaie=$this->global->MAIN_MONNAIE;

		// $this->compta->mode = Option du module Compta: Defini le mode de calcul des etats comptables (CA,...)
		$this->compta->mode = 'RECETTES-DEPENSES';  // Par d�faut
		if (defined('COMPTA_MODE') && COMPTA_MODE) {
			// Peut etre 'RECETTES-DEPENSES' ou 'CREANCES-DETTES'
		    $this->compta->mode = COMPTA_MODE;
		}

		// $this->defaulttx
		if (defined('FACTURE_TVAOPTION') && FACTURE_TVAOPTION == 'franchise')
		{
			$this->defaulttx='0';		// Taux par d�faut des factures clients
		}
		else {
			$this->defaulttx='';		// Pas de taux par d�faut des factures clients, le premier sera pris
		}

		// $this->liste_limit = constante de taille maximale des listes
		if (! $this->global->MAIN_SIZE_LISTE_LIMIT) $this->global->MAIN_SIZE_LISTE_LIMIT=20;
		$this->liste_limit=$this->global->MAIN_SIZE_LISTE_LIMIT;

		// $this->produit->limit_size = constante de taille maximale des select de produit
		if (! isset($this->global->PRODUIT_LIMIT_SIZE)) $this->global->PRODUIT_LIMIT_SIZE=50;
		$this->produit->limit_size=$this->global->PRODUIT_LIMIT_SIZE;

		// $this->theme et $this->css
		if (! $this->global->MAIN_THEME) $this->global->MAIN_THEME="eldy";
		$this->theme=$this->global->MAIN_THEME;
		$this->css  = "theme/".$this->theme."/".$this->theme.".css";

		// $this->email_from          = email pour envoi par Dolibarr des mails auto (notifications, ...)
		$this->notification->email_from="dolibarr-robot@domain.com";
		if (defined('NOTIFICATION_EMAIL_FROM'))
		{
		    $this->notification->email_from=NOTIFICATION_EMAIL_FROM;
		}

		// $this->mailing->email_from = email pour envoi par Dolibarr des mailings
		if (defined('MAILING_EMAIL_FROM'))
		{
		    $this->mailing->email_from=MAILING_EMAIL_FROM;
		}
		else $this->mailing->email_from=$this->email_from;

		// $this->adherent->email_resil, ...
		if (defined("MAIN_MAIL_RESIL"))
		{
		  $this->adherent->email_resil=MAIN_MAIL_RESIL;
		}
		if (defined("MAIN_MAIL_RESIL_SUBJECT"))
		{
		  $this->adherent->email_resil_subject=MAIN_MAIL_RESIL_SUBJECT;
		}
		if (defined("MAIN_MAIL_VALID"))
		{
		  $this->adherent->email_valid=MAIN_MAIL_VALID;
		}
		if (defined("MAIN_MAIL_VALID_SUBJECT"))
		{
		  $this->adherent->email_valid_subject=MAIN_MAIL_VALID_SUBJECT;
		}
		if (defined("MAIN_MAIL_EDIT"))
		{
		  $this->adherent->email_edit=MAIN_MAIL_EDIT;
		}
		if (defined("MAIN_MAIL_EDIT_SUBJECT"))
		{
		  $this->adherent->email_edit_subject=MAIN_MAIL_EDIT_SUBJECT;
		}
		if (defined("MAIN_MAIL_NEW"))
		{
		  $this->adherent->email_new=MAIN_MAIL_NEW;
		}
		if (defined("MAIN_MAIL_NEW_SUBJECT"))
		{
		  $this->adherent->email_new_subject=MAIN_MAIL_NEW_SUBJECT;
		}

		// Format de la date
		// \todo Mettre les 4 formats dans fichier langue
		$this->format_date_short="%d/%m/%Y";
		$this->format_date_text_short="%d %b %Y";
		$this->format_date_hour_short="%d/%m/%Y %H:%M";
		$this->format_date_hour_text_short="%d %b %Y %H:%M";

		$this->format_date_short_java="dd/MM/yyyy";

		/* \todo Ajouter une option Gestion de la TVA dans le module compta qui permet de d�sactiver la fonction TVA
		 * (pour particuliers ou lib�raux en franchise)
		 * En attendant, valeur forc�e � 1
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

	}

}

?>
