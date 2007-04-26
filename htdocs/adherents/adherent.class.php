<?php
/* Copyright (C) 2002-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2002-2003 Jean-Louis Bergamo   <jlb@j1b.org>
 * Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 */

/**
	    \file       htdocs/adherents/adherent.class.php
        \ingroup    adherent
		\brief      Fichier de la classe permettant la gestion d'un adh�rent
		\author     Rodolphe Qiedeville
		\author	    Jean-Louis Bergamo
		\author	    Laurent Destailleur
		\author     Sebastien Di Cintio
		\author     Benoit Mortier
		\version    $Revision$
*/

require_once(DOL_DOCUMENT_ROOT."/includes/xmlrpc/xmlrpc.php");


/**
        \class      Adherent
		\brief      Classe permettant la gestion d'un adh�rent
*/

class Adherent
{
	var $id;
	var $db;
	var $error;
	var $errors=array();
	
	var $prenom;
	var $nom;
	var $fullname;
	var $login;
	var $pass;
	var $societe;
	var $adresse;
	var $cp;
	var $ville;
	var $pays_id;
	var $pays_code;
	var $pays;

	var $email;
	var $phone;
	var $phone_perso;
	var $phone_mobile;

	var $morphy;
	var $public;
	var $commentaire;
	var $statut;			// -1=brouillon, 0=r�sili�, 1=valid�,pay�
	var $photo;

	var $datec;
	var $datem;
	var $datefin;
	var $datevalid;
	var $naiss;

	var $typeid;			// Id type adherent
	var $type;				// Libell� type adherent
	var $need_subscription;

	var $user_id;
	var $user_login;


	//  var $public;
	var $array_options;



	/**
			\brief Adherent
			\param DB		base de donn�es
			\param id		id de l'adh�rent
	*/
	function Adherent($DB)
	{
		$this->db = $DB ;
		$this->statut = -1;
		// l'adherent n'est pas public par defaut
		$this->public = 0;
		// les champs optionnels sont vides
		$this->array_options=array();
	}


	/**
		\brief	    Fonction envoyant un email au destinataire (recipient) avec le text fourni en parametre.
		\param	    recipients		destinataires
		\param	    text			contenu du message
		\param	    subject			sujet du message
		\return		int				<0 si ko, >0 si ok
		\remarks		La particularite de cette fonction est de remplacer certains champs
		\remarks		par leur valeur pour l'adherent en l'occurrence :
		\remarks		%PRENOM% : est remplace par le prenom
		\remarks		%NOM% : est remplace par nom
		\remarks		%INFOS% : l'ensemble des attributs de cet adherent
		\remarks		%SERVEUR% : URL du serveur web
		\remarks		etc..
	*/
	function send_an_email($recipients,$text,$subject="Vos coordonnees sur %SERVEUR%")
	{
		global $conf,$langs;

	    $patterns = array (
		       '/%PRENOM%/',
		       '/%NOM%/',
		       '/%INFOS%/',
		       '/%DOL_MAIN_URL_ROOT%/',
		       '/%SOCIETE%/',
		       '/%ADRESSE%/',
		       '/%CP%/',
		       '/%VILLE%/',
		       '/%PAYS%/',
		       '/%EMAIL%/',
		       '/%NAISS%/',
		       '/%PHOTO%/',
		       '/%LOGIN%/',
		       '/%PASSWORD%/'
		       );
	    $infos.= $langs->trans("Lastname").": $this->nom\n";
	    $infos = $langs->trans("Firstname").": $this->prenom\n";
	    $infos.= $langs->trans("Company").": $this->societe\n";
	    $infos.= $langs->trans("Address").": $this->adresse\n";
	    $infos.= $langs->trans("Zip").": $this->cp\n";
	    $infos.= $langs->trans("Town").": $this->ville\n";
	    $infos.= $langs->trans("Country").": $this->pays\n";
	    $infos.= $langs->trans("EMail").": $this->email\n";
	    $infos.= $langs->trans("Login").": $this->login\n";
	    $infos.= $langs->trans("Password").": $this->pass\n";
	    $infos.= $langs->trans("Birthday").": $this->naiss\n";
	    $infos.= $langs->trans("Photo").": $this->photo\n";
		$infos.= $langs->trans("Public").": ".yn($this->public)."\n";

	    $replace = array (
		      $this->prenom,
		      $this->nom,
		      $infos,
		      DOL_MAIN_URL_ROOT,
		      $this->societe,
		      $this->adresse,
		      $this->cp,
		      $this->ville,
		      $this->pays,
		      $this->email,
		      $this->naiss,
		      $this->photo,
		      $this->login,
		      $this->pass
		      );
		$texttosend = preg_replace ($patterns, $replace, $text);
		$subjectosend = preg_replace ($patterns, $replace, $subject);
		$msgishtml=0;

		// Envoi mail confirmation
        include_once(DOL_DOCUMENT_ROOT."/lib/CMailFile.class.php");

        $from=$conf->email_from;
        if ($conf->global->ADHERENT_MAIL_FROM) $from=$conf->global->ADHERENT_MAIL_FROM;

		$mailfile = new CMailFile($subjectosend,$this->email,$from,$texttosend,
									array(),array(),array(),
									'', '', 0, $msgishtml);
        if ($mailfile->sendfile())
        {
            return 1;
        }
        else
        {
            $this->error=$langs->trans("ErrorFailedToSendMail",$from,$this->email).'. '.$mailfile->error;
            return -1;
        }

	}


/**
		\brief	imprime une liste d'erreur.
*/

  function print_error_list()
  {
    $num = sizeof($this->error);
    for ($i = 0 ; $i < $num ; $i++)
      {
	print "<li>" . $this->error[$i];
      }
  }


/**
		\brief      Renvoie le libelle traduit de la nature d'un adherent (physique ou morale)
		\param	    morphy		Nature physique ou morale de l'adh�rent
*/

  function getmorphylib($morphy='')
  {
    global $langs;
    if (! $morphy) { $morphy=$this->morphy; }
    if ($morphy == 'phy') { return $langs->trans("Physical"); }
    if ($morphy == 'mor') { return $langs->trans("Moral"); }
    return $morphy;
  }

/**
		\brief      V�rifie les donn�es entr�es
		\param	    minimum
*/

  function check($minimum=0)
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

      if (strlen(trim($this->login)) == 0)
	{
	  $error_string[$err] = "Le login saisi est invalide";
	  $err++;
	}

      if (strlen(trim($this->pass)) == 0)
	{
	  $error_string[$err] = "Le pass saisi est invalide";
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
       */

      if ($err)
	{
	  $this->error = $error_string;
	  return 0;
	}
      else
	{
	  return 1;
	}

    }

	/**
		\brief  	Fonction qui cr�e l'adh�rent
		\return		int			<0 si ko, >0 si ok
	*/
	function create()
	{
		global $conf,$langs,$user;

		// Verification parametres
		if ($conf->global->ADHERENT_MAIL_REQUIRED && ! ValidEMail($this->email))
		{
			$this->error = $langs->trans("ErrorBadEMail",$this->email);
			return -1;
		}
		if (! $this->datec) $this->datec=time();
		
		$this->db->begin();
		
		// Insertion membre
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."adherent";
		$sql.= " (datec,login)";
		$sql.= " VALUES (";
		$sql.= " '".$this->db->idate($this->datec)."',";
		$sql.= " '".addslashes($this->login)."'";
		$sql.= ")";

		dolibarr_syslog("Adherent::create sql=".$sql);
		$result = $this->db->query($sql);
		if ($result)
		{
			$id = $this->db->last_insert_id(MAIN_DB_PREFIX."adherent");
			if ($id > 0)
			{
				$this->id=$id;
				
				// Mise a jour
				$result=$this->update($user,1);
				if ($result < 0)
				{
					$this->db->rollback();
					return -1;
				}
				
				$this->use_webcal=($conf->global->PHPWEBCALENDAR_MEMBERSTATUS=='always'?1:0);

	            // Appel des triggers
	            include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
	            $interface=new Interfaces($this->db);
	            $result=$interface->run_triggers('MEMBER_CREATE',$this,$user,$langs,$conf);
                if ($result < 0) $this->errors=$interface->errors;
	            // Fin appel triggers
				if (sizeof($this->errors))
				{
					$this->db->rollback();
					return -3;
				}
				else
				{
					$this->db->commit();
					return $this->id;
				}
			}
			else
			{
				$this->error='Failed to get last insert id';
				$this->db->rollback();
				return -2;
			}				
		}
		else
		{
			$this->error=$this->db->error();
			$this->db->rollback();
			return -1;
		}
	}


