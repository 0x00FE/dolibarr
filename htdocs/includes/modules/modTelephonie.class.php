<?php
/* Copyright (C) 2004-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
        \defgroup   telephonie  Module telephonie
        \brief      Module pour g�rer la t�l�phonie
*/

/**
        \file       htdocs/includes/modules/modTelephonie.class.php
        \ingroup    telephonie
        \brief      Fichier de description et activation du module de T�l�phonie
*/

include_once "DolibarrModules.class.php";

/**
        \class      modTelephonie
        \brief      Classe de description et activation du module Telephonie
*/

class modTelephonie extends DolibarrModules
{

   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modTelephonie($DB)
  {
    $this->db = $DB ;
    $this->id = 'telephonie';   // Same value xxx than in file modXxx.class.php file
    $this->numero = 56 ;

    $this->family = "technic";
    $this->name = "Telephonie";
    $this->description = "Gestion de la Telephonie";

    $this->revision = explode(" ","$Revision$");
    $this->version = $this->revision[1];

    $this->const_name = "MAIN_MODULE_TELEPHONIE";
    $this->const_config = MAIN_MODULE_TELEPHONIE;
    $this->special = 1;
    $this->picto='phoning';

    // Dir
    $this->dirs = array();

    // D�pendances
    $this->depends = array();
    $this->requiredby = array();

    // Constantes
    $this->const = array();

    // Boites
    $this->boxes = array();

    // Permissions
    $this->rights = array();
    $this->rights_class = 'telephonie';
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

    $this->rights[1][0] = 141; // id de la permission
    $this->rights[1][1] = 'Consulter la telephonie'; // libelle de la permission
    $this->rights[1][2] = 'r'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[1][3] = 1; // La permission est-elle une permission par d�faut
    $this->rights[1][4] = 'lire';

    $this->rights[2][0] = 142; // id de la permission
    $this->rights[2][1] = 'Commander les lignes'; // libelle de la permission
    $this->rights[2][2] = 'w'; // type de la permission (d�pr�ci� � ce jour)
    $this->rights[2][3] = 0; // La permission est-elle une permission par d�faut
    $this->rights[2][4] = 'ligne_commander';

    $this->rights[3][0] = 143;
    $this->rights[3][1] = 'Activer une ligne';
    $this->rights[3][2] = 'w';
    $this->rights[3][3] = 0;
    $this->rights[3][4] = 'ligne_activer';

    $this->rights[4][0] = 144; // id de la permission
    $this->rights[4][1] = 'Configurer la telephonie'; // libelle de la permission
    $this->rights[4][2] = 'w';
    $this->rights[4][3] = 0;

    $this->rights[5][0] = 145;
    $this->rights[5][1] = 'Configurer les fournisseurs';
    $this->rights[5][2] = 'w';
    $this->rights[5][3] = 0;
    $this->rights[5][4] = 'fournisseur';
    $this->rights[5][5] = 'config';

    $this->rights[6][0] = 192;
    $this->rights[6][1] = 'Cr�er des lignes';
    $this->rights[6][2] = 'w';
    $this->rights[6][3] = 0;
    $this->rights[6][4] = 'ligne';
    $this->rights[6][5] = 'creer';

    $this->rights[7][0] = 202;
    $this->rights[7][1] = 'Cr�er des liaisons ADSL';
    $this->rights[7][2] = 'w';
    $this->rights[7][3] = 0;
    $this->rights[7][4] = 'adsl';
    $this->rights[7][5] = 'creer';

    $this->rights[8][0] = 203;
    $this->rights[8][1] = "Demander la commande des liaisons";
    $this->rights[8][2] = 'w';
    $this->rights[8][3] = 0;
    $this->rights[8][4] = 'adsl';
    $this->rights[8][5] = 'requete';

    $this->rights[9][0] = 204;
    $this->rights[9][1] = 'Commander les liaisons';
    $this->rights[9][2] = 'w';
    $this->rights[9][3] = 0;
    $this->rights[9][4] = 'adsl';
    $this->rights[9][5] = 'commander';

    $this->rights[10][0] = 205;
    $this->rights[10][1] = 'G�rer les liaisons';
    $this->rights[10][2] = 'w';
    $this->rights[10][3] = 0;
    $this->rights[10][4] = 'adsl';
    $this->rights[10][5] = 'gerer';
    $r = 10;

    $r++;


    $this->rights[$r][0] = 271;
    $this->rights[$r][1] = 'Consulter le CA';
    $this->rights[$r][2] = 'w';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'ca';
    $this->rights[$r][5] = 'lire';
    $r++;

    $this->rights[$r][0] = 272;
    $this->rights[$r][1] = 'Consulter les factures';
    $this->rights[$r][2] = 'r';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'facture';
    $this->rights[$r][5] = 'lire';
    $r++;

    $this->rights[$r][0] = 206;
    $this->rights[$r][1] = 'Consulter les liaisons';
    $this->rights[$r][2] = 'w';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'adsl';
    $this->rights[$r][5] = 'lire';
    $r++;

    $this->rights[$r][0] = 215;
    $this->rights[$r][1] = 'D�finir le mode de r�glement';
    $this->rights[$r][2] = 'w';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'contrat';
    $this->rights[$r][5] = 'paiement';
    $r++;

    $this->rights[$r][0] = 193;
    $this->rights[$r][1] = 'R�silier des lignes';
    $this->rights[$r][2] = 'w';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'ligne';
    $this->rights[$r][5] = 'resilier';
    $r++;

    $this->rights[$r][0] = 194;
    $this->rights[$r][1] = 'Consulter la marge des lignes';
    $this->rights[$r][2] = 'r';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'ligne';
    $this->rights[$r][5] = 'gain';
    $r++;

    $this->rights[$r][0] = 146;
    $this->rights[$r][1] = 'Consulter les fournisseurs';
    $this->rights[$r][2] = 'w';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'fournisseur';
    $this->rights[$r][5] = 'lire';
    $r++;

    $this->rights[$r][0] = 147;
    $this->rights[$r][1] = 'Consulter les stats';
    $this->rights[$r][2] = 'w';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'stats';
    $this->rights[$r][5] = 'lire';
    $r++;

    $this->rights[$r][0] = 281;
    $this->rights[$r][1] = 'Consulter les services';
    $this->rights[$r][2] = 'w';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'service';
    $this->rights[$r][5] = 'lire';
    $r++;

    $this->rights[$r][0] = 282;
    $this->rights[$r][1] = 'Affecter des services � un contrat';
    $this->rights[$r][2] = 'w';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'service';
    $this->rights[$r][5] = 'affecter';
    $r++;

    $this->rights[$r][0] = 291;
    $this->rights[$r][1] = 'Consulter les tarifs';
    $this->rights[$r][2] = 'w';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'tarifs';
    $this->rights[$r][5] = 'lire';
    $r++;

    $this->rights[$r][0] = 292;
    $this->rights[$r][1] = 'Definir les permissions sur les tarifs';
    $this->rights[$r][2] = 'w';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'tarif';
    $this->rights[$r][5] = 'permission';
    $r++;

    $this->rights[$r][0] = 293;
    $this->rights[$r][1] = 'Modifier les tarifs clients';
    $this->rights[$r][2] = 'w';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'tarif';
    $this->rights[$r][5] = 'client_modifier';
    $r++;


    // Dir
    $this->dirs[0] = $conf->telephonie->dir_output;
    $this->dirs[1] = $conf->telephonie->dir_output."/ligne";
    $this->dirs[2] = $conf->telephonie->dir_output."/ligne/commande" ;	 
    $this->dirs[3] = $conf->telephonie->dir_output."/logs" ;
    $this->dirs[4] = $conf->telephonie->dir_output."/client" ;
    $this->dirs[5] = $conf->telephonie->dir_output."/rapports" ;
    $this->dirs[6] = $conf->telephonie->dir_output."/ligne/commande/retour" ;
    
    return $this->_init($sql);
  }

  /**
    \brief      Fonction appel�e lors de la d�sactivation d'un module.
    Supprime de la base les constantes, boites et permissions du module.
   */
  function remove()
  {
    $sql = array();

    return $this->_remove($sql);
  }
}
?>
