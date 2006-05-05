<?php
/* Copyright (C) 2003-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
 */

/**
        \defgroup   expedition     Module expedition
        \brief      Module pour g�rer les expeditions de produits
*/

/**
        \file       htdocs/includes/modules/modExpedition.class.php
        \ingroup    expedition
        \brief      Fichier de description et activation du module Expedition
*/

include_once "DolibarrModules.class.php";

/** \class modExpedition
		\brief      Classe de description et activation du module Expedition
*/

class modExpedition extends DolibarrModules
{

   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modExpedition($DB)
  {
    $this->db = $DB ;
    $this->id = 'expedition';   // Same value xxx than in file modXxx.class.php file
    $this->numero = 80 ;

    $this->family = "crm";
    $this->name = "Expedition";
    $this->description = "Gestion des exp�ditions";
    $this->revision = explode(' ','$Revision$');
    $this->version = $this->revision[1];
    $this->const_name = 'MAIN_MODULE_EXPEDITION';
    $this->special = 0;
    $this->picto = "sending";

    // Dir
    $this->dirs = array();

    // Config pages
    $this->config_page_url = "expedition.php";

    // D�pendances
    $this->depends = array("modCommande");
    $this->requiredby = array();

    // Constantes
    $this->const = array();
	  $this->const[0][0] = "LIVRAISON_ADDON_PDF";
    $this->const[0][1] = "chaine";
    $this->const[0][2] = "typhon";
    $this->const[0][3] = 'Nom du gestionnaire de g�n�ration des commandes en PDF';
    $this->const[0][4] = 0;
    
    $this->const[1][0] = "LIVRAISON_ADDON";
    $this->const[1][1] = "chaine";
    $this->const[1][2] = "mod_livraison_jade";
    $this->const[1][3] = 'Nom du gestionnaire de num�rotation des bons de livraison';
    $this->const[1][4] = 0;
    
    // Boxes
    $this->boxes = array();

    // Permissions
    $this->rights = array();
    $this->rights_class = 'expedition';
  
    $this->rights[1][0] = 101;
    $this->rights[1][1] = 'Lire les expeditions';
    $this->rights[1][2] = 'r';
    $this->rights[1][3] = 1;
    $this->rights[1][4] = 'lire';

    $this->rights[2][0] = 102;
    $this->rights[2][1] = 'Cr�er modifier les expeditions';
    $this->rights[2][2] = 'w';
    $this->rights[2][3] = 0;
    $this->rights[2][4] = 'creer';

    $this->rights[3][0] = 104;
    $this->rights[3][1] = 'Valider les expeditions';
    $this->rights[3][2] = 'd';
    $this->rights[3][3] = 0;    
    $this->rights[3][4] = 'valider';

    $this->rights[4][0] = 109;
    $this->rights[4][1] = 'Supprimer les expeditions';
    $this->rights[4][2] = 'd';
    $this->rights[4][3] = 0;
    $this->rights[4][4] = 'supprimer';
    
    $this->rights[1][0] = 1101;
    $this->rights[1][1] = 'Lire les bons de livraison';
    $this->rights[1][2] = 'r';
    $this->rights[1][3] = 1;
    $this->rights[1][4] = 'lire';

    $this->rights[2][0] = 1102;
    $this->rights[2][1] = 'Cr�er modifier les bons de livraison';
    $this->rights[2][2] = 'w';
    $this->rights[2][3] = 0;
    $this->rights[2][4] = 'creer';

    $this->rights[3][0] = 1104;
    $this->rights[3][1] = 'Valider les bons de livraison';
    $this->rights[3][2] = 'd';
    $this->rights[3][3] = 0;    
    $this->rights[3][4] = 'valider';

    $this->rights[4][0] = 1109;
    $this->rights[4][1] = 'Supprimer les bons de livraison';
    $this->rights[4][2] = 'd';
    $this->rights[4][3] = 0;
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

    // Dir
    $this->dirs[0] = DOL_DATA_ROOT."/expedition";

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
