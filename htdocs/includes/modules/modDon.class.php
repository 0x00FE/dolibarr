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

/**     \defgroup   don     Module don
        \brief      Module pour g�rer le suivi des dons
*/

/**
        \file       htdocs/includes/modules/modDon.class.php
        \ingroup    don
        \brief      Fichier de description et activation du module Don
*/

include_once "DolibarrModules.class.php";

/** \class modDon
        \brief      Classe de description et activation du module Don
*/

class modDon  extends DolibarrModules
{

   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modDon($DB)
  {
    $this->db = $DB ;
    $this->numero = 700 ;

    $this->family = "financial";
    $this->name = "Don";
    $this->description = "Gestion des dons (exp�rimental)";
    $this->const_name = "MAIN_MODULE_DON";
    $this->const_config = MAIN_MODULE_DON;
    $this->special = 0;

    // Dir
    $this->dirs = array();

    // D�pendances
    $this->depends = array();
    $this->requiredby = array();

    // Constantes
    $this->const = array();
    $this->const[0][0] = "DONS_FORM";
    $this->const[0][1] = "chaine";
    $this->const[0][2] = "fsfe.fr.php";
    $this->const[0][3] = 'Nom du gestionnaire de formulaire de dons';
    $this->const[0][4] = 1;

    // Boxes
    $this->boxes = array();
    
    // Permissions
    $this->rights = array();
    $this->rights_class = 'don';
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
