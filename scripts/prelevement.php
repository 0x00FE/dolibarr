<?PHP
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
 *
 * $Id$
 * $Source$
 *
 *
 * Script de prelevement
 *
 */

require ("../htdocs/master.inc.php");

require_once (DOL_DOCUMENT_ROOT."/bon-prelevement.class.php");
require_once (DOL_DOCUMENT_ROOT."/facture.class.php");
require_once (DOL_DOCUMENT_ROOT."/societe.class.php");
require_once (DOL_DOCUMENT_ROOT."/paiement.class.php");
require_once (DOL_DOCUMENT_ROOT."/lib/dolibarrmail.class.php");

$error = 0;

$datetimeprev = time();

$month = strftime("%m", $datetimeprev);
$year = strftime("%Y", $datetimeprev);

$user = new user($db, PRELEVEMENT_USER);

/*
 *
 * Lectures des factures
 *
 */

$factures = array();
$factures_prev = array();

if (!$error)
{
  
  $sql = "SELECT f.rowid, pfd.rowid as pfdrowid, f.fk_soc";
  $sql .= ", pfd.code_banque, pfd.code_guichet, pfd.number, pfd.cle_rib";
  $sql .= ", pfd.amount";
  $sql .= ", s.nom";
  $sql .= " FROM ".MAIN_DB_PREFIX."facture as f";
  $sql .= " , ".MAIN_DB_PREFIX."societe as s";
  $sql .= " , ".MAIN_DB_PREFIX."prelevement_facture_demande as pfd";

  $sql .= " WHERE f.rowid = pfd.fk_facture";
  $sql .= " AND s.idp = f.fk_soc";
  $sql .= " AND f.fk_statut = 1";
  $sql .= " AND f.paye = 0";
  $sql .= " AND pfd.traite = 0";
  $sql .= " AND f.total_ttc > 0";
  $sql .= " AND f.fk_mode_reglement = 3";
  
  if ( $db->query($sql) )
    {
      $num = $db->num_rows();
      $i = 0;
      
      while ($i < $num)
	{
	  $row = $db->fetch_row();
	  
	  $factures[$i] = $row;
	  
	  $i++;
	}            
      $db->free();
      dolibarr_syslog("$i factures � pr�lever");
    }
  else
    {
      $error = 1;
      dolibarr_syslog("Erreur -1");
      dolibarr_syslog($db->error());
    }
}

/*
 *
 * Verif des clients
 *
 */

if (!$error)
{
  /*
   * V�rification des RIB
   *
   */
  $i = 0;
  dolibarr_syslog("D�but v�rification des RIB");

  if (sizeof($factures) > 0)
    {      
      foreach ($factures as $fac)
	{
	  $fact = new Facture($db);
	  
	  if ($fact->fetch($fac[0]) == 1)
	    {
	      $soc = new Societe($db);
	      if ($soc->fetch($fact->socidp) == 1)
		{
		  if ($soc->verif_rib() == 1)
		    {
		      $factures_prev[$i] = $fac;
		      /* second tableau necessaire pour bon-prelevement */
		      $factures_prev_id[$i] = $fac[0];
		      $i++;
		    }
		  else
		    {
		      dolibarr_syslog("Erreur de RIB societe $fact->socidp $soc->nom");
		    }
		}
	      else
		{
		  dolibarr_syslog("Impossible de lire la soci�t�");
		}
	    }
	  else
	    {
	      dolibarr_syslog("Impossible de lire la facture");
	    }
	}
    }
  else
    {
      dolibarr_syslog("Aucune factures a traiter");
    }
}

/*
 *
 *
 *
 */

dolibarr_syslog(sizeof($factures_prev)." factures seront pr�lev�es");

