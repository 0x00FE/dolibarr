<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006 Regis Houssin        <regis.houssin@cap-networks.com>
 * Copyright (C) 2006      Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2007      Franky Van Liedekerke <franky.van.liedekerke@telenet.be>
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
        \file       htdocs/livraison/livraison.class.php
        \ingroup    livraison
        \brief      Fichier de la classe de gestion des bons de livraison
        \version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT."/commonobject.class.php");
require_once(DOL_DOCUMENT_ROOT."/expedition/expedition.class.php");
require_once(DOL_DOCUMENT_ROOT."/product/stock/mouvementstock.class.php");


/**
        \class      Livraison
		\brief      Classe de gestion des bons de livraison
*/
class Livraison extends CommonObject
{
	var $db;
	var $id;

	var $brouillon;
	var $commande_id;


	/**
	* Initialisation
	*
	*/
	function Livraison($DB)
    {
    	global $langs;

		$this->db = $DB;
		$this->lignes = array();
		
		$this->statuts[-1] = $langs->trans("Canceled");
		$this->statuts[0]  = $langs->trans("Draft");
		$this->statuts[1]  = $langs->trans("Validated");
		
		$this->products = array();
    }

	/**
	*    \brief      Cr�� bon de livraison en base
	*    \param      user        Objet du user qui cr�e
	*    \return     int         <0 si erreur, id livraison cr��e si ok
	*/
	function create($user)
	{
		global $conf;
	
		dolibarr_syslog("Livraison::create ");
	
		$error = 0;
	
		/* On positionne en mode brouillon le bon de livraison */
		$this->brouillon = 1;
	
		$this->user = $user;
	
		$this->db->begin();
	
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."livraison (date_creation, fk_user_author, fk_adresse_livraison";
		if ($this->commande_id) $sql.= ", fk_commande";
		if ($this->expedition_id) $sql.= ", fk_expedition";
		$sql.= ")";
		$sql.= " VALUES (now(), $user->id, $this->adresse_livraison_id";
		if ($this->commande_id) $sql.= ", $this->commande_id";
		if ($this->expedition_id) $sql.= ", $this->expedition_id";
		$sql.= ")";
	
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."livraison");
	