	/**
			\brief 		Fonction qui met � jour l'adh�rent
			\param		user			Utilisateur qui r�alise la mise a jour
			\param		notrigger		1=d�sactive le trigger UPDATE (quand appel� par creation)
			\return		int				<0 si KO, >0 si OK
	*/
	function update($user,$notrigger=0)
	{
		global $conf,$langs;

		dolibarr_syslog("Adherent::update user=".$user->id." notrigger=".$notrigger);

		// Verification parametres
		if ($conf->global->ADHERENT_MAIL_REQUIRED && ! ValidEMail($this->email))
		{
			$this->error = $langs->trans("ErrorBadEMail",$this->email);
			return -1;
		}

		$this->db->begin();

		$sql = "UPDATE ".MAIN_DB_PREFIX."adherent SET";
		$sql .= " prenom = ".($this->prenom?"'".addslashes($this->prenom)."'":"null");
		$sql .= ",nom="     .($this->nom?"'".addslashes($this->nom)."'":"null");
		$sql .= ",login="   .($this->login?"'".addslashes($this->login)."'":"null");
		$sql .= ",pass="    .($this->pass?"'".addslashes($this->pass)."'":"null");
		$sql .= ",societe=" .($this->societe?"'".addslashes($this->societe)."'":"null");
		$sql .= ",adresse=" .($this->adresse?"'".addslashes($this->adresse)."'":"null");
		$sql .= ",cp="      .($this->cp?"'".addslashes($this->cp)."'":"null");
		$sql .= ",ville="   .($this->ville?"'".addslashes($this->ville)."'":"null");
		$sql .= ",pays="    ."'".$this->pays_id."'";
		$sql .= ",email="   ."'".$this->email."'";
		$sql .= ",phone="   .($this->phone?"'".addslashes($this->phone)."'":"null");
		$sql .= ",phone_perso="  .($this->phone_perso?"'".addslashes($this->phone_perso)."'":"null");
		$sql .= ",phone_mobile=" .($this->phone_mobile?"'".addslashes($this->phone_mobile)."'":"null");
		$sql .= ",note="    .($this->commentaire?"'".addslashes($this->commentaire)."'":"null");
		$sql .= ",photo="   .($this->photo?"'".$this->photo."'":"null");
		$sql .= ",public="  ."'".$this->public."'";
		$sql .= ",statut="  .$this->statut;
		$sql .= ",fk_adherent_type=".$this->typeid;
		$sql .= ",morphy="  ."'".$this->morphy."'";
		$sql .= ",naiss="   .($this->naiss?"'".$this->db->idate($this->naiss)."'":"null");
		if ($this->datefin)   $sql .= ",datefin='".$this->db->idate($this->datefin)."'";		// Ne doit etre modifi� que par effacement cotisation
		if ($this->datevalid) $sql .= ",datevalid='".$this->db->idate($this->datevalid)."'";	// Ne doit etre modifi� que par validation adherent

		$sql .= " WHERE rowid = ".$this->id;

		dolibarr_syslog("Adherent::update sql=$sql");
		$result = $this->db->query($sql);
		if (! $result)
		{
			$this->error=$this->db->error();
			$this->db->rollback();
			return -1;
		}

		if (sizeof($this->array_options) > 0)
		{
			$sql_del = "DELETE FROM ".MAIN_DB_PREFIX."adherent_options WHERE adhid = ".$this->id;
			$this->db->query($sql_del);

			$sql = "INSERT INTO ".MAIN_DB_PREFIX."adherent_options (adhid";
			foreach($this->array_options as $key => $value)
			{
				// recupere le nom de l'attribut
				$attr=substr($key,8);
				$sql.=",$attr";
			}
			$sql .= ") VALUES (".$this->id;
			foreach($this->array_options as $key => $value)
			{
				$sql.=",'".$this->array_options[$key]."'";
			}
			$sql.=")";

			$result = $this->db->query($sql);
			if (! $result)
			{
				$this->error=$this->db->error();
				dolibarr_syslog("Adherent::update ".$this->error);
				$this->db->rollback();
				return -2;
			}
		}

		if (! $notrigger)
		{
			$this->use_webcal=($conf->global->PHPWEBCALENDAR_MEMBERSTATUS=='always'?1:0);

			// Appel des triggers
	        include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
	        $interface=new Interfaces($this->db);
	        $result=$interface->run_triggers('MEMBER_MODIFY',$this,$user,$langs,$conf);
            if ($result < 0) $this->errors=$interface->errors;
 	        // Fin appel triggers
		}

		$this->db->commit();

		return 1;
	}


	/**
			\brief 		Fonction qui supprime l'adh�rent et les donn�es associ�es
			\param		rowid
			\return		int			<0 si KO, 0 = rien a effacer, >0 si OK
	*/
	function delete($rowid)
	{
		global $conf, $langs;
		$result = 0;

		// Suppression options
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."adherent_options WHERE adhid = ".$rowid;
		
		dolibarr_syslog("Adherent::delete sql=".$sql);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."cotisation WHERE fk_adherent = ".$rowid;
			dolibarr_syslog("Adherent::delete sql=".$sql);
			$resql=$this->db->query( $sql);
			if ($resql)
			{
				$sql = "DELETE FROM ".MAIN_DB_PREFIX."adherent WHERE rowid = ".$rowid;
				dolibarr_syslog("Adherent::delete sql=".$sql);
				$resql=$this->db->query($sql);
				if ($resql)
				{
					if ($this->db->affected_rows($resql))
					{
						$this->use_webcal=($conf->global->PHPWEBCALENDAR_MEMBERSTATUS=='always'?1:0);

						// Appel des triggers
				        include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
				        $interface=new Interfaces($this->db);
				        $result=$interface->run_triggers('MEMBER_DELETE',$this,$user,$langs,$conf);
						if ($result < 0) $this->errors=$interface->errors;
				        // Fin appel triggers

						$this->db->commit();
						return 1;
					}
					else
					{
						// Rien a effacer
						$this->db->rollback();
						return 0;
					}
				}
				else
				{
					$this->error=$this->db->error();
					$this->db->rollback();
					return -3;
				}
			}
			else
			{
				$this->error=$this->db->error();
				$this->db->rollback();
				return -2;
			}
		}
		else
		{
			$this->error=$this->db->error();
			$this->db->rollback();
			return -1;
		}
	
