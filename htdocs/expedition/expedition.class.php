<?php
/* Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2007 Franky Van Liedekerke <franky.van.liedekerke@telenet.be>
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
        \file       htdocs/expedition/expedition.class.php
        \ingroup    expedition
        \brief      Fichier de la classe de gestion des expeditions
        \version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT."/commonobject.class.php");
require_once(DOL_DOCUMENT_ROOT."/commande/commande.class.php");


/** 
        \class      Expedition
		\brief      Classe de gestion des expeditions
*/
class Expedition extends CommonObject
{
	var $db;
	var $id;
	var $brouillon;
	var $entrepot_id;
	var $modelpdf;


	/**
	* Initialisation
	*
	*/
	function Expedition($DB)
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
   *    \brief      Cr�� exp�dition en base
   *    \param      user        Objet du user qui cr�
   *    \return     int         <0 si erreur, id exp�dition cr��e si ok
   */
	function create($user)
    {
        require_once DOL_DOCUMENT_ROOT ."/product/stock/mouvementstock.class.php";
        $error = 0;
        /* On positionne en mode brouillon la commande */
        $this->brouillon = 1;
    
        $this->user = $user;
    
        $this->db->begin();
    
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."expedition (date_creation, fk_user_author, date_expedition, fk_commande";
        if ($this->entrepot_id) $sql.= ", fk_entrepot";
        $sql.= ")";
        $sql.= " VALUES (now(), $user->id, ".$this->db->idate($this->date_expedition).",$this->commande_id";
        if ($this->entrepot_id) $sql.= ", $this->entrepot_id";
        $sql.= ")";
    
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."expedition");
    
