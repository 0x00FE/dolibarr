<?PHP
/* Copyright (C) 2002-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Laurent Destailleur  <eldy@users.sourceforge.net>
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
 * Gestion des factures
 *
 * @author Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * @author Laurent Destailleur  <eldy@users.sourceforge.net>
 * @see http://www.dolibarr.com/
 */

class Facture
{
  var $id;
  var $db;
  var $socidp;
  var $number;
  var $author;
  var $date;
  var $ref;
  var $amount;
  var $remise;
  var $tva;
  var $total;
  var $note;
  var $paye;
  var $db_table;
  var $propalid;
  var $projetid;

  /**
   * Initialisation de la classe
   *
   */
  Function Facture($DB, $soc_idp="", $facid="")
    {
      $this->db = $DB ;
      $this->socidp = $soc_idp;
      $this->products = array();        // Tableau de lignes de factures
      $this->db_table = MAIN_DB_PREFIX."facture";
      $this->amount = 0;
      $this->remise = 0;
      $this->remise_percent = 0;
      $this->tva = 0;
      $this->total = 0;
      $this->propalid = 0;
      $this->projetid = 0;
      $this->id = $facid;
  }
  /**
   * Cr�ation la facture
   *
   *
   */
  Function create($user)
    {
      /* On positionne en mode brouillon la facture */
      $this->brouillon = 1;

      /* Facture r�currente */
      if ($this->fac_rec > 0)
	{
	  require_once DOL_DOCUMENT_ROOT . '/compta/facture/facture-rec.class.php';
	  $_facrec = new FactureRec($this->db, $this->fac_rec);
	  $_facrec->fetch($this->fac_rec);

	  $this->projetid       = $_facrec->projetid;
	  $this->cond_reglement = $_facrec->cond_reglement_id;
	  $this->amount         = $_facrec->amount;
	  $this->remise         = $_facrec->remise;
	  $this->remise_percent = $_facrec->remise_percent;
	}

      $sql = "SELECT fdm,nbjour FROM ".MAIN_DB_PREFIX."cond_reglement WHERE rowid = $this->cond_reglement";
      if ($this->db->query($sql) )
	{
	  if ($this->db->num_rows())
	    {
	      $obj = $this->db->fetch_object(0);
	      $cdr_nbjour = $obj->nbjour;
	      $cdr_fdm = $obj->fdm;
	    }
	  $this->db->free();
	}
      $datelim = $this->date + ( $cdr_nbjour * 3600 * 24 );
      /*
       *  Insertion dans la base
       */
      $socid = $this->socidp;
      $number = $this->number;
      $amount = $this->amount;
      $remise = $this->remise;
      
      if (! $remise)
	{
	  $remise = 0 ;
	}

      if (! $this->projetid)
	{
	  $this->projetid = "NULL";
	}
      
      $totalht = ($amount - $remise);
      $tva = tva($totalht);
      $total = $totalht + $tva;

      $sql = "INSERT INTO $this->db_table (facnumber, fk_soc, datec, amount, remise, remise_percent, datef, note, fk_user_author,fk_projet, fk_cond_reglement, date_lim_reglement) ";
      $sql .= " VALUES ('$number','$socid', now(), '$totalht', '$remise','$this->remise_percent', ".$this->db->idate($this->date).",'".addslashes($this->note)."',$user->id, $this->projetid, $this->cond_reglement,".$this->db->idate($datelim).")";      
      if ( $this->db->query($sql) )
	{
	  $this->id = $this->db->last_insert_id();

	  $sql = "UPDATE ".MAIN_DB_PREFIX."facture SET facnumber='(PROV".$this->id.")' WHERE rowid=".$this->id;
	  $this->db->query($sql);

	  if ($this->id && $this->propalid)
	    {
	      $sql = "INSERT INTO ".MAIN_DB_PREFIX."fa_pr (fk_facture, fk_propal) VALUES (".$this->id.",".$this->propalid.")";
	      $this->db->query($sql);
	    }

	  if ($this->id && $this->commandeid)
	    {
	      $sql = "INSERT INTO ".MAIN_DB_PREFIX."co_fa (fk_facture, fk_commande) VALUES (".$this->id.",".$this->commandeid.")";
	      $this->db->query($sql);
	    }

	  /*
	   * Produits/services
	   *
	   */
	  for ($i = 0 ; $i < sizeof($this->products) ; $i++)
	    {
	      $prod = new Product($this->db, $this->products[$i]);
	      $prod->fetch($this->products[$i]);

	      $result_insert = $this->addline($this->id, 
					      $prod->libelle,
					      $prod->price,
					      $this->products_qty[$i], 
					      $prod->tva_tx, 
					      $this->products[$i], 
					      $this->products_remise_percent[$i],
					      $this->products_date_start[$i],
					      $this->products_date_end[$i]
					      );


	      if ( $result_insert < 0)
		{
		  print $sql . '<br>' . $this->db->error() .'<br>';
		}
	    }
	  /*
	   * Produits de la facture r�currente
	   *
	   */
	  if ($this->fac_rec > 0)
	    {
	      for ($i = 0 ; $i < sizeof($_facrec->lignes) ; $i++)
		{
		  if ($_facrec->lignes[$i]->produit_id)
		    {
		      $prod = new Product($this->db, $_facrec->lignes[$i]->produit_id);
		      $prod->fetch($_facrec->lignes[$i]->produit_id);
		    }
		  
		  $result_insert = $this->addline($this->id, 
						  addslashes($_facrec->lignes[$i]->desc),
						  $_facrec->lignes[$i]->subprice,
						  $_facrec->lignes[$i]->qty,
						  $_facrec->lignes[$i]->tva_taux,
						  $_facrec->lignes[$i]->produit_id,
						  $_facrec->lignes[$i]->remise_percent);
		  
		  
		  if ( $result_insert < 0)
		    {
		      print $sql . '<br>' . $this->db->error() .'<br>';
		    }
		}
	    }
	  /*
	   *
	   *
	   */
	  $this->updateprice($this->id);	  
	  return $this->id;
	}
      else
	{
	  print "Erreur facture.class fonction create: ".$this->db->error() . '<br>'.$sql.'<br>';
	  return 0;
	}
    }

