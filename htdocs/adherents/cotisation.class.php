<?PHP
/* Copyright (C) 2002-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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

/*!	\file cotisation.class.php
		\brief Classe permettant de g�rer les cotisations
		\author Rodolphe Quiedeville
		\version $Revision$
*/

/*! \class Cotisation
		\brief Classe permettant de g�rer les cotisations
*/

class Cotisation
{
  var $id;
  var $db;
  var $date;
  var $amount;
  var $prenom;
  var $nom;
  var $societe;
  var $adresse;
  var $cp;
  var $ville;
  var $pays;
  var $email;
  var $public;
  var $projetid;
  var $modepaiement;
  var $modepaiementid;
  var $commentaire;
  var $statut;

  var $projet;
  var $errorstr;

/*!
		\brief Cotisation
		\param DB				base de donn�es
		\param soc_idp
*/

	Function Cotisation($DB, $soc_idp="")
    {
      $this->db = $DB ;
      $this->modepaiementid = 0;
    }

  /*
   *
   *
   *
   */

  Function print_error_list()
  {
    $num = sizeof($this->errorstr);
    for ($i = 0 ; $i < $num ; $i++)
      {
	print "<li>" . $this->errorstr[$i];
      }
  }

	/*
   *
   *
   */

	Function check($minimum=0)
    {
      $err = 0;

      if (strlen(trim($this->societe)) == 0)
	{
	  if ((strlen(trim($this->nom)) + strlen(trim($this->prenom))) == 0)
	    {
	      $error_string[$err] = "Vous devez saisir vos nom et pr�nom ou le nom de votre soci�t�.";
	      $err++;
	    }
	}

      if (strlen(trim($this->adresse)) == 0)
	{
	  $error_string[$err] = "L'adresse saisie est invalide";
	  $err++;
	}

      if (strlen(trim($this->cp)) == 0)
	{
	  $error_string[$err] = "Le code postal saisi est invalide";
	  $err++;
	}

      if (strlen(trim($this->ville)) == 0)
	{
	  $error_string[$err] = "La ville saisie est invalide";
	  $err++;
	}

      if (strlen(trim($this->email)) == 0)
	{
	  $error_string[$err] = "L'email saisi est invalide";
	  $err++;
	}

      $this->amount = trim($this->amount);

      $map = range(0,9);
      for ($i = 0; $i < strlen($this->amount) ; $i++)
	{
	  if (!isset($map[substr($this->amount, $i, 1)] ))
	    {
	      $error_string[$err] = "Le montant du don contient un/des caract�re(s) invalide(s)";
	      $err++;
	      $amount_invalid = 1;
	      break;
	    } 	      
	}

      if (! $amount_invalid)
	{
	  if ($this->amount == 0)
	    {
	      $error_string[$err] = "Le montant du don est null";
	      $err++;
	    }
	  else
	    {
	      if ($this->amount < $minimum && $minimum > 0)
		{
		  $error_string[$err] = "Le montant minimum du don est de $minimum";
		  $err++;
		}
	    }
	}
      
      /*
       * Return errors
       *
       */

      if ($err)
	{
	  $this->errorstr = $error_string;
	  return 0;
	}
      else
	{
	  return 1;
	}

    }

/*!
		\brief fonction qui permet de cr�er le don
		\param userid			userid de l'adh�rent
*/

	Function create($userid)
    {
      /*
       *  Insertion dans la base
       */

      $this->date = $this->db->idate($this->date);

      $sql = "INSERT INTO ".MAIN_DB_PREFIX."don (datec, amount, fk_paiement,prenom, nom, societe,adresse, cp, ville, pays, public, fk_don_projet, note, fk_user_author, datedon, email)";
      $sql .= " VALUES (now(), $this->amount, $this->modepaiementid,'$this->prenom','$this->nom','$this->societe','$this->adresse', '$this->cp','$this->ville','$this->pays',$this->public, $this->projetid, '$this->commentaire', $userid, '$this->date','$this->email')";
      
      $result = $this->db->query($sql);
      
      if ($result) 
	{
	  return $this->db->last_insert_id();
	}
      else
	{
	  print $this->db->error();
	  print "<h2><br>$sql<br></h2>";
	  return 0;
	}  
    }

/*!
		\brief fonction qui permet de mettre � jour le don
		\param userid			userid de l'adh�rent
*/

