<?php
/* Copyright (C) 2002-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2006 Houssin R�gis        <regis.houssin@cap-networks.com>
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
        \file       htdocs/comm/adresse_livraison.class.php
        \ingroup    societe
        \brief      Fichier de la classe des adresses de livraison
        \version    $Revision$
*/


/**
        \class 		Societe
        \brief 		Classe permettant la gestion des adresses de livraison
*/

class Livraison
{
	var $db;
	
	var $id;
	var $label;
	var $socid;
	var $nom;
	var $adresse;
	var $cp;
	var $ville;
	var $departement_id;
	var $pays_id;
	var $pays_code;
	var $note;


  /**
   *    \brief  Constructeur de la classe
   *    \param  DB     handler acc�s base de donn�es
   *    \param  id     id societe (0 par defaut)
   */
  function Livraison($DB, $id=0)
  {
	global $conf;
	
    $this->db = $DB;

    $this->id = $id;

    return 1;
  }

  /**
   *    \brief      Cr�e l'adresse de livraison de la soci�t� en base
   *    \param      user        Objet utilisateur qui demande la cr�ation
   *    \return     int         0 si ok, < 0 si erreur
   */

    function create($socid, $user='')
    {
        global $langs,$conf;

        // Nettoyage param�tres
        $this->nom=trim($this->nom);
        $this->label=trim($this->label);

        dolibarr_syslog("societe.class.php::create delivery adress ".$this->label);

        $this->db->begin();
        
        $result = $this->verify();

        if ($result >= 0)
        {
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."societe_adresse_livraison (label, fk_societe, nom, datec, fk_user_creat) ";
            $sql .= " VALUES ('".addslashes($this->label)."', '".$socid."', '".addslashes($this->nom)."', now(), '".$user->id."')";

            $result=$this->db->query($sql);
            if ($result)
            {
                $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."societe_adresse_livraison");

                $ret = $this->update($this->id, $socid, $user);

                if ($ret >= 0)
                {
                    dolibarr_syslog("Societe::Create delivery adress success id=".$this->id);
                    $this->db->commit();
		            return 0;
                }
                else
                {
                    dolibarr_syslog("Societe::Create delivery adress echec update");
                    $this->db->rollback();
                    return -3;
                }
            }
            else

            {
                if ($this->db->errno() == 'DB_ERROR_RECORD_ALREADY_EXISTS')
                {

                    $this->error=$langs->trans("ErrorCompanyNameAlreadyExists",$this->nom);
                }
                else
                {
                    dolibarr_syslog("Societe::Create echec insert sql=$sql");
                }
                $this->db->rollback();
                return -2;
            }

        }
        else
        {
            $this->db->rollback();
            dolibarr_syslog("Societe::Create echec verify sql=$sql");
            return -1;
        }
    }


