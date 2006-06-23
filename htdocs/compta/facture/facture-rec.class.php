<?php
/* Copyright (C) 2003-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \file       htdocs/compta/facture/facture-rec.class.php
        \ingroup    facture
        \brief      Fichier de la classe des factures recurentes
        \version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT."/notify.class.php");
require_once(DOL_DOCUMENT_ROOT."/product.class.php");
require_once(DOL_DOCUMENT_ROOT."/facture.class.php");


/**
        \class      FactureRec
        \brief      Classe de gestion des factures recurrentes/Mod�les
*/
class FactureRec extends Facture
{
	var $db ;
	var $element='commande';

	var $id ;

	var $socidp;		// Id client
	var $client;		// Objet societe client (� charger par fetch_client)

    var $number;
    var $author;
    var $date;
    var $ref;
    var $amount;
    var $remise;
    var $tva;
    var $total;
    var $note;
    var $db_table;
    var $propalid;
    var $projetid;


    /**
     * 		\brief		Initialisation de la class
     *
     */
    function FactureRec($DB, $facid=0)
    {
        $this->db = $DB ;
        $this->facid = $facid;
    }
    
    /**
     * 		\brief		Cr�� la facture recurrente/modele
     *		\return		int			<0 si ko, id facture rec cr�e si ok
     */
    function create($user)
    {
    	global $langs;
    	
		// Nettoyage parametere
		$this->titre=trim($this->titre);

		// Validation parameteres
		if (! $this->titre)
		{
			$this->error=$langs->trans("ErrorFieldRequired",$langs->trans("Title"));
			return -3;
		}

    	// Charge facture modele
    	$facsrc=new Facture($this->db);
    	$result=$facsrc->fetch($this->facid);
        if ($result > 0)
        {
            // On positionne en mode brouillon la facture
            $this->brouillon = 1;

            $sql = "INSERT INTO ".MAIN_DB_PREFIX."facture_rec (titre, fk_soc, datec, amount, remise, remise_percent, note, fk_user_author,fk_projet, fk_cond_reglement) ";
            $sql.= " VALUES ('$this->titre', '$facsrc->socidp', now(), '$facsrc->amount', '$facsrc->remise', '$facsrc->remise_percent', '".addslashes($this->note)."','$user->id',";
            $sql.= " ".($facsrc->projetid?"'".$facsrc->projetid."'":"null").", ";
            $sql.= " '".$facsrc->cond_reglement_id."')";
            if ( $this->db->query($sql) )
            {
                $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."facture_rec");

                /*
                 * Produits
                 */
                for ($i = 0 ; $i < sizeof($facsrc->lignes) ; $i++)
                {
                    if ($facsrc->lignes[$i]->produit_id > 0)
                    {
                        $prod = new Product($this->db);
                        $prod->fetch($facsrc->lignes[$i]->produit_id);
                    }

                    $result_insert = $this->addline($this->id,
                    addslashes($facsrc->lignes[$i]->desc),
                    $facsrc->lignes[$i]->subprice,
                    $facsrc->lignes[$i]->qty,
                    $facsrc->lignes[$i]->tva_taux,
                    $facsrc->lignes[$i]->produit_id,
                    $facsrc->lignes[$i]->remise_percent);


                    if ( $result_insert < 0)
                    {
                        $this->error=$this->db->error().' sql='.$sql;
                    }
                }

                return $this->id;
            }
            else
            {
                $this->error=$this->db->error().' sql='.$sql;
                return -2;
            }
        }
        else
        {
            return -1;
        }
    }

    /**
     * Recup�re l'objet facture
     */
    function fetch($rowid, $societe_id=0)
    {

        $sql = "SELECT f.fk_soc,f.titre,f.amount,f.tva,f.total,f.total_ttc,f.remise,f.remise_percent,f.fk_projet, c.rowid as crid, c.libelle, c.libelle_facture, f.note, f.fk_user_author";
        $sql .= " FROM ".MAIN_DB_PREFIX."facture_rec as f, ".MAIN_DB_PREFIX."cond_reglement as c";
        $sql .= " WHERE f.rowid=$rowid AND c.rowid = f.fk_cond_reglement";

        if ($societe_id > 0)
        {
            $sql .= " AND f.fk_soc = ".$societe_id;
        }

        if ($this->db->query($sql) )
        {
            if ($this->db->num_rows())
            {
                $obj = $this->db->fetch_object();

                $this->id                 = $rowid;
                $this->datep              = $obj->dp;
                $this->titre              = $obj->titre;
                $this->amount             = $obj->amount;
                $this->remise             = $obj->remise;
                $this->total_ht           = $obj->total;
                $this->total_tva          = $obj->tva;
                $this->total_ttc          = $obj->total_ttc;
                $this->paye               = $obj->paye;
                $this->remise_percent     = $obj->remise_percent;
                $this->socidp             = $obj->fk_soc;
                $this->statut             = $obj->fk_statut;
                $this->date_lim_reglement     = $obj->dlr;
                $this->cond_reglement_id      = $obj->crid;
                $this->cond_reglement         = $obj->libelle;
                $this->cond_reglement_facture = $obj->libelle_facture;
                $this->projetid               = $obj->fk_projet;
                $this->note                   = stripslashes($obj->note);
                $this->user_author            = $obj->fk_user_author;
                $this->lignes                 = array();

                if ($this->statut == 0)
                {
                    $this->brouillon = 1;
                }

                $this->db->free();

                /*
                * Lignes
                */

                $sql = "SELECT l.fk_product,l.description, l.subprice, l.price, l.qty, l.rowid, l.tva_taux, l.remise_percent";
                $sql .= " FROM ".MAIN_DB_PREFIX."facturedet_rec as l WHERE l.fk_facture = ".$this->id." ORDER BY l.rowid ASC";

                $result = $this->db->query($sql);
                if ($result)
                {
                    $num = $this->db->num_rows();
                    $i = 0; $total = 0;

                    while ($i < $num)
                    {
                        $objp = $this->db->fetch_object($result);
                        $faclig = new FactureLigne($this->db);
                        $faclig->produit_id     = $objp->fk_product;
                        $faclig->desc           = stripslashes($objp->description);
                        $faclig->qty            = $objp->qty;
                        $faclig->price          = $objp->price;
                        $faclig->subprice          = $objp->subprice;
                        $faclig->tva_taux       = $objp->tva_taux;
                        $faclig->remise_percent = $objp->remise_percent;
                        $this->lignes[$i]       = $faclig;
                        $i++;
                    }

                    $this->db->free();

                    return 1;
                }
                else
                {
                    print $this->db->error();
                    return -1;
                }
            }
            else
            {
                print "Error";
                return -2;
            }
        }
        else
        {
            print $this->db->error();
            return -3;
        }
    }

    /**
     * Valide la facture
     */
    function valid($userid, $dir)
    {
        $sql = "UPDATE ".MAIN_DB_PREFIX."facture SET fk_statut = 1, date_valid=now(), fk_user_valid=$userid";
        $sql .= " WHERE rowid = $this->id AND fk_statut = 0 ;";

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
     * Supprime la facture
     */
    function delete($rowid)
    {
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."facturedet_rec WHERE fk_facture = $rowid;";

        if ($this->db->query( $sql) )
        {
            $sql = "DELETE FROM ".MAIN_DB_PREFIX."facture_rec WHERE rowid = $rowid";

            if ($this->db->query( $sql) )
            {
                return 1;
            }
            else
            {
                print "Err : ".$this->db->error();
                return -1;
            }
        }
        else
        {
            print "Err : ".$this->db->error();
            return -2;
        }
    }

    /**
     * Valide la facture
     */
    function set_valid($rowid, $user, $soc)
    {
        if ($this->brouillon)
        {
            $action_notify = 2; // ne pas modifier cette valeur

            $numfa = $this->getNextNumRef($soc);

            $sql = "UPDATE ".MAIN_DB_PREFIX."facture set facnumber='$numfa', fk_statut = 1, fk_user_valid = $user->id WHERE rowid = $rowid ;";
            $result = $this->db->query( $sql);

            /*
            * Notify
            */
            $facref = sanitize_string($this->ref);
            $filepdf = $conf->facture->dir_output . "/" . $facref . "/" . $facref . ".pdf";


            $mesg = "La facture ".$this->ref." a �t� valid�e.\n";

            $notify = New Notify($this->db);
            $notify->send($action_notify, $this->socidp, $mesg, "facture", $rowid, $filepdf);
            /*
            * Update Stats
            *
            */
            $sql = "SELECT fk_product FROM ".MAIN_DB_PREFIX."facturedet WHERE fk_facture = ".$this->id;
            $sql .= " AND fk_product IS NOT NULL";

            $result = $this->db->query($sql);

            if ($result)
            {
                $num = $this->db->num_rows();
                $i = 0;
                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($result);

                    $sql = "UPDATE ".MAIN_DB_PREFIX."product SET nbvente=nbvente+1 WHERE rowid = ".$obj->fk_product;
                    $db2 = $this->db->dbclone();
                    $result = $db2->query($sql);
                    $i++;
                }
            }
            /*
            * Contrats
            */
            $contrat = new Contrat($this->db);
            $contrat->create_from_facture($this->id, $user, $this->socidp);

            return $result;
        }
    }
 
     /**
     *      \brief      Renvoie la r�f�rence de facture suivante non utilis�e en fonction du module
     *                  de num�rotation actif d�fini dans FACTURE_ADDON
     *      \param	    soc  		            objet societe
     *      \return     string                  reference libre pour la facture
     */
    function getNextNumRef($soc)
    {
        global $db, $langs;
        $langs->load("bills");
    
        $dir = DOL_DOCUMENT_ROOT . "/includes/modules/facture/";
    
        if (defined("FACTURE_ADDON") && FACTURE_ADDON)
        {
            $file = FACTURE_ADDON."/".FACTURE_ADDON.".modules.php";
    
            // Chargement de la classe de num�rotation
            $classname = "mod_facture_".FACTURE_ADDON;
            require_once($dir.$file);
    
            $obj = new $classname();
    
            $numref = "";
            $numref = $obj->getNumRef($soc,$this);
    
            if ( $numref != "")
            {
                return $numref;
            }
            else
            {
                dolibarr_print_error($db,"modules_facture::getNextNumRef ".$obj->error);
                return "";
            }
        }
        else
        {
            print $langs->trans("Error")." ".$langs->trans("Error_FACTURE_ADDON_NotDefined");
            return "";
        }
    }
    
    /**
     * Ajoute un produit dans la facture
     */
    function add_product($idproduct, $qty, $remise_percent)
    {
        if ($idproduct > 0)
        {
            $i = sizeof($this->products);
            $this->products[$i] = $idproduct;
            if (!$qty)
            {
                $qty = 1 ;
            }
            $this->products_qty[$i] = $qty;
            $this->products_remise_percent[$i] = $remise_percent;
        }
    }
 
  /**
   * Ajoute une ligne de facture
   */
  function addline($facid, $desc, $pu, $qty, $txtva, $fk_product='NULL', $remise_percent=0)
  {
    if ($this->brouillon)
      {
	if (strlen(trim($qty))==0)
	  {
	    $qty=1;
	  }
	$remise = 0;
	$price = round(price2num($pu), 2);
	$subprice = $price;
	if (trim(strlen($remise_percent)) > 0)
	  {
	    $remise = round(($pu * $remise_percent / 100), 2);
	    $price = $pu - $remise;
	  }
	
	$sql = "INSERT INTO ".MAIN_DB_PREFIX."facturedet_rec (fk_facture,description,price,qty,tva_taux, fk_product, remise_percent, subprice, remise)";
	$sql .= " VALUES ('$facid', '$desc'";
	$sql .= ",".price2num($price);
	$sql .= ",".price2num($qty);
	$sql .= ",".price2num($txtva);
	$sql .= ",'$fk_product'";
	$sql .= ",'".price2num($remise_percent)."'";
	$sql .= ",'".price2num($subprice)."'";
	$sql .= ",'".price2num($remise)."') ;";
	
	if ( $this->db->query( $sql) )
	  {
	    $this->update_price($facid);
	    return 1;
	  }
	else
	  {
	    print "$sql";
	    return -1;
	  }
      }
    }
  
  /**
   * Mets � jour une ligne de facture
   */
  function updateline($rowid, $desc, $pu, $qty, $remise_percent=0)
  {
    if ($this->brouillon)
      {
	if (strlen(trim($qty))==0)
	  {
	    $qty=1;
	  }
	$remise = 0;
	$price = round(price2num($pu), 2);
	$subprice = $price;
	if (trim(strlen($remise_percent)) > 0)
	  {
	    $remise = round(($pu * $remise_percent / 100), 2);
	    $price = $pu - $remise;
	  }
	else
	  {
	    $remise_percent=0;
	  }
	
	$sql = "UPDATE ".MAIN_DB_PREFIX."facturedet set description='$desc'";
	$sql .= ",price=".price2num($price);
	$sql .= ",subprice=".price2num($subprice);
	$sql .= ",remise=".price2num($remise);
	$sql .= ",remise_percent=".price2num($remise_percent);
	$sql .= ",qty=".price2num($qty);
	$sql .= " WHERE rowid = $rowid ;";
	
	$result = $this->db->query( $sql);
	
	$this->update_price($this->id);
      }
  }
  
  /**
   * Supprime une ligne
   */
  function deleteline($rowid)
  {
    if ($this->brouillon)
      {
	$sql = "DELETE FROM ".MAIN_DB_PREFIX."facturedet WHERE rowid = $rowid;";
	$result = $this->db->query( $sql);
	
	$this->update_price($this->id);
      }
  }
  
  /**
   * Mise � jour des sommes de la facture
   */
  function update_price($facid)
  {
    include_once DOL_DOCUMENT_ROOT . "/lib/price.lib.php";
    $err=0;
    $sql = "SELECT price, qty, tva_taux FROM ".MAIN_DB_PREFIX."facturedet_rec WHERE fk_facture = $facid;";
    
    $result = $this->db->query($sql);
    
    if ($result)
      {
	$num = $this->db->num_rows();
	$i = 0;
	while ($i < $num)
	  {
	    $obj = $this->db->fetch_object($result);
	    
	    $products[$i][0] = $obj->price;
	    $products[$i][1] = $obj->qty;
	    $products[$i][2] = $obj->tva_taux;
	    
	    $i++;
	  }
	
	$this->db->free();
	
	$calculs = calcul_price($products, $this->remise_percent);
	
	$this->total_remise   = $calculs[3];
	$this->amount_ht      = $calculs[4];
	$this->total_ht       = $calculs[0];
	$this->total_tva      = $calculs[1];
	$this->total_ttc      = $calculs[2];
	$tvas                 = $calculs[5];
	
	$sql = "UPDATE ".MAIN_DB_PREFIX."facture_rec SET ";
	$sql .= " amount = ".price2num($this->amount_ht);
	$sql .= ", remise=".price2num($this->total_remise);
	$sql .= ", total=".price2num($this->total_ht);
	$sql .= ", tva=".price2num($this->total_tva);
	$sql .= ", total_ttc=".price2num($this->total_ttc);

	$sql .= " WHERE rowid = $facid ;";
	
	if ( $this->db->query($sql) )
	  {
	    if ($err == 0)
	      {
		return 1;
	      }
	    else
	      {
		return -3;
	      }
	  }
	else
	  {
	    print "$sql<br>";
	    return -2;
	  }
      }
    else
      {
	print "Error";
            return -1;
      }
  }
  
  /**
   * Applique une remise
   */
  function set_remise($user, $remise)
  {
    if ($user->rights->facture->creer)
      {
	
	$this->remise_percent = $remise ;
	
	$sql = "UPDATE ".MAIN_DB_PREFIX."facture SET remise_percent = ".price2num($remise);
	$sql .= " WHERE rowid = $this->id AND fk_statut = 0 ;";
	
	if ($this->db->query($sql) )
	  {
	    $this->update_price($this->id);
	    return 1;
	  }
	else
	  {
	    print $this->db->error() . ' in ' . $sql;
	    return -1;
	  }
      }
  } 
  /**
   * Rend la facture automatique
   *
   */
  function set_auto($user, $freq, $courant)
  {
    if ($user->rights->facture->creer)
      {
	
	$sql = "UPDATE ".MAIN_DB_PREFIX."facture_rec ";
	$sql .= " SET frequency = '".$freq."', last_gen='".$courant."'";
	$sql .= " WHERE rowid = ".$this->facid.";";
	
	$resql = $this->db->query($sql);

	if ($resql)
	  {
	    $this->frequency 	= $freq;
	    $this->last_gen 	= $courant;
	    return 0;
	  }
	else
	  {
	    print $this->db->error() . ' in ' . $sql;
	    return -1;
	  }
      }
    else
      {
	return -2;
      }
  }
}
?>
