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
 */

class TelephonieContrat {
  var $db;
  var $id;
  var $ligne;

  function TelephonieContrat($DB, $id=0)
  {
    global $config;

    $this->db = $DB;
    $this->error_message = '';
    $this->statuts[-1] = "En attente";
    $this->statuts[1] = "A commander";
    $this->statuts[2] = "Command�e chez le fournisseur";
    $this->statuts[3] = "Activ�e";
    $this->statuts[4] = "A r�silier";
    $this->statuts[5] = "R�siliation demand�e";
    $this->statuts[6] = "R�sili�e";
    $this->statuts[7] = "Rejet�e";

    return 1;
  }
  /*
   * Creation du contrat
   * Le commercial qui fait le suivi est par defaut le commercial qui a signe
   */
  function create($user, $isfacturable='oui', $mode_paiement='pre')
  {
    $sql = "INSERT INTO ".MAIN_DB_PREFIX."telephonie_contrat";
    $sql .= " (ref, fk_soc, fk_client_comm, fk_soc_facture, note";
    $sql .= " , fk_commercial_sign, fk_commercial_suiv, fk_user_creat, date_creat)";

    $sql .= " VALUES ('PROV".time()."'";

    $sql .= ", $this->client,$this->client_comm,$this->client_facture,'$this->note'";
    $sql .= ",$this->commercial_sign, $this->commercial_sign, $user->id, now())";
    
    if ( $this->db->query($sql) )
      {
	$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."telephonie_contrat");

	$sql = "UPDATE ".MAIN_DB_PREFIX."telephonie_contrat";
	$sql .= " SET ref='".substr("00000000".$this->id,-8)."'";
	$sql .= " , isfacturable = '".$isfacturable."'";
	$sql .= " , mode_paiement = '".$mode_paiement."'";
	$sql .= " WHERE rowid=".$this->id;
	$this->db->query($sql);

	/*
	 * On applique la grille de tarif du distributeur
	 *
	 */
	$grille_tarif = 0;

	$sql = "SELECT d.grille_tarif ";
	$sql .= " FROM ".MAIN_DB_PREFIX."telephonie_distributeur as d";
	$sql .= " , ".MAIN_DB_PREFIX."telephonie_distributeur_commerciaux as dc";
	$sql .= " WHERE dc.fk_distributeur = d.rowid";
	$sql .= " AND dc.fk_user = ".$this->commercial_sign;
		
	$resql = $this->db->query($sql);
	
	if ($resql)
	  {
	    if ($this->db->num_rows($resql))
	      {
		$row = $this->db->fetch_row($resql);
		
		$grille_tarif = $row[0];
	      }
	    $this->db->free($resql);
	  }


	if ($grille_tarif > 0)
	  {
	    $sql = "UPDATE ".MAIN_DB_PREFIX."telephonie_contrat";
	    $sql .= " SET grille_tarif =".$grille_tarif;
	    $sql .= " WHERE rowid=".$this->id;
	    $this->db->query($sql);	    
	  }

	return 0;
      }   
    else
      {
	$this->error_message = "Echec de la cr�ation du contrat";
	dolibarr_syslog("TelephonieContrat::Create Error -1");
	dolibarr_syslog($this->db->error());
	return -1;
      }
  }
  /*
   *
   *
   */
  function update($user)
  {
    $error = 0 ;

    if (!$this->db->begin())
      {
	$error++;
	dolibarr_syslog("TelephonieContrat::Update Error -1");
      }

    if (!$error)
      {

	$sql = "UPDATE ".MAIN_DB_PREFIX."telephonie_contrat";
	$sql .= " SET ";
	$sql .= " fk_soc = ".$this->client ;
	$sql .= ", fk_soc_facture = ".$this->client_facture;
	$sql .= ", fk_commercial_suiv = ".$this->commercial_suiv_id;
	$sql .= ", mode_paiement = '".$this->mode_paiement."'";
	$sql .= ", note =  '$this->note'";
	
	$sql .= " WHERE rowid = ".$this->id;
	
	if (! $this->db->query($sql) )
	  {
	    $error++;
	    dolibarr_syslog("TelephonieContrat::Update Error -2");
	  }
      }

    if (!$error)
      {
	$sql = "UPDATE ".MAIN_DB_PREFIX."telephonie_societe_ligne";
	$sql .= " SET ";
	$sql .= " fk_soc = ".$this->client ;
	$sql .= ", fk_soc_facture = ".$this->client_facture;
	$sql .= ", fk_commercial_suiv = ".$this->commercial_suiv_id;
	$sql .= ", mode_paiement = '".$this->mode_paiement."'";
	$sql .= " WHERE fk_contrat = ".$this->id;
	
	
	if (! $this->db->query($sql) )
	  {
	    $error++;
	    dolibarr_syslog("TelephonieContrat::Update Error -3");
	  }
      }

    if (!$error)
      {
	$this->db->commit();
	return 0;
      }
    else
      {
	$this->db->rollback();
	return -1;
      }
  }
  /*
   *
   *
   *
   */
  function fetch($id)
    {
      $sql = "SELECT c.rowid, c.ref, c.fk_client_comm, c.fk_soc, c.fk_soc_facture, c.note";
      $sql .= ", c.fk_commercial_sign, c.fk_commercial_suiv";
      $sql .= ", c.isfacturable, c.mode_paiement";
      $sql .= ", c.fk_user_creat, c.date_creat";
      $sql .= ", c.grille_tarif";
      $sql .= " FROM ".MAIN_DB_PREFIX."telephonie_contrat as c";
      $sql .= " WHERE c.rowid = ".$id;

      $resql = $this->db->query($sql);

      if ($resql)
	{
	  if ($this->db->num_rows($resql))
	    {
	      $obj = $this->db->fetch_object($resql);

	      $this->id                 = $obj->rowid;
	      $this->socid              = $obj->fk_soc;
	      $this->ref                = $obj->ref;
	      $this->remise             = $obj->remise;
	      $this->client_comm_id     = $obj->fk_client_comm;
	      $this->client_id          = $obj->fk_soc;
	      $this->client_facture_id  = $obj->fk_soc_facture;

	      $this->commercial_sign_id = $obj->fk_commercial_sign;
	      $this->commercial_suiv_id = $obj->fk_commercial_suiv;

	      $this->statut             = $obj->statut;
	      $this->mode_paiement      = $obj->mode_paiement;
	      $this->code_analytique    = $obj->code_analytique;

	      $this->user_creat         = $obj->fk_user_creat;

	      $this->grille_tarif_id    = $obj->grille_tarif;

	      if ($obj->isfacturable == 'oui')
		{
		  $this->facturable        = 1;
		}
	      else
		{
		  $this->facturable        = 0;
		}

	      $this->ref_url = '<a href="'.DOL_URL_ROOT.'/telephonie/contrat/fiche.php?id='.$this->id.'">'.$this->ref.'</a>';


	      $result = 1;
	    }
	  else
	    {
	      dolibarr_syslog("TelephonieContrat::Fecth Erreur -2");
	      $result = -2;
	    }

	  $this->db->free($resql);
	}
      else
	{
	  /* Erreur select SQL */
	  print $this->db->error();
	  $result = -1;
	  dolibarr_syslog("TelephonieContrat::Fecth Erreur -1");
	}

      $sql = "SELECT libelle";
      $sql .= " FROM ".MAIN_DB_PREFIX."telephonie_tarif_grille";
      $sql .= " WHERE rowid = ".$this->grille_tarif_id;

      $resql = $this->db->query($sql);

      if ($resql)
	{
	  if ($this->db->num_rows($resql))
	    {
	      $obj = $this->db->fetch_object($resql);
	      
	      $this->grille_tarif_nom = $obj->libelle;
	    }
	  $this->db->free($resql);
	}


      return $result;
  }
  /*
   *
   *
   */
  function delete()
  {
    $sql = "DELETE FROM ".MAIN_DB_PREFIX."telephonie_contrat";
    $sql .= " WHERE rowid = ".$this->id;

    $this->db->query($sql);
  }
  /*
   *
   *
   *
   */
  function add_contact_facture($cid)
  {

    $this->del_contact_facture($cid);
        
    $sql = "INSERT INTO ".MAIN_DB_PREFIX."telephonie_contrat_contact_facture";
    $sql .= " (fk_contrat, fk_contact) ";
    $sql .= " VALUES ($this->id, $cid )";
    
    $this->db->query($sql);
  }
  /*
   *
   *
   */
  function del_contact_facture($cid)
  {
        
    $sql = "DELETE FROM ".MAIN_DB_PREFIX."telephonie_contrat_contact_facture";
    $sql .= " WHERE fk_contrat=".$this->id." AND fk_contact=".$cid;
    
    return $this->db->query($sql);   
  }
  /*
   *
   *
   */
  function count_associated_services()
  {
    $num = 0;
    $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."telephonie_contrat_service";
    $sql .= " WHERE fk_contrat=".$this->id;

    if ( $this->db->query( $sql) )
      {
	$num = $this->db->num_rows();
      }

    return $num;
  }
  /*
   *
   *
   */
  function add_service($user, $sid)
  {
    $result = 0;

    $sql = "SELECT montant FROM ".MAIN_DB_PREFIX."telephonie_service";
    $sql .= " WHERE rowid=".$sid;

    $resql = $this->db->query( $sql);

    if ($resql)
      {
	$row = $this->db->fetch_row($resql);
	$montant = $row[0];
      }
    else
      {
	$result = -1;
      }


    if ($result == 0)
      {
	$sql = "INSERT INTO ".MAIN_DB_PREFIX."telephonie_contrat_service";
	$sql .= " (fk_contrat, fk_service, fk_user_creat, date_creat, montant) ";
	$sql .= " VALUES ($this->id, $sid, $user->id, now(),".$montant.")";
	
	$resql = $this->db->query( $sql);
	
	if ($resql)
	  {
	    return 0 ;
	  }
      }
  }
  /*
   *
   *
   */
  function remove_service($user, $sid)
  {
    $sql = "DELETE FROM ".MAIN_DB_PREFIX."telephonie_contrat_service";
    $sql .= " WHERE fk_contrat = ".$this->id;
    $sql .= " AND rowid = ".$sid;
    
    if ($this->db->query($sql) )
      {
	return 0 ;
      }
  }
  /*
   *
   *
   */
  function get_contact_facture()
  {
    $this->contact_facture_id = array();        
    $res   = array();
    $resid = array();

    $sql = "SELECT c.idp, c.name, c.firstname, c.email ";
    $sql .= "FROM ".MAIN_DB_PREFIX."socpeople as c";
    $sql .= ",".MAIN_DB_PREFIX."telephonie_contrat_contact_facture as cf";
    $sql .= " WHERE c.idp = cf.fk_contact ";
    $sql .= " AND cf.fk_contrat = ".$this->id." ORDER BY name ";

    $resql = $this->db->query($sql);

    if ( $resql )
      {
	$num = $this->db->num_rows($resql);
	if ( $num > 0 )
	  {
	    $i = 0;
	    while ($i < $num)
	      {
		$row = $this->db->fetch_row($resql);
		
		array_push($res, $row[1] . " " . $row[2] . " &lt;".$row[3]."&gt;");
		array_push($resid, $row[0]);
		$i++;
	      }
	    
	    $this->db->free($resql);
	  }	
      }
    $this->contact_facture_id = $resid;
    return $res;
  }
}
?>