/**
   *    \brief      Verification lors de la modification de l'adresse de livraison
   *    \return     0 si ok, < 0 en cas d'erreur
   */
	function verify()
	{
		$this->label=trim($this->label);
		$this->nom=trim($this->nom);
		$result = 0;
		if (!$this->nom || !$this->label)
		{
			$this->error = "Le nom de la soci�t� et le label ne peut �tre vide.\n";
			$result = -2;
		}
		return $result;
	}


    /**
     *      \brief      Mise a jour des param�tres de l'adresse de livraison
     *      \param      id              id adresse de livraison
     *      \param      user            Utilisateur qui demande la mise � jour
     *      \return     int             <0 si ko, >=0 si ok
     */
    function update($idl, $socid, $user='')
    {
        global $langs;

        dolibarr_syslog("Societe::Update");

		// Nettoyage des param�tres
		
        $this->fk_societe=$socid;
        $this->label=trim($this->label);
        $this->nom=trim($this->nom);
        $this->adresse=trim($this->adresse);
        $this->cp=trim($this->cp);
        $this->ville=trim($this->ville);
        $this->departement_id=trim($this->departement_id);
        $this->pays_id=trim($this->pays_id);
        $this->note=trim($this->note);

        $result = $this->verify();		// Verifie que nom et label obligatoire

        if ($result >= 0)
        {
            dolibarr_syslog("Societe::Update delivery adress verify ok");
        
            $sql = "UPDATE ".MAIN_DB_PREFIX."societe_adresse_livraison";
            $sql.= " SET label = '" . addslashes($this->label) ."'"; // Champ obligatoire
            $sql.= ",nom = '" . addslashes($this->nom) ."'"; // Champ obligatoire
            $sql.= ",address = '" . addslashes($this->adresse) ."'";
        
            if ($this->cp)
            { $sql .= ",cp = '" . $this->cp ."'"; }
        
            if ($this->ville)
            { $sql .= ",ville = '" . addslashes($this->ville) ."'"; }
        
            $sql .= ",fk_departement = '" . ($this->departement_id?$this->departement_id:'0') ."'";
            $sql .= ",fk_pays = '" . ($this->pays_id?$this->pays_id:'0') ."'";
            $sql.= ",note = '" . addslashes($this->note) ."'";

            if ($user) $sql .= ",fk_user_modif = '".$user->id."'";
            $sql .= " WHERE fk_societe = '" . $socid ."' AND rowid = '" . $idl ."'";
        
            $resql=$this->db->query($sql);
            if ($resql)
            {        
                $result = 1;
            }
            else
            {
                if ($this->db->errno() == 'DB_ERROR_RECORD_ALREADY_EXISTS')
                {
                    // Doublon
                    $this->error = $langs->trans("ErrorDuplicateField");
                    $result =  -1;
                }
                else
                {
        
                    $this->error = $langs->trans("Error sql=$sql");
                    dolibarr_syslog("Societe::Update delivery adress echec sql=$sql");
                    $result =  -2;
                }
            }
        }

        return $result;

    }

    /**
     *    \brief      Charge depuis la base toutes les adresses de livraison d'une soci�t�
     *    \param      socid       Id de la soci�t� � charger en m�moire
     *    \param      user        Objet de l'utilisateur
     *    \return     int         >0 si ok, <0 si ko
     */
    function fetch($socid, $user=0)
    {
		   global $langs, $conf;

		   $sql = 'SELECT idp, nom, client, fournisseur';
		   $sql .= ' FROM '.MAIN_DB_PREFIX.'societe';
		   $sql .= ' WHERE idp = '.$socid;
		   
		   $resqlsoc=$this->db->query($sql);
    
        if ($resqlsoc)
        {
            if ($this->db->num_rows($resqlsoc))
            {
                $obj = $this->db->fetch_object($resqlsoc);
                
                $this->nom_societe = $obj->nom;
                $this->socid = $obj->idp;
                $this->id = $obj->idp;
                
                if ($this->client == 1)
				        {
				        	$this->nom_url = '<a href="'.DOL_URL_ROOT.'/comm/fiche.php?socid='.$this->id.'">'.$obj->nom.'</a>';
				        }
				        elseif($this->client == 2)
				        {
				        	$this->nom_url = '<a href="'.DOL_URL_ROOT.'/comm/prospect/fiche.php?id='.$this->id.'">'.$obj->nom.'</a>';
				        }
				        else
				        {
					        $this->nom_url = '<a href="'.DOL_URL_ROOT.'/soc.php?socid='.$this->id.'">'.$obj->nom.'</a>';
				        }
             }
             
             $this->lignes = array();
             $this->db->free($resqlsoc);
             
             // Adresses de livraison li�es � la soci�t�
             if ($this->socid)
             {
             	  $sql = 'SELECT a.rowid as idl, a.label, a.nom, a.address,'.$this->db->pdate('a.datec').' as dc';
             	  $sql .= ','. $this->db->pdate('a.tms').' as date_update, a.fk_societe';
             	  $sql .= ', a.cp, a.ville, a.note, a.fk_departement, a.fk_pays';
             	  $sql .= ', p.code as pays_code, p.libelle as pays';
             	  $sql .= ', d.code_departement as departement_code, d.nom as departement';
             	  $sql .= ' FROM '.MAIN_DB_PREFIX.'societe_adresse_livraison as a';
             	  $sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_pays as p ON a.fk_pays = p.rowid';
             	  $sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_departements as d ON a.fk_departement = d.rowid';
             	  $sql .= ' WHERE a.fk_societe = '.$this->socid;
             	  
             	  $resql=$this->db->query($sql);
		            if ($resql)
		            {
			            $num = $this->db->num_rows($resql);
			            $i = 0;
			            while ($i < $num)
			            {
				            $objp                  = $this->db->fetch_object($resql);
				            
				            $ligne                 = new AdresseLivraisonLigne();

				            $ligne->idl             = $objp->idl;
				            $ligne->date_creation   = $objp->dc;
				            $ligne->date_update     = $objp->date_update;
                    $ligne->label           = stripslashes($objp->label);
				            $ligne->nom             = stripslashes($objp->nom);
				            $ligne->adresse         = stripslashes($objp->address);
				            $ligne->cp              = $objp->cp;
				            $ligne->ville           = stripslashes($objp->ville);
				            $ligne->adresse_full    = stripslashes($objp->address) . "\n". $objp->cp . ' '. stripslashes($objp->ville);
				            $ligne->pays_id         = $objp->fk_pays;
				            $ligne->pays_code       = $objp->fk_pays?$objp->pays_code:'';
				            $ligne->pays            = $objp->fk_pays?($langs->trans('Country'.$objp->pays_code)!='Country'.$objp->pays_code?$langs->trans('Country'.$objp->pays_code):$objp->pays):'';
				            $ligne->departement_id  = $objp->fk_departement;
				            $ligne->departement     = $objp->fk_departement?$objp->departement:'';
				            $ligne->note            = $objp->note;
				            
				            $this->lignes[$i]      = $ligne;
				            $i++;
                   }
                   $this->db->free($resql);
                   return 1;
                 }
                 else
                 {
                 	dolibarr_syslog('Erreur AdresseLivraison::Fetch aucune adresse dde livraison');
                 	return -1;
			           }
		          }
		          else
		          {
			          dolibarr_syslog('AdresseLivraison::Societe inconnue');
			          return -2;
		          }
	       }
	       else
	       {
	       	dolibarr_syslog('Erreur Societe::Fetch '.$this->db->error());
			    $this->error=$this->db->error();
			  }
     }
     
         /**
     *    \brief      Charge depuis la base l'objet adresse de livraison
     *    \param      socid       Id de l'adresse de livraison � charger en m�moire
     *    \param      user        Objet de l'utilisateur
     *    \return     int         >0 si ok, <0 si ko
     */
    function fetch_adresse($idl, $user=0)
    {
		global $langs;
		global $conf;

		$sql = 'SELECT a.rowid, a.label, a.nom, a.address,'.$this->db->pdate('a.datec').' as dc';
		$sql .= ','. $this->db->pdate('a.tms').' as date_update';
		$sql .= ', a.cp,a.ville, a.note, a.fk_departement, a.fk_pays';
		$sql .= ', p.code as pays_code, p.libelle as pays';
		$sql .= ', d.code_departement as departement_code, d.nom as departement';
		$sql .= ' FROM '.MAIN_DB_PREFIX.'societe_adresse_livraison as a';
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_pays as p ON a.fk_pays = p.rowid';
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_departements as d ON a.fk_departement = d.rowid';
		$sql .= ' WHERE a.rowid = '.$idl;
		$resql=$this->db->query($sql);
		if ($resql)
		{
			if ($this->db->num_rows($resql))
			{
				$obj = $this->db->fetch_object($resql);

				$this->idl = $obj->rowid;

				$this->date_update = $obj->date_update;
				$this->date_creation = $obj->date_creation;

				$this->label = stripslashes($obj->label);
				$this->nom = stripslashes($obj->nom);
				$this->adresse =  stripslashes($obj->address);
				$this->cp = $obj->cp;
				$this->ville =  stripslashes($obj->ville);
				$this->adresse_full =  stripslashes($obj->address) . "\n". $obj->cp . ' '. stripslashes($obj->ville);

				$this->pays_id = $obj->fk_pays;
				$this->pays_code = $obj->fk_pays?$obj->pays_code:'';
				$this->pays = $obj->fk_pays?($langs->trans('Country'.$obj->pays_code)!='Country'.$obj->pays_code?$langs->trans('Country'.$obj->pays_code):$obj->pays):'';

				$this->departement_id = $obj->fk_departement;
				$this->departement= $obj->fk_departement?$obj->departement:'';

				$this->note = $obj->note;

				$result = 1;
			}
			else
			{
				dolibarr_syslog('Erreur Societe::Fetch aucune adresse de livraison avec id='.$this->id.' - '.$sql);
				$this->error='Erreur Societe::Fetch aucune adresse de livraison avec id='.$this->id.' - '.$sql;
				$result = -2;
			}

			$this->db->free($resql);
		}
		else
		{
			dolibarr_syslog('Erreur Societe::Fetch echec sql='.$sql);
			dolibarr_syslog('Erreur Societe::Fetch '.$this->db->error());
			$this->error=$this->db->error();
			$result = -3;
		}

		return $result;
	}
     

    /**
     *    \brief      Suppression d'une adresse de livraison
     *    \param      id      id de la societe � supprimer
     */
    function delete($idl,$socid)
    {
      dolibarr_syslog("Societe::Delete delivery adress");

      $sql = "DELETE FROM ".MAIN_DB_PREFIX."societe_adresse_livraison";
      $sql .= " WHERE rowid=".$idl." AND fk_societe = ".$socid;

      $result = $this->db->query($sql);

      if (!$result) 
      {
	       print $this->db->error() . '<br>' . $sql;
      }
    }


  /**
   *    \brief      Renvoie le nom d'une societe a partir d'un id
   *    \param      id      id de la soci�t� recherch�e
   *
   */
  function get_nom($id)
  {

    $sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE idp='$id';";

    $result = $this->db->query($sql);

    if ($result)
      {
    	if ($this->db->num_rows())
    	  {
    	    $obj = $this->db->fetch_object($result);
    	    return $obj->nom;
    	  }
    	$this->db->free();
      }
    else {
      dolibarr_print_error($this->db);
    }

  }


   /*
    *       \brief     Charge les informations d'ordre info dans l'objet societe
    *       \param     id     id de la societe a charger
    */
    function info($id)
    {
        $sql = "SELECT s.idp, s.nom, ".$this->db->pdate("datec")." as datec, ".$this->db->pdate("datea")." as datea,";
        $sql.= " fk_user_creat, fk_user_modif";
        $sql.= " FROM ".MAIN_DB_PREFIX."societe as s";
        $sql.= " WHERE s.idp = ".$id;

        $result=$this->db->query($sql);
        if ($result)
        {
            if ($this->db->num_rows($result))
            {
                $obj = $this->db->fetch_object($result);

                $this->id = $obj->idp;

                if ($obj->fk_user_creat) {
                    $cuser = new User($this->db, $obj->fk_user_creat);
                    $cuser->fetch();
                    $this->user_creation     = $cuser;
                }

                if ($obj->fk_user_modif) {
                    $muser = new User($this->db, $obj->fk_user_modif);
                    $muser->fetch();
                    $this->user_modification = $muser;
                }
			    $this->ref			     = $obj->nom;
                $this->date_creation     = $obj->datec;
                $this->date_modification = $obj->datea;
            }

            $this->db->free($result);

        }
        else
        {
            dolibarr_print_error($this->db);
        }
    }

}

class AdresseLivraisonLigne  
{
    
    var $idl;
    var $date_creation;
    var $date_update;
    var $label;
    var $nom;
    var $adresse;
    var $cp;
    var $ville;
    var $adresse_full;
    var $pays_id;
    var $pays_code;
    var $pays;
    var $departement_id;
    var $departement;
    var $note;

    function AdresseLivraisonLigne()
    {
    }
}
?>