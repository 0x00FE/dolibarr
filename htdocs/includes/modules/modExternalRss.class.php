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

/*!     \defgroup   externalrss     Module ExternalRss
        \brief      Module pour inclure des informations externes RSS
*/

/*!
        \file       htdocs/includes/modules/modExternalRss.class.php
        \ingroup    externalrss
        \brief      Fichier de description et activation du module ExternalRss
*/

include_once "DolibarrModules.class.php";

/*! \class modExternalRss
		\brief      Classe de description et activation du module ExternalRss
*/

class modExternalRss extends DolibarrModules
{

  /*
   * Initialisation
   *
   */

  function modExternalRss($DB)
  {
    $this->db = $DB ;
    $this->numero = 320;

    $this->family = "technic";
    $this->name = "Syndication RSS";
    $this->description = "Ajout de files d'informations RSS dans les �crans Dolibarr";
    $this->const_name = "MAIN_MODULE_EXTERNALRSS";
    $this->const_config = MAIN_MODULE_EXTERNALRSS;

    // Config pages
    $this->config_page_url = array("external_rss.php");

    // D�pendances
    $this->depends = array();
    $this->requiredby = array();

    $this->const = array();
    $this->boxes = array();
    /*
     * Boites
     */
    $this->boxes[0][0] = "Informations externes RSS";
    $this->boxes[0][1] = "box_external_rss.php";

  }
  /*
   *
   *
   *
   */

  function init()
  {
    /*
     *  Activation du module
     */
    
    $sql = array();
		
    return $this->_init($sql);
  }
  /*
   *
   *
   */
  function remove()
  {
    $sql = array();

    return $this->_remove($sql);
  }
}
?>
