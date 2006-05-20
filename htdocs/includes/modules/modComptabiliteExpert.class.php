<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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

/**     \defgroup   comptabilite_expert     Module comptabilite expert
        \brief      Module pour inclure des fonctions de comptabilit� (gestion de comptes comptables et rapports)
*/

/**
        \file       htdocs/includes/modules/modComptabiliteExpert.class.php
        \ingroup    comptabilite_expert
        \brief      Fichier de description et activation du module Comptabilite Expert
*/

include_once "DolibarrModules.class.php";

/**
        \class      modComptabiliteExpert
        \brief      Classe de description et activation du module Comptabilite Expert
*/

class modComptabiliteExpert extends DolibarrModules
{

   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modComptabiliteExpert($DB)
  {
    global $conf;
    
    $this->db = $DB ;
    $this->id = 'comptabiliteexpert';   // Same value xxx than in file modXxx.class.php file
    $this->numero = 130 ;
    
    $this->family = "financial";
    $this->name = "ComptabiliteExpert";
    $this->description = "Gestion expert de comptabilit� (doubles parties)";

//    $this->revision = explode(' ','$Revision$');
//    $this->version = $this->revision[1];
    $this->version = "development";

    $this->const_name = 'MAIN_MODULE_COMPTABILITE_EXPERT';
    $this->special = 0;
        
    // Config pages
    $this->config_page_url = "comptaexpert.php";

    // D�pendances
    $this->depends = array("modFacture","modBanque");
    $this->requiredby = array();
    $this->conflictwith = array("modComptabilite");
   	$this->langfiles = array("compta");

    // Constantes
    $this->const = array();

    // R�pertoires
    $this->dirs = array();
    $this->dirs[0] = $conf->comptaexpert->dir_output;
    $this->dirs[1] = $conf->comptaexpert->dir_output."/rapport";
    $this->dirs[2] = $conf->comptaexpert->dir_output."/export";
    $this->dirs[3] = $conf->comptaexpert->dir_images;

    // Boites
    $this->boxes = array();

    // Permissions
    $this->rights = array();
    $this->rights_class = 'comptaexpert';

    $this->rights[1][0] = 131;
    $this->rights[1][1] = 'Lire le plan de compte';
    $this->rights[1][2] = 'r';
    $this->rights[1][3] = 1;
    $this->rights[1][4] = 'plancompte';
    $this->rights[1][5] = 'lire';

    $this->rights[2][0] = 132;
    $this->rights[2][1] = 'Cr�er/modifier un plan de compte';
    $this->rights[2][2] = 'w';
    $this->rights[2][3] = 0;
    $this->rights[2][4] = 'plancompte';
    $this->rights[2][5] = 'creer';

    $this->rights[3][0] = 133;
    $this->rights[3][1] = 'Cloturer plan de compte';
    $this->rights[3][2] = 'w';
    $this->rights[3][3] = 0;
    $this->rights[3][4] = 'plancompte';
    $this->rights[3][5] = 'cloturer';

    $this->rights[4][0] = 141;
    $this->rights[4][1] = 'Lire les mouvements comptables';
    $this->rights[4][2] = 'r';
    $this->rights[4][3] = 1;
    $this->rights[4][4] = 'mouvements';
    $this->rights[4][5] = 'lire';

    $this->rights[5][0] = 142;
    $this->rights[5][1] = 'Cr�er/modifier/annuler les mouvements comptables';
    $this->rights[5][2] = 'w';
    $this->rights[5][3] = 0;
    $this->rights[5][4] = 'mouvements';
    $this->rights[5][5] = 'creer';

    $this->rights[6][0] = 145;
    $this->rights[6][1] = 'Lire CA, bilans, r�sultats, journaux, grands livres';
    $this->rights[6][2] = 'r';
    $this->rights[6][3] = 0;
    $this->rights[6][4] = 'comptarapport';
    $this->rights[6][5] = 'lire';

  }


   /**
    *   \brief      Fonction appel�e lors de l'activation du module. Ins�re en base les constantes, boites, permissions du module.
    *               D�finit �galement les r�pertoires de donn�es � cr�er pour ce module.
    */
  function init()
  {
    global $conf;
    
    // Nettoyage avant activation
    $this->remove();

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
