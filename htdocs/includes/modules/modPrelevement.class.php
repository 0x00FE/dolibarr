<?php
/* Copyright (C) 2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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

/*!     \defgroup   prelevement     Module prelevement
        \brief      Module de gestion des pr�l�vements bancaires
*/

/*!
        \file       htdocs/includes/modules/modPrelevement.class.php
        \ingroup    prelevement
        \brief      Fichier de description et activation du module Prelevement
*/

include_once "DolibarrModules.class.php";

/*! \class modPrelevement
		\brief      Classe de description et activation du module Prelevement
*/

class modPrelevement extends DolibarrModules
{

   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modPrelevement($DB)
  {
    $this->db = $DB ;
    $this->numero = 57 ;

    $this->family = "technic";
    $this->name = "Prelevement";
    $this->description = "Gestion des Pr�l�vements (experimental)";
    $this->const_name = "MAIN_MODULE_PRELEVEMENT";
    $this->const_config = MAIN_MODULE_PRELEVEMENT;
    $this->special = 0;

    // Dir
    $this->dirs = array();
    $this->data_directory = DOL_DATA_ROOT . "/prelevement/bon/";

    // D�pendances
    $this->depends = array();
    $this->requiredby = array();

    $this->const = array();
    $this->boxes = array();
  }

   /**
    *   \brief      Fonction appel� lors de l'activation du module. Ins�re en base les constantes, boites, permissions du module.
    *               D�finit �galement les r�pertoires de donn�es � cr�er pour ce module.
    */
  function init()
  {
    /*
     * Permissions
     */    
    $this->remove();
    $sql = array(
		 "INSERT INTO ".MAIN_DB_PREFIX."rights_def VALUES (150,'Tous les droits sur les pr�l�vements','prelevement','a',0);",
		 "INSERT INTO ".MAIN_DB_PREFIX."rights_def VALUES (151,'Consulter les prelevement','pr�l�vements','r',0);",
		 "INSERT INTO ".MAIN_DB_PREFIX."rights_def VALUES (152,'Configurer les prelevement','pr�l�vements','w',0);");
    
    /*
     * Documents
     *
     */
    $this->dirs[0] = DOL_DATA_ROOT . "/prelevement/" ;
    $this->dirs[1] = DOL_DATA_ROOT . "/prelevement/bon" ;

    return $this->_init($sql);
  }

  /**
   *    \brief      Fonction appel�e lors de la d�sactivation d'un module.
   *                Supprime de la base les constantes, boites et permissions du module.
   */
  function remove()
  {
    $sql = array("DELETE FROM ".MAIN_DB_PREFIX."rights_def WHERE module = 'prelevement';");

    return $this->_remove($sql);
  }
}
?>
