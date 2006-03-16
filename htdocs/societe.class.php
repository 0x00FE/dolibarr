<?php
/* Copyright (C) 2002-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2003      Brian Fraval         <brian@fraval.org>
 * Copyright (C) 2006      Andre Cianfarani     <acianfa@free.fr>
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
        \file       htdocs/societe.class.php
        \ingroup    societe
        \brief      Fichier de la classe des societes
        \version    $Revision$
*/


/**
        \class 		Societe
        \brief 		Classe permettant la gestion des societes
*/

class Societe
{
	var $db;
	
	var $id;
	var $nom;
	var $adresse;
	var $cp;
	var $ville;
	var $departement_id;
	var $pays_id;
	var $pays_code;
	var $tel;
	var $fax;
	var $url;
	var $siren;
	var $siret;
	var $ape;
	
	var $prefix_comm;
	
	var $tva_assuj;
	var $tva_intra;
	
	var $capital;
	var $typent_id;
	var $effectif_id;
	var $forme_juridique_code;
	var $forme_juridique;
	
	var $remise_client;
	
	var $client;
	var $prospect;
	var $fournisseur;
	
	var $code_client;
	var $code_fournisseur;
	var $code_compta;
	var $code_compta_fournisseur;
	
	var $note;
	
	var $stcomm_id;
	var $statut_commercial;
	
	var $price_level;


  /**
   *    \brief  Constructeur de la classe
   *    \param  DB     handler acc�s base de donn�es
   *    \param  id     id societe (0 par defaut)
   */
  function Societe($DB, $id=0)
  {
	global $conf;
	
    $this->db = $DB;
    $this->creation_bit = 0;

    $this->id = $id;
    $this->client = 0;
    $this->prospect = 0;
    $this->fournisseur = 0;
    $this->typent_id  = 0;
    $this->effectif_id  = 0;
    $this->forme_juridique_code  = 0;

	// D�finit selon les modules codeclient et codefournisseur
	
    // definit module code client
    $varclient = $conf->global->SOCIETE_CODECLIENT_ADDON;
	require_once DOL_DOCUMENT_ROOT.'/includes/modules/societe/'.$varclient.'.php';
    $this->mod_codeclient = new $varclient;
    $this->codeclient_modifiable = $this->mod_codeclient->code_modifiable;

    // definit module code fournisseur
    $varfournisseur = $conf->global->SOCIETE_CODEFOURNISSEUR_ADDON;
	if ($varfournisseur == $varclient)	// Optimisation
	{
    	$this->mod_codefournisseur = $this->mod_codeclient;
	}
	else
	{
		require_once DOL_DOCUMENT_ROOT.'/includes/modules/societe/'.$varfournisseur.'.php';
    	$this->mod_codefournisseur = new $varfournisseur;
    }
    $this->codefournisseur_modifiable = $this->mod_codefournisseur->code_modifiable;

    return 1;
  }

  /**
   *    \brief      Cr�e la societe en base
   *    \param      user        Objet utilisateur qui demande la cr�ation
   *    \return     int         0 si ok, < 0 si erreur
   */

