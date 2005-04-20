<?php
/* Copyright (C) 2005 Laurent Destailleur <eldy@users.sourceforge.net>
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
 * or see http://www.gnu.org/
 *
 * $Id$
 * $Source$
 *
 *
 * L'utilisation d'adresses de courriers �lectroniques dans les op�rations
 * de prospection commerciale est subordonn�e au recueil du consentement 
 * pr�alable des personnes concern�es.
 *
 * Le dispositif juridique applicable a �t� introduit par l'article 22 de 
 * la loi du 21 juin 2004  pour la confiance dans l'�conomie num�rique.
 *
 * Les dispositions applicables sont d�finies par les articles L. 34-5 du 
 * code des postes et des t�l�communications et L. 121-20-5 du code de la 
 * consommation. L'application du principe du consentement pr�alable en 
 * droit fran�ais r�sulte de la transposition de l'article 13 de la Directive 
 * europ�enne du 12 juillet 2002 � Vie priv�e et communications �lectroniques �. 
 *
 */

/**
       	\file       htdocs/includes/modules/mailings/pomme.modules.php
		\ingroup    mailing
		\brief      Fichier de la classe permettant de g�n�rer la liste de destinataires Pomme
		\version    $Revision$
*/

include_once DOL_DOCUMENT_ROOT.'/includes/modules/mailings/modules_mailings.php';


/**
	    \class      mailing_pomme
		\brief      Classe permettant de g�n�rer la liste des destinataires Pomme
*/

class mailing_pomme extends MailingTargets
{
    var $name='DolibarrUsers';                      // Identifiant du module mailing
    var $desc='Tous les utilisateurs avec emails de Dolibarr';  // Libell� utilis� si aucune traduction pour MailingModuleDescXXX ou XXX=name trouv�e
    var $require_module=array();                    // Module mailing actif si modules require_module actifs
    var $require_admin=1;                           // Module mailing actif pour user admin ou non
    var $picto='user';

    var $db;
    var $statssql=array();


    function mailing_pomme($DB)
    {
        global $langs;
        $langs->load("users");
        
        $this->db=$DB;

        // Liste des tableaux des stats espace mailing
        $sql = "SELECT '".$langs->trans("DolibarrUsers")."' as label, count(distinct(email)) as nb FROM ".MAIN_DB_PREFIX."user as u";
        $sql.= " WHERE u.email != ''"; // u.email IS NOT NULL est implicite dans ce test
        $this->statssql[0]=$sql;
        
    }
    
    function getNbOfRecipients()
    {
        // La requete doit retourner: nb
        $sql  = "SELECT count(distinct(u.email)) as nb";
        $sql .= " FROM ".MAIN_DB_PREFIX."user as u";
        $sql .= " WHERE u.email != ''"; // u.email IS NOT NULL est implicite dans ce test

        return parent::getNbOfRecipients($sql); 
    }
    
    function add_to_target($mailing_id)
    {
        // La requete doit retourner: email, fk_contact, name, firstname
        $sql = "SELECT u.email as email, null as fk_contact, u.name as name, u.firstname as firstname";
        $sql .= " FROM ".MAIN_DB_PREFIX."user as u";
        $sql .= " WHERE u.email != ''"; // u.email IS NOT NULL est implicite dans ce test
        $sql .= " ORDER BY u.email";

        return parent::add_to_target($mailing_id, $sql);
    }

}

?>
