<?php
/* Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *
 */

/*!     \defgroup   expedition     Module expedition
        \brief      Module pour g�rer les expeditions de produits
*/

/*!
        \file       htdocs/includes/modules/modExpedition.class.php
        \ingroup    expedition
        \brief      Fichier de description et activation du module Expedition
*/

include_once "DolibarrModules.class.php";

/*! \class modExpedition
		\brief      Classe de description et activation du module Expedition
*/

class modExpedition extends DolibarrModules
{

   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modExpedition($DB)
  {
    $this->db = $DB ;
    $this->numero = 80 ;

    $this->family = "crm";
    $this->name = "Expedition";
    $this->description = "Gestion des exp�ditions";
    $this->const_name = "MAIN_MODULE_EXPEDITION";
    $this->const_config = MAIN_MODULE_EXPEDITION;
    $this->special = 0;

    // Dir
    $this->dirs = array();

    // Config pages
    $this->config_page_url = "expedition.php";

    // D�pendances
    $this->depends = array();
    $this->requiredby = array();

    // Constantes
    $this->const = array();
    
    // Boxes
    $this->boxes = array();

    // Permissions
    $this->rights = array();
    $this->rights_class = 'expedition';
  }

   /**
    *   \brief      Fonction appel�e lors de l'activation du module. Ins�re en base les constantes, boites, permissions du module.
    *               D�finit �galement les r�pertoires de donn�es � cr�er pour ce module.
    */
  function init()
  {
    // Permissions
    $this->remove();

    $sql = array(
		 "INSERT INTO ".MAIN_DB_PREFIX."rights_def VALUES (100,'Tous les droits sur les expeditions','expedition','a',0);",
		 "INSERT INTO ".MAIN_DB_PREFIX."rights_def VALUES (101,'Lire les expeditions','expedition','r',1);",
		 "INSERT INTO ".MAIN_DB_PREFIX."rights_def VALUES (102,'Cr�er modifier les expeditions','expedition','w',0);",
		 "INSERT INTO ".MAIN_DB_PREFIX."rights_def VALUES (104,'Valider les expeditions','expedition','d',0);",
		 "INSERT INTO ".MAIN_DB_PREFIX."rights_def VALUES (109,'Supprimer les expeditions','expedition','d',0);",
		 );
    
    return $this->_init($sql);
  }

  /**
   *    \brief      Fonction appel�e lors de la d�sactivation d'un module.
   *                Supprime de la base les constantes, boites et permissions du module.
   */
  function remove()
  {
    $sql = array(

		 );

    return $this->_remove($sql);

  }
}
?>
