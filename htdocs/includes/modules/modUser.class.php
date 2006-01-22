<?php
/* Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \defgroup   user  Module user
        \brief      Module pour g�rer les utilisateurs
*/

/**
        \file       htdocs/includes/modules/modUser.class.php
        \ingroup    user
        \brief      Fichier de description et activation du module Utilisateur
*/

include_once "DolibarrModules.class.php";

/**
        \class      modUser
        \brief      Classe de description et activation du module User
*/

class modUser extends DolibarrModules
{

   /**
    *   \brief      Constructeur. Definit les noms, constantes et boites
    *   \param      DB      handler d'acc�s base
    */
  function modUser($DB)
  {
    $this->db = $DB ;
    $this->id = 'user';   // Same value xxx than in file modXxx.class.php file
    $this->numero = 0 ;

    $this->family = "base";
    $this->name = "User";
    $this->description = "Gestion des utilisateurs (requis)";

    $this->revision = explode(' ','$Revision$');
    $this->version = $this->revision[1];

    $this->const_name = 'MAIN_MODULE_USER';
    $this->picto='group';

    // Dir
    $this->dirs = array();

    // Config pages
    // $this->config_page_url = "/user/admin/index.php";

    // D�pendances
    $this->depends = array();
    $this->requiredby = array();
    $this->langfiles = array("users","companies");

    // Constantes
    $this->const = array();

    // Boites
    $this->boxes = array();

    // Permissions
    $this->rights = array();            // L'admin b�n�ficie toujours des droits de ce module, actif ou non
    $this->rights_class = 'user';
    $r=0;
    
    $r++;
    $this->rights[$r][0] = 251;
    $this->rights[$r][1] = 'Consulter les autres utilisateurs, leurs groupes et permissions';
    $this->rights[$r][2] = 'r';
    $this->rights[$r][3] = 1;
    $this->rights[$r][4] = 'user';
    $this->rights[$r][5] = 'lire';

    $r++;
    $this->rights[$r][0] = 252;
    $this->rights[$r][1] = 'Cr�er/modifier les autres utilisateurs, leurs groupes et permissions';
    $this->rights[$r][2] = 'w';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'user';
    $this->rights[$r][5] = 'creer';

    $r++;
    $this->rights[$r][0] = 253;
    $this->rights[$r][1] = 'Modifier mot de passe des autres utilisateurs';
    $this->rights[$r][2] = 'w';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'user';
    $this->rights[$r][5] = 'password';

    $r++;
    $this->rights[$r][0] = 254;
    $this->rights[$r][1] = 'Supprimer ou d�sactiver les autres utilisateurs';
    $this->rights[$r][2] = 'd';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'user';
    $this->rights[$r][5] = 'supprimer';

    $r++;
    $this->rights[$r][0] = 255;
    $this->rights[$r][1] = 'Cr�er/modifier ses propres infos utilisateur';
    $this->rights[$r][2] = 'w';
    $this->rights[$r][3] = 1;
    $this->rights[$r][4] = 'self';
    $this->rights[$r][5] = 'supprimer';

    $r++;
    $this->rights[$r][0] = 256;
    $this->rights[$r][1] = 'Modifier son propre mot de passe';
    $this->rights[$r][2] = 'w';
    $this->rights[$r][3] = 1;
    $this->rights[$r][4] = 'self';
    $this->rights[$r][5] = 'password';

    $r++;
    $this->rights[$r][0] = 258;
    $this->rights[$r][1] = 'Exporter les utilisateurs';
    $this->rights[$r][2] = 'r';
    $this->rights[$r][3] = 0;
    $this->rights[$r][4] = 'user';
    $this->rights[$r][5] = 'export';

    // Exports
    //--------
    $r=0;

    $r++;
    $this->export_code[$r]=$this->id.'_'.$r;
    $this->export_label[$r]='Liste des utilisateurs Dolibarr et attributs';
    $this->export_fields_array[$r]=array('u.rowid'=>"Id",'u.name'=>"Lastname",'u.firstname'=>"Firstname",'u.code'=>"Code",'u.login'=>"Login",'u.datec'=>"DateCreation",'u.tms'=>"DateLastModification",'u.admin'=>"Admin",'u.fk_socpeople'=>"IdContact",'u.note'=>"Note",'u.datelastaccess'=>'DateLastAccess');
    $this->export_alias_array[$r]=array('u.rowid'=>"rowid",'u.name'=>"name",'u.firstname'=>"firstname",'u.code'=>"code",'u.login'=>"login",'u.datec'=>"datecreation",'u.tms'=>"datelastmodification",'u.admin'=>"admin",'u.fk_socpeople'=>"idcontact",'u.note'=>"note",'u.datelastaccess'=>'datelastaccess');
    $this->export_sql[$r]="select ";
    $i=0;
    foreach ($this->export_alias_array[$r] as $key => $value)
    {
        if ($i > 0) $this->export_sql[$r].=', ';
        else $i++;
        $this->export_sql[$r].=$key.' as '.$value;
    }
    $this->export_sql[$r].=' from '.MAIN_DB_PREFIX.'user as u';
    $this->export_permission[$r]=array(array("user","user","export"));

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

        $sql = array();
    
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
