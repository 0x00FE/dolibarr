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

/*!   \defgroup   telephonie  Module telephonie
      \brief      Module pour g�rer la t�l�phonie
*/

/*!
      \file       htdocs/includes/modules/modTelephonie.class.php
      \ingroup    telephonie
      \brief      Fichier de description et activation du module de T�l�phonie
*/

include_once "DolibarrModules.class.php";

/*! \class modTelephonie
    \brief Classe de description et activation du module Telephonie
*/

class modTelephonie extends DolibarrModules
{

   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modTelephonie($DB)
  {
    $this->db = $DB ;
    $this->numero = 56 ;

    $this->family = "technic";
    $this->name = "Telephonie";
    $this->description = "Gestion de la Telephonie (experimental)";
    $this->const_name = "MAIN_MODULE_TELEPHONIE";
    $this->const_config = MAIN_MODULE_TELEPHONIE;
    $this->special = 1;

    // Dir
    $this->dirs = array();

    // D�pendances
    $this->depends = array();
    $this->requiredby = array();

    // Constantes
    $this->const = array();

    // Boites
    $this->boxes = array();

    // Permissions
    $this->rights = array();
    $this->rights_class = 'telephonie';
  }

   /**
    *   \brief      Fonction appel�e lors de l'activation du module. Ins�re en base les constantes, boites, permissions du module.
    *               D�finit �galement les r�pertoires de donn�es � cr�er pour ce module.
    */
  function init()
  {
    // Permissions
    $this->remove();

    $this->rights[0][0] = 140; // id de la permission
    $this->rights[0][1] = 'Tous les droits sur la telephonie'; // libelle de la permission
    $this->rights[0][2] = 'a'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[0][3] = 0; // La permission est-elle une permission par d�faut

    $this->rights[1][0] = 141; // id de la permission
    $this->rights[1][1] = 'Consulter la telephonie'; // libelle de la permission
    $this->rights[1][2] = 'r'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[1][3] = 0; // La permission est-elle une permission par d�faut

    $this->rights[2][0] = 142; // id de la permission
    $this->rights[2][1] = 'Commander les lignes'; // libelle de la permission
    $this->rights[2][2] = 'w'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[2][3] = 0; // La permission est-elle une permission par d�faut

    $this->rights[3][0] = 143; // id de la permission
    $this->rights[3][1] = 'Activer une ligne'; // libelle de la permission
    $this->rights[3][2] = 'w'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[3][3] = 0; // La permission est-elle une permission par d�faut

    $this->rights[4][0] = 144; // id de la permission
    $this->rights[4][1] = 'Configurer la telephonie'; // libelle de la permission
    $this->rights[4][2] = 'w'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[4][3] = 0; // La permission est-elle une permission par d�faut

    // Dir
    $this->dirs[0] = DOL_DATA_ROOT . "/telephonie/" ;
    $this->dirs[1] = DOL_DATA_ROOT . "/telephonie/ligne/" ;	  
    $this->dirs[2] = DOL_DATA_ROOT . "/telephonie/ligne/commande" ;	 
    $this->dirs[3] = DOL_DATA_ROOT . "/telephonie/logs" ;
    $this->dirs[4] = DOL_DATA_ROOT . "/telephonie/client" ;
    $this->dirs[5] = DOL_DATA_ROOT . "/telephonie/rapports" ;
    
    return $this->_init($sql);



  }

  /**
   *    \brief      Fonction appel�e lors de la d�sactivation d'un module.
   *                Supprime de la base les constantes, boites et permissions du module.
   */
  function remove()
  {
    $sql = array("DELETE FROM ".MAIN_DB_PREFIX."rights_def WHERE module = 'telephonie';");

    return $this->_remove($sql);
  }
}
?>
