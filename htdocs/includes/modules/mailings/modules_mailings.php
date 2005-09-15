<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 * or see http://www.gnu.org/
 *
 * $Id$
 * $Source$
 *
 */

/**
	    \file       htdocs/includes/modules/mailings/modules_mailings.php
		\ingroup    mailing
		\brief      Fichier contenant la classe m�re des classes de liste de destinataires mailing
		\version    $Revision$
*/


/**
	    \class      MailingTargets
		\brief      Classe m�re des classes de liste de destinataires mailing
*/

class MailingTargets
{
    var $db='';
    var $error='';

    function MailingTargets($DB)
    {
        $this->db=$DB;
    }
    
    /**     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Retourne la traduction de la cl� MailingModuleDescXXX ou XXX nom du module, ou $this->desc si non trouv�
     */
    function getDesc()
    {
        global $langs;
        $langs->load("mails");
        $transstring="MailingModuleDesc".$this->name;
        if ($langs->trans($transstring) != $transstring) return $langs->trans($transstring); 
        else return $this->desc;
    }

    /**     \brief      Renvoi un exemple de num�rotation
     *      \return     string      Example
     */
    function getNbOfRecords()
    {
        return 0;
    }

    /**
     *    \brief      Retourne nombre de destinataires
     *    \param      sql         Requete sql de comptage
     *    \return     int         Nb de destinataires si ok, < 0 si erreur
     */
    function getNbOfRecipients($sql)
    {
        $result=$this->db->query($sql);
        if ($result)
        {
            $obj = $this->db->fetch_object($result);
            return $obj->nb;
        }
        else
        {
            return -1;
        }
    }

    /**
     *      \brief      Affiche formulaire de filtre qui apparait dans page de selection
     *                  des destinataires de mailings
     *      \return     string      Retourne zone select
     */
    function formFilter()
    {
        return '&nbsp;';
    }
    
    /**
     *      \brief      Met a jour nombre de destinataires
     *      \param      mailing_id          Id du mailing concern�
     *      \return     int                 < 0 si erreur, nb destinataires si ok
     */
    function update_nb($mailing_id)
    {
        // Mise a jour nombre de destinataire dans table des mailings
        $sql = "SELECT COUNT(*) nb FROM ".MAIN_DB_PREFIX."mailing_cibles";
        $sql .= " WHERE fk_mailing = ".$mailing_id;
        $result=$this->db->query($sql);
        if ($result)
        {
            $obj=$this->db->fetch_object($result);
            $nb=$obj->nb;
                    
            $sql = "UPDATE ".MAIN_DB_PREFIX."mailing";
            $sql .= " SET nbemail = ".$nb." WHERE rowid = ".$mailing_id;
            if (!$this->db->query($sql))
            {
                dolibarr_syslog($this->db->error());
                $this->error=$this->db->error();
                return -1;
            }
        }
        else {
            return -1;
        }
        return $nb;
    }

    /**
     *    \brief      Ajoute destinataires dans table des cibles
     *    \param      mailing_id    Id du mailing concern�
     *    \param      sql           Requete sql de selection des destinataires
     *    \return     int           < 0 si erreur, nb ajout si ok
     */
    function add_to_target($mailing_id, $sql)
    {
        $cibles = array();

        // Stocke destinataires dans cibles
        $result=$this->db->query($sql);
        if ($result)
        {
            $num = $this->db->num_rows($result);
            $i = 0;
            $j = 0;

            dolibarr_syslog("mailing-prepare: mailing $num cibles trouv�es");

            $old = '';
            while ($i < $num)
            {
                $obj = $this->db->fetch_object($result);
                if ($old <> $obj->email)
                {
                    $cibles[$j] = array($obj->email,$obj->fk_contact,$obj->name,$obj->firstname,$this->url($obj->id));
                    $old = $obj->email;
                    $j++;
                }

                $i++;
            }
        }
        else
        {
            dolibarr_syslog($this->db->error());
            $this->error=$this->db->error();
            return -1;
        }

        $this->db->begin();
        
        // Ins�re destinataires de cibles dans table
        $j = 0;
        $num = sizeof($cibles);
        for ($i = 0 ; $i < $num ; $i++)
        {
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."mailing_cibles";
            $sql .= " (fk_mailing, ";
            if ($cibles[$i][1]) $sql .= "fk_contact, ";
            $sql .= "nom, prenom, email, url)";
            $sql .= " VALUES (".$mailing_id.",";
            if ($cibles[$i][1]) $sql .=  $cibles[$i][1] .",";
            $sql .=  "'".addslashes($cibles[$i][2])."',";
            $sql .=  "'".addslashes($cibles[$i][3])."',";
            $sql .=  "'".addslashes($cibles[$i][0])."',";
            $sql .=  "'".addslashes($cibles[$i][4])."')";

            $result=$this->db->query($sql);
            if ($result)
            {
                $j++;
            }
            else
            {
                if ($this->db->errno() != 'DB_ERROR_RECORD_ALREADY_EXISTS')
                {
                    // Si erreur autre que doublon
                    dolibarr_syslog($this->db->error());
                    $this->error=$this->db->error();
                    $this->db->rollback();
                    return -1;
                }
            }
        }

        dolibarr_syslog("mailing-prepare: mailing $j cibles ajout�es");

        $this->update_nb($mailing_id);

        $this->db->commit();
        return $j;
    }

    /**
     *    \brief      Supprime tous les destinataires de la table des cibles
     *    \param      mailing_id        Id du mailing concern�
     */
    function clear_target($mailing_id)
    {
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."mailing_cibles";
        $sql .= " WHERE fk_mailing = ".$mailing_id;

        if (! $this->db->query($sql))
        {
            dolibarr_syslog($this->db->error());
        }

        $this->update_nb($mailing_id);
    }

}

?>
