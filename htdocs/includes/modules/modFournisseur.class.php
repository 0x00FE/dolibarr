<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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

/**     \defgroup   fournisseur     Module fournisseur
        \brief      Module pour g�rer des soci�t�s et contacts de type fournisseurs
*/

/**
        \file       htdocs/includes/modules/modFournisseur.class.php
        \ingroup    fournisseur
        \brief      Fichier de description et activation du module Fournisseur
*/


include_once "DolibarrModules.class.php";

/**     \class      modFournisseur
		\brief      Classe de description et activation du module Fournisseur
*/

class modFournisseur extends DolibarrModules
{

   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modFournisseur($DB)
  {
    $this->db = $DB ;
    $this->numero = 40 ;

    $this->family = "products";
    $this->name = "Fournisseur";
    $this->description = "Gestion des fournisseurs";
    $this->version = 'dolibarr';                        // 'experimental' or 'dolibarr' or version
    $this->const_name = "MAIN_MODULE_FOURNISSEUR";
    $this->const_config = MAIN_MODULE_FOURNISSEUR;
    $this->special = 0;

    // Dir
    $this->dirs = array();

    // D�pendances
    $this->depends = array("modSociete");
    $this->requiredby = array();

    // Constantes
    $this->const = array();

    // Boxes
    $this->boxes = array();
    $this->boxes[0][0] = "Derniers founisseurs";
    $this->boxes[0][1] = "box_fournisseurs.php";

    // Permissions
    $this->rights = array();
    $this->rights_class = 'fournisseur';
  }

   /**
    *   \brief      Fonction appel�e lors de l'activation du module. Ins�re en base les constantes, boites, permissions du module.
    *               D�finit �galement les r�pertoires de donn�es � cr�er pour ce module.
    */
  function init()
  {
    $this->remove();

    $this->rights[0][0] = 170; // id de la permission
    $this->rights[0][1] = 'Tous les droits sur les fournisseurs'; // libelle de la permission
    $this->rights[0][2] = 'a'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[0][3] = 0; // La permission est-elle une permission par d�faut

    $this->rights[1][0] = 180; // id de la permission
    $this->rights[1][1] = 'Tous les droits sur les commandes fournisseurs'; // libelle de la permission
    $this->rights[1][2] = 'a'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[1][3] = 0; // La permission est-elle une permission par d�faut

    $this->rights[2][0] = 181;
    $this->rights[2][1] = 'Lire les commandes fournisseur';
    $this->rights[2][2] = 'w';
    $this->rights[2][3] = 1;
    $this->rights[2][4] = 'commande';
    $this->rights[2][5] = 'lire';

    $this->rights[3][0] = 182;
    $this->rights[3][1] = 'Cr�er une commande fournisseur';
    $this->rights[3][2] = 'w';
    $this->rights[3][3] = 0;
    $this->rights[3][4] = 'commande';
    $this->rights[3][5] = 'creer';

    $this->rights[4][0] = 183;
    $this->rights[4][1] = 'Valider une commande fournisseur';
    $this->rights[4][2] = 'w';
    $this->rights[4][3] = 0;
    $this->rights[4][4] = 'commande';
    $this->rights[4][5] = 'valider';

    $this->rights[5][0] = 184;
    $this->rights[5][1] = 'Approuver les commandes fournisseur';
    $this->rights[5][2] = 'w';
    $this->rights[5][3] = 0;
    $this->rights[5][4] = 'commande';
    $this->rights[5][5] = 'approuver';

    $this->rights[6][0] = 185;
    $this->rights[6][1] = 'Commander une commande fournisseur';
    $this->rights[6][2] = 'w';
    $this->rights[6][3] = 0;
    $this->rights[6][4] = 'commande';
    $this->rights[6][5] = 'commander';

    $this->rights[7][0] = 186;
    $this->rights[7][1] = 'Clot�rer les commandes fournisseur';
    $this->rights[7][2] = 'w';
    $this->rights[7][3] = 0;
    $this->rights[7][4] = 'commande';
    $this->rights[7][5] = 'cloturer';

    $sql = array();

    $this->load_datas();
  
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

  /**
   *    \brief      Fonction appel� par l'init (donc lors de l'activation d'un module)
   *
   */
  function load_datas()
  {
    $sql = "SELECT count(rowid) FROM ".MAIN_DB_PREFIX."c_methode_commande_fournisseur";

    if ($this->db->query($sql))
      {
	$row = $this->db->fetch_row();

	if ($row[0] == 0)
	  {
	    $this->db->free();

	    $sql = "INSERT INTO ".MAIN_DB_PREFIX."c_methode_commande_fournisseur";
	    $sql .= " (code,libelle) VALUES ('OrderByMail','Courrier')";

	    $this->db->query($sql);

	    $sql = "INSERT INTO ".MAIN_DB_PREFIX."c_methode_commande_fournisseur";
	    $sql .= " (code,libelle) VALUES ('OrderByFax','Fax')";

	    $this->db->query($sql);

	    $sql = "INSERT INTO ".MAIN_DB_PREFIX."c_methode_commande_fournisseur";
	    $sql .= " (code,libelle) VALUES ('OrderByEMail','EMail')";

	    $this->db->query($sql);

	    $sql = "INSERT INTO ".MAIN_DB_PREFIX."c_methode_commande_fournisseur";
	    $sql .= " (code,libelle) VALUES ('OrderByPhone','T�l�phone')";

	    $this->db->query($sql);

	    $sql = "INSERT INTO ".MAIN_DB_PREFIX."c_methode_commande_fournisseur";
	    $sql .= " (code,libelle) VALUES ('OrderByWWW','En ligne')";

	    $this->db->query($sql);
	  }
      }
  }
}
?>
