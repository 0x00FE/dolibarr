<?php
/* Copyright (C) 2005      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2007 Laurent Destailleur  <eldy@users.sourceforge.net>
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
		\file       htdocs/discount.class.php
		\ingroup    propal,facture,commande
		\brief      Fichier de la classe de gestion des remises
		\version    $Revision$
*/


/**
		\class      DiscountAbsolute
		\brief      Classe permettant la gestion des remises fixes
*/

class DiscountAbsolute
{
	var $db;
	var $error;
	
	var $id;					// Id remise
	var $amount_ht;				//
	var $amount_tva;			//
	var $amount_ttc;			//
	var $tva_tx;				//
	var $fk_user;				// Id utilisateur qui accorde la remise
	var $description;			// Description libre
	var $datec;					// Date creation
	var $fk_facture;			// Id facture qd une remise a �t� utilis�
	var $fk_facture_source;		// Id facture avoir � l'origine de la remise
	var $ref_facture_source;	// Ref facture avoir � l'origine de la remise
	
	/**
	 *    \brief  Constructeur de la classe
	 *    \param  DB          handler acc�s base de donn�es
	 */
	function DiscountAbsolute($DB)
	{
		$this->db = $DB;
	}

	
	/**
	 *    	\brief      Charge objet remise depuis la base
	 *    	\param      rowid       id du projet � charger
	 *		\return		int			<0 si ko, =0 si non trouv�, >0 si ok
	 */
	function fetch($rowid)
	{
		$sql = "SELECT sr.fk_soc,";
		$sql.= " sr.fk_user,";
		$sql.= " sr.amount_ht, sr.amount_tva, sr.amount_ttc, sr.tva_tx,";
		$sql.= " sr.fk_facture, sr.fk_facture_source, sr.description,";
		$sql.= " ".$this->db->pdate("sr.datec")." as datec,";
		$sql.= " f.facnumber as ref_facture_source";
		$sql.= " FROM ".MAIN_DB_PREFIX."societe_remise_except as sr";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."facture as f ON sr.fk_facture_source = f.rowid";
		$sql.= " WHERE sr.rowid=".$rowid;
	
		dolibarr_syslog("DiscountAbsolute::fetch sql=".$sql);
 		$resql = $this->db->query($sql);
		if ($resql)
		{
			if ($this->db->num_rows($resql))
			{
				$obj = $this->db->fetch_object($resql);
	
				$this->id = $rowid;
				$this->fk_soc = $obj->fk_soc;
				$this->amount_ht = $obj->amount_ht;
				$this->amount_tva = $obj->amount_tva;
				$this->amount_ttc = $obj->amount_ttc;
				$this->tva_tx = $obj->tva_tx;
				$this->fk_user = $obj->fk_user;
				$this->fk_facture = $obj->fk_facture;
				$this->fk_facture_source = $obj->fk_facture_source;		// Id avoir source
				$this->ref_facture_source = $obj->ref_facture_source;	// Ref avoir source
				$this->description = $obj->description;
				$this->datec = $obj->datec;
	
				$this->db->free($resql);
				return 1;
			}
			else
			{
				$this->db->free($resql);
				return 0;
			}
		}
		else
		{
			$this->error=$this->db->error();
			return -1;
		}
	}


    /**
     *      \brief      Create in database
     *      \param      user        User that create
     *      \return     int         <0 si ko, >0 si ok
     */
    function create($user)
    {
    	global $conf, $langs;
    	
		// Nettoyage parametres
		$this->amount_ht=price2num($this->amount_ht);
		$this->amount_tva=price2num($this->amount_tva);
		$this->amount_ttc=price2num($this->amount_ttc);
		$this->tva_tx=price2num($this->tva_tx);
		
        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."societe_remise_except";
		$sql.= " (datec, fk_soc, fk_user, description,";
		$sql.= " amount_ht, amount_tva, amount_ttc, tva_tx,";
		$sql.= " fk_facture_source";
		$sql.= ")";
		$sql.= " VALUES (now(),".$this->fk_soc.",".$user->id.",'".addslashes($this->desc)."',";
		$sql.= " '".$this->amount_ht."','".$this->amount_tva."','".$this->amount_ttc."','".$this->tva_tx."',";
		$sql.= " ".($this->fk_facture_source?"'".$this->fk_facture_source."'":"null");
		$sql.= ")";

	   	dolibarr_syslog("DiscountAbsolute::create sql=".$sql);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$this->id=$this->db->last_insert_id(MAIN_DB_PREFIX."societe_remise_except");
			return $this->id;
		}
		else
		{
            $this->error=$this->db->lasterror().' - sql='.$sql;
            dolibarr_syslog("DiscountAbsolute::create ".$this->error);
            return -1;
		}
    }


				 	/*
	*   \brief      Delete object in database
	*	\return		int			<0 if KO, >0 if OK
	*/
	function delete()
	{
		global $conf, $langs;
	
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."societe_remise_except ";
		$sql.= " WHERE rowid = ".$this->id." AND fk_facture IS NULL";

	   	dolibarr_syslog("DiscountAbsolute::delete sql=".$sql);
		if (! $this->db->query($sql))
		{
			$this->error=$this->db->lasterror().' sql='.$sql;
			return -1;
		}
		else
		{
			return 1;
		}
	}
	

	
	/**
	*		\brief		Link the discount to a particular invoice
	*		\param		rowid		Invoice id
	*		\return		int			<0 ko, >0 ok
	*/
	function link_to_invoice($rowid)
	{
		dolibarr_syslog("Discount.class::link_to_invoice link discount ".$this->id." to invoice rowid=".$rowid);

		$sql ="UPDATE ".MAIN_DB_PREFIX."societe_remise_except";
		$sql.=" SET fk_facture = ".$rowid;
		$sql.=" WHERE rowid = ".$this->id;
		$resql = $this->db->query($sql);
		if ($resql)
		{
			return 1;
		}
		else
		{
			$this->error=$this->db->error();
			dolibarr_syslog("Discount.class::link_to_invoice ".$this->error." sql=".$sql);
			return -1;
		}
	}
	
	/**
		\brief      Renvoie nom clicable (avec eventuellement le picto)
		\param		withpicto		0=Pas de picto, 1=Inclut le picto dans le lien, 2=Picto seul
		\param		option			Sur quoi pointe le lien
		\return		string			Chaine avec URL
	*/
	function getNomUrl($withpicto,$option='')
	{
		global $langs;
		
		$result='';
		
		$lien = '<a href="'.DOL_URL_ROOT.'/compta/facture.php?facid='.$this->fk_facture_source.'">';
		$lienfin='</a>';
		
		$picto='bill';
		$label=$langs->trans("ShowDiscount").': '.$this->ref_facture_source;
		
		if ($withpicto) $result.=($lien.img_object($label,$picto).$lienfin);
		if ($withpicto && $withpicto != 2) $result.=' ';
		$result.=$lien.$this->ref_facture_source.$lienfin;
		return $result;
	}
	
}
?>