  Function update($userid)
    {
      
      $this->date = $this->db->idate($this->date);

      $sql = "UPDATE ".MAIN_DB_PREFIX."don SET ";
      $sql .= "amount = " . $this->amount;
      $sql .= ",fk_paiement = ".$this->modepaiementid;
      $sql .= ",prenom = '".$this->prenom ."'";
      $sql .= ",nom='".$this->nom."'";
      $sql .= ",societe='".$this->societe."'";
      $sql .= ",adresse='".$this->adresse."'";
      $sql .= ",cp='".$this->cp."'";
      $sql .= ",ville='".$this->ville."'";
      $sql .= ",pays='".$this->pays."'";
      $sql .= ",public=".$this->public;
      $sql .= ",fk_don_projet=".$this->projetid;
      $sql .= ",note='".$this->commentaire."'";
      $sql .= ",datedon='".$this->date."'";
      $sql .= ",email='".$this->email."'";
      $sql .= ",fk_statut=".$this->statut;

      $sql .= " WHERE rowid = $this->id";
      
      $result = $this->db->query($sql);
      
      if ($result) 
	{
	  return 1;
	}
      else
	{
	  print $this->db->error();
	  print "<h2><br>$sql<br></h2>";
	  return 0;
	}  
    }

/*!
		\brief fonction qui permet de supprimer le don
		\param rowid
*/

  Function delete($rowid)

  {

    $sql = "DELETE FROM ".MAIN_DB_PREFIX."don WHERE rowid = $rowid AND fk_statut = 0;";

    if ( $this->db->query( $sql) )
      {
	if ( $this->db->affected_rows() )
	  {
	    return 1;
	  }
	else
	  {
	    return 0;
	  }
      }
    else
      {
	print "Err : ".$this->db->error();
	return 0;
      }
  }

/*!
		\brief fonction qui permet de r�cup�rer le don
		\param rowid
*/

  Function fetch($rowid)
  {
    $sql = "SELECT d.rowid, ".$this->db->pdate("d.datedon")." as datedon, d.prenom, d.nom, d.societe, d.amount, p.libelle as projet, d.fk_statut, d.adresse, d.cp, d.ville, d.pays, d.public, d.amount, d.fk_paiement, d.note, cp.libelle, d.email, d.fk_don_projet";
    $sql .= " FROM ".MAIN_DB_PREFIX."don as d, ".MAIN_DB_PREFIX."don_projet as p, ".MAIN_DB_PREFIX."c_paiement as cp";

    $sql .= " WHERE p.rowid = d.fk_don_projet AND cp.id = d.fk_paiement AND d.rowid = $rowid";

    if ( $this->db->query( $sql) )
      {
	if ($this->db->num_rows())
	  {

	    $obj = $this->db->fetch_object(0);

	    $this->id             = $obj->rowid;
	    $this->date           = $obj->datedon;
	    $this->prenom         = stripslashes($obj->prenom);
	    $this->nom            = stripslashes($obj->nom);
	    $this->societe        = stripslashes($obj->societe);
	    $this->statut         = $obj->fk_statut;
	    $this->adresse        = stripslashes($obj->adresse);
	    $this->cp             = stripslashes($obj->cp);
	    $this->ville          = stripslashes($obj->ville);
	    $this->email          = stripslashes($obj->email);
	    $this->pays           = stripslashes($obj->pays);
	    $this->projet         = $obj->projet;
	    $this->projetid       = $obj->fk_don_projet;
	    $this->public         = $obj->public;
	    $this->modepaiementid = $obj->fk_paiement;
	    $this->modepaiement   = $obj->libelle;
	    $this->amount         = $obj->amount;
	    $this->commentaire    = stripslashes($obj->note);
	  }
      }
    else
      {
	print $this->db->error();
      }

  }

/*!
		\brief fonction qui permet de valider la promesse de don
		\param	rowid
		\param 	userid			userid de l'adh�rent
*/