    function create($user='')
    {
        global $langs,$conf;

        // Nettoyage param�tres
        $this->nom=trim($this->nom);

        dolibarr_syslog("societe.class.php::create ".$this->nom);

        $this->db->begin();

        $result = $this->verify();

        if ($result >= 0)
        {
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."societe (nom, datec, datea, fk_user_creat) ";
            $sql .= " VALUES ('".addslashes($this->nom)."', now(), now(), '".$user->id."')";

            $result=$this->db->query($sql);
            if ($result)
            {
                $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."societe");

                $this->creation_bit = 1;

                $ret = $this->update($this->id,$user,0);
                
                // si un commercial cr�e un client il lui est affect� automatiquement
                if (!$user->rights->commercial->client->voir)
                {
                	$this->add_commercial($user, $user->id);
                }

                if ($ret >= 0)
                {
                    $this->use_webcal=($conf->global->PHPWEBCALENDAR_COMPANYCREATE=='always'?1:0);

                    // Appel des triggers
                    include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
                    $interface=new Interfaces($this->db);
                    $result=$interface->run_triggers('COMPANY_CREATE',$this,$user,$langs,$conf);
                    // Fin appel triggers

                    dolibarr_syslog("Societe::Create success id=".$this->id);
                    $this->db->commit();
		            return 0;
                }
                else
                {
                    dolibarr_syslog("Societe::Create echec update");
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
   *    \brief      Verification lors de la modification
   *    \return     0 si ok, < 0 en cas d'erreur
   */
	function verify()
	{
		$this->nom=trim($this->nom);
		$result = 0;
		if (! $this->nom)
		{
			$this->error = "Le nom de la soci�t� ne peut �tre vide.\n";
			$result = -2;
		}
		if ($this->client && $this->codeclient_modifiable == 1)
		{
			// On ne v�rifie le code client que si la soci�t� est un client / prospect et que le code est modifiable
			// Si il n'est pas modifiable il n'est pas mis � jour lors de l'update
			$rescode = $this->verif_codeclient();
			if ($rescode <> 0)
			{
				if ($rescode == -1)
				{
					$this->error .= "La syntaxe du code client est incorrecte.\n";
				}
				if ($rescode == -2)
				{
					$this->error .= "Vous devez saisir un code client.\n";
				}
				if ($rescode == -3)
				{
					$this->error .= "Ce code client est d�j� utilis�.\n";
				}
				$result = -3;
			}
		}
		return $result;
	}

    /**
     *      \brief      Mise a jour des param�tres de la soci�t
     *      \param      id              id societe
     *      \param      user            Utilisateur qui demande la mise � jour
     *      \param      call_trigger    0=non, 1=oui
     *      \return     int             <0 si ko, >=0 si ok
     */
    function update($id, $user='', $call_trigger=1)
    {
        global $langs;

        dolibarr_syslog("Societe::Update");

		// Nettoyage des param�tres
        $this->id=$id;
        $this->capital=trim($this->capital);
        $this->nom=trim($this->nom);
        $this->adresse=trim($this->adresse);
        $this->cp=trim($this->cp);
        $this->ville=trim($this->ville);
        $this->departement_id=trim($this->departement_id);
        $this->pays_id=trim($this->pays_id);
        $this->tel=trim($this->tel);
        $this->fax=trim($this->fax);
        $this->url=trim($this->url);
        $this->siren=trim($this->siren);
        $this->siret=trim($this->siret);
        $this->ape=trim($this->ape);
        $this->prefix_comm=trim($this->prefix_comm);

		$this->tva_assuj=trim($this->tva_assuj);
        $this->tva_intra=trim($this->tva_intra);

        $this->capital=trim($this->capital);
        $this->effectif_id=trim($this->effectif_id);
        $this->forme_juridique_code=trim($this->forme_juridique_code);

        $result = $this->verify();		// Verifie que nom obligatoire et code client ok et unique

        if ($result >= 0)
        {
            dolibarr_syslog("Societe::Update verify ok");
        
            if (strlen($this->capital) == 0)
            {
                $this->capital = 0;
            }
        
            $this->tel = ereg_replace(" ","",$this->tel);
            $this->tel = ereg_replace("\.","",$this->tel);
            $this->fax = ereg_replace(" ","",$this->fax);
            $this->fax = ereg_replace("\.","",$this->fax);
        
            $sql = "UPDATE ".MAIN_DB_PREFIX."societe";
            $sql.= " SET nom = '" . addslashes($this->nom) ."'"; // Champ obligatoire
            $sql.= ",datea = now()";
            $sql.= ",address = '" . addslashes($this->adresse) ."'";
        
            if ($this->cp)
            { $sql .= ",cp = '" . $this->cp ."'"; }
        
            if ($this->ville)
            { $sql .= ",ville = '" . addslashes($this->ville) ."'"; }
        
            $sql .= ",fk_departement = '" . ($this->departement_id?$this->departement_id:'0') ."'";
            $sql .= ",fk_pays = '" . ($this->pays_id?$this->pays_id:'0') ."'";
        
            $sql .= ",tel = ".($this->tel?"'".addslashes($this->tel)."'":"null");
            $sql .= ",fax = ".($this->fax?"'".addslashes($this->fax)."'":"null");
            $sql .= ",url = ".($this->url?"'".addslashes($this->url)."'":"null");
        
            $sql .= ",siren = '". addslashes($this->siren) ."'";
            $sql .= ",siret = '". addslashes($this->siret) ."'";
            $sql .= ",ape   = '". addslashes($this->ape)   ."'";
        
			$sql .= ",tva_assuj = ".($this->tva_assuj>=0?"'".$this->tva_assuj."'":"null");
            $sql .= ",tva_intra = '" . addslashes($this->tva_intra) ."'";

            $sql .= ",capital = '" .   addslashes($this->capital) ."'";
       
            $sql .= ",prefix_comm = ".($this->prefix_comm?"'".addslashes($this->prefix_comm)."'":"null");
        
            $sql .= ",fk_effectif = ".($this->effectif_id?"'".$this->effectif_id."'":"null");
        
            $sql .= ",fk_typent = ".($this->typent_id?"'".$this->typent_id."'":"null");
        
            $sql .= ",fk_forme_juridique = ".($this->forme_juridique_code?"'".$this->forme_juridique_code."'":"null");
        
            $sql .= ",client = " . $this->client;
            $sql .= ",fournisseur = " . $this->fournisseur;
        
            if ($this->creation_bit || $this->codeclient_modifiable)
            {
                // Attention check_codeclient peut modifier le code suivant le module utilise
                $this->check_codeclient();
        
                $sql .= ", code_client = ".($this->code_client?"'".addslashes($this->code_client)."'":"null");
        
                // Attention get_codecompta peut modifier le code suivant le module utilise
                $this->get_codecompta('customer');
        
                $sql .= ", code_compta = ".($this->code_compta?"'".addslashes($this->code_compta)."'":"null");
            }
        
            if ($this->creation_bit || $this->codefournisseur_modifiable)
            {
                // Attention check_codefournisseur peut modifier le code suivant le module utilise
                $this->check_codefournisseur();
        
                $sql .= ", code_fournisseur = ".($this->code_fournisseur?"'".addslashes($this->code_fournisseur)."'":"null");
        
                // Attention get_codecompta peut modifier le code suivant le module utilise
                $this->get_codecompta('supplier');
        
                $sql .= ", code_compta_fournisseur = ".($this->code_compta_fournisseur?"'".addslashes($this->code_compta_fournisseur)."'":"null");
            }
            if ($user) $sql .= ",fk_user_modif = '".$user->id."'";
            $sql .= " WHERE idp = '" . $id ."'";
        
        	// Verifie que code compta d�fini
        
            $resql=$this->db->query($sql);
            if ($resql)
            {
                if ($call_trigger)
                {
                    // Appel des triggers
                    include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
                    $interface=new Interfaces($this->db);
                    $result=$interface->run_triggers('COMPANY_MODIFY',$this,$user,$langs,$conf);
                    // Fin appel triggers
                }
        
                $result = 1;
            }
            else
            {
                if ($this->db->errno() == 'DB_ERROR_RECORD_ALREADY_EXISTS')
                {
                    // Doublon
                    $this->error = $langs->trans("ErrorPrefixAlreadyExists",$this->prefix_comm);
                    $result =  -1;
                }
                else
                {
        
                    $this->error = $langs->trans("Error sql=$sql");
                    dolibarr_syslog("Societe::Update echec sql=$sql");
                    $result =  -2;
                }
            }
        }

        return $result;

    }

    /**
     *    \brief      Charge depuis la base l'objet societe
     *    \param      socid       Id de la soci�t� � charger en m�moire
     *    \param      user        Objet de l'utilisateur
     *    \return     int         >0 si ok, <0 si ko
     */
    function fetch($socid, $user=0)
    {
		global $langs;
		global $conf;
		/* Lecture des permissions */
		if ($user <> 0)
		{
			$sql = "SELECT p.pread, p.pwrite, p.pperms";
			$sql .= " FROM ".MAIN_DB_PREFIX."societe_perms as p";
			$sql .= " WHERE p.fk_user = '".$user->id."'";
			$sql .= " AND p.fk_soc = '".$socid."';";
			$resql=$this->db->query($sql);
			if ($resql)
			{
				if ($row = $this->db->fetch_row($resql))
				{
					$this->perm_read  = $row[0];
					$this->perm_write = $row[1];
					$this->perm_perms = $row[2];
				}
			}
		}

		$sql = 'SELECT s.idp, s.nom, s.address,'.$this->db->pdate('s.datec').' as dc, prefix_comm';
		// multiprix
		if($conf->global->PRODUIT_MULTIPRICES == 1)
			$sql .= ', s.price_level';
		$sql .= ','. $this->db->pdate('s.tms').' as date_update';
		$sql .= ', s.tel, s.fax, s.url,s.cp,s.ville, s.note, s.siren, client, fournisseur';
		$sql .= ', s.siret, s.capital, s.ape, s.tva_intra, s.rubrique';
		$sql .= ', s.fk_typent as typent_id';
		$sql .= ', s.fk_effectif as effectif_id, e.libelle as effectif';
		$sql .= ', s.fk_forme_juridique as forme_juridique_code, fj.libelle as forme_juridique';
		$sql .= ', s.code_client, s.code_compta, s.code_fournisseur, s.parent';
		$sql .= ', s.fk_departement, s.fk_pays, s.fk_stcomm, s.remise_client, s.mode_reglement, s.cond_reglement, s.tva_assuj';
		$sql .= ', p.code as pays_code, p.libelle as pays';
		$sql .= ', d.code_departement as departement_code, d.nom as departement';
		$sql .= ', st.libelle as stcomm';
		$sql .= ' FROM '.MAIN_DB_PREFIX.'societe as s';
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_effectif as e ON s.fk_effectif = e.id';
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_pays as p ON s.fk_pays = p.rowid';
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_stcomm as st ON s.fk_stcomm = st.id';
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_forme_juridique as fj ON s.fk_forme_juridique = fj.code';
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_departements as d ON s.fk_departement = d.rowid';
		$sql .= ' WHERE s.idp = '.$socid;
		$resql=$this->db->query($sql);
		if ($resql)
		{
			if ($this->db->num_rows($resql))
			{
				$obj = $this->db->fetch_object($resql);

				$this->id = $obj->idp;

				$this->date_update = $obj->date_update;

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

				$transcode=$langs->trans('StatusProspect'.$obj->fk_stcomm);
				$libelle=($transcode!='StatusProspect'.$obj->fk_stcomm?$transcode:$obj->stcomm);
				$this->stcomm_id = $obj->fk_stcomm;     // id statut commercial
				$this->statut_commercial = $libelle;    // libelle statut commercial

				$this->url = $obj->url;
				$this->tel = $obj->tel;
				$this->fax = $obj->fax;

				$this->parent    = $obj->parent;

				$this->siren     = $obj->siren;
				$this->siret     = $obj->siret;
				$this->ape       = $obj->ape;
				$this->capital   = $obj->capital;

				$this->code_client = $obj->code_client;
				$this->code_fournisseur = $obj->code_fournisseur;

				if (! $this->code_client && $this->mod_codeclient->code_modifiable_null == 1)
				{
					$this->codeclient_modifiable = 1;
				}

				if (! $this->code_fournisseur && $this->mod_codefournisseur->code_modifiable_null == 1)
				{
					$this->codefournisseur_modifiable = 1;
				}

				$this->code_compta = $obj->code_compta;
				$this->code_compta_fournisseur = $obj->code_compta_fournisseur;

				$this->tva_assuj      = $obj->tva_assuj;
				$this->tva_intra      = $obj->tva_intra;
				$this->tva_intra_code = substr($obj->tva_intra,0,2);
				$this->tva_intra_num  = substr($obj->tva_intra,2);

				$this->typent_id      = $obj->typent_id;
				//$this->typent         = $obj->fk_typent?$obj->typeent:'';

				$this->effectif_id    = $obj->effectif_id;
				$this->effectif       = $obj->effectif_id?$obj->effectif:'';

				$this->forme_juridique_code= $obj->forme_juridique_code;
				$this->forme_juridique     = $obj->forme_juridique_code?$obj->forme_juridique:'';

				$this->prefix_comm = $obj->prefix_comm;

				$this->remise_client = $obj->remise_client;
				$this->mode_reglement = $obj->mode_reglement;
				$this->cond_reglement = $obj->cond_reglement;
				$this->client      = $obj->client;
				$this->fournisseur = $obj->fournisseur;

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

				$this->rubrique = $obj->rubrique;
				$this->note = $obj->note;
				// multiprix
				if($conf->global->PRODUIT_MULTIPRICES == 1)
					$this->price_level = $obj->price_level;

				$result = 1;
			}
			else
			{
				dolibarr_syslog('Erreur Societe::Fetch aucune societe avec id='.$this->id.' - '.$sql);
				$this->error='Erreur Societe::Fetch aucune societe avec id='.$this->id.' - '.$sql;
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
     *    \brief      Suppression d'une societe de la base avec ses d�pendances (contacts, rib...)
     *    \param      id      id de la societe � supprimer
     */
    function delete($id)
    {
        dolibarr_syslog("Societe::Delete");
        $sqr = 0;

        if ( $this->db->begin())
        {
            $sql = "DELETE from ".MAIN_DB_PREFIX."socpeople ";
            $sql .= " WHERE fk_soc = " . $id .";";

            if ($this->db->query($sql))
            {
                $sqr++;
            }
            else
            {
                $this->error .= "Impossible de supprimer les contacts.\n";
                dolibarr_syslog("Societe::Delete erreur -1");
            }

            $sql = "DELETE from ".MAIN_DB_PREFIX."societe_rib ";
            $sql .= " WHERE fk_soc = " . $id .";";

            if ($this->db->query($sql))
            {
                $sqr++;
            }
            else
            {
                $this->error .= "Impossible de supprimer le RIB.\n";
                dolibarr_syslog("Societe::Delete erreur -2");
            }

            $sql = "DELETE from ".MAIN_DB_PREFIX."societe ";
            $sql .= " WHERE idp = " . $id .";";

            if ($this->db->query($sql))
            {
                $sqr++;
            }
            else
            {
                $this->error .= "Impossible de supprimer la soci�t�.\n";
                dolibarr_syslog("Societe::Delete erreur -3");
            }

            if ($sqr == 3)
            {
                // Appel des triggers
                include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
                $interface=new Interfaces($this->db);
                $result=$interface->run_triggers('COMPANY_DELETE',$this,$user,$langs,$conf);
                // Fin appel triggers

                $this->db->commit();

                // Suppression du r�pertoire document
                $docdir = $conf->societe->dir_output . "/" . $id;

                if (file_exists ($docdir))
                {
                    $this->deldir($docdir);
                }

                return 0;
            }
            else
            {
                $this->db->rollback();
                return -1;
            }
        }

    }

    /**
     *    \brief      Cette fonction permet de supprimer le r�pertoire de la societe
     *                et sous r�pertoire, meme s'ils contiennent des documents.
     *    \param      dir     repertoire a supprimer
     */
    function deldir($dir)
    {
        $current_dir = opendir($dir);

        while($entryname = readdir($current_dir))
        {
            if(is_dir("$dir/$entryname") and ($entryname != "." and $entryname!=".."))
            {
                deldir("${dir}/${entryname}");
            }
            elseif($entryname != "." and $entryname!="..")
            {
                unlink("${dir}/${entryname}");
            }
        }
        closedir($current_dir);
        rmdir(${dir});
    }

  /**
   *    \brief     Retournes les factures impay�es de la soci�t�
   *    \return    array   tableau des id de factures impay�es
   *
   */
  function factures_impayes()
  {
    $facimp = array();
    /*
     * Lignes
     */
    $sql = "SELECT f.rowid";
    $sql .= " FROM ".MAIN_DB_PREFIX."facture as f WHERE f.fk_soc = '".$this->id . "'";
    $sql .= " AND f.fk_statut = '1' AND f.paye = '0'";

    if ($this->db->query($sql))
      {
	$num = $this->db->num_rows();
	$i = 0;

	while ($i < $num)
	  {
	    $objp = $this->db->fetch_object();
	    $array_push($facimp, $objp->rowid);
	    $i++;
	    print $i;
	  }

	$this->db->free();
      }
    return $facimp;
  }

  /**
   *    \brief      Attribut le prefix de la soci�t� en base
   *
   */
  function attribute_prefix()
  {
    $sql = "SELECT nom FROM ".MAIN_DB_PREFIX."societe WHERE idp = '$this->id'";
    if ( $this->db->query( $sql) )
      {
	if ( $this->db->num_rows() )
	  {
	    $nom = preg_replace("/[[:punct:]]/","",$this->db->result(0,0));
	    $this->db->free();

	    $prefix = $this->genprefix($nom,4);

	    $sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."societe WHERE prefix_comm = '$prefix'";
	    if ( $this->db->query( $sql) )
	      {
		if ( $this->db->result(0, 0) )
		  {
		    $this->db->free();
		  }
		else
		  {
		    $this->db->free();

		    $sql = "UPDATE ".MAIN_DB_PREFIX."societe set prefix_comm='$prefix' WHERE idp='$this->id'";

		    if ( $this->db->query( $sql) )
		      {

		      }
		    else
		      {
			dolibarr_print_error($this->db);
		      }
		  }
	      }
	    else
	      {
	        dolibarr_print_error($this->db);
	      }
	  }
      }
    else
      {
	dolibarr_print_error($this->db);
      }
    return $prefix;
  }

  /**
   *    \brief      G�n�re le pr�fix de la soci�t�
   *    \param      nom         nom de la soci�t�
   *    \param      taille      taille du prefix � retourner
   *    \param      mot         l'indice du mot � utiliser
   *
   */
  function genprefix($nom, $taille=4,$mot=0)
  {
    $retour = "";
    $tab = explode(" ",$nom);
    if($mot < count($tab)) {
      $prefix = strtoupper(substr($tab[$mot],0,$taille));
      //On v�rifie que ce prefix n'a pas d�j� �t� pris ...
      $sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."societe WHERE prefix_comm = '$prefix'";
      if ( $this->db->query( $sql) )
	{
	  if ( $this->db->result(0, 0) )
	    {
	      $this->db->free();
	      $retour = $this->genprefix($nom,$taille,$mot+1);
	    }
	  else
	    {
	      $retour = $prefix;
	    }
	}
    }
    return $retour;
  }

  /**
   *    \brief     D�finit la soci�t� comme un client
   *
   */
  function set_as_client()
  {
    if ($this->id)
      {
	$sql  = "UPDATE ".MAIN_DB_PREFIX."societe ";
	$sql .= " SET client = 1";
	$sql .= " WHERE idp = " . $this->id .";";

	return $this->db->query($sql);
      }
  }

  /**
   *    \brief      D�finit la soci�t� comme un client
   *    \param      remise      montant de la remise
   *    \param      user        utilisateur qui place la remise
   */
  function set_remise_client($remise, $user)
  {
    if ($this->id)
      {
	$sql  = "UPDATE ".MAIN_DB_PREFIX."societe ";
	$sql .= " SET remise_client = '".$remise."'";
	$sql .= " WHERE idp = " . $this->id .";";

	$this->db->query($sql);

	$sql  = "INSERT INTO ".MAIN_DB_PREFIX."societe_remise ";
	$sql .= " ( datec, fk_soc, remise_client, fk_user_author )";
	$sql .= " VALUES (now(),".$this->id.",'".$remise."',".$user->id.")";

	if (! $this->db->query($sql) )
	  {
	    dolibarr_print_error($this->db);
	  }

      }
  }

  /**
   *    \brief      D�finit la soci�t� comme un client
   *    \param      remise      montant de la remise
   *    \param      user        utilisateur qui place la remise
   *
   */
  function set_remise_except($remise, $user)
  {
    if ($this->id)
      {
	$remise = price2num($remise);

	$sql  = "DELETE FROM  ".MAIN_DB_PREFIX."societe_remise_except ";
	$sql .= " WHERE fk_soc = " . $this->id ." AND fk_facture IS NULL;";

	$this->db->query($sql);

	$sql  = "INSERT INTO ".MAIN_DB_PREFIX."societe_remise_except ";
	$sql .= " ( datec, fk_soc, amount_ht, fk_user )";
	$sql .= " VALUES (now(),".$this->id.",'".$remise."',".$user->id.")";

	if (! $this->db->query($sql) )
	  {
	    dolibarr_print_error($this->db);
	  }

      }
  }
function set_price_level($price_level, $user)
  {
    if ($this->id)
      {
	$sql  = "UPDATE ".MAIN_DB_PREFIX."societe ";
	$sql .= " SET price_level = '".$price_level."'";
	$sql .= " WHERE idp = " . $this->id .";";

	$this->db->query($sql);

	$sql  = "INSERT INTO ".MAIN_DB_PREFIX."societe_prices ";
	$sql .= " ( datec, fk_soc, price_level, fk_user_author )";
	$sql .= " VALUES (now(),".$this->id.",'".$price_level."',".$user->id.")";

	if (! $this->db->query($sql) )
	  {
	    dolibarr_print_error($this->db);
	  }

      }
  }

  /**
   *
   *
   */
  function add_commercial($user, $commid)
  {
    if ($this->id > 0 && $commid > 0)
      {
	$sql  = "DELETE FROM  ".MAIN_DB_PREFIX."societe_commerciaux ";
	$sql .= " WHERE fk_soc = " . $this->id ." AND fk_user =".$commid;

	$this->db->query($sql);

	$sql  = "INSERT INTO ".MAIN_DB_PREFIX."societe_commerciaux ";
	$sql .= " ( fk_soc, fk_user )";
	$sql .= " VALUES (".$this->id.",".$commid.")";

	if (! $this->db->query($sql) )
	  {
	    dolibarr_syslog("Societe::add_commercial Erreur");
	  }

      }
  }

  /**
   *
   *
   *
   */
  function del_commercial($user, $commid)
  {
    if ($this->id > 0 && $commid > 0)
      {
	$sql  = "DELETE FROM  ".MAIN_DB_PREFIX."societe_commerciaux ";
	$sql .= " WHERE fk_soc = " . $this->id ." AND fk_user =".$commid;

	if (! $this->db->query($sql) )
	  {
	    dolibarr_syslog("Societe::del_commercial Erreur");
	  }

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

  /**
   *    \brief      Renvoie la liste des contacts emails existant pour la soci�t�
   *    \return     array       tableau des contacts emails
   */
  function contact_email_array()
  {
    $contact_email = array();

    $sql = "SELECT idp, email, name, firstname FROM ".MAIN_DB_PREFIX."socpeople WHERE fk_soc = '$this->id'";

    if ($this->db->query($sql) )
      {
	$nump = $this->db->num_rows();

	if ($nump)
	  {
	    $i = 0;
	    while ($i < $nump)
	      {
		$obj = $this->db->fetch_object();

		$contact_email[$obj->idp] = "$obj->firstname $obj->name &lt;$obj->email&gt;";
		$i++;
	      }
	  }
	return $contact_email;
      }
    else
      {
	dolibarr_print_error($this->db);
      }

  }

  /**
   *    \brief      Renvoie la liste des contacts de cette soci�t�
   *    \return     array      tableau des contacts
   */
  function contact_array()
  {
    $contacts = array();

    $sql = "SELECT idp, name, firstname FROM ".MAIN_DB_PREFIX."socpeople WHERE fk_soc = '$this->id'";

    if ($this->db->query($sql) )
      {
	$nump = $this->db->num_rows();

	if ($nump)
	  {
	    $i = 0;
	    while ($i < $nump)
	      {
		$obj = $this->db->fetch_object();

		$contacts[$obj->idp] = "$obj->firstname $obj->name";
		$i++;
	      }
	  }
	return $contacts;
      }
    else
      {
	dolibarr_print_error($this->db);
      }

  }

    /**
     *    \brief      Renvoie l'email d'un contact par son id
     *    \param      rowid       id du contact
     *    \return     string      email du contact
     */
    function contact_get_email($rowid)
    {

        $sql = "SELECT idp, email, name, firstname FROM ".MAIN_DB_PREFIX."socpeople WHERE idp = '$rowid'";

        if ($this->db->query($sql) )
        {
            $nump = $this->db->num_rows();

            if ($nump)
            {

                $obj = $this->db->fetch_object();

                $contact_email = "$obj->firstname $obj->name <$obj->email>";

            }
            return $contact_email;
        }
        else
        {
            dolibarr_print_error($this->db);
        }

    }


    /**
     *    \brief      Renvoie la liste des libell�s traduits types actifs de soci�t�s
     *    \return     array      tableau des types
     */
    function typent_array()
    {
        global $langs;

        $effs = array();

        $sql  = "SELECT id, code, libelle";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_typent";
        $sql .= " WHERE active = 1";
        $sql .= " ORDER by id";
        $result=$this->db->query($sql);
        if ($result)
        {
            $num = $this->db->num_rows($result);
            $i = 0;

            while ($i < $num)
            {
                $objp = $this->db->fetch_object($result);
                if ($langs->trans($objp->code) != $objp->code)
                    $effs[$objp->id] = $langs->trans($objp->code);
                else
                    $effs[$objp->id] = $objp->libelle!='-'?$objp->libelle:'';
                $i++;
            }
            $this->db->free($result);
        }

        return $effs;
    }


    /**
     *    \brief      Renvoie la liste des types d'effectifs possibles (pas de traduction car nombre)
     *    \return     array      tableau des types d'effectifs
     */
    function effectif_array()
    {
        $effs = array();

        $sql = "SELECT id, libelle";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_effectif";
        $sql .= " ORDER BY id ASC";
        if ($this->db->query($sql))
        {
            $num = $this->db->num_rows();
            $i = 0;

            while ($i < $num)
            {
                $objp = $this->db->fetch_object();
                $effs[$objp->id] = $objp->libelle!='-'?$objp->libelle:'';
                $i++;
            }
            $this->db->free();
        }
        return $effs;
    }

    /**
     *    \brief      Renvoie la liste des formes juridiques existantes (pas de traduction car unique au pays)
     *    \return     array      tableau des formes juridiques
     */
    function forme_juridique_array()
    {
        $fj = array();

        $sql = "SELECT code, libelle";
        $sql .= " FROM ".MAIN_DB_PREFIX."c_forme_juridique";
        $sql .= " ORDER BY code ASC";
        if ($this->db->query($sql))
        {
            $num = $this->db->num_rows();
            $i = 0;

            while ($i < $num)
            {
                $objp = $this->db->fetch_object();
                $fj[$objp->code] = $objp->libelle!='-'?$objp->libelle:'';
                $i++;
            }
            $this->db->free();
        }
        return $fj;
    }


  /**
   *    \brief      Affiche le rib
   */
  function display_rib()
  {
    global $langs;

    require_once DOL_DOCUMENT_ROOT . "/companybankaccount.class.php";

    $bac = new CompanyBankAccount($this->db, $this->id);
    $bac->fetch();

    if ($bac->code_banque || $bac->code_guichet || $bac->number || $bac->cle_rib)
    {
        $rib = $bac->code_banque." ".$bac->code_guichet." ".$bac->number." (".$bac->cle_rib.")";
    }
    else
    {
        $rib=$langs->trans("NoRIB");
    }
    return $rib;
  }


  function rib()
  {
    require_once DOL_DOCUMENT_ROOT . "/companybankaccount.class.php";

    $bac = new CompanyBankAccount($this->db, $this->id);
    $bac->fetch();

    $this->bank_account = $bac;

    return 1;
  }


  function verif_rib()
  {
    $this->rib();
    return $this->bank_account->verif();
  }


	/**
	 *    \brief      Verifie code client
	 *    \return     Renvoie 0 si ok, peut modifier le code client suivant le module utilis
	 */
	function verif_codeclient()
	{
		global $conf;
		if ($conf->global->SOCIETE_CODECLIENT_ADDON)
		{
			require_once DOL_DOCUMENT_ROOT.'/includes/modules/societe/'.$conf->global->SOCIETE_CODECLIENT_ADDON.'.php';
	
			$var = $conf->global->SOCIETE_CODECLIENT_ADDON;
	
			$mod = new $var;
	
			return $mod->verif($this->db, $this->code_client, $this->id);
		}
		else
		{
			return 0;
		}
	}
	
	
	function check_codeclient()
	{
		if ($conf->global->SOCIETE_CODECLIENT_ADDON)
		{
			require_once DOL_DOCUMENT_ROOT.'/includes/modules/societe/'.$conf->global->SOCIETE_CODECLIENT_ADDON.'.php';
	
			$var = $conf->global->SOCIETE_CODECLIENT_ADDON;
	
			$mod = new $var;
	
			return $mod->verif($this->db, $this->code_client);
		}
		else
		{
			return 0;
		}
	}
	
	function check_codefournisseur()
	{
		if ($conf->global->CODEFOURNISSEUR_ADDON)
		{
			require_once DOL_DOCUMENT_ROOT.'/includes/modules/societe/'.$conf->global->CODEFOURNISSEUR_ADDON.'.php';
	
			$var = $conf->global->CODEFOURNISSEUR_ADDON;
	
			$mod = new $var;
	
			return $mod->verif($this->db, $this->code_fournisseur);
		}
		else
		{
			return 0;
		}
	}

	/**
	 *    	\brief  	Renvoie un code compta, suivant le module de code compta.
	 *            		Peut �tre identique � celui saisit ou g�n�r� automatiquement.
	 *            		A ce jour seul la g�n�ration automatique est impl�ment�e
	 *    	\param      type			Type de tiers ('customer' ou 'supplier')
	 */
	function get_codecompta($type)
	{
		global $conf;
	
		if ($conf->global->SOCIETE_CODECOMPTA_ADDON)
		{
			require_once DOL_DOCUMENT_ROOT.'/includes/modules/societe/'.$conf->global->SOCIETE_CODECOMPTA_ADDON.'.php';
	
			$var = $conf->global->SOCIETE_CODECOMPTA_ADDON;
	
			$mod = new $var;
	
			$result = $mod->get_code($this->db, $this, $type);
	
			if ($type == 'customer') $this->code_compta = $mod->code;
			if ($type == 'supplier') $this->code_compta_fournisseur = $mod->code;
	
			return $result;
		}
		else
		{
			if ($type == 'customer') $this->code_compta = '';
			if ($type == 'supplier') $this->code_compta_fournisseur = '';

			return 0;
		}
	}
	
    /**
     *    \brief      D�fini la soci�t� m�re pour les filiales
     *    \param      id      id compagnie m�re � positionner
     *    \return     int     <0 si ko, >0 si ok
     */
    function set_parent($id)
    {
        if ($this->id)
        {
            $sql  = "UPDATE ".MAIN_DB_PREFIX."societe ";
            $sql .= " SET parent = ".$id;
            $sql .= " WHERE idp = " . $this->id .";";

            if ( $this->db->query($sql) )
            {
                return 1;
            }
            else
            {
                return -1;
            }
        }
    }

    /**
     *    \brief      Supprime la soci�t� m�re
     *    \param      id      id compagnie m�re � effacer
     *    \return     int     <0 si ko, >0 si ok
     */
    function remove_parent($id)
    {
        if ($this->id)
        {
            $sql  = "UPDATE ".MAIN_DB_PREFIX."societe ";
            $sql .= " SET parent = null";
            $sql .= " WHERE idp = " . $this->id .";";

            if ( $this->db->query($sql) )
            {
                return 1;
            }
            else
            {
                return -1;
            }
        }
    }

    /**
     *      \brief      Verifie la validite d'un identifiant professionnel en
     *                  fonction du pays de la societe (siren, siret, ...)
     *      \param      idprof          1,2,3,4 (Exemple: 1=siren,2=siret,3=naf,4=rcs/rm)
     *      \param      soc             Objet societe
     *      \return     int             <0 si ko, >0 si ok
     */
    function id_prof_check($idprof,$soc)
    {
        $ok=1;

        // Verifie SIREN si pays FR
        if ($idprof == 1 && $soc->pays_code == 'FR')
        {
            $chaine=trim($this->siren);
            $chaine=eregi_replace(' ','',$chaine);

            if (strlen($chaine) != 9) return -1;

            $sum = 0;

            for ($i = 0 ; $i < 10 ; $i = $i+2)
            {
                $sum = $sum + substr($this->siren, (8 - $i), 1);
            }

            for ($i = 1 ; $i < 9 ; $i = $i+2)
            {
                $ps = 2 * substr($this->siren, (8 - $i), 1);

                if ($ps > 9)
                {
                    $ps = substr($ps, 0,1) + substr($ps, 1 ,1);
                }
                $sum = $sum + $ps;
            }

            if (substr($sum, -1) != 0) return -1;
        }

        // Verifie SIRET si pays FR
        if ($idprof == 2 && $soc->pays_code == 'FR')
        {
            $chaine=trim($this->siret);
            $chaine=eregi_replace(' ','',$chaine);

            if (strlen($chaine) != 14) return -1;
        }

        return $ok;
    }

    /**
     *      \brief      Renvoi url de v�rification d'un identifiant professionnal
     *      \param      idprof          1,2,3,4 (Exemple: 1=siren,2=siret,3=naf,4=rcs/rm)
     *      \param      soc             Objet societe
     *      \return     string          url ou chaine vide si aucune url connue
     */
    function id_prof_url($idprof,$soc)
    {
        global $langs;

        $url='';
        if ($idprof == 1 && $soc->pays_code == 'FR') $url='http://www.societe.com/cgi-bin/recherche?rncs='.$soc->siren;
        if ($idprof == 1 && $soc->pays_code == 'GB') $url='http://www.companieshouse.gov.uk/WebCHeck/findinfolink/';

        if ($url) return '<a target="_blank" href="'.$url.'">['.$langs->trans("Check").']</a>';
        return '';
    }

    /**
     *      \brief      Indique si la soci�t� a des projets
     *      \return     bool	   true si la soci�t� a des projets, false sinon
     */
    function has_projects()
    {
        $sql = 'SELECT COUNT(*) as numproj FROM '.MAIN_DB_PREFIX.'projet WHERE fk_soc = ' . $this->id;
        $resql = $this->db->query($sql);
        if ($resql)
        {
            $nump = $this->db->num_rows($resql);
            $obj = $this->db->fetch_object();
            $count = $obj->numproj;
        }
        else
        {
            $count = 0;
            print $this->db->error();
        }
        $this->db->free($resql);
        return ($count > 0);
    }


    function AddPerms($user_id, $read, $write, $perms)
    {
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."societe_perms";
        $sql .= " (fk_soc, fk_user, pread, pwrite, pperms) ";
        $sql .= " VALUES (".$this->id.",".$user_id.",".$read.",".$write.",".$perms.");";

        $resql=$this->db->query($sql);
        if ($resql)
        {

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

   /*
    *       \brief     Renvoi si pays appartient � CEE
    *       \param     boolean		true = pays dans CEE, false= pays hors CEE
    */
    function isInEEC()
    {
    	// \todo liste code pays � compl�ter
		$country_code_in_EEC=array('BE','FR','LU');	
    	//print "dd".$this->pays_code;
		return in_array($this->pays_code,$country_code_in_EEC);
	}

}

?>