            $sql = "UPDATE ".MAIN_DB_PREFIX."expedition SET ref='(PROV".$this->id.")' WHERE rowid=".$this->id;
            if ($this->db->query($sql))
            {
    
                $this->commande = new Commande($this->db);
                $this->commande->id = $this->commande_id;
                $this->commande->fetch_lines();
    
                /*
                *  Insertion des produits dans la base
                */
                for ($i = 0 ; $i < sizeof($this->lignes) ; $i++)
                {
                    //TODO
                    if (! $this->create_line(0, $this->lignes[$i]->commande_ligne_id, $this->lignes[$i]->qty))
                    {
                        $error++;
                    }
                }

                /*
                 *
                 */
                $sql = "UPDATE ".MAIN_DB_PREFIX."commande SET fk_statut = 2 WHERE rowid=".$this->commande_id;
                if (! $this->db->query($sql))
                {
                    $error++;
                }
        
                if ($error==0)
                {
                    $this->db->commit();
                    return $this->id;
                }
                else
                {
                    $error++;
                    $this->error=$this->db->error()." - sql=$sql";
                    $this->db->rollback();
                    return -3;
                }
            }
            else
            {
                $error++;
                $this->error=$this->db->error()." - sql=$sql";
                $this->db->rollback();
                return -2;
            }
        }
        else
        {
            $error++;
            $this->error=$this->db->error()." - sql=$sql";
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

    $sql = "INSERT INTO ".MAIN_DB_PREFIX."expeditiondet (fk_expedition, fk_commande_ligne, qty)";
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
	 *		\brief		Lit une expedition
	 *		\param		id
	 */
    function fetch ($id)
    {
        global $conf;
    
        $sql = "SELECT e.rowid, e.date_creation, e.ref, e.fk_user_author, e.fk_statut, e.fk_commande, e.fk_entrepot,";
        $sql.= " ".$this->db->pdate("e.date_expedition")." as date_expedition, e.model_pdf,";
        $sql.= " c.fk_adresse_livraison";
        if ($conf->livraison->enabled) $sql.=", l.rowid as livraison_id";
        $sql.= " FROM ".MAIN_DB_PREFIX."commande as c,";
        $sql.= " ".MAIN_DB_PREFIX."expedition as e";
        if ($conf->livraison->enabled) $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."livraison as l ON e.rowid = l.fk_expedition";
        $sql.= " WHERE e.rowid = ".$id;
        $sql.= " AND e.fk_commande = c.rowid";
    
        $result = $this->db->query($sql) ;
    
        if ($result)
        {
        	if ($this->db->num_rows($result))
        	{
            $obj = $this->db->fetch_object($result);
    
            $this->id                   = $obj->rowid;
            $this->ref                  = $obj->ref;
            $this->statut               = $obj->fk_statut;
            $this->commande_id          = $obj->fk_commande;
            if ($conf->livraison->enabled)
            {
            	$this->livraison_id       = $obj->livraison_id;
            }
            $this->user_author_id       = $obj->fk_user_author;
            $this->date                 = $obj->date_expedition;
            $this->entrepot_id          = $obj->fk_entrepot;
            $this->adresse_livraison_id = $obj->fk_adresse_livraison;
			      $this->modelpdf             = $obj->model_pdf;
            $this->db->free($result);
    
            if ($this->statut == 0) $this->brouillon = 1;
            
			      $this->lignes = array();
    
            $file = $conf->expedition->dir_output . "/" .get_exdir($expedition->id,2) . "/" . $this->id.".pdf";
            $this->pdf_filename = $file;
            
            /*
             * Lignes
             */
            $result=$this->fetch_lines();
            if ($result < 0)
            {
            	return -3;
            }
    
            return 1;
          }
          else
          {
          	dolibarr_syslog('Expedition::Fetch Error rowid='.$rowid.' numrows=0 sql='.$sql);
	          $this->error='Delivery with id '.$rowid.' not found sql='.$sql;
	          return -2;
	        }
        }
        else
        {
        	dolibarr_syslog('Expedition::Fetch Error rowid='.$rowid.' Erreur dans fetch de l\'expedition');
        	$this->error=$this->db->error();
        	return -1;
        }
    }

  /**
   *        \brief      Valide l'expedition, et met a jour le stock si stock g�r�
   *        \param      user        Objet de l'utilisateur qui valide
   *        \return     int
   */
    function valid($user)
    {
        global $conf;
        
        require_once DOL_DOCUMENT_ROOT ."/product/stock/mouvementstock.class.php";
    
        dolibarr_syslog("expedition.class.php::valid");

        $this->db->begin();
        
        $error = 0;
        $provref = $this->ref;
        
        if ($user->rights->expedition->valider)
        {
            $this->ref = "EXP".$this->id;
    
            // Tester si non dej� au statut valid�. Si oui, on arrete afin d'�viter
            // de d�cr�menter 2 fois le stock.
            $sql = "SELECT ref FROM ".MAIN_DB_PREFIX."expedition where ref='".$this->ref."' AND fk_statut <> '0'";
            $resql=$this->db->query($sql);
            if ($resql)
            {
            	$num = $this->db->num_rows($resql);
            	if ($num > 0)
            	{
            		return 0;
            	}
            }

            $sql = "UPDATE ".MAIN_DB_PREFIX."expedition";
            $sql.= " SET ref='".$this->ref."', fk_statut = 1, date_valid = now(), fk_user_valid = ".$user->id;
            $sql.= " WHERE rowid = ".$this->id." AND fk_statut = 0";
    
            if ($this->db->query($sql) )
            {
                // Si module stock g�r� et que expedition faite depuis un entrepot
                if ($conf->stock->enabled && $this->entrepot_id && $conf->global->STOCK_CALCULATE_ON_SHIPMENT == 1)
                {
                    /*
                     * Enregistrement d'un mouvement de stock pour chaque produit de l'expedition
                     */
                    dolibarr_syslog("expedition.class.php::valid enregistrement des mouvements");

                    $sql = "SELECT cd.fk_product, ed.qty ";
                    $sql.= " FROM ".MAIN_DB_PREFIX."commandedet as cd, ".MAIN_DB_PREFIX."expeditiondet as ed";
                    $sql.= " WHERE ed.fk_expedition = $this->id AND cd.rowid = ed.fk_commande_ligne";
        
                    $resql=$this->db->query($sql);
                    if ($resql)
                    {
                        $num = $this->db->num_rows($resql);
                        $i=0;
                        while($i < $num)
                        {
                            dolibarr_syslog("expedition.class.php::valid movment $i");

                            $obj = $this->db->fetch_object($resql);

                            $mouvS = new MouvementStock($this->db);
                            $result=$mouvS->livraison($user, $obj->fk_product, $this->entrepot_id, $obj->qty);
                            if ($result < 0)
                            {
                                $this->db->rollback();
                                $this->error=$this->db->error()." - sql=$sql";
                                dolibarr_syslog("expedition.class.php::valid ".$this->error);
                                return -3;
                            }

                            $i++;
                        }
                        
                    }
                    else
                    {
                        $this->db->rollback();
                        $this->error=$this->db->error()." - sql=$sql";
                        dolibarr_syslog("expedition.class.php::valid ".$this->error);
                        return -2;
                    }
                }
               
              // On efface le r�pertoire de pdf provisoire
							$expeditionref = sanitize_string($provref);
							if ($conf->expedition->dir_output)
							{
								$dir = $conf->expedition->dir_output . "/" . $expeditionref;
								$file = $dir . "/" . $expeditionref . ".pdf";
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
                
            }
            else
            {
                $this->db->rollback();
                $this->error=$this->db->error()." - sql=$sql";
                dolibarr_syslog("expedition.class.php::valid ".$this->error);
                return -1;
            }
        }
        else
        {
            $this->error="Non autorise";
            dolibarr_syslog("expedition.class.php::valid ".$this->error);
            return -1;
        }

        $this->db->commit();
        //dolibarr_syslog("expedition.class.php::valid commit");
        return 1;
    }

    
   /**
     *      \brief      Cr�e un bon de livraison � partir de l'exp�dition
     *      \param      user        Utilisateur
     *      \return     int         <0 si ko, >=0 si ok
     */
    function create_delivery($user)
    {
        global $conf;
        
        if ($conf->livraison->enabled)
        {
            if ($this->statut == 1)
            {
                // Exp�dition valid�e
                include_once(DOL_DOCUMENT_ROOT."/livraison/livraison.class.php");
                $livraison = new Livraison($this->db);
                $result=$livraison->create_from_sending($user, $this->id);
    
                return $result;
            }
            else return 0;
        }
        else return 0;
    }
    
  /**
   * Ajoute une ligne
   *
   */
  function addline( $id, $qty )
    {
      $num = sizeof($this->lignes);
      $ligne = new ExpeditionLigne($this->db);

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

    $sql = "DELETE FROM ".MAIN_DB_PREFIX."expeditiondet WHERE fk_expedition = $this->id ;";
    if ( $this->db->query($sql) ) 
      {
	$sql = "DELETE FROM ".MAIN_DB_PREFIX."expedition WHERE rowid = $this->id;";
	if ( $this->db->query($sql) ) 
	  {
	    $this->db->commit();
	    
	    // On efface le r�pertoire de pdf provisoire
		$expref = sanitize_string($this->ref);
		if ($conf->expedition->dir_output)
		{
				$dir = $conf->expedition->dir_output . "/" . $expref ;
				$file = $conf->expedition->dir_output . "/" . $expref . "/" . $expref . ".pdf";
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
  
  
  /**
   * Classe la commande
   *
   *
   */
  function classin($cat_id)
    {
      $sql = "UPDATE ".MAIN_DB_PREFIX."commande SET fk_projet = $cat_id";
      $sql .= " WHERE rowid = $this->id;";
      
      if ($this->db->query($sql) )
	{
	  return 1;
	}
      else
	{
	  print $this->db->error() . ' in ' . $sql;
	}
    }


	/**
	 *		\brief		Positionne modele derniere generation
	 *		\param		user		Objet use qui modifie
	 *		\param		modelpdf	Nom du modele
	 */
	function set_pdf_model($user, $modelpdf)
	{
		if ($user->rights->expedition->creer)
		{
			$sql = "UPDATE ".MAIN_DB_PREFIX."expedition SET model_pdf = '$modelpdf'";
			$sql.= " WHERE rowid = ".$this->id;
	
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
	

	/*
	* Lit la commande associ�e
	*
	*/
	function fetch_commande()
	{
		$this->commande = & new Commande($this->db);
		$this->commande->fetch($this->commande_id);
	}

	
	function fetch_lines()
	{
		$sql = "SELECT cd.rowid, cd.fk_product, cd.description, cd.qty as qty_commande";
		$sql.= ", ed.qty as qty_expedie, ed.fk_commande_ligne";
		$sql.= ", p.ref, p.label, p.weight, p.weight_units, p.volume, p.volume_units";
		$sql.= " FROM (".MAIN_DB_PREFIX."commandedet as cd";
		$sql.= ", ".MAIN_DB_PREFIX."expeditiondet as ed)";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product as p ON (p.rowid = cd.fk_product)";
		$sql.= " WHERE ed.fk_expedition = ".$this->id;
		$sql.= " AND ed.fk_commande_ligne = cd.rowid";
	
	
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$ligne = new ExpeditionLigne($this->db);
				$obj = $this->db->fetch_object($resql);
	
				$ligne->order_line_id  = $obj->fk_commande_ligne;
				$ligne->fk_product     = $obj->fk_product;
				$ligne->ref            = $obj->ref;
				$ligne->libelle        = $obj->label;
				$ligne->description    = $obj->description;
				$ligne->qty_commande   = $obj->qty_commande;
				$ligne->qty_expedie    = $obj->qty_expedie;
				$ligne->weight         = $obj->weight;
				$ligne->weight_units   = $obj->weight_units;
				$ligne->volume         = $obj->volume;
				$ligne->volume_units   = $obj->volume_units;
	
				$this->lignes[$i] = $ligne;
				$i++;
			}
			$this->db->free($resql);
			return 1;
		}
		else
		{
			$this->error=$this->db->error();
	    dolibarr_syslog('Expedition::fetch_lines: Error '.$this->error);
	    return -3;
	  }
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
        	if ($statut==0) return $this->statuts[$statut];
        	if ($statut==1) return $this->statuts[$statut];
    	}
    	if ($mode==1)
    	{
        	if ($statut==0) return $this->statuts[$statut];
        	if ($statut==1) return $this->statuts[$statut];
    	}
        if ($mode == 4)
        {
        	if ($statut==0) return img_picto($langs->trans('StatusSendingDraft'),'statut0').' '.$langs->trans('StatusSendingDraft');
        	if ($statut==1) return img_picto($langs->trans('StatusSendingValidated'),'statut4').' '.$langs->trans('StatusSendingValidated');
		}
    }  

	/**
	 *		\brief		Initialise la facture avec valeurs fictives al�atoire
	 *					Sert � g�n�rer une facture pour l'aperu des mod�les ou demo
	 */
	function initAsSpecimen()
	{
		global $user,$langs;

		// Charge tableau des id de soci�t� socids
		$socids = array();
		$sql = "SELECT idp FROM ".MAIN_DB_PREFIX."societe WHERE client=1 LIMIT 10";
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num_socs = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num_socs)
			{
				$i++;

				$row = $this->db->fetch_row($resql);
				$socids[$i] = $row[0];
			}
		}

		// Charge tableau des produits prodids
		$prodids = array();
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."product WHERE envente=1";
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num_prods = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num_prods)
			{
				$i++;
				$row = $this->db->fetch_row($resql);
				$prodids[$i] = $row[0];
			}
		}

		// Initialise param�tres
		$this->id=0;
		$this->ref = 'SPECIMEN';
		$this->specimen=1;
		$socid = rand(1, $num_socs);
        $this->statut               = 1;
        $this->commande_id          = 0;
        if ($conf->livraison->enabled)
        {
          	$this->livraison_id     = 0;
        }
        $this->date                 = time();
        $this->entrepot_id          = 0;
        $this->adresse_livraison_id = 0;
		$this->socid = $socids[$socid];

		$nbp = 5;
		$xnbp = 0;
		while ($xnbp < $nbp)
		{
			$ligne=new ExpeditionLigne($this->db);
			$ligne->desc=$langs->trans("Description")." ".$xnbp;
			$ligne->libelle=$langs->trans("Description")." ".$xnbp;
			$ligne->qty=10;
			$ligne->qty_expedition=5;
			$prodid = rand(1, $num_prods);
			$ligne->fk_product=$prodids[$prodid];
			$xnbp++;
		}
	}  
}


/**
        \class      ExpeditionLigne
		\brief      Classe de gestion des lignes de bons d'expedition
*/
class ExpeditionLigne
{
	var $db;
	
	// From llx_expeditiondet
	var $qty;
	var $qty_expedition;
	var $fk_product;
	
	// From llx_commandedet
	var $qty_commande;
	var $libelle;       // Label produit
	var $product_desc;  // Description produit
	var $ref;

	
	function ExpeditionLigne($DB)
	{
		$this->db=$DB;	
	}

}

?>
