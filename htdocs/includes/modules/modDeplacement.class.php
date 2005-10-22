<?php
/* Copyright (C) 2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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

/**
        \defgroup   deplacement     Module deplacement
        \brief      Module pour g�rer les d�placements
*/

/**
        \file       htdocs/includes/modules/modDeplacement.class.php
        \ingroup    deplacement
        \brief      Fichier de description et activation du module Deplacement
*/

include_once "DolibarrModules.class.php";

/**
        \class      modDeplacement
		\brief      Classe de description et activation du module Deplacement
*/

class modDeplacement extends DolibarrModules
{

   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modDeplacement($DB)
  {
    $this->db = $DB ;
    $this->numero = 75 ;

    $this->family = "crm";
    $this->name = "D�placement";                        // Si traduction Module75Name non trouv�e
    $this->description = "Gestion des d�placements";    // Si traduction Module75Desc non trouv�e

    $this->revision = explode(' ','$Revision$');
    $this->version = $this->revision[1];

    $this->const_name = 'MAIN_MODULE_DEPLACEMENT';
    $this->special = 0;
    $this->picto = "trip";
    
    // Dir
    $this->dirs = array();

    // Config pages
    $this->config_page_url = "";

    // D�pendances
    $this->depends = array();
    $this->requiredby = array();

    // Constantes
    $this->const = array();
    
    // Boxes
    $this->boxes = array();

    // Permissions
    $this->rights = array();
    $this->rights_class = 'deplacement';

    $this->rights[1][0] = 170;
    $this->rights[1][1] = 'Lire les d�placements';
    $this->rights[1][2] = 'r';
    $this->rights[1][3] = 1;
    $this->rights[1][4] = 'lire';

    $this->rights[2][0] = 171;
    $this->rights[2][1] = 'Cr�er/modifier les d�placements';
    $this->rights[2][2] = 'w';
    $this->rights[2][3] = 0;
    $this->rights[2][4] = 'creer';

    $this->rights[3][0] = 172;
    $this->rights[3][1] = 'Supprimer les d�placements';
    $this->rights[3][2] = 'd';
    $this->rights[3][3] = 0;
    $this->rights[3][4] = 'supprimer';
  
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
