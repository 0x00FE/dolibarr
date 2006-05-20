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

/**     \defgroup   comptabilite     Module comptabilite
        \brief      Module pour inclure des fonctions de comptabilit� (gestion de comptes comptables et rapports)
*/

/**
        \file       htdocs/includes/modules/modComptabilite.class.php
        \ingroup    comptabilite
        \brief      Fichier de description et activation du module Comptabilite
*/

include_once "DolibarrModules.class.php";

/** \class modComptabilite
        \brief      Classe de description et activation du module Comptabilite
*/

class modComptabilite extends DolibarrModules
{

   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modComptabilite($DB)
  {
    global $conf;

    $this->db = $DB ;
    $this->id = 'comptabilite';   // Same value xxx than in file modXxx.class.php file
    $this->numero = 10 ;
    
    $this->family = "financial";
    $this->name = "Comptabilite";
    $this->description = "Gestion sommaire de comptabilit�";

    $this->revision = explode(" ","$Revision$");
    $this->version = $this->revision[1];

    $this->const_name = 'MAIN_MODULE_COMPTABILITE';
    $this->special = 0;
        
    // Config pages
    $this->config_page_url = "compta.php";

    // D�pendances
    $this->depends = array("modFacture","modBanque");
    $this->requiredby = array();
    $this->conflictwith = array("modComptabiliteExpert");
	$this->langfiles = array("compta");

    // Constantes
    $this->const = array();

    // R�pertoires
    $this->dirs = array();
    $this->dirs[0] = $conf->compta->dir_output;
    $this->dirs[1] = $conf->compta->dir_output."/rapport";
    $this->dirs[2] = $conf->compta->dir_output."/export";
    $this->dirs[3] = $conf->compta->dir_images;

    // Boites
    $this->boxes = array();

    // Permissions
    $this->rights = array();
    $this->rights_class = 'compta';
    $r=0;
    
    $r++;
    $this->rights[$r][0] = 91;
    $this->rights[$r][1] = 'Lire les charges';
    $this->rights[$r][2] = 'r';
    $this->rights[$r][3] = 1;
    $this->rights[$r][4] = 'charges';
    $this->rights[$r][5] = 'lire';

    $r++;
    $this->rights[$r][0] = 92;
    $this->rights[$r][1] = 'Cr�er modifier les charges';
    $this->rights[$r][2] = 'w';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'charges';
    $this->rights[$r][5] = 'creer';

    $r++;
    $this->rights[$r][0] = 93;
    $this->rights[$r][1] = 'Supprimer les charges';
    $this->rights[$r][2] = 'd';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'charges';
    $this->rights[$r][5] = 'supprimer';

    $r++;
    $this->rights[$r][0] = 95;
    $this->rights[$r][1] = 'Lire CA, bilans, r�sultats';
    $this->rights[$r][2] = 'r';
    $this->rights[$r][3] = 1;
    $this->rights[$r][4] = 'resultat';
    $this->rights[$r][5] = 'lire';

    $r++;
    $this->rights[$r][0] = 96;
    $this->rights[$r][1] = 'Param�trer la ventilation';
    $this->rights[$r][2] = 'r';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'ventilation';
    $this->rights[$r][5] = 'parametrer';

    $r++;
    $this->rights[$r][0] = 97;
    $this->rights[$r][1] = 'Lire les ventilations de factures';
    $this->rights[$r][2] = 'r';
    $this->rights[$r][3] = 1;
    $this->rights[$r][4] = 'ventilation';
    $this->rights[$r][5] = 'lire';

    $r++;
    $this->rights[$r][0] = 98;
    $this->rights[$r][1] = 'Ventiler les lignes de factures';
    $this->rights[$r][2] = 'r';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'ventilation';
    $this->rights[$r][5] = 'creer';

    /*
    Ce n'est pas un module en particulier qui doit conditionner l'acc�s � un espace 
    partag� par plusieurs module. C'est au sein de l'espace compta/tr�so que chaque zone
    est prot�g�e par le droit ad�quat.
    Sinon on bloque aussi utilisation du module banque, tva, des commandes � facturer,
    ou d'un autre module de compta.
    $r++;
    $this->rights[$r][0] = 98;
    $this->rights[$r][1] = "Acc�s � l'espace compta/tr�so";
    $this->rights[$r][2] = 'r';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'general';
    $this->rights[$r][5] = 'lire';
    */

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
