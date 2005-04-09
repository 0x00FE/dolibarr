<?php
/* Copyright (C) 2002-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Sebastien Di Cintio  <sdicintio@ressource-toi.org>
 * Copyright (C) 2004      Benoit Mortier       <benoit.mortier@opensides.be>
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
        \file       htdocs/facture.class.php
        \ingroup    facture
        \brief      Fichier de la classe des factures clients
        \version    $Revision$
*/


/**
        \class      Facture
        \brief      Classe permettant la gestion des factures clients
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
  var $propalid;
  var $projetid;
  var $prefixe_facture;

  /**
   *    \brief  Constructeur de la classe
   *    \param  DB          handler acc�s base de donn�es
   *    \param  soc_idp     id societe ("" par defaut)
   *    \param  facid       id facture ("" par defaut)
   */
  function Facture($DB, $soc_idp="", $facid="")
    {
      $this->db = $DB ;
      $this->socidp = $soc_idp;
      $this->products = array();        // Tableau de lignes de factures
      
      $this->amount = 0;
      $this->remise = 0;
      $this->remise_percent = 0;
      $this->tva = 0;
      $this->total = 0;
      $this->propalid = 0;
      $this->projetid = 0;
      $this->id = $facid;
      $this->prefixe_facture = '';      // utilis� dans le module de num�rotation saturne
      $this->remise_exceptionnelle = 0;
  }
  
  /**
   *    \brief      Cr�ation de la facture en base
   *    \param      user       object utilisateur qui cr�e
   *
   */
  function create($user)
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
	      $obj = $this->db->fetch_object();
	      $cdr_nbjour = $obj->nbjour;
	      $cdr_fdm = $obj->fdm;
	    }
	  $this->db->free();
	}
      $datelim = $this->date + ( $cdr_nbjour * 3600 * 24 );

      if ($cdr_fdm)
	{
	  $mois=date('m', $datelim);
	  $annee=date('Y', $datelim);
	  $fins=array(31,28,31,30,31,30,31,31,30,31,30,31);
	  $datelim=mktime(0,0,0,$mois,$fins[$mois-1],$annee);
	}
      
      /*
       * Lecture de la remise exceptionnelle
       *
       */
      $sql  = "SELECT rowid, rc.amount_ht, fk_soc, fk_user";
      $sql .= " FROM ".MAIN_DB_PREFIX."societe_remise_except as rc";
      $sql .= " WHERE rc.fk_soc =". $this->socidp;
      $sql .= " AND fk_facture IS NULL";
      
      $resql = $this->db->query($sql) ;

      if ( $resql)
	{
	  $nurmx = $this->db->num_rows($resql);
	  
	  if ($nurmx > 0)
	    {
	      $row = $this->db->fetch_row($resql);
	      $this->remise_exceptionnelle = $row;
	    }
	  $this->db->free($resql);
	}      
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

      if (strlen($this->mode_reglement)==0) $this->mode_reglement = 0;


      if (! $this->projetid)
	{
	  $this->projetid = "NULL";
	}
      
      $totalht = ($amount - $remise);
      $tva = tva($totalht);
      $total = $totalht + $tva;

      $sql = "INSERT INTO ".MAIN_DB_PREFIX."facture (facnumber, fk_soc, datec, amount, remise, remise_percent";
      $sql .= ", datef, note, fk_user_author,fk_projet";
      $sql .= ", fk_cond_reglement, fk_mode_reglement, date_lim_reglement) ";

      $sql .= " VALUES ('$number','$socid', now(), '$totalht', '$remise'";
      $sql .= ",'$this->remise_percent', ".$this->db->idate($this->date);
      $sql .= ",'".addslashes($this->note)."',$user->id, $this->projetid";
      $sql .= ",".$this->cond_reglement.",".$this->mode_reglement.",".$this->db->idate($datelim).")";      
      if ( $this->db->query($sql) )
	{
	  $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."facture");

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
		  dolibarr_print_error($this->db);
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
		      dolibarr_print_error($this->db);
		    }
		}
	    }

	  /*
	   *
	   *
	   */

	  $this->updateprice($this->id);

	  /*
	   * Affectation de la remise exceptionnelle
	   */
	  $this->_affect_remise_exceptionnelle();

	  $this->updateprice($this->id);

	  return $this->id;
	}
      else
	{
	  dolibarr_print_error($this->db);
	}
    }

  /*
   * Affecte la remise exceptionnelle
   *
   */

  function _affect_remise_exceptionnelle()
  {
    if ($this->remise_exceptionnelle[1] > 0)
      {
	if ($this->remise_exceptionnelle[1] > ($this->total_ht * 0.9))
	  {

	    $remise = floor($this->total_ht * 0.9);

	    $result_insert = $this->addline($this->id, 
					    addslashes("Remise exceptionnelle"),
					    (0 - $remise),
					    1,
					    '19.6');

	    $reliquat = $this->remise_exceptionnelle[1] - $remise;

	    $sql = "UPDATE ".MAIN_DB_PREFIX."societe_remise_except";
	    $sql .= " SET fk_facture = ".$this->id;
	    $sql .= " ,amount_ht = '".ereg_replace(",",".",$remise)."'";
	    $sql .= " WHERE rowid =".$this->remise_exceptionnelle[0];
	    $sql .= " AND fk_soc =". $this->socidp;
	    $this->db->query( $sql) ; 


	    $sql = "INSERT INTO ".MAIN_DB_PREFIX."societe_remise_except";
	    $sql .= " (fk_soc, datec, amount_ht, fk_user) ";
	    $sql .= " VALUES ";
	    $sql .= " (".$this->socidp;
	    $sql .= " ,now()";
	    $sql .= " ,'".ereg_replace(",",".",$reliquat)."'";
	    $sql .= " ,".$this->remise_exceptionnelle[3];
	    $sql .= ")";

	    $this->db->query( $sql) ; 

	  }
	else
	  {
	    $remise = $this->remise_exceptionnelle[1];

	    $result_insert = $this->addline($this->id, 
					    addslashes("Remise exceptionnelle"),
					    (0 - $remise),
					    1,
					    '19.6');

	    $sql = "UPDATE ".MAIN_DB_PREFIX."societe_remise_except";
	    $sql .= " SET fk_facture = ".$this->id;
	    $sql .= " WHERE rowid =".$this->remise_exceptionnelle[0];
	    $sql .= " AND fk_soc =". $this->socidp;
	    $this->db->query( $sql) ; 


	  }
      }    
  }

  /**
   *    \brief      Recup�re l'objet facture et ses lignes de factures
   *    \param      rowid       id de la facture a r�cup�rer
   *    \param      societe_id  id de societe
   *    \return     int         1 si ok, < 0 si erreur
   */
  function fetch($rowid, $societe_id=0)
    {
      //dolibarr_syslog("Facture::Fetch rowid : $rowid, societe_id : $societe_id");

      $sql = "SELECT f.fk_soc,f.facnumber,f.amount,f.tva,f.total,f.total_ttc,f.remise,f.remise_percent";
      $sql .= ",".$this->db->pdate("f.datef")." as df,f.fk_projet";
      $sql .= ",".$this->db->pdate("f.date_lim_reglement")." as dlr";
      $sql .= ", c.rowid as cond_regl_id, c.libelle, c.libelle_facture";
      $sql .= ", f.note, f.paye, f.fk_statut, f.fk_user_author";
      $sql .= ", f.fk_mode_reglement";
      $sql .= " FROM ".MAIN_DB_PREFIX."facture as f, ".MAIN_DB_PREFIX."cond_reglement as c";
      $sql .= " WHERE f.rowid=$rowid AND c.rowid = f.fk_cond_reglement";
      if ($societe_id > 0) 
	{
	  $sql .= " AND f.fk_soc = ".$societe_id;
	}
      $result = $this->db->query($sql);

      if ($result)
	{
	  if ($this->db->num_rows($result))
	    {
	      $obj = $this->db->fetch_object($result);

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
	      $this->mode_reglement     = $obj->fk_mode_reglement;

	      if ($this->statut == 0)
		{
		  $this->brouillon = 1;
		}


	      /*
	       * Lignes
	       */

	      $sql = "SELECT l.fk_product, l.description, l.price, l.qty, l.rowid, l.tva_taux, l.remise, l.remise_percent, l.subprice, ".$this->db->pdate("l.date_start")." as date_start,".$this->db->pdate("l.date_end")." as date_end";
	      $sql .= " FROM ".MAIN_DB_PREFIX."facturedet as l WHERE l.fk_facture = ".$this->id;
	
	      $result2 = $this->db->query($sql);
	      if ($result2)
		{
		  $num = $this->db->num_rows($result2);
		  $i = 0; $total = 0;
		  
		  while ($i < $num)
		    {
		      $objp = $this->db->fetch_object($result2);
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
	    
		  $this->db->free($result2);

		  return 1;
		} 
	      else
		{
		  dolibarr_syslog("Erreur Facture::Fetch rowid=$rowid, Erreur dans fetch des lignes");
		  return -3;
		}
	    }
	  else
	    {
	      dolibarr_syslog("Erreur Facture::Fetch rowid=$rowid numrows=0 sql=$sql");
	      $this->error="Bill with id $rowid not found sql=$sql";
		  return -2;
	    }

	  $this->db->free($result);
	}
      else
	{
	  dolibarr_syslog("Erreur Facture::Fetch rowid=$rowid Erreur dans fetch de la facture");
	  return -1;
	}
  }

  /**
   * \brief     Recup�re l'objet client li� � la facture
   *
   */
  function fetch_client()
    {
      $client = new Societe($this->db);
      $client->fetch($this->socidp);
      $this->client = $client;
    }

  /**
   * \brief     Valide la facture
   * \param     userid      id de l'utilisateur qui valide
   */
  function valid($userid)
    {
      $sql = "UPDATE ".MAIN_DB_PREFIX."facture SET fk_statut = 1, date_valid=now(), fk_user_valid=$userid";

      $sql .= " WHERE rowid = $this->id AND fk_statut = 0 ;";

      if ($this->db->query($sql) )
	{
	  return 1;
	}
      else
	{
	  dolibarr_print_error($this->db);
	}
    }

  /**
   * \brief     Classe la facture
   * \param     cat_id      id de la cat�gorie dans laquelle classer la facture
   *
   */
  function classin($cat_id)
    {
      $sql = "UPDATE ".MAIN_DB_PREFIX."facture SET fk_projet = $cat_id";
      $sql .= " WHERE rowid = $this->id;";
      
      if ($this->db->query($sql) )
	{
	  return 1;
	}
      else
	{
	  dolibarr_print_error($this->db);
	}
    }

  /**
   * \brief     Supprime la facture
   * \param     rowid      id de la facture � supprimer
   */
  function delete($rowid)
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
		      /*
		       * On repositionne la remise
		       */
		      $sql = "UPDATE ".MAIN_DB_PREFIX."societe_remise_except";
		      $sql .= " SET fk_facture = NULL WHERE fk_facture = $rowid";
		      
		      if ($this->db->query( $sql) )
			{
			  
			  $sql = "DELETE FROM ".MAIN_DB_PREFIX."facture WHERE rowid = $rowid AND fk_statut = 0;";

			  $this->db->query( $sql) ; 


			  return 1;
			}
		      else
			{
        	  dolibarr_print_error($this->db);
			}

		    }
		  else
		    {
        	  dolibarr_print_error($this->db);
		    }
		}
	      else
		{
    	  dolibarr_print_error($this->db);
		}
	    }
	  else
	    {
    	  dolibarr_print_error($this->db);
	    }
	}
      else
	{
	  dolibarr_print_error($this->db);
	}
    }

  /**
   *    \brief     Tag la facture comme pay�e compl�tement
   *    \param     rowid       id de la facture � modifier
   */
  function set_payed($rowid)
    {
      $sql = "UPDATE ".MAIN_DB_PREFIX."facture set paye=1 WHERE rowid = ".$rowid ;
      $return = $this->db->query( $sql);
    }
  /**
   *    \brief     Tag la facture comme pay�e compl�tement
   *    \param     rowid       id de la facture � modifier
   */
  function set_unpayed($rowid)
    {
      $sql = "UPDATE ".MAIN_DB_PREFIX."facture set paye=0 WHERE rowid = ".$rowid ;
      $return = $this->db->query( $sql);
    }
  /**
   *    \brief     Tag la facture comme paiement commenc�e
   *    \param     rowid       id de la facture � modifier
   */
  function set_paiement_started($rowid)
    {
      $sql = "UPDATE ".MAIN_DB_PREFIX."facture set fk_statut=2 WHERE rowid = $rowid ;";
      $return = $this->db->query( $sql);
    }

  /**
   *    \brief     Tag la facture comme abandonn�e
   *    \param     rowid       id de la facture � modifier
   */
  function set_canceled($rowid)
    {
      $sql = "UPDATE ".MAIN_DB_PREFIX."facture set fk_statut=3 WHERE rowid = $rowid ;";
      $return = $this->db->query( $sql);
    }

  /**
   * \brief     Tag la facture comme valid�e et valide la facture
   * \param     rowid        id de la facture � valider
   * \param     user         utilisateur qui valide la facture
   * \param     soc          societe
   * \param     force_number force le num�ro de facture
   */
  function set_valid($rowid, $user, $soc, $force_number='')
    {
      global $conf;
      
      if ($this->brouillon)
	{
	  $action_notify = 2; // ne pas modifier cette valeur

	  if ($force_number)
	    {
	      $numfa=$force_number;
	    }
	  else
	    {
	      $numfa = facture_get_num($soc, $this->prefixe_facture); // d�finit dans includes/modules/facture
	    }



	  $sql = "UPDATE ".MAIN_DB_PREFIX."facture set facnumber='$numfa', fk_statut = 1, fk_user_valid = $user->id";

	  /* Si l'option est activ�e on force la date de facture */

	  if (defined("FAC_FORCE_DATE_VALIDATION") && FAC_FORCE_DATE_VALIDATION == "1")
	    {
	      $sql .= ", datef=now()";
	    }
	  $sql .= " WHERE rowid = $rowid ;";

	  $result = $this->db->query( $sql);

	  if (! $result)
	    {
	      dolibarr_syslog("Facture::set_valid()  - 10");
	      dolibarr_print_error($this->db);
	    }
     
	  /*
	   * On cr�e les contrats de services automatiquement si
	   * l'option CONTRACT_AUTOCREATE_FROM_BILL est active
	   * (Cas ou les contrats sont implicites comme lors de ventes de services en lignes)
	   */      
	  if ($conf->contrat->enabled)
	    {
	      if (defined("CONTRACT_AUTOCREATE_FROM_BILL") && CONTRACT_AUTOCREATE_FROM_BILL == "1")
		{
		  $contrat = new Contrat($this->db);
		  $contrat->create_from_facture($rowid, $user, $soc->id);
		}
	    }
        
	  /*
	   * Notify
	   *
	   */
		$forbidden_chars=array("/","\\",":","*","?","\"","<",">","|","[","]",",",";","=");
		$facref = str_replace($forbidden_chars,"_",$this->ref);
		$filepdf = FAC_OUTPUTDIR . "/" . $facref . "/" . $facref . ".pdf";
	  
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
		  $obj = $this->db->fetch_object($result);
		  
		  $sql = "UPDATE ".MAIN_DB_PREFIX."product SET nbvente=nbvente+1 WHERE rowid = ".$obj->fk_product;
		  $db2 = $this->db->dbclone();
		  $result = $db2->query($sql);

		  $i++;
		}
	    }
      
	  return $result;
	}
    }

  /**
   * \brief     Ajoute un produit dans l'objet facture
   * \param     idproduct
   * \param     qty
   * \param     remise_percent
   * \param     datestart
   * \param     dateend
   */
  function add_product($idproduct, $qty, $remise_percent, $datestart='', $dateend='')
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
   * \brief     Ajoute une ligne de facture (associ� � aucun produit/service pr�d�fini)
   * \param     facid           id de la facture
   * \param     desc            description de la ligne
   * \param     pu              prix unitaire
   * \param     qty             quantit�
   * \param     txtva           taux de tva
   * \param     fk_product      id du produit/service pred�fini
   * \param     remise_percent  pourcentage de remise de la ligne
   * \param     datestart       date de debut de validit� du service
   * \param     dateend         date de fin de validit� du service
   */
  function addline($facid, $desc, $pu, $qty, $txtva, $fk_product=0, $remise_percent=0, $datestart='', $dateend='')
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
	  
	  $remise_percent = trim($remise_percent);

	  if ($this->socidp)
	    {
	      $soc = new Societe($this->db);
	      $soc->fetch($this->socidp);
	      $remise_client = $soc->remise_client;
	      if ($remise_client > $remise_percent)
		{
		  $remise_percent = $remise_client ;
		}
	    }


	  if ($remise_percent > 0)
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
    	  dolibarr_print_error($this->db);
	    }
	}
    }

  /**
   * \brief     Mets � jour une ligne de facture
   * \param     rowid           id de la ligne de facture
   * \param     desc            description de la ligne
   * \param     pu              prix unitaire
   * \param     qty             quantit�
   * \param     remise_percent  pourcentage de remise de la ligne
   * \param     datestart       date de debut de validit� du service
   * \param     dateend         date de fin de validit� du service
   * \return    int     0 si erreur
   */
  function updateline($rowid, $desc, $pu, $qty, $remise_percent=0, $datestart='', $dateend='')
  {
    //dolibarr_syslog("Facture::UpdateLine");
    
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
	if ($result)
	  {
	    $this->updateprice($this->id);
	  }
	else
	  {
	    dolibarr_print_error($this->db);
	  }
	return $result;
	
      }
  }

  /**
   * \brief     Supprime une ligne facture de la base
   * \param     rowid      id de la ligne de facture a supprimer
   */
  function deleteline($rowid)
    {
      if ($this->brouillon)
	{
	  $sql = "DELETE FROM ".MAIN_DB_PREFIX."facturedet WHERE rowid = $rowid;";
	  $result = $this->db->query( $sql);

	  $this->updateprice($this->id);
	}
    }

  /**
   * \brief     Mise � jour des sommes de la facture
   * \param     facid      id de la facture a modifier
   */
  function updateprice($facid)
    {
      include_once DOL_DOCUMENT_ROOT . "/lib/price.lib.php";
      $err=0;
      $sql = "SELECT price, qty, tva_taux FROM ".MAIN_DB_PREFIX."facturedet WHERE fk_facture = $facid;";
  
      $result = $this->db->query($sql);

      if ($result)
	{
	  $num = $this->db->num_rows($result);
	  $i = 0;
	  while ($i < $num)	  
	    {
	      $obj = $this->db->fetch_object($result);

	      $products[$i][0] = $obj->price;
	      $products[$i][1] = $obj->qty;
	      $products[$i][2] = $obj->tva_taux;

	      $i++;
	    }

	  $this->db->free($result);
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
				
		      $sql_del = "DELETE FROM ".MAIN_DB_PREFIX."facture_tva_sum where fk_facture =$this->id;";
		      $this->db->query($sql_del);
		
		      $sql = "INSERT INTO ".MAIN_DB_PREFIX."facture_tva_sum (fk_facture,amount,tva_tx) values ($this->id,'".ereg_replace(",",".",$tvas[$key])."','".ereg_replace(",",".",$key)."');";
		
		    //  $sql = "REPLACE INTO ".MAIN_DB_PREFIX."facture_tva_sum SET fk_facture=".$this->id;
//		      $sql .= ", amount = '".$tvas[$key]."'";
	//	      $sql .= ", tva_tx='".$key."'";
		      
		      if (! $this->db->query($sql) )
			{
			  dolibarr_print_error($this->db);
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
    	  dolibarr_print_error($this->db);
	    }
	}
      else
	{
    	  dolibarr_print_error($this->db);
	}
    }

  /**
   * \brief     Applique une remise
   * \param     user
   * \param     remise
   */
  function set_remise($user, $remise)
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
    	  dolibarr_print_error($this->db);
	    }
	}
  }

  /**
   * \brief     Envoie une relance
   */
  function send_relance($destinataire, $replytoname, $replytomail, $user)
    {
      $soc = new Societe($this->db, $this->socidp);

			$forbidden_chars=array("/","\\",":","*","?","\"","<",">","|","[","]",",",";","=");
			$facref = str_replace($forbidden_chars,"_",$this->ref);
			$file = FAC_OUTPUTDIR . "/" . $facref . "/" . $facref . ".pdf";

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
        	  dolibarr_print_error($this->db);
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
   * \brief     Renvoie la liste des sommes de tva
   */
  function getSumTva()
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
   * \brief     Renvoie la sommes des paiements deja effectu�s
   * \remarks   Utilis� entre autre par certains mod�les de factures
   */
  function getSommePaiement()
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
   *    \brief      Retourne le libell� du statut d'une facture (brouillon, valid�e, abandonn�e, pay�e)
   *    \return     string      Libell�
   */
    function getLibStatut()
    {
		return $this->LibStatut($this->paye,$this->statut);
    }

  /**
   *    \brief      Renvoi le libell� long d'un statut donn�
   *    \param      paye        etat paye
   *    \param      statut      id statut
   *    \return     string      Libell� long du statut
   */
    function LibStatut($paye,$statut)
    {
        global $langs;
        $langs->load("bills");
        if (! $paye)
        {
            if ($statut == 0) return $langs->trans("BillStatusDraft");
            if ($statut == 3) return $langs->trans("BillStatusCanceled");
            return $langs->trans("BillStatusValidated");
        }
        else
        {
            return $langs->trans("BillStatusPayed");
        }
    }

  /**
   *    \brief      Renvoi le libell� court d'un statut donn�
   *    \param      paye        etat paye
   *    \param      statut      id statut
   *    \param      amount      amount already payed
   *    \return     string      Libell� court du statut
   */
    function PayedLibStatut($paye,$statut,$amount=0)
    {
        global $langs;
        $langs->load("bills");
        if (! $paye)
        {
            if ($statut == 0) return $langs->trans("BillShortStatusDraft");
            if ($statut == 3) return $langs->trans("BillStatusCanceled");
            if ($amount) return $langs->trans("BillStatusStarted");
            return $langs->trans("BillStatusNotPayed");
        }
        else
        {
            return $langs->trans("BillStatusPayed");
        }
    }
    

  /**
   *    \brief      Mets � jour les commentaires
   *    \param      note        note
   *    \return     int         <0 si erreur, >0 si ok
   */
  function update_note($note)
    {
        $sql = "UPDATE ".MAIN_DB_PREFIX."facture SET note = '".addslashes($note)."'";
        $sql .= " WHERE rowid =". $this->id;
        
        if ($this->db->query($sql) )
        {
            $this->note = "$note";
            return 1;
        }
        else
        {
            return -1;
        }
    }

  /*
   * \brief     Charge les informations d'ordre info dans l'objet facture
   * \param     id  id de la facture a charger
   */
  function info($id) 
    {
      $sql = "SELECT c.rowid, ".$this->db->pdate("datec")." as datec";
      $sql .= ", fk_user_author, fk_user_valid";
      $sql .= " FROM ".MAIN_DB_PREFIX."facture as c";
      $sql .= " WHERE c.rowid = ".$id;
      
      $result=$this->db->query($sql);
      if ($result) 
	{
	  if ($this->db->num_rows($result)) 
	    {
	      $obj = $this->db->fetch_object($result);

	      $this->id = $obj->rowid;

          if ($obj->fk_user_author) {
    	      $cuser = new User($this->db, $obj->fk_user_author);
    	      $cuser->fetch();
    	      $this->user_creation     = $cuser;
          }
          
          if ($obj->fk_user_valid) {
    	      $vuser = new User($this->db, $obj->fk_user_valid);
    	      $vuser->fetch();
    	      $this->user_validation = $vuser;
          }
          
	      $this->date_creation     = $obj->datec;
	      //$this->date_validation   = $obj->datev; \todo La date de validation n'est pas encore g�r�e

	    }
	    
	  $this->db->free($result);

	}
      else
	{
	  dolibarr_print_error($this->db);
	}
    }

  /**
   * \brief     Change le mode de r�glement
   * \param     mode   nouveau mode
   */
  function mode_reglement($mode)
  {
    //dolibarr_syslog("Facture::ModeReglement");
    if ($this->statut > 0 && $this->paye == 0)
      {	  
	$sql = "UPDATE ".MAIN_DB_PREFIX."facture";
	$sql .= " SET fk_mode_reglement = ".$mode;
	$sql .= " WHERE rowid=".$this->id;
	
	if ( $this->db->query( $sql) )
	  {
	    $this->mode_reglement = $mode;
	    return 0;
	  }
	else
	  {
	    dolibarr_syslog("Facture::mode_reglement Erreur -2");
	    return -2;
	  }
      }
    else
      {
	dolibarr_syslog("Facture::mode_reglement, etat facture incompatible");
	return -3;
      }
  }


  /**
   * \brief     Cr�� une demande de pr�l�vement
   * \param     user         utilisateur cr�ant la demande
   */
  function demande_prelevement($user)
  {
    //dolibarr_syslog("Facture::DemandePrelevement");

    $soc = new Societe($this->db);
    $soc->id = $this->socidp;
    $soc->rib();


    if ($this->statut > 0 && $this->paye == 0 &&  $this->mode_reglement == 3)
      {	  
	$sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."prelevement_facture_demande";
	$sql .= " WHERE fk_facture=".$this->id;
	$sql .= " AND traite = 0";
	
	if ( $this->db->query( $sql) )
	  {
	    $row = $this->db->fetch_row();
	    
	    if ($row[0] == 0)
	      {		
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."prelevement_facture_demande";
		$sql .= " (fk_facture, amount, date_demande, fk_user_demande, code_banque, code_guichet, number, cle_rib)";
		$sql .= " VALUES (".$this->id;
		$sql .= ",'".ereg_replace(",",".",$this->total_ttc)."'";
		$sql .= ",now(),".$user->id."";
		$sql .= ",'".$soc->bank_account->code_banque."'";
		$sql .= ",'".$soc->bank_account->code_guichet."'";
		$sql .= ",'".$soc->bank_account->number."'";
		$sql .= ",'".$soc->bank_account->cle_rib."')";
		
		if ( $this->db->query( $sql) )
		  {
		    return 0;
		  }
		else
		  {
		    dolibarr_syslog("Facture::DemandePrelevement Erreur");
		    return -1;
		  }
	      }
	    else
	      {
		dolibarr_syslog("Facture::DemandePrelevement Impossible de cr�er une demande, demande d�ja en cours");
	      }
	  }
	else
	  {
	    dolibarr_syslog("Facture::DemandePrelevement Erreur -2");
	    return -2;
	  }
      }
    else
      {
	dolibarr_syslog("Facture::DemandePrelevement Impossible de cr�er une demande, etat facture incompatible");
	return -3;
      }
  }
  /**
   * \brief     Supprime une demande de pr�l�vement
   * \param     user         utilisateur cr�ant la demande
   * \param     did          id de la demande a supprimer
   */
  function demande_prelevement_delete($user, $did)
  {
    $sql = "DELETE FROM ".MAIN_DB_PREFIX."prelevement_facture_demande";
    $sql .= " WHERE rowid = ".$did;
    $sql .= " AND traite = 0";
    
    if ( $this->db->query( $sql) )
      {
	return 0;
      }
    else
      {
	dolibarr_syslog("Facture::DemandePrelevement Erreur");
	return -1;
      }
  }
  /*
   *
   */
}



/**
  \class FactureLigne
  \brief Classe permettant la gestion des lignes de factures
*/

class FactureLigne
{

  /**
   * \brief     Constructeur d'objets ligne de facture
   * \param     DB      handler d'acc�s base de donn�e
   */
  function FactureLigne($DB)
    {
        $this->db= $DB ;
    }

  /**
   * \brief     Recup�re l'objet ligne de facture
   * \param     rowid           id de la ligne de facture
   * \param     societe_id      id de la societe
   */
  function fetch($rowid, $societe_id=0)
    {
      $sql = "SELECT fk_product, description, price, qty, rowid, tva_taux, remise, remise_percent, subprice, ".$this->db->pdate("date_start")." as date_start,".$this->db->pdate("date_end")." as date_end";
      $sql .= " FROM ".MAIN_DB_PREFIX."facturedet WHERE rowid = ".$rowid;

      $result = $this->db->query($sql);
      if ($result)
	  {
	      $objp = $this->db->fetch_object($result);
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

    	  $this->db->free($result);
	  }
	  else {
    	  dolibarr_print_error($this->db);
	  }

    }

}

?>
