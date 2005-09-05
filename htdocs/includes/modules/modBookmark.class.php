<?php
/* Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
        \defgroup   bookmark    Module Bookmark
        \brief      Module pour g�rer l'addon Bookmark
*/

/**
        \file       htdocs/includes/modules/modBookmark.class.php
        \ingroup    bookmark
        \brief      Fichier de description et activation du module Bookmark
*/

include_once "DolibarrModules.class.php";

/** 
    \class      modBookmark
    \brief      Classe de description et activation du module Bookmark
*/

class modBookmark extends DolibarrModules
{

   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modBookmark($DB)
  {
    $this->db = $DB ;
    $this->numero = 67 ;

    $this->family = "technic";
    $this->name = "Bookmark";
    $this->description = "Gestion des Bookmarks";
    $this->revision = explode(" ","$Revision$");
    $this->version = $this->revision[1]."(DEV)";

    $this->const_name = "MAIN_MODULE_BOOKMARK";
    $this->const_config = MAIN_MODULE_BOOKMARK;
    $this->special = 1;
    $this->picto='bookmark';

    // Dir
    $this->dirs = array();

    // D�pendances
    $this->depends = array();
    $this->requiredby = array();

    // Config pages
    //$this->config_page_url = "";

    // Constantes
    $this->const = array();

    // Boites
    $this->boxes = array();

    // Permissions
    $this->rights = array();
    $this->rights_class = 'bookmark';
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
