<?php
/* Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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

/**   	\file       htdocs/includes/modules/mailings/poire.modules.php
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
    var $name="ContactProspects";
    var $desc='Tous les contacts associ�s aux prospects';
    var $require_module=array("prospet");
    
    function mailing_cerise($DB)
    {
        $this->db=$DB;
    }
    
    function getNbOfRecipients()
    {
        $sql  = "SELECT count(distinct(c.email)) nb";
        $sql .= " FROM ".MAIN_DB_PREFIX."socpeople as c";
        $sql .= ", ".MAIN_DB_PREFIX."societe as s";
        $sql .= " WHERE s.idp = c.fk_soc";
        $sql .= " AND s.client = 2";
        $sql .= " AND c.email IS NOT NULL";

        if ( $this->db->query($sql) )
        {
            $obj = $this->db->fetch_object();
            return $obj->nb;
        }
        return 0;
    }

    function add_to_target($mailing_id)
    {
        $cibles = array();

        $sql = "SELECT distinct(c.email), c.idp, c.name, c.firstname, s.nom ";
        $sql .= " FROM ".MAIN_DB_PREFIX."socpeople as c";
        $sql .= ", ".MAIN_DB_PREFIX."societe as s";
        $sql .= " WHERE s.idp = c.fk_soc";
        $sql .= " AND s.client = 2";
        $sql .= " AND c.email IS NOT NULL";
        $sql .= " ORDER BY c.email ASC";

        if ( $this->db->query($sql) )
        {
            $num = $this->db->num_rows();
            $i = 0;
            $j = 0;

            dolibarr_syslog("mailing-prepare: mailing $num cibles trouv�es");

            $olde = '';

            while ($i < $num)
            {
                $row = $this->db->fetch_row();

                if ($olde <> $row[0])
                {
                    $cibles[$j] = $row;
                    $olde = $row[0];
                    $j++;
                }

                $i++;
            }
        }
        else
        {
            dolibarr_syslog($this->db->error());
        }

        $sql = "DELETE FROM ".MAIN_DB_PREFIX."mailing_cibles";
        $sql .= " WHERE fk_mailing = ".$mailing_id;

        if (!$this->db->query($sql))
        {
            dolibarr_syslog($this->db->error());
        }

        $num = sizeof($cibles);

        for ($i = 0 ; $i < $num ; $i++)
        {

            $sql = "INSERT INTO ".MAIN_DB_PREFIX."mailing_cibles";
            $sql .= " (fk_mailing, fk_contact, nom, prenom, email)";
            $sql .= " VALUES (".$mailing_id.",";
            $sql .=  $cibles[$i][1] .",";
            $sql .=  "'".$cibles[$i][2] ."',";
            $sql .=  "'".$cibles[$i][3] ."',";
            $sql .=  "'".$cibles[$i][0] ."')";

            if (!$this->db->query($sql))
            {
                dolibarr_syslog($this->db->error());
            }
        }

        dolibarr_syslog("mailing-prepare: mailing $i cibles ajout�es");

        $sql = "UPDATE ".MAIN_DB_PREFIX."mailing";
        $sql .= " SET nbemail = ".$i." WHERE rowid = ".$mailing_id;

        if (!$this->db->query($sql))
        {
            dolibarr_syslog($this->db->error());
        }

        return 0;

    }

}

?>
