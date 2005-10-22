<?php
/* Copyright (C) 2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005 Regis Houssin        <regis.houssin@cap-networks.com>
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

/**     \defgroup   ldap     Module ldap
        \brief      Module pour interfacer les contacts avec un annuaire Ldap
*/

/**
        \file       htdocs/includes/modules/modLdap.class.php
        \ingroup    ldap
        \brief      Fichier de description et activation du module Ldap
*/

include_once "DolibarrModules.class.php";

/** \class modLdap
		\brief      Classe de description et activation du module Ldap
*/

class modLdap extends DolibarrModules
{
   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modLdap($DB)
  {
    $this->db = $DB ;
    $this->numero = 200 ;

    $this->name = "Ldap";
    $this->family = "technic";
    $this->description = "Synchronisation Ldap";
    $this->version = 'experimental';    // 'experimental' or 'dolibarr' or version
    $this->const_name = 'MAIN_MODULE_LDAP';
    $this->special = 1;

    // Dir
    $this->dirs = array();

    // Config pages
    $this->config_page_url = "ldap.php";

    // D�pendances
    $this->depends = array();
    $this->requiredby = array();

    // Constantes
    $this->const = array();
    
    // Boites
    $this->boxes = array();

    // Permissions
    $this->rights = array();
    $this->rights_class = 'ldap';
  }

   /**
    *   \brief      Fonction appel� lors de l'activation du module. Ins�re en base les constantes, boites, permissions du module.
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
