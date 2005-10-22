<?php
/* Copyright (C) 2003,2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      Jean-Louis Bergamo   <jlb@j1b.org>
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

/**
        \defgroup   adherent     Module adherents
        \brief      Module pour g�rer les adh�rents d'une association
*/

/**
        \file       htdocs/includes/modules/modAdherent.class.php
        \ingroup    adherent
        \brief      Fichier de description et activation du module adherents
*/

include_once "DolibarrModules.class.php";

/**
        \class      modAdherent
        \brief      Classe de description et activation du module Adherent
*/

class modAdherent extends DolibarrModules
{

    /**
     *   \brief      Constructeur. Definit les noms, constantes et boites
     *   \param      DB      handler d'acc�s base
     */
    function modAdherent($DB)
    {
        $this->db = $DB ;
        $this->numero = 310 ;
    
        $this->family = "hr";
        $this->name = "Adh�rents";
        $this->description = "Gestion des adh�rents d'une association";
        $this->version = 'dolibarr';                        // 'experimental' or 'dolibarr' or version
        $this->const_name = 'MAIN_MODULE_ADHERENT';
        $this->special = 1;
        $this->picto='user';
    
        // Dir
        //----
        $this->dirs = array();
    
        // Config pages
        //-------------
        $this->config_page_url = "adherent.php";
    
        // D�pendances
        //------------
        $this->depends = array();
        $this->requiredby = array();
    
        // Constantes
        //-----------
        $this->const = array();
        $this->const[0]= array("ADHERENT_MAIL_RESIL","texte","Votre adhesion sur %SERVEUR% vient d'etre resilie.\r\nNous esperons vous revoir tres bientot","Mail de r�siliation");
        $this->const[1]=array("ADHERENT_MAIL_VALID","texte","Votre adhesion vient d'etre validee. \r\nVoici le rappel de vos coordonnees (toute information erronee entrainera la non validation de votre inscription) :\r\n\r\n%INFO%\r\n\r\nVous pouvez a tout moment, grace a votre login et mot de passe, modifier vos coordonnees a l'adresse suivante : \r\n%SERVEUR%public/adherents/","Mail de validation");
        $this->const[2]= array("ADHERENT_MAIL_EDIT","texte","Voici le rappel des coordonnees que vous avez modifiees (toute information erronee entrainera la non validation de votre inscription) :\r\n\r\n%INFO%\r\n\r\nVous pouvez a tout moment, grace a votre login et mot de passe, modifier vos coordonnees a l'adresse suivante :\r\n%SERVEUR%public/adherents/","Mail d'edition");
        $this->const[3] = array("ADHERENT_MAIL_RESIL","texte","Votre adhesion sur %SERVEUR% vient d'etre resilie.\r\nNous esperons vous revoir tres bientot","Mail de r�siliation");
        $this->const[4] = array("ADHERENT_MAIL_NEW","texte","Merci de votre inscription. Votre adhesion devrait etre rapidement validee.^M\nVoici le rappel des coordonnees que vous avez rentrees (toute information erronee entrainera la non validation de votre inscription) :\r\n\r\n%INFO%\r\n\r\nVous pouvez a tout moment, grace a votre login et mot de passe, modifier vos coordonnees a l'adresse suivante :\r\n%SERVEUR%public/adherents/","Mail de nouvel inscription");
        $this->const[5] = array("ADHERENT_MAIL_VALID_SUBJECT","chaine"," Votre adh<E9>sion a ete valid<E9>e sur %SERVEUR%","sujet du mail de validation");
        $this->const[6] = array("ADHERENT_MAIL_RESIL_SUBJECT","chaine","Resiliation de votre adhesion sur %SERVEUR% ","sujet du mail de resiliation");
        $this->const[7] = array("ADHERENT_MAIL_NEW_SUBJECT","chaine","Bienvenue sur %SERVEUR%","Sujet du mail de nouvelle adhesion");
        $this->const[8] = array("ADHERENT_MAIL_EDIT_SUBJECT","chaine","Votre fiche a ete editee sur %SERVEUR%","Sujet du mail d'edition");
        $this->const[9] = array("ADHERENT_GLASNOST_SERVEUR","chaine","","serveur glasnost");
        $this->const[10] = array("ADHERENT_MAILMAN_UNSUB_URL","chaine","http://%SERVER%/cgi-bin/mailman/admin/%LISTE%/members?adminpw=%ADMINPW%&user=%EMAIL%","Url de desinscription aux listes mailman");
        $this->const[11] = array("ADHERENT_MAILMAN_URL","chaine","http://%SERVER%/cgi-bin/mailman/admin/%LISTE%/members?adminpw=%ADMINPW%&send_welcome_msg_to_this_batch=1&subscribees=%EMAIL%","url pour les inscriptions mailman");
        $this->const[12] = array("ADHERENT_MAILMAN_LISTS","chaine","","Listes auxquelles les nouveaux adh�rents sont inscris");
        $this->const[13] = array("ADHERENT_GLASNOST_USER","chaine","","Administrateur glasnost");
        $this->const[14] = array("ADHERENT_GLASNOST_PASS","chaine","","password de l'administrateur");
        $this->const[15] = array("ADHERENT_USE_GLASNOST_AUTO","yesno","","inscription automatique a glasnost ?");
        $this->const[16] = array("ADHERENT_USE_SPIP_AUTO","yesno","","Utilisation de SPIP automatiquement");
        $this->const[17] = array("ADHERENT_SPIP_USER","chaine","","Utilisateur de connection a la base spip");
        $this->const[18] = array("ADHERENT_SPIP_PASS","chaine","","Mot de passe de connection a la base spip");
        $this->const[19] = array("ADHERENT_SPIP_SERVEUR","chaine","","serveur spip");
        $this->const[20] = array("ADHERENT_SPIP_DB","chaine","","db spip");
        $this->const[21] = array("ADHERENT_MAIL_FROM","chaine","","From des mails");
        $this->const[22] = array("ADHERENT_MAIL_COTIS","texte","Bonjour %PRENOM%,^M\n^M\nCet email confirme que votre cotisation a ete recue\r\net enregistree","Mail de validation de cotisation");
        $this->const[23] = array("ADHERENT_MAIL_COTIS_SUBJECT","chaine"," Recu de votre cotisation","sujet du mail de validation de cotisation");
        $this->const[24] = array("ADHERENT_TEXT_NEW_ADH","texte","","Texte d'entete du formaulaire d'adhesion en ligne");
        $this->const[25] = array("ADHERENT_CARD_HEADER_TEXT","chaine","%ANNEE%","Texte imprime sur le haut de la carte adherent");
        $this->const[26] = array("ADHERENT_CARD_FOOTER_TEXT","chaine","Association %SERVER%","Texte imprime sur le bas de la carte adherent");
        $this->const[27] = array("ADHERENT_CARD_TEXT","texte","%PRENOM% %NOM%\r\nMembre n� %ID%\r\n%EMAIL%\r\n%ADRESSE%\r\n%CP% %VILLE%\r\n%PAYS%","Texte imprime sur la carte adherent");
        $this->const[28] = array("ADHERENT_MAILMAN_ADMINPW","chaine","","Mot de passe Admin des liste mailman");
        $this->const[29] = array("ADHERENT_MAILMAN_SERVER","chaine","","Serveur hebergeant les interfaces d'Admin des listes mailman");
        $this->const[30] = array("ADHERENT_MAILMAN_LISTS_COTISANT","chaine","","Liste(s) auxquelles les nouveaux cotisants sont inscris automatiquement");
        $this->const[31] = array("ADHERENT_BANK_USE_AUTO","yesno","","Insertion automatique des cotisation dans le compte banquaire");
        $this->const[32] = array("ADHERENT_BANK_ACCOUNT","chaine","","ID du Compte banquaire utilise");
        $this->const[33] = array("ADHERENT_BANK_CATEGORIE","chaine","","ID de la categorie banquaire des cotisations");
        $this->const[34] = array("ADHERENT_ETIQUETTE_TYPE","chaine","L7163","Type d etiquette (pour impression de planche d etiquette)");
    
        // Boites
        //-------
        $this->boxes = array();
    
        // Permissions
        //------------
        $this->rights = array();
        $this->rights_class = 'adherent';
        $r=0;

        // $this->rights[$r][0]     Id permission (unique tous modules confondus)
        // $this->rights[$r][1]     Libell� par d�faut si traduction de cl� "PermissionXXX" non trouv�e (XXX = Id permission)
        // $this->rights[$r][2]     Non utilis�
        // $this->rights[$r][3]     1=Permis par defaut, 0=Non permis par defaut
        // $this->rights[$r][4]     Niveau 1 pour nommer permission dans code
        // $this->rights[$r][5]     Niveau 2 pour nommer permission dans code
        
        $r++;
        $this->rights[$r][0] = 71;
        $this->rights[$r][1] = 'Lire les fiche adherents';
        $this->rights[$r][2] = 'r';
        $this->rights[$r][3] = 1;
        $this->rights[$r][4] = 'lire';
    
        $r++;
        $this->rights[$r][0] = 72;
        $this->rights[$r][1] = 'Cr�er/modifier les adherents';
        $this->rights[$r][2] = 'w';
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'creer';
    
        $r++;
        $this->rights[$r][0] = 74;
        $this->rights[$r][1] = 'Supprimer les adherents';
        $this->rights[$r][2] = 'd';
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'supprimer';
    
        $r++;
        $this->rights[$r][0] = 76;
        $this->rights[$r][1] = 'Exporter les adh�rents';
        $this->rights[$r][2] = 'r';
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'export';
    
        $r++;
        $this->rights[$r][0] = 75;
        $this->rights[$r][1] = 'Configurer les types et caract�ristiques des adherents';
        $this->rights[$r][2] = 'w';
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'configurer';

        $r++;
        $this->rights[$r][0] = 78;
        $this->rights[$r][1] = 'Lire les cotisations';
        $this->rights[$r][2] = 'r';
        $this->rights[$r][3] = 1;
        $this->rights[$r][4] = 'cotisation';
        $this->rights[$r][5] = 'lire';
    
        $r++;
        $this->rights[$r][0] = 79;
        $this->rights[$r][1] = 'Cr�er/modifier les cotisations';
        $this->rights[$r][2] = 'w';
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'cotisation';
        $this->rights[$r][5] = 'creer';

        // Exports
        //--------
        $r=0;

        // $this->export_code[$r]          Code unique identifiant l'export (tous modules confondus)
        // $this->export_label[$r]         Libell� par d�faut si traduction de cl� "ExportXXX" non trouv�e (XXX = Code)
        // $this->export_fields_sql[$r]    Liste des champs exportables en codif sql
        // $this->export_fields_name[$r]   Liste des champs exportables en codif traduction
        // $this->export_sql[$r]           Requete sql qui offre les donn�es � l'export
        // $this->export_permission[$r]    Liste des codes permissions requis pour faire l'export

        $r++;
        $this->export_code[$r]=$this->numero.'_'.$r;
        $this->export_label[$r]='Adh�rents et attributs';
        $this->export_fields_code[$r]=array(0=>'a.nom',1=>'a.prenom',2=>'a.login',3=>'a.cp',4=>'a.ville',5=>'a.pays',6=>'a.email',7=>'a.login',8=>'a.naiss');
        $this->export_fields_label[$r]=array(0=>"Lastname",1=>"Firstname",2=>"Address",3=>"Zip",4=>"Town",5=>"Country",6=>"Email",7=>"Login",8=>"Birthday");
        $this->export_sql[$r]="select ".join(',',$this->export_fields_code[$r]).' from '.MAIN_DB_PREFIX.'adherent';
        $this->export_permission[$r]=array(array("adherent","export"));
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
        $this->dirs[0] = $conf->adherent->dir_output;
        $this->dirs[1] = $conf->adherent->dir_output."/photos";
        $this->dirs[2] = $conf->adherent->dir_export;
        
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
