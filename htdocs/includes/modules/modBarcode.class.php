<?php
/* Copyright (C) 2005      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005      Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005      Regis Houssin        <regis.houssin@cap-networks.com>
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
        \defgroup   barcode         Module code barre
        \brief      Module pour g�rer les codes barres des produits
*/

/**
        \file       htdocs/includes/modules/modBarcode.class.php
        \ingroup    barcode,produit
        \brief      Fichier de description et activation du module Barcode
*/

include_once "DolibarrModules.class.php";

/**
        \class      modBarcode
		\brief      Classe de description et activation du module Barcode
*/

class modBarcode extends DolibarrModules
{

   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modBarcode($DB)
  {
    $this->db = $DB ;
    $this->id = 'barcode';   // Same value xxx than in file modXxx.class.php file
    $this->numero = 55 ;
    
    $this->family = "products";
    $this->name = "Codes barres";
    $this->description = "Gestion des codes barres des produits";

    //$this->revision = explode(' ','$Revision$');
    //$this->version = $this->revision[1];
    $this->version = 'experimental';    // 'experimental' or 'dolibarr' or version

    $this->const_name = 'MAIN_MODULE_BARCODE';
    $this->special = 0;
    $this->picto='barcode';

    // Dir
    $this->dirs = array();

    // D�pendances
    $this->depends = array("modProduit");
	$this->requiredby = array();
	
	  // Config pages
    $this->config_page_url = "barcode.php";
	
    // Constantes
    $this->const = array();
    
    $this->const[0][0] = "BARCODE_ENCODE_TYPE";
    $this->const[0][1] = "chaine";
    $this->const[0][2] = "EAN13";

    // Boxes
    $this->boxes = array();
    // $this->boxes[0][0] = "Derniers produits/services enregistr�s";
    // $this->boxes[0][1] = "box_produits.php";
    // $this->boxes[1][0] = "Derniers produits/services vendus";
    // $this->boxes[1][1] = "box_services_vendus.php";

    // Permissions
    $this->rights = array();
    $this->rights_class = 'barcode';
    
    $this->rights[1][0] = 300; // id de la permission
    $this->rights[1][1] = 'Lire les codes barres'; // libelle de la permission
    $this->rights[1][2] = 'r'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[1][3] = 1; // La permission est-elle une permission par d�faut
    $this->rights[1][4] = 'lire';

    $this->rights[2][0] = 301; // id de la permission
    $this->rights[2][1] = 'Cr�er/modifier les codes barres'; // libelle de la permission
    $this->rights[2][2] = 'w'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[2][3] = 0; // La permission est-elle une permission par d�faut
    $this->rights[2][4] = 'creer';

    $this->rights[4][0] = 302; // id de la permission
    $this->rights[4][1] = 'Supprimer les codes barres'; // libelle de la permission
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
