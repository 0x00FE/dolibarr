<?php
/* Copyright (C) 2005      Matthieu Valleton    <mv@seeschloss.org>
 * Copyright (C) 2005-2007 Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \defgroup   category       Module categorie
        \brief      Module pour g�rer les cat�gories
		\version	$Id$
*/

/**
        \file       htdocs/includes/modules/modCategorie.class.php
        \ingroup    category
        \brief      Fichier de description et activation du module Categorie
*/
include_once(DOL_DOCUMENT_ROOT ."/includes/modules/DolibarrModules.class.php");


/**
        \class      modCategorie
        \brief      Classe de description et activation du module Categorie
*/
class modCategorie extends DolibarrModules
{
	/**
	 *		\brief	Constructeur. d�finit les noms, constantes et bo�tes
	 * 		\param	DB	handler d'acc�s base
	 */
	function modCategorie ($DB)
	{
		$this->db = $DB;
		$this->numero = 1780;
	
		$this->family = "technic";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = eregi_replace('^mod','',get_class($this));
		$this->description = "Gestion des cat�gories (produits, clients, fournisseurs...)";
	
		$this->revision = explode(' ','$Revision$');
		$this->version = $this->revision[1];
		//$this->version = 'experimental';    // 'development' or 'experimental' or 'dolibarr' or version
	
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->special = 0;
		$this->picto = 'generic';
	
		// Dir
		$this->dirs = array();
	
		// Dependencies
		$this->depends = array("modProduit");
	
		// Config pages
		$this->config_page_url = array();
		$this->langfiles = array("products","companies","categories");
		
		// Constantes
		$this->const = array();
	
		// Boxes
		$this->boxes = array();
	
		// Permissions
		$this->rights = array();
		$this->rights_class = 'categorie';
	
		$r=0;
	
		$this->rights[$r][0] = 241; // id de la permission
		$this->rights[$r][1] = 'Lire les categories'; // libelle de la permission
		$this->rights[$r][2] = 'r'; // type de la permission (d�pr�ci� � ce jour)
		$this->rights[$r][3] = 1; // La permission est-elle une permission par d�faut
		$this->rights[$r][4] = 'lire';
		$r++;
	
		$this->rights[$r][0] = 242; // id de la permission
		$this->rights[$r][1] = 'Creer/modifier les categories'; // libelle de la permission
		$this->rights[$r][2] = 'w'; // type de la permission (d�pr�ci� � ce jour)
		$this->rights[$r][3] = 0; // La permission est-elle une permission par d�faut
		$this->rights[$r][4] = 'creer';
		$r++;
	
		$this->rights[$r][0] = 243; // id de la permission
		$this->rights[$r][1] = 'Supprimer les categories'; // libelle de la permission
		$this->rights[$r][2] = 'd'; // type de la permission (d�pr�ci� � ce jour)
		$this->rights[$r][3] = 0; // La permission est-elle une permission par d�faut
		$this->rights[$r][4] = 'supprimer';
		$r++;
	
		$this->rights[$r][0] = 244; // id de la permission
		$this->rights[$r][1] = 'Voir le contenu des categories cachees'; // libelle de la permission
		$this->rights[$r][2] = 'r'; // type de la permission (d�pr�ci� � ce jour)
		$this->rights[$r][3] = 1; // La permission est-elle une permission par d�faut
		$this->rights[$r][4] = 'voir';
		$r++;
		
		// Exports
		//--------
		$r=0;

		$r++;
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='List of supplier categories';
		$this->export_permission[$r]=array(array("categorie","lire"));
		$this->export_fields_array[$r]=array('u.rowid'=>"CategId",'u.label'=>"Label",'u.description'=>"Description",'s.rowid'=>'CompanyId','s.nom'=>'Name');
		$this->export_entities_array[$r]=array('u.rowid'=>"category",'u.label'=>"category",'u.description'=>"category",'s.rowid'=>'company','s.nom'=>'company');
		$this->export_alias_array[$r]=array('u.rowid'=>"idcateg",'u.label'=>"label",'u.description'=>"description",'s.rowid'=>'idsoc','s.nom'=>'name');
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'categorie as u, '.MAIN_DB_PREFIX.'categorie_fournisseur as cf, '.MAIN_DB_PREFIX.'societe as s';
		$this->export_sql_end[$r] .=' WHERE u.rowid = cf.fk_categorie AND cf.fk_societe = s.rowid';
		$this->export_sql_end[$r] .=' AND u.type = 1';	// Supplier categories

		$r++;
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='List of customer/prospect categories';
		$this->export_permission[$r]=array(array("categorie","lire"));
		$this->export_fields_array[$r]=array('u.rowid'=>"CategId",'u.label'=>"Label",'u.description'=>"Description",'s.rowid'=>'CompanyId','s.nom'=>'Name');
		$this->export_entities_array[$r]=array('u.rowid'=>"category",'u.label'=>"category",'u.description'=>"category",'s.rowid'=>'company','s.nom'=>'company');
		$this->export_alias_array[$r]=array('u.rowid'=>"idcateg",'u.label'=>"label",'u.description'=>"description",'s.rowid'=>'idsoc','s.nom'=>'name');
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'categorie as u, '.MAIN_DB_PREFIX.'categorie_societe as cf, '.MAIN_DB_PREFIX.'societe as s';
		$this->export_sql_end[$r] .=' WHERE u.rowid = cf.fk_categorie AND cf.fk_societe = s.rowid';
		$this->export_sql_end[$r] .=' AND u.type = 2';	// Customer/Prospect categories
		
		$r++;
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='List of products categories';
		$this->export_permission[$r]=array(array("categorie","lire"));
		$this->export_fields_array[$r]=array('u.rowid'=>"CategId",'u.label'=>"Label",'u.description'=>"Description",'p.rowid'=>'ProductId','p.ref'=>'Ref');
		$this->export_entities_array[$r]=array('u.rowid'=>"category",'u.label'=>"category",'u.description'=>"category",'p.rowid'=>'product','p.ref'=>'product');
		$this->export_alias_array[$r]=array('u.rowid'=>"idcateg",'u.label'=>"label",'u.description'=>"description",'p.rowid'=>'idprod','p.ref'=>'ref');
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'categorie as u, '.MAIN_DB_PREFIX.'categorie_product as cp, '.MAIN_DB_PREFIX.'product as p';
		$this->export_sql_end[$r] .=' WHERE u.rowid = cp.fk_categorie AND cp.fk_product = p.rowid';
		$this->export_sql_end[$r] .=' AND u.type = 0';	// Supplier categories
	}


	/**
	 *   \brief      Fonction appel�e lors de l'activation du module. Ins�re en base les constantes, boites, permissions du module.
	 *               D�finit �galement les r�pertoires de donn�es � cr�er pour ce module.
	 */
	function init()
	{
		// Permissions
		$this->remove();
	
		$sql = array();
	
		return $this->_init($sql);
	}

	/**
	 *    \brief      Fonction appel�e lors de la d�sactivation d'un module.
	 *                Supprime de la base les constantes, boites et permissions du module.
	 */
	function remove()
	{
		$sql = array();
	
		return $this->_remove($sql);
	}

}
?>
