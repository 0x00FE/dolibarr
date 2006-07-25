<?php
/* Copyright (C) 2003-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Sebastien Di Cintio  <sdicintio@ressource-toi.org>
 * Copyright (C) 2004      Benoit Mortier       <benoit.mortier@opensides.be>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
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

/**     \defgroup   commande     Module commande
        \brief      Module pour g�rer le suivi des commandes
*/

/**
        \file       htdocs/includes/modules/modCommande.class.php
        \ingroup    commande
        \brief      Fichier de description et activation du module Commande
*/

include_once "DolibarrModules.class.php";

/**     \class      modCommande
        \brief      Classe de description et activation du module Commande
*/

class modCommande extends DolibarrModules
{

   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modCommande($DB)
  {
    $this->db = $DB ;
    $this->id = 'commande';   // Same value xxx than in file modXxx.class.php file
    $this->numero = 25 ;

    $this->family = "crm";
    $this->name = "Commande";
    $this->description = "Gestion des commandes clients";
    $this->revision = explode(' ','$Revision$');
    $this->version = $this->revision[1];
    $this->const_name = 'MAIN_MODULE_COMMANDE';
    $this->special = 0;
    $this->picto='order';

    // Dir
    $this->dirs = array();

    // Config pages
    $this->config_page_url = "commande.php";

    // D�pendances
    $this->depends = array("modCommercial");
    $this->requiredby = array("modExpedition");

    // Constantes
    $this->const = array();
	$this->const[0][0] = "COMMANDE_ADDON_PDF";
    $this->const[0][1] = "chaine";
    $this->const[0][2] = "einstein";
    $this->const[0][3] = 'Nom du gestionnaire de g�n�ration des commandes en PDF';
    $this->const[0][4] = 0;
    
    $this->const[1][0] = "COMMANDE_ADDON";
    $this->const[1][1] = "chaine";
    $this->const[1][2] = "mod_commande_marbre";
    $this->const[1][3] = 'Nom du gestionnaire de num�rotation des commandes';
    $this->const[1][4] = 0;

    // Boites
    $this->boxes = array();
    $this->boxes[0][0] = "Commandes";
    $this->boxes[0][1] = "box_commandes.php";

    // Permissions
    $this->rights = array();
    $this->rights_class = 'commande';

    $this->rights[1][0] = 81;
    $this->rights[1][1] = 'Lire les commandes clients';
    $this->rights[1][2] = 'r';
    $this->rights[1][3] = 1;
    $this->rights[1][4] = 'lire';

    $this->rights[2][0] = 82;
    $this->rights[2][1] = 'Cr�er modifier les commandes clients';
    $this->rights[2][2] = 'w';
    $this->rights[2][3] = 0;
    $this->rights[2][4] = 'creer';

    $this->rights[3][0] = 84;
    $this->rights[3][1] = 'Valider les commandes clients';
    $this->rights[3][2] = 'd';
    $this->rights[3][3] = 0;    
    $this->rights[3][4] = 'valider';
    
    $this->rights[4][0] = 86;
    $this->rights[4][1] = 'Envoyer les commandes clients';
    $this->rights[4][2] = 'd';
    $this->rights[4][3] = 0;
    $this->rights[4][4] = 'envoyer';
    
    $this->rights[5][0] = 87;
    $this->rights[5][1] = 'Cl�turer les commandes clients';
    $this->rights[5][2] = 'd';
    $this->rights[5][3] = 0;
    $this->rights[5][4] = 'cloturer';
    
    $this->rights[6][0] = 88;
    $this->rights[6][1] = 'Annuler les commandes clients';
    $this->rights[6][2] = 'd';
    $this->rights[6][3] = 0;
    $this->rights[6][4] = 'annuler';

    $this->rights[7][0] = 89;
    $this->rights[7][1] = 'Supprimer les commandes clients';
    $this->rights[7][2] = 'd';
    $this->rights[7][3] = 0;
    $this->rights[7][4] = 'supprimer';

  }


   /**
    *   \brief      Fonction appel�e lors de l'activation du module. Ins�re en base les constantes, boites, permissions du module.
    *               D�finit �galement les r�pertoires de donn�es � cr�er pour ce module.
    */
  function init()
  {
    global $conf;
    
    // Permissions
    $this->remove();

    // Dir
    $this->dirs[0] = $conf->commande->dir_output;
    $this->dirs[1] = $conf->commande->dir_images;
	$sql = array(
		 "DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = '".$this->const[0][2]."'",
		 "INSERT INTO ".MAIN_DB_PREFIX."document_model (nom,type) VALUES('".$this->const[0][2]."','order')"
		 );

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