if (sizeof($factures_prev) > 0)
{
  /*
   * Ouverture de la transaction
   *
   */

  if (!$db->query("BEGIN"))
    {
      $error++;
    } 

  /*
   * Traitements
   *
   */
  
  if (!$error)
    {
      $ref = "T".substr($year,-2).$month;
      
      /*
       *
       *
       */
      $sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."prelevement_bons";
      $sql .= " WHERE ref LIKE '$ref%'";
      
      if ($db->query($sql))
	{      
	  $row = $db->fetch_row();
	}
      else
	{
	  $error++;
	  dolibarr_syslog("Erreur recherche reference");
	}

      $ref = $ref . substr("00".($row[0]+1), -2);

      $filebonprev = $ref;
   
      /*
       * Creation du prelevement
       *
       */
      
      $sql = "INSERT INTO ".MAIN_DB_PREFIX."prelevement_bons (ref,datec)";
      $sql .= " VALUES ('".$ref."',now())";
      
      if ($db->query($sql))
	{      
	  $prev_id = $db->last_insert_id();

	  $bonprev = new BonPrelevement($db, DOL_DATA_ROOT."/prelevement/bon/".$filebonprev);
	  $bonprev->id = $prev_id;
	}
      else
	{
	  $error++;
	  dolibarr_syslog("Erreur cr�ation du bon de prelevement");
	}
      
    }
  
  /*
   *
   *
   *
   */
  if (!$error)
    {      
      dolibarr_syslog("D�but g�n�ration des paiements");
      dolibarr_syslog("Nombre de factures ".sizeof($factures_prev));
      
      if (sizeof($factures_prev) > 0)
	{
	  foreach ($factures_prev as $fac)
	    {
	      $fact = new Facture($db);
	      $fact->fetch($fac[0]);

	      $pai = new Paiement($db);

	      $pai->amounts = array();
	      $pai->amounts[$fac[0]] = $fact->total_ttc;
	      $pai->datepaye = $db->idate($datetimeprev);
	      $pai->paiementid = 3; // pr�l�vement
	      $pai->num_paiement = $ref;

	      if ($pai->create($user, 1) == -1)  // on appelle en no_commit
		{
		  $error++;
		  dolibarr_syslog("Erreur creation paiement facture ".$fac[0]);
		}
	      else
		{
		  /* 
		   * Validation du paiement 
		   */
		  $pai->valide();

		  /*
		   * Ajout d'une ligne de pr�l�vement
		   *
		   *
		   * $fac[3] : banque 
		   * $fac[4] : guichet
		   * $fac[5] : number
		   * $fac[6] : cle rib
		   * $fac[7] : amount
		   * $fac[8] : client nom
		   * $fac[2] : client id
		   */

		  $ri = $bonprev->AddFacture($fac[0], $fac[2], $fac[8], $fac[7], 
					     $fac[3], $fac[4], $fac[5], $fac[6]); 
		  if ($ri <> 0)
		    {
		      $error++;
		    }

		  /*
		   *
		   *

		  $sql = "INSERT INTO ".MAIN_DB_PREFIX."prelevement_facture (fk_facture,fk_prelevement)";
		  $sql .= " VALUES (".$fac[0].",".$prev_id.")";
	      
		  if ($db->query($sql))
		    {      

		    }
		  else
		    {
		      $error++;
		      dolibarr_syslog("Erreur de liens paiement facture");
		    }

		  */

		  /*
		   * Mise � jour des demandes
		   *
		   */
		  $sql = "UPDATE ".MAIN_DB_PREFIX."prelevement_facture_demande";
		  $sql .= " SET traite = 1";
		  $sql .= ", date_traite=now()";
		  $sql .= ", fk_prelevement = ".$prev_id;
		  $sql .= " WHERE rowid=".$fac[1];
	      
		  if ($db->query($sql))
		    {      

		    }
		  else
		    {
		      $error++;
		      dolibarr_syslog("Erreur mise a jour des demandes");
		      dolibarr_syslog($db->error());
		    }

		}
	    }
	}
  
      dolibarr_syslog("Fin des paiements");
    }

  if (!$error)
    {
      /*
       * Bon de Prelevement
       *
       *
       */

      dolibarr_syslog("Debut prelevement");
      dolibarr_syslog("Nombre de factures ".sizeof($factures_prev));

      if (sizeof($factures_prev) > 0)
	{
	  $bonprev->date_echeance = $datetimeprev;      
	  $bonprev->reference_remise = $ref;


	  $bonprev->numero_national_emetteur = PRELEVEMENT_NUMERO_NATIONAL_EMETTEUR;
	  $bonprev->raison_sociale = PRELEVEMENT_RAISON_SOCIALE; 

	  $bonprev->emetteur_code_etablissement = PRELEVEMENT_CODE_BANQUE;
	  $bonprev->emetteur_code_guichet       = PRELEVEMENT_CODE_GUICHET;
	  $bonprev->emetteur_numero_compte      = PRELEVEMENT_NUMERO_COMPTE;

      
	  $bonprev->factures = $factures_prev_id;
      
	  $bonprev->generate();  
	}
      dolibarr_syslog( $filebonprev ) ;
      dolibarr_syslog("Fin prelevement");
    }

  /*
   * Mise � jour du total
   *
   */

  $sql = "UPDATE ".MAIN_DB_PREFIX."prelevement_bons";
  $sql .= " SET amount = ".ereg_replace(",",".",$bonprev->total);
  $sql .= " WHERE rowid = ".$prev_id;

  if (!$db->query($sql))
    {
      $error++;
      dolibarr_syslog("Erreur mise � jour du total");
      dolibarr_syslog($sql);
    }

  /*
   * Rollback ou Commit
   *
   */
  if (!$error)
    {
      $db->query("COMMIT");
      dolibarr_syslog("COMMIT");
    }
  else
    {
      $db->query("ROLLBAK");
      dolibarr_syslog("ROLLBACK");
    }
}

$db->close();

// FIN
?>
