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
       	\file       htdocs/includes/modules/mailings/fraise.modules.php
		\ingroup    mailing
		\brief      Fichier de la classe permettant de g�n�rer la liste de destinataires Fraise
		\version    $Revision$
*/

include_once DOL_DOCUMENT_ROOT.'/includes/modules/mailings/modules_mailings.php';


/**
	    \class      mailing_fraise
		\brief      Classe permettant de g�n�rer la liste des destinataires Fraise
*/

class mailing_fraise extends MailingTargets
{
    var $name='MembersValidated';                   // Identifiant du module mailing
    var $desc='Tous les membres � jour';            // Libell� utilis� si aucune traduction pour MailingModuleDescXXX ou XXX=name trouv�e
    var $require_module=array('adherent');          // Module mailing actif si modules require_module actifs
    var $require_admin=0;                           // Module mailing actif pour user admin ou non
    var $picto='user';
    
    var $db;
    var $statssql=array();


    function mailing_fraise($DB)
    {
        global $langs;
        $langs->load("members");
        
        $this->db=$DB;

        // Liste des tableaux des stats espace mailing
        $this->statssql[0]="SELECT '".$langs->trans("MembersStatusValidated")."' as label, count(*) as nb FROM ".MAIN_DB_PREFIX."adherent where statut = 1";
    }
    
    function getNbOfRecipients()
    {
        // La requete doit retourner: nb
        $sql  = "SELECT count(distinct(a.email)) as nb";
        $sql .= " FROM ".MAIN_DB_PREFIX."adherent as a";
        $sql .= " WHERE a.email IS NOT NULL and statut=1";

        return parent::getNbOfRecipients($sql); 
    }
    
    function add_to_target($mailing_id)
    {
        // La requete doit retourner: email, fk_contact, name, firstname
        $sql = "SELECT a.email as email, null as fk_contact, a.nom as name, a.prenom as firstname";
        $sql .= " FROM ".MAIN_DB_PREFIX."adherent as a";
        $sql .= " WHERE a.email IS NOT NULL AND a.statut=1";
        $sql .= " ORDER BY a.email";

        return parent::add_to_target($mailing_id, $sql);
    }

}

?>