  /**
   * Recup�re l'objet facture et ses lignes de factures
   *
   */
  Function fetch($rowid, $societe_id=0)
    {
      $sql = "SELECT f.fk_soc,f.facnumber,f.amount,f.tva,f.total,f.total_ttc,f.remise,f.remise_percent,".$this->db->pdate("f.datef")." as df,f.fk_projet,".$this->db->pdate("f.date_lim_reglement")." as dlr, c.rowid as cond_regl_id, c.libelle, c.libelle_facture, f.note, f.paye, f.fk_statut, f.fk_user_author";
      $sql .= " FROM ".MAIN_DB_PREFIX."facture as f, ".MAIN_DB_PREFIX."cond_reglement as c";
      $sql .= " WHERE f.rowid=$rowid AND c.rowid = f.fk_cond_reglement";
      
      if ($societe_id > 0) 
	{
	  $sql .= " AND f.fk_soc = ".$societe_id;
	}

      if ($this->db->query($sql) )
	{
	  if ($this->db->num_rows())
	    {
	      $obj = $this->db->fetch_object(0);
	      
	      $this->id                 = $rowid;
	      $this->datep              = $obj->dp;
	      $this->date               = $obj->df;
	      $this->ref                = $obj->facnumber;
	      $this->amount             = $obj->amount;
	      $this->remise             = $obj->remise;
	      $this->total_ht           = $obj->total;
	      $this->total_tva          = $obj->tva;
	      $this->total_ttc          = $obj->total_ttc;
	      $this->paye               = $obj->paye;
	      $this->remise_percent     = $obj->remise_percent;
	      $this->socidp             = $obj->fk_soc;
	      $this->statut             = $obj->fk_statut;
	      $this->date_lim_reglement = $obj->dlr;
	      $this->cond_reglement_id  = $obj->cond_regl_id;
	      $this->cond_reglement     = $obj->libelle;
	      $this->cond_reglement_facture = $obj->libelle_facture;
	      $this->projetid           = $obj->fk_projet;
	      $this->note               = stripslashes($obj->note);
	      $this->user_author        = $obj->fk_user_author;
	      $this->lignes             = array();

	      if ($this->statut == 0)
		{
		  $this->brouillon = 1;
		}

	      $this->db->free();

	      /*
	       * Lignes
	       */

	      $sql = "SELECT l.fk_product, l.description, l.price, l.qty, l.rowid, l.tva_taux, l.remise, l.remise_percent, l.subprice, ".$this->db->pdate("l.date_start")." as date_start,".$this->db->pdate("l.date_end")." as date_end";
	      $sql .= " FROM ".MAIN_DB_PREFIX."facturedet as l WHERE l.fk_facture = ".$this->id;
	
	      $result = $this->db->query($sql);
	      if ($result)
		{
		  $num = $this->db->num_rows();
		  $i = 0; $total = 0;
		  
		  while ($i < $num)
		    {
		      $objp = $this->db->fetch_object($i);
		      $faclig = new FactureLigne($this->db);
		      $faclig->desc           = stripslashes($objp->description);
		      $faclig->qty            = $objp->qty;
		      $faclig->price          = $objp->price;
		      $faclig->subprice       = $objp->subprice;
		      $faclig->tva_taux       = $objp->tva_taux;
		      $faclig->remise         = $objp->remise;
		      $faclig->remise_percent = $objp->remise_percent;
		      $faclig->produit_id     = $objp->fk_product;
		      $faclig->date_start     = $objp->date_start;
		      $faclig->date_end       = $objp->date_end;
		      $this->lignes[$i] = $faclig;
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
   * Recup�re l'objet client li� � la facture
   *
   */
  Function fetch_client()
    {
      $client = new Societe($this->db);
      $client->fetch($this->socidp);
      $this->client = $client;
    }
  /**
   * Valide la facture
   *
   *
   */
  Function valid($userid, $dir)
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
   * Class la facture
   *
   *
   */
  Function classin($cat_id)
    {
      $sql = "UPDATE ".MAIN_DB_PREFIX."facture SET fk_projet = $cat_id";
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
   * Supprime la facture
   *
   */
  Function delete($rowid)
    {
      $sql = "DELETE FROM ".MAIN_DB_PREFIX."facture_tva_sum WHERE fk_facture = $rowid;";

      if ( $this->db->query( $sql) )
	{
	  $sql = "DELETE FROM ".MAIN_DB_PREFIX."fa_pr WHERE fk_facture = $rowid;";

	  if ($this->db->query( $sql) )
	    {

	      $sql = "DELETE FROM ".MAIN_DB_PREFIX."co_fa WHERE fk_facture = $rowid;";
	      
	      if ($this->db->query( $sql) )
		{
		  $sql = "DELETE FROM ".MAIN_DB_PREFIX."facturedet WHERE fk_facture = $rowid;";
	      
		  if ($this->db->query( $sql) )
		    {
		      $sql = "DELETE FROM ".MAIN_DB_PREFIX."facture WHERE rowid = $rowid AND fk_statut = 0;";
		      
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
	      else
		{
		  print "Err : ".$this->db->error();
		  return -3;
		}
	    }
	  else
	    {
	      print "Err : ".$this->db->error();
	      return -4;
	    }
	}
      else
	{
	  print "Err : ".$this->db->error();
	  return -5;
	}
    }

  /**
   * Retourne le libell� du statut d'une facture (brouillon, valid�e, abandonn�e, pay�e)
   *
   */
  Function get_libstatut()
    {
		return $this->LibStatut($this->paye,$this->statut);
    }

  /**
   * Tag la facture comme pay�e compl�tement
   *
   */
  Function set_payed($rowid)
    {
      $sql = "UPDATE ".MAIN_DB_PREFIX."facture set paye=1 WHERE rowid = $rowid ;";
      $return = $this->db->query( $sql);
    }
  /**
   * Tag la facture comme paiement commenc�e
   *
   */
  Function set_paiement_started($rowid)
    {
      $sql = "UPDATE ".MAIN_DB_PREFIX."facture set fk_statut=2 WHERE rowid = $rowid ;";
      $return = $this->db->query( $sql);
    }
  /**
   * Tag la facture comme abandonn�e
   *
   */
  Function set_canceled($rowid)
    {
      $sql = "UPDATE ".MAIN_DB_PREFIX."facture set fk_statut=3 WHERE rowid = $rowid ;";
      $return = $this->db->query( $sql);
    }
  /**
   * Tag la facture comme valid�e et valide la facture
   *
   */
  Function set_valid($rowid, $user, $soc)
    {
      if ($this->brouillon)
	{
	  $action_notify = 2; // ne pas modifier cette valeur

	  $numfa = facture_get_num($soc); // d�finit dans includes/modules/facture

	  $sql = "UPDATE ".MAIN_DB_PREFIX."facture set facnumber='$numfa', fk_statut = 1, fk_user_valid = $user->id";

	  /* Si l'option est activ�e on force la date de facture */

	  if (defined("FAC_FORCE_DATE_VALIDATION") && FAC_FORCE_DATE_VALIDATION == "1")
	    {
	      $sql .= ", datef=now()";
	    }
	  $sql .= " WHERE rowid = $rowid ;";

	  $result = $this->db->query( $sql);

	  if (! $result) { print "Err : ".$this->db->error(); return -1; }
     
	  /*
	   * Notify
	   *
	   */
	  $filepdf = FAC_OUTPUTDIR . "/" . $this->ref . "/" . $this->ref . ".pdf";
	  
	  $mesg = "La facture ".$this->ref." a �t� valid�e.\n";
	  
	  $notify = New Notify($this->db);
	  $notify->send($action_notify, $this->socidp, $mesg, "facture", $rowid, $filepdf);
	  /*
	   * Update Stats
	   *
	   */
	  $sql = "SELECT fk_product FROM ".MAIN_DB_PREFIX."facturedet WHERE fk_facture = ".$this->id;
	  $sql .= " AND fk_product > 0";
	  
	  $result = $this->db->query($sql);
	  
	  if ($result)
	    {
	      $num = $this->db->num_rows();
	      $i = 0;
	      while ($i < $num)	  
		{
		  $obj = $this->db->fetch_object($i);
		  
		  $sql = "UPDATE ".MAIN_DB_PREFIX."product SET nbvente=nbvente+1 WHERE rowid = ".$obj->fk_product;
		  $db2 = $this->db->clone();
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
   * Ajoute un produit dans l'objet facture
   *
   */
  Function add_product($idproduct, $qty, $remise_percent, $datestart='', $dateend='')
    {
      if ($idproduct > 0)
	{
	  $i = sizeof($this->products);     // On recupere nb de produit deja dans tableau products
	  $this->products[$i] = $idproduct; // On ajoute a la suite
	  if (!$qty)
	    {
	      $qty = 1 ;
	    }
	  $this->products_qty[$i] = $qty;
	  $this->products_remise_percent[$i] = $remise_percent;
	  if ($datestart) { $this->products_date_start[$i] = $datestart; }
	  if ($dateend)   { $this->products_date_end[$i] = $dateend; }
	}
    }

  /**
   * Ajoute une ligne de facture (associ� � aucun produit/service pr�d�fini)
   *
   */
  Function addline($facid, $desc, $pu, $qty, $txtva, $fk_product=0, $remise_percent=0, $datestart='', $dateend='')
    {
      if ($this->brouillon)
	{
	  if (strlen(trim($qty))==0)
	    {
	      $qty=1;
	    }
	  $remise = 0;
	  $_price = $pu;
	  $subprice = $pu;

	  if (trim(strlen($remise_percent)) > 0)
	    {
	      $remise = ($pu * $remise_percent / 100);
	      $_price = ($pu - $remise);
	    }

	  /* Formatage des prix */
	  $_price    = ereg_replace(",",".",$_price);
	  $subprice  = ereg_replace(",",".",$subprice);
	  
	  $sql = "INSERT INTO ".MAIN_DB_PREFIX."facturedet (fk_facture,description,price,qty,tva_taux, fk_product, remise_percent, subprice, remise, date_start, date_end)";
	  $sql .= " VALUES ($facid, '".addslashes($desc)."','$_price','$qty','$txtva',$fk_product,'$remise_percent','$subprice','$remise', ";

	  if ($datestart) { $sql.= "'$datestart', "; }
	  else { $sql.=" null, "; }
	  if ($dateend) { $sql.= "'$dateend' "; }
	  else { $sql.=" null "; }
      $sql.=")";
     
	  if ( $this->db->query( $sql) )
	    {
	      $this->updateprice($facid);
	      return 1;
	    }
	  else
	    {
	      print "Erreur facture.class fonction addline : ".$this->db->error()."<br>$sql<br>";
	      return -1;
	    }
	}
    }
  /**
   * Mets � jour une ligne de facture
   * Retourne 0 si erreur
   */
  Function updateline($rowid, $desc, $pu, $qty, $remise_percent=0, $datestart='', $dateend='')
    {
      if ($this->brouillon)
	{
	  if (strlen(trim($qty))==0)
	    {
	      $qty=1;
	    }
	  $remise = 0;
	  $price = ereg_replace(",",".",$pu);
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
	  $sql .= ",price='"    .     ereg_replace(",",".",$price)."'";
	  $sql .= ",subprice='" .     ereg_replace(",",".",$subprice)."'";
	  $sql .= ",remise='".        ereg_replace(",",".",$remise)."'";
	  $sql .= ",remise_percent='".ereg_replace(",",".",$remise_percent)."'";
	  $sql .= ",qty='$qty'";

  	  if ($datestart) { $sql.= ",date_start='$datestart'"; }
	  else { $sql.=",date_start=null"; }
	  if ($dateend) { $sql.= ",date_end='$dateend'"; }
	  else { $sql.=",date_end=null"; }

	  $sql .= " WHERE rowid = $rowid ;";

	  $result = $this->db->query( $sql);
      if ($result) {
	    $this->updateprice($this->id);
	  }
	  else {
	    print "Erreur : ".$this->db->error()."<br>".$sql; 
	  }
      return $result;

	}
    }
  /**
   * Supprime une ligne
   *
   */
  Function deleteline($rowid)
    {
      if ($this->brouillon)
	{
	  $sql = "DELETE FROM ".MAIN_DB_PREFIX."facturedet WHERE rowid = $rowid;";
	  $result = $this->db->query( $sql);

	  $this->updateprice($this->id);
	}
    }
  /**
   * Mise � jour des sommes de la facture
   *
   */
  Function updateprice($facid)
    {
      include_once DOL_DOCUMENT_ROOT . "/lib/price.lib.php";
      $err=0;
      $sql = "SELECT price, qty, tva_taux FROM ".MAIN_DB_PREFIX."facturedet WHERE fk_facture = $facid;";
  
      $result = $this->db->query($sql);

      if ($result)
	{
	  $num = $this->db->num_rows();
	  $i = 0;
	  while ($i < $num)	  
	    {
	      $obj = $this->db->fetch_object($i);

	      $products[$i][0] = $obj->price;
	      $products[$i][1] = $obj->qty;
	      $products[$i][2] = $obj->tva_taux;

	      $i++;
	    }

	  $this->db->free();
	  /*
	   *
	   */
	  $calculs = calcul_price($products, $this->remise_percent);

	  $this->total_remise   = $calculs[3];
	  $this->amount_ht      = $calculs[4];
	  $this->total_ht       = $calculs[0];
	  $this->total_tva      = $calculs[1];
	  $this->total_ttc      = $calculs[2];
	  $tvas                 = $calculs[5];
	  /*
	   *
	   */

	  $sql = "UPDATE ".MAIN_DB_PREFIX."facture ";
	  $sql .= "SET amount ='".ereg_replace(",",".",$this->amount_ht)."'";
	  $sql .= ", remise='".   ereg_replace(",",".",$this->total_remise)."'";
	  $sql .= ", total='".    ereg_replace(",",".",$this->total_ht)."'";
	  $sql .= ", tva='".      ereg_replace(",",".",$this->total_tva)."'";
	  $sql .= ", total_ttc='".ereg_replace(",",".",$this->total_ttc)."'";
	  
	  $sql .= " WHERE rowid = $facid ;";
	  
	  if ( $this->db->query($sql) )
	    {
	      
	      $sql = "DELETE FROM ".MAIN_DB_PREFIX."facture_tva_sum WHERE fk_facture=".$this->id;

	      if ( $this->db->query($sql) )
		{
		  foreach ($tvas as $key => $value)
		    {
		      $sql = "REPLACE INTO ".MAIN_DB_PREFIX."facture_tva_sum SET fk_facture=".$this->id;
		      $sql .= ", amount = '".$tvas[$key]."'";
		      $sql .= ", tva_tx='".$key."'";
		      
		      if (! $this->db->query($sql) )
			{
			  print "$sql<br>";
			  $err++;
			}
		    }
		}
	      else
		{
		  $err++;
		}

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
   *
   */
  Function set_remise($user, $remise)
    {
      if ($user->rights->facture->creer)
	{

	  $this->remise_percent = $remise ;

	  $sql = "UPDATE ".MAIN_DB_PREFIX."facture SET remise_percent = ".ereg_replace(",",".",$remise);
	  $sql .= " WHERE rowid = $this->id AND fk_statut = 0 ;";
	  
	  if ($this->db->query($sql) )
	    {
	      $this->updateprice($this->id);
	      return 1;
	    }
	  else
	    {
	      print $this->db->error() . ' in ' . $sql;
	      return 0;
	    }
	}
  }
  /**
   * Envoie une relance
   *
   *
   */
  Function send_relance($destinataire, $replytoname, $replytomail, $user)
    {
      $soc = new Societe($this->db, $this->socidp);

      $file = FAC_OUTPUTDIR . "/" . $this->ref . "/" . $this->ref . ".pdf";

      if (file_exists($file))
	{

	  $sendto = $soc->contact_get_email($destinataire);
	  $sendtoid = $destinataire;
	  
	  if (strlen($sendto))
	    {
	      
	      $subject = "Relance facture $this->ref";
	      $message = "Nous apportons � votre connaissance que la facture $this->ref n'a toujours pas �t� r�gl�e.\n\nCordialement\n\n";
	      $filename = "$this->ref.pdf";
	      
	      $replyto = $replytoname . " <".$replytomail .">";
	      
	      $mailfile = new CMailFile($subject,
					$sendto,
					$replyto,
					$message,
					array($file), 
					array("application/pdf"), 
					array($filename)
					);
	      
	      if ( $mailfile->sendfile() )
		{
		  
		  $sendto = htmlentities($sendto);
		  
		  $sql = "INSERT INTO ".MAIN_DB_PREFIX."actioncomm (datea,fk_action,fk_soc,note,fk_facture, fk_contact,fk_user_author, label, percent) VALUES (now(), 10 ,$this->socidp ,'Relance envoy�e � $sendto',$this->id, $sendtoid, $user->id, 'Relance Facture par mail',100);";
		  
		  if (! $this->db->query($sql) )
		    {
		      print $this->db->error();
		      print "<p>$sql</p>";
		    }	      	      	      
		}
	      else
		{
		  print "<b>!! erreur d'envoi<br>$sendto<br>$replyto<br>$filename";
		}	  
	    }
	  else
	    {
	      print "Can't get email $sendto";
	    }
	}      
    }
  /** 
   * Renvoie la liste des sommes de tva
   *
   */
  Function getSumTva()
  {
    $sql = "SELECT amount, tva_tx FROM ".MAIN_DB_PREFIX."facture_tva_sum WHERE fk_facture = ".$this->id;
    if ($this->db->query($sql))
      {
	$num = $this->db->num_rows();
	$i = 0;
	while ($i < $num)	  
	  {
	    $row = $this->db->fetch_row($i);
	    $tvs[$row[1]] = $row[0];
	    $i++;
	  }
	
	return $tvs;
      }
    else
      {
	return -1;
      }
  }

  /** 
   * Renvoie la sommes des paiements deja effectu�s
   * Utilis� entre autre par certains mod�les de factures
   */
  Function getSommePaiement()
  {
    $sql = "SELECT sum(amount) FROM ".MAIN_DB_PREFIX."paiement_facture WHERE fk_facture = ".$this->id;
    if ($this->db->query($sql))
      {
	$row = $this->db->fetch_row(0);
	return $row[0];
      }
    else
      {
	return -1;
      }
  }

  /**
   * RODO TODO
   *
   */
  Function pdf()
    {      

    }

  /**
   * Renvoi un libell� du statut
   *
   */
  Function LibStatut($paye,$statut)
    {
		if (! $paye)
		  {
		    if ($statut == 0) return 'Brouillon (� valider)';
		    if ($statut == 3) return 'Abandonn�e';
			return 'Valid�e (� payer)';
		  }
		else
		  {
		    return 'Pay�e';
    	  }
    }


  /**
   * Mets � jour les commentaires
   *
   */
  Function update_note($note)
    {
      $sql = "UPDATE ".MAIN_DB_PREFIX."facture SET note = '$note'";
      $sql .= " WHERE rowid =". $this->id;
      
      if ($this->db->query($sql) )
	{
	  return 1;
	}
      else
	{
	  print $this->db->error() . ' in ' . $sql;
	  return -1;
	}
    }

  /*
   * Information sur l'objet
   *
   */
  Function info($id) 
    {
      $sql = "SELECT c.rowid, ".$this->db->pdate("datec")." as datec";
      $sql .= ", fk_user_author, fk_user_valid";
      $sql .= " FROM ".MAIN_DB_PREFIX."facture as c";
      $sql .= " WHERE c.rowid = $id";
      
      if ($this->db->query($sql)) 
	{
	  if ($this->db->num_rows()) 
	    {
	      $obj = $this->db->fetch_object($result , 0);

	      $this->id = $obj->rowid;

	      $cuser = new User($this->db, $obj->fk_user_author);
	      $cuser->fetch();
	      $this->user_creation     = $cuser;

	      $vuser = new User($this->db, $obj->fk_user_valid);
	      $vuser->fetch();
	      $this->user_validation = $vuser;

	      $this->date_creation     = $obj->datec;

	    }
	  $this->db->free();

	}
      else
	{
	  print $this->db->error();
	}
    }

}

class FactureLigne
{
  Function FactureLigne($DB)
    {
        $this->db= $DB ;
    }

  /**
   * Recup�re l'objet ligne de facture
   *
   */
  Function fetch($rowid, $societe_id=0)
    {
      $sql = "SELECT fk_product, description, price, qty, rowid, tva_taux, remise, remise_percent, subprice, ".$this->db->pdate("date_start")." as date_start,".$this->db->pdate("date_end")." as date_end";
      $sql .= " FROM ".MAIN_DB_PREFIX."facturedet WHERE rowid = ".$rowid;

      $result = $this->db->query($sql);
      if ($result)
	  {
	      $objp = $this->db->fetch_object($i);
	      $this->desc           = stripslashes($objp->description);
	      $this->qty            = $objp->qty;
	      $this->price          = $objp->price;
	      $this->subprice       = $objp->subprice;
	      $this->tva_taux       = $objp->tva_taux;
	      $this->remise         = $objp->remise;
	      $this->remise_percent = $objp->remise_percent;
	      $this->produit_id     = $objp->fk_product;
	      $this->date_start     = $objp->date_start;
	      $this->date_end       = $objp->date_end;
	      $i++;
	  }
	  else {
	      print "Erreur ".$this->db->error()."<br>".$sql; 
	  }
	  $this->db->free();
    }



}

?>