		return $result;
	
	}

	
		/**
	 *    \brief     Change le mot de passe d'un utilisateur
	 *    \param     user             Object user de l'utilisateur qui fait la modification
	 *    \param     password         Nouveau mot de passe (� g�n�rer si non communiqu�)
	 *    \param     isencrypted      0 ou 1 si il faut crypter le mot de passe en base (0 par d�faut)
	 *    \return    string           mot de passe, < 0 si erreur
	 */
    function password($user, $password='', $isencrypted=0)
    {
        global $langs;

        dolibarr_syslog("Adherent::Password user=".$user->id." password=".eregi_replace('.','*',$password)." isencrypted=".$isencrypted);

        // Si nouveau mot de passe non communiqu�, on g�n�re par module
        if (! $password)
        {
        	// TODO Mettre appel au module de g�n�ration de mot de passe
        	$password=creer_pass_aleatoire_1('');
        	//$password=creer_pass_aleatoire_2('');
        }

		// Cryptage mot de passe
        if ($isencrypted)
        {
        	// Crypte avec systeme encodage par defaut du PHP
            //$sqlpass = crypt($password, makesalt());
            $password_indatabase = md5($password);
        }
        else
        {
            $password_indatabase = $password;
        }

		// Mise a jour
        $sql = "UPDATE ".MAIN_DB_PREFIX."adherent SET pass = '".addslashes($password_indatabase)."'";
        $sql.= " WHERE rowid = ".$this->id;

        $result = $this->db->query($sql);
        if ($result)
        {
            if ($this->db->affected_rows())
            {
		        $this->pass=$password;
		        $this->pass_indatabase=$password_indatabase;

                // Appel des triggers
                include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
                $interface=new Interfaces($this->db);
                $result=$interface->run_triggers('MEMBER_NEW_PASSWORD',$this,$user,$lang,$conf);
                if ($result < 0) $this->errors=$interface->errors;
                // Fin appel triggers

                return $this->pass;
            }
            else {
                return -2;
            }
        }
        else
        {
            dolibarr_print_error($this->db);
            return -1;
        }
    }
	
	
	/**
	*		\brief      Fonction qui r�cup�re l'adh�rent en donnant son login
	*		\param	    login		login de l'adh�rent
	*/
	function fetch_login($login)
	{
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."adherent WHERE login='$login' LIMIT 1";
	
		$result=$this->db->query( $sql);
	
		if ($result)
		{
			if ($this->db->num_rows())
			{
				$obj = $this->db->fetch_object($result);
				$this->fetch($obj->rowid);
			}
		}
		else
		{
			dolibarr_print_error($this->db);
		}
	}


    /**
    		\brief 		Fonction qui r�cup�re l'adh�rent en donnant son rowid
    		\param		rowid
    		\return		int			<0 si KO, >0 si OK
    */
    function fetch($rowid)
    {
        global $langs;

        $sql = "SELECT d.rowid, d.prenom, d.nom, d.societe, d.statut, d.public, d.adresse, d.cp, d.ville, d.note,";
        $sql.= " d.email, d.phone, d.phone_perso, d.phone_mobile, d.login, d.pass,";
        $sql.= " d.photo, d.fk_adherent_type, d.morphy,";
        $sql.= " ".$this->db->pdate("d.datec")." as datec,";
        $sql.= " ".$this->db->pdate("d.tms")." as datem,";
        $sql.= " ".$this->db->pdate("d.datefin")." as datefin,";
   		$sql.= " d.naiss as datenaiss,";
        $sql.= " ".$this->db->pdate("d.datevalid")." as datev,";
        $sql.= " d.pays,";
        $sql.= " p.rowid as pays_id, p.code as pays_code, p.libelle as pays_lib,";
        $sql.= " t.libelle as type, t.cotisation as cotisation,";
        $sql.= " u.rowid as user_id, u.login as user_login";
        $sql.= " FROM ".MAIN_DB_PREFIX."adherent_type as t, ".MAIN_DB_PREFIX."adherent as d";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."c_pays as p ON d.pays = p.rowid";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON d.rowid = u.fk_member";
        $sql.= " WHERE d.rowid = ".$rowid." AND d.fk_adherent_type = t.rowid";
		dolibarr_syslog("Adherent::fetch sql=".$sql);
		
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id             = $obj->rowid;
                $this->prenom         = $obj->prenom;
                $this->nom            = $obj->nom;
                $this->fullname       = trim($obj->nom.' '.$obj->prenom);
                $this->login          = $obj->login;
                $this->pass           = $obj->pass;
                $this->societe        = $obj->societe;
                $this->adresse        = $obj->adresse;
                $this->cp             = $obj->cp;
                $this->ville          = $obj->ville;
                $this->pays_id        = $obj->pays_id;
                $this->pays_code      = $obj->pays_code;
                if ($langs->trans("Country".$obj->pays_code) != "Country".$obj->pays_code) $this->pays = $langs->trans("Country".$obj->pays_code);
                elseif ($obj->pays_lib) $this->pays=$obj->pays_lib;
                else $this->pays=$obj->pays;
                $this->phone          = $obj->phone;
                $this->phone_perso    = $obj->phone_perso;
                $this->phone_mobile   = $obj->phone_mobile;
                $this->email          = $obj->email;

                $this->photo          = $obj->photo;
                $this->statut         = $obj->statut;
                $this->public         = $obj->public;

                $this->datec          = $obj->datec;
                $this->datem          = $obj->datem;
                $this->datefin        = $obj->datefin;
                $this->datevalid      = $obj->datevalid;
                $this->naiss          = $obj->datenaiss;

                $this->commentaire    = $obj->note;
                $this->morphy         = $obj->morphy;

                $this->typeid         = $obj->fk_adherent_type;
                $this->type           = $obj->type;
                $this->need_subscription = ($obj->cotisation=='yes'?1:0);
				
                $this->user_id        = $obj->user_id;
                $this->user_login     = $obj->user_login;
            }
			return 1;
        }
        else
        {
            $this->error=$this->db->error();
			return -1;
        }
    }