			$numref="(PROV".$this->id.")";
			$sql = "UPDATE ".MAIN_DB_PREFIX."livraison ";
			$sql.= "SET ref='".addslashes($numref)."' WHERE rowid=".$this->id;
			$resql=$this->db->query($sql);
			if ($resql)
			{
				if (! $conf->expedition->enabled)
				{
					$commande = new Commande($this->db);
					$commande->id = $this->commande_id;
					$this->lignes = $commande->fetch_lines();
				}
	
	
				/*
				*  Insertion des produits dans la base
				*/
				for ($i = 0 ; $i < sizeof($this->lignes) ; $i++)
				{
					if (! $this->create_line(0, $this->lignes[$i]->commande_ligne_id, $this->lignes[$i]->qty))
					{
						$error++;
					}
				}
	
				if (! $conf->expedition->enabled)
				{
					$sql = "UPDATE ".MAIN_DB_PREFIX."commande";
					$sql.= " SET fk_statut = 2";
					$sql.= " WHERE rowid=".$this->commande_id;
					$resql=$this->db->query($sql);
					if (! $resql)
					{
						$error++;
					}
				}
	
				if ($error==0)
				{
					$this->db->commit();
					return $this->id;
				}
				else
				{
					$error++;
					$this->error=$this->db->error()." - sql=".$this->db->lastqueryerror;
					dolibarr_syslog("Livraison::create Error -3 ".$this->error);
					$this->db->rollback();
					return -3;
				}
			}
			else
			{
				$error++;
				$this->error=$this->db->error()." - sql=".$this->db->lastqueryerror;
				dolibarr_syslog("Livraison::create Error -2 ".$this->error);
				$this->db->rollback();
				return -2;
			}
		}
		else
		{
			$error++;
			$this->error=$this->db->error()." - sql=".$this->db->lastqueryerror;
			dolibarr_syslog("Livraison::create Error -1 ".$this->error);
			$this->db->rollback();
			return -1;
		}
	}
	
	/**
	*
	*
	*/
	function create_line($transaction, $commande_ligne_id, $qty)
	{
		$error = 0;
		$idprod = 0;
		$j = 0;
	
		while (($j < sizeof($this->commande->lignes)) && idprod == 0)
		{
			if ($this->commande->lignes[$j]->id == $commande_ligne_id)
			{
				$idprod = $this->commande->lignes[$j]->fk_product;
			}
			$j++;
		}
	
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."livraisondet (fk_livraison, fk_commande_ligne, qty)";
		$sql .= " VALUES ($this->id,".$commande_ligne_id.",".$qty.")";
	
		if (! $this->db->query($sql) )
		{
			$error++;
		}
	
		if ($error == 0 )
		{
			return 1;
		}
	}
	
	/**
	*
	* Lit un bon de livraison
	*
	*/
	function fetch ($id)
	{
		global $conf;
	
		$sql = "SELECT l.rowid, l.date_creation, l.date_valid, l.ref, l.fk_user_author,";
		$sql .=" l.fk_statut, l.fk_commande, l.fk_expedition, l.fk_user_valid, l.note, l.note_public";
		$sql .= ", ".$this->db->pdate("l.date_livraison")." as date_livraison, l.fk_adresse_livraison, l.model_pdf";
		$sql .= ", c.fk_soc";
		$sql .= " FROM ".MAIN_DB_PREFIX."livraison as l, ".MAIN_DB_PREFIX."commande as c";
		$sql .= " WHERE l.rowid = ".$id." AND c.rowid = l.fk_commande";
	
		$result = $this->db->query($sql) ;
	
		if ( $result )
		{
			$obj = $this->db->fetch_object($result);
	
			$this->id                   = $obj->rowid;
			$this->date_creation        = $obj->date_creation;
			$this->date_valid           = $obj->date_valid;
			$this->ref                  = $obj->ref;
			$this->socid               = $obj->fk_soc;
			$this->statut               = $obj->fk_statut;
			$this->commande_id          = $obj->fk_commande;
			$this->expedition_id        = $obj->fk_expedition;
			$this->user_author_id       = $obj->fk_user_author;
			$this->user_valid_id        = $obj->fk_user_valid;
			$this->date_livraison       = $obj->date_livraison;
			$this->adresse_livraison_id = $obj->fk_adresse_livraison;
			$this->note                 = $obj->note;
			$this->note_public          = $obj->note_public;
			$this->modelpdf             = $obj->model_pdf;
			$this->db->free();
	
			if ($this->statut == 0) $this->brouillon = 1;
	
			$file = $conf->livraison->dir_output . "/" .get_exdir($livraison->id,2) . "/" . $this->id.".pdf";
			$this->pdf_filename = $file;
	
			return 1;
		}
		else
		{
			$this->error=$this->db->error();
			return -1;
		}
	}
	
	/**
	*        \brief      Valide l'expedition, et met a jour le stock si stock g�r
	*        \param      user        Objet de l'utilisateur qui valide
	*        \return     int
	*/
	function valid($user)
	{
		global $conf;
	
		dolibarr_syslog("livraison.class.php::valid begin");
	
		$this->db->begin();
	
		$error = 0;
	
		if ($user->rights->expedition->livraison->valider)
		{
			if (defined('LIVRAISON_ADDON'))
			{
				if (is_readable(DOL_DOCUMENT_ROOT .'/livraison/mods/'.LIVRAISON_ADDON.'.php'))
				{
					require_once DOL_DOCUMENT_ROOT .'/livraison/mods/'.LIVRAISON_ADDON.'.php';
	
					// Definition du nom de module de numerotation de commande
					$modName=LIVRAISON_ADDON;
	
					// Recuperation de la nouvelle reference
					$objMod = new $modName($this->db);
					$soc = new Societe($this->db);
					$soc->fetch($this->socid);
	
					// on v�rifie si le bon de livraison est en num�rotation provisoire
					$livref = substr($this->ref, 1, 4);
					if ($livref == PROV)
					{
						$num = $objMod->livraison_get_num($soc);
					}
					else
					{
						$num = $this->ref;
					}
	
					// Tester si non dej� au statut valid�. Si oui, on arrete afin d'�viter
          // de d�cr�menter 2 fois le stock.
          $sql = "SELECT ref FROM ".MAIN_DB_PREFIX."livraison where ref='".$this->ref."' AND fk_statut='1'";
          $resql=$this->db->query($sql);
          if ($resql)
          {
          	$num = $this->db->num_rows($resql);
           	if ($num > 0)
          	{
           		return 0;
           	}
          }
					
					$sql = "UPDATE ".MAIN_DB_PREFIX."livraison ";
					$sql.= " SET ref='".addslashes($num)."', fk_statut = 1, date_valid = now(), fk_user_valid = ".$user->id;
					$sql.= " WHERE rowid = ".$this->id." AND fk_statut = 0";
					$resql=$this->db->query($sql);
					if ($resql)
					{
						// Si module stock g�r� et que expedition faite depuis un entrepot
						if (!$conf->expedition->enabled && $conf->stock->enabled && $this->entrepot_id && $conf->global->STOCK_CALCULATE_ON_SHIPMENT == 1)
						{
	
							//Enregistrement d'un mouvement de stock pour chaque produit de l'expedition
	
	
							dolibarr_syslog("livraison.class.php::valid enregistrement des mouvements");
	
							$sql = "SELECT cd.fk_product, ld.qty ";
							$sql.= " FROM ".MAIN_DB_PREFIX."commandedet as cd, ".MAIN_DB_PREFIX."livraisondet as ld";
							$sql.= " WHERE ld.fk_livraison = $this->id AND cd.rowid = ld.fk_commande_ligne";
	
							$resql=$this->db->query($sql);
							if ($resql)
							{
								$num = $this->db->num_rows($resql);
								$i=0;
								while($i < $num)
								{
									dolibarr_syslog("livraison.class.php::valid movment $i");
	
									$obj = $this->db->fetch_object($resql);
	
									$mouvS = new MouvementStock($this->db);
									$result=$mouvS->livraison($user, $obj->fk_product, $this->entrepot_id, $obj->qty);
									if ($result < 0)
									{
										$this->db->rollback();
										$this->error=$this->db->error()." - sql=$sql";
										dolibarr_syslog("livraison.class.php::valid ".$this->error);
										return -3;
									}
									$i++;
								}
	
							}
							else
							{
								$this->db->rollback();
								$this->error=$this->db->error()." - sql=$sql";
								dolibarr_syslog("livraison.class.php::valid ".$this->error);
								return -2;
	
							}
						}
	
						// On efface le r�pertoire de pdf provisoire
						$livraisonref = sanitize_string($this->ref);
						if ($conf->expedition->dir_output)
						{
							$dir = $conf->livraison->dir_output . "/" . $livraisonref ;
							$file = $dir . "/" . $livraisonref . ".pdf";
							if (file_exists($file))
							{
								if (!dol_delete_file($file))
								{
									$this->error=$langs->trans("ErrorCanNotDeleteFile",$file);
									return 0;
								}
							}
							if (file_exists($dir))
							{
								if (!dol_delete_dir($dir))
								{
									$this->error=$langs->trans("ErrorCanNotDeleteDir",$dir);
									return 0;
								}
							}
						}
	
						dolibarr_syslog("livraison.class.php::valid ok");
					}
					else
					{
						$this->db->rollback();
						$this->error=$this->db->error()." - sql=$sql";
						dolibarr_syslog("livraison.class.php::valid ".$this->error);
						return -1;
					}
				}
			}
		}
		else
		{
			$this->error="Non autorise";
			dolibarr_syslog("livraison.class.php::valid ".$this->error);
			return -1;
		}
	
		$this->db->commit();
		dolibarr_syslog("livraison.class.php::valid commit");
		return 1;
	}
	
	/**     \brief      Cr�� le bon de livraison depuis une exp�dition existante
	\param      user            Utilisateur qui cr�e
	\param      sending_id      Id de l'exp�dition qui sert de mod�le
	*/
	function create_from_sending($user, $sending_id)
	{
		$expedition = new Expedition($this->db);
		$result=$expedition->fetch($sending_id);
	
		$this->lignes = array();
		$this->date_livraison = time();
		$this->expedition_id = $sending_id;
	
		for ($i = 0 ; $i < sizeof($expedition->lignes) ; $i++)
		{
			$LivraisonLigne = new LivraisonLigne($this->db);
			$LivraisonLigne->commande_ligne_id = $expedition->lignes[$i]->order_line_id;
			$LivraisonLigne->libelle           = $expedition->lignes[$i]->libelle;
			$LivraisonLigne->description       = $expedition->lignes[$i]->product_desc;
			$LivraisonLigne->qty               = $expedition->lignes[$i]->qty_expedie;
			$LivraisonLigne->fk_product        = $expedition->lignes[$i]->fk_product;
			$LivraisonLigne->ref               = $expedition->lignes[$i]->ref;
			$this->lignes[$i] = $LivraisonLigne;
		}
	
		$this->commande_id          = $expedition->commande_id;
		$this->note                 = $expedition->note;
		$this->projetid             = $expedition->projetidp;
		$this->date_livraison       = $expedition->date_livraison;
		$this->adresse_livraison_id = $expedition->adresse_livraison_id;
	
		return $this->create($user);
	}
	
	
	/**
	* Ajoute une ligne
	*
	*/
	function addline( $id, $qty )
	{
		$num = sizeof($this->lignes);
		$ligne = new livraisonLigne($this->db);
	
		$ligne->commande_ligne_id = $id;
		$ligne->qty = $qty;
	
		$this->lignes[$num] = $ligne;
	}
	
	/**
	*
	*
	*/
	function delete_line($idligne)
	{
		if ($this->statut == 0)
		{
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."commandedet WHERE rowid = $idligne";
	
			if ($this->db->query($sql) )
			{
				$this->update_price();
	
				return 1;
			}
			else
			{
				return 0;
			}
		}
	}
	
	/**
	* Supprime la fiche
	*
	*/
	function delete()
	{
		$this->db->begin();
	
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."livraisondet WHERE fk_livraison = $this->id ;";
		if ( $this->db->query($sql) )
		{
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."livraison WHERE rowid = $this->id;";
			if ( $this->db->query($sql) )
			{
				$this->db->commit();
	
				// On efface le r�pertoire de pdf provisoire
				$livref = sanitize_string($this->ref);
				if ($conf->livraison->dir_output)
				{
					$dir = $conf->livraison->dir_output . "/" . $livref ;
					$file = $conf->livraison->dir_output . "/" . $livref . "/" . $livref . ".pdf";
					if (file_exists($file))
					{
						if (!dol_delete_file($file))
						{
							$this->error=$langs->trans("ErrorCanNotDeleteFile",$file);
							return 0;
						}
					}
					if (file_exists($dir))
					{
						if (!dol_delete_dir($dir))
						{
							$this->error=$langs->trans("ErrorCanNotDeleteDir",$dir);
							return 0;
						}
					}
				}
	
				return 1;
			}
			else
			{
				$this->db->rollback();
				return -2;
			}
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}
	
	
	/*
	* Lit la commande associ�e
	*
	*/
	function fetch_commande()
	{
		$this->commande =& new Commande($this->db);
		$this->commande->fetch($this->commande_id);
	}
	
	/**
	*
	*
	*/
	
	function fetch_adresse_livraison($id)
	{
		$idadresse = $id;
		$adresse = new Societe($this->db);
		$adresse->fetch_adresse_livraison($idadresse);
		$this->adresse = $adresse;
	}
	
	
	function fetch_lignes()
	{
		$this->lignes = array();
	
		$sql = "SELECT p.label, c.description, c.qty as qtycom, l.qty as qtyliv";
		$sql .= ", c.fk_product, c.price, p.ref";
		$sql .= " FROM ".MAIN_DB_PREFIX."livraisondet as l";
		$sql .= " , ".MAIN_DB_PREFIX."commandedet as c";
		$sql .= " , ".MAIN_DB_PREFIX."product as p";
	
		$sql .= " WHERE l.fk_livraison = ".$this->id;
		$sql .= " AND l.fk_commande_ligne = c.rowid";
		$sql .= " AND c.fk_product = p.rowid";
	
	
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$ligne = new LivraisonLigne($this->db);
	
				$obj = $this->db->fetch_object($resql);
	
				$ligne->fk_product     = $obj->fk_product;
				$ligne->qty_commande   = $obj->qtycom;
				$ligne->qty_livre      = $obj->qtyliv;
				$ligne->ref            = $obj->ref;
				$ligne->label          = stripslashes($obj->label);
				$ligne->description    = stripslashes($obj->description);
				$ligne->price          = $obj->price;
	
				$this->lignes[$i] = $ligne;
				$i++;
			}
			$this->db->free($resql);
		}
	
		return $this->lignes;
	}


    /**
     *    \brief      Retourne le libell� du statut d'une expedition
     *    \return     string      Libell�
     */
    function getLibStatut($mode=0)
    {
    	return $this->LibStatut($this->statut,$mode);
    }
    
	/**
	 *		\brief      Renvoi le libell� d'un statut donn�
	 *    	\param      statut      Id statut
	 *    	\param      mode        0=libell� long, 1=libell� court, 2=Picto + Libell� court, 3=Picto, 4=Picto + Libell� long, 5=Libell� court + Picto
	 *    	\return     string		Libell�
	 */
    function LibStatut($statut,$mode)
    {
		global $langs;

    	if ($mode==0)
    	{
        	if ($statut==-1) return $this->statuts[$statut];
        	if ($statut==0) return $this->statuts[$statut];
        	if ($statut==1) return $this->statuts[$statut];
    	}
    	if ($mode==1)
    	{
        	if ($statut==-1) return $this->statuts[$statut];
        	if ($statut==0) return $this->statuts[$statut];
        	if ($statut==1) return $this->statuts[$statut];
    	}
      if ($mode == 4)
      {
        	if ($statut==-1) return img_picto($langs->trans('StatusSendingCanceled'),'statut5').' '.$langs->trans('StatusSendingDraft');
        	if ($statut==0) return img_picto($langs->trans('StatusSendingDraft'),'statut0').' '.$langs->trans('StatusSendingDraft');
        	if ($statut==1) return img_picto($langs->trans('StatusSendingValidated'),'statut4').' '.$langs->trans('StatusSendingValidated');
		  }
    }
    
  /**
   *		\brief		Positionne modele derniere generation
   *		\param		user		Objet use qui modifie
   *		\param		modelpdf	Nom du modele
   */
  function set_pdf_model($user, $modelpdf)
  {
    if ($user->rights->expedition->livraison->creer)
      {
	$sql = "UPDATE ".MAIN_DB_PREFIX."livraison SET model_pdf = '$modelpdf'";
	$sql .= " WHERE rowid = $this->id ;";

	if ($this->db->query($sql) )
	  {
	    $this->modelpdf=$modelpdf;
	    return 1;
	  }
	else
	  {
	    dolibarr_print_error($this->db);
	    return 0;
	  }
      }
  }

}


/**
        \class      LivraisonLigne
		\brief      Classe de gestion des lignes de bons de livraison
*/
class LivraisonLigne
{
	var $db;
	
	// From llx_expeditiondet
	var $qty;
	var $fk_product;
	var $commande_ligne_id;
	var $libelle;       // Label produit
	var $description;  // Description produit
	var $ref;
		
	function LivraisonLigne($DB)
	{
		$this->db=$DB;	
	}

}

?>
