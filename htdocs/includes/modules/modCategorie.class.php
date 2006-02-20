<?php
/* Copyright (C) 2005      Matthieu Valleton    <mv@seeschloss.org>
 * Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \defgroup   categorie       Module categorie
        \brief      Module pour g�rer les cat�gories
*/

/**
        \file       htdocs/includes/modules/modCategorie.class.php
        \ingroup    categorie
        \brief      Fichier de description et activation du module Categorie
*/

include_once "DolibarrModules.class.php";

/**
        \class      modProduit
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
    $this->id = 'categorie';   // Same value xxx than in file modXxx.class.php file
    $this->numero = 1780;

    $this->family = "products";
    $this->name = "Cat�gorie";
    $this->description = "Gestion des cat�gories de produits";
    $this->version = 'experimental';    // 'development' or 'experimental' or 'dolibarr' or version
    $this->const_name = 'MAIN_MODULE_CATEGORIE';
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

    // Permissions
    $this->rights = array();
    $this->rights_class = 'categorie';

    $r=0;

    $this->rights[$r][0] = 241; // id de la permission
    $this->rights[$r][1] = 'Lire les cat�gories'; // libelle de la permission
    $this->rights[$r][2] = 'r'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[$r][3] = 1; // La permission est-elle une permission par d�faut
    $this->rights[$r][4] = 'lire';
    $r++;

    $this->rights[$r][0] = 242; // id de la permission
    $this->rights[$r][1] = 'Cr�er/modifier les cat�gories'; // libelle de la permission
    $this->rights[$r][2] = 'w'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[$r][3] = 0; // La permission est-elle une permission par d�faut
    $this->rights[$r][4] = 'creer';
	$r++;
	
    $this->rights[$r][0] = 243; // id de la permission
    $this->rights[$r][1] = 'Supprimer les cat�gories'; // libelle de la permission
    $this->rights[$r][2] = 'd'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[$r][3] = 0; // La permission est-elle une permission par d�faut
    $this->rights[$r][4] = 'supprimer';
	$r++;
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
