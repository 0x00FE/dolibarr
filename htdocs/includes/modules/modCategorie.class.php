<?php
/* Copyright (C) 2005 Matthieu Valleton <mv@seeschloss.org>
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
 */

/**     \defgroup   produit     Module produit
        \brief      Module pour g�rer le suivi de produits pr�d�finis
*/

/**
   \file       htdocs/includes/modules/modProduit.class.php
   \ingroup    produit
   \brief      Fichier de description et activation du module Produit
*/

include_once "DolibarrModules.class.php";

/** \class modProduit
    \brief      Classe de description et activation du module Produit
*/

class modCategorie extends DolibarrModules
{

  /**
   * \brief	Constructeur. d�finit les noms, constantes et bo�tes
   * \param	DB	handler d'acc�s base
   */
  function modCategorie ($DB)
  {
    $this->db = $DB;
    $this->numero = 1780;

    $this->family = "products";
    $this->name = "Cat�gorie";
    $this->description = "Gestion des cat�gories";
    $this->version = 'experimental';    // 'experimental' or 'dolibarr' or version
    $this->const_name = "MAIN_MODULE_CATEGORIE";
    $this->const_config = MAIN_MODULE_CATEGORIE;
    $this->special = 0;
    $this->picto = '';

    // Dir
    $this->dirs = array();

    // D�pendances
    $this->depends = array("modProduit");

    // Constantes
    $this->const = array();

    // Boxes
    $this->boxes = array();
    $this->boxes[0][0] = "Derni�res cat�gories enregistr�es";
    $this->boxes[0][1] = "box_last_cats.php";

    // Permissions
    $this->rights = array();
    $this->rights_class = 'categorie';
  }

  /**
   *   \brief      Fonction appel�e lors de l'activation du module. Ins�re en base les constantes, boites, permissions du module.
   *               D�finit �galement les r�pertoires de donn�es � cr�er pour ce module.
   */
  function init()
  {
    // Permissions
    $this->remove();

    $this->rights[0][0] = 241; // id de la permission
    $this->rights[0][1] = 'Lire les cat�gories'; // libelle de la permission
    $this->rights[0][2] = 'r'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[0][3] = 1; // La permission est-elle une permission par d�faut
    $this->rights[0][4] = 'lire';

    $this->rights[1][0] = 242; // id de la permission
    $this->rights[1][1] = 'Cr�er/modifier les cat�gories'; // libelle de la permission
    $this->rights[1][2] = 'w'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[1][3] = 0; // La permission est-elle une permission par d�faut
    $this->rights[1][4] = 'creer';

    $this->rights[2][0] = 243; // id de la permission
    $this->rights[2][1] = 'Supprimer les cat�gories'; // libelle de la permission
    $this->rights[2][2] = 'd'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[2][3] = 0; // La permission est-elle une permission par d�faut
    $this->rights[2][4] = 'supprimer';

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
