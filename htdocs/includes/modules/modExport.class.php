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

/**     \defgroup   export      Module export
        \brief      Module g�n�rique pour r�aliser des exports de donn�es en base
*/

/**
        \file       htdocs/includes/modules/modExport.class.php
        \ingroup    export
        \brief      Fichier de description et activation du module export
*/

include_once "DolibarrModules.class.php";

/**     \class      modExport
		\brief      Classe de description et activation du module export
*/

class modExport extends DolibarrModules
{

   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modExport($DB)
  {
    $this->db = $DB ;
    $this->numero = 240;

    $this->family = "technic";
    $this->name = "Exports";
    $this->description = "Permet export des donn�es de la base en fichiers";
    $this->version = 'dolibarr';                        // 'experimental' or 'dolibarr' or version
    $this->const_name = 'MAIN_MODULE_EXPORT';
    $this->special = 0;
    $this->picto='';

    // Dir
    $this->dirs = array();

    // Config pages
    $this->config_page_url = array();

    // D�pendances
    $this->depends = array();
    $this->requiredby = array();
    $this->phpmin = array(4,2,0);
    $this->phpmax = array();

    // Constantes
    $this->const = array();

    // Boxes
    $this->boxes = array();

    // Permissions
    $this->rights = array();
    $this->rights_class = 'export';
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
