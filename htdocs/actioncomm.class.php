<?php
/* Copyright (C) 2002-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * $Id$
 * $Source$
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
 */

/**
        \file       htdocs/actioncomm.class.php
        \ingroup    commercial
        \brief      Fichier de la classe des actions commerciales
        \version    $Revision$
*/


/**     \class      ActionComm
	    \brief      Classe permettant la gestion des actions commerciales
*/

class ActionComm
{
  var $id;
  var $db;

  var $date;
  var $type;
  var $priority;
  var $user;
  var $author;
  var $societe;
  var $contact;
  var $note;
  var $percent;
  var $error;

  /**
   *    \brief      Constructeur
   *    \param      db      Handler d'acc�s base de donn�e
   */
  function ActionComm($db) 
    {
      $this->db = $db;
      $this->societe = new Societe($db);
      $this->author = new User($db);
      if (class_exists("Contact"))
      {
	    $this->contact = new Contact($db);
      }
    }

  /**
   *    \brief      Ajout d'une action en base (et eventuellement dans webcalendar)
   *    \param      author      auteur de la creation de l'action
   *    \param      webcal      ressource webcalendar: 0=on oublie webcal, 1=on ajoute une entr�e g�n�rique dans webcal, objet=ajout de l'objet dans webcal
   *    \return     int         id de l'action cr��e
   */
  function add($author, $webcal=0)
    {
      global $conf;
      
      if (! $this->contact)
	{
	  $this->contact = 0;
	}
      if (! $this->propalrowid)
	{
	  $this->propalrowid = 0;
	}
      if (! $this->percent)
	{
	  $this->percent = 0;
	}
      if (! $this->priority)
	{
	  $this->priority = 0;
	}

      $sql = "INSERT INTO ".MAIN_DB_PREFIX."actioncomm (datea, label, fk_action, fk_soc, fk_user_author, fk_user_action, fk_contact, percent, note,priority,propalrowid) ";
      $sql .= " VALUES ('$this->date', '$this->libelle', $this->type, $this->societe, $author->id,";
      $sql .= $this->user->id . ", $this->contact, '$this->percent', '$this->note', $this->priority, $this->propalrowid);";
      
      if ($this->db->query($sql) )
	{
        $idaction = $this->db->last_insert_id();
        
        if ($conf->webcal->enabled) {
            if (is_object($webcal))
            {
                // Ajoute entr�e dans webcal
                $result=$webcal->add($author,$webcal->date,$webcal->texte,$webcal->desc);
                if ($result < 0) {
                    $this->error="Echec insertion dans webcal: ".$webcal->error;   
                }
            }
            else if ($webcal == 1)
            {
                // \todo On ajoute une entr�e g�n�rique, pour l'instant pas utilis�
                   
            }
        }

	    return $idaction;
	}
      else
	{
	    dolibarr_print_error($this->db);
        return -1;
	}

    }

  /**
   *    \brief      Charge l'objet action depuis la base
   *    \param      id      id de l'action a r�cup�rer
   */
  function fetch($id)
    {      
      $sql = "SELECT ".$this->db->pdate("a.datea")." as da, a.note, a.label, c.libelle, fk_soc, fk_user_author, fk_contact, fk_facture, a.percent ";
      $sql .= "FROM ".MAIN_DB_PREFIX."actioncomm as a, ".MAIN_DB_PREFIX."c_actioncomm as c WHERE a.id=$id AND a.fk_action=c.id;";

      if ($this->db->query($sql) )
	{
	  if ($this->db->num_rows())
	    {
	      $obj = $this->db->fetch_object();
	      
	      $this->id = $id;
	      $this->type = $obj->libelle;
	      $this->libelle = $obj->label;
	      $this->date = $obj->da;
	      $this->note =$obj->note;
	      $this->percent =$obj->percent;
	      $this->societe->id = $obj->fk_soc;
	      $this->author->id = $obj->fk_user_author;
	      $this->contact->id = $obj->fk_contact;
	      $this->fk_facture = $obj->fk_facture;
	      if ($this->fk_facture)
		{
		  $this->objet_url = '<a href="'. DOL_URL_ROOT . '/compta/facture.php?facid='.$this->fk_facture.'">Facture</a>';
		}
	      
	      $this->db->free();
	    }
	}
      else
	{
	  dolibarr_print_error($this->db);
	}    
    }

  /**
   *    \brief      Supprime l'action de la base
   *    \param      id      id de l'action a effacer
   *    \return     int     1 en cas de succ�s
   */
  function delete($id)
    {      
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."actioncomm WHERE id=$id;";
        
        if ($this->db->query($sql) )
        {
            return 1;
        }
    }

  /**
   *    \brief      Met a jour l'action en base
   *    \return     int     1 en cas de succ�s
   */
  function update()
    {
      if ($this->percent > 100)
	{
	  $this->percent = 100;
	}
      
      $sql = "UPDATE ".MAIN_DB_PREFIX."actioncomm ";
      $sql .= " SET percent=$this->percent";

      if ($this->percent == 100)
	{
	  $sql .= ", datea = now()";
	}

      $sql .= ", fk_contact =". $this->contact->id;

      $sql .= " WHERE id=$this->id;";
      
      if ($this->db->query($sql) )
	{
	  return 1;
	}
    }
    
}    
?>