  Function valid_promesse($rowid, $userid)
  {

    $sql = "UPDATE ".MAIN_DB_PREFIX."don SET fk_statut = 1, fk_user_valid = $userid WHERE rowid = $rowid AND fk_statut = 0;";

    if ( $this->db->query( $sql) )
      {
	if ( $this->db->affected_rows() )
	  {
	    return 1;
	  }
	else
	  {
	    return 0;
	  }
      }
    else
      {
	print "Err : ".$this->db->error();
	return 0;
      }
  }

/*!
		\brief fonction qui permet de mettre le don comme pay�
		\param	rowid
		\param	modedepaiement
*/

	Function set_paye($rowid, $modepaiement='')
  {
    $sql = "UPDATE ".MAIN_DB_PREFIX."don SET fk_statut = 2";

    if ($modepaiement)
      {
	$sql .= ", fk_paiement=$modepaiement";
      }
    $sql .=  " WHERE rowid = $rowid AND fk_statut = 1;";

    if ( $this->db->query( $sql) )
      {
	if ( $this->db->affected_rows() )
	  {
	    return 1;
	  }
	else
	  {
	    return 0;
	  }
      }
    else
      {
	print "Err : ".$this->db->error();
	return 0;
      }
  }

/*!
		\brief fonction qui permet de mettre un commentaire sur le don
		\param	rowid
		\param	commentaire
*/

  Function set_commentaire($rowid, $commentaire='')
  {
    $sql = "UPDATE ".MAIN_DB_PREFIX."don SET note = '$commentaire'";

    $sql .=  " WHERE rowid = $rowid ;";

    if ( $this->db->query( $sql) )
      {
	if ( $this->db->affected_rows() )
	  {
	    return 1;
	  }
	else
	  {
	    return 0;
	  }
      }
    else
      {
	print "Err : ".$this->db->error();
	return 0;
      }
  }

/*!
		\brief fonction qui permet de mettre le don comme encaiss�
		\param	rowid
*/

	Function set_encaisse($rowid)
  {

    $sql = "UPDATE ".MAIN_DB_PREFIX."don SET fk_statut = 3 WHERE rowid = $rowid AND fk_statut = 2;";

    if ( $this->db->query( $sql) )
      {
	if ( $this->db->affected_rows() )
	  {
	    return 1;
	  }
	else
	  {
	    return 0;
	  }
      }
    else
      {
	print "Err : ".$this->db->error();
	return 0;
      }
  }

  /*
   * Somme des dons encaiss�s
   */

  Function sum_actual()
  {
    $sql = "SELECT sum(amount)";
    $sql .= " FROM ".MAIN_DB_PREFIX."don";
    $sql .= " WHERE fk_statut = 3";

    if ( $this->db->query( $sql) )
      {
	$row = $this->db->fetch_row(0);

	return $row[0];

      }
  }
  /* Paiement recu en attente d'encaissement
   * 
   *
   */
  Function sum_pending()
  {
    $sql = "SELECT sum(amount)";
    $sql .= " FROM ".MAIN_DB_PREFIX."don";
    $sql .= " WHERE fk_statut = 2";

    if ( $this->db->query( $sql) )
      {
	$row = $this->db->fetch_row(0);

	return $row[0];

      }
  }

  /*
   * Somme des promesses de dons valid�es
   *
   */
	 
  Function sum_intent()
  {
    $sql = "SELECT sum(amount)";
    $sql .= " FROM ".MAIN_DB_PREFIX."don";
    $sql .= " WHERE fk_statut = 1";

    if ( $this->db->query( $sql) )
      {
	$row = $this->db->fetch_row(0);

	return $row[0];

      }
  }
}
?>