/**
		\brief      Fonction qui r�cup�re les donn�es optionelles de l'adh�rent
		\param	    rowid
*/

	function fetch_optionals($rowid)
  {
    $tab=array();
    $sql = "SELECT *";
    $sql .= " FROM ".MAIN_DB_PREFIX."adherent_options";
    $sql .= " WHERE adhid=".$rowid;

    $result=$this->db->query( $sql);

    if ($result)
    {
    	if ($this->db->num_rows())
    	{
    	  $tab = $this->db->fetch_array($result);

    	  foreach ($tab as $key => $value)
    	  {
    	    if ($key != 'optid' && $key != 'tms' && $key != 'adhid')
    	    {
    	      // we can add this attribute to adherent object
    	      $this->array_options["options_$key"]=$value;
    	    }
    	  }
    	}
    }
    else
    {
      dolibarr_print_error($this->db);
    }

  }

  /*
   * fetch optional attribute name
   */
  function fetch_name_optionals()
  {
    $array_name_options=array();
    $sql = "SHOW COLUMNS FROM ".MAIN_DB_PREFIX."adherent_options";

    $result=$this->db->query( $sql);

    if ($result)
    {
        if ($this->db->num_rows())
        {
            //$array_name_options[]=$tab->Field;
            while ($tab = $this->db->fetch_object($result))
            {
                if ($tab->Field != 'optid' && $tab->Field != 'tms' && $tab->Field != 'adhid')
                {
                    // we can add this attribute to adherent object
                    $array_name_options[]=$tab->Field;
                }
            }
            return $array_name_options;
        }
        else
        {
            return array();
        }
    }
    else
    {
        dolibarr_print_error($this->db);
        return array() ;
    }

  }

    /**
    		\brief      Fonction qui ins�re la cotisation dans la base de donn�es
    					et eventuellement liens dans banques, mailman, etc...
    		\param	    date        	Date d'effet de la cotisation
    		\param	    montant     	Montant cotisation (accepte 0 pour les adh�rents non soumis � cotisation)
    		\param		account_id		Id compte bancaire
    		\param		operation		Type operation (si Id compte bancaire fourni)
    		\param		label			Label operation (si Id compte bancaire fourni)
    		\param		num_chq			Numero cheque (si Id compte bancaire fourni)
    		\param		emetteur_nom	Nom emetteur ch�que
    		\param		emetteur_banque	Nom banque emetteur ch�que
            \return     int         	rowid de l'entr�e ajout�e, <0 si erreur
    */
    function cotisation($date, $montant, $accountid=0, $operation='', $label='', $num_chq='', $emetteur_nom='', $emetteur_banque='')
    {
        global $conf,$langs,$user;

		// Nettoyage parametres
		if (! $montant) $montant=0;
		
        $this->db->begin();

        $sql = "INSERT INTO ".MAIN_DB_PREFIX."cotisation (fk_adherent, datec, dateadh, cotisation)";
        $sql .= " VALUES (".$this->id.", now(), ".$this->db->idate($date).", ".$montant.")";

        dolibarr_syslog("Adherent::cotisation sql=".$sql);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $rowid=$this->db->last_insert_id(MAIN_DB_PREFIX."cotisation");
			// datefin = date + 1 an
            $datefin = dolibarr_time_plus_duree($date,1,'y');

            $sql = "UPDATE ".MAIN_DB_PREFIX."adherent SET datefin = ".$this->db->idate($datefin);
            $sql.= " WHERE rowid =". $this->id;
            
            dolibarr_syslog("Adherent::cotisation sql=".$sql);
            $resql=$this->db->query($sql);
            if ($resql)
            {
		        // Rajout du nouveau cotisant dans les listes qui vont bien
		        if ($conf->global->ADHERENT_MAILMAN_LISTS_COTISANT && ! $adh->datefin)
		        {
		            $result=$adh->add_to_mailman($conf->global->ADHERENT_MAILMAN_LISTS_COTISANT);
		        }

	            // Insertion dans la gestion bancaire si configur� pour
	            if ($conf->global->ADHERENT_BANK_USE && $accountid)
	            {
	                $acct=new Account($this->db,$accountid);

	                $dateop=time();

	                $insertid=$acct->addline($dateop, $operation, $label, $montant, $num_chq, '', $user, $emetteur_nom, $emetteur_banque);
	                if ($insertid > 0)
	                {
	        			$inserturlid=$acct->add_url_line($insertid, $this->id, DOL_URL_ROOT.'/adherents/fiche.php?rowid=', $this->getFullname(), 'member');
	                    if ($inserturlid > 0)
	                    {
	                        // Met a jour la table cotisation
	                        $sql="UPDATE ".MAIN_DB_PREFIX."cotisation SET fk_bank=".$insertid." WHERE rowid=".$rowid;
	                        $resql = $this->db->query($sql);
	                        if (! $resql)
	                        {
				                $this->error=$this->db->error();
				                $this->db->rollback();
				                return -5;
	                        }
	                    }
	                    else
	                    {
			                $this->error=$acct->error();
			                $this->db->rollback();
			                return -4;
	                    }
	                }
	                else
	                {
		                $this->error=$this->db->error();
		                $this->db->rollback();
		                return -3;
	                }
	            }

				// Ajout de propri�t�s pour le triggers
				$this->last_subscription_date=$dateop;
				$this->last_subscription_date_start=$date;
				$this->last_subscription_date_end=$datefin;
				$this->last_subscription_amount=$montant;
				$this->use_webcal=($conf->global->PHPWEBCALENDAR_MEMBERSTATUS=='always'?1:0);
				
                // Appel des triggers
                include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
                $interface=new Interfaces($this->db);
                $result=$interface->run_triggers('MEMBER_SUBSCRIPTION',$this,$user,$langs,$conf);
                if ($result < 0) $this->errors=$interface->errors;
                // Fin appel triggers

               	$this->db->commit();
               	return $rowid;
            }
            else
            {
                $this->error=$this->db->error();
                dolibarr_syslog("Adherent::cotisation error ".$this->error);
                $this->db->rollback();
                return -2;
            }
        }
        else
        {
            $this->error=$this->db->error();
            dolibarr_syslog("Adherent::cotisation error ".$this->error);
            $this->db->rollback();
            return -1;
        }
    }

	/**
	 *		\brief 		Fonction qui v�rifie que l'utilisateur est valide
	 *		\param		userid		userid adh�rent � valider
	 *		\return		int			<0 si ko, >0 si ok
	 */
	function validate($user)
	{
		global $user,$langs,$conf;

		$sql = "UPDATE ".MAIN_DB_PREFIX."adherent SET";
		$sql.= " statut=1, datevalid = now(),";
		$sql.= " fk_user_valid=".$user->id;
		$sql.= " WHERE rowid = ".$this->id;

		dolibarr_syslog("Adherent::validate sql=".$sql);
		$result = $this->db->query($sql);
		if ($result)
		{
			$this->use_webcal=($conf->global->PHPWEBCALENDAR_MEMBERSTATUS=='always'?1:0);

			// Appel des triggers
			include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
			$interface=new Interfaces($this->db);
			$result=$interface->run_triggers('MEMBER_VALIDATE',$this,$user,$langs,$conf);
            if ($result < 0) $this->errors=$interface->errors;
			// Fin appel triggers

			$this->db->commit();
			return 1;
		}
		else
		{
			$this->error=$this->db->error();
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *		\brief 		Fonction qui r�silie un adh�rent
	 *		\param		userid		userid adh�rent � r�silier
	 *		\return		int			<0 si ko, >0 si ok
	 */
	function resiliate($userid)
	{
		global $user,$langs,$conf;

		$this->db->begin();

		$sql = "UPDATE ".MAIN_DB_PREFIX."adherent SET ";
		$sql .= "statut=0";
		$sql .= ",fk_user_valid=".$userid;
		$sql .= " WHERE rowid = ".$this->id;

		$result = $this->db->query($sql);
		if ($result)
		{
			$this->use_webcal=($conf->global->PHPWEBCALENDAR_MEMBERSTATUS=='always'?1:0);

	        // Appel des triggers
	        include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
	        $interface=new Interfaces($this->db);
	        $result=$interface->run_triggers('MEMBER_RESILIATE',$this,$user,$langs,$conf);
            if ($result < 0) $this->errors=$interface->errors;
	        // Fin appel triggers

			$this->db->commit();
			return 1;
		}
		else
		{
			$this->error=$this->db->error();
			$this->db->rollback();
			return -1;
		}
	}


	/**
			\brief 		Fonction qui ajoute l'adh�rent au abonnements automatiques
			\param		adht
			\remarks	mailing-list, spip, glasnost, etc...
			\return		int		<0 si KO, >=0 si OK
	*/
	function add_to_abo($adht)
	{
		$err=0;

		// mailman
		if (defined("ADHERENT_USE_MAILMAN") && ADHERENT_USE_MAILMAN == 1)
		{
			$result=$this->add_to_mailman();
			if ($result < 0)
			{
				$err+=1;
			}
		}
	
		// glasnost
		if ($adht->vote == 'yes' &&
		defined("ADHERENT_USE_GLASNOST") && ADHERENT_USE_GLASNOST ==1 &&
		defined("ADHERENT_USE_GLASNOST_AUTO") && ADHERENT_USE_GLASNOST_AUTO ==1
		)
		{
			$result=$this->add_to_glasnost();
			if(! $result)
			{
				$err+=1;
			}
		}

		// spip
		if (
		defined("ADHERENT_USE_SPIP") && ADHERENT_USE_SPIP ==1 &&
		defined("ADHERENT_USE_SPIP_AUTO") && ADHERENT_USE_SPIP_AUTO ==1
		)
		{
			$result=$this->add_to_spip();
			if(!$result)
			{
				$err+=1;
			}
		}
		if ($err)
		{
			// error
			return -$err;
		}
		else
		{
			return 1;
		}
	}


	/**
			\brief      fonction qui supprime l'adh�rent des abonnements automatiques
			\param	    adht
			\remarks	mailing-list, spip, glasnost, etc...
	*/
	function del_to_abo($adht)
	{
		$err=0;
		// mailman
		if (defined("ADHERENT_USE_MAILMAN") && ADHERENT_USE_MAILMAN == 1)
		{
			if(!$this->del_to_mailman()){
				$err+=1;
			}
		}
		if ($adht->vote == 'yes' &&
		defined("ADHERENT_USE_GLASNOST") && ADHERENT_USE_GLASNOST ==1 &&
		defined("ADHERENT_USE_GLASNOST_AUTO") && ADHERENT_USE_GLASNOST_AUTO ==1
		)
		{
			if(!$this->del_to_glasnost()){
				$err+=1;
			}
		}
		if (
		defined("ADHERENT_USE_SPIP") && ADHERENT_USE_SPIP ==1 &&
		defined("ADHERENT_USE_SPIP_AUTO") && ADHERENT_USE_SPIP_AUTO ==1
		)
		{
			if(!$this->del_to_spip()){
				$err+=1;
			}
		}
		if ($err>0){
			// error
			return 0;
			}else{
				return 1;
			}
		}


	/**
			\brief fonction qui donne les droits r�dacteurs dans spip
			\return		int		=0 si KO, >0 si OK
	*/
	function add_to_spip()
	{
		dolibarr_syslog("Adherent::add_to_spip");

		if (defined("ADHERENT_USE_SPIP") && ADHERENT_USE_SPIP ==1 &&
				defined('ADHERENT_SPIP_SERVEUR') && ADHERENT_SPIP_SERVEUR != '' &&
				defined('ADHERENT_SPIP_USER') && ADHERENT_SPIP_USER != '' &&
				defined('ADHERENT_SPIP_PASS') && ADHERENT_SPIP_PASS != '' &&
				defined('ADHERENT_SPIP_DB') && ADHERENT_SPIP_DB != ''
				){
			$mdpass=md5($this->pass);
			$htpass=crypt($this->pass,makesalt());
			$query = "INSERT INTO spip_auteurs (nom, email, login, pass, htpass, alea_futur, statut) VALUES(\"".$this->prenom." ".$this->nom."\",\"".$this->email."\",\"".$this->login."\",\"$mdpass\",\"$htpass\",FLOOR(32000*RAND()),\"1comite\")";
			//      $mydb=new Db('mysql',ADHERENT_SPIP_SERVEUR,ADHERENT_SPIP_USER,ADHERENT_SPIP_PASS,ADHERENT_SPIP_DB);
			$mydb=new DoliDb('mysql',ADHERENT_SPIP_SERVEUR,ADHERENT_SPIP_USER,ADHERENT_SPIP_PASS,ADHERENT_SPIP_DB);
			$result = $mydb->query($query);

			if ($result)
			{
				$mydb->close();
				return 1;
			}
			else
			{
				$this->error=$mydb->error();
				return 0;
			}
		}
	}

	/**
			\brief fonction qui enl�ve les droits r�dacteurs dans spip
	*/
	function del_to_spip()
    {
      if (defined("ADHERENT_USE_SPIP") && ADHERENT_USE_SPIP ==1 &&
	  defined('ADHERENT_SPIP_SERVEUR') && ADHERENT_SPIP_SERVEUR != '' &&
	  defined('ADHERENT_SPIP_USER') && ADHERENT_SPIP_USER != '' &&
	  defined('ADHERENT_SPIP_PASS') && ADHERENT_SPIP_PASS != '' &&
	  defined('ADHERENT_SPIP_DB') && ADHERENT_SPIP_DB != ''
	  ){
	$query = "DELETE FROM spip_auteurs WHERE login='".$this->login."'";
	$mydb=new DoliDb('mysql',ADHERENT_SPIP_SERVEUR,ADHERENT_SPIP_USER,ADHERENT_SPIP_PASS,ADHERENT_SPIP_DB);
	$result = $mydb->query($query);

	if ($result)
	  {
	    $mydb->close();
	    return 1;
	  }
	else
	  {
	    $this->error=$mydb->error();
	    return 0;
	  }
      }
    }

/**
		\brief      Fonction qui dit si cet utilisateur est un r�dacteur existant dans spip
		\return     int     1=existe, 0=n'existe pas, -1=erreur
*/

	function is_in_spip()
    {
        if (defined("ADHERENT_USE_SPIP") && ADHERENT_USE_SPIP ==1 &&
            defined('ADHERENT_SPIP_SERVEUR') && ADHERENT_SPIP_SERVEUR != '' &&
            defined('ADHERENT_SPIP_USER') && ADHERENT_SPIP_USER != '' &&
            defined('ADHERENT_SPIP_PASS') && ADHERENT_SPIP_PASS != '' &&
            defined('ADHERENT_SPIP_DB') && ADHERENT_SPIP_DB != '')
        {

            $query = "SELECT login FROM spip_auteurs WHERE login='".$this->login."'";
            $mydb=new DoliDb('mysql',ADHERENT_SPIP_SERVEUR,ADHERENT_SPIP_USER,ADHERENT_SPIP_PASS,ADHERENT_SPIP_DB);

            if ($mydb->ok) {

                $result = $mydb->query($query);

                if ($result)
                {
                    if ($mydb->num_rows())
                    {
                        # nous avons au moins une reponse
                        $mydb->close();
                        return 1;
                    }
                    else
                    {
                    # nous n'avons pas de reponse => n'existe pas
                    $mydb->close();
                    return 0;
                    }
                }
                else
                {
                    # error
                    $this->error=$mydb->error();
                    return -1;
                }
            } else {
                $this->error="Echec de connexion avec les identifiants ".ADHERENT_SPIP_SERVEUR." ".ADHERENT_SPIP_USER." ".ADHERENT_SPIP_PASS." ".ADHERENT_SPIP_DB;
                return -1;
            }
        }
    }


	/**
		\brief      Fonction qui ajoute l'utilisateur dans glasnost
		\return		int		=0 si KO, >0 si OK
	*/
	function add_to_glasnost()
	{
		global $conf,$langs;

		dolibarr_syslog("Adherent::add_to_glasnost");
		
		if (defined("ADHERENT_USE_GLASNOST") && ADHERENT_USE_GLASNOST ==1 &&
				defined('ADHERENT_GLASNOST_SERVEUR') && ADHERENT_GLASNOST_SERVEUR != '' &&
				defined('ADHERENT_GLASNOST_USER') && ADHERENT_GLASNOST_USER != '' &&
				defined('ADHERENT_GLASNOST_PASS') && ADHERENT_GLASNOST_PASS != ''
				){
			// application token is not useful here
			$applicationtoken='';
			list($success, $response) =
			XMLRPC_request(ADHERENT_GLASNOST_SERVEUR.':8001',
			'/RPC2',
			'callGateway',
			array(XMLRPC_prepare("glasnost://".ADHERENT_GLASNOST_SERVEUR."/authentication"),
			XMLRPC_prepare('getUserIdAndToken'),
			XMLRPC_prepare(array("glasnost://".ADHERENT_GLASNOST_SERVEUR."/authentication","$applicationtoken",ADHERENT_GLASNOST_USER,ADHERENT_GLASNOST_PASS))
			)
			);
			if ($success){
				$userid=$response[0];
				$usertoken=$response[1];
			}else{
				$this->error=$response['faultString'];
				return 0;
			}

			list($success,$response)=
			XMLRPC_request(ADHERENT_GLASNOST_SERVEUR.':8001',
			'/RPC2',
			'callGateway',
			array(XMLRPC_prepare("glasnost://".ADHERENT_GLASNOST_SERVEUR."/people"),
			XMLRPC_prepare('addObject'),
			XMLRPC_prepare(array(
			"glasnost://".ADHERENT_GLASNOST_SERVEUR."/people",
			"$applicationtoken",
			$usertoken,
			array(
			'__thingCategory__'=>'object',
			'__thingName__'=>  'Person',
			'firstName'=>$this->prenom,
			'lastName'=>$this->nom,
			'login'=>$this->login,
			'email'=>$this->email
			)
			)
			)
			)
			);
			if ($success){
				$personid=$response[0];
			}else{
				$this->error=$response['faultString'];
				return 0;
			}
			return 1;
		}else{
			$this->error="Constantes de connection non definies";
			return 0;
		}
	}

	/**
			\brief fonction qui enl�ve l'utilisateur de glasnost
	*/
	function del_to_glasnost()
    {
      if (defined("ADHERENT_USE_GLASNOST") && ADHERENT_USE_GLASNOST ==1 &&
	  defined('ADHERENT_GLASNOST_SERVEUR') && ADHERENT_GLASNOST_SERVEUR != '' &&
	  defined('ADHERENT_GLASNOST_USER') && ADHERENT_GLASNOST_USER != '' &&
	  defined('ADHERENT_GLASNOST_PASS') && ADHERENT_GLASNOST_PASS != ''
	  ){
	// application token is not useful here
	$applicationtoken='';
	list($success, $response) =
	  XMLRPC_request(ADHERENT_GLASNOST_SERVEUR.':8001',
			 '/RPC2',
			 'callGateway',
			 array(XMLRPC_prepare("glasnost://".ADHERENT_GLASNOST_SERVEUR."/authentication"),
			       XMLRPC_prepare('getUserIdAndToken'),
			       XMLRPC_prepare(array("glasnost://".ADHERENT_GLASNOST_SERVEUR."/authentication","$applicationtoken",ADHERENT_GLASNOST_USER,ADHERENT_GLASNOST_PASS))
			       )
			 );
	if ($success){
	  $userid=$response[0];
	  $usertoken=$response[1];
	}else{
	  return 0;
	}
	// recuperation du personID
	list($success,$response)=
	  XMLRPC_request(ADHERENT_GLASNOST_SERVEUR.':8001',
			 '/RPC2',
			 'callGateway',
			 array(XMLRPC_prepare("glasnost://".ADHERENT_GLASNOST_SERVEUR."/people"),
			       XMLRPC_prepare('getObjectByLogin'),
			       XMLRPC_prepare(array(
						    "glasnost://".ADHERENT_GLASNOST_SERVEUR."/people",
						    "$applicationtoken",
						    $usertoken,
						    $this->login
						    )
					      )
			       )
			 );
	if ($success){
	  $personid=$response['id'];
	}else{
	  $this->error=$response['faultString'];
	  return 0;
	}
	if (defined('ADHERENT_GLASNOST_DEFAULT_GROUPID') && ADHERENT_GLASNOST_DEFAULT_GROUPID != ''){
	  // recuperation des personne de ce groupe
	  list($success,$response)=
	    XMLRPC_request(ADHERENT_GLASNOST_SERVEUR.':8001',
			 '/RPC2',
			   'callGateway',
			   array(XMLRPC_prepare("glasnost://".ADHERENT_GLASNOST_SERVEUR."/groups"),
				 XMLRPC_prepare('getObject'),
				 XMLRPC_prepare(array(
						      "glasnost://".ADHERENT_GLASNOST_SERVEUR."/groups",
						      "$applicationtoken",
						      $usertoken,
						      ADHERENT_GLASNOST_DEFAULT_GROUPID
						      )
						)
				 )
			   );
	  if ($success){
	    $groupids=$response['membersSet'];
	  }else{
	    $this->error=$response['faultString'];
	    return 0;
	  }
	  // TODO faire la verification que le user n'est pas dans ce
	  // groupe par defaut. si il y ai il faut l'effacer et
	  // modifier le groupe
	}
	// suppression du personID
	list($success,$response)=
	  XMLRPC_request(ADHERENT_GLASNOST_SERVEUR.':8001',
			 '/RPC2',
			 'callGateway',
			 array(XMLRPC_prepare("glasnost://".ADHERENT_GLASNOST_SERVEUR."/people"),
			       XMLRPC_prepare('deleteObject'),
			       XMLRPC_prepare(array(
						    "glasnost://".ADHERENT_GLASNOST_SERVEUR."/people",
						    "$applicationtoken",
						    $usertoken,
						    $personid
						    )
					      )
			       )
			 );
	if ($success){
	  return 1;
	}else{
	  $this->error=$response['faultString'];
	  return 0;
	}
      }else{
	$this->error="Constantes de connection non definies";
	return 0;
      }
    }

/**
		\brief fonction qui v�rifie si l'utilisateur est dans glasnost
*/

  function is_in_glasnost()
    {
      if (defined("ADHERENT_USE_GLASNOST") && ADHERENT_USE_GLASNOST ==1 &&
	  defined('ADHERENT_GLASNOST_SERVEUR') && ADHERENT_GLASNOST_SERVEUR != '' &&
	  defined('ADHERENT_GLASNOST_USER') && ADHERENT_GLASNOST_USER != '' &&
	  defined('ADHERENT_GLASNOST_PASS') && ADHERENT_GLASNOST_PASS != ''
	  ){
	// application token is not useful here
	$applicationtoken='';
	list($success, $response) =
	  XMLRPC_request(ADHERENT_GLASNOST_SERVEUR.':8001',
			 '/RPC2',
			 'callGateway',
			 array(XMLRPC_prepare("glasnost://".ADHERENT_GLASNOST_SERVEUR."/authentication"),
			       XMLRPC_prepare('getUserIdAndToken'),
			       XMLRPC_prepare(array("glasnost://".ADHERENT_GLASNOST_SERVEUR."/authentication","$applicationtoken",ADHERENT_GLASNOST_USER,ADHERENT_GLASNOST_PASS))
			       )
			 );
	if ($success){
	  $userid=$response[0];
	  $usertoken=$response[1];
	}else{
	  return 0;
	}
	// recuperation du personID
	list($success,$response)=
	  XMLRPC_request(ADHERENT_GLASNOST_SERVEUR.':8001',
			 '/RPC2',
			 'callGateway',
			 array(XMLRPC_prepare("glasnost://".ADHERENT_GLASNOST_SERVEUR."/people"),
			       XMLRPC_prepare('getObjectByLogin'),
			       XMLRPC_prepare(array(
						    "glasnost://".ADHERENT_GLASNOST_SERVEUR."/people",
						    "$applicationtoken",
						    $usertoken,
						    $this->login
						    )
					      )
			       )
			 );
	if ($success){
	  $personid=$response['id'];
	  return 1;
	}else{
	  $this->error=$response['faultString'];
	  return 0;
	}
      }else{
	$this->error="Constantes de connection non definies";
	return 0;
      }
    }


	/**
			\brief 		Fonction qui rajoute l'utilisateur dans mailman
			\return		int		<0 si KO, >0 si OK
	*/
	function add_to_mailman($listes='')
	{
		global $conf,$langs;
		
		dolibarr_syslog("Adherent::add_to_mailman");

		if (! function_exists("curl_init"))
		{
			$this->error=$langs->trans("ErrorFunctionNotAvailableInPHP","curl_init");
			return -1;	
		}
		
		if (defined("ADHERENT_MAILMAN_URL") && ADHERENT_MAILMAN_URL != '' && defined("ADHERENT_MAILMAN_LISTS") && ADHERENT_MAILMAN_LISTS != '')
		{
			if ($listes =='')
			{
				$lists=explode(',',ADHERENT_MAILMAN_LISTS);
			}
			else
			{
					$lists=explode(',',$listes);
			}
			foreach ($lists as $list)
			{
				// on remplace dans l'url le nom de la liste ainsi
				// que l'email et le mot de passe
				$patterns = array (
				'/%LISTE%/',
				'/%EMAIL%/',
				'/%PASSWORD%/',
				'/%MAILMAN_ADMINPW%/'
				);
				$replace = array (
				$list,
				$this->email,
				$this->pass,
				$conf->global->ADHERENT_MAILMAN_ADMINPW
				);
				$curl_url = preg_replace ($patterns, $replace, $conf->global->ADHERENT_MAILMAN_URL);

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,"$curl_url");
				//curl_setopt($ch, CURLOPT_URL,"http://www.j1b.org/");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch, CURLOPT_FAILONERROR, 1);
				@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, 5);
				//curl_setopt($ch, CURLOPT_POST, 0);
				//curl_setopt($ch, CURLOPT_POSTFIELDS, "a=3&b=5");
				//--- Start buffering
				//ob_start();
				$result=curl_exec ($ch);
				dolibarr_syslog($result);
				//--- End buffering and clean output
				//ob_end_clean();
				if (curl_error($ch) > 0)
				{
					// error
					return 0;
				}
				curl_close ($ch);

			}
			return 1;
		}
		else
		{
			$this->error="Constantes de connection non definies";
			return -1;
		}
	}

	/**
		\brief 		Fonction qui d�sinscrit l'utilisateur de toutes les mailing list mailman
		\remarks	Utilise lors de la r�siliation d'adh�sion
	*/
	function del_to_mailman($listes='')
	{
		global $conf;
		
		if (defined("ADHERENT_MAILMAN_UNSUB_URL") && ADHERENT_MAILMAN_UNSUB_URL != '' && defined("ADHERENT_MAILMAN_LISTS") && ADHERENT_MAILMAN_LISTS != '')
		{
			if ($listes==''){
				$lists=explode(',',ADHERENT_MAILMAN_LISTS);
				if (defined("ADHERENT_MAILMAN_LISTS_COTISANT") && ADHERENT_MAILMAN_LISTS_COTISANT !=''){
					$lists=array_merge ($lists,explode(',',ADHERENT_MAILMAN_LISTS_COTISANT));
				}
			}else{
				$lists=explode(',',$listes);
			}
			foreach ($lists as $list)
			{
				// on remplace dans l'url le nom de la liste ainsi
				// que l'email et le mot de passe
				$patterns = array (
				'/%LISTE%/',
				'/%EMAIL%/',
				'/%PASSWORD%/',
				'/%MAILMAN_ADMINPW%/'
				);
				$replace = array (
				$list,
				$this->email,
				$this->pass,
				$conf->global->ADHERENT_MAILMAN_ADMINPW
				);
				$curl_url = preg_replace ($patterns, $replace, $conf->global->ADHERENT_MAILMAN_UNSUB_URL);

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,"$curl_url");
				//curl_setopt($ch, CURLOPT_URL,"http://www.j1b.org/");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch, CURLOPT_FAILONERROR, 1);
				@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, 5);
				//curl_setopt($ch, CURLOPT_POST, 0);
				//curl_setopt($ch, CURLOPT_POSTFIELDS, "a=3&b=5");
				//--- Start buffering
				//ob_start();
				$result=curl_exec ($ch);
				dolibarr_syslog($result);
				//--- End buffering and clean output
				//ob_end_clean();
				if (curl_error($ch) > 0)
				{
					// error
					return 0;
				}
				curl_close ($ch);

			}
			return 1;
		}
		else
		{
			$this->error="Constantes de connection non definies";
			return 0;
		}
	}

	/**
	 *    \brief      Retourne le nom complet de l'adh�rent
	 *    \return     string      	Nom complet
	 */
    function getFullname()
    {
        if ($this->nom && $this->prenom) return $this->nom.' '.$this->prenom;
        if ($this->nom)    return $this->nom;
        if ($this->prenom) return $this->prenom;
        return '';
    }


	/**
	 *    	\brief      Renvoie nom clicable (avec eventuellement le picto)
	 *		\param		withpicto		0=Pas de picto, 1=Inclut le picto dans le lien, 2=Picto seul
	 *		\param		maxlen			Longueur max libelle
	 *		\return		string			Chaine avec URL
	 */
	function getNomUrl($withpicto=0,$maxlen=0)
	{
		global $langs;
		
		$result='';

		$lien = '<a href="'.DOL_URL_ROOT.'/adherents/fiche.php?rowid='.$this->id.'">';
		$lienfin='</a>';
		
		$picto='user';
		$label=$langs->trans("ShowMember");
		
		if ($withpicto) $result.=($lien.img_object($label,$picto).$lienfin);
		if ($withpicto && $withpicto != 2) $result.=' ';
		$result.=$lien.($maxlen?dolibarr_trunc($this->ref,$maxlen):$this->ref).$lienfin;
		return $result;
	}


	/**
	 *    	\brief      Retourne le libell� du statut d'un adh�rent (brouillon, valid�, r�sili�)
	 *    	\param      mode        0=libell� long, 1=libell� court, 2=Picto + Libell� court, 3=Picto, 4=Picto + Libell� long, 5=Libell� court + Picto
	 *    	\return     string		Libell�
	 */
    function getLibStatut($mode=0)
    {
		return $this->LibStatut($this->statut,$this->need_subscription,$this->datefin,$mode);
    }

	/**
	*    	\brief      Renvoi le libell� d'un statut donn�
 	*    	\param      statut      			Id statut
	*		\param		need_subscription		1 si type adherent avec cotisation, 0 sinon
	*		\param		date_end_subscription	Date fin adh�sion
	*    	\param      mode        			0=libell� long, 1=libell� court, 2=Picto + Libell� court, 3=Picto, 4=Picto + Libell� long, 5=Libell� court + Picto
 	*    	\return     string      			Libell�
 	*/
    function LibStatut($statut,$need_subscription,$date_end_subscription,$mode=0)
    {
        global $langs;
        $langs->load("members");
		if ($mode == 0)
		{
	        if ($statut == -1) return $langs->trans("MemberStatusDraft");
	        if ($statut == 1)
	        {
	        	if (! $date_end_subscription)            return $langs->trans("MemberStatusActive");
	        	elseif ($date_end_subscription < time()) return $langs->trans("MemberStatusActiveLate");
	        	else                                     return $langs->trans("MemberStatusPayed");
	        }
	        if ($statut == 0)  return $langs->trans("MemberStatusResiliated");
		}
		if ($mode == 1)
		{
	        if ($statut == -1) return $langs->trans("MemberStatusDraft");
	        if ($statut == 1)
	        {
	        	if (! $date_end_subscription)            return $langs->trans("MemberStatusActiveShort");
	        	elseif ($date_end_subscription < time()) return $langs->trans("MemberStatusActiveLateShort");
	        	else                                     return $langs->trans("MemberStatusPayedShort");
	        }
	        if ($statut == 0)  return $langs->trans("MemberStatusResiliated");
		}
		if ($mode == 2)
		{
	        if ($statut == -1) return img_picto($langs->trans('MemberStatusDraft'),'statut0').' '.$langs->trans("MemberStatusDraft");
	        if ($statut == 1)
	        {
	        	if (! $date_end_subscription)            return img_picto($langs->trans('MemberStatusActive'),'statut1').' '.$langs->trans("MemberStatusActiveShort");
	        	elseif ($date_end_subscription < time()) return img_picto($langs->trans('MemberStatusActiveLate'),'statut3').' '.$langs->trans("MemberStatusActiveLateShort");
	        	else                                     return img_picto($langs->trans('MemberStatusPayed'),'statut4').' '.$langs->trans("MemberStatusPayedShort");
	        }
	        if ($statut == 0)  return img_picto($langs->trans('MemberStatusResiliated'),'statut5').' '.$langs->trans("MemberStatusResiliated");
		}
		if ($mode == 3)
		{
	        if ($statut == -1) return img_picto($langs->trans('MemberStatusDraft'),'statut0');
	        if ($statut == 1)
	        {
	        	if (! $date_end_subscription)            return img_picto($langs->trans('MemberStatusActive'),'statut1');
	        	elseif ($date_end_subscription < time()) return img_picto($langs->trans('MemberStatusActiveLate'),'statut3');
	        	else                                     return img_picto($langs->trans('MemberStatusPayed'),'statut4');
	        }
	        if ($statut == 0)  return img_picto($langs->trans('MemberStatusResiliated'),'statut5');
		}
		if ($mode == 4)
		{
	        if ($statut == -1) return img_picto($langs->trans('MemberStatusDraft'),'statut0').' '.$langs->trans("MemberStatusDraft");
	        if ($statut == 1)
	        {
	        	if (! $date_end_subscription)            return img_picto($langs->trans('MemberStatusActive'),'statut1').' '.$langs->trans("MemberStatusActive");
	        	elseif ($date_end_subscription < time()) return img_picto($langs->trans('MemberStatusActiveLate'),'statut3').' '.$langs->trans("MemberStatusActiveLate");
	        	else                                     return img_picto($langs->trans('MemberStatusPayed'),'statut4').' '.$langs->trans("MemberStatusPayed");
	        }
	        if ($statut == 0)  return img_picto($langs->trans('MemberStatusResiliated'),'statut5').' '.$langs->trans("MemberStatusResiliated");
		}
        if ($mode == 5)
        {
	        if ($statut == -1) return $langs->trans("MemberStatusDraft").' '.img_picto($langs->trans('MemberStatusDraft'),'statut0');
	        if ($statut == 1)
	        {
	        	if (! $date_end_subscription)            return $langs->trans("MemberStatusActive").' '.img_picto($langs->trans('MemberStatusActive'),'statut1');
	        	elseif ($date_end_subscription < time()) return $langs->trans("MemberStatusActiveLate").' '.img_picto($langs->trans('MemberStatusActiveLate'),'statut3');
	        	else                                     return $langs->trans("MemberStatusPayed").' '.img_picto($langs->trans('MemberStatusPayed'),'statut4');
	        }
	        if ($statut == 0)  return $langs->trans("MemberStatusResiliated").' '.img_picto($langs->trans('MemberStatusResiliated'),'statut5');
		}
    }


    /**
     *      \brief      Charge indicateurs this->nb de tableau de bord
     *      \return     int         <0 si ko, >0 si ok
     */
    function load_state_board()
    {
        global $conf;

        $this->nb=array();

        $sql = "SELECT count(a.rowid) as nb";
        $sql.= " FROM ".MAIN_DB_PREFIX."adherent as a";
        $sql.= " WHERE a.statut > 0";
        $resql=$this->db->query($sql);
        if ($resql)
        {
            while ($obj=$this->db->fetch_object($resql))
            {
                $this->nb["members"]=$obj->nb;
            }
            return 1;
        }
        else
        {
            dolibarr_print_error($this->db);
            $this->error=$this->db->error();
            return -1;
        }

    }

    /**
     *      \brief      Charge indicateurs this->nbtodo et this->nbtodolate de tableau de bord
     *      \param      user        Objet user
     *      \return     int         <0 si ko, >0 si ok
     */
    function load_board($user)
    {
        global $conf;

        if ($user->societe_id) return -1;   // protection pour eviter appel par utilisateur externe

        $this->nbtodo=$this->nbtodolate=0;
        $sql = "SELECT a.rowid,".$this->db->pdate("a.datefin")." as datefin";
        $sql.= " FROM ".MAIN_DB_PREFIX."adherent as a";
        $sql.= " WHERE a.statut=1";
        $resql=$this->db->query($sql);
        if ($resql)
        {
            while ($obj=$this->db->fetch_object($resql))
            {
                $this->nbtodo++;
                if ($obj->datefin < (time() - $conf->adherent->cotisation->warning_delay)) $this->nbtodolate++;
            }
            return 1;
        }
        else
        {
            dolibarr_print_error($this->db);
            $this->error=$this->db->error();
            return -1;
        }
    }

	/**
	*      \brief      Charge les propri�t�s id_previous et id_next
	*      \param      filter      filtre
	*      \return     int         <0 si ko, >0 si ok
	*/
	function load_previous_next_id($filter='')
	{
		$sql = "SELECT MAX(rowid)";
		$sql.= " FROM ".MAIN_DB_PREFIX."adherent";
		$sql.= " WHERE rowid < '".addslashes($this->id)."'";
		if (isset($filter)) $sql.=" AND ".$filter;
		$result = $this->db->query($sql) ;
		if (! $result)
		{
			$this->error=$this->db->error();
			return -1;
		}
		$row = $this->db->fetch_row($result);
		$this->id_previous = $row[0];
		
		$sql = "SELECT MIN(rowid)";
		$sql.= " FROM ".MAIN_DB_PREFIX."adherent";
		$sql.= " WHERE rowid > '".addslashes($this->id)."'";
		if (isset($filter)) $sql.=" AND ".$filter;
		$result = $this->db->query($sql) ;
		if (! $result)
		{
			$this->error=$this->db->error();
			return -2;
		}
		$row = $this->db->fetch_row($result);
		$this->id_next = $row[0];
	}

	
	/**
	 *		\brief		Initialise le membre avec valeurs fictives al�atoire
	 */
	function initAsSpecimen()
	{
		global $user,$langs;

		// Initialise param�tres
		$this->id=0;
		$this->specimen=1;
		$this->nom = 'DOLIBARR';
		$this->prenom = 'SPECIMEN';
		$this->fullname=trim($this->nom.' '.$this->prenom);
		$this->login='dolibspec';
		$this->pass='dolibspec';
		$this->societe = 'Societe ABC';
		$this->adresse = '61 jump street';
		$this->cp = '75000';
		$this->ville = 'Paris';
		$this->pays_id = 1;
		$this->pays_code = 'FR';
		$this->pays = 'France';
		$this->morphy = 1;
		$this->email = 'specimen@specimen.com';
		$this->phone        = '0999999999';
		$this->phone_perso  = '0999999998';
		$this->phone_mobile = '0999999997';
		$this->commentaire='No comment';
		$this->naiss=time();
		$this->photo='';
		$this->public=1;
		$this->statut=1;

		$this->typeid=1;				// Id type adherent
		$this->type='Type adherent';	// Libell� type adherent
		$this->need_subscription=0;
	}
	
	
	/*
	*	\brief		Retourne chaine DN complete dans l'annuaire LDAP pour l'objet
	*	\param		info		Info string loaded by _load_ldap_info
	*	\param		mode		0=Return DN without key inside (ou=xxx,dc=aaa,dc=bbb)
								1=Return full DN (uid=qqq,ou=xxx,dc=aaa,dc=bbb)
								2=Return key only (uid=qqq)
	*	\return		string		DN
	*/
	function _load_ldap_dn($info,$mode=0)
	{
		global $conf;
		$dn='';
		if ($mode==0) $dn=$conf->global->LDAP_KEY_MEMBERS."=".$info[$conf->global->LDAP_KEY_MEMBERS].",".$conf->global->LDAP_MEMBER_DN;
		if ($mode==1) $dn=$conf->global->LDAP_MEMBER_DN;
		if ($mode==2) $dn=$conf->global->LDAP_KEY_MEMBERS."=".$info[$conf->global->LDAP_KEY_MEMBERS];
		return $dn;
	}


	/*
	*	\brief		Initialise tableau info (tableau des attributs LDAP)
	*	\return		array		Tableau info des attributs
	*/
	function _load_ldap_info()
	{
		global $conf,$langs;

		$info=array();

		// Object classes
		$info["objectclass"]=split(',',$conf->global->LDAP_MEMBER_OBJECT_CLASS);
		
		// Champs
		if ($this->fullname  && $conf->global->LDAP_FIELD_FULLNAME) $info[$conf->global->LDAP_FIELD_FULLNAME] = $this->fullname;
		if ($this->nom && $conf->global->LDAP_FIELD_NAME)         $info[$conf->global->LDAP_FIELD_NAME] = $this->nom;
		if ($this->prenom && $conf->global->LDAP_FIELD_FIRSTNAME) $info[$conf->global->LDAP_FIELD_FIRSTNAME] = $this->prenom;
		if ($this->login && $conf->global->LDAP_FIELD_LOGIN)      $info[$conf->global->LDAP_FIELD_LOGIN] = $this->login;
		if ($this->pass && $conf->global->LDAP_FIELD_PASSWORD)    $info[$conf->global->LDAP_FIELD_PASSWORD] = $this->pass;	// this->pass = mot de passe non crypt�
		if ($this->poste && $conf->global->LDAP_FIELD_TITLE)      $info[$conf->global->LDAP_FIELD_TITLE] = $this->poste;
		if ($this->address && $conf->global->LDAP_FIELD_ADDRESS)  $info[$conf->global->LDAP_FIELD_ADDRESS] = $this->address;
		if ($this->cp && $conf->global->LDAP_FIELD_ZIP)           $info[$conf->global->LDAP_FIELD_ZIP] = $this->cp;
		if ($this->ville && $conf->global->LDAP_FIELD_TOWN)       $info[$conf->global->LDAP_FIELD_TOWN] = $this->ville;
		if ($this->pays && $conf->global->LDAP_FIELD_COUNTRY)     $info[$conf->global->LDAP_FIELD_COUNTRY] = $this->pays;
		if ($this->email && $conf->global->LDAP_FIELD_MAIL)       $info[$conf->global->LDAP_FIELD_MAIL] = $this->email;
		if ($this->phone && $conf->global->LDAP_FIELD_PHONE)      $info[$conf->global->LDAP_FIELD_PHONE] = $this->phone;
		if ($this->phone_perso && $conf->global->LDAP_FIELD_PHONE_PERSO) $info[$conf->global->LDAP_FIELD_PHONE_PERSO] = $this->phone_perso;
		if ($this->phone_mobile && $conf->global->LDAP_FIELD_MOBILE) $info[$conf->global->LDAP_FIELD_MOBILE] = $this->phone_mobile;
		if ($this->fax && $conf->global->LDAP_FIELD_FAX)	      $info[$conf->global->LDAP_FIELD_FAX] = $this->fax;
		if ($this->commentaire && $conf->global->LDAP_FIELD_DESCRIPTION) $info[$conf->global->LDAP_FIELD_DESCRIPTION] = $this->commentaire;
		if ($this->naiss && $conf->global->LDAP_FIELD_BIRTHDATE)  $info[$conf->global->LDAP_FIELD_BIRTHDATE] = dolibarr_print_date($this->naiss,'%Y%m%d%H%M%SZ');

		return $info;
	}	


    /**
     *      \brief     Charge les informations d'ordre info dans l'objet adherent
     *      \param     id       Id du membre a charger
     */
	function info($id)
	{
		$sql = 'SELECT a.rowid, '.$this->db->pdate('a.datec').' as datec,';
		$sql.= ' '.$this->db->pdate('a.datevalid').' as datev,';
		$sql.= ' '.$this->db->pdate('a.tms').' as datem,';
		$sql.= ' a.fk_user_author, a.fk_user_valid, a.fk_user_mod';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'adherent as a';
		$sql.= ' WHERE a.rowid = '.$id;
		$result=$this->db->query($sql);
		if ($result)
		{
			if ($this->db->num_rows($result))
			{
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if ($obj->fk_user_author)
				{
					$cuser = new User($this->db, $obj->fk_user_author);
					$cuser->fetch();
					$this->user_creation   = $cuser;
				}

				if ($obj->fk_user_valid)
				{
					$vuser = new User($this->db, $obj->fk_user_valid);
					$vuser->fetch();
					$this->user_validation = $vuser;
				}

				if ($obj->fk_user_mod)
				{
					$muser = new User($this->db, $obj->fk_user_mod);
					$muser->fetch();
					$this->user_modification = $mluser;
				}

				$this->date_creation     = $obj->datec;
				$this->date_validation   = $obj->datev;
				$this->date_modification = $obj->datem;
			}

			$this->db->free($result);

		}
		else
		{
			dolibarr_print_error($this->db);
		}
	}

}
?>
