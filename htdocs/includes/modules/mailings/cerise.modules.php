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

/**   	\file       htdocs/includes/modules/mailings/cerise.modules.php
		\ingroup    mailing
		\brief      Fichier de la classe permettant de g�n�rer la liste de destinataires Cerise
		\version    $Revision$
*/

include_once DOL_DOCUMENT_ROOT.'/includes/modules/mailings/modules_mailings.php';


/**	    \class      mailing_cerise
		\brief      Classe permettant de g�n�rer la liste des destinataires Cerise
*/

class mailing_cerise extends MailingTargets
{
    var $name="ContactProspects";                           // Identifiant du module mailing
    var $desc='Tous les contacts de toutes les soci�t�s prospects';   // Libell� utilis� si aucune traduction pour MailingModuleDescXXX ou XXX=name trouv�e
    var $require_module=array("commercial");                // Module mailing actif si modules require_module actifs
    var $require_admin=0;                                   // Module mailing actif pour user admin ou non
    var $picto='contact';

    var $db;
    var $statssql=array();

  
    function mailing_cerise($DB)
    {
        global $langs;
        $langs->load("commercial");
        
        $this->db=$DB;

        // Liste des tableaux des stats espace mailing
        $this->statssql[0]="SELECT '".$langs->trans("Prospects")."' label, count(*) nb FROM ".MAIN_DB_PREFIX."societe WHERE client = 2";
        $this->statssql[1]="SELECT '".$langs->trans("NbOfProspectsContacts")."' label, count(distinct(c.email)) nb FROM ".MAIN_DB_PREFIX."socpeople as c, ".MAIN_DB_PREFIX."societe as s WHERE s.idp = c.fk_soc AND s.client = 2 AND c.email IS NOT NULL";
    }
    
    function getNbOfRecipients()
    {
        // La requete doit retourner: nb
        $sql  = "SELECT count(distinct(c.email)) nb";
        $sql .= " FROM ".MAIN_DB_PREFIX."socpeople as c";
        $sql .= ", ".MAIN_DB_PREFIX."societe as s";
        $sql .= " WHERE s.idp = c.fk_soc";
        $sql .= " AND s.client = 2";
        $sql .= " AND c.email IS NOT NULL";

        return parent::getNbOfRecipients($sql);
    }

    function add_to_target($mailing_id)
    {
        // La requete doit retourner: email, fk_contact, name, firstname
        $sql = "SELECT c.email email, c.idp fk_contact, c.name name, c.firstname firstname";
        $sql .= " FROM ".MAIN_DB_PREFIX."socpeople as c";
        $sql .= ", ".MAIN_DB_PREFIX."societe as s";
        $sql .= " WHERE s.idp = c.fk_soc";
        $sql .= " AND s.client = 2";
        $sql .= " AND c.email IS NOT NULL";
        $sql .= " ORDER BY c.email";

        return parent::add_to_target($mailing_id, $sql);
    }

}

?>
