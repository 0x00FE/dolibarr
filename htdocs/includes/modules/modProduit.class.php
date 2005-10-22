<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Sebastien Di Cintio  <sdicintio@ressource-toi.org>
 * Copyright (C) 2004      Benoit Mortier       <benoit.mortier@opensides.be>
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
        \defgroup   produit     Module produit
        \brief      Module pour g�rer le suivi de produits pr�d�finis
*/

/**
        \file       htdocs/includes/modules/modProduit.class.php
        \ingroup    produit
        \brief      Fichier de description et activation du module Produit
*/

include_once "DolibarrModules.class.php";

/**
        \class      modProduit
		\brief      Classe de description et activation du module Produit
*/

class modProduit extends DolibarrModules
{

   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modProduit($DB)
  {
    $this->db = $DB ;
    $this->numero = 50 ;
    
    $this->family = "products";
    $this->name = "Produit";
    $this->description = "Gestion des produits";

    $this->revision = explode(' ','$Revision$');
    $this->version = $this->revision[1];

    $this->const_name = 'MAIN_MODULE_PRODUIT';
    $this->special = 0;
    $this->picto='product';

    // Dir
    $this->dirs = array();

    // D�pendances
    $this->depends = array();
	$this->requiredby = array("modStock","modService","modBarcode");
    $this->config_page_url = "produit.php";
	
    // Constantes
    $this->const = array();

    // Boxes
    $this->boxes = array();
    $this->boxes[0][0] = "Derniers produits/services enregistr�s";
    $this->boxes[0][1] = "box_produits.php";
    $this->boxes[1][0] = "Derniers produits/services vendus";
    $this->boxes[1][1] = "box_services_vendus.php";

    // Permissions
    $this->rights = array();
    $this->rights_class = 'produit';
    
    $this->rights[1][0] = 31; // id de la permission
    $this->rights[1][1] = 'Lire les produits/services'; // libelle de la permission
    $this->rights[1][2] = 'r'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[1][3] = 1; // La permission est-elle une permission par d�faut
    $this->rights[1][4] = 'lire';

    $this->rights[2][0] = 32; // id de la permission
    $this->rights[2][1] = 'Cr�er modifier les produits/services'; // libelle de la permission
    $this->rights[2][2] = 'w'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[2][3] = 0; // La permission est-elle une permission par d�faut
    $this->rights[2][4] = 'creer';

    $this->rights[3][0] = 33; // id de la permission
    $this->rights[3][1] = 'Commander les produits/services'; // libelle de la permission
    $this->rights[3][2] = 'w'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[3][3] = 0; // La permission est-elle une permission par d�faut
    $this->rights[3][4] = 'commander';

    $this->rights[4][0] = 34; // id de la permission
    $this->rights[4][1] = 'Supprimer les produits/services'; // libelle de la permission
    $this->rights[4][2] = 'd'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[4][3] = 0; // La permission est-elle une permission par d�faut
    $this->rights[4][4] = 'supprimer';

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
