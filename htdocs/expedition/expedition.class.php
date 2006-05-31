<?php
/* Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
 *
 */

/**
        \file       htdocs/expedition/expedition.class.php
        \ingroup    expedition
        \brief      Fichier de la classe de gestion des expeditions
        \version    $Revision$
*/


/** 
        \class      Expedition
		\brief      Classe de gestion des expeditions
*/
class Expedition 
{
  var $db ;
  var $id ;
  var $brouillon;
  var $entrepot_id;

  /**
   * Initialisation
   *
   */
  function Expedition($DB)
    {
      $this->db = $DB;
      $this->lignes = array();

      $this->sources[0] = "Proposition commerciale";
      $this->sources[1] = "Internet";
      $this->sources[2] = "Courrier";
      $this->sources[3] = "T�l�phone";
      $this->sources[4] = "Fax";

      $this->statuts[-1] = "Annul�e";
      $this->statuts[0] = "Brouillon";
      $this->statuts[1] = "Valid�e";

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
                $this->commande->fetch_lignes();
    
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
	    $idprod = $this->commande->lignes[$j]->product_id;
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
   *
   * Lit une expedition
   *
   */
    function fetch ($id)
    {
        global $conf;
    
        $sql = "SELECT e.rowid, e.date_creation, e.ref, e.fk_user_author, e.fk_statut, e.fk_commande, e.fk_entrepot";
        $sql .= ", ".$this->db->pdate("e.date_expedition")." as date_expedition, c.fk_adresse_livraison";
        if ($conf->livraison->enabled) $sql.=", l.rowid as livraison_id";
        $sql .= " FROM ".MAIN_DB_PREFIX."expedition as e";
        $sql .= ", ".MAIN_DB_PREFIX."commande as c";
        if ($conf->livraison->enabled) $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."livraison as l ON e.rowid = l.fk_expedition";
        $sql .= " WHERE e.rowid = $id";
        $sql .= " AND e.fk_commande = c.rowid";
    
        $result = $this->db->query($sql) ;
    
        if ($result)
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
            $this->db->free($result);
    
            if ($this->statut == 0) $this->brouillon = 1;
            
            // ligne de produit associ�e � une exp�dition
			      $this->lignes = array();
			      $sql = "SELECT c.description, c.qty as qtycom, e.qty as qtyexp, e.fk_commande_ligne";
			      $sql .= ", c.fk_product, c.label, p.ref";
			      $sql .= " FROM ".MAIN_DB_PREFIX."expeditiondet as e";
			      $sql .= " , ".MAIN_DB_PREFIX."commandedet as c";
			      $sql .= " , ".MAIN_DB_PREFIX."product as p";
			      $sql .= " WHERE e.fk_expedition = ".$this->id;
			      $sql .= " AND e.fk_commande_ligne = c.rowid";
			      $sql .= " AND c.fk_product = p.rowid";
			      
			      $resultp = $this->db->query($sql);
            
            if ($resultp)
            {
                    $num = $this->db->num_rows($resultp);
                    $i = 0;
    
                    while ($i < $num)
                    {
                        $objp                        = $this->db->fetch_object($resultp);
    
                        $ligne                       = new ExpeditionLigne();

                        $ligne->commande_ligne_id    = $objp->fk_commande_ligne;
                        $ligne->product_desc         = $objp->description;  // Description ligne
                        $ligne->qty_commande         = $objp->qtycom;
                        $ligne->product_id           = $objp->fk_product;

                        $ligne->libelle              = $objp->label;        // Label produit
                        $ligne->ref                  = $objp->ref;
    
                        $this->lignes[$i]            = $ligne;
                        $i++;
                    }
                    $this->db->free($resultp);
                }
                else
                {
                    dolibarr_syslog("Propal::Fetch Erreur lecture des produits");
                    return -1;
                }
    
            $file = $conf->expedition->dir_output . "/" .get_exdir($expedition->id) . "/" . $this->id.".pdf";
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
    
            // \todo Tester si non dej� au statut valid�. Si oui, on arrete afin d'�viter
            //       de d�cr�menter 2 fois le stock.

            $sql = "UPDATE ".MAIN_DB_PREFIX."expedition SET ref='".$this->ref."', fk_statut = 1, date_valid=now(), fk_user_valid=$user->id";
            $sql .= " WHERE rowid = $this->id AND fk_statut = 0 ;";
    
            if ($this->db->query($sql) )
            {
                // Si module stock g�r� et que expedition faite depuis un entrepot
                if ($conf->stock->enabled && $this->entrepot_id)
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
   * Ajoute un produit
   *
   */
  function insert_product_generic($p_desc, $p_price, $p_qty, $p_tva_tx=19.6, $p_product_id=0, $remise_percent=0)
    {
      if ($this->statut == 0)
	{
	  if (strlen(trim($p_qty)) == 0)
	    {
	      $p_qty = 1;
	    }

	  $p_price = price2num($p_price);

	  $price = $p_price;
	  $subprice = $p_price;
	  if ($remise_percent > 0)
	    {
	      $remise = round(($p_price * $remise_percent / 100), 2);
	      $price = $p_price - $remise;
	    }

	  $sql = "INSERT INTO ".MAIN_DB_PREFIX."commandedet (fk_commande, fk_product, qty, price, tva_tx, description, remise_percent, subprice) VALUES ";
	  $sql .= " (".$this->id.", $p_product_id,". $p_qty.",". $price.",".$p_tva_tx.",'". addslashes($p_desc) ."',$remise_percent, $subprice) ; ";
	  
	  if ($this->db->query($sql) )
	    {
	      
	      if ($this->update_price() > 0)
		{	      
		  return 1;
		}
	      else
		{
		  return -1;
		}
	    }
	  else
	    {
	      print $this->db->error();
	      print "<br>".$sql;
	      return -2;
	    }
	}
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
      $ligne = new ExpeditionLigne();

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


  /*
   * Lit la commande associ�e
   *
   */
  function fetch_commande()
  {
    $this->commande =& new Commande($this->db);
    $this->commande->fetch($this->commande_id);
  }


  function fetch_lignes()
  {
    $this->lignes = array();

    $sql = "SELECT c.description, c.qty as qtycom, e.qty as qtyexp";    
    $sql .= ", c.fk_product";
    $sql .= " FROM ".MAIN_DB_PREFIX."expeditiondet as e";
    $sql .= " , ".MAIN_DB_PREFIX."commandedet as c";

    $sql .= " WHERE e.fk_expedition = ".$this->id;
    $sql .= " AND e.fk_commande_ligne = c.rowid";


    $resql = $this->db->query($sql);
    if ($resql)
    {
      	$num = $this->db->num_rows($resql);
      	$i = 0;
      	while ($i < $num)
      	{
      		$ligne = new ExpeditionLigne();
      		$obj = $this->db->fetch_object($resql);
      		
      		$ligne->product_id     = $obj->fk_product;
      		$ligne->qty_commande   = $obj->qtycom;
      		$ligne->qty_expedition = $obj->qtyexp;
      		$ligne->description    = stripslashes($obj->description);
      		
      		$this->lignes[$i] = $ligne;
      		$i++;
      	}
      	$this->db->free($resql);
      }
    return $this->lignes;
  }
}


class ExpeditionLigne
{
	// From llx_expeditiondet
		var $qty;
		var $qty_expedition;
		var $product_id;
	
		// From llx_commandedet
		var $qty_commande;
		var $libelle;       // Label produit
		var $product_desc;  // Description produit
		var $ref;
		
	function ExpeditionLigne()
	{
		
	}

}

?>
