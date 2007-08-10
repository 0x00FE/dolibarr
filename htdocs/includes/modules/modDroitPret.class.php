<?php
/* Copyright (C) 2007 Patrick Raguin <patrick.raguin@gmail.com>
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
        \defgroup   DroitPret     Module pr�t
        \brief      Module pour g�rer le suivi des droits de pr�ts
*/

/**
        \file       htdocs/includes/modules/modDroitPret.class.php
        \ingroup    don
        \brief      Fichier de description et activation du module DroitPret
*/

include_once "DolibarrModules.class.php";

/**
        \class      modDroitPret
        \brief      Classe de description et activation du module DroitPr�t
*/

class modDroitPret  extends DolibarrModules
{

   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modDroitPret($DB)
  {
    $this->db = $DB ;
    $this->id = 'droitpret';   // Same value xxx than in file modXxx.class.php file
    $this->numero = 2200 ;

    $this->family = "other";
    $this->name = "Droit Pr�t";
    $this->description = "Gestion du droit de pr�ts";
    $this->version = 'experimental';    // 'development' or 'experimental' or 'dolibarr' or version
    $this->const_name = 'MAIN_MODULE_DROITPRET';
    $this->special = 2;

    // Dir
    $this->dirs = array();

    // D�pendances
    $this->depends = array();
    $this->requiredby = array();

    // Config pages
    $this->config_page_url = array("droitpret.php");

    // Constantes
    $this->const = array();

    // Boxes
    $this->boxes = array();
    
    // Permissions
    $this->rights = array();
    $this->rights_class = 'droitpret';

    $this->rights[1][0] = 2200;
    $this->rights[1][1] = 'Lire les droits de pr�ts';
    $this->rights[1][2] = 'r';
    $this->rights[1][3] = 1;
    $this->rights[1][4] = 'lire';

    $this->rights[2][0] = 2201;
    $this->rights[2][1] = 'Cr�er/modifier les droits de pr�ts';
    $this->rights[2][2] = 'w';
    $this->rights[2][3] = 0;
    $this->rights[2][4] = 'creer';


  }


   /**
    *   \brief      Fonction appel�e lors de l'activation du module. Ins�re en base les constantes, boites, permissions du module.
    *               D�finit �galement les r�pertoires de donn�es � cr�er pour ce module.
    */
  function init()
  {
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
