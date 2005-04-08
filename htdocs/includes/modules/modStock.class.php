<?php
/* Copyright (C) 2003-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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

/**
        \defgroup   stock     Module stock
        \brief      Module pour g�rer la tenue de stocks produits
*/

/**
        \file       htdocs/includes/modules/modStock.class.php
        \ingroup    stock
        \brief      Fichier de description et activation du module Stock
*/

include_once "DolibarrModules.class.php";

/**
        \class      modStock
		\brief      Classe de description et activation du module Stock
*/

class modStock extends DolibarrModules
{

   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modStock($DB)
  {
    $this->db = $DB ;
    $this->numero = 52 ;

    $this->family = "products";
    $this->name = "Stock produits";
    $this->description = "Gestion des stocks";

    $this->revision = explode(" ","$Revision$");
    $this->version = $this->revision[1];

    $this->const_name = "MAIN_MODULE_STOCK";
    $this->const_config = MAIN_MODULE_STOCK;
    $this->special = 0;
    $this->picto='stock';

    // Dir
    $this->dirs = array();

    // D�pendences
    $this->depends = array("modProduit");
    $this->requiredby = array();

    // Constantes
    $this->const = array();
    
    // Boxes
    $this->boxes = array();

    // Permissions
    $this->rights = array();
    $this->rights_class = 'stock';
  }

   /**
    *   \brief      Fonction appel�e lors de l'activation du module. Ins�re en base les constantes, boites, permissions du module.
    *               D�finit �galement les r�pertoires de donn�es � cr�er pour ce module.
    */
  function init()
  {
    $this->rights[0][0] = 1001;
    $this->rights[0][1] = 'Lire les stocks';
    $this->rights[0][2] = 'r';
    $this->rights[0][3] = 1;
    $this->rights[0][4] = 'lire';
    $this->rights[0][5] = '';

    $this->rights[1][0] = 1002;
    $this->rights[1][1] = 'Cr�er/Modifier les stocks';
    $this->rights[1][2] = 'w';
    $this->rights[1][3] = 0;
    $this->rights[1][4] = 'creer';
    $this->rights[1][5] = '';

    $this->rights[2][0] = 1003;
    $this->rights[2][1] = 'Supprimer les stocks';
    $this->rights[2][2] = 'd';
    $this->rights[2][3] = 0;
    $this->rights[2][4] = 'supprimer';
    $this->rights[2][5] = '';

    $this->rights[3][0] = 1004;
    $this->rights[3][1] = 'Lire mouvements de stocks';
    $this->rights[3][2] = 'r';
    $this->rights[3][3] = 1;
    $this->rights[3][4] = 'mouvement';
    $this->rights[3][5] = 'lire';

    $this->rights[4][0] = 1005;
    $this->rights[4][1] = 'Cr�er/modifier mouvements de stocks';
    $this->rights[4][2] = 'w';
    $this->rights[4][3] = 0;
    $this->rights[4][4] = 'mouvement';
    $this->rights[4][5] = 'creer';

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
